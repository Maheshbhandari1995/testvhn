<?php
namespace App\Traits;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Storage;

trait ApisTraits	
{
    public function rcAuthbridge($vendor, $vehicleNo)
    {
        //return $result = array('status'=>'failed','status_code' => '', 'msg'=>'Temporary', 'response'=>'');
        $response = '';
        $result = [];
        $encrypted_string_url           = Config::get('custom.authbridge.rc.encrypted_string_url');
        $utilitysearch_url              = Config::get('custom.authbridge.rc.utilitysearch_url');
        $decrypt_encrypted_string_url   = Config::get('custom.authbridge.rc.decrypt_encrypted_string_url');
        $username                       = Config::get('custom.authbridge.rc.username');
        $api_id                         = Config::get('custom.authbridge.rc.api_id');
        $api_name                       = Config::get('custom.authbridge.rc.api_name');
        $vendor                         = Config::get('custom.authbridge.rc.vender');
        


        $dataStep1      = array('docNumber' => $vehicleNo, 'transID' => '1234567', 'docType' => '372');
        $jsonDataStep1  = json_encode($dataStep1);
        $headers        = ['username:' . $username,'Content-Type: application/json'];
        $addArr         = array('url'=> $decrypt_encrypted_string_url, 'requestData' =>$jsonDataStep1);

        //$step1 = $this->curlPostHit($encrypted_string_url, $headers, $jsonDataStep1, true);
        $step1 = ApisTraits::curlPostHit($encrypted_string_url, $headers, $jsonDataStep1, true);

        // print_r($step1); exit;
        if(isset($step1['status']) && $step1['status'] == 'success')
        {
            $dataStep2      = ['requestData' => $step1['response']];
            $jsonDataStep2  = json_encode($dataStep2);
            $headers        = ['username:' . $username,'Content-Type: application/json'];
            $step2          = ApisTraits::curlPostHit($utilitysearch_url, $headers, $jsonDataStep2, true);
            //$step2          = $this->curlPostHit($utilitysearch_url, $headers, $jsonDataStep2, true);

            if(isset($step2['status']) && $step2['status'] == 'success')
            {
                $dataStep3      = $step2['response'];
                $headers        = ['username:' . $username,'Content-Type: application/json'];
                $step3          = ApisTraits::curlPostHit($decrypt_encrypted_string_url, $headers, $dataStep3, true);
                //$step3          = $this->curlPostHit($decrypt_encrypted_string_url, $headers, $dataStep3, true);
                if(isset($step3['status']) && $step3['status'] == 'success')
                {
                    $response       = $step3['response'];
                    $responseData   = json_decode($response, true);
                    $message        = isset($responseData['message']) ? $responseData['message'] : '';
                    $statusCode     = isset($responseData['status']) ? $responseData['status'] : '101';
                    if ((isset($responseData['status_code']) && $responseData['status_code'] === 200) || $responseData['status'] == 1)
                    {
                        $statusCode = 200;                        
                        $result   = ['status'=>'success','status_code'=>$statusCode, 'msg'=>'success', 'response'=>$response];
                    }
					// else if ((isset($responseData['status_code']) && $responseData['status_code'] === 404) || $responseData['status'] == 9)
                    // {
                        // $statusCode = $responseData['status_code'];                        
                        // $result   = ['status'=>'success','status_code'=>$statusCode, 'msg'=>'success', 'response'=>$response];
                    // }
                    else{
                        $message    = isset($responseData['msg']) ? $responseData['msg'] : 'Failed';
                        $result     = ['status'=>'failed','status_code'=>$statusCode, 'msg'=>$message, 'response'=>$response];
                    }
					
					//Add History For 200/1 & 404/9
					if(in_array($statusCode, [200,1,9]))
					{
						ApisTraits::addHistoryRCForApiList($vehicleNo, $jsonDataStep1, $vendor, $response, $statusCode);
					}
                }
                else{
                    //Error
                    $result   = ['status'=>'failed','status_code'=>$step3['status_code'], 'msg'=>$step3['msg'], 'response'=>$response];
                }
            }
            else{
                //Error
                $result   = ['status'=>'failed','status_code'=>$step2['status_code'], 'msg'=>$step2['msg'], 'response'=>$response];
            }
        }
        else{
            //Error
            $result   = ['status'=>'failed','status_code'=>$step1['status_code'], 'msg'=>$step1['msg'], 'response'=>$response];
        }
        $returnArr = array_merge($addArr,$result);
        return $returnArr;
    }

