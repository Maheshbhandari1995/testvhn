<?php

namespace App\Http\Controllers;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use App\Models\Company;
use Illuminate\Validation\Rule;
use App\Models\Rcdetails;
use Yajra\DataTables\Buttons\Button;
use Yajra\DataTables\Buttons\DatatableButton;
use Illuminate\Support\Facades\Session;

class BillingController extends Controller
{

    public function getBillingReportCsv(Request $request){

        ini_set('memory_limit', '1024M');
        $dateFrom = $request->input('date_from');
        $dateTo = $request->input('date_to');
        $sessionData = session('data');
        $sqlDateFrom = date('Y-m-d', strtotime($dateFrom));
        $sqlDateTo = date('Y-m-d', strtotime($dateTo));
        $org = $request->input('org');
        $all_value = $org[0];

        if (isset($dateFrom) && isset($dateTo)) {
           
                if ($all_value === "All") {

                    $organizations = Company::whereIn('del_status', [1, 0])->pluck('name')->toArray();

                    
                    $dataApiLog = DB::table('api_log')
                    ->selectRaw('UPPER(client_name) AS client_name')
                    ->selectRaw('UPPER(api_name) AS api_name')
                    ->selectRaw('SUM(CASE WHEN response_status_code = 200 THEN 1 ELSE 0 END) AS success_count')
                    ->selectRaw('SUM(CASE WHEN response_status_code != 200 THEN 1 ELSE 0 END) AS failed_count')
                    ->whereRaw("DATE(api_log.created_at) BETWEEN ? AND ?", [$sqlDateFrom, $sqlDateTo])
                    ->whereIn('api_log.client_name', $organizations)
                    ->groupBy('client_name', 'api_name');
                    

                    $dataBillingLog = DB::table('external_source_data')
                    ->selectRaw('UPPER(client_name) AS client_name')
                    ->selectRaw('UPPER(api_name) AS api_name')
                    ->selectRaw('SUM(CASE WHEN status_code = 200 THEN 1 ELSE 0 END) AS success_count')
                    ->selectRaw('SUM(CASE WHEN status_code != 200 THEN 1 ELSE 0 END) AS failed_count')
                    ->whereRaw("DATE(external_source_data.created_at) BETWEEN ? AND ?", [$sqlDateFrom, $sqlDateTo])
                    ->whereIn('external_source_data.client_name', $organizations)
                    ->groupBy('client_name', 'api_name');
                   

                } else {
                    $dataApiLog = DB::table('api_log')
                        ->selectRaw('UPPER(client_name) AS client_name')
                        ->selectRaw('UPPER(api_name) AS api_name')
                        ->selectRaw('SUM(CASE WHEN response_status_code = 200 THEN 1 ELSE 0 END) AS success_count')
                        ->selectRaw('SUM(CASE WHEN response_status_code != 200 THEN 1 ELSE 0 END) AS failed_count')
                        ->whereRaw("DATE(api_log.created_at) BETWEEN ? AND ?", [$sqlDateFrom, $sqlDateTo])
                        ->whereIn('api_log.client_name', $org)
                        ->groupBy('client_name', 'api_name');
                        

                        $dataBillingLog = DB::table('external_source_data')
                        ->selectRaw('UPPER(client_name) AS client_name')
                        ->selectRaw('UPPER(api_name) AS api_name')
                        ->selectRaw('SUM(CASE WHEN status_code = 200 THEN 1 ELSE 0 END) AS success_count')
                        ->selectRaw('SUM(CASE WHEN status_code != 200 THEN 1 ELSE 0 END) AS failed_count')
                        ->whereRaw("DATE(external_source_data.created_at) BETWEEN ? AND ?", [$sqlDateFrom, $sqlDateTo])
                        ->whereIn('external_source_data.client_name', $org)
                        ->groupBy('client_name', 'api_name');

                     
                      
                        
                }  

                $data = $dataApiLog->union($dataBillingLog)->get();

                //$data = $dataApiLog->union($dataBillingLog)->union($dataarchiveLog)->get();
        
            $csvarray = [];

            $csvarray[] = ['Client Name','Type API','Success','Failed'];

            foreach ($data as $row) {
                $csvarray[] = [
                    $row->client_name,
                    $row->api_name,
                    $row->success_count,
                    $row->failed_count,
                ];
            }

            $timestamp = date('Y_m_d_H_i_s');
                $filename = 'report_modulebilling' . $timestamp . '.csv';
                $headers = [
                    'Content-Type' => 'text/csv',
                    'Content-Disposition' => "attachment; filename=\"$filename\""
                ];
                $tempFilePath = tempnam(sys_get_temp_dir(), 'report');
                $tempFile = fopen($tempFilePath, 'w');
        
                foreach ($csvarray as $row) {
                    fputcsv($tempFile, $row);
                }
        
                fclose($tempFile);
                $url = request()->root();
                $parsedUrl = parse_url($url);
                $baseUrl = $parsedUrl['scheme'] . '://' . $parsedUrl['host'] . $parsedUrl['path'];
                $filePath = storage_path("app/public/uploads/rcbulk/$filename");
                //$file_url = $baseUrl . "/public/storage/uploads/rcbulk/" . $filename;
                $file_url = $baseUrl . "/storage/app/public/uploads/rcbulk/" . $filename;
        
                rename($tempFilePath, $filePath);
                chmod($filePath, 0755);
        

        return response()->json(['download' => '1', 'file_url' => $file_url, 'file_name' => $filename], 200);

        }

    }

