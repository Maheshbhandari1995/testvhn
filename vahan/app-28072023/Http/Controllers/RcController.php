<?php

namespace App\Http\Controllers;
use App\Models\Company;
use App\Models\Rcdetails;
use App\Models\Module;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use App\Traits\CommonTraits;

class RcController extends Controller
{
    use CommonTraits;



    public function init(Request $request)
    {
        $sessionData    = session('data');
        $client_id      = $sessionData['Client_id'];
        if($this->checkCredit() === false)
        {
            return response()->json(['status' => 'No more credit limit available']);
        }
        else{
            $vehicleNo      = $request->input('vehicleNo'); 
            $vehicleNo      = $this->filterVehicleNumber($vehicleNo);
            if ($this->validateVehicleNumber($vehicleNo) === false) {
                return response()->json(['status' => 'Please enter valid vehicle number']);
            }
            else{
                $moduleData = $this->getModuleData($client_id);
                $vendor     = $moduleData['vendorname'];
                //For Primary API
                $response   = $this->apiList($vendor, $vehicleNo);

            }
        }
    }

    public function getModuleData($client_id, $module = 'rc')
    {
        $returnArr = '';
        $result = DB::select("SELECT vendorname, sec_vendor FROM `api_list` WHERE client_id = '$client_id' AND apiname = '$module' AND `status` IN (0,1) AND del_status = 1");
        if (!empty($result)) {
            
            $returnArr = $result[0];
        }
        return $returnArr;
    }
    

    protected function apiList($vendor, $vehicleNo)
    {
        switch (strtolower($vendor)) {
            case "authbridge":
                $this->rcAuthbridge($vehicleNo);
                break;
            case "signzy":
                $this->rcSignzy($vehicleNo);
                break;
            case "invincible":
                $this->rcIncincible($vehicleNo);
                break;
            case "rto":
                $this->rcRTO($vehicleNo);
                break;
            case "edas_internal":
                $this->rcEdasInternal($vehicleNo);
                break;
            default:
                echo "!";
        }
    }

    public function invincibleViewRCWithChassis(){
        return view('rc.rc_chassis');
    }

    public function invincibleRCWithChassisPostData(Request $request)
    {       
        $sessionData = session('data');
        if($this->checkCredit() === false)
        {
            return response()->json(['status' => 'No more credit limit available']);
        }
        else{
        // return $request;
        
            $response_from = 1;
            $apiUrl     = Config::get('custom.invincible.rc_chassis.url');
            $clientId   = Config::get('custom.invincible.rc_chassis.clientId');
            $secretKey  = Config::get('custom.invincible.rc_chassis.secretKey');
            $api_id     = Config::get('custom.invincible.rc_chassis.api_id');
            $api_name   = Config::get('custom.invincible.rc_chassis.api_name');
            $vendor     = Config::get('custom.invincible.rc_chassis.vender');
            // Get the vehicle number from the request
            $method = 'POST';
            $chassisNumber = $request->input('chassisNumber'); 
        
            $isValidchassisNumber = $this->validateChassisNumber($chassisNumber);
            if ($isValidchassisNumber === false) {
                return response()->json(['status' => 'Please enter valid chassis number']);
            }
            
            // Request data
            $data = [
                'chassisNumber' => $chassisNumber
            ];
            $jsonData = json_encode($data);
            
            //-------------------------Start Check History-------------------------------------
            $response = $this->checkHistoryRCWithChassis($chassisNumber, $vendor);
            
            if(empty($response))
            {
                $headers = array(
                    'clientId:'.$clientId,
                    'secretKey:'.$secretKey,
                    'Content-Type: application/json'
                );

                $curl = curl_init();
                curl_setopt($curl, CURLOPT_URL, $apiUrl);
                curl_setopt($curl, CURLOPT_POST, true);
                curl_setopt($curl, CURLOPT_POSTFIELDS, $jsonData);
                curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
                curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
                $response = curl_exec($curl);
                // echo $apiUrl; die;
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
                    $error_no = isset($responseData['code']) ? $responseData['code'] : '';
                    $remark = 'Response from Vendor API';
                    //success
                    if($error_no == 200)
                    {
                        $this->updateUtilizedCredit($sessionData['Client_id']);
                        $this->addHistoryRCWithChassis($chassisNumber, $vendor, $jsonData, $response);
                        $return = $responseData;
                    }
                    else{
                        //error
                        $return = json_encode(array('code' => $error_no, 'message'=> $error));
                    }
                    //echo "<pre>"; print_r($responseData);die;
                }
            }
            else{

                $responseData = (!empty($response)) ? json_decode($response, true) : '';
                $error = isset($responseData['message']) ? $responseData['message'] : '';
                $error_no = isset($responseData['code']) ? $responseData['code'] : '';
                $remark = 'Response from History';
                $response_from = 2;
                //success
                if($error_no == 200)
                {
                    $this->updateUtilizedCredit($sessionData['Client_id']);
                    $return = $responseData;
                }
                else{
                    //error
                    $return = json_encode(array('code' => $error_no, 'message'=> $error));
                }
                //echo "<pre>"; print_r($responseData);die;
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
            $api_log->response = $response;
            $api_log->response_from = $response_from;
            $api_log->status = '1';
            $api_log->method = $method;
            $api_log->save();

            
            return $return;
        }
    }


