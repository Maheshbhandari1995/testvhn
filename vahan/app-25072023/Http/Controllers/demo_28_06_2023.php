<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use App\Models\Company;
use App\Models\Rcdetails;
use App\Models\Module;
use App\Models\Bulkfilelog;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Http;
use PhpParser\Node\Expr\Print_;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use App\Traits\CommonTraits;
use Carbon\Carbon;

class BulkUploadController extends Controller
{
    use CommonTraits;

    public function authbridgeViewRC()
    {
        //echo "123"; die;
        //$users = Users::whereIn('status', [0,1,2])->get(); //compact('users')
        return view('rc.rc_bulk');
    }

    public function authbridgeRCBulkData(Request $request)
{
    // Get the uploaded file
    $file = $request->file('rcdata');

    // Check if the file was uploaded successfully
    if ($file->isValid()) {
        // Get the path of the uploaded file
        $filePath = $file->path();

        // Read the contents of the file
        $vehicleNumbers = file($filePath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

        $results = [];

        // Set the configuration values
        $encrypted_string_url = Config::get('custom.authbridge.rc.encrypted_string_url');
        $utilitysearch_url = Config::get('custom.authbridge.rc.utilitysearch_url');
        $decrypt_encrypted_string_url = Config::get('custom.authbridge.rc.decrypt_encrypted_string_url');
        $username = Config::get('custom.authbridge.rc.username');
        $api_id = Config::get('custom.authbridge.rc.api_id');
        $api_name = Config::get('custom.authbridge.rc.api_name');
        $vendor = Config::get('custom.authbridge.rc.vender');
        $method = 'POST';

        foreach ($vehicleNumbers as $vehicleNo) {
            // Validate the vehicle number
            $isValidVehicleNumber = $this->validateVehicleNumber($vehicleNo);
            if ($isValidVehicleNumber === false) {
                $results[] = ['vehicleNo' => $vehicleNo, 'status' => 'Please enter a valid vehicle number'];
                continue;
            }

            // Check history for the vehicle
            $historyResp = $this->checkHistoryRC($vehicleNo, $vendor);

            //     return $historyResp;
            // die;

            if (empty($historyResp)) {
                // Step 1
                $dataStep1 = [];

                // Step 1
                $dataStep1['docNumber'] = $vehicleNo;
                $dataStep1['transID'] = '1234567';
                $dataStep1['docType'] = '372';

                // $jsonDataStep1 = json_encode($dataStep1);

                // $jsonDataStep1 = null;

                $jsonDataStep1 = json_encode($dataStep1);

                // return $jsonDataStep1;
                // die;

                $headers = [
                    'username:' . $username,
                    'Content-Type: application/json'
                ];

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
                    $results[] = ['vehicleNo' => $vehicleNo, 'status_code' => $statusCode, 'message' => $message];
                    continue;
                }

                // Step 2
                $dataStep2 = [
                    'requestData' => $responseStep1,
                ];

                $jsonDataStep2 = json_encode($dataStep2);

                // return $jsonDataStep2;
                // die;


                $headers = [
                    'username:' . $username,
                    'Content-Type: application/json'
                ];

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
                    $results[] = ['vehicleNo' => $vehicleNo, 'status_code' => $statusCode, 'message' => $message];
                    continue;
                }

                $headers = [
                    'username:' . $username,
                    'Content-Type: application/json'
                ];

                $curlStep3 = curl_init();
                curl_setopt($curlStep3, CURLOPT_URL, $decrypt_encrypted_string_url);
                curl_setopt($curlStep3, CURLOPT_POST, true);
                curl_setopt($curlStep3, CURLOPT_POSTFIELDS, $responseStep2);
                curl_setopt($curlStep3, CURLOPT_HTTPHEADER, $headers);
                curl_setopt($curlStep3, CURLOPT_RETURNTRANSFER, true);
                $responseStep3 = curl_exec($curlStep3);

                // return $responseStep3;
                // die;

                if ($responseStep3 === false) {
                    $message = curl_error($curlStep3);
                    $statusCode = curl_errno($curlStep3);
                    $remark = 'Curl Error';
                    $results[] = ['vehicleNo' => $vehicleNo, 'status_code' => $statusCode, 'message' => $message];
                    continue;
                }

                $decodedResponseStep3 = json_decode($responseStep3, true);

                if ($decodedResponseStep3['status_code'] === 200) {
                    $results[] = ['data' => $decodedResponseStep3];
                    // 'vehicleNo' => $vehicleNo, 'data' =>
                    // return $results;
                    // die

                    $sessionData = session('data');
                    $this->updateUtilizedCredit($sessionData['Client_id']);

                    $csvData = [];
                    $csvData[] = ['S.No.',	'Input RC Number'	,'Transaction ID'	,'Vehicle Category'	,'Vehicle Class',	'Fuel Type',	'Chassis Number',	'Engine Number',	'Manufacture Date',	'Model / Makers Class Date'	,'Maker/Manufacturer','Engine Capacity	Color'	,'Gross Weight',	'No of cylinder'	,'Seating Capacity',	'sleeper Capacity'	,'Norms Type',	'Body Type',	'Owner Serial Number'	,'Mobile Number'	,'Unloading Weight'	,'Rc Standard Cap'	,'Vehicle Standing Capacity',	'Vehicle Number',	'Blacklist Status'	,'Is Commercial',	'Noc Details',	'Registration Number'	,'Registration Date',	'Fitness Date/RC Expiry Date',	'RTO',	'Tax Upto',	'Vehicle Tax Up to'	,'Status'	,'Status As On'	,'Owners Name',	'Father Name/Husband Name',	'Permanent Address'	,'Present Address',	'Financer Name'	,'Insurance To Date/Insurance Upto',	'Policy Number'	,'Insurance Company',	'PUCC NO'	,'PUCC Upto',	'Permit Issue Date',	'Permit Number',	'Permit Type',	'Permit Vald From'	,'Permit Valid Upto',	'Non Use Status'	,'Non Use From',	'Non Use To',	'National Permit Number',	'National Permit Upto',	'National Permit Issued By','test'];

                    foreach ($results as $index => $data) {
                        $msg = $data['data']['msg'];
                    
                        $csvData[] = [
                            $index + 1, // S.No.
                            isset($msg['Registration Details']['Registration Number']) ? $msg['Registration Details']['Registration Number'] : null, // Input RC Number
                            isset($data['data']['ts_transaction_id']) ? $data['data']['ts_transaction_id']  : null, // Transaction ID
                            isset($msg['Vehicle Details']['Vehicle Category']) ? $msg['Vehicle Details']['Vehicle Category']  : null, // Vehicle Category
                            isset($msg['Vehicle Details']['Vehicle Class']) ? $msg['Vehicle Details']['Vehicle Class'] : null, // Vehicle Class
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
                            isset($msg['RC Status']['National Permit Issued By']) ? $msg['RC Status']['National Permit Issued By'] : null // National Permit Issued By
                        ];

                        
                        $message = curl_error($curlStep3);
                        $statusCode = curl_errno($curlStep3);
                        $api_log =  new Rcdetails();
                        $api_log->api_id = $api_id;
                        $api_log->api_name = $api_name;
                        $api_log->vender = $vendor;
                        $api_log->user_id = $sessionData['userID'];
                        $api_log->client_id = $sessionData['Client_id'];
                        $api_log->client_name = $sessionData['clientName'];
                        $api_log->response_status_code = 200;
                        $api_log->response_message  = 'success';
                        $api_log->remark  = isset($remark) ? $remark : 'record from vendor api';
                        $api_log->api_url = $decrypt_encrypted_string_url;
                        $api_log->request  = isset($msg['Registration Details']['Registration Number']) ? $msg['Registration Details']['Registration Number'] : null ;
                        $api_log->response = json_encode($data);
                        $api_log->status = '1';
                        $api_log->method = $method;
                        $api_log->save();
                        
                    }


                } else {
                    $results[] = ['vehicleNo' => $vehicleNo, 'status' => 'Error: ' . $decodedResponseStep3['statusDesc']];
                }
            } else {
                 $results = ['data' => $historyResp];

                 $sessionData = session('data');
                 $this->updateUtilizedCredit($sessionData['Client_id']);
         
                 $csvData = [];
                 $csvData[] = ['S.No.',	'Input RC Number'	,'Transaction ID'	,'Vehicle Category'	,'Vehicle Class',	'Fuel Type',	'Chassis Number',	'Engine Number',	'Manufacture Date',	'Model / Makers Class Date'	,'Maker/Manufacturer','Engine Capacity	Color'	,'Gross Weight',	'No of cylinder'	,'Seating Capacity',	'sleeper Capacity'	,'Norms Type',	'Body Type',	'Owner Serial Number'	,'Mobile Number'	,'Unloading Weight'	,'Rc Standard Cap'	,'Vehicle Standing Capacity',	'Vehicle Number',	'Blacklist Status'	,'Is Commercial',	'Noc Details',	'Registration Number'	,'Registration Date',	'Fitness Date/RC Expiry Date',	'RTO',	'Tax Upto',	'Vehicle Tax Up to'	,'Status'	,'Status As On'	,'Owners Name',	'Father Name/Husband Name',	'Permanent Address'	,'Present Address',	'Financer Name'	,'Insurance To Date/Insurance Upto',	'Policy Number'	,'Insurance Company',	'PUCC NO'	,'PUCC Upto',	'Permit Issue Date',	'Permit Number',	'Permit Type',	'Permit Vald From'	,'Permit Valid Upto',	'Non Use Status'	,'Non Use From',	'Non Use To',	'National Permit Number',	'National Permit Upto',	'National Permit Issued By','test'];

                 //$response = json_decode($results['data'], true);
                 
                 $response = json_decode($results['data'], true);

                $data = $response;

                    // return $data[0]['data']['msg']['Registration Details']['Registration Number'];
                    //     die;
                    //     exit;
                    foreach ($data[0] as $item) {

                            $msg = $item['msg'];

                            $csvData[] = [
                            null, // S.No.
                            isset($msg['Registration Details']['Registration Number']) ? $msg['Registration Details']['Registration Number'] : null, // Input RC Number
                            isset($data['data']['ts_transaction_id']) ? $data['data']['ts_transaction_id']  : null, // Transaction ID
                            isset($msg['Vehicle Details']['Vehicle Category']) ? $msg['Vehicle Details']['Vehicle Category']  : null, // Vehicle Category
                            isset($msg['Vehicle Details']['Vehicle Class']) ? $msg['Vehicle Details']['Vehicle Class'] : null, // Vehicle Class
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
                            isset($msg['RC Status']['National Permit Issued By']) ? $msg['RC Status']['National Permit Issued By'] : null // National Permit Issued By
                        ];
                        //echo $Data['Registration Number'];

                        $api_log =  new Rcdetails();
                        $api_log->api_id = $api_id;
                        $api_log->api_name = $api_name;
                        $api_log->vender = $vendor;
                        $api_log->user_id = $sessionData['userID'];
                        $api_log->client_id = $sessionData['Client_id'];
                        $api_log->client_name = $sessionData['clientName'];
                        $api_log->response_status_code = 200;
                        $api_log->response_message  = 'success';
                        $api_log->remark  = 'record from vendor api';
                        $api_log->api_url = $decrypt_encrypted_string_url;
                        $api_log->request  = isset($msg['Registration Details']['Registration Number']) ? $msg['Registration Details']['Registration Number'] : null ;
                        $api_log->response = json_encode($results);
                        $api_log->status = '1';
                        $api_log->method = $method;
                        $api_log->save();
                }
                
            }
        }

        
        $value_result = $this->addHistoryRC($vehicleNumbers, $vendor, $results);

        $timestamp = date('Y_m_d_His');
        $filename = 'rcdata_' . $timestamp . '.csv';
        
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"$filename\""
        ];
        
