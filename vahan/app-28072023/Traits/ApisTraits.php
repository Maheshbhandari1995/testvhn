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
        return $result = array('status'=>'failed','status_code' => '', 'msg'=>'Temporary', 'response'=>'');
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

        $step1 = $this->curlPostHit($encrypted_string_url, $headers, $jsonDataStep1, true);
        if(isset($step1['status']) && $step1['status'] == 'success')
        {
            $dataStep2      = ['requestData' => $step1['response']];
            $jsonDataStep2  = json_encode($dataStep2);
            $headers        = ['username:' . $username,'Content-Type: application/json'];
            $step2          = $this->curlPostHit($utilitysearch_url, $headers, $jsonDataStep2, true);

            if(isset($step2['status']) && $step2['status'] == 'success')
            {
                $dataStep3      = $step2['response'];
                $headers        = ['username:' . $username,'Content-Type: application/json'];
                $step3          = $this->curlPostHit($decrypt_encrypted_string_url, $headers, $dataStep3, true);
                if(isset($step3['status']) && $step3['status'] == 'success')
                {
                    $response       = $step3['response'];
                    $responseData   = json_decode($response, true);
                    $message        = isset($responseData['message']) ? $responseData['message'] : '';
                    $statusCode     = isset($responseData['status_code']) ? $responseData['status_code'] : '101';
                    if (isset($responseData['status_code']) && $responseData['status_code'] === 200) {
                        $this->addHistoryRCForApiList($vehicleNo, $jsonDataStep1, $vendor, $response);
                        $result   = ['status'=>'success','status_code'=>$statusCode, 'msg'=>$message, 'response'=>$response];
                    }
                    else{
                        //Error
                        $result   = ['status'=>'failed','status_code'=>$statusCode, 'msg'=>$message, 'response'=>$response];
                    }
                }
                else{
                    //Error
                    $result   = ['status'=>'failed','status_code'=>'', 'msg'=>$step3['msg'], 'response'=>$response];
                }
            }
            else{
                //Error
                $result   = ['status'=>'failed','status_code'=>'', 'msg'=>$step2['msg'], 'response'=>$response];
            }
        }
        else{
            //Error
            $result   = ['status'=>'failed','status_code'=>'', 'msg'=>$step1['msg'], 'response'=>$response];
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
        $curlRes            = $this->curlPostHit($url, $headers, $jsonData, true);
        
        $addArr         = array('url'=> $url, 'requestData' =>$jsonData);
      
        if(isset($curlRes['status']) && $curlRes['status'] == 'success')
        {
            // echo "<pre> headers : "; print_r($curlRes);
            $response       = $curlRes['response'];
            $responseData   = json_decode($response, true);
            $message        = isset($responseData['message']) ? $responseData['message'] : '';
            $statusCode     = isset($responseData['status_code']) ? $responseData['status_code'] : '';
            if($statusCode == 200 || !empty($responseData['result']))
            {
                
                $statusCode = 200;
                $historyStatus = $this->addHistoryRCForApiList($vehicleNo, $vendor, $jsonData, $response);
                $result = array('status'=>'success','status_code' => $statusCode, 'msg'=> $message, 'response'=>$response);
                // echo $historyStatus."<pre> inside : "; print_r($result);
            }
            else{
                $result = array('status'=>'failed','status_code' => $statusCode, 'msg'=> $message, 'response'=>$response);
            }
        }
        else{
            $result = array('status'=>'failed','status_code' => '', 'msg'=> $curlRes['msg'], 'response'=>$response);
        }
        
        $returnArr = array_merge($addArr,$result);
        // echo "<pre> returnArr : "; print_r($returnArr);
        return $returnArr;
    }
    
    public function curlPostHit($url, $header, $postFields, $post = true)
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
    public function addHistoryRCForApiList($vehicleNo, $request, $vendor, $response)
    { 
        return DB::table('history_rc')->insert([
            'vehicle_no' => $vehicleNo,
            'vendor' => $vendor,
            'request' => $request,
            'response' => $response,
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

        $returnArr = '';
        $result = DB::select("SELECT response FROM `history_rc` WHERE vehicle_no = '$vehicleNo' AND vendor IN ('$vendorStr') AND `status` IN (0,1) AND created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY) ORDER BY id DESC LIMIT 1");
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
        // echo "<pre>returnArr : "; print_r($returnArr);die; 
        return $returnArr;
    }
}