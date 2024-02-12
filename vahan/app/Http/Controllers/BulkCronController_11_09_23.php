<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use App\Models\Rcdetails;
use App\Models\Bulkfilelog;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use App\Traits\CommonTraits;
use App\Traits\ApisTraits;
use Yajra\DataTables\Facades\DataTables;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\File;

class BulkCronController extends Controller
{
    use CommonTraits;
    use ApisTraits;
    //This is a cron function which will execute at every 10 sec interval
    public function processBulkData()
    {
		ini_set('max_execution_time', 0);
		ini_set('memory_limit', '-1');
        $defaultStatusCode = 101;
        $custom_log = Log::channel('custom_log');
        $custom_log->debug("\n\n\n--------------------Start The processBulkData Here------------------------------------");

        $startDate = '2023-07-18 00:00:00';
        $fileData = DB::table('bulkfile_log')
            ->leftJoin('clients', 'bulkfile_log.client_id', '=', 'clients.id')
            ->select('bulkfile_log.*', 'clients.name as clientname')
            ->whereIn('bulkfile_log.status', [0,1])
            ->whereIn('clients.status', [0,1])
            ->where('clients.del_status', 1)
            ->where('bulkfile_log.is_processed', 1)
            ->where('bulkfile_log.created_at', '>=', $startDate)
            ->orderBy('bulkfile_log.id', 'asc')
            ->first();
            //->get();
        
        if(!empty($fileData))
        {
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
            //, 'cron_bulk_dump.status'
            $vehicleNumbers = DB::table('cron_bulk_dump')
                ->select('cron_bulk_dump.input', 'cron_bulk_dump.id')
                ->whereIn('cron_bulk_dump.status', [0,1])
                ->where('bulk_id', $id)
                ->orderBy('cron_bulk_dump.id', 'asc')
                ->take(25)
                ->get();
                //echo $id; dd($vehicleNumbers->toSql());
            $vehicleArr = $vehicleNumbers->pluck('input')->toArray();
                 
            $custom_log->debug(__LINE__." ---- Picked up non processed data  : ". json_encode($vehicleArr));
            if (!empty($vehicleArr)) {
                //block the selected rows
                $updateIds = $vehicleNumbers->pluck('id')->toArray();
                $processingFlag = $this->updateCron_bulk_dump($updateIds, $id, 'processing');
                $custom_log->debug(__LINE__." --- Set processing flag = 2 --- in table cron_bulk_dump for records count = ". $processingFlag);

                foreach ($vehicleNumbers as $key => $input) {
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

                    $custom_log->debug("\n\n".__LINE__." :  -------------- Process Start for Vehicle No (".$vehicleNo.") ---------------\n"); 
                    $isLogExist = $this->checkAPILogExist($input_id, $id);
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
                            $custom_log->debug(__LINE__." ----isValidVehicleNumber ---- : ".$isValidVehicleNumber);
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
                                    $responseArr        = $this->apiList($primaryVendor, $vehicleNo);
                                    $primary_response   = $responseArr['response'];
                                    $primary_status   = $responseArr['status_code'];
                                    //echo "<pre> primaryVendor : "; print_r($responseArr);
                                    if((isset($responseArr['status']) && $responseArr['status'] != 'success'))
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
                                            $responseArr = $this->apiList($secondaryVendor, $vehicleNo);
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
                    $this->updateCron_bulk_dump(array($input_id), $id, 'completed');
                    $custom_log->debug(__LINE__." :  ------ updateCron_bulk_dump as completed for ---- ".$input_id. '-----'.$id);
                }

                $processedCnt = $this->checkProcessedCount($id);
                $Bulkfilelog = Bulkfilelog::findOrFail($id);
                $Bulkfilelog->processed_count	= $processedCnt;
                $Bulkfilelog->save();
                $custom_log->debug(__LINE__." : Total Count : ". $totalCount. " - processed_count ". $processedCnt);
                if($totalCount ==  $processedCnt)
                {
                    $custom_log->debug(__LINE__." : Inside to downlaod excel data.");
                    // $sheedResult = $this->downloadDumpDataRCAuth($id);
                    $sheedResult = $this->downlaodDumpRCList($id);
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
           
        }
        else{
            $custom_log->debug(__LINE__." : None of the files available to process --");
        }      

        $custom_log->debug("\n\n\n--------------------End The processBulkData Here------------------------------------");
    }

    protected function apiList($vendor, $vehicleNo)
    {
        $response = array();
        switch (strtolower($vendor)) {
            case "authbridge":
                $response = $this->rcAuthbridge($vendor, $vehicleNo);
                break;
            case "signzy":
                $response = $this->rcSignzy($vendor, $vehicleNo);
                break;
            default:
                $response = array('status'=>'failed','status_code'=>'101', 'msg'=>'Invelid vendor', 'data'=>[]);
        }
        return $response;
    }


    protected function downlaodDumpRCList($id)
    {
        $bulkData = DB::table('bulkfile_log')
        ->select('bulkfile_log.created_at')
        ->where('bulkfile_log.id', $id)
        ->first();
        $dateCreated = $bulkData->created_at;

        $results = DB::table('cron_bulk_dump')
            ->leftJoin('api_log', 'cron_bulk_dump.id', '=', 'api_log.bulk_dump_id')
            ->select('api_log.response', 'cron_bulk_dump.input', 'api_log.response_status_code', 'api_log.vender')
            ->whereIn('api_log.status', [0,1])
            ->where('api_log.bulk_id', $id)
            ->orderBy('api_log.id', 'asc')
            ->get()
            ->toArray();

        if(!empty($results))
        {
            $csvData = array();
            $csvData[] = ['Sr.', 'Input RC Number','Vehicle Class', 'Fuel Type',	'Chassis Number',	'Engine Number',	'Manufacture Date',	'Model / Makers Class'	,'Maker/Manufacturer','Engine Capacity',	'Color'	,'Gross Weight',	'No of cylinder'	,'Seating Capacity',	'sleeper Capacity'	,'Norms Type',	'Body Type',	'Owner Serial Number'	,'Mobile Number'	,'Unloading Weight'	,'Rc Standard Cap'	,'Vehicle Standing Capacity',	'Vehicle Number',	'Blacklist Status'	,'Is Commercial',	'Noc Details',	'Registration Number'	,'Registration Date',	'Fitness Date/RC Expiry Date',	'RTO',	'Tax Upto',	'Vehicle Tax Up to'	,'Status'	,'Status As On'	,'Owners Name',	'Father Name/Husband Name',	'Permanent Address'	,'Present Address',	'Financer Name'	,'Insurance To Date/Insurance Upto',	'Policy Number'	,'Insurance Company',	'PUCC NO'	,'PUCC Upto',	'Permit Issue Date',	'Permit Number',	'Permit Type',	'Permit Vald From'	,'Permit Valid Upto',	'Non Use Status'	,'Non Use From',	'Non Use To',	'National Permit Number',	'National Permit Upto',	'National Permit Issued By','Remark'];
            foreach($results as $index => $result)
            {
                $vendor     = $result->vender;
                $vehicleNo  = $result->input;
                $number     = $index + 1; 
                if(!empty($result))
                {
                    switch (strtolower($vendor)) {
                        case "authbridge":
                            $csvData[] = $this->downloadDumpDataRCAuth($result, $vehicleNo, $number);
                            break;
                        case "signzy":
                            $csvData[] = $this->downloadDumpDataRCSign($result, $vehicleNo, $number);
                            break;
                        default:
                            $csvData[] = [$number,$vehicleNo,'','','','','','','','','','','','','','','','','','','','','','','','','','','','',	'',	'','','','','','','','','','','','','','','','','','','','','','','','','No Data Found'];
                    }
                }
                else{
                    $csvData[] = [$number,$vehicleNo,'','','','','','','','','','','','','','','','','','','','','','','','','','','','',	'',	'','','','','','','','','','','','','','','','','','','','','','','','','No Data Found'];
                }
            }


            $timestamp = date('Y_m_d_His');
            //$timestamp = date('Y_m_d_H_i_s');
            $downloadFilename = 'RC_RESULT_' . $timestamp . '.csv';
            
            $headers = [
                'Content-Type' => 'text/csv',
                'Content-Disposition' => "attachment; filename=\"$downloadFilename\""
            ];
            
            $tempFilePath = tempnam(sys_get_temp_dir(), 'RC_RESULT_');
            $tempFile = fopen($tempFilePath, 'w');
            foreach ($csvData as $csvRow) {
                fputcsv($tempFile, $csvRow);
            }
            
            fclose($tempFile);
            // Get the base URL of the application
            $baseUrl = URL::to('/');
            // Store and download paths are different, use Storage to get the correct file path
            $filePath = Storage::path("public/uploads/rcbulk/$downloadFilename");
            $file_url = "/storage/app/public/uploads/rcbulk/". $downloadFilename;
            rename($tempFilePath, $filePath);
            chmod($filePath, 0755);
            $startDate 	= Carbon::parse($dateCreated);
            $endDate 	= Carbon::parse(now());
            $totalTime = $endDate->diff($startDate)->format('%i min %s sec');

            $Bulkfilelog = Bulkfilelog::findOrFail($id);
            if ($Bulkfilelog->processed_count === $Bulkfilelog->count) {
                $Bulkfilelog->downloadurl	= $file_url;
                $Bulkfilelog->is_processed	= 2;
                $Bulkfilelog->duration		= $totalTime;
                $Bulkfilelog->updated_at	= now();
                $Bulkfilelog->save();
            } 
            return array('status'=> 'success', 'msg'=>'Process Comleted Successfully');
        }
        else{
            return array('status'=> 'failed', 'msg'=>'File not created');
        }
    }

    public function updateCron_bulk_dump($idArr, $bulk_id, $type = 'completed')
    {
        $return = '';
        $status = 2;
        if($type == 'completed')
        {
            $status = 3;
        }
		else if($type == 'init')
		{
			$status = 1;
		}
        if(!is_array($idArr))
        {
            $idArr = explode(',',$idArr);
        }
        $return = DB::table('cron_bulk_dump')
        ->where('bulk_id', $bulk_id)
        ->whereIn('id', $idArr)
        ->update(['status' => $status]);

        return $return;
    }


    
    public function checkAPILogExist($input_id, $bulk_id)
    {
        $result = false;
        $fileData = DB::table('api_log')
            ->select('api_log.id')
            ->whereIn('api_log.status', [0,1])
            ->where('api_log.bulk_dump_id', $input_id)
            ->where('api_log.bulk_id',  $bulk_id) // Applying the "like" query for the complete date
            ->first();

        if(isset($fileData->id))
        {
            $result = true;
        }
        
        return $result;
    }

    public function checkProcessedCount($bulk_id)
    {
        return $count = DB::table('cron_bulk_dump')
            ->select('id')
            ->where('status', 3)
            ->where('bulk_id', $bulk_id) // Applying the "like" query for the complete date
            ->count();
    }

    public function downloadDumpDataRCAuth($result, $vehicleNo, $number)
    {
        $csvData = [$number,$vehicleNo,'','','','','','','','','','','','','','','','','','','','','','','','','','','','',	'',	'','','','','','','','','','','','','','','','','','','','','','','','','No Data'];
        if(!empty($result))
        {
            $response       = (!empty($result->response)) ? json_decode($result->response, true) : '';
            $status_code    = (!empty($result->response_status_code)) ? $result->response_status_code : '101';
            $message        = isset($response['message']) ? $response['message'] : 'Data not found' ;
            if(!empty($status_code) && $status_code != '200')
            {
                $csvData = [$number,$vehicleNo,'','','','','','','','','','','','','','','','','','','','','','','','','','','','',	'',	'','','','','','','','','','','','','','','','','','','','','','','','',$message];
            }
            else{
                if(isset($response['msg'])){
                    $msg = $response['msg']; 
                    $csvData = [
                        $number,
                        isset($msg['Registration Details']['Registration Number']) ? $msg['Registration Details']['Registration Number'] : null, // Input RC Number
                        
                        (isset($msg['Vehicle Details']['Vehicle Category']) ? $msg['Vehicle Details']['Vehicle Category']  : null) . " ". (isset($msg['Vehicle Details']['Vehicle Class']) ? $msg['Vehicle Details']['Vehicle Class'] : null), // Vehicle Class
                        isset($msg['Vehicle Details']['Fuel Type']) ? $msg['Vehicle Details']['Fuel Type'] : null, // Fuel Type
                        isset($msg['Vehicle Details']['Chassis Number']) ? $msg['Vehicle Details']['Chassis Number'] : null, // Chassis Number
                        isset($msg['Vehicle Details']['Engine Number']) ? $msg['Vehicle Details']['Engine Number']  : null, // Engine Number
                        isset($msg['Vehicle Details']['Manufacture Date']) ? $msg['Vehicle Details']['Manufacture Date'] : null, // Manufacture Date
                        isset($msg['Vehicle Details']['Model / Makers Class']) ? $msg['Vehicle Details']['Model / Makers Class'] : null, // Model / Makers Class Date
                        isset($msg['Vehicle Details']['Maker/Manufacturer']) ? $msg['Vehicle Details']['Maker/Manufacturer'] : null, // Maker/Manufacturer
                        isset($msg['Vehicle Details']['Engine Capacity']) ? $msg['Vehicle Details']['Engine Capacity'] : null, // Engine Capacity
                        isset($msg['Vehicle Details']['Color']) ? $msg['Vehicle Details']['Color'] : null, // Color
                        isset($msg['Vehicle Details']['Gross Weight']) ? $msg['Vehicle Details']['Gross Weight'] : null, // Gross Weight
                        isset($msg['Vehicle Details']['No of cylinder']) ? $msg['Vehicle Details']['No of cylinder'] : null, // No of cylinder
                        isset($msg['Vehicle Details']['Seating Capacity']) ? $msg['Vehicle Details']['Seating Capacity'] : null, // Seating Capacity
                        isset($msg['Vehicle Details']['sleeper Capacity']) ? $msg['Vehicle Details']['sleeper Capacity'] : null, // sleeper Capacity
                        isset($msg['Vehicle Details']['Norms Type']) ? $msg['Vehicle Details']['Norms Type'] : null, // Norms Type
                        isset($msg['Vehicle Details']['Body Type']) ? $msg['Vehicle Details']['Body Type'] : null, // Body Type
                        isset($msg['Vehicle Details']['Owner Serial Number']) ? $msg['Vehicle Details']['Owner Serial Number'] : null, // Owner Serial Number
                        isset($msg['Vehicle Details']['Mobile Number']) ? $msg['Vehicle Details']['Mobile Number'] : null, // Mobile Number
                        isset($msg['Vehicle Details']['Unloading Weight']) ? $msg['Vehicle Details']['Unloading Weight'] : null, // Unloading Weight
                        isset($msg['Vehicle Details']['Rc Standard Cap']) ? $msg['Vehicle Details']['Rc Standard Cap'] : null, // Rc Standard Cap
                        isset($msg['Vehicle Details']['Vehicle Standing Capacity']) ? $msg['Vehicle Details']['Vehicle Standing Capacity'] : null, // Vehicle Standing Capacity
                        isset($msg['Vehicle Details']['Vehicle Number']) ? $msg['Vehicle Details']['Vehicle Number'] : null, // Vehicle Number
                        isset($msg['Vehicle Details']['Blacklist Status']) ? $msg['Vehicle Details']['Blacklist Status'] : null, // Blacklist Status
                        isset($msg['Vehicle Details']['Is Commercial']) ? $msg['Vehicle Details']['Is Commercial'] : null, // Is Commercial
                        isset($msg['Vehicle Details']['Noc Details']) ? $msg['Vehicle Details']['Noc Details'] : null, // Noc Details
                        isset($msg['Registration Details']['Registration Number']) ? $msg['Registration Details']['Registration Number'] : null, // Registration Number
                        isset($msg['Registration Details']['Registration Date']) ? $msg['Registration Details']['Registration Date'] : null, // Registration Date
                        isset($msg['Registration Details']['Fitness Date/RC Expiry Date']) ? $msg['Registration Details']['Fitness Date/RC Expiry Date']  : null, // Fitness Date/RC Expiry isset(Date
                        isset($msg['Registration Details']['RTO']) ? $msg['Registration Details']['RTO'] : null, // RTO
                        isset($msg['Registration Details']['Tax Upto']) ? $msg['Registration Details']['Tax Upto'] : null, // Tax Upto
                        isset($msg['Registration Details']['Vehicle Tax Up to']) ? $msg['Registration Details']['Vehicle Tax Up to'] : null, // Vehicle Tax Up to
                        isset($msg['Registration Details']['Status']) ? $msg['Registration Details']['Status'] : null, // Status
                        isset($msg['Registration Details']['Status As On']) ? $msg['Registration Details']['Status As On'] : null, // Status As On
                        isset($msg['Owners Details']['Owners Name']) ? $msg['Owners Details']['Owners Name'] : null, // Owners Name
                        isset($msg['Owners Details']['Father Name/Husband Name']) ? $msg['Owners Details']['Father Name/Husband Name'] : null, // Father Name/Husband Name
                        isset($msg['Owners Details']['Permanent Address']) ? $msg['Owners Details']['Permanent Address']  : null, // Permanent Address
                        isset($msg['Owners Details']['Present Address']) ? $msg['Owners Details']['Present Address'] : null, // Present Address
                        isset($msg['Hypothecation Details']['Financer Name']) ? $msg['Hypothecation Details']['Financer Name'] : null, // Financer Name
                        isset($msg['Insurance Details']['Insurance To Date/Insurance Upto']) ? $msg['Insurance Details']['Insurance To Date/Insurance Upto'] : null, // Insurance To Date/isset(Insurance Upto
                        isset($msg['Insurance Details']['Policy Number']) ? $msg['Insurance Details']['Policy Number'] : null, // Policy Number
                        isset($msg['Insurance Details']['Insurance Company']) ? $msg['Insurance Details']['Insurance Company'] : null, // Insurance Company
                        isset($msg['RC Status']['PUCC NO']) ? $msg['RC Status']['PUCC NO'] : null, // PUCC NO
                        isset($msg['RC Status']['PUCC Upto']) ? $msg['RC Status']['PUCC Upto'] : null, // PUCC Upto
                        isset($msg['RC Status']['Permit Issue Date']) ? $msg['RC Status']['Permit Issue Date'] : null, // Permit Issue Date
                        isset($msg['RC Status']['Permit Number']) ? $msg['RC Status']['Permit Number'] : null, // Permit Number
                        isset($msg['RC Status']['Permit Type']) ? $msg['RC Status']['Permit Type'] : null, // Permit Type
                        isset($msg['RC Status']['Permit Vald From']) ? $msg['RC Status']['Permit Vald From'] : null, // Permit Vald From
                        isset($msg['RC Status']['Permit Valid Upto']) ? $msg['RC Status']['Permit Valid Upto'] : null, // Permit Valid Upto
                        isset($msg['RC Status']['Non Use Status']) ? $msg['RC Status']['Non Use Status'] : null, // Non Use Status
                        isset($msg['RC Status']['Non Use From']) ? $msg['RC Status']['Non Use From'] : null, // Non Use From
                        isset($msg['RC Status']['Non Use To']) ? $msg['RC Status']['Non Use To'] : null, // Non Use To
                        isset($msg['RC Status']['National Permit Number']) ? $msg['RC Status']['National Permit Number'] : null, // National Permit Number
                        isset($msg['RC Status']['National Permit Upto']) ? $msg['RC Status']['National Permit Upto'] : null, // National Permit Upto
                        isset($msg['RC Status']['National Permit Issued By']) ? $msg['RC Status']['National Permit Issued By'] : null // National
                    ];
                }
                else{
                    $csvData = [$number,$vehicleNo,'','','','','','','','','','','','','','','','','','','','','','','','','','','','',	'',	'','','','','','','','','','','','','','','','','','','','','','','','',$message]; 
                } 
            }
        }
        return $csvData;
    }


    
    public function downloadDumpDataRCSign($result, $vehicleNo, $number)
    {
        $csvData = [$number,$vehicleNo,'','','','','','','','','','','','','','','','','','','','','','','','','','','','',	'',	'','','','','','','','','','','','','','','','','','','','','','','','','No Data Found'];
        if(!empty($result))
        {
            $response       = (!empty($result->response)) ? json_decode($result->response, true) : '';
            $status_code    = (!empty($result->response_status_code)) ? $result->response_status_code : '101';
            $message        = isset($response['message']) ? $response['message'] : 'Data not found' ;
            if(!empty($status_code) && $status_code != '200')
            {
                $csvData = [$number,$vehicleNo,'','','','','','','','','','','','','','','','','','','','','','','','','','','','',	'',	'','','','','','','','','','','','','','','','','','','','','','','','',$message];
            }
            else{
                if(isset($response['result'])){
                    $msg = $response['result']; 
                    $csvData = [
                        $number,
                        isset($msg['regNo']) ? $msg['regNo'] : null, // Input RC Number
                        isset($msg['class']) ? $msg['class']." ".$msg['vehicleCategory']  : null, // Vehicle Class
                        isset($msg['type']) ? $msg['type'] : null, // type
                        isset($msg['chassis']) ? $msg['chassis'] : null, // Chassis Number
                        isset($msg['engine']) ? $msg['engine']  : null, // Engine Number
                        isset($msg['regDate']) ? $msg['regDate'] : null, // Manufacture Date
                        isset($msg['model']) ? $msg['model'] : null, // Model / Makers Class Date
                        isset($msg['vehicleManufacturerName']) ? $msg['vehicleManufacturerName'] : null, // Maker/Manufacturer 
                        isset($msg['vehicleCubicCapacity']) ? $msg['vehicleCubicCapacity'] : null, // Engine Capacity -----
                        isset($msg['vehicleColour']) ? $msg['vehicleColour'] : null, // Color
                        isset($msg['grossVehicleWeight']) ? $msg['grossVehicleWeight'] : null, // Gross Weight
                        isset($msg['vehicleCylindersNo']) ? $msg['vehicleCylindersNo'] : null, // No of cylinder
                        isset($msg['vehicleSeatCapacity']) ? $msg['vehicleSeatCapacity'] : null, // Seating Capacity
                        isset($msg['vehicleSleeperCapacity']) ? $msg['vehicleSleeperCapacity'] : null, // sleeper Capacity
                        isset($msg['normsType']) ? $msg['normsType'] : null, // Norms Type
                        isset($msg['bodyType']) ? $msg['bodyType'] : null, // Body Type
                        isset($msg['ownerCount']) ? $msg['ownerCount'] : null, // Owner Serial Number
                        isset($msg['mobileNumber']) ? $msg['mobileNumber'] : null, // Mobile Number
                        isset($msg['unladenWeight']) ? $msg['unladenWeight'] : null, // Unloading Weight
                        isset($msg['rcStandardCap']) ? $msg['rcStandardCap'] : null, // Rc Standard Cap
                        isset($msg['vehicleStandingCapacity']) ? $msg['vehicleStandingCapacity'] : null, // Vehicle Standing Capacity
                        isset($msg['vehicleNumber']) ? $msg['vehicleNumber'] : null, // Vehicle Number
                        isset($msg['blacklistStatus']) ? $msg['blacklistStatus'] : null, // Blacklist Status
                        isset($msg['isCommercial']) ? $msg['isCommercial'] : null, // Is Commercial
                        isset($msg['nocDetails']) ? $msg['nocDetails'] : null, // Noc Details
                        isset($msg['regNo']) ? $msg['regNo'] : null, // Registration Number
                        isset($msg['regDate']) ? $msg['regDate'] : null, // Registration Date
                        isset($msg['rcExpiryDate']) ? $msg['rcExpiryDate']  : null, // Fitness Date/RC Expiry isset(Date
                        isset($msg['RTO']) ? $msg['RTO'] : null, // RTO
                        isset($msg['vehicleTaxUpto']) ? $msg['vehicleTaxUpto'] : null, // Tax Upto
                        isset($msg['vehicleTaxUpto']) ? $msg['vehicleTaxUpto'] : null, // Vehicle Tax Up to
                        isset($msg['status']) ? $msg['status'] : null, // Status
                        isset($msg['statusAsOn']) ? $msg['statusAsOn'] : null, // Status As On
                        isset($msg['owner']) ? $msg['owner'] : null, // Owners Name
                        isset($msg['ownerFatherName']) ? $msg['ownerFatherName'] : null, // Father Name/Husband Name
                        isset($msg['permanentAddress']) ? $msg['permanentAddress']  : null, // Permanent Address
                        isset($msg['presentAddress']) ? $msg['presentAddress'] : null, // Present Address
                        isset($msg['rcFinancer']) ? $msg['rcFinancer'] : null, // Financer Name
                        isset($msg['vehicleInsuranceUpto']) ? $msg['vehicleInsuranceUpto'] : null, // Insurance To Date/isset(Insurance Upto
                        isset($msg['vehicleInsurancePolicyNumber']) ? $msg['vehicleInsurancePolicyNumber'] : null, // Policy Number
                        isset($msg['vehicleInsuranceCompanyName']) ? $msg['vehicleInsuranceCompanyName'] : null, // Insurance Company
                        isset($msg['puccNumber']) ? $msg['puccNumber'] : null, // PUCC NO
                        isset($msg['puccUpto']) ? $msg['puccUpto'] : null, // PUCC Upto
                        isset($msg['permitIssueDate']) ? $msg['permitIssueDate'] : null, // Permit Issue Date
                        isset($msg['permitNumber']) ? $msg['permitNumber'] : null, // Permit Number
                        isset($msg['permitType']) ? $msg['permitType'] : null, // Permit Type
                        isset($msg['permitValidFrom']) ? $msg['permitValidFrom'] : null, // Permit Vald From
                        isset($msg['permitValidUpto']) ? $msg['permitValidUpto'] : null, // Permit Valid Upto
                        isset($msg['nonUseStatus']) ? $msg['nonUseStatus'] : null, // Non Use Status
                        isset($msg['nonUseFrom']) ? $msg['nonUseFrom'] : null, // Non Use From
                        isset($msg['nonUseTo']) ? $msg['nonUseTo'] : null, // Non Use To
                        isset($msg['nationalPermitNumber']) ? $msg['nationalPermitNumber'] : null, // National Permit Number
                        isset($msg['nationalPermitUpto']) ? $msg['nationalPermitUpto'] : null, // National Permit Upto
                        isset($msg['nationalPermitIssuedBy']) ? $msg['nationalPermitIssuedBy'] : null // National
                    ];
                }
                else{
                    $csvData = [$number,$vehicleNo,'','','','','','','','','','','','','','','','','','','','','','','','','','','','',	'',	'','','','','','','','','','','','','','','','','','','','','','','','',$message]; 
                } 
            }
        }
        return $csvData;
    }

    //To check if found any record in the bulkfile_log as processed with not processed in the cron_bulk_dump then it reset the flag and do the cron activity again.
    public function resetBulkProcessFlag()
    {
        $custom_log = Log::channel('custom_log');
        $custom_log->debug("\n\n\n--------------------Start The resetBulkProcessFlag Here------------------------------------");


        $fileData = DB::table('bulkfile_log')
        ->leftJoin('cron_bulk_dump', 'bulkfile_log.id', '=', 'cron_bulk_dump.bulk_id')
        ->select('bulkfile_log.*', 'cron_bulk_dump.id as dumpid', 'cron_bulk_dump.input as input')
        ->whereIn('bulkfile_log.status', [0,1])
        ->whereIn('cron_bulk_dump.status', [0,1])
        ->where('bulkfile_log.is_processed', 2)
        ->where('bulkfile_log.retry_attempts', '<=', 3)
        ->orderBy('cron_bulk_dump.id', 'asc')
        ->get();
        
        if($fileData)
        {
            $retryDataArr = array();
            foreach($fileData as $k => $data)
            {
                $custom_log->debug(__LINE__." Found the Data : ".json_encode($data));
               
                $custom_log->debug(__LINE__."  Retry Attemps : ".$data->retry_attempts);
                if($data->retry_attempts <= 2)
                {
                    
                    $retryDataArr[$data->id]['dumpid'][] = $data->dumpid;
                    $retryDataArr[$data->id]['retry_attempts'] = $data->retry_attempts;
                }
                else{

                    $custom_log->debug(__LINE__." Create log once attemp is reached!");

                    DB::table('cron_bulk_dump')
                    ->where('id', $data->dumpid)
                    ->where('bulk_id', $data->id)
                    ->update(['status' => 3]);


                    $api_log =  new Rcdetails();
                    $api_log->api_id = $data->api_id;
                    $api_log->api_name = $data->api_name;
                    $api_log->vender = $data->vendor;
                    $api_log->user_id = $data->user_id;
                    $api_log->client_id = $data->client_id;
                    $api_log->client_name = '';
                    $api_log->response_status_code = '101';
                    $api_log->response_message  = 'Reached max retry attempt';
                    $api_log->remark  = 'Reached max retry attempt';
                    $api_log->api_url = '';
                    $api_log->request  = '';
                    $api_log->response = '';
                    $api_log->request_type = 2;
                    $api_log->bulk_id = $data->id;
                    $api_log->bulk_dump_id = $data->dumpid;
                    $api_log->response_from = 2;
                    $api_log->status = '1';
                    $api_log->method = 'POST';
                    $api_log->save();
                }
            }

            if(!empty($retryDataArr))
            {
                $custom_log->debug(__LINE__." Rest the table for retry attemps : ".json_encode($retryDataArr));
                //$retryDataArr[$data->id]['dumpid'][] = $data->dumpid;
                foreach($retryDataArr as $bulkid => $data)
                {
                    $retryAttempts = $data['retry_attempts'] + 1;
                    DB::table('bulkfile_log')
                    ->where('id', $bulkid)
                    ->update(['is_processed' => 1, 'retry_attempts'=>$retryAttempts]);

                    DB::table('cron_bulk_dump')
                    ->whereIn('id', $data['dumpid'])
                    ->where('bulk_id', $bulkid)
                    ->update(['status' => 1]);
                }
            }
        }

        $custom_log->debug("\n\n\n--------------------End The resetBulkProcessFlag Here------------------------------------");
    }

    public function downloadProcessedFile()
    {
        $fileData = DB::table('bulkfile_log')
        ->select('bulkfile_log.id as bulkid')
        ->whereIn('bulkfile_log.status', [0,1])
        ->where('bulkfile_log.is_processed', 2)
        ->whereNull('bulkfile_log.downloadurl')
        ->orderBy('bulkfile_log.id', 'asc')
        ->take(5)
        ->get();

        $bulkidArr = array_unique($fileData->pluck('bulkid')->toArray());
        if(!empty($bulkidArr))
        {
            foreach($bulkidArr as $k => $id)
            {
                $this->downloadDumpDataRCAuth($id);
            }
        }
    }
 
    public function getCurrentControllerName()
    {
        $controllerName = class_basename(__CLASS__);
        return Str::replaceLast('Controller', '', $controllerName);
    }

    private function checkHistoryRC($vehicleNo, $vendor)
    {
        $returnArr = '';
        $result = DB::select("SELECT id, response FROM `history_rc` WHERE vehicle_no = '$vehicleNo' AND vendor = '$vendor' AND `status` IN (0,1) AND created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY) ORDER BY id DESC LIMIT 1");
        if (!empty($result)) {
            
            $returnArr = $result[0]->response;
        }
        else{
            DB::table('history_rc')
            ->where('vehicle_no', $vehicleNo)
            ->where('vendor', $vendor)
            ->whereIn('status', [0, 1])
            ->delete();
            $returnArr = '';
        }
        // echo "<pre>"; print_r($returnArr);die;
        return $returnArr;
    }

    private function addHistoryRC($vehicleNo, $request, $vendor, $response)
    {
        $createdAt = now();
        // Insert the new record
        return DB::table('history_rc')->insert([
            'vehicle_no' => $vehicleNo,
            'vendor' => $vendor,
            'request' => $request,
            'response' => $response,
            'status' => 1,
            'created_at' => $createdAt,
        ]);
    }

}