    public function getOrganizationNames()
    {
        $organizations = Company::whereIn('del_status', [1,0])->pluck('name')->toArray();

        return response()->json($organizations);
    }

    private function getInput($module, $vendor, $jsonData)
    {   

        $input = '';

        $datatype = gettype($jsonData);
        // echo "<pre>";
        // print_r($datatype);

        if($datatype == 'array'){
            if(($module == 'rc' || $module == 'challan' ) && $vendor == 'authbridge')
            {   
                if(isset($jsonData['docNumber'])){

                    $input = $jsonData['docNumber'];
                }
                else{
                    $input = $jsonData;
                }
            }
            else if(($module == 'rc' || $module == 'challan') && $vendor == 'signzy')
            {
                $input = $jsonData['essentials']['vehicleNumber'];
            }
            else if($module == 'license' && $vendor == 'signzy'){
                $input = $jsonData['essentials']['number'];
            }
            else if($module == 'license' && $vendor == 'authbridge'){
                $input = $jsonData['essentials']['number'];
            }
            else if($module == 'challan' && $vendor == 'rto'){
                $input = $jsonData['vehicle_number'];
            }
            else if($module == 'rc_logic' && $vendor == 'edas_internal'){
                $input = $jsonData['Vehicle_No'];
            }
            else if($module == 'rc_chassis'){
                $input = $jsonData['chassisNumber'];
            }
        }else if($datatype == NULL || $datatype == 'NULL' || $datatype == '')
        {
            $input = $jsonData;
        }
        else{
            $input = $jsonData;
        }


        return $input;
    }

