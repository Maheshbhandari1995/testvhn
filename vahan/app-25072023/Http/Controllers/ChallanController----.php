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
use Carbon\Carbon;

class ChallanController extends Controller
{
    use CommonTraits;

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
          // echo "<pre>"; print_r($request->input);die;
            $apiUrl = Config::get('custom.invincible.challan_chassis.challan_url');
            $clientId = Config::get('custom.invincible.challan_chassis.clientId');
            $secretKey = Config::get('custom.invincible.challan_chassis.secretKey');
            $api_id     = Config::get('custom.invincible.challan_chassis.api_id');
            $api_name   = Config::get('custom.invincible.challan_chassis.api_name');
            $vendor     = Config::get('custom.invincible.challan_chassis.vender');
            $response_from = 1;
            // Get the vehicle number from the request
            $vehicleNo      = $request->input('vehicle_No');
            $chassis_No     = $request->input('chassis_No');
            $method         = 'POST';
            $data = array(
                'vehicleNumber' => $vehicleNo,
                'chassisLast_5_digits' => $chassis_No,
            );
            
            // Convert data to JSON format
            $jsonData = json_encode($data);

            // Request data
            $response = $this->checkHistoryChallan($vehicleNo, $vendor);
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
                    //echo "<pre>";print_r($headers); echo "<pre>";print_r($response);die;