        $tempFilePath = tempnam(sys_get_temp_dir(), 'rcdata');
        $tempFile = fopen($tempFilePath, 'w');
        
        foreach ($csvData as $csvRow) {
            fputcsv($tempFile, $csvRow);
        }
        
        fclose($tempFile);

        $url = request()->root();
        $parsedUrl = parse_url($url);
        $baseUrl = $parsedUrl['scheme'] . '://' . $parsedUrl['host'] . $parsedUrl['path'];
        
        $filePath = storage_path("app/public/uploads/rcbulk/$filename");
        $file_url = $baseUrl."/public/storage/uploads/rcbulk/".$filename;
        
        rename($tempFilePath, $filePath);

        $bulk_log_save = $this->addBulkLog($filename,$vehicleNumbers,$file_url);

        return response()->json(['download' => '1','file_url' => $file_url,'file_name' => $filename], 200);

    } else {
        return response()->json(['error' => 'File upload failed']);
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
    
    // Validate the vehicle number
    private function validateVehicleNumber($vehicleNumber)
    {
        // Regular expression pattern for vehicle number validation
        $regex = '/^[A-Z]{2}[0-9]{1,2}[A-Z]{1,2}[0-9]{1,4}$/';
    
        // Test the vehicle number against the regex pattern
        $isValid = preg_match($regex, $vehicleNumber);
    
        return $isValid === 1;
    }

    // Validate the vehicle number
    private function validateChassisNumber($Number)
    {
        // Regular expression pattern for vehicle number validation
        $regex = '/^[A-HJ-NPR-Z0-9]{17}$/i';
    
        // Test the vehicle number against the regex pattern
        $isValid = preg_match($regex, $Number);
    
        return $isValid === 1;
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

    private function addHistoryRC($vehicleNumbers, $vendor, $results)
    {
        $createdAt = now();

        $vehicleNo = json_encode($vehicleNumbers);
        $result = json_encode($results);

        return DB::table('history_rc')->insert([
            'vehicle_no' => $vehicleNo,
            'vendor' => $vendor,
            'request' => $vehicleNo,
            'response' => $result,
            'status' => 1,
            'created_at' => $createdAt,
        ]);
    }

    public function addBulkLog($filename,$vehicleNumbers,$file_url)
    {
        $bulklog =  new Bulkfilelog();
        $bulklog->filename = $filename;
        $bulklog->count = count($vehicleNumbers);
        $bulklog->downloadurl = $file_url;
        $bulklog->status = '1';
        $bulklog->save();
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


//////////////////////////////////////////////////////////

<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use App\Models\Company;
use App\Models\Rcdetails;
use App\Models\Module;
use App\Models\Bulkfilelog;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Http;
use PhpParser\Node\Expr\Print_;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use App\Traits\CommonTraits;
use Carbon\Carbon;

class BulkUploadController extends Controller
{
    use CommonTraits;

    public function authbridgeViewRC()
    {
        //echo "123"; die;
        //$users = Users::whereIn('status', [0,1,2])->get(); //compact('users')
        return view('rc.rc_bulk');
    }

    public function authbridgeRCBulkData(Request $request)
{
    // Get the uploaded file
    $file = $request->file('rcdata');

    // Check if the file was uploaded successfully
    if ($file->isValid()) {
        // Get the path of the uploaded file
        $filePath = $file->path();

        // Read the contents of the file
        $vehicleNumbers = file($filePath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

        $results = [];

        // Set the configuration values
        $encrypted_string_url = Config::get('custom.authbridge.rc.encrypted_string_url');
        $utilitysearch_url = Config::get('custom.authbridge.rc.utilitysearch_url');
        $decrypt_encrypted_string_url = Config::get('custom.authbridge.rc.decrypt_encrypted_string_url');
        $username = Config::get('custom.authbridge.rc.username');
        $api_id = Config::get('custom.authbridge.rc.api_id');
        $api_name = Config::get('custom.authbridge.rc.api_name');
        $vendor = Config::get('custom.authbridge.rc.vender');
        $method = 'POST';

        foreach ($vehicleNumbers as $vehicleNo) {
            // Validate the vehicle number
            $isValidVehicleNumber = $this->validateVehicleNumber($vehicleNo);
            if ($isValidVehicleNumber === false) {
                $results[] = ['vehicleNo' => $vehicleNo, 'status' => 'Please enter a valid vehicle number'];
                continue;
            }

            // Check history for the vehicle
            $historyResp = $this->checkHistoryRC($vehicleNo, $vendor);

            //     return $historyResp;
            // die;

            if (empty($historyResp)) {
                // Step 1
                $dataStep1 = [];

                // Step 1
                $dataStep1['docNumber'] = $vehicleNo;
                $dataStep1['transID'] = '1234567';
                $dataStep1['docType'] = '372';

                // $jsonDataStep1 = json_encode($dataStep1);

                // $jsonDataStep1 = null;

                $jsonDataStep1 = json_encode($dataStep1);

                // return $jsonDataStep1;
                // die;

                $headers = [
                    'username:' . $username,
                    'Content-Type: application/json'
                ];

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
                    $results[] = ['vehicleNo' => $vehicleNo, 'status_code' => $statusCode, 'message' => $message];
                    continue;
                }

                // Step 2
                $dataStep2 = [
                    'requestData' => $responseStep1,
                ];

                $jsonDataStep2 = json_encode($dataStep2);

                // return $jsonDataStep2;
                // die;


                $headers = [
                    'username:' . $username,
                    'Content-Type: application/json'
                ];

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
                    $results[] = ['vehicleNo' => $vehicleNo, 'status_code' => $statusCode, 'message' => $message];
                    continue;
                }

                $headers = [
                    'username:' . $username,
                    'Content-Type: application/json'
                ];

                $curlStep3 = curl_init();
                curl_setopt($curlStep3, CURLOPT_URL, $decrypt_encrypted_string_url);
                curl_setopt($curlStep3, CURLOPT_POST, true);
                curl_setopt($curlStep3, CURLOPT_POSTFIELDS, $responseStep2);
                curl_setopt($curlStep3, CURLOPT_HTTPHEADER, $headers);
                curl_setopt($curlStep3, CURLOPT_RETURNTRANSFER, true);
                $responseStep3 = curl_exec($curlStep3);

                // return $responseStep3;
                // die;

                if ($responseStep3 === false) {
                    $message = curl_error($curlStep3);
                    $statusCode = curl_errno($curlStep3);
                    $remark = 'Curl Error';
                    $results[] = ['vehicleNo' => $vehicleNo, 'status_code' => $statusCode, 'message' => $message];
                    continue;
                }

                $decodedResponseStep3 = json_decode($responseStep3, true);

                if ($decodedResponseStep3['status_code'] === 200) {
                    $results[] = ['data' => $decodedResponseStep3];
                    // 'vehicleNo' => $vehicleNo, 'data' =>
                    // return $results;
                    // die
                    // $value_result = $this->addHistoryRC($vehicleNo,$jsonDataStep1, $vendor, $responseStep3);
                    $sessionData = session('data');
                    $this->updateUtilizedCredit($sessionData['Client_id']);

                    $csvData = [];
                    $csvData[] = ['S.No.',	'Input RC Number'	,'Transaction ID'	,'Vehicle Category'	,'Vehicle Class',	'Fuel Type',	'Chassis Number',	'Engine Number',	'Manufacture Date',	'Model / Makers Class Date'	,'Maker/Manufacturer','Engine Capacity	Color'	,'Gross Weight',	'No of cylinder'	,'Seating Capacity',	'sleeper Capacity'	,'Norms Type',	'Body Type',	'Owner Serial Number'	,'Mobile Number'	,'Unloading Weight'	,'Rc Standard Cap'	,'Vehicle Standing Capacity',	'Vehicle Number',	'Blacklist Status'	,'Is Commercial',	'Noc Details',	'Registration Number'	,'Registration Date',	'Fitness Date/RC Expiry Date',	'RTO',	'Tax Upto',	'Vehicle Tax Up to'	,'Status'	,'Status As On'	,'Owners Name',	'Father Name/Husband Name',	'Permanent Address'	,'Present Address',	'Financer Name'	,'Insurance To Date/Insurance Upto',	'Policy Number'	,'Insurance Company',	'PUCC NO'	,'PUCC Upto',	'Permit Issue Date',	'Permit Number',	'Permit Type',	'Permit Vald From'	,'Permit Valid Upto',	'Non Use Status'	,'Non Use From',	'Non Use To',	'National Permit Number',	'National Permit Upto',	'National Permit Issued By','test'];

                    foreach ($results as $index => $data) {
                        $msg = $data['data']['msg'];
                    
                        $csvData[] = [
                            $index + 1, // S.No.
                            isset($msg['Registration Details']['Registration Number']) ? $msg['Registration Details']['Registration Number'] : null, // Input RC Number
                            isset($data['data']['ts_transaction_id']) ? $data['data']['ts_transaction_id']  : null, // Transaction ID
                            isset($msg['Vehicle Details']['Vehicle Category']) ? $msg['Vehicle Details']['Vehicle Category']  : null, // Vehicle Category
                            isset($msg['Vehicle Details']['Vehicle Class']) ? $msg['Vehicle Details']['Vehicle Class'] : null, // Vehicle Class
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
                            isset($msg['RC Status']['National Permit Issued By']) ? $msg['RC Status']['National Permit Issued By'] : null // National Permit Issued By
                        ];

                        $value_result = $this->addHistoryRC($vehicleNo,$jsonDataStep1, $vendor, $data);

                        $message = curl_error($curlStep3);
                        $statusCode = curl_errno($curlStep3);
                        $api_log =  new Rcdetails();
                        $api_log->api_id = $api_id;
                        $api_log->api_name = $api_name;
                        $api_log->vender = $vendor;
                        $api_log->user_id = $sessionData['userID'];
                        $api_log->client_id = $sessionData['Client_id'];
                        $api_log->client_name = $sessionData['clientName'];
                        $api_log->response_status_code = 200;
                        $api_log->response_message  = 'success';
                        $api_log->remark  = isset($remark) ? $remark : 'record from vendor api';
                        $api_log->api_url = $decrypt_encrypted_string_url;
                        $api_log->request  = isset($msg['Registration Details']['Registration Number']) ? $msg['Registration Details']['Registration Number'] : null ;
                        $api_log->response = json_encode($data);
                        $api_log->status = '1';
                        $api_log->method = $method;
                        $api_log->save();
                        
                    }


                } else {
                    $results[] = ['vehicleNo' => $vehicleNo, 'status' => 'Error: ' . $decodedResponseStep3['statusDesc']];
                }
            } else {
                 $results = ['data' => $historyResp];

                 $sessionData = session('data');
                 $this->updateUtilizedCredit($sessionData['Client_id']);
         
                 $csvData = [];
                 $csvData[] = ['S.No.',	'Input RC Number'	,'Transaction ID'	,'Vehicle Category'	,'Vehicle Class',	'Fuel Type',	'Chassis Number',	'Engine Number',	'Manufacture Date',	'Model / Makers Class Date'	,'Maker/Manufacturer','Engine Capacity	Color'	,'Gross Weight',	'No of cylinder'	,'Seating Capacity',	'sleeper Capacity'	,'Norms Type',	'Body Type',	'Owner Serial Number'	,'Mobile Number'	,'Unloading Weight'	,'Rc Standard Cap'	,'Vehicle Standing Capacity',	'Vehicle Number',	'Blacklist Status'	,'Is Commercial',	'Noc Details',	'Registration Number'	,'Registration Date',	'Fitness Date/RC Expiry Date',	'RTO',	'Tax Upto',	'Vehicle Tax Up to'	,'Status'	,'Status As On'	,'Owners Name',	'Father Name/Husband Name',	'Permanent Address'	,'Present Address',	'Financer Name'	,'Insurance To Date/Insurance Upto',	'Policy Number'	,'Insurance Company',	'PUCC NO'	,'PUCC Upto',	'Permit Issue Date',	'Permit Number',	'Permit Type',	'Permit Vald From'	,'Permit Valid Upto',	'Non Use Status'	,'Non Use From',	'Non Use To',	'National Permit Number',	'National Permit Upto',	'National Permit Issued By','test'];

                 //$response = json_decode($results['data'], true);
                 
                 $response = json_decode($results['data'], true);

                //  return $response['data']['msg'];
                //  die;

                    $data = $response;
//  
                    // $msg = $data;
                    // return  $msg['data']['msg'];
                    // exit;
                    // exit;
                    // return $data[0]['data']['msg']['Registration Details']['Registration Number'];
                    //     die;
                    //     exit;
                   foreach ($data as $datas) {

                        // print_r($datas['msg']);
                        // exit;

                             $msg = $datas['msg'];

                            // return $msg;
                            // exit;

                            $csvData[] = [
                            null, // S.No.
                            isset($msg['Registration Details']['Registration Number']) ? $msg['Registration Details']['Registration Number'] : null, // Input RC Number
                            isset($data['data']['ts_transaction_id']) ? $data['data']['ts_transaction_id']  : null, // Transaction ID
                            isset($msg['Vehicle Details']['Vehicle Category']) ? $msg['Vehicle Details']['Vehicle Category']  : null, // Vehicle Category
                            isset($msg['Vehicle Details']['Vehicle Class']) ? $msg['Vehicle Details']['Vehicle Class'] : null, // Vehicle Class
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
                            isset($msg['RC Status']['National Permit Issued By']) ? $msg['RC Status']['National Permit Issued By'] : null // National Permit Issued By
                        ];
                        //echo $Data['Registration Number'];

                        $api_log =  new Rcdetails();
                        $api_log->api_id = $api_id;
                        $api_log->api_name = $api_name;
                        $api_log->vender = $vendor;
                        $api_log->user_id = $sessionData['userID'];
                        $api_log->client_id = $sessionData['Client_id'];
                        $api_log->client_name = $sessionData['clientName'];
                        $api_log->response_status_code = 200;
                        $api_log->response_message  = 'success';
                        $api_log->remark  = 'record from vendor api';
                        $api_log->api_url = $decrypt_encrypted_string_url;
                        $api_log->request  = isset($msg['Registration Details']['Registration Number']) ? $msg['Registration Details']['Registration Number'] : null ;
                        $api_log->response = json_encode($results);
                        $api_log->status = '1';
                        $api_log->method = $method;
                        $api_log->save();
                }
                
            }
        }

    

        $timestamp = date('Y_m_d_His');
        $filename = 'rcdata_' . $timestamp . '.csv';
        
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"$filename\""
        ];
        
        $tempFilePath = tempnam(sys_get_temp_dir(), 'rcdata');
        $tempFile = fopen($tempFilePath, 'w');
        
        foreach ($csvData as $csvRow) {
            fputcsv($tempFile, $csvRow);
        }
        
        fclose($tempFile);

        $url = request()->root();
        $parsedUrl = parse_url($url);
        $baseUrl = $parsedUrl['scheme'] . '://' . $parsedUrl['host'] . $parsedUrl['path'];
        
        $filePath = storage_path("app/public/uploads/rcbulk/$filename");
        $file_url = $baseUrl."/public/storage/uploads/rcbulk/".$filename;
        
        rename($tempFilePath, $filePath);

        $bulk_log_save = $this->addBulkLog($filename,$vehicleNumbers,$file_url);

        return response()->json(['download' => '1','file_url' => $file_url,'file_name' => $filename], 200);

    } else {
        return response()->json(['error' => 'File upload failed']);
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
    
    // Validate the vehicle number
    private function validateVehicleNumber($vehicleNumber)
    {
        // Regular expression pattern for vehicle number validation
        $regex = '/^[A-Z]{2}[0-9]{1,2}[A-Z]{1,2}[0-9]{1,4}$/';
    
        // Test the vehicle number against the regex pattern
        $isValid = preg_match($regex, $vehicleNumber);
    
        return $isValid === 1;
    }

    // Validate the vehicle number
    private function validateChassisNumber($Number)
    {
        // Regular expression pattern for vehicle number validation
        $regex = '/^[A-HJ-NPR-Z0-9]{17}$/i';
    
        // Test the vehicle number against the regex pattern
        $isValid = preg_match($regex, $Number);
    
        return $isValid === 1;
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

    private function addHistoryRC($vehicleNo, $request, $vendor, $data)
    {
        $createdAt = now();

        return DB::table('history_rc')->insert([
            'vehicle_no' => $vehicleNo,
            'vendor' => $vendor,
            'request' => $request,
            'response' => json_encode($data),
            'status' => 1,
            'created_at' => $createdAt,
        ]);
    }

    public function addBulkLog($filename,$vehicleNumbers,$file_url)
    {
        $bulklog =  new Bulkfilelog();
        $bulklog->filename = $filename;
        $bulklog->count = count($vehicleNumbers);
        $bulklog->downloadurl = $file_url;
        $bulklog->status = '1';
        $bulklog->save();
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