    public function authbridgeViewRC(){
        return view('rc.rc_auth');
    }

    public function authbridgeRCPostData(Request $request)
    {       
        $sessionData = session('data');
        if($this->checkCredit() === false)
        {
            return response()->json(['status' => 'No more credit limit available']);
        }
        else{
        // return $request;
            $response_from = 1;
            $encrypted_string_url   = Config::get('custom.authbridge.rc.encrypted_string_url');
            $utilitysearch_url      = Config::get('custom.authbridge.rc.utilitysearch_url');
            $decrypt_encrypted_string_url      = Config::get('custom.authbridge.rc.decrypt_encrypted_string_url');
            $username               = Config::get('custom.authbridge.rc.username');
            $api_id                 = Config::get('custom.authbridge.rc.api_id');
            $api_name               = Config::get('custom.authbridge.rc.api_name');
            $vendor                 = Config::get('custom.authbridge.rc.vender');
            // Get the vehicle number from the request
            $method = 'POST';
            $vehicleNo = $request->input('vehicleNo'); 
            $vehicleNo = $this->filterVehicleNumber($vehicleNo);
            $isValidVehicleNumber = $this->validateVehicleNumber($vehicleNo);
            if ($isValidVehicleNumber === false) {
                return response()->json(['status' => 'Please enter valid vehicle number']);
            }
            $response = "";
            //-------------------------Start Check History-------------------------------------
            $response = $this->checkHistoryRC($vehicleNo, $vendor);
            $dataStep1 = [
                'docNumber' => $vehicleNo,
                'transID' => '1234567',
                'docType' => '372'
            ];

            $jsonDataStep1 = json_encode($dataStep1);
            
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
                            $message = isset($responseData['message']) ? $responseData['message'] : '';
                            $statusCode = isset($responseData['status_code']) ? $responseData['status_code'] : '';
                            $remark = 'Response from Vendor API';
                            //success
                            if($statusCode == 200)
                            {
                                $this->addHistoryRC($vehicleNo, $vendor, $jsonDataStep1, $response);
                                $return = $responseData;
                                $this->updateUtilizedCredit($sessionData['Client_id']);
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
                $message = isset($responseData['message']) ? $responseData['message'] : '';
                $statusCode = isset($responseData['status_code']) ? $responseData['status_code'] : '';
                $remark = 'Response from History';
                $response_from = 2;
                //success
                if($statusCode == 200)
                {
                    $return = $responseData;
                    $this->updateUtilizedCredit($sessionData['Client_id']);
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
            $api_log->response = $response;
            $api_log->response_from = $response_from;
            $api_log->status = '1';
            $api_log->method = $method;
            $api_log->save();

            // echo "<pre>"; print_r($api_log);die;
            return $return;

        }
    }

    public function rcSignzy($vehicleNo)
    { 
            $url            = Config::get('custom.signzy.rc.url');
            $Authorization  = Config::get('custom.signzy.rc.Authorization');
            $api_id         = Config::get('custom.signzy.rc.api_id');
            $api_name       = Config::get('custom.signzy.rc.api_name');
            $vendor         = Config::get('custom.signzy.rc.vender');
            $method         = 'POST';
            $response       = "";
            $response_from  = 1;
            $data = [
                'essentials' => ['vehicleNumber' => $vehicleNo],
                'task' => 'detailedSearch'
            ];

            $jsonData = json_encode($data);
            
            //-------------------------Start Check History-------------------------------------
            $response = $this->checkHistoryRC($vehicleNo, $vendor);
            if(empty($response))
            {
                $headers = array(
                    'Authorization:'.$Authorization,
                    'Content-Type: application/json'
                );

                $curl = curl_init();
                curl_setopt($curl, CURLOPT_URL, $url);
                curl_setopt($curl, CURLOPT_POST, true);
                curl_setopt($curl, CURLOPT_POSTFIELDS, $jsonData);
                curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
                curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
                $response = curl_exec($curl);
                
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
                    $message = isset($responseData['message']) ? $responseData['message'] : '';
                    $statusCode = isset($responseData['status_code']) ? $responseData['status_code'] : '';
                    $remark = 'Response from Vendor API';
                    //success
                    if($statusCode == 200 || !empty($responseData['result']))
                    {
                        $this->updateUtilizedCredit($sessionData['Client_id']);
                        $statusCode = 200;
                        $this->addHistoryRC($vehicleNo, $vendor, $jsonData, $response);
                        $return = json_encode(array('status_code' => $statusCode, 'message'=> $responseData['result']));
                    }
                    else{
                        $return = json_encode(array('status_code' => $statusCode, 'message'=> $message));
                    }
                }
            }
            else{
                $responseData = (!empty($response)) ? json_decode($response, true) : '';
                //echo "<pre> "; print_r($responseData['result']);die;
                $message = isset($responseData['message']) ? $responseData['message'] : '';
                $statusCode = isset($responseData['status_code']) ? $responseData['status_code'] : '';
                $remark = 'Response from History';
                $response_from = 2;
                //success
                if($statusCode == 200 || !empty($responseData['result']))
                {
                    $this->updateUtilizedCredit($sessionData['Client_id']);
                    $statusCode = 200;
                    $return = json_encode(array('status_code' => $statusCode, 'message'=> $responseData['result']));
                }
                else{
                    //error
                    $return = json_encode(array('status_code' => $statusCode, 'message'=> $message));
                }
            }
            
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
            $api_log->api_url = $url;
            $api_log->request = $jsonData;
            $api_log->response = $response;
            $api_log->response_from = $response_from;
            $api_log->status = '1';
            $api_log->method = $method;
            $api_log->save();
            //echo "<pre>"; print_r($return);die;
            return $return;
        }
    }

