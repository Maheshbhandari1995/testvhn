<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use App\Models\Rcdetails;
use App\Models\Bulkfilelog;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use App\Traits\CommonTraits;
use Yajra\DataTables\Facades\DataTables;

class RcBulkUploadController extends Controller
{
    use CommonTraits;

    public function authbridgeViewRC()
    {
        return view('rc.rc_bulk');
    }
    
    public function authbridgeRCBulkData(Request $request)
    {
        $sessionData = session('data');
        
       // echo "<pre>"; print_r($sessionData);die;
        $request->validate([
            'rcdata' => 'required|file|mimes:csv,txt|max:2048', // Adjust the max file size as per your requirements
        ]);

        if ($request->hasFile('rcdata')) {
            $file = $request->file('rcdata');
            $fileName = date('Y_m_d_His') . '_' . $file->getClientOriginalName();
            // Move the uploaded file to the storage directory
            $file->storeAs('csv', $fileName, 'public');
            $fileExists = Storage::disk('public')->exists('csv/'.$fileName);
            if($fileExists)
            {
                $filePath = Storage::disk('public')->path('csv/'.$fileName);
                $uploadedFile = Bulkfilelog::create([
                    'user_id'       => $sessionData['userID'],
                    'client_id'     => $sessionData['Client_id'],
                    'api_id'        => Config::get('custom.authbridge.rc.api_id'),
                    'vendor'        => Config::get('custom.authbridge.rc.vender'),
                    'filename'      => $fileName,
                    'upload_url'    => $filePath,
                    'status'        => 1,
                    'api_name'      => 'RC',
                    'is_processed'  => 1,
                ]);

                if($uploadedFile)
                {
                    $result = $this->processUploadedFiles($uploadedFile->id);
                    if($result['status'] == 'success'){
                        // return response()->json(['status'=> 'success', 'msg' => 'We have recieved your data please check the RC Bulk Upload List for the status.']);
                        return redirect('rc.rc_bulk_upload')->with('success', 'We have recieved your data please check the RC Bulk Upload List for the status.');
                    }
                    else{
                       // return response()->json(['status'=> 'failed', 'msg' => $result['msg']]);
                        return redirect('rc.rc_bulk_upload')->with('failed', $result['msg']);
                    }
                        
                }
                else{
                    // redirect()->route('rc.rc_bulk_upload')->with('success', 'Sorry Unable to process the data!');
                    // return response()->json(['status'=> 'failed', 'msg' => 'Sorry Unable to process the data.']);
                    return redirect('rc.rc_bulk_upload')->with('failed', 'Sorry Unable to process the data');
                }
            }  
        }
        else{
            // redirect()->route('rc.rc_bulk_upload')->with('success', 'Sorry, Unable to find the file.');
            //return response()->json(['status'=> 'failed', 'msg' => 'Sorry, Unable to find the file.']);
            return redirect('rc.rc_bulk_upload')->with('failed', 'Sorry, Unable to find the file');
        }
    }


  
    public function processUploadedFiles($id)
    { 
        $sessionData = session('data');
        if(!empty($id))
        {
            $fileData = DB::table('bulkfile_log')
                ->leftJoin('clients', 'bulkfile_log.client_id', '=', 'clients.id')
                ->select('bulkfile_log.*', 'clients.name as clientname')
                ->whereIn('bulkfile_log.status', [0,1])
                ->whereIn('clients.status', [0,1])
                ->where('clients.del_status', 1)
                ->where('bulkfile_log.is_processed', 1)
                ->where('bulkfile_log.id', $id)
                ->get()
                ->first();

            $clientname = $fileData->clientname;
            $user_id = $fileData->user_id;
            $client_id = $fileData->client_id;
            $api_id = $fileData->api_id;
            $api_name = $fileData->api_name;
            $vendor = $fileData->vendor;
            // $user_id = $fileData->user_id;

            // echo "<pre>"; print_r($fileData);
            $existFileName = 'csv/'.$fileData->filename;
            if (Storage::disk('public')->exists($existFileName)) {

                $filePath                       = Storage::disk('public')->path($existFileName);
                $vehicleNumbers                 = file($filePath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
                $vehicleNumbers                 = array_slice($vehicleNumbers, 1);
                $results = [];
                $encrypted_string_url           = Config::get('custom.authbridge.rc.encrypted_string_url');
                $utilitysearch_url              = Config::get('custom.authbridge.rc.utilitysearch_url');
                $decrypt_encrypted_string_url   = Config::get('custom.authbridge.rc.decrypt_encrypted_string_url');
                $username                       = Config::get('custom.authbridge.rc.username');
                $api_id                         = Config::get('custom.authbridge.rc.api_id');
                $api_name                       = Config::get('custom.authbridge.rc.api_name');
                $vendor                         = Config::get('custom.authbridge.rc.vender');
                $method                         = 'POST';
                $response_from                  = 1;
                foreach ($vehicleNumbers as $key => $vehicleNo) {

                    if($this->checkCredit($client_id) === false)
                    {
                        $results[$key]['error'] = ['vehicleNo' => $vehicleNo, 'status_code' => '101', 'message' => 'No Credit Available'];
                        continue;
                    }
                    
                    // Validate the vehicle number
                    $isValidVehicleNumber = $this->validateVehicleNumber($vehicleNo);
                    if ($isValidVehicleNumber === false) {
                        $statusCode = '101';
                        $results[$key]['error'] = ['vehicleNo' => $vehicleNo, 'status_code' => $statusCode, 'message' => 'Please enter a valid vehicle number'];
                        continue;
                    }
                    $dataStep1 = [];
                    $dataStep1['docNumber'] = $vehicleNo;
                    $dataStep1['transID'] = '1234567';
                    $dataStep1['docType'] = '372';

                    $jsonDataStep1 = json_encode($dataStep1);

                    $response = $this->checkHistoryRC($vehicleNo, $vendor);
                    //checking data is present in db

                    if (empty($response)) 
                    {
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
                            $results[$key]['error'] = ['vehicleNo' => $vehicleNo, 'status_code' => $statusCode, 'message' => $message];
                            
                        }
                        else{

                            $dataStep2 = [
                                'requestData' => $responseStep1,
                            ];

                            $jsonDataStep2 = json_encode($dataStep2);


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
                                $results[$key]['error'] = ['vehicleNo' => $vehicleNo, 'status_code' => $statusCode, 'message' => $message];
                            }
                            else
                            {
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
                                $response = curl_exec($curlStep3);

                                if ($response === false) {
                                    $message = curl_error($curlStep3);
                                    $statusCode = curl_errno($curlStep3);
                                    $remark = 'Curl Error';
                                    $results[$key]['error'] = ['vehicleNo' => $vehicleNo, 'status_code' => $statusCode, 'message' => $message];
                                }
                                else
                                {

                                    $responseData = json_decode($response, true);
                                    $message = isset($responseData['message']) ? $responseData['message'] : '';
                                    $statusCode = isset($responseData['status_code']) ? $responseData['status_code'] : '';
                                    $remark = 'Response from Vendor API';
                                    $response_from = 1;

                                    if (isset($responseData['status_code']) && $responseData['status_code'] === 200) {
                                        $this->updateUtilizedCredit($client_id);
                                        $this->addHistoryRC($vehicleNo, $jsonDataStep1, $vendor, $response);
                                        $results[$key]['data'] = $responseData;
                                    } 
                                    else 
                                    {
                                        $results[$key]['error'] = ['vehicleNo' => $vehicleNo, 'status_code' => $statusCode, 'message' => 'Error: data is not valid'];
                                    }
                                }
                            }
                        }
                    } 
                    else 
                    {
                        $responseData = (!empty($response)) ? json_decode($response, true) : '';
                        $message = isset($responseData['message']) ? $responseData['message'] : '';
                        $statusCode = isset($responseData['status_code']) ? $responseData['status_code'] : '';
                        $remark = 'Response from History';
                        $response_from = 2;
                        if($statusCode == 200)
                        {
                            $this->updateUtilizedCredit($client_id);
                            $results[$key]['data'] = $responseData;
                        }
                        else 
                        {
                            $results[$key]['error'] = ['vehicleNo' => $vehicleNo, 'status_code' => $statusCode, 'message' => 'Error: data is not valid'];
                        }
                    
                    }
                    
                    $api_log =  new Rcdetails();
                    $api_log->api_id = $api_id;
                    $api_log->api_name = $api_name;
                    $api_log->vender = $vendor;
                    $api_log->user_id = $user_id;
                    $api_log->client_id = $client_id;
                    $api_log->client_name = $clientname;
                    $api_log->response_status_code = $statusCode;
                    $api_log->response_message  = 'success';
                    $api_log->remark  = $remark;
                    $api_log->api_url = $decrypt_encrypted_string_url;
                    $api_log->request  = $jsonDataStep1 ;
                    $api_log->response = $response;
                    $api_log->request_type = 2;
                    $api_log->bulk_id = $id;
                    $api_log->response_from = $response_from;
                    $api_log->status = '1';
                    $api_log->method = $method;
                    $api_log->save();
                }
                
                //Prepare CSV export
                if(!empty($results))
                {
                    $csvData = array();
                    $csvData[] = ['S.No.',	'Input RC Number'	,'Transaction ID'	,'Vehicle Category'	,'Vehicle Class',	'Fuel Type',	'Chassis Number',	'Engine Number',	'Manufacture Date',	'Model / Makers Class Date'	,'Maker/Manufacturer','Engine Capacity	Color'	,'Gross Weight',	'No of cylinder'	,'Seating Capacity',	'sleeper Capacity'	,'Norms Type',	'Body Type',	'Owner Serial Number'	,'Mobile Number'	,'Unloading Weight'	,'Rc Standard Cap'	,'Vehicle Standing Capacity',	'Vehicle Number',	'Blacklist Status'	,'Is Commercial',	'Noc Details',	'Registration Number'	,'Registration Date',	'Fitness Date/RC Expiry Date',	'RTO',	'Tax Upto',	'Vehicle Tax Up to'	,'Status'	,'Status As On'	,'Owners Name',	'Father Name/Husband Name',	'Permanent Address'	,'Present Address',	'Financer Name'	,'Insurance To Date/Insurance Upto',	'Policy Number'	,'Insurance Company',	'PUCC NO'	,'PUCC Upto',	'Permit Issue Date',	'Permit Number',	'Permit Type',	'Permit Vald From'	,'Permit Valid Upto',	'Non Use Status'	,'Non Use From',	'Non Use To',	'National Permit Number',	'National Permit Upto',	'National Permit Issued By','Remark'];
                    // echo "<pre>"; print_r($results);
                    foreach($results as $index => $result)
                    {
                        if(!empty($result))
                        {
                            $number = $index + 1; // S.No.
                            foreach($result as $type => $data)
                            {
                                if(!empty($data) && $type == 'error')
                                {
                                    $csvData[] = [$number,$data['vehicleNo'],'','','','','','','','','','','','','','','','','','','','','','','','','','','','','',	'',	'','','','','','','','','','','','','','','','','','','','','','','','',$data['status_code']." => ".$data['message']];
                                }
                                else{
                                    if(isset($data['msg'])){
                                        $msg = $data['msg']; 
                                        $csvData[] = [
                                            $number,
                                            isset($msg['Registration Details']['Registration Number']) ? $msg['Registration Details']['Registration Number'] : null, // Input RC Number
                                            isset($data['ts_transaction_id']) ? $data['ts_transaction_id']  : null, // Transaction ID
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
                                            isset($msg['RC Status']['National Permit Issued By']) ? $msg['RC Status']['National Permit Issued By'] : null // National
                                        ];
                                    }
                                    else{
                                        $csvData[] = [$number,'','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','',	'','','','','','','','','','','','','','','','','','','','','','','','',$data['status_code']." => ".$data['message']];
                                    } 
                                }
                            }
                        }
                    }


                    $timestamp = date('Y_m_d_His');
                    //$timestamp = date('Y_m_d_H_i_s');
                    $downloadFilename = 'RC_RESULT_' . $timestamp . '.csv';
                    
                    $headers = [
                        'Content-Type' => 'text/csv',
                        'Content-Disposition' => "attachment; filename=\"$downloadFilename\""
                    ];
                    
                    $tempFilePath = tempnam(sys_get_temp_dir(), 'RC_RESULT_');
                    $tempFile = fopen($tempFilePath, 'w');
                    foreach ($csvData as $csvRow) {
                        fputcsv($tempFile, $csvRow);
                    }
                    
                    fclose($tempFile);
                    //creating dynamic url for data store and download the data
                    $url = request()->root();  //root url including scheme ,host and path(EX: https://172.30.10.102/vahan)
                    $parsedUrl = parse_url($url);
                    $baseUrl = $parsedUrl['scheme'] . '://' . $parsedUrl['host'] . $parsedUrl['path'];
                    //storepath and download path both are different
                    $filePath = storage_path("app/public/uploads/rcbulk/$downloadFilename");
                    $file_url = $baseUrl."/public/storage/uploads/rcbulk/".$downloadFilename;
                    
                    rename($tempFilePath, $filePath);

                    $Bulkfilelog = Bulkfilelog::findOrFail($id);
                    $Bulkfilelog->count	= (count($csvData) - 1);
                    $Bulkfilelog->downloadurl	= $file_url;
                    $Bulkfilelog->is_processed	= 2;
                    $Bulkfilelog->updated_at	= now();
                    $Bulkfilelog->save();
                    return array('status'=> 'success', 'msg'=>'Process Comleted Successfully');
                }

            } else {
                
               return array('status'=> 'failed', 'msg'=>'File not exist');
            }

        }
    }
    
    // Validate the vehicle number
    private function validateVehicleNumber($vehicleNumber)
    {
        //validate the user vahicle number.
        $regex = '/^[A-Z]{2}[0-9]{1,2}[A-Z]{1,2}[0-9]{1,4}$/';
    
    
        $isValid = preg_match($regex, $vehicleNumber);
    
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

    private function addHistoryRC($vehicleNo, $request, $vendor, $response)
    {
        $createdAt = now();
        // Insert the new record
        return DB::table('history_rc')->insert([
            'vehicle_no' => $vehicleNo,
            'vendor' => $vendor,
            'request' => $request,
            'response' => $response,
            'status' => 1,
            'created_at' => $createdAt,
        ]);
    }

 

    public function addBulkLog($filename,$vehicleNumbers,$file_url,$api_id,$api_name)
    {   
        //insert download url in db
        $sessionData = session('data');
        $bulklog =  new Bulkfilelog();
        $bulklog->filename = $filename;
        $bulklog->count = count($vehicleNumbers);
        $bulklog->downloadurl = $file_url;
        $bulklog->status = '1';
        $bulklog->api_id = $api_id;
        $bulklog->api_name = $api_name;
        $bulklog->user_id = $sessionData['userID'];
        $bulklog->client_id = $sessionData['Client_id'];
        $bulklog->save();
    }

    public function rcBulkReportList(Request $request)
    {   
        //fetching download url data from bulkfile_log
        $sessionData = session('data');
        
        if ($request->ajax()) {
 
            if(isset($sessionData) && $sessionData['userRole'] == 'user')
            {
                $data = DB::table('bulkfile_log')
                ->join('clients', 'clients.id', '=', 'bulkfile_log.client_id')
                ->select('bulkfile_log.*', 'clients.name as client_name')
                ->where('bulkfile_log.user_id', $sessionData['userID'])
                ->where('bulkfile_log.client_id',$sessionData['Client_id'])
                ->latest()
                ->get(); 
            }
            else if(isset($sessionData) && $sessionData['userRole'] == 'super_admin')
            {
                $data = DB::table('bulkfile_log')
                ->join('clients', 'clients.id', '=', 'bulkfile_log.client_id')
                ->select('bulkfile_log.*', 'clients.name as client_name')
                ->latest()
                ->get(); 
            }
            else if(isset($sessionData) && $sessionData['userRole'] == 'admin')
            {
                $data = DB::table('bulkfile_log')
                ->join('clients', 'clients.id', '=', 'bulkfile_log.client_id')
                ->select('bulkfile_log.*', 'clients.name as client_name')
                ->where('bulkfile_log.client_id',$sessionData['Client_id'])
                ->latest()
                ->get(); 
            } 

            return DataTables::of($data)
                ->make(true);
        }
 
         return abort(404);
    }

}