    public function getSummaryBillingReportCsv(Request $request){

        ini_set('memory_limit', '1024M');
        $dateFrom = $request->input('date_from');
        $dateTo = $request->input('date_to');
        $sessionData = session('data');
        $sqlDateFrom = date('Y-m-d', strtotime($dateFrom));
        $sqlDateTo = date('Y-m-d', strtotime($dateTo));
        $org = $request->input('org');
        $all_value = $org[0];

        if (isset($dateFrom) && isset($dateTo)) {
            if($sessionData['userRole'] == 'super_admin' || $sessionData['userRole'] == 'mis')
            {   
                if ($all_value === "All") {
                    $organizations = Company::whereIn('del_status', [1, 0])->pluck('name')->toArray();
                
                    $dataBillingLog = DB::table('external_source_data')
                        ->select('external_source_data.client_name', 'external_source_data.status_code as response_status_code', 'external_source_data.created_at', 'external_source_data.vendor_name as vendor', 'external_source_data.input as request', 'external_source_data.api_name as Module', 'external_source_data.user_name as name',DB::raw("'Rc-logic' as Rcdetails"),DB::raw("
                        CASE
                            WHEN is_history = 1 THEN 'Api History'
                            WHEN is_history = 2 THEN 'Api Vendor'
                            ELSE NULL
                        END
                            AS cache_status"
                        ),DB::raw("'Api' as source"))
                        ->whereRaw("DATE(external_source_data.created_at) BETWEEN ? AND ?", [$sqlDateFrom, $sqlDateTo])
                        ->whereIn('external_source_data.client_name', $organizations)
                        ->get();

                     $dataApiLog = DB::table('api_log')
                        ->leftJoin('clients', 'api_log.client_id', '=', 'clients.id')
                        ->leftJoin('users', 'users.id', '=', 'api_log.user_id')
                        ->select('api_log.client_name', 'api_log.response_status_code', 'api_log.created_at', 'api_log.vender as vendor', 'api_log.input as request', 'api_log.api_name as Module', 'users.name',DB::raw("
                        CASE
                            WHEN api_log.api_name = 'rc' AND api_log.request_type = 1 THEN 'Single rc'
                            WHEN api_log.api_name = 'rc' AND api_log.request_type = 2 THEN 'Bulk rc'
                            ELSE NULL
                        END
                        as Rcdetails"
                        ),DB::raw("
                                CASE
                                    WHEN response_from IN (0, 1) THEN 'Portal Vendor'
                                    WHEN response_from = 2 THEN 'Portal History'
                                    ELSE NULL
                                END
                        AS cache_status"
                        ),DB::raw("'Portal' as source") )
                        ->whereRaw("DATE(api_log.created_at) BETWEEN ? AND ?", [$sqlDateFrom, $sqlDateTo])
                        ->whereIn('api_log.status', [0, 1])
                        ->whereIn('api_log.client_name', $organizations)
                        ->get();


                        $data = $dataBillingLog->merge($dataApiLog); 

                        //$data = $dataBillingLog->merge($dataApiLog)->merge($dataarchiveLog);
                
                
                } else {

                    $dataBillingLog = DB::table('external_source_data')
                        ->select('external_source_data.client_name', 'external_source_data.status_code as response_status_code', 'external_source_data.created_at', 'external_source_data.vendor_name as vendor', 'external_source_data.input as request', 'external_source_data.api_name as Module', 'external_source_data.user_name as name',DB::raw("'Rc-logic' as Rcdetails"),DB::raw("
                        CASE
                            WHEN is_history = 1 THEN 'Api History'
                            WHEN is_history = 2 THEN 'Api Vendor'
                            ELSE NULL
                        END
                            AS cache_status"
                        ),DB::raw("'Api' as source"))
                        ->whereRaw("DATE(external_source_data.created_at) BETWEEN ? AND ?", [$sqlDateFrom, $sqlDateTo])
                        ->whereIn('external_source_data.client_name', $org)
                        ->get();


                    $dataApiLog = DB::table('api_log')
                    ->leftJoin('clients', 'api_log.client_id', '=', 'clients.id')
                    ->leftJoin('users', 'api_log.user_id', '=', 'users.id')
                    ->select('api_log.client_name', 'api_log.response_status_code', 'api_log.created_at', 'api_log.vender as vendor', 'api_log.input as request', 'api_log.api_name as Module', 'users.name',DB::raw("
                    CASE
                        WHEN api_log.api_name = 'rc' AND api_log.request_type = 1 THEN 'Single rc'
                        WHEN api_log.api_name = 'rc' AND api_log.request_type = 2 THEN 'Bulk rc'
                        ELSE NULL
                    END
                    as Rcdetails"
                    ),DB::raw("
                    CASE
                        WHEN response_from IN (0, 1) THEN 'Portal Vendor'
                        WHEN response_from = 2 THEN 'Portal History'
                        ELSE NULL
                    END
                    AS cache_status"
                    ),DB::raw("'Portal' as source"))
                    ->whereRaw("DATE(api_log.created_at) BETWEEN ? AND ?", [$sqlDateFrom, $sqlDateTo])
                    ->whereIn('api_log.status', [0, 1])
                    ->whereIn('api_log.client_name', $org)
                    ->get();
                    
                        $data = $dataApiLog->union($dataBillingLog); 

                      //$data = $dataApiLog->union($dataBillingLog)->union($dataarchiveLog)->get();
                }
            }


            // print_r($data);

            $csvarray = [];

            $csvarray[] = ['Client Name','User','Type API', 'Client Input', 'Timestamp','Status code','Cache Status','Type','Source'];

            foreach ($data as $row) {

                $csvarray[] = [
                    $row->client_name,
                    $row->name,
                    $row->Module,            
                    $row->request,
                    $row->created_at,
                    $row->response_status_code,
                    $row->cache_status,
                    $row->Rcdetails,
                    $row->source,
                ];
            }

            $timestamp = date('Y_m_d_H_i_s');
                $filename = 'report_summarybilling' . $timestamp . '.csv';
                $headers = [
                    'Content-Type' => 'text/csv',
                    'Content-Disposition' => "attachment; filename=\"$filename\""
                ];
                $tempFilePath = tempnam(sys_get_temp_dir(), 'report');
                $tempFile = fopen($tempFilePath, 'w');
        
                foreach ($csvarray as $row) {
                    fputcsv($tempFile, $row);
                }
        
                fclose($tempFile);
                $url = request()->root();
                $parsedUrl = parse_url($url);
                $baseUrl = $parsedUrl['scheme'] . '://' . $parsedUrl['host'] . $parsedUrl['path'];
                $filePath = storage_path("app/public/uploads/rcbulk/$filename");
                //$file_url = $baseUrl . "/public/storage/uploads/rcbulk/" . $filename;
                $file_url = $baseUrl . "/storage/app/public/uploads/rcbulk/" . $filename;
                rename($tempFilePath, $filePath);
                chmod($filePath, 0755);
        

        return response()->json(['download' => '1', 'file_url' => $file_url, 'file_name' => $filename], 200);

        }


    }


