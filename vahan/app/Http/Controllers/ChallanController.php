<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use App\Models\Company;
use App\Models\Rcdetails;
use App\Models\Module;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Http;
use PhpParser\Node\Expr\Print_;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use App\Traits\CommonTraits;
use App\Traits\ApisTraits;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use PDF;

class ChallanController extends Controller
{
    use CommonTraits;
    use ApisTraits;

    public function invincibleViewChallanWithChassis()
    {
        //echo "123"; die;
        //$users = Users::whereIn('status', [0,1,2])->get(); //compact('users')
        return view('challan.challan_chassis');
    }

    public function invincibleChallanWithChassisPostData(Request $request)
    { 
        $sessionData = session('data');
        if($this->checkCredit() === false)
        {
            return response()->json(['status' => 'No more credit limit available']);
        }
        else{
            
            $response_from = 1;
            // echo "<pre>"; print_r($request->input);die;
            $apiUrl =     Config::get('custom.invincible.challan_chassis.challan_url');
            $clientId =   Config::get('custom.invincible.challan_chassis.clientId');
            $secretKey =  Config::get('custom.invincible.challan_chassis.secretKey');
            $api_id     = Config::get('custom.invincible.challan_chassis.api_id');
            $api_name   = Config::get('custom.invincible.challan_chassis.api_name');
            $vendor     = Config::get('custom.invincible.challan_chassis.vender');
            
            // Get the vehicle number from the request
            $vehicleNo      = $request->input('vehicle_No');
            $chassis_No     = $request->input('chassis_No');
            $method         = 'POST';
            $data = array(
                'vehicleNumber' => $vehicleNo,
                'chassisLast_5_digits' => $chassis_No,
            );
            
            $jsonData = json_encode($data);

            // Request data
            $response = $this->checkHistoryChallanChassis($vehicleNo,$chassis_No, $vendor);
            if(empty($response))
            {
                // Create the headers array with the token
                $headers = array(
                    'clientId:'.$clientId,
                    'secretKey:'.$secretKey,
                    'Content-Type: application/json'
                );

                try {
                    //Make the API request
                // $response = Http::withHeaders($headers)->post($apiUrl, $data);
                    $curl = curl_init();
                    // Set the cURL options
                    curl_setopt($curl, CURLOPT_URL, $apiUrl);
                    curl_setopt($curl, CURLOPT_POST, true);
                    curl_setopt($curl, CURLOPT_POSTFIELDS, $jsonData);
                    curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
                    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);

                    // Execute the cURL request
                    $response = curl_exec($curl);
                    //echo "<pre>";print_r($response);

                    if ($response === false) {
                        $error = curl_error($curl);
                        $error_no = curl_errno($curl);
                        $remark = 'Curl Error';
                        // $return = json_encode(array('code' => $error_no, 'message'=> $error));
						$return = json_encode(array('status_code' => $error_no, 'message'=> $error));
                    } 
                    else 
                    {
                        if(empty($response))
                        {
                            // return json_encode(array('status' => 'No Record Found!'));
							$return = json_encode(array('status_code' => '101', 'message'=> 'No Record Found!'));
                        }
                        //echo "<pre>";print_r($response);die;
                        // API call was successful
                        $responseData = json_decode($response, true);
                        $error = isset($responseData['message']) ? $responseData['message'] : '';
                        $error_no = isset($responseData['code']) ? $responseData['code'] : '';
                        $remark = 'Response from Vendor API';
                        //success
                        if($error_no == 200 || $error_no == 100)
                        {
                            $this->addHistoryChallanChassis($vehicleNo,$chassis_No, $vendor, $jsonData, $response);
                            $this->updateUtilizedCredit($sessionData['Client_id']);
                            // $return = $this->formateDataforinvChallan($responseData);
							$return = json_encode(array('status_code' => $error_no, 'message'=> 'success', 'data' =>$this->formateDataforinvChallan($responseData)));

                        }
                        else{
                            //error
                            // $return = json_encode(array('code' => $error_no, 'message'=> $error));
							$return = json_encode(array('status_code' => $error_no, 'message'=> $error));

                        }
                    }
                } catch (\Exception $e) {
                    return response()->json(['error' => $response], $response);
                }
            }
            else{
                $responseData = json_decode($response, true);
                $error = isset($responseData['message']) ? $responseData['message'] : '';
                $error_no = isset($responseData['code']) ? $responseData['code'] : '';
                $remark = 'Response from History';
                $response_from = 2;
                if($error_no == 200 || $error_no == 100)
                {
                    // $return = ;
					$return = json_encode(array('status_code' => $error_no, 'message'=> 'success', 'data' =>$this->formateDataforinvChallan($responseData)));
                    $this->updateUtilizedCredit($sessionData['Client_id']);
					// $return = json_encode(array('status_code' => $error_no, 'message'=> 'success', $data => ));
                }
                else{
                    // $return = json_encode(array('code' => $error_no, 'message'=> $error));
					$return = json_encode(array('status_code' => $error_no, 'message'=> $error));
                }
            }


            $api_log = new Rcdetails();
            $api_log->api_id = $api_id;
            $api_log->api_name = $api_name;
            $api_log->vender = $vendor;
            $api_log->user_id = $sessionData['userID'];
            $api_log->client_id = $sessionData['Client_id'];
            $api_log->client_name = $sessionData['clientName'];
            $api_log->response_status_code = isset($error_no) ? $error_no : '';
            $api_log->response_message  = isset($error) ? $error : '';
            $api_log->remark  = isset($remark) ? $remark : '';
            $api_log->api_url = $apiUrl;
            $api_log->input = $vehicleNo;
            $api_log->request = $jsonData;
            $api_log->response = $response;
            $api_log->response_from = $response_from;
            $api_log->status = '1';
            $api_log->method = $method;
            $api_log->save();

            // if (!empty($return)) {
            //     // If $return has previous data, add $api_log->id to it
            //     $return['id'] = $api_log->id;
            // }

            return $return;
        }
    }

    public function formateDataforinvChallan($responseData)
    {
        $resArr = [];
        if(!empty($responseData['Error']))
        {
            return $responseData;
        }
        if(!empty($responseData['result']['challanDetails']))
        {
            return $responseData['result']['challanDetails'];
        }
        if(!empty($responseData['result']['challans']))
        {
            foreach($responseData['result']['challans'] as $key => $values)
            {
                foreach($values as $k => $val)
                {
                    $resArr[$key][$this->separateWordsFromCamelCase($k)] = $val;
                }
            
            }
        } 
        return $resArr;
    }
   
   
    public function signzyViewChallan()
    {
        //echo "123"; die;
        //$users = Users::whereIn('status', [0,1,2])->get(); //compact('users')
        return view('challan.schallan');
    }

    
    public function signzyChallanPostData(Request $request)
    {   
        $sessionData = session('data');
        if($this->checkCredit() === false)
        {
            //return response()->json(['status' => 'No more credit limit available']);
            return $result = json_encode(array('code' => 401, 'message'=> 'No more credit limit available'));
        }
        else{
            
            $response_from = 1;
          // echo "<pre>"; print_r($request->input);die;
            $apiUrl = Config::get('custom.signzy.challan.url');
            $Authorization = Config::get('custom.signzy.challan.Authorization');
            $api_id     = Config::get('custom.signzy.challan.api_id');
            $api_name   = Config::get('custom.signzy.challan.api_name');
            $vendor     = Config::get('custom.signzy.challan.vender');
            
            // Get the vehicle number from the request
            $vehicleNo = $request->input('vehicle_No');
            $method = 'POST';

            $data = array(
                'essentials' => array(
                    'vehicleNumber' => $vehicleNo
                ),
                'task' => 'challanSearch'
            );
            
            // Convert data to JSON format
            $jsonData = json_encode($data);
           
            // Create the headers array with the token

            $response = $this->checkHistoryChallan($vehicleNo, $vendor);

            if(empty($response))
            {

                $headers = array(
                    'Authorization:'.$Authorization,
                    'Content-Type:application/json',
                );

                
                    //Make the API request
                    $curl = curl_init();
                    curl_setopt($curl, CURLOPT_URL, $apiUrl);
                    curl_setopt($curl, CURLOPT_POST, true);
                    curl_setopt($curl, CURLOPT_POSTFIELDS, $jsonData);
                    curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
                    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);

                    // Execute the cURL request
                    $response = curl_exec($curl);
                    //echo "<pre>";print_r($headers); echo "<pre>";print_r($response);die;
                  //  $error = '';
                    if ($response === false) {
                        $error = curl_error($curl);
                        $error_no = curl_errno($curl);
                        $remark = 'Curl Error';
                        $return = json_encode(array('code' => $error_no, 'message'=> $error));
                    } 
                    else 
                    {
                        $responseData = json_decode($response, true);

                        //return $responseData;
                        // $error = isset($responseData['message']) ? $responseData['message'] : '';
                        // $error_no = isset($responseData['code']) ? $responseData['code'] : '';
                        $remark = 'Response from API';
                        //success
                        if(!empty($responseData['result']))
                        {   
                            $error_no = 200;
                            $this->addHistoryChallan($vehicleNo, $vendor, $jsonData, $response);
                            $this->updateUtilizedCredit($sessionData['Client_id']);
                            $return = json_encode(array('code' => $error_no, 'message'=> $responseData));
                        }
                        else{
                            //error
                                $error = isset($responseData['error']['message']) ? $responseData['error']['message'] : 'Not Found';
                                $error_no = isset($responseData['error']['status']) ? $responseData['error']['status'] : '101';
                                $return = json_encode(array('code' => $error_no, 'message'=> $error));
                        }   

                        $secondaryVendor = '';
                        $secondary_response = '-';
                        $secondary_status = '';

                        $api_detail_log_id = DB::table('api_detail_log')->insertGetId([
                            'input' => $vehicleNo,
                            'primary_vendor' => $vendor,
                            'primary_response' => $response,
                            'primary_status' => $error_no,
                            'secondary_vendor' => $secondaryVendor,
                            'secondary_response' => $secondary_response,
                            'secondary_status' => $secondary_status,
                            'status' => 1,
                            'created_at' => now(),
                        ]);

                    }

            }
            else{

                $responseData = json_decode($response, true);
                //return $responseData;
                // $error = isset($responseData['message']) ? $responseData['message'] : '';
                // $error_no = isset($responseData['code']) ? $responseData['code'] : '';
                $remark = 'Response from History';
                $api_detail_log_id  = 0;
                $response_from = 2;
                if(!empty($responseData['result']))
                {   
                    $error_no = 200;
                    $return = json_encode(array('code' => $error_no, 'message'=> $responseData));
                    $this->updateUtilizedCredit($sessionData['Client_id']);
                }
                else{

                    $error = isset($responseData['error']['message']) ? $responseData['error']['message'] : '';
                                $error_no = isset($responseData['error']['status']) ? $responseData['error']['status'] : '101';
                                $return = json_encode(array('code' => $error_no, 'message'=> $error));
                }
            } 


                    
                    $api_log =  new Rcdetails();
                    $api_log->api_id = $api_id;
                    $api_log->api_name = $api_name;
                    $api_log->vender = $vendor;
                    $api_log->user_id = $sessionData['userID'];
                    $api_log->client_id = $sessionData['Client_id'];
                    $api_log->client_name = $sessionData['clientName'];
                    $api_log->response_status_code = isset($error_no) ? $error_no : '101';
                    $api_log->response_message  = isset($error) ? $error : '';
                    $api_log->remark  = isset($remark) ? $remark : '';
                    $api_log->api_url = $apiUrl;
                    $api_log->request = $jsonData;
                    $api_log->input = $vehicleNo;
                    $api_log->api_detail_log_id = $api_detail_log_id;
                    $api_log->response = $response;
                    $api_log->response_from = $response_from;
                    $api_log->status = '1';
                    $api_log->method = $method;
                    $api_log->save();


                    $returnArr['api_log_id'] = $api_log->id;
                    $returnArr['return'] = json_decode($return, true);

                    return $returnArr;
        }
    }
    

    public function retrieveChallanRtoData(Request $request ){
        $sessionData = session('data');
        if($this->checkCredit() === false)
        {
            return $result = json_encode(array('code' => 401, 'message'=> 'No more credit limit available'));
        }
        else{

            $response_from =    1;
            $apiUrl =           Config::get('custom.rto.challan.url');
            $user_id =          Config::get('custom.rto.challan.user_id');
            $Authorization =    Config::get('custom.rto.challan.Authorization');
            $api_id     =       Config::get('custom.rto.challan.api_id');
            $api_name   =       Config::get('custom.rto.challan.api_name');
            $vendor     =       Config::get('custom.rto.challan.vender');
            
            // Get the vehicle number from the request
            $vehicleNo = $request->input('vehicle_No');
            $method = 'POST';
            // Request data
            $data = [
                'vehicle_number' => $vehicleNo,
                'user_id' => $user_id
            ];

            $jsonData = json_encode($data);

            $response = $this->checkHistoryChallan($vehicleNo, $vendor);

            if(empty($response))
            {
               
                $headers = array(
                    'Authorization:'.$Authorization,
                    'Content-Type:application/json',
                );
                $curl = curl_init();
                curl_setopt($curl, CURLOPT_URL, $apiUrl);
                curl_setopt($curl, CURLOPT_POST, true);
                curl_setopt($curl, CURLOPT_POSTFIELDS, $jsonData);
                curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
                curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
                
                $response = curl_exec($curl);
                $requestHeaders = implode("\r\n", $headers);

                if ($response === false) {
                    $error = curl_error($curl);
                    $error_no = curl_errno($curl);
                    $remark = 'Curl Error';
                    $return = json_encode(array('code' => $error_no, 'message'=> $error));
                } 
                else 
                {
                    $responseData = json_decode($response, true);

                    $error = isset($responseData['message']) ? $responseData['message'] : '';
                    $error_no = isset($responseData['statusCode']) ? $responseData['statusCode'] : '';
                    $remark = 'Response from Vendor API';

                    if($error_no == 200)
                    {
                        $this->addHistoryChallan($vehicleNo, $vendor, $jsonData, $response);
                        $this->updateUtilizedCredit($sessionData['Client_id']);
                        $return = json_encode(array('code' => $error_no, 'message'=> $responseData));

                    }
                    else{
                        $error = isset($responseData['message']) ? $responseData['message'] : '';
                        $error_no = isset($responseData['statusCode']) ? $responseData['statusCode'] : '';
                        $return = json_encode(array('code' => $error_no, 'message'=> $error));
                    }
                }

            }
            else{
                $responseData = json_decode($response, true);
                $error = isset($responseData['message']) ? $responseData['message'] : '';
                $error_no = isset($responseData['statusCode']) ? $responseData['statusCode'] : '';
                $remark = 'Response from History';
                $response_from = 2;
                if($error_no == 200)
                {
                    $return = json_encode(array('code' => $error_no, 'message'=> $responseData));
                    $this->updateUtilizedCredit($sessionData['Client_id']);
                }
                else{
                    $error = isset($responseData['message']) ? $responseData['message'] : '';
                    $error_no = isset($responseData['statusCode']) ? $responseData['statusCode'] : '';
                    $return = json_encode(array('code' => $error_no, 'message'=> $error));
                }
            }

            $api_log =  new Rcdetails();
            $api_log->api_id = $api_id;
            $api_log->api_name = $api_name;
            $api_log->vender = $vendor;
            $api_log->user_id = $sessionData['userID'];
            $api_log->client_id = $sessionData['Client_id'];
            $api_log->client_name = $sessionData['clientName'];
            $api_log->response_status_code = isset($error_no) ? $error_no : '';
            $api_log->response_message  = isset($error) ? $error : '';
            $api_log->remark  = isset($remark) ? $remark : '';
            $api_log->api_url = $apiUrl;
            $api_log->request = $jsonData;
            $api_log->input = $vehicleNo;
            $api_log->response = $response;
            $api_log->response_from = $response_from;
            $api_log->status = '1';
            $api_log->method = $method;
            $api_log->save();

            return $return;
        }
    }
   
   
    public function retrieveChallanData(Request $request)
    {   
        // $data = 'gaurav';
        // return $data;

        $sessionData = session('data');
    $defaultStatusCode = 101;        
    $clientid   = $sessionData['Client_id'];        
    $userID     = $sessionData['userID'];        
    $clientName = $sessionData['clientName'];
    $moduleName = 'challan';
    $custom_log = Log::channel('custom');
    //$custom_log->setPath(str_replace(['%clientName%', '%moduleName%'], [$clientName, $moduleName], $custom_log->getPath()));
    // $custom_log = Log::channel('custom_log');
    $custom_log->debug("\n\n\n---------Start process here for Client : ".$clientName." & Module ".$moduleName." ---------\n");

    $vehicleNo              = strtoupper($request->input('vehicle_No'));
    $vehicleNo              = $this->filterVehicleNumber($vehicleNo);
    $isValidVehicleNumber   = $this->validateVehicleNumber($vehicleNo);
    $custom_log->debug(__LINE__." ----isValidVehicleNumber ---- : ".$isValidVehicleNumber);
    if ($isValidVehicleNumber === false) {
        $statusCode         = $defaultStatusCode; 
        $response_message   = 'Vehicle number is not valid';
        //$response   = json_encode(['vehicleNo' => $vehicleNo, 'status_code' => $statusCode, 'message' => $response_message]);
        return $response = json_encode(['vendor' => 'NA', 'statusCode' => $statusCode, 'response_message' => $response_message]);  
    }
    else{
        //$primaryVendor = 'rto';
        $primaryVendor = Module::where('client_id', $clientid)
            ->where('apiname', 'challan')
            ->value('vendorname');
        $secondaryVendor = Module::where('client_id', $clientid)
            ->where('apiname', 'challan')
            ->value('sec_vendor');

        $apiResult = DB::table('api_master')
        ->select('api_master.id', 'api_master.vender')
        ->whereIn('api_master.vender', [$primaryVendor, $secondaryVendor])
        ->where('api_master.api_name', 'challan')
        ->get();
        // echo "primaryVendor".$primaryVendor;die;
        foreach ($apiResult as $result) {
            $appidArr[strtolower($result->vender)] = $result->id;
        }
        $vendor         = $primaryVendor;
        $response       = '';
        $response_from  = 1;
        $response_type  = 0;
        $url            = '';
        $statusCode     = 0;
        $requestData    = '';
        $response_message = '';
        $remark         = 'Response from History';
        $primary_response   = '';
        $secondary_response = '';
        $primary_status     = 0;
        $secondary_status   = 0;
        $api_detail_log_id  = 0;

        $custom_log->debug("\n\n".__LINE__." :  -------------- Process Start for Vehicle No (".$vehicleNo.") ---------------\n");        
        $isCreditAvaialbe = $this->checkCredit($clientid);
        $custom_log->debug(__LINE__." ----checkCredit ---- : ".$isCreditAvaialbe);                  
        if($isCreditAvaialbe === false)
        {
            $statusCode         = $defaultStatusCode;
            $response_message   = 'You do not have enough credit to perform this action';
            return $response = json_encode(['vendor' => $vendor, 'statusCode' => $statusCode, 'response_message' => $response_message]);                        
        }
        else{
            
            $response = [];
            $responseHistrory = $this->checkHistoryChallanForApiList([$primaryVendor,$secondaryVendor], $vehicleNo);
            if(isset($responseHistrory) && empty($responseHistrory)) 
            {
                
                $custom_log->debug(__LINE__." --- API Hits for Primary Vendor --- ".$primaryVendor);
                $response_from  = 1; //0,1 => Vendor's API, 2=> History
                $response_type  = 1; //0=> History, 1 => Primary, 2 => Secondary
                $vendor         = $primaryVendor; 
                $remark         = 'Response from Primary Vendor API';
                // Primary Vendor
                $responseArr        = $this->apiList($primaryVendor, $vehicleNo);
            //     echo "0<pre>";
            //    return $responseArr;
            //     exit;
                $primary_response   = $responseArr['response'];
                $primary_status     = $responseArr['status_code'];
                // echo "<pre> primary status : "; print_r($responseArr['status']);
                if((isset($responseArr['status']) && $responseArr['status'] != 'success' || $responseArr['status_code'] != 200))
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
                        // echo "<pre>";
                        // print_r($responseArr);
                        // exit;
                        $secondary_response     = $responseArr['response'];
                        $responseData = json_decode($responseArr['response'] , true); 
                        $status = isset($responseData['statusCode']) ? $responseData['statusCode']  : $responseData['status'] ;
                        $secondary_status    = $status ;
                        // echo $secondary_response,$secondary_status; exit;
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
                $response_from  = 2;                   
                $response       = $responseHistrory['response'];
                $vendor         = $responseHistrory['vendor'];
                $statusCode     = 200;
                $url            = '';
                $requestData    = $vehicleNo;
                $response_message= 'success';
                $custom_log->debug(__LINE__." --- Pulled from History  --- ");
            }
        }
        // echo "<pre> sdfsdfsdf : "; print_r($response);die;  
        $updatedID = $this->updateUtilizedCredit($clientid);
        $custom_log->debug(__LINE__." --- Update clients for Credit with updated ID --- ". $updatedID);
            
    
        //Details Log need to be incorporate
        $api_log =  new Rcdetails();
        $api_log->api_id = $appidArr[strtolower($vendor)];
        $api_log->api_name = 'challan';
        $api_log->vender = $vendor;
        $api_log->user_id = $userID;
        $api_log->client_id = $clientid;
        $api_log->client_name = $clientName;
        $api_log->response_status_code = $statusCode;
        $api_log->response_message  = $response_message;
        $api_log->remark  = $remark;
        $api_log->api_url = $url;
        $api_log->input  = $vehicleNo ;
        $api_log->request  = $requestData ;
        $api_log->response = $response;
        $api_log->request_type = 1;
        $api_log->response_from = $response_from;
        $api_log->response_type = $response_type;
        $api_log->api_detail_log_id = $api_detail_log_id;
        $api_log->status = '1';
        $api_log->method = 'POST';
        $api_log->save();

        $returnArr['statusCode'] = $statusCode;
        $returnArr['response_message'] = $response_message;
        $returnArr['vendor'] = $vendor;
        $returnArr['response'] = json_decode($response, true);
        //$statusCode."#~#".$response_message."#~#".$vendor."#~#".
        $custom_log->debug(__LINE__." --- Add APILOG Table  for ID --- ". $api_log->id);
        return json_encode($returnArr);
    }
    }

        protected function apiList($vendor, $vehicleNo)
        {
            // echo $vendor;
            // exit;
            $response = array();
            switch (strtolower($vendor)) {
                case "authbridge":
                    $response = $this->challanAuthbridge($vendor, $vehicleNo);
                    break;
                case "rto":
                    $response = $this->challanRto($vendor, $vehicleNo);
                    break;
                default:
                    $response = array('status'=>'failed','status_code'=>'101', 'msg'=>'Invelid vendor', 'data'=>[]);
            }
            return $response;
        }
    
    
    public function authbridgeViewChallan(){
        return view('challan.challan_auth');
    }

    public function authbridgeChallanPostData(Request $request)
    {       
        $sessionData = session('data');
        if($this->checkCredit() === false)
        {
            return response()->json(['status' => 'No more credit limit available']);
        }
        else{
        // return $request;
            $response_from          = 1;
            $encrypted_string_url   = Config::get('custom.authbridge.challan.encrypted_string_url');
            $utilitysearch_url      = Config::get('custom.authbridge.challan.utilitysearch_url');
            $decrypt_encrypted_string_url      = Config::get('custom.authbridge.challan.decrypt_encrypted_string_url');
            $username               = Config::get('custom.authbridge.challan.username');
            $api_id                 = Config::get('custom.authbridge.challan.api_id');
            $api_name               = Config::get('custom.authbridge.challan.api_name');
            $vendor                 = Config::get('custom.authbridge.challan.vender');
            $doc_type               = Config::get('custom.authbridge.challan.doc_type');
            // Get the vehicle number from the request
            $method = 'POST';
            $vehicleNo = $request->input('vehicleNo'); 
            $vehicleNo = $this->filterVehicleNumber($vehicleNo);
            $isValidVehicleNumber = $this->validateVehicleNumber($vehicleNo);
            if ($isValidVehicleNumber === false) {
                return response()->json(['status' => 'Please enter valid vehicle number']);
            }
            $response = "";
            $dataStep1 = [
                'docNumber' => $vehicleNo,
                'transID' => '123456',
                'docType' => $doc_type
            ];

            $jsonDataStep1 = json_encode($dataStep1);
            //-------------------------Start Check History-------------------------------------
            $response = $this->checkHistoryChallan($vehicleNo, $vendor);
            
            if(empty($response))
            {
                //------------------------- Step1-------------------------------------
                

                $headers = array(
                    'username:'.$username,
                    'Content-Type: application/json'
                );

                $curlStep1 = curl_init();
                curl_setopt($curlStep1, CURLOPT_URL, $encrypted_string_url);
                curl_setopt($curlStep1, CURLOPT_POST, true);
                curl_setopt($curlStep1, CURLOPT_POSTFIELDS, $jsonDataStep1);
                curl_setopt($curlStep1, CURLOPT_HTTPHEADER, $headers);
                curl_setopt($curlStep1, CURLOPT_RETURNTRANSFER, true);
                $responseStep1 = curl_exec($curlStep1);
                if ($responseStep1 === false) {
                    $message = curl_error($curlStep1);
                    $statusCode = curl_errno($curlStep1);
                    $remark = 'Curl Error';
                    $return = json_encode(array('status_code' => $statusCode, 'message'=> $message));
                }
                else
                { 
                    
                // ------------------------- Step2-------------------------------------
                    $dataStep2 = [
                        'requestData' => $responseStep1,
                    ];

                    $jsonDataStep2 = json_encode($dataStep2);

                    $headers = array(
                        'username:'.$username,
                        'Content-Type: application/json'
                    );

                    $curlStep2 = curl_init();
                    curl_setopt($curlStep2, CURLOPT_URL, $utilitysearch_url);
                    curl_setopt($curlStep2, CURLOPT_POST, true);
                    curl_setopt($curlStep2, CURLOPT_POSTFIELDS, $jsonDataStep2);
                    curl_setopt($curlStep2, CURLOPT_HTTPHEADER, $headers);
                    curl_setopt($curlStep2, CURLOPT_RETURNTRANSFER, true);
                    $responseStep2 = curl_exec($curlStep2);

                    if ($responseStep2 === false) {
                        $message = curl_error($curlStep1);
                        $statusCode = curl_errno($curlStep1);
                        $remark = 'Curl Error';
                        $return = json_encode(array('status_code' => $statusCode, 'message'=> $message));
                    }
                    else
                    {
                        $headers = array(
                            'username:'.$username,
                            'Content-Type: application/json'
                        );

                        $curl = curl_init();
                        curl_setopt($curl, CURLOPT_URL, $decrypt_encrypted_string_url);
                        curl_setopt($curl, CURLOPT_POST, true);
                        curl_setopt($curl, CURLOPT_POSTFIELDS, $responseStep2);
                        curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
                        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
                        $response = curl_exec($curl);
                        // echo $apiUrl; die;
                    
                        if ($response === false) {
                            $message = curl_error($curl);
                            $statusCode = curl_errno($curl);
                            $remark = 'Curl Error';
                            $return = json_encode(array('status_code' => $statusCode, 'message'=> $message));
                        } 
                        else 
                        {
                            $responseData = json_decode($response, true);
                            //echo "<pre> "; print_r($responseData);die;
                            $message = isset($responseData['msg']) ? $responseData['msg'] : '';
                            $statusCode = isset($responseData['status']) ? $responseData['status'] : '';
                            $remark = 'Response from Vendor API';
                            //success
                            if($statusCode == 200 || $statusCode == 1 && (!empty($responseData['data']) && is_array($responseData['data']) === true))
                            {
                                $statusCode = 200;
                                $this->updateUtilizedCredit($sessionData['Client_id']);
                                $this->addHistoryChallan($vehicleNo, $vendor, $jsonDataStep1, $response);
                                $return = json_encode(array('status_code' => $statusCode, 'message'=> $responseData));
                            }
                            else{
                                //error
                                $return = json_encode(array('status_code' => $statusCode, 'message'=> $message));
                            }
                            //echo "<pre>"; print_r($responseData);die;
                        }

                    }
                }
            }
            else{
                $responseData = json_decode($response, true);
                //echo "<pre> "; print_r($responseData);die;
                $message = isset($responseData['msg']) ? $responseData['msg'] : '';
                $statusCode = isset($responseData['status']) ? $responseData['status'] : '';
                $remark = 'Response from History';
                $response_from = 2;
                //success
                if($statusCode == 200 || $statusCode == 1)
                {
                    $statusCode = 200;
                    $this->updateUtilizedCredit($sessionData['Client_id']);
                    $return = json_encode(array('status_code' => $statusCode, 'message'=> $responseData));
                }
                else{
                    //error
                    $return = json_encode(array('status_code' => $statusCode, 'message'=> $message));
                }

            }
            //echo "<pre>"; print_r($response);die;
           

            $api_log =  new Rcdetails();
            $api_log->api_id = $api_id;
            $api_log->api_name = $api_name;
            $api_log->vender = $vendor;
            $api_log->user_id = $sessionData['userID'];
            $api_log->client_id = $sessionData['Client_id'];
            $api_log->client_name = $sessionData['clientName'];
            $api_log->response_status_code = isset($statusCode) ? $statusCode : '';
            $api_log->response_message  = isset($message) ? $message : '';
            $api_log->remark  = isset($remark) ? $remark : '';
            $api_log->api_url = $decrypt_encrypted_string_url;
            $api_log->request = $jsonDataStep1;
            $api_log->input = $vehicleNo;
            $api_log->response = $response;
            $api_log->response_from = $response_from;
            $api_log->status = '1';
            $api_log->method = $method;
            $api_log->save();

            // echo "<pre>"; print_r($api_log);die;
            return $return;

        }
    }


    // Helper function to separate camel case words into multiple words
    function separateWordsFromCamelCase($inputString)
    {
        $pattern = '/(?<=[a-z])(?=[A-Z])/';  // Pattern to match the position between lowercase and uppercase letters
        $words = preg_split($pattern, $inputString);
        $words = array_map('ucfirst', $words);
        $result = implode(' ', $words);
        return $result;
    }

  

    public function getCurrentControllerName()
    {
        $controllerName = class_basename(__CLASS__);
        return Str::replaceLast('Controller', '', $controllerName);
    }


    private function checkHistoryChallan($vehicleNo, $vendor)
    {
        $returnArr = '';
       // $sevenDaysAgo = Carbon::now()->subDays(7)->toDateString();
        $result = DB::select("SELECT id, response FROM `history_challan` WHERE vehicle_no = '$vehicleNo' AND vendor = '$vendor' AND `status` IN (0,1) AND created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY) ORDER BY id DESC LIMIT 1");
        if (!empty($result)) {
           
            $returnArr = $result[0]->response;
        }
        else{
            DB::table('history_challan')
            ->where('vehicle_no', $vehicleNo)
            ->where('vendor', $vendor)
            ->whereIn('status', [0, 1])
            ->delete();
            $returnArr = '';
        }
        return $returnArr;
    }

    private function addHistoryChallan($vehicleNo, $vendor, $request, $response)
    {
        $createdAt = now();
        return DB::table('history_challan')->insert([
            'vehicle_no' => $vehicleNo,
            'vendor' => $vendor,
            'request' => $request,
            'response' => $response,
            'status' => 1,
            'created_at' => $createdAt,
        ]);
    }

    private function checkHistoryChallanChassis($vehicleNo,$chassis_No,$vendor)
    {
        $returnArr = '';
       // $sevenDaysAgo = Carbon::now()->subDays(7)->toDateString();
        $result = DB::select("SELECT id, response FROM `history_challan_chassis` WHERE vehicle_no = '$vehicleNo' AND vendor = '$vendor' AND chassis_no= '$chassis_No' AND `status` IN (0,1) AND created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY) ORDER BY id DESC LIMIT 1");
        if (!empty($result)) {
           
            $returnArr = $result[0]->response;
        }
        else{
            $returnArr = '';
        }
        return $returnArr;
    }

    private function addHistoryChallanChassis($vehicleNo,$chassis_No, $vendor, $request, $response)
    {
        $createdAt = now();
        return DB::table('history_challan_chassis')->insert([
            'vehicle_no' => $vehicleNo,
            'vendor' => $vendor,
            'chassis_no' =>$chassis_No,
            'request' => $request,
            'response' => $response,
            'status' => 1,
            'created_at' => $createdAt,
        ]);
    }

    public function Challanpdf(Request $request){

        $id = $request->input('id'); 
        $sessionData = session('data');     
        $clientid   = $sessionData['Client_id'];    

        $apiResult = DB::table('api_log')
		->select('api_log.id', 'api_log.vender', 'api_log.response')
		->where('api_log.id', $id)
		->where('api_log.client_id', $clientid)
		->first();  


        $responseData = json_decode($apiResult->response, true);
		// $responseData = $this->standardRcResponse('signzy',$responseData); 
		$responseData = $this->standardChallanResponse($apiResult->vender,$responseData); 

        // echo "<pre>"; return $responseData;exit;
        $url = request()->root();
		$parsedUrl = parse_url($url);
		$baseUrl = $parsedUrl['scheme'] . '://' . $parsedUrl['host'] . $parsedUrl['path'];

        $headerImagePath = $baseUrl.'/assets/img/edas-logo-light.png';
		
        // Load the view with data and generate PDF content
        $pdf = PDF::loadView('pdf.rcchallan_pdf', ['data' => $responseData]);
        // $pdf->setOptions([
        //     'header-spacing' => 10, // Adjust spacing as needed
        //     'callbacks' => [
        //         'header' => function ($pdf) use ($headerImagePath) {
        //             $pdf->image(public_path($headerImagePath), 10, 10, 30);
        //             // $pdf->image(public_path($baseUrl.'/assets/img/edas-logo-light.png'), 10, 10, 30); 
        //         },
        //     ],
        // ]);

        // Generate a unique filename for the PDF
        $filename = 'challan_details' . time() . '.pdf';
        // Save the PDF to a storage directory (e.g., storage/app/pdf)
        $pdf->save(storage_path('app/pdf/' . $filename));
		
		$url = request()->root();
		$parsedUrl = parse_url($url);
		$baseUrl = $parsedUrl['scheme'] . '://' . $parsedUrl['host'] . $parsedUrl['path'];
        $filePath = storage_path("app/pdf/$filename");
		$file_url = $baseUrl . "/storage/app/pdf/" . $filename;
		chmod($filePath, 0755);

        // Provide the option to download the PDF
		return response()->json(['download' => '1', 'file_url' => $file_url, 'file_name' => $filename], 200);
		
        // echo"<pre>";print_r( $pdfresponse);exit;
    }

    public function standardChallanResponse($vendor, $response)
	{
        switch (strtolower($vendor)) {
            case "invincible":
                $response = $this->challanIncResponsePDF($response);
                break;
            case "signzy":
                $response = $this->challanSignzyResponsePDF($response);
                break;
        }
        return $response;
	}

    public function challanIncResponsePDF($response){


    }

    public function challanSignzyResponsePDF($response){

        $returnArr = [];

        if (isset($response['result']['challanDetails']) && is_array($response['result']['challanDetails'])) {
            $challanDetails = $response['result']['challanDetails'];
    
            foreach ($challanDetails as $challan) {
                $data = [
                    'accusedName'    => isset($challan['accusedName']) ? $challan['accusedName'] : null,
                    'amount'         => isset($challan['amount']) ? $challan['amount'] : null,
                    'challanDate'    => isset($challan['challanDate']) ? $challan['challanDate'] : null,
                    'challanNumber'  => isset($challan['challanNumber']) ? $challan['challanNumber'] : null,
                    'challanPlace'   => isset($challan['challanPlace']) ? $challan['challanPlace'] : null,
                    'challanStatus'  => isset($challan['challanStatus']) ? $challan['challanStatus'] : null,
                    'court_challan'  => isset($challan['court_challan']) ? $challan['court_challan'] : null,
                    'offenseDetails' => isset($challan['offenseDetails']) ? $challan['offenseDetails'] : null,
                    'rto'            => isset($challan['rto']) ? $challan['rto'] : null,
                    'state'          => isset($challan['state']) ? $challan['state'] : null,
                ];
    
                $returnArr[] = $data;
            }
        }
    
        return $returnArr;


    }






}
