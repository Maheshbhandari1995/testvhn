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


class LicenseController extends Controller
{
    use CommonTraits;
    use ApisTraits;

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

    
    public function checkCredit()
    {
        $sessionData = session('data');
        $clientID = $sessionData['Client_id'];
        $maxCnt = DB::select("SELECT max_count as max_count, envtype FROM `clients` WHERE id = '$clientID'");
        if($maxCnt[0]->envtype == 'preproduction')
        {
            $apiCnt = DB::select("SELECT count(*) as cnt FROM `api_log` WHERE client_id='$clientID'");
            if($apiCnt[0]->cnt < $maxCnt[0]->max_count)
            {
                return true;
            }
            else{
                return false;
            }
        }
        else{
            return true;
        }
    }

    public function validateDLNumber($LicenseNo) {
        if (trim($LicenseNo) === '') {
            return false;
        }
        $regex = '/^[A-Za-z]{2}\d{2}\s?\d{11}$/';
        $isValid = preg_match($regex, $LicenseNo);
        return $isValid;
    }
    
    public function validateDOB($date){
        if (trim($date) === '') {
            return false;
        }
        $dateRegex = '/^\d{2}-\d{2}-\d{4}$/';
        $isValid = preg_match($dateRegex, $date);
        return $isValid;
    }

    public function validatedevDOB($date){
        if (trim($date) === '') {
            return false;
        }
        $dateRegex = '/^\d{2}\/\d{2}\/\d{4}$/';
        $isValid = preg_match($dateRegex, $date);
        return $isValid;
    }

