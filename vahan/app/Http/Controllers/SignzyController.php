<?php

namespace App\Http\Controllers;
use App\Models\Company;
use App\Models\Rcdetails;
use App\Models\Api_mapping;
use App\Models\Module;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Http;


class RcdetailsController extends Controller
{
    public function verifyApiAccess(Request $request)
    {
        $sessionData = session('data');
        $client_id  = $sessionData['Client_id'];
        $vendorname = $request->input('vendor');
        $apiname    = $request->input('api');
        $mappingData = Api_mapping::where('client_id', $client_id)->where('apiname', $apiname)->where('vendorname', $vendorname)->where('del_status', 1)->whereIn('status', [0,1,2])->get();

        if(!$mappingData)
        {
            return Redirect()->back()->with('error','Not authorized user!');
        }
        
    }
    public function retrieveVehicleData(Request $request)
    {       
        // return $request;
        $apiUrl = Config::get('custom.rc_authbridge.rc_authbridge_url');
        $token = Config::get('custom.rc_authbridge.rc_authbridge_token');
        // Get the vehicle number from the request
        $method = 'POST';
        $vehicleNo = $request->input('vehicleNo'); 
    
        $isValidVehicleNumber = $this->validateVehicleNumber($vehicleNo);
        if ($isValidVehicleNumber === false) {
            return response()->json(['status' => 'Please enter valid vehicle number']);
        }
    
        // Request data
        $data = [
            'vehicle_No' => $vehicleNo
        ];
        // echo "<pre>"; print_r($data);die;
    
        // Make the API request using Laravel's HTTP client
        $response = Http::withHeaders([
            'token' => $token,
            'Content-Type' => 'application/json'
        ])->post($apiUrl, $data);
            
        // echo $apiUrl; die;
        if ($response->failed()) {
            // Handle the error appropriately
            return response()->json(['error' => $response->body()]);
        }
    
       $responseData = $response->json();
    
        if (empty($responseData)) {
            return response()->json(['status' => 'No Record Found!']);
        }

        //save the vehicle data in Rcdetails(api_list)
        // $api_details = Module::where('del_status', 1)->pluck('company','vendorname','apiurl','apiname', 'id');
        
        $data = json_decode($response->body(),true);
        // $decodedBody = $response->decoded;
        // print_r($data['status_code']);
        $api_log =  new Rcdetails();
        $api_log->response_status_code = $data['status_code'];
        $api_log->api_url = $apiUrl;
        $api_log->request = $vehicleNo;
        $api_log->response = $response->body();
        $api_log->status = '1';
        $api_log->method = $method;
        $api_log->save();

        return $response->body();
    }
    
    // Separate a camel case word into multiple words
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
}