    public function rcSignzy($vendor, $vehicleNo)
    {
        $response   = '';
        $result     = [];
        $url                = Config::get('custom.signzy.rc.url');
        $Authorization      = Config::get('custom.signzy.rc.Authorization');
        $api_id             = Config::get('custom.signzy.rc.api_id');
        $api_name           = Config::get('custom.signzy.rc.api_name');
        $vendor             = Config::get('custom.signzy.rc.vender');

        $headers            = array('Authorization:'.$Authorization,'Content-Type: application/json');
        $data = [
            'essentials' => ['vehicleNumber' => $vehicleNo],
            'task' => 'detailedSearch'
        ];
        $jsonData           = json_encode($data);
        //$curlRes            = $this->curlPostHit($url, $headers, $jsonData, true);
        $curlRes = ApisTraits::curlPostHit($url, $headers, $jsonData, true);
        
        $addArr         = array('url'=> $url, 'requestData' =>$jsonData);
      
        if(isset($curlRes['status']) && $curlRes['status'] == 'success')
        {
            
            $response       = $curlRes['response'];
            $responseData   = json_decode($response, true);
            $message        = isset($responseData['message']) ? $responseData['message'] : '';
            $statusCode     = isset($responseData['status_code']) ? $responseData['status_code'] : '101';
            if($statusCode == 200 || !empty($responseData['result']))
            {                
                $statusCode = 200;
                $result = array('status'=>'success','status_code' => $statusCode, 'msg'=> $message, 'response'=>$response);
                // echo $historyStatus."<pre> inside : "; print_r($result);
            }
            else{
                
                $error          = isset($responseData['error']) ? $responseData['error'] : ''; 
                $statusCode     = isset($error['statusCode']) ? $error['statusCode'] : '101';
                $message        = isset($error['message']) ? $error['message'] : '';
                $result = array('status'=>'failed','status_code' => $statusCode, 'msg'=> $message, 'response'=>$response);
            }
			//Add History For 200/1 & 404/9
			if(in_array($statusCode, [200,404]))
			{
				$historyStatus = ApisTraits::addHistoryRCForApiList($vehicleNo, $jsonData, $vendor, $response, $statusCode);
			}
        }
        else{
            $result = array('status'=>'failed','status_code' => '', 'msg'=> $curlRes['msg'], 'response'=>$response);
        }
        
        $returnArr = array_merge($addArr,$result);
        // echo "<pre> returnArr : "; print_r($returnArr);
        return $returnArr;
    }
    
