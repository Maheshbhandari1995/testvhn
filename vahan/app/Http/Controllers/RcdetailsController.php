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
use App\Traits\ApisTraits;
use Illuminate\Support\Facades\Log;
use PDF;
use Dompdf\FontMetrics;
class RcdetailsController extends Controller
{
    use CommonTraits;
    use ApisTraits;

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
            $response_from = 1;
            $apiUrl     = Config::get('custom.invincible.rc_chassis.url');
            $clientId   = Config::get('custom.invincible.rc_chassis.clientId');
            $secretKey  = Config::get('custom.invincible.rc_chassis.secretKey');
            $api_id     = Config::get('custom.invincible.rc_chassis.api_id');
            $api_name   = Config::get('custom.invincible.rc_chassis.api_name');
            $vendor     = Config::get('custom.invincible.rc_chassis.vender');
            $method = 'POST';
            $chassisNumber = $request->input('chassisNumber'); 
        
            $isValidchassisNumber = $this->validateChassisNumber($chassisNumber);
            if ($isValidchassisNumber === false) {
                return response()->json(['status' => 'Please enter valid chassis number']);
            }

            $data = [
                'chassisNumber' => $chassisNumber
            ];
            $jsonData = json_encode($data);
            
            
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
                    if($error_no == 200)
                    {
                        $this->addHistoryRCWithChassis($chassisNumber, $vendor, $jsonData, $response);
                        $return = $responseData;
                    }
                    else{
                        $return = json_encode(array('code' => $error_no, 'message'=> $error));
                    }
                }
            }
            else{

                $responseData = (!empty($response)) ? json_decode($response, true) : '';
                $error = isset($responseData['message']) ? $responseData['message'] : '';
                $error_no = isset($responseData['code']) ? $responseData['code'] : '';
                $remark = 'Response from History';
                $response_from = 2;
                if($error_no == 200)
                {
                    $return = $responseData;
                }
                else{
                    $return = json_encode(array('code' => $error_no, 'message'=> $error));
                }
            }   

            $this->updateUtilizedCredit($sessionData['Client_id']);
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
            $api_log->input = $chassisNumber;
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
                //echo "<pre>";print_r($headers);die;
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
                    
                        if ($response === false) {
                            $message = curl_error($curl);
                            $statusCode = curl_errno($curl);
                            $remark = 'Curl Error';
                            $return = json_encode(array('status_code' => $statusCode, 'message'=> $message));
                        } 
                        else 
                        {
                            $responseData = json_decode($response, true);
                            $message = isset($responseData['message']) ? $responseData['message'] : '';
                            $statusCode = isset($responseData['status_code']) ? $responseData['status_code'] : '';
                            $status_code = isset($responseData['status']) ? $responseData['status'] : '';
                            $remark = 'Response from Vendor API';
                            if($statusCode == 200 || $status_code == 1)
                            {
                                $this->addHistoryRC($vehicleNo, $vendor, $jsonDataStep1, $response);
                                $return = $responseData;
                            }
                            else{
                                $return = json_encode(array('status_code' => $statusCode, 'message'=> $message));
                            }
                        }

                    }
                }
            }
            else{
                $responseData = json_decode($response, true);
                $message = isset($responseData['message']) ? $responseData['message'] : '';
                $statusCode = isset($responseData['status_code']) ? $responseData['status_code'] : '';
                $status_code = isset($responseData['status']) ? $responseData['status'] : '';
                $remark = 'Response from History';
                $response_from = 2;
                if($statusCode == 200 || $status_code == 1)
                {
                    $return = $responseData;
                }
                else{
                    $return = json_encode(array('status_code' => $statusCode, 'message'=> $message));
                }

            }

            
            $this->updateUtilizedCredit($sessionData['Client_id']);

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
            return $return;

        }
    }


    
    public function signzyViewRC(){
        return view('rc.rc');
    }

    public function signzyRCPostData(Request $request)
    {     
        $sessionData = session('data');
        $return = false;  
        if($this->checkCredit() === false)
        {
            return response()->json(['status' => 'No more credit limit available']);
        }
        else{
        // return $request;
        
            $response_from = 1;
            $url            = Config::get('custom.signzy.rc.url');
            $Authorization  = Config::get('custom.signzy.rc.Authorization');
            $api_id          = Config::get('custom.signzy.rc.api_id');
            $api_name       = Config::get('custom.signzy.rc.api_name');
            $vendor          = Config::get('custom.signzy.rc.vender');
            // Get the vehicle number from the request
            $method = 'POST';
            $vehicleNo = $request->input('vehicleNo'); 
        
            $vehicleNo = $this->filterVehicleNumber($vehicleNo);
            $isValidVehicleNumber = $this->validateVehicleNumber($vehicleNo);
            if ($isValidVehicleNumber === false) {
                return response()->json(['status' => 'Please enter valid vehicle number']);
            }
            $response = "";
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
                    $message = isset($responseData['message']) ? $responseData['message'] : '';
                    $statusCode = isset($responseData['status_code']) ? $responseData['status_code'] : '';
                    $remark = 'Response from Vendor API';
                    if($statusCode == 200 || !empty($responseData['result']))
                    {
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
                $message = isset($responseData['message']) ? $responseData['message'] : '';
                $statusCode = isset($responseData['status_code']) ? $responseData['status_code'] : '';
                $remark = 'Response from History';
                $response_from = 2;
                if($statusCode == 200 || !empty($responseData['result']))
                {
                    $statusCode = 200;
                    $return = json_encode(array('status_code' => $statusCode, 'message'=> $responseData['result']));
                }
                else{
                    $return = json_encode(array('status_code' => $statusCode, 'message'=> $message));
                }
            }
            
            
            $this->updateUtilizedCredit($sessionData['Client_id']);
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
            $api_log->input = $vehicleNo;
            $api_log->response = $response;
            $api_log->response_from = $response_from;
            $api_log->status = '1';
            $api_log->method = $method;
            $api_log->save();
            return $return;
        }
    }

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
                        $this->addHistoryRCWithChassis($chassisNumber, $vendor, $buildQueryData, $response);
                        $return = json_encode(array('code' => $error_no, 'message'=> $error, 'data'=>$responseData));
                    }
                    else{
                        $return = json_encode(array('code' => $error_no, 'message'=> $error));
                    }
                }
            }
            else{

                $responseData = (!empty($response)) ? json_decode($response, true) : '';
                $error = isset($responseData['message']) ? $responseData['message'] : '';
                $error_no = isset($responseData['statusCode']) ? $responseData['statusCode'] : '';
                $remark = 'Response from History';
                $response_from = 2;
                if($error_no == 200)
                {
                    $return = json_encode(array('code' => $error_no, 'message'=> $error, 'data'=>$responseData));
                }
                else{
                    $return = json_encode(array('code' => $error_no, 'message'=> $error));
                }
            }   

            
            $this->updateUtilizedCredit($sessionData['Client_id']);

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
            $api_log->input = $chassisNumber;
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
        $result = DB::select("SELECT id, response FROM `history_rc` WHERE vehicle_no = '$vehicleNo' AND vendor = '$vendor' AND `status` IN (0,1) AND created_at >= 
        CASE 
            WHEN status_code IN ('404','9') THEN DATE_SUB(NOW(), INTERVAL 1 DAY) 
            ELSE DATE_SUB(NOW(), INTERVAL 7 DAY) 
        END ORDER BY id DESC LIMIT 1");

        if (!empty($result)) {
            
            $returnArr = $result[0]->response;
        }
        else{
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

    
    public function rcPostData(Request $request)
    {
        $sessionData = session('data');
        $defaultStatusCode = 101;        
        $clientid   = $sessionData['Client_id'];        
        $userID     = $sessionData['userID'];        
        $clientName = $sessionData['clientName'];
        $moduleName = 'rc';
        $custom_log = Log::channel('custom');
        //$custom_log->setPath(str_replace(['%clientName%', '%moduleName%'], [$clientName, $moduleName], $custom_log->getPath()));
        // $custom_log = Log::channel('custom_log');
        $custom_log->debug("\n\n\n---------Start process here for Client : ".$clientName." & Module ".$moduleName." ---------\n");

        $vehicleNo              = strtoupper($request->input('vehicleNo'));
        $vehicleNo              = $this->filterVehicleNumber($vehicleNo);
        $isValidVehicleNumber   = $this->validateVehicleNumber($vehicleNo);
        $custom_log->debug(__LINE__." ----isValidVehicleNumber ---- : ".$isValidVehicleNumber);
        if ($isValidVehicleNumber === false) {
            $statusCode         = $defaultStatusCode; 
            $response_message   = 'Vehicle number is not valid';
            //$response   = json_encode(['vehicleNo' => $vehicleNo, 'status_code' => $statusCode, 'message' => $response_message]);
            return $response = json_encode(['vendor' => 'authbridge', 'statusCode' => $statusCode, 'response_message' => $response_message]);  
        }
        else{

            $primaryVendor = Module::where('client_id', $clientid)
                ->where('apiname', 'rc')
                ->value('vendorname');
            $secondaryVendor = Module::where('client_id', $clientid)
                ->where('apiname', 'rc')
                ->value('sec_vendor');

            $apiResult = DB::table('api_master')
            ->select('api_master.id', 'api_master.vender')
            ->whereIn('api_master.vender', [$primaryVendor, $secondaryVendor])
            ->where('api_master.api_name', 'rc')
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
                $responseHistrory = $this->checkHistoryRCForApiList([$primaryVendor,$secondaryVendor], $vehicleNo);
                if(isset($responseHistrory) && empty($responseHistrory)) 
                {
                    
                    $custom_log->debug(__LINE__." --- API Hits for Primary Vendor --- ".$primaryVendor);
                    $response_from  = 1; //0,1 => Vendor's API, 2=> History
                    $response_type  = 1; //0=> History, 1 => Primary, 2 => Secondary
                    $vendor         = $primaryVendor; 
                    $remark         = 'Response from Primary Vendor API';
                    // Primary Vendor
                    $responseArr        = $this->apiList($primaryVendor, $vehicleNo);
                    $primary_response   = $responseArr['response'];
                    $primary_status     = $responseArr['status_code'];

                    // $responseArr['status'] = 'failed';
                    //echo "<pre> primaryVendor : "; print_r($responseArr);
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
                            $responseArr = $this->apiList($secondaryVendor, $vehicleNo);
                            $secondary_response     = $responseArr['response'];
                            $secondary_status       = $responseArr['status_code'];
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
                    $response_from  = 2;                   
                    $response       = $responseHistrory['response'];
                    $vendor         = $responseHistrory['vendor'];
                    $statusCode     = $responseHistrory['status_code'];
                    $url            = '';
                    $requestData    = json_encode(array("docNumber" => $vehicleNo,"transID" => "1234567","docType" =>"372"));
                    $response_message= ($responseHistrory['status_code'] == 200 ? "Success": " No Data Found");
                    $custom_log->debug(__LINE__." --- Pulled from History  --- ");
                }
            }
            // echo "<pre> sdfsdfsdf : "; print_r($response);die;  
            $updatedID = $this->updateUtilizedCredit($clientid);
            $custom_log->debug(__LINE__." --- Update clients for Credit with updated ID --- ". $updatedID);
                
        
            //Details Log need to be incorporate
            $api_log =  new Rcdetails();
            $api_log->api_id = $appidArr[strtolower($vendor)];
            $api_log->api_name = 'rc';
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

            $returnArr['api_log_id'] = $api_log->id;
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


	public function downloadPDF_RC(Request $request)
    {
		//Request $request
		$sessionData = session('data');
        $clientid	= $sessionData['Client_id'];        
        $userID     = $sessionData['userID'];        
        $clientName = $sessionData['clientName'];
        $id         = $request->input('id');
        // $id         = 4735; //13236
        // Fetch data to be included in the PDF from the database
	   $apiResult = DB::table('api_log')
		->select('api_log.id', 'api_log.vender', 'api_log.response', )
		->where('api_log.id', $id)
		->where('api_log.client_id', $clientid)
		->first();
		
		$responseData = json_decode($apiResult->response, true);
		// $responseData = $this->standardRcResponse('signzy',$responseData); 
		$responseData = $this->standardRcResponse($apiResult->vender,$responseData); 
		
        // Load the view with data and generate PDF content
        $pdf = PDF::loadView('pdf.rc_pdf', ['data' => $responseData]);

        // Generate a unique filename for the PDF
        $filename = 'rc_details' . time() . '.pdf';
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
    }
	
	public function standardRcResponse($vendor, $response)
	{
        switch (strtolower($vendor)) {
            case "authbridge":
                $response = $this->rcAuthbridgeResponsePDF($response);
                break;
            case "signzy":
                $response = $this->rcSignzyResponsePDF($response);
                break;
        }
        return $response;
	}
	
	public function rcAuthbridgeResponsePDF($response)
	{
		$returnArr = [];
		if(isset($response['msg']) && !empty($response['msg'])){
            $msg 	= $response['msg']; 
			$header = ['Input RC Number','Vehicle Class', 'Fuel Type',	'Chassis Number',	'Engine Number',	'Manufacture Date',	'Model / Makers Class'	,'Maker/Manufacturer','Engine Capacity',	'Color'	,'Gross Weight',	'No of cylinder'	,'Seating Capacity',	'sleeper Capacity'	,'Norms Type',	'Body Type',	'Owner Serial Number'	,'Mobile Number'	,'Unloading Weight'	,'Rc Standard Cap'	,'Vehicle Standing Capacity',	'Vehicle Number',	'Blacklist Status'	,'Is Commercial',	'Noc Details',	'Registration Number'	,'Registration Date',	'Fitness Date/RC Expiry Date',	'RTO',	'Tax Upto',	'Vehicle Tax Up to'	,'Status'	,'Status As On'	,'Owners Name',	'Father Name/Husband Name',	'Permanent Address'	,'Present Address',	'Financer Name'	,'Insurance To Date/Insurance Upto',	'Policy Number'	,'Insurance Company',	'PUCC NO'	,'PUCC Upto',	'Permit Issue Date',	'Permit Number',	'Permit Type',	'Permit Vald From'	,'Permit Valid Upto',	'Non Use Status'	,'Non Use From',	'Non Use To',	'National Permit Number',	'National Permit Upto',	'National Permit Issued By'];
		
			$data = [
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
			
			$returnArr	= array_combine($header, $data);
		}
		return $returnArr;
	}
	
	public function rcSignzyResponsePDF($response)
	{
		$returnArr = [];
		if(isset($response['result']) && !empty($response['result'])){
            $msg 	= $response['result']; 
			$header = ['Input RC Number','Vehicle Class', 'Fuel Type',	'Chassis Number',	'Engine Number',	'Manufacture Date',	'Model / Makers Class'	,'Maker/Manufacturer','Engine Capacity',	'Color'	,'Gross Weight',	'No of cylinder'	,'Seating Capacity',	'sleeper Capacity'	,'Norms Type',	'Body Type',	'Owner Serial Number'	,'Mobile Number'	,'Unloading Weight'	,'Rc Standard Cap'	,'Vehicle Standing Capacity',	'Vehicle Number',	'Blacklist Status'	,'Is Commercial',	'Noc Details',	'Registration Number'	,'Registration Date',	'Fitness Date/RC Expiry Date',	'RTO',	'Tax Upto',	'Vehicle Tax Up to'	,'Status'	,'Status As On'	,'Owners Name',	'Father Name/Husband Name',	'Permanent Address'	,'Present Address',	'Financer Name'	,'Insurance To Date/Insurance Upto',	'Policy Number'	,'Insurance Company',	'PUCC NO'	,'PUCC Upto',	'Permit Issue Date',	'Permit Number',	'Permit Type',	'Permit Vald From'	,'Permit Valid Upto',	'Non Use Status'	,'Non Use From',	'Non Use To',	'National Permit Number',	'National Permit Upto',	'National Permit Issued By'];
			$data = [
				isset($msg['regNo']) ? $msg['regNo'] : null, // Input RC Number
				isset($msg['class']) ? $msg['class']." ".$msg['vehicleCategory']  : null, // Vehicle Class
				isset($msg['type']) ? $msg['type'] : null, // type
				isset($msg['chassis']) ? $msg['chassis'] : null, // Chassis Number
				isset($msg['engine']) ? $msg['engine']  : null, // Engine Number
				isset($msg['vehicleManufacturingMonthYear']) ? $msg['vehicleManufacturingMonthYear'] : null, // Manufacture Date
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
			$returnArr	= array_combine($header, $data);
		}
		return $returnArr;
	}
	
	/* public function flattenArray($array) {
		$result = array();
		foreach ($array as $key => $value) {
			if (is_array($value)) {
				$result = array_merge($result, $this->flattenArray($value));
			} else {
				$result[$key] = $value;
			}
		}
		return $result;
	} */

}