    public function getVendorBillingReportCsv(Request $request){
        ini_set('memory_limit', '1024M');
        $dateFrom = $request->input('date_from');
        $dateTo = $request->input('date_to');
        $sessionData = session('data');
        $sqlDateFrom = date('Y-m-d', strtotime($dateFrom));
        $sqlDateTo = date('Y-m-d', strtotime($dateTo));
        $org = $request->input('org');
        $all_value = $org[0];

        if (isset($dateFrom) && isset($dateTo)) {
            if($sessionData['userRole'] == 'super_admin' || $sessionData['userRole'] == 'mis')
            {   
                if ($all_value === "All") {
                    $organizations = Company::whereIn('del_status', [1, 0])->pluck('name')->toArray();
                
                     $data = DB::table('api_log')
                       ->leftJoin('api_detail_log', 'api_detail_log.id', '=', 'api_log.api_detail_log_id')
                        ->leftJoin('clients', 'api_log.client_id', '=', 'clients.id')
                        ->leftJoin('users', 'users.id', '=', 'api_log.user_id')
                        ->select('clients.name as Client_name','api_log.created_at', 'api_detail_log.primary_vendor','api_detail_log.secondary_vendor','api_log.input as request', 'api_log.api_name as Module','api_log.response_from','users.name as Name','api_detail_log.primary_status as primary_status', 'api_log.vender as history_vendor','api_detail_log.secondary_status as secondary_status', 'api_log.response_status_code')
                        ->whereRaw("DATE(api_log.created_at) BETWEEN ? AND ?", [$sqlDateFrom, $sqlDateTo])
                        //->whereIn('api_detail_log.status', [0, 1])
                        ->whereIn('api_log.client_name', $organizations)
                        //->distinct()
                        ->get();

                
                } else {

                    $data = DB::table('api_log')
                       ->leftJoin('api_detail_log', 'api_detail_log.id', '=', 'api_log.api_detail_log_id')
                        ->leftJoin('clients', 'api_log.client_id', '=', 'clients.id')
                        ->leftJoin('users', 'users.id', '=', 'api_log.user_id')
                       ->select('clients.name as Client_name','api_log.created_at', 'api_detail_log.primary_vendor','api_detail_log.secondary_vendor','api_log.input as request', 'api_log.api_name as Module','api_log.response_from','users.name as Name','api_detail_log.primary_status as primary_status', 'api_log.vender as history_vendor','api_detail_log.secondary_status as secondary_status', 'api_log.response_status_code')
                        ->whereRaw("DATE(api_log.created_at) BETWEEN ? AND ?", [$sqlDateFrom, $sqlDateTo])
                        //->whereIn('api_detail_log.status', [0, 1])
                        ->whereIn('api_log.client_name', $org)
                        //->distinct()
                        ->get();

                }
            }

            // echo "<pre>"; print_r($data);exit;
            $csvarray = [];

            $csvarray[] = ['Client','User','Type API', 'Request','Source','Primary Vendor','Secondary Vendor','Primary Status','Secondary Status','Timestamp'];

            foreach ($data as $row) {

                $csvarray[] = [
                    $row->Client_name,
                    $row->Name,
                    $row->Module,            
                    $row->request,
                    ($row->response_from == 1) ? 'Vendor API' : 'History',
                    ($row->primary_vendor == null || empty($row->primary_vendor)) ? $row->history_vendor : $row->primary_vendor,
                    $row->secondary_vendor,
                    ($row->primary_vendor == null || empty($row->primary_vendor)) ? $row->response_status_code : $row->primary_status,
                    $row->secondary_status,
                    $row->created_at,

                ];
            }

            $timestamp = date('Y_m_d_H_i_s');
                $filename = 'report_vendorbilling' . $timestamp . '.csv';
                $headers = [
                    'Content-Type' => 'text/csv',
                    'Content-Disposition' => "attachment; filename=\"$filename\""
                ];
                $tempFilePath = tempnam(sys_get_temp_dir(), 'report');
                $tempFile = fopen($tempFilePath, 'w');
        
                foreach ($csvarray as $row) {
                    fputcsv($tempFile, $row);
                }
        
                fclose($tempFile);
                $url = request()->root();
                $parsedUrl = parse_url($url);
                /* $baseUrl = $parsedUrl['scheme'] . '://' . $parsedUrl['host'] . $parsedUrl['path'];
                $filePath = storage_path("app/public/uploads/rcbulk/$filename");
                $file_url = $baseUrl . "/public/storage/uploads/rcbulk/" . $filename; */
				
				$baseUrl = $parsedUrl['scheme']."://".rtrim($parsedUrl['host']).$parsedUrl['path'];
                $filePath = storage_path("app/public/uploads/rcbulk/$filename");
                $file_url = $baseUrl . "/storage/app/public/uploads/rcbulk/" . $filename;
        
                rename($tempFilePath, $filePath);
				chmod($filePath, 0755);

        return response()->json(['download' => '1', 'file_url' => $file_url, 'file_name' => $filename], 200);

        }


    }

