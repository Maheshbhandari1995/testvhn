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


class LicenseController extends Controller
{
    // public function retrieveLicenseData(Request $request)
    // {       
    //     if($this->checkCredit() === false)
    //     {
    //         return response()->json(['status' => 'No more credit limit available']);
    //     }
    //     else{
   
    //         $apiUrl = Config::get('custom.license.license_url');
    //         $token = Config::get('custom.license.license_token');
    //         $api_name = 'license';
    //         $vendorname = 'sinzy';
    //         $method = 'POST';
    //         $dl_number = $request->input('dl');
    //         //echo $dl_number;
    //         $dob = $request->input('dob');
    //         $dob = date("m/d/Y", strtotime($dob));
    //         // $licensedate = $request->input('licensedate');
    //         // $licensedate = date("m/d/Y", strtotime($licensedate));
           
    //         $data = array(
    //             "number" => $dl_number,
    //             "dob" => $dob
    //             //"issueDate" => $licensedate
    //         );
            
    //         $jsonData = json_encode($data);


    //         $headers = array(
    //             'token: ' . $token,
    //             'Content-Type: application/json',
    //             'Content-Length: ' . strlen($jsonData)
    //         );

    //         $curl = curl_init($apiUrl);
    //         curl_setopt($curl, CURLOPT_CUSTOMREQUEST, $method);
    //         curl_setopt($curl, CURLOPT_POSTFIELDS, $jsonData);
    //         curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
    //         curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
    //         // $response = curl_exec($curl);
    //         // $httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);

    //         try {
    //             $response = curl_exec($curl);
    //             $httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
            
    //             // if ($httpCode === 200) {
    //                 $responseData = json_decode($response, true);
            
    //                 if (is_array($responseData) && !empty($responseData)) {

    //                     $api_log = new Rcdetails();
    //                     $sessionData = session('data');
    //                     $api_log->user_id = $sessionData['userID'];
    //                     $api_log->client_id = $sessionData['Client_id'];
    //                     $api_log->client_name = $sessionData['clientName'];
    //                     $api_log->api_name = $api_name;
    //                     $api_log->vender = $vendorname;
    //                     $api_log->api_url =  $apiUrl;
    //                     $api_log->request = $jsonData;
    //                     $api_log->response = json_encode($responseData); // Convert object to JSON string
    //                        // Access the statusCode value
    //                     $statusCode = isset($responseData['error']['statusCode']) ? $responseData['error']['statusCode'] : 200;
    //                     $api_log->response_status_code = $statusCode;

    //                     $api_log->status = '1';
    //                     $api_log->method = $method;
    //                     $api_log->save();

    //                     return json_encode($responseData);

    //                 } else {
    //                     return response()->json(['status' => 'No Record Found!']);
    //                 }
    //             // } else {
    //             //     return response()->json(['error' => $response], $httpCode);
    //             // }
    //         } catch (\Exception $e) {
    //             return response()->json(['error' => $e->getMessage()]);
    //         } finally {
    //             curl_close($curl);
    //         }
    //     }
            
    // }

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

    public function retrieveSignzyLicenseData(Request $request){
        if($this->checkCredit() === false)
        {
            return response()->json(['status' => 'No more credit limit available']);
        }
        else{
   
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
            $dob = date("m/d/Y", strtotime($dob));
            // $licensedate = $request->input('licensedate');
            // $licensedate = date("m/d/Y", strtotime($licensedate));
            $hitoryResp = $this->checkHistoryLicense($dl_number, $vendorname);
            if (!empty($hitoryResp)) {
                return $hitoryResp;
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
                // $response = curl_exec($curl);
                // $httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);

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
                        $api_log->response = json_encode($responseData); // Convert object to JSON string
                        // Access the statusCode value
                        $statusCode = isset($responseData['error']['statusCode']) ? $responseData['error']['statusCode'] : 200;
                        $api_log->response_status_code = $statusCode;
                        $api_log->status = '1';
                        $api_log->method = $method;
                        $api_log->save();
                
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
                        $api_log->response = json_encode($responseData); // Convert object to JSON string
                        // Access the statusCode value
                        $statusCode = isset($responseData['error']['statusCode']) ? $responseData['error']['statusCode'] : 200;
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
                    $api_log->response = $e->getMessage();
                    $api_log->response_status_code = 500; // Assuming 500 for error status code
                    $api_log->status = '1';
                    $api_log->method = $method;
                    $api_log->save();
                
                    return response()->json(['error' => $e->getMessage()]);
                } finally {
                    curl_close($curl);
                }
                // try {
                //     $response = curl_exec($curl);
                //     $httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
                
                //     // if ($httpCode === 200) {
                //         $responseData = json_decode($response, true);
                
                //         if (is_array($responseData) && !empty($responseData)) {

                //             $isResultSet = isset($responseData['response']['result']);
                //             if ($isResultSet == true) {
                //                     $createdAt = now();
                //                     $insertData = [
                //                         'license_no' => $dl_number,
                //                         'vendor' => $vendorname,
                //                         'request' => $jsonData,
                //                         'response' => $response,
                //                         'status' => 1,
                //                         'created_at' => $createdAt,
                //                     ];
                //                     $inserted = DB::table('history_license')->insert($insertData);
                //                     //$return = $responseData;
                //             }else if($isResultSet == true || $isResultSet == false){
                //                 $api_log = new Rcdetails();
                //                 $sessionData = session('data');
                //                 $api_log->user_id = $sessionData['userID'];
                //                 $api_log->client_id = $sessionData['Client_id'];
                //                 $api_log->client_name = $sessionData['clientName'];
                //                 $api_log->api_name = $api_name;
                //                 $api_log->vender = $vendorname;
                //                 $api_log->api_url =  $apiUrl;
                //                 $api_log->request = $jsonData;
                //                 $api_log->response = json_encode($responseData); // Convert object to JSON string
                //                 // Access the statusCode value
                //                 $statusCode = isset($responseData['error']['statusCode']) ? $responseData['error']['statusCode'] : 200;
                //                 $api_log->response_status_code = $statusCode;
                //                 //$api_log->api_id = $api_id;
                //                 $api_log->status = '1';
                //                 $api_log->method = $method;
                //                 $api_log->save();
                //             }

                //             return json_encode($responseData);

                //         } else {
                //             return response()->json(['status' => 'No Record Found!']);
                //         }
                //     // } else {
                //     //     return response()->json(['error' => $response], $httpCode);
                //     // }
                // } catch (\Exception $e) {
                //     return response()->json(['error' => $e->getMessage()]);
                // } finally {
                //     curl_close($curl);
                // }
            }
        }
            
    }

    private function checkHistoryLicense($dl_number, $vendorname)
    {
        $returnArr = '';
       // $sevenDaysAgo = Carbon::now()->subDays(7)->toDateString();
        $result = DB::select("SELECT id, response FROM `history_license` WHERE license_no = '$dl_number' AND vendor = '$vendorname' AND `status` IN (0,1) AND created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY) ORDER BY id DESC LIMIT 1");
        if (!empty($result)) {
           
            $returnArr = $result[0]->response;
        }
        else{
            DB::table('history_license')
            ->where('license_no', $dl_number)
            ->where('vendor', $vendorname)
            ->whereIn('status', [0, 1])
            ->delete();
            $returnArr = '';
        }
        
        return $returnArr;
    }

   
}