    // public function retrieveVehicleData(Request $request)
    // {       
    //     if($this->checkCredit() === false)
    //     {
    //         return response()->json(['status' => 'No more credit limit available']);
    //     }
    //     else{
    //     // return $request;
    //         $apiUrl = Config::get('custom.rc_authbridge.rc_authbridge_url');
    //         $token = Config::get('custom.rc_authbridge.rc_authbridge_token');
    //         // Get the vehicle number from the request
    //         $method = 'POST';
    //         $vehicleNo = $request->input('vehicleNo'); 
        
    //         $isValidVehicleNumber = $this->validateVehicleNumber($vehicleNo);
    //         if ($isValidVehicleNumber === false) {
    //             return response()->json(['status' => 'Please enter valid vehicle number']);
    //         }
        
    //         // Request data
    //         $data = [
    //             'vehicle_No' => $vehicleNo
    //         ];
    //         // echo "<pre>"; print_r($data);die;
        
    //         // Make the API request using Laravel's HTTP client
    //         $response = Http::withHeaders([
    //             'token' => $token,
    //             'Content-Type' => 'application/json'
    //         ])->post($apiUrl, $data);
                
    //         // echo $apiUrl; die;
    //         if ($response->failed()) {
    //             // Handle the error appropriately
    //             return response()->json(['error' => $response->body()]);
    //         }
        
    //         $responseData = $response->json();
        
    //         if (empty($responseData)) {

    //             return response()->json(['status' => 'No Record Found!']);
    //         }

    //         //save the vehicle data in Rcdetails(api_list)
    //         // $api_details = Module::where('del_status', 1)->pluck('company','vendorname','apiurl','apiname', 'id');
    //         $sessionData = session('data');
    //         // echo "<pre>"; print_r($sessionData);die;
    //         $data = json_decode($response->body(),true);
    //         $api_log =  new Rcdetails();
    //         $api_name = 'license';
    //         $vendorname = 'sinzy';
    //         // $api_log->api_id = $sessionData['userID'];
    //         $api_log->api_name = $api_name;
    //         $api_log->vender = $vendorname;
    //         $api_log->user_id = $sessionData['userID'];
    //         $api_log->client_id = $sessionData['Client_id'];
    //         $api_log->client_name = $sessionData['clientName'];
    //         $api_log->response_status_code = $data['status_code'];
    //         $api_log->api_url = $apiUrl;
    //         $api_log->request = $vehicleNo;
    //         $api_log->response = $response->body();
    //         $api_log->status = '1';
    //         $api_log->method = $method;
    //         $api_log->save();

    //         return $response->body();
    //     }
    // }
    