    public function getapiReportCsv(Request $request){

        ini_set('memory_limit', '512M');
        $dateFrom = $request->input('date_from');
        $dateTo = $request->input('date_to');
        $sessionData = session('data');
        $sqlDateFrom = date('Y-m-d', strtotime($dateFrom));
        $sqlDateTo = date('Y-m-d', strtotime($dateTo));

        if (isset($dateFrom) && isset($dateTo)) {
            if($sessionData['userRole'] == 'admin')
            {   
               
                     $data = DB::table('external_source_data')
                        ->select('external_source_data.Client_name as Client_name','external_source_data.input as Request', 'external_source_data.api_name as Module','external_source_data.status_code','external_source_data.created_at as Timestamp')
                        ->whereRaw("DATE(external_source_data.created_at) BETWEEN ? AND ?", [$sqlDateFrom, $sqlDateTo])
                        ->get();

            }

            $csvarray = [];

            $csvarray[] = ['Client','Request','Module','Timestamp','Remark'];

            foreach ($data as $row) {

                if ($row->status_code == 200 || $row->status_code == 401 || $row->status_code == 1 ) {
                    //$status = "success";
                    $remark = "success";
                }
                else if($row->status_code == 400)
                {
                    $remark = "Invalid VRN";
                } 
                else {
                    //$status = "failed";
                    $remark = "Data not found";
                }

                $csvarray[] = [
                    $row->Client_name,
                    $row->Request,
                    $row->Module,     
                    $row->Timestamp,
                    $remark,

                ];
            }

            $timestamp = date('Y_m_d_H_i_s');
                $filename = 'report_api' . $timestamp . '.csv';
                $headers = [
                    'Content-Type' => 'text/csv',
                    'Content-Disposition' => "attachment; filename=\"$filename\""
                ];
                $tempFilePath = tempnam(sys_get_temp_dir(), 'report');
                $tempFile = fopen($tempFilePath, 'w');
        
                foreach ($csvarray as $row) {
                    fputcsv($tempFile, $row);
                }
        
                fclose($tempFile);
                $url = request()->root();
                $parsedUrl = parse_url($url);
				
				$baseUrl = $parsedUrl['scheme']."://".rtrim($parsedUrl['host']).$parsedUrl['path'];
                $filePath = storage_path("app/public/uploads/api/$filename");
                $file_url = $baseUrl . "/storage/app/public/uploads/api/" . $filename;
        
                rename($tempFilePath, $filePath);
				chmod($filePath, 0755);

        return response()->json(['download' => '1', 'file_url' => $file_url, 'file_name' => $filename], 200);

        }
    }