                    if ($response === false) {
                        $error = curl_error($curl);
                        $error_no = curl_errno($curl);
                        $remark = 'Curl Error';
                        $return = json_encode(array('code' => $error_no, 'message'=> $error));
                    } 
                    else 
                    {
                        if(empty($response))
                        {
                            return json_encode(array('status' => 'No Record Found!'));
                        }
                        
                        // API call was successful
                        $responseData = json_decode($response, true);
                        $error = isset($responseData['message']) ? $responseData['message'] : '';
                        $error_no = isset($responseData['code']) ? $responseData['code'] : '';
                        $remark = 'Response from Vendor API';
                        //success
                        if($error_no == 200)
                        {
                            $this->updateUtilizedCredit($sessionData['Client_id']);
                            $this->addHistoryChallan($vehicleNo, $vendor, $jsonData, $response);
                            $return = $this->formateDataforinvChallan($responseData);
                        }
                        else{
                            //error
                            $return = json_encode(array('code' => $error_no, 'message'=> $error));
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
                if($error_no == 200)
                {
                    $this->updateUtilizedCredit($sessionData['Client_id']);
                    $return = $this->formateDataforinvChallan($responseData);
                }
                else{
                    $return = json_encode(array('code' => $error_no, 'message'=> $error));
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
            $api_log->request = $jsonData;
            $api_log->response = $response;
            $api_log->response_from = $response_from;
            $api_log->status = '1';
            $api_log->method = $method;
            $api_log->save();

            return json_encode($response);
        }
    }

    public function formateDataforinvChallan($responseData)
    {
        $resArr = [];
        if(!empty($responseData['Error']))
        {
            return json_encode($responseData);
        }
        if(!empty($responseData['result']['challanDetails']))
        {
            return json_encode(array('Error' => $responseData['result']['challanDetails']));
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
        return view('challan.challan_sing');
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
          // echo "<pre>"; print_r($request->input);die;
            $apiUrl = Config::get('custom.signzy.challan.url');
            $clientId = Config::get('custom.signzy.challan.clientId');
            $secretKey = Config::get('custom.signzy.challan.secretKey');
            $api_id     = Config::get('custom.signzy.challan.api_id');
            $api_name   = Config::get('custom.signzy.challan.api_name');
            $vendor     = Config::get('custom.signzy.challan.vender');
            $response_from = 1;
            // Get the vehicle number from the request
            $vehicleNo = $request->input('vehicle_No');
            $method = 'POST';
            // Request data
            $data = array(
                'vehicleNumber' => $vehicleNo
            );
            
            // Convert data to JSON format
            $jsonData = json_encode($data);
           
            // Create the headers array with the token
            $headers = array(
                'clientId:'.$clientId,
                'secretKey:'.$secretKey,
                'Content-Type: application/json'
            );

            try {
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
                    $remark = 'Response from API';
                    
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

            } catch (\Exception $e) {
                return response()->json(['error' => $response], $response);
            }
        }
    }
   
   
    public function retrieveChallanData(Request $request)
    {   
        $sessionData = session('data');
        if($this->checkCredit() === false)
        {
            return $result = json_encode(array('code' => 401, 'message'=> 'No more credit limit available'));
        }
        else{
          // echo "<pre>"; print_r($request->input);die;
            $apiUrl = Config::get('custom.signzy.challan.url');
            $Authorization = Config::get('custom.signzy.challan.Authorization');
            $api_id     = Config::get('custom.signzy.challan.api_id');
            $api_name   = Config::get('custom.signzy.challan.api_name');
            $vendor     = Config::get('custom.signzy.challan.vender');
            $response_from = 1;
            
            // Get the vehicle number from the request
            $vehicleNo = $request->input('vehicle_No');
            $method = 'POST';
            // Request data
            $data = [
                'essentials' => ['vehicleNumber' => $vehicleNo],
                'task' => 'challanSearch'
            ];
            
            // Convert data to JSON format
            $jsonData = json_encode($data);
            
            //-------------------------Start Check History-------------------------------------
            $response = $this->checkHistoryChallan($vehicleNo, $vendor);
            // echo $vehicleNo."<pre>dfasdf"; print_r($response);die;
            if(empty($response))
            {
                // Create the headers array with the token
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
                // $requestHeaders = curl_getinfo($curl, CURLINFO_HEADER_OUT);
                // Execute the cURL request
                $response = curl_exec($curl);
                $requestHeaders = implode("\r\n", $headers);
                //echo "requestHeaders : <pre>";print_r($responseHeaders); 
               // echo "<pre>";print_r($requestHeaders); echo "<pre>";print_r($jsonData);  echo "<pre>";print_r($response);die;

                if ($response === false) {
                    $error = curl_error($curl);
                    $error_no = curl_errno($curl);
                    $remark = 'Curl Error';
                    $return = json_encode(array('code' => $error_no, 'message'=> $error));
                } 
                else 
                {
                    $responseData = json_decode($response, true);
                    $error = isset($responseData['result']['message']) ? $responseData['result']['message'] : '';
                    $error_no = isset($responseData['result']['status_code']) ? $responseData['result']['status_code'] : '';
                    $remark = 'Response from Vendor API';
                    //success
                    if($error_no == 200)
                    {
                        $this->addHistoryChallan($vehicleNo, $vendor, $jsonData, $response);
                        $this->updateUtilizedCredit($sessionData['Client_id']);
                        $return = json_encode(array('code' => $error_no, 'message'=> $responseData));
                        
                    }
                    else{
                        $error = isset($responseData['error']['message']) ? $responseData['error']['message'] : '';
                        $error_no = isset($responseData['error']['statusCode']) ? $responseData['error']['statusCode'] : '';
                        $return = json_encode(array('code' => $error_no, 'message'=> $error));
                    }
                }

            }
            else{
                $responseData = json_decode($response, true);
                $error = isset($responseData['result']['message']) ? $responseData['result']['message'] : '';
                $error_no = isset($responseData['result']['status_code']) ? $responseData['result']['status_code'] : '';
                $remark = 'Response from History';
                $response_from = 2;
                //success
                if($error_no == 200)
                {
                    $return = json_encode(array('code' => $error_no, 'message'=> $responseData));
                    $this->updateUtilizedCredit($sessionData['Client_id']);
                }
                else{
                    $error = isset($responseData['error']['message']) ? $responseData['error']['message'] : '';
                    $error_no = isset($responseData['error']['statusCode']) ? $responseData['error']['statusCode'] : '';
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
            $api_log->response = $response;
            $api_log->response_from = $response_from;
            $api_log->status = '1';
            $api_log->method = $method;
            $api_log->save();

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
}