    public static function curlPostHit($url, $header, $postFields, $post = true)
    {
        $response = array();
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_POST, $post);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $postFields);
        curl_setopt($curl, CURLOPT_HTTPHEADER, $header);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        $result = curl_exec($curl);

        if ($result === false) {
            $message = curl_error($curl);
            $statusCode = curl_errno($curl);
            $remark = 'Curl Error';
            $response   = ['status'=>'failed','status_code'=>$statusCode, 'msg'=>$message, 'response'=>[]];
        }
        else{
            $response   = ['status'=>'success','status_code'=>'', 'msg'=>'', 'response'=>$result];
        }
        return $response;
    }

    // Insert the new record in history table
    public static function addHistoryRCForApiList($vehicleNo, $request, $vendor, $response, $statusCode = 200)
    { 
        return DB::table('history_rc')->insert([
            'vehicle_no' => $vehicleNo,
            'vendor' => $vendor,
            'request' => $request,
            'response' => $response,
            'status_code' => $statusCode,
            'status' => 1,
            'created_at' => now(),
        ]);
    }

    public function checkHistoryRCForApiList($vendor, $vehicleNo)
    {
        if(is_array($vendor))
        {
            $vendorStr = implode("', '", $vendor);
        }
        else{
            $vendorStr = $vendor;
        }

        $returnArr = [];
        $result = DB::select("SELECT response, vendor, status_code FROM `history_rc` WHERE vehicle_no = '$vehicleNo' AND vendor IN ('$vendorStr') AND `status` IN (0,1) AND created_at >= 
         CASE 
             WHEN status_code IN ('404','9') THEN DATE_SUB(NOW(), INTERVAL 1 DAY) 
             ELSE DATE_SUB(NOW(), INTERVAL 7 DAY) 
         END ORDER BY id DESC LIMIT 1");

         // AND created_at >= 
        // CASE 
        //     WHEN status_code IN ('404','9') THEN DATE_SUB(NOW(), INTERVAL 1 DAY) 
        //     ELSE DATE_SUB(NOW(), INTERVAL 7 DAY) 
        // END
       
        if (!empty($result)) {
            
            $returnArr['response'] 		= $result[0]->response;
            $returnArr['vendor'] 		= $result[0]->vendor;
            $returnArr['status_code'] 	= $result[0]->status_code;
        }
        else{
            $returnArr = [];
        }

        return $returnArr;
    }

    public function customEncrypt($data, $key)
    {
        $iv = openssl_random_pseudo_bytes(openssl_cipher_iv_length('aes-256-cbc'));
        $encryptedData = openssl_encrypt($data, 'aes-256-cbc', $key, OPENSSL_RAW_DATA, $iv);
        return base64_encode($iv . $encryptedData);
    }
    
    public function customDecrypt($encryptedData, $key)
    {
        $data = base64_decode($encryptedData);
        $ivLength = openssl_cipher_iv_length('aes-256-cbc');
        $iv = substr($data, 0, $ivLength);
        $encryptedDataWithoutIV = substr($data, $ivLength);
        return openssl_decrypt($encryptedDataWithoutIV, 'aes-256-cbc', $key, OPENSSL_RAW_DATA, $iv);
    }

    public function checkHistoryChallanForApiList($vendor, $vehicleNo)
    {
        if(is_array($vendor))
        {
            $vendorStr = implode("', '", $vendor);
        }
        else{
            $vendorStr = $vendor;
        }

        $returnArr = [];
        $result = DB::select("SELECT response, vendor FROM `history_challan` WHERE vehicle_no = '$vehicleNo' AND vendor IN ('$vendorStr') AND `status` IN (0,1) AND created_at >= DATE_SUB(NOW(), INTERVAL 1 DAY) ORDER BY id DESC LIMIT 1");

       
        if (!empty($result)) {
            
            $returnArr['response'] = $result[0]->response;
            $returnArr['vendor'] = $result[0]->vendor;
        }
        else{
            $returnArr = [];
        }
       
        return $returnArr;
    }

    public function addHistoryChallanForApiList($vehicleNo, $request, $vendor, $response)
    { 
        return DB::table('history_challan')->insert([
            'vehicle_no' => $vehicleNo,
            'vendor' => $vendor,
            'request' => $request,
            'response' => $response,
            'status' => 1,
            'created_at' => now(),
        ]);
    }

    public function challanAuthbridge($vendor, $vehicleNo)
    {   
        $response = '';
        $response_from          = 1;
                $result = [];
                $encrypted_string_url           = Config::get('custom.authbridge.challan.encrypted_string_url');
                $utilitysearch_url              = Config::get('custom.authbridge.challan.utilitysearch_url');
                $decrypt_encrypted_string_url   = Config::get('custom.authbridge.challan.decrypt_encrypted_string_url');;
                $username                       = Config::get('custom.authbridge.challan.username');
                $api_id                         = Config::get('custom.authbridge.challan.api_id');
                $api_name                       = Config::get('custom.authbridge.challan.api_name');
                $vendor                         = Config::get('custom.authbridge.challan.vender');
                       $doc_type               = Config::get('custom.authbridge.challan.doc_type');
        
        
                $dataStep1      = array('docNumber' => $vehicleNo, 'transID' => '123456', 'docType' => $doc_type);
                $jsonDataStep1  = json_encode($dataStep1);
                $headers        = ['username:' . $username,'Content-Type: application/json'];
                $addArr         = array('url'=> $decrypt_encrypted_string_url, 'requestData' =>$jsonDataStep1);
        
                $step1 = curlPostHit($encrypted_string_url, $headers, $jsonDataStep1, true);
                //$step1 = $this->curlPostHit($encrypted_string_url, $headers, $jsonDataStep1, true);

                if(isset($step1['status']) && $step1['status'] == 'success')
                {
                    $dataStep2      = ['requestData' => $step1['response']];
                    $jsonDataStep2  = json_encode($dataStep2);
                    $headers        = ['username:' . $username,'Content-Type: application/json'];
                    $step2          =curlPostHit($utilitysearch_url, $headers, $jsonDataStep2, true);
                    //$step2          = $this->curlPostHit($utilitysearch_url, $headers, $jsonDataStep2, true);
                    // echo "<pre>";print_r($step2);exit;
                    if(isset($step2['status']) && $step2['status'] == 'success')
                    {
                        $dataStep3      = $step2['response'];
                        $headers        = ['username:' . $username,'Content-Type: application/json'];
                        //$step3          = $this->curlPostHit($decrypt_encrypted_string_url, $headers, $dataStep3, true);
                        $step3          = curlPostHit($decrypt_encrypted_string_url, $headers, $dataStep3, true);
                        if(isset($step3['status']) && $step3['status'] == 'success')
                        {
                            $response       = $step3['response'];
                            $responseData   = json_decode($response, true);
                            $message        = isset($responseData['message']) ? $responseData['message'] : '';
                            $statusCode     = isset($responseData['status']) ? $responseData['status'] : '101';
                            if ((isset($responseData['status_code']) && $responseData['status_code'] === 200) || $responseData['status'] == 1)
                            {
                                $statusCode = 200;
                                $this->addHistoryChallanForApiList($vehicleNo, $jsonDataStep1, $vendor, $response);
                                $result   = ['status'=>'success','status_code'=>$statusCode, 'msg'=>'success', 'response'=>$response];
                            }
                            else{
                                $message    = isset($responseData['msg']) ? $responseData['msg'] : 'Failed';
                                $result     = ['status'=>'failed','status_code'=>$statusCode, 'msg'=>$message, 'response'=>$response];
                            }
                        }
                        else{
                            //Error
                            $result   = ['status'=>'failed','status_code'=>$step3['status_code'], 'msg'=>$step3['msg'], 'response'=>$response];
                        }
                    }
                    else{
                        //Error
                        $responseData = json_decode($step2['response'], true); 
                        $status = $responseData['status'];
                        $msg = $responseData['msg'];

                        $result   = ['status'=>'failed','status_code'=>$status, 'msg'=>$msg, 'response'=>$response];
                    }
                }
                else{
                    //Error
                    $responseData = json_decode($step1['response'], true); 
                    $status = $responseData['status'];
                    $msg = $responseData['msg'];

                    $result   = ['status'=>'failed','status_code'=>$status, 'msg'=>$msg, 'response'=>$response];
                }
                $returnArr = array_merge($addArr,$result);
                return $returnArr;
    }

    public function challanRto($vendor, $vehicleNo)
    {   
        $response   = '';
        $response_from =    1;
                $result     = [];
                $url                = Config::get('custom.rto.challan.url');
            $user_id =          Config::get('custom.rto.challan.user_id');
                $Authorization      = Config::get('custom.rto.challan.Authorization');
                $api_id             = Config::get('custom.rto.challan.api_id');
                $api_name           = Config::get('custom.rto.challan.api_name');
                $vendor             = Config::get('custom.rto.challan.vender');
        
                $headers = array(
                            'Authorization:'.$Authorization,
                            'Content-Type:application/json',
                        );
                $data = [
                        'vehicle_number' => $vehicleNo,
                        'user_id' => $user_id
                    ];
                $jsonData           = json_encode($data);
                //$curlRes            = $this->curlPostHit($url, $headers, $jsonData, true);
                $curlRes            = curlPostHit($url, $headers, $jsonData, true);
                // echo "<pre>";
                // print_r($curlRes);
                // exit;
                
                $addArr         = array('url'=> $url, 'requestData' =>$jsonData);
              
                if($curlRes['status'] == 'success')
                {
                    
                    $response       = $curlRes['response'];
                    $responseData   = json_decode($response, true);
                //      echo "<pre>";
                // print_r($responseData);
                // exit;
                    $message        = isset($responseData['message']) ? $responseData['message'] : '';
                    $statusCode     = isset($responseData['statusCode']) ? $responseData['statusCode'] : '';
                    $status        = isset($responseData['status']) ? $responseData['status'] : '';
                    if($statusCode == 200 || $status == 1  )
                    {
                        
                        $statusCode = 200;
                        $historyStatus = $this->addHistoryChallanForApiList($vehicleNo, $jsonData, $vendor, $response);
                        $result = array('status'=>'success','status_code' => $statusCode, 'msg'=> $message, 'response'=>$response);
                        // echo $historyStatus."<pre> inside : "; print_r($result);
                    }
                    else{
                        if($statusCode != 200 )
                        {
                        $error          = isset($responseData['error']) ? $responseData['error'] : ''; 
                        $statusCode     = isset($error['statusCode']) ? $error['statusCode'] : '';
                        $message        = isset($error['message']) ? $error['message'] : '';
                        $result = array('status'=>'failed','status_code' => $statusCode, 'msg'=> $message, 'response'=>$response);
                    }
                    }
                }
                else{
                    $result = array('status'=>'failed','status_code' => '', 'msg'=> $curlRes['msg'], 'response'=>$response);
                }
                
                $returnArr = array_merge($addArr,$result);
                // echo "<pre> returnArr : "; print_r($returnArr);
                return $returnArr;
    }


  
    

}