    // Separate a camel case word into multiple words
    
    
    public function rtoRCWithChassisPostData(Request $request)
    {       
        $sessionData = session('data');
        if($this->checkCredit() === false)
        {
            return response()->json(['status' => 'No more credit limit available']);
        }
        else{
        // return $request;
        
            $response_from = 1;
            $apiUrl             = Config::get('custom.rto.rc.url');
            $authorization      = Config::get('custom.rto.rc.Authorization');
            $user_id            = Config::get('custom.rto.rc.user_id');
            $api_id             = Config::get('custom.rto.rc.api_id');
            $api_name           = Config::get('custom.rto.rc.api_name');
            $vendor             = Config::get('custom.rto.rc.vender');
            // Get the vehicle number from the request
            $method = 'POST';
            $chassisNumber = $request->input('chassisNo'); 
        
            $isValidchassisNumber = $this->validateChassisNumber($chassisNumber);
            if ($isValidchassisNumber === false) {
                return response()->json(['status' => 'Please enter valid chassis number']);
            }
            
            // Request data
            $data = array(
                'user_id' => $user_id,
                'chassisNumber' => $chassisNumber
            );

            $buildQueryData = http_build_query($data);
            
            //-------------------------Start Check History-------------------------------------
            $response = $this->checkHistoryRCWithChassis($chassisNumber, $vendor);
            
            if(empty($response))
            {
                $headers = array(
                    'Authorization:'.$authorization,
                    'Content-Type: application/x-www-form-urlencoded'
                );

                $curl = curl_init();
                curl_setopt($curl, CURLOPT_URL, $apiUrl);
                curl_setopt($curl, CURLOPT_POST, true);
                curl_setopt($curl, CURLOPT_POSTFIELDS, $buildQueryData);
                curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
                curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
                $response = curl_exec($curl);
                // echo $apiUrl; die;
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
                    //success
                    if($error_no == 200)
                    {
                        $this->updateUtilizedCredit($sessionData['Client_id']);
                        $this->addHistoryRCWithChassis($chassisNumber, $vendor, $buildQueryData, $response);
                        // $return = $responseData;
                        $return = json_encode(array('code' => $error_no, 'message'=> $error, 'data'=>$responseData));
                    }
                    else{
                        //error
                        $return = json_encode(array('code' => $error_no, 'message'=> $error));
                    }
                    //echo "<pre>"; print_r($responseData);die;
                }
            }
            else{

                $responseData = (!empty($response)) ? json_decode($response, true) : '';
                $error = isset($responseData['message']) ? $responseData['message'] : '';
                $error_no = isset($responseData['statusCode']) ? $responseData['statusCode'] : '';
                $remark = 'Response from History';
                $response_from = 2;
                //success
                if($error_no == 200)
                {
                    $this->updateUtilizedCredit($sessionData['Client_id']);
                    // $return = $responseData;
                    $return = json_encode(array('code' => $error_no, 'message'=> $error, 'data'=>$responseData));
                }
                else{
                    //error
                    $return = json_encode(array('code' => $error_no, 'message'=> $error));
                }
                //echo "<pre>"; print_r($responseData);die;
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
            $api_log->request = $buildQueryData;
            $api_log->request_type = 1;
            $api_log->response = $response;
            $api_log->response_from = $response_from;
            $api_log->status = '1';
            $api_log->method = $method;
            $api_log->save();
            return $return;
        }
    }

    
    private function separateWordsFromCamelCase($inputString)
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
    
    private function checkHistoryRC($vehicleNo, $vendor)
    {
        $returnArr = '';
       //echo "SELECT id, response FROM `history_rc` WHERE vehicle_no = '$vehicleNo' AND vendor = '$vendor' AND `status` IN (0,1) AND created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY) ORDER BY id DESC LIMIT 1";die;
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

    private function addHistoryRC($vehicleNo, $vendor, $request, $response)
    {
        $createdAt = now();
        return DB::table('history_rc')->insert([
            'vehicle_no' => $vehicleNo,
            'vendor' => $vendor,
            'request' => $request,
            'response' => $response,
            'status' => 1,
            'created_at' => $createdAt,
        ]);
    }


    private function checkHistoryRCWithChassis($chassis_no, $vendor)
    {
        $returnArr = '';
       // $sevenDaysAgo = Carbon::now()->subDays(7)->toDateString();
        $result = DB::select("SELECT id, response FROM `history_rc_chassis` WHERE chassis_no = '$chassis_no' AND vendor = '$vendor' AND `status` IN (0,1) AND created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY) ORDER BY id DESC LIMIT 1");
        if (!empty($result)) {
           
            $returnArr = $result[0]->response;
        }
        else{
            DB::table('history_rc_chassis')
            ->where('chassis_no', $chassis_no)
            ->where('vendor', $vendor)
            ->whereIn('status', [0, 1])
            ->delete();
            $returnArr = '';
        }
        return $returnArr;
    }

    private function addHistoryRCWithChassis($chassis_no, $vendor, $request, $response)
    {
        $createdAt = now();
        return DB::table('history_rc_chassis')->insert([
            'chassis_no' => $chassis_no,
            'vendor' => $vendor,
            'request' => $request,
            'response' => $response,
            'status' => 1,
            'created_at' => $createdAt,
        ]);
    }
}