    public function Licensedigitapdldata(Request $request){

        $sessionData = session('data');
        if($this->checkCredit() === false)
        {
            return response()->json(['status' => 'No more credit limit available']);
        }
        else{
                //return $request;

            $response_from = 1;
            $url                    = Config::get('custom.digitap.license.url');
            $authorization          = Config::get('custom.digitap.license.Authorization');
            $api_id                 = Config::get('custom.digitap.license.api_id');
            $api_name               = Config::get('custom.digitap.license.api_name');
            $vendor                 = Config::get('custom.digitap.license.vender');
            $client_ref_num         = Config::get('custom.digitap.license.client_ref_num');
            // Get the vehicle number from the request
            $method = 'POST';
            $dl = $request->input('dl'); 
            $dob = $request->input('dob');
            $isValiddlNumber = $this->validateDLNumber($dl);
            $isValiddob = $this->validatedevDOB($dob);
            if ($isValiddlNumber === false) {
                return response()->json(['status' => 'Please enter valid Driving license number']);
            }
            elseif($isValiddob === false ){
                return response()->json(['status' => 'Please enter valid date of birth format']);
            }
            $responseStep1 = "";
            //-------------------------Start Check History-------------------------------------
            $responseStep1 = $this->checkHistoryLicense($dl,$dob,$vendor);


            $dataStep1 = array(
                'dl_number' => $dl,
                'dob' => $dob,
                'client_ref_num' => '1234',
            );

            $jsonDataStep1 = json_encode($dataStep1);
            
            if(empty($responseStep1))
            {
                //------------------------- Step1-------------------------------------
                
                $headers = array(
                    'Authorization:'.$authorization,
                    'Content-Type: application/json'
                );
                //echo "<pre>";print_r($headers);die;
                $curlStep1 = curl_init();
                curl_setopt($curlStep1, CURLOPT_URL, $url);
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
                            $responseData = json_decode($responseStep1, true);
                            //echo "<pre>"; print_r($responseData);exit;
                            $message = isset($responseData['message']) ? $responseData['message'] : '';
                            $statusCode = isset($responseData['result_code']) ? $responseData['result_code'] : '';
                            $status_code = isset($responseData['http_response_code']) ? $responseData['http_response_code'] : '';
                            $remark = 'Response from Vendor API';
                            // !empty($responseData['result'])
                            if($statusCode == 101 && !empty($responseData['result']) )
                            {   
                                $statusCode = 200;
                                $this->addHistoryLicense($dl, $dob,$vendor, $jsonDataStep1, $responseStep1,$statusCode);
                                $return = $responseData;

                            }
                            else{
                                $statusCode = 404;
                                $this->addHistoryLicense($dl, $dob,$vendor, $jsonDataStep1, $responseStep1,$statusCode);
                                $return = json_encode(array('status_code' => $statusCode, 'message'=> $message, 'status' => $status_code));
                            }
                }
            }
            else{
                $responseData = json_decode($responseStep1, true);
                $message = isset($responseData['message']) ? $responseData['message'] : '';
                $statusCode = isset($responseData['result_code']) ? $responseData['result_code'] : '';
                $status_code = isset($responseData['http_response_code']) ? $responseData['http_response_code'] : '';
                $remark = 'Response from History';
                $response_from = 2;
                if($statusCode == 101 && !empty($responseData['result']))
                {
                    $statusCode = 200;
                    $return = $responseData;
                }
                else{
                    $return = json_encode(array('status_code' => $statusCode, 'message'=> $message, 'status' => $status_code));
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
            $api_log->request = $jsonDataStep1;
            $api_log->input = $dl;
            $api_log->response = $responseStep1;
            $api_log->response_from = $response_from;
            $api_log->status = '1';
            $api_log->method = $method;
            $api_log->save();


            return $return;

        }
        

    }

    public function LicenseAuthbridgedldata(Request $request){

        $sessionData = session('data');
        if($this->checkCredit() === false)
        {
            return response()->json(['status' => 'No more credit limit available']);
        }
        else{
        // return $request;
            $response_from = 1;
            $encrypted_string_url   = Config::get('custom.authbridge.license.encrypted_string_url');
            $utilitysearch_url      = Config::get('custom.authbridge.license.utilitysearch_url');
            $decrypt_encrypted_string_url      = Config::get('custom.authbridge.license.decrypt_encrypted_string_url');
            $username               = Config::get('custom.authbridge.license.username');
            $api_id                 = Config::get('custom.authbridge.license.api_id');
            $api_name               = Config::get('custom.authbridge.license.api_name');
            $vendor                 = Config::get('custom.authbridge.license.vender');
            // Get the vehicle number from the request
            $method = 'POST';
            $dl = $request->input('dl'); 
            $dob = $request->input('dob');
            $isValiddlNumber = $this->validateDLNumber($dl);
            $isValiddob = $this->validateDOB($dob);
            if ($isValiddlNumber === false) {
                return response()->json(['status' => 'Please enter valid Driving license number']);
            }
            elseif($isValiddob === false ){
                return response()->json(['status' => 'Please enter valid date of birth format']);
            }
            $response = "";
            //-------------------------Start Check History-------------------------------------
            $response = $this->checkHistoryLicense($dl,$dob,$vendor);


            $dataStep1 = array(
                'transID' => '1234567',
                'docType' => '326',
                'docNumber' => $dl,
                'dob' => $dob
            );

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
                    //return $responseStep1;
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
                        $message = curl_error($curlStep2);
                        $statusCode = curl_errno($curlStep2);
                        $remark = 'Curl Error';
                        $return = json_encode(array('status_code' => $statusCode, 'message'=> $message));
                    }
                    else
                    {   
                       // return $responseStep2;
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
                            //echo "<pre>"; print_r($responseData);exit;
                            $message = isset($responseData['message']) ? $responseData['message'] : '';
                            $statusCode = isset($responseData['status_code']) ? $responseData['status_code'] : '';
                            $status_code = isset($responseData['status']) ? $responseData['status'] : '';
                            $remark = 'Response from Vendor API';
                            if($statusCode == 200 || $status_code == 1)
                            {   
                                $status_code = 200;
                                $this->addHistoryLicense($dl, $dob,$vendor, $jsonDataStep1, $response,$status_code);

                                $return = $responseData;

                            }
                            else{
                                $this->addHistoryLicense($dl, $dob,$vendor, $jsonDataStep1, $response,$status_code);
                                $return = json_encode(array('status_code' => $statusCode, 'message'=> $message, 'status' => $status_code));
                            }
                            //echo "<pre>"; print_r($responseData);exit;
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
                    $status_code = 200;
                    $return = $responseData;
                }
                else{
                    $return = json_encode(array('status_code' => $statusCode, 'message'=> $message, 'status' => $status_code));
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
            $api_log->response_status_code = isset($status_code) ? $status_code : '';
            $api_log->response_message  = isset($message) ? $message : '';
            $api_log->remark  = isset($remark) ? $remark : '';
            $api_log->api_url = $decrypt_encrypted_string_url;
            $api_log->request = $jsonDataStep1;
            $api_log->input = $dl;
            $api_log->response = $response;
            $api_log->response_from = $response_from;
            $api_log->status = '1';
            $api_log->method = $method;
            $api_log->save();


            return $return;

        }
    }

    private function addHistoryLicense($dl,$dob,$vendor, $request, $response,$status_code)
    {
        $createdAt = now();
        return DB::table('history_license')->insert([
            'license_no' => $dl,
            'dob' => $dob,
            'vendor' => $vendor,
            'request' => $request,
            'status_code' => $status_code,
            'response' => $response,
            'status' => 1,
            'created_at' => $createdAt,
        ]);
    }


    public function retrieveSignzyLicenseData(Request $request){
        if($this->checkCredit() === false)
        {
            return response()->json(['status' => 'No more credit limit available']);
        }
        else{
            $sessionData = session('data');
            $apiUrl = Config::get('custom.signzy.license.url');
            $token = Config::get('custom.signzy.license.accessToken');
            $api_name = Config::get('custom.signzy.license.api_name');
            $vendorname = Config::get('custom.signzy.license.vender');
            $itemId = Config::get('custom.signzy.license.itemId');
            $api_id = Config::get('custom.signzy.license.api_id');
            $service = Config::get('custom.signzy.license.service');
            $task = Config::get('custom.signzy.license.task');
            $method = 'POST';
            $dl_number = $request->input('dl');
            //echo $dl_number;
            $dob = $request->input('dob');
            $dob = date("d/m/Y", strtotime($dob));
            $hitoryResp = $this->checkHistoryLicense($dl_number,$dob, $vendorname);

            if (!empty($hitoryResp)) {

                $data = array(
                    "service" => $service,
                    "itemId" => $itemId,
                    "accessToken" => $token,
                    "task" => $task,
                    "essentials" => array(
                        "number" => $dl_number,
                        "dob" => $dob
                    )
                );
                
                $jsonData = json_encode($data);

                $api_log = new Rcdetails();
                $sessionData = session('data');
                $api_log->user_id = $sessionData['userID'];
                $api_log->client_id = $sessionData['Client_id'];
                $api_log->client_name = $sessionData['clientName'];
                $api_log->api_name = $api_name;
                $api_log->vender = $vendorname;
                $api_log->api_url = $apiUrl;
                $api_log->input = $dl_number;
                $api_log->request = $jsonData;
                $api_log->response = $hitoryResp; 
                $api_log->response_message ='success';
                $statusCode = 200;
                $api_log->response_status_code = $statusCode;
                $api_log->status = '1';
                $api_log->method = $method;
                $api_log->save();

                return $hitoryResp;
                //echo 1;
            } else {
                $data = array(
                    "service" => $service,
                    "itemId" => $itemId,
                    "accessToken" => $token,
                    "task" => $task,
                    "essentials" => array(
                        "number" => $dl_number,
                        "dob" => $dob
                    )
                );
                
                $jsonData = json_encode($data);
                
                $headers = array(
                    'token: ' . $token,
                    'Content-Type: application/json',
                    'Content-Length: ' . strlen($jsonData)
                );

                $curl = curl_init($apiUrl);
                curl_setopt($curl, CURLOPT_CUSTOMREQUEST, $method);
                curl_setopt($curl, CURLOPT_POSTFIELDS, $jsonData);
                curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);

                try {
                    $response = curl_exec($curl);
                    $httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
                
                    $responseData = json_decode($response, true);
                
                    if (is_array($responseData) && !empty($responseData)) {
                        $isResultSet = isset($responseData['response']['result']);
                        if ($isResultSet) {
                            $createdAt = now();
                            $insertData = [
                                'license_no' => $dl_number,
                                'vendor' => $vendorname,
                                'request' => $jsonData,
                                'response' => $response,
                                'dob' => $dob,
                                'status' => 1,
                                'created_at' => $createdAt,
                            ];
                            $inserted = DB::table('history_license')->insert($insertData);
                        }

                        $api_log = new Rcdetails();
                        $sessionData = session('data');
                        $api_log->user_id = $sessionData['userID'];
                        $api_log->client_id = $sessionData['Client_id'];
                        $api_log->client_name = $sessionData['clientName'];
                        $api_log->api_name = $api_name;
                        $api_log->vender = $vendorname;
                        $api_log->api_url = $apiUrl;
                        $api_log->request = $jsonData;
                        $api_log->input = $dl_number;
                        $api_log->response = json_encode($responseData); 
                        $api_log->response_message ='success';
                        $statusCode = isset($responseData['error']['statusCode']) ? $responseData['error']['statusCode'] : 200;
                        $api_log->response_status_code = $statusCode;
                        $api_log->status = '1';
                        $api_log->method = $method;
                        $api_log->save();

                        $this->updateUtilizedCredit($sessionData['Client_id']);
                
                        return json_encode($responseData);
                    } else {
                        $api_log = new Rcdetails();
                        $sessionData = session('data');
                        $api_log->user_id = $sessionData['userID'];
                        $api_log->client_id = $sessionData['Client_id'];
                        $api_log->client_name = $sessionData['clientName'];
                        $api_log->api_name = $api_name;
                        $api_log->vender = $vendorname;
                        $api_log->api_url = $apiUrl;
                        $api_log->request = $jsonData;
                        $api_log->input = $dl_number;
                        $api_log->response = json_encode($responseData); // Convert object to JSON string
                        // Access the statusCode value
                        $statusCode = isset($responseData['error']['statusCode']) ? $responseData['error']['statusCode'] : 200;
                        $api_log->response_message ='failed';
                        $api_log->response_status_code = $statusCode;
                        $api_log->status = '1';
                        $api_log->method = $method;
                        $api_log->save();
                
                        return json_encode($responseData);
                    }
                } catch (\Exception $e) {
                    $api_log = new Rcdetails();
                    $sessionData = session('data');
                    $api_log->user_id = $sessionData['userID'];
                    $api_log->client_id = $sessionData['Client_id'];
                    $api_log->client_name = $sessionData['clientName'];
                    $api_log->api_name = $api_name;
                    $api_log->vender = $vendorname;
                    $api_log->api_url = $apiUrl;
                    $api_log->request = $jsonData;
                    $api_log->input = $dl_number;
                    $api_log->response = $e->getMessage();
                    $api_log->response_status_code = 500;
                    $api_log->response_message ='failed';
                    $api_log->status = '1';
                    $api_log->method = $method;
                    $api_log->save();
                
                    return response()->json(['error' => $e->getMessage()]);
                } finally {
                    curl_close($curl);
                }
            }
        }
            
    }

    private function checkHistoryLicense($dl_number,$dob, $vendorname)
    {   
        $sessionData = session('data');
        $returnArr = '';
       // $sevenDaysAgo = Carbon::now()->subDays(7)->toDateString();
        $result = DB::select("SELECT id, response FROM `history_license` WHERE license_no = '$dl_number' AND dob='$dob' AND vendor = '$vendorname' AND `status` IN (0,1) AND created_at >= 
        CASE 
            WHEN status_code IN ('404','9') THEN DATE_SUB(NOW(), INTERVAL 1 DAY) 
            ELSE DATE_SUB(NOW(), INTERVAL 7 DAY) 
        END ORDER BY id DESC LIMIT 1");
        if (!empty($result)) {
            $returnArr = $result[0]->response;
        }
        else{
            $returnArr = '';
        }
        
        return $returnArr;
    }

   
}