    public function getApiSummaryReportCsv(Request $request){
        ini_set('memory_limit', '1024M');
        $dateFrom = $request->input('date_from');
        $dateTo = $request->input('date_to');
        $sessionData = session('data');
        $client = $sessionData['clientName'];
        $sqlDateFrom = date('Y-m-d', strtotime($dateFrom));
        $sqlDateTo = date('Y-m-d', strtotime($dateTo));

        if (isset($dateFrom) && isset($dateTo)) {

        $data = DB::table('external_source_data')
        ->select(DB::raw('DATE(created_at) AS Date'))
        // Cached Data
        ->selectRaw('COUNT(CASE WHEN vendor_name = "Signzy" AND status_code = 200 AND is_score = 1 AND is_history = 1 THEN 1 END) AS Signzy_cache_200')
        ->selectRaw('COUNT(CASE WHEN vendor_name = "Authbridge" AND status_code = 200 AND is_score = 1 AND is_history = 1 THEN 1 END) AS Authbridge_cache_200')
        ->selectRaw('COUNT(CASE WHEN vendor_name = "Signzy" AND status_code = 200 AND is_score = 0 AND is_history = 1 THEN 1 END) AS Signzy_cache_401')
        ->selectRaw('COUNT(CASE WHEN vendor_name = "Authbridge" AND status_code = 200 AND is_score = 0 AND is_history = 1 THEN 1 END) AS Authbridge_cache_401')
        ->selectRaw('COUNT(CASE WHEN status_code = 404 AND is_history = 1 THEN 1 END) AS Status_cache_404')
        ->selectRaw('COUNT(CASE WHEN status_code = 9 AND is_history = 1 THEN 1 END) AS Status_cache_9')
        ->selectRaw('COUNT(CASE WHEN status_code = 0 AND is_history = 1 THEN 1 END) AS Status_cache_0')
        ->selectRaw('COUNT(CASE WHEN status_code = 1 AND is_history = 1 THEN 1 END) AS Status_cache_1')
        ->selectRaw('COUNT(CASE WHEN status_code = "" AND is_history = 1 THEN 1 END) AS Status_cache_Blanks')
        ->selectRaw('COUNT(CASE WHEN status_code NOT IN (200, 1, 0, 9, 404, "") AND is_history = 1 THEN 1 END) AS Status_cache_Other')
        // Non-Cached Data
        ->selectRaw('COUNT(CASE WHEN vendor_name = "Signzy" AND status_code = 200 AND is_score = 1 AND is_history = 2 THEN 1 END) AS Signzy_200')
        ->selectRaw('COUNT(CASE WHEN vendor_name = "Authbridge" AND status_code = 200 AND is_score = 1 AND is_history = 2 THEN 1 END) AS Authbridge_200')
        ->selectRaw('COUNT(CASE WHEN vendor_name = "Signzy" AND status_code = 200 AND is_score = 1 AND is_history = 2 THEN 1 END) AS Signzy_401')
        ->selectRaw('COUNT(CASE WHEN vendor_name = "Authbridge" AND status_code = 200 AND is_score = 1 AND is_history = 2 THEN 1 END) AS Authbridge_401')
        ->selectRaw('COUNT(CASE WHEN status_code = 404 AND is_history = 2 THEN 1 END) AS Status_404')
        ->selectRaw('COUNT(CASE WHEN status_code = 9 AND is_history = 2 THEN 1 END) AS Status_9')
        ->selectRaw('COUNT(CASE WHEN status_code = 0 AND is_history = 2 THEN 1 END) AS Status_0')
        ->selectRaw('COUNT(CASE WHEN status_code = 1 AND is_history = 2 THEN 1 END) AS Status_1')
        ->selectRaw('COUNT(CASE WHEN status_code = "" AND is_history = 2 THEN 1 END) AS Status_Blanks')
        ->selectRaw('COUNT(CASE WHEN status_code NOT IN (200, 401, 1, 0, 9, 404, "") AND is_history = 2 THEN 1 END) AS Status_Other')
        // Unique Data
        ->selectRaw('COUNT(DISTINCT CASE WHEN status_code = 200 THEN input END) AS count_200')
        ->selectRaw('COUNT(DISTINCT CASE WHEN status_code = 409 AND status_code NOT IN (200, 401, 1, 9, 0, 1138, 1010, 500) THEN input END) AS count_409')
        ->selectRaw('COUNT(DISTINCT CASE WHEN status_code = 404 THEN input END) AS count_404')
        ->selectRaw('COUNT(DISTINCT CASE WHEN status_code = 0 THEN input END) AS count_0')
        ->selectRaw('COUNT(DISTINCT CASE WHEN status_code = 9 THEN input END) AS count_9')
        ->selectRaw('COUNT(DISTINCT CASE WHEN status_code = 1 THEN input END) AS count_1')
        ->selectRaw('COUNT(DISTINCT CASE WHEN status_code = 200 AND is_score = 1  THEN input END) AS count_401')
        ->selectRaw('COUNT(DISTINCT CASE WHEN status_code = "" THEN input END) AS count_blanks')
        ->selectRaw('COUNT(DISTINCT CASE WHEN status_code NOT IN (200,9,401, 409, 404, 1, 0, "") THEN input END) AS other_count')
        ->whereBetween('created_at', [$sqlDateFrom, $sqlDateTo])
        ->where('external_source_data.Client_name','=',$client)
        ->groupBy(DB::raw('DATE(created_at)'))
        ->get();

            $csvarray = [];

            $csvarray[] = ['Date','Signzy 200','Authbridge 200','Signzy 401','Authbridge 401','404','9','0','1','Blanks' ,'Other Status','Signzy_cache_200','Authbridge_cache_200','Signzy_cache_401','Authbridge_cache_401','cache_404','cache_9','cache_0','cache_1','cache_Blanks' ,'cache_Other_Status','Unique 200','Unique 401','Unique 404','Unique 9' ,'Unique 0','Unique 1','unique 409','unique_blank','unique other status'];

            foreach ($data as $row) {

                $csvarray[] = [
                    $row->Date,//
                    $row->Signzy_200,//
                    $row->Authbridge_200,   //         
                    $row->Signzy_401, //
                    $row->Authbridge_401,//
                    $row->Status_404,//
                    $row->Status_9,//
                    $row->Status_0,//
                    $row->Status_1,//
                    $row->Status_Blanks,//
                    $row->Status_Other,//
                    $row->Signzy_cache_200,
                    $row->Authbridge_cache_200,
                    $row->Signzy_cache_401,
                    $row->Authbridge_cache_401,
                    $row->Status_cache_404,
                    $row->Status_cache_9,
                    $row->Status_cache_0,
                    $row->Status_cache_1,
                    $row->Status_cache_Blanks,
                    $row->Status_cache_Other,
                    $row->count_200,
                    $row->count_401,
                    $row->count_404,
                    $row->count_9,
                    $row->count_409,
                    $row->count_blanks,
                    $row->other_count,
                ];
            }

            $timestamp = date('Y_m_d_H_i_s');
                $filename = 'apireport_summarybilling' . $timestamp . '.csv';
                $headers = [
                    'Content-Type' => 'text/csv',
                    'Content-Disposition' => "attachment; filename=\"$filename\""
                ];
                $tempFilePath = tempnam(sys_get_temp_dir(), 'report');
                $tempFile = fopen($tempFilePath, 'w');
        
                foreach ($csvarray as $row) {
                    fputcsv($tempFile, $row);
                }
        
                fclose($tempFile);
                $url = request()->root();
                $parsedUrl = parse_url($url);
                $baseUrl = $parsedUrl['scheme'] . '://' . $parsedUrl['host'] . $parsedUrl['path'];
                $filePath = storage_path("app/public/uploads/rcbulk/$filename");
                //$file_url = $baseUrl . "/public/storage/uploads/rcbulk/" . $filename;
                $file_url = $baseUrl . "/storage/app/public/uploads/rcbulk/" . $filename;
                rename($tempFilePath, $filePath);
                chmod($filePath, 0755);
        

        return response()->json(['download' => '1', 'file_url' => $file_url, 'file_name' => $filename], 200);


        }
        
    }
}

