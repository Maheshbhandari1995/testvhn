<?php
namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use App\Models\Rcdetails;
use App\Models\Bulkfilelog;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use App\Traits\CommonTraits;
use App\Traits\ApisTraits;
use Yajra\DataTables\Facades\DataTables;
use Carbon\Carbon;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\File;
use App\Http\Controllers\BulkCronController;
use Illuminate\Support\Facades\Cache;

class ProcessBulkFile implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    use CommonTraits;
    use ApisTraits;
	public $tries = 25; // Increase this value based on your needs

    public $fileData;
    public $cnt = 30;
    public $st_cnt = 30;

    public function __construct($fileData, $count)
    {
        $this->fileData = $fileData;
        $this->cnt = ceil($this->st_cnt / $count);
    }
	

    public function handle()
    {           
	
		$cacheKey = 'process_bulk_file_lock';

        // Check if the lock is set
        if (Cache::has($cacheKey)) {
            $this->release(60); // Release the job and retry after 60 seconds
            return;
        }

        // Set the lock
        Cache::put($cacheKey, true, now()->addMinutes(1)); // Lock for 1 minute
		
	//date("Y-m-d H:i:s")
            ini_set('max_execution_time', 0);
            ini_set('memory_limit', '-1');
            $custom_log = Log::channel('custom_log');
            $fileData = $this->fileData;
			$custom_log->debug(__LINE__." ---- Start Processiong at  : ". date("Y-m-d H:i:s"));

            $clientname     = $fileData->clientname;
            $user_id        = $fileData->user_id;
            $client_id      = $fileData->client_id;
            $api_id         = $fileData->api_id;
            $api_name       = $fileData->api_name;
            $vendor         = $fileData->vendor;
            $dateCreated    = $fileData->created_at;
            $totalCount     = $fileData->count;
            $id             = $fileData->id;
            $processed_count = $fileData->processed_count;
            $filename_bulk  = $fileData->filename;
            $remark         = '';
            $jsonDataStep1  = '';
            $vehicleArr     = []; 
            $primaryVendor  = 'signzy';
            $secondaryVendor = 'authbridge';			
            $method         = 'POST';
            $defaultStatusCode = 101;

				$vehicleNumbers = DB::table('cron_bulk_dump')
				->select('cron_bulk_dump.input', 'cron_bulk_dump.id')
				->whereIn('cron_bulk_dump.status', [0,1])
				->where('bulk_id',$id)
				->orderBy('cron_bulk_dump.id', 'asc')
				->take($this->cnt) //change by gaurav at 10:51 16/11/23
				->get();

				$vehicleArr = $vehicleNumbers->pluck('input')->toArray();
					 
				$custom_log->debug(__LINE__." ---- Picked up non processed data  : ". json_encode($vehicleArr));
				if (!empty($vehicleArr)) {
					//block the selected rows
					$updateIds = $vehicleNumbers->pluck('id')->toArray();
					$processingFlag = BulkCronController::updateCron_bulk_dump($updateIds, $id, 'processing');
					$custom_log->debug(__LINE__." --- Set processing flag = 2 --- in table cron_bulk_dump for records count = ". $processingFlag);

					foreach ($vehicleNumbers as $key => $input) {
						
						ini_set('max_execution_time', 0);
						ini_set('memory_limit', '-1');
						$vehicleNo      = strtoupper($input->input);
						$input_id       = $input->id;
						$response       = '';
						$msg            = 'failed';
						$response_from  = 1;
						$response_type  = 0;
						$url            = '';
						$statusCode     = 0;
						$requestData    = '';
						$response       = '';
						$response_message = '';
						$remark         = 'Response from History';
						$primary_response   = '';
						$secondary_response = '';
						$primary_status     = 0;
						$secondary_status   = 0;
						$api_detail_log_id  = 0;


						$processed_count++;

						$custom_log->debug("\n\n".__LINE__." :  -------------- Process Start for Vehicle No (".$vehicleNo.") ---------------".date("Y-m-d H:i:s")."\n"); 
						$isLogExist = BulkCronController::checkAPILogExist($input_id, $id);
						$custom_log->debug(__LINE__." --- isLogExist  --- ". $isLogExist. " For ----".$input_id."------".$id);
						if($isLogExist == false)
						{
							$isCreditAvaialbe = $this->checkCredit($client_id);
							if($isCreditAvaialbe === false)
							{
								$statusCode = $defaultStatusCode;
								$response = json_encode(['vehicleNo' => $vehicleNo, 'status_code' => $statusCode, 'message' => 'You do not have enough credit to perform this action']);                           
							}
							else{
								$custom_log->debug(__LINE__." ----checkCredit ---- : ".$isCreditAvaialbe);
								// Validate the vehicle number
								$vehicleNo              = $this->filterVehicleNumber($vehicleNo);
								$isValidVehicleNumber   = $this->validateVehicleNumber($vehicleNo);
								//$vehicleNo = $isValidVehicleNumber;
								$custom_log->debug(__LINE__." ----isValidVehicleNumber ---- : ".$vehicleNo);

								//////////////////////
								// $response   = json_encode(['vehicleNo' => $vehicleNo, 'status_code' => $statusCode, 'message' => 'vehicle number is not valid']);
								////////////////////////////

								if ($isValidVehicleNumber === false) {
									$statusCode = $defaultStatusCode;
									$response   = json_encode(['vehicleNo' => $vehicleNo, 'status_code' => $statusCode, 'message' => 'vehicle number is not valid']);
								}
								else{
									$response = [];
									$responseHistrory = $this->checkHistoryRCForApiList([$primaryVendor,$secondaryVendor], $vehicleNo);
									if(isset($responseHistrory) && empty($responseHistrory)) 
									{
										
										$custom_log->debug(__LINE__." --- API Hits for Primary Vendor --- ".$primaryVendor);
										$response_from  = 1;
										$response_type  = 1;
										$vendor         = $primaryVendor; 
										$remark         = 'Response from Primary Vendor API';
										// Primary Vendor
										$responseArr        = BulkCronController::apiList($primaryVendor, $vehicleNo);
										$primary_response   = $responseArr['response'];
										$primary_status   = $responseArr['status_code'];
										//echo "<pre> primaryVendor : "; print_r($responseArr);
										//adding condition for primaryVendor hits if 404 and 9 
										if((isset($responseArr['status']) && $responseArr['status'] != 'success' && in_array($responseArr['status_code'], [409, 0])))
										{
										//  echo "<pre> secondaryVendor -- : "; print_r($secondaryVendor);//die;
											$custom_log->debug(__LINE__." --- No data found from Primary vendor with status --- ".$responseArr['status']);
											//Secondary Vendor
											if(isset($secondaryVendor) && !empty($secondaryVendor))
											{
												$vendor = $secondaryVendor;
												$response_type  = 2;
												$remark         = 'Response from Secondary Vendor API';
												$custom_log->debug(__LINE__." --- API Hits for Secondary Vendor --- ".$secondaryVendor);
												$responseArr = BulkCronController::apiList($secondaryVendor, $vehicleNo);
												$secondary_response     = $responseArr['response'];
												$secondary_status         = $responseArr['status_code'];
												if((isset($responseArr['status']) && $responseArr['status'] == 'success'))
												{
													$custom_log->debug(__LINE__." --- Pulled from Secondary Vendor  --- ");
												}
												else{
													//Error Response
													$custom_log->debug(__LINE__." --- No data found from secondary vendor  --- ");
												}
											// echo "<pre> secondaryVendor : "; print_r($responseArr);
											}else{
												//Error Response
												$custom_log->debug(__LINE__." --- Secondary Vendor Name : ".$secondaryVendor);
											}
										}
										else{   
											$secondaryVendor = '';
											$secondary_response = '-';
											$secondary_status = '';
											$custom_log->debug(__LINE__." --- Pulled from Primary Vendor  --- ");
										}

										//
										$response       = $responseArr['response'];
										$statusCode     = $responseArr['status_code'];
										$url            = $responseArr['url'];
										$requestData    = $responseArr['requestData'];
										$response_message= $responseArr['msg'];

										//Add API Details Log
										$api_detail_log_id = DB::table('api_detail_log')->insertGetId([
											'input' => $vehicleNo,
											'primary_vendor' => $primaryVendor,
											'primary_response' => $primary_response,
											'primary_status' => $primary_status,
											'secondary_vendor' => $secondaryVendor,
											'secondary_response' => $secondary_response,
											'secondary_status' => $secondary_status,
											'status' => 1,
											'created_at' => now(),
										]);
										$custom_log->debug(__LINE__." --- Add Entry into  api_detail_log  with inserted ID --- ". $api_detail_log_id);
									}
									else{                                
										$response       = $responseHistrory['response'];
										$vendor         = $responseHistrory['vendor'];
										$statusCode     = $responseHistrory['status_code'];
										$url            = '';
										$response_from  = 2;
										$requestData    = json_encode(array("docNumber" => $vehicleNo,"transID" => "1234567","docType" =>"372"));
										$response_message= ($responseHistrory['status_code'] == 200 ? "Success": " No Data Found");
										$custom_log->debug(__LINE__." --- Pulled from History  --- ");
									}
								}
							

							}
						   // echo "<pre> sdfsdfsdf : "; print_r($response);die;  
							$updatedID = $this->updateUtilizedCredit($client_id);
							$custom_log->debug(__LINE__." --- Update clients for Credit with updated ID --- ". $updatedID);

							//Details Log need to be incorporate
							$api_log =  new Rcdetails();
							$api_log->api_id = $api_id;
							$api_log->api_name = $api_name;
							$api_log->vender = $vendor;
							$api_log->user_id = $user_id;
							$api_log->client_id = $client_id;
							$api_log->client_name = $clientname;
							$api_log->response_status_code = $statusCode;
							$api_log->response_message  = $response_message;
							$api_log->remark  = $remark;
							$api_log->api_url = $url;
							$api_log->input  = $vehicleNo ;
							$api_log->request  = $requestData ;
							$api_log->response = $response;
							$api_log->request_type = 2;
							$api_log->bulk_id = $id;
							$api_log->bulk_dump_id = $input_id;
							$api_log->response_from = $response_from;
							$api_log->response_type = $response_type;
							$api_log->api_detail_log_id = $api_detail_log_id;
							$api_log->status = '1';
							$api_log->method = $method;
							$api_log->save();

							$custom_log->debug(__LINE__." --- Add APILOG Table  for ID --- ". $api_log->id);
						}
						else{
							$custom_log->debug(__LINE__." --- Log is exist into log table  --- ". $isLogExist. " For ----".$input_id."------".$id);
						}
						//update flag as completed
						BulkCronController::updateCron_bulk_dump(array($input_id), $id, 'completed');
						$custom_log->debug(__LINE__." :  ------ updateCron_bulk_dump as completed for ---- ".$input_id. '-----'.$id. '---'.date("Y-m-d H:i:s"));
					}

					$processedCnt = BulkCronController::checkProcessedCount($id);
					$Bulkfilelog = Bulkfilelog::findOrFail($id);
					$Bulkfilelog->processed_count	= $processedCnt;
					$Bulkfilelog->save();
					$custom_log->debug(__LINE__." : Total Count : ". $totalCount. " - processed_count ". $processedCnt);
					if($totalCount ==  $processedCnt)
					{
						$custom_log->debug(__LINE__." : Inside to downlaod excel data.");
						// $sheedResult = $this->downloadDumpDataRCAuth($id);
						$sheedResult = BulkCronController::downlaodDumpRCList($id);
						$custom_log->debug(__LINE__." : downlaodDumpRCList - ". json_encode($sheedResult));
						if(isset($sheedResult['status']) && ($sheedResult['status'] == 'success'))
						{
							//Add Notification status
							$notifyStaus = DB::table('notification')->insert([
								'user_id' => $user_id,
								'client_id' => $client_id,
								'subject' => 'Bulk Upload Report For File : '.$filename_bulk. " has been proccessed",
								'body' => 'Bulk Upload Report for file '.$filename_bulk.' has been proccessed successfully, please check and download from the list!',
								'status' => 1,
								'created_at' => now()
							]);

							$custom_log->debug(__LINE__." : Add notification to the notification table, status = ".$notifyStaus);
						}
						else{
							$custom_log->debug(__LINE__." : ----download staus is failed--------------");
						}
					}
					else{
						$custom_log->debug(__LINE__." : ----Total count is no match with processed count --------------");
					}
				} else 
				{
					$custom_log->debug(__LINE__." : ----No more vehicle no is availble to pull the data , so updating the is_processed flag--------------");
					$processedFlag = DB::table('bulkfile_log')
						->where('id', $id)
						->update(['is_processed' => 2]);
					if($processedFlag == 1)
					{
						$processFlagStatus = json_encode(array('status'=> 'success', 'msg'=>'Task has been processed'));
					}   
					else{
						$processFlagStatus = json_encode(array('status'=> 'failed', 'msg'=>'Unable to update the processed flag.'));
					}

					$custom_log->debug(__LINE__." : Status of the updating processed flag as --".$processFlagStatus);
				}	
				
			
			$custom_log->debug(__LINE__." ---- End Processiong at  : ". date("Y-m-d H:i:s"));
			
		// Release the lock
        Cache::forget($cacheKey);
	}
	
	
	protected function isLocked()
	{
		return $this->job->getRawBody()['is_locked'] ?? false;
	}

	protected function lock()
	{
		$this->job->getRawBody()['is_locked'] = true;
	}

	protected function releaseLock()
	{
		$this->job->getRawBody()['is_locked'] = false;
		$this->job->delete();
	}
}
