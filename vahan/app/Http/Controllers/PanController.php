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
class PanController extends Controller
{
    use CommonTraits;
    use ApisTraits;

    public function authbridgeViewPancard(){
        return view('pancard.pancard');
    }

    public function authbridgePancardPostData(Request $request)
    {       
        $sessionData = session('data');
        if($this->checkCredit() === false)
        {
            return response()->json(['status' => 'No more credit limit available']);
        }
        else{
        // return $request;
            $response_from = 1;
            $encrypted_string_url   = Config::get('custom.authbridge.pancard.encrypted_string_url');
            $utilitysearch_url      = Config::get('custom.authbridge.pancard.utilitysearch_url');
            $decrypt_encrypted_string_url      = Config::get('custom.authbridge.pancard.decrypt_encrypted_string_url');
            $username               = Config::get('custom.authbridge.pancard.username');
            $api_id                 = Config::get('custom.authbridge.pancard.api_id');
            $api_name               = Config::get('custom.authbridge.pancard.api_name');
            $vendor                 = Config::get('custom.authbridge.pancard.vender');
            // Get the vehicle number from the request
            $method = 'POST';
            $pancard = $request->input('input');
            $response = "";
            //-------------------------Start Check History-------------------------------------
            $response = $this->checkHistoryPan($pancard, $vendor);
            $dataStep1 = [
                'PanNumber' => $pancard,
                'transID' => '123456',
                'docType' => '523'
            ];

            $jsonDataStep1 = json_encode($dataStep1);
            
            if(empty($response))
            {
                //------------------------- Step1-------------------------------------
                

                $headers = array(
                    'username:'.$username,
                    'Content-Type: application/json'
                );
                // echo "<pre>";print_r($dataStep1);///die;
                // echo "<pre>";print_r($headers);die;
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
							
							//echo "<pre>"; print_r($responseData);die;
                            $message = isset($responseData['message']) ? $responseData['message'] : '';
                            $statusCode = isset($responseData['status_code']) ? $responseData['status_code'] : '';
                            $status_code = isset($responseData['status']) ? $responseData['status'] : '';
                            $remark = 'Response from Vendor API';
                            if($statusCode == 200 || $status_code == 1)
                            {   
                                $this->addHistorypan($pancard, $vendor, $jsonDataStep1, $response,$statusCode);
                                $return = $responseData;
                            }
                            else{
                                $this->addHistorypan($pancard, $vendor, $jsonDataStep1, $response,$statusCode);
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
            $api_log->input = $pancard;
            $api_log->response = $response;
            $api_log->response_from = $response_from;
            $api_log->status = '1';
            $api_log->method = $method;
            $api_log->save();
            return $return;

        }
    }

    public function checkHistoryPan($pan,$vendor){

        $returnArr = '';
        $result = DB::select("SELECT id, response FROM `history_pan` WHERE pan_no = '$pan' AND vendor = '$vendor' AND `status` IN (0,1) AND created_at >= 
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
    private function addHistorypan($pan,$vendor, $request, $response,$status_code)
    {
        $createdAt = now();
        return DB::table('history_pan')->insert([
            'pan_no' => $pan,
            'vendor' => $vendor,
            'request' => $request,
            'status_code' => $status_code,
            'response' => $response,
            'status' => 1,
            'created_at' => $createdAt,
        ]);
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
 
 
}
