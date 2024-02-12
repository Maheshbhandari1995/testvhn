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

        
        $dateFrom = $request->input('date_from');
        $dateTo = $request->input('date_to');
        $sessionData = session('data');
        $sqlDateFrom = date('Y-m-d', strtotime($dateFrom));
        $sqlDateTo = date('Y-m-d', strtotime($dateTo));
        $org = $request->input('org');
        $all_value = $org[0];

        if (isset($dateFrom) && isset($dateTo)) {
            if($sessionData['userRole'] == 'super_admin')
            {
                if ($all_value === "All") {
                    $organizations = Company::whereIn('del_status', [1, 0])->pluck('name')->toArray();

                    
                    $data = DB::table('api_log')
                    ->selectRaw('UPPER(client_name) AS client_name')
                    ->selectRaw('UPPER(api_name) AS api_name')
                    ->selectRaw('SUM(CASE WHEN response_status_code = 200 THEN 1 ELSE 0 END) AS success_count')
                    ->selectRaw('SUM(CASE WHEN response_status_code != 200 THEN 1 ELSE 0 END) AS failed_count')
                    ->whereRaw("DATE(api_log.created_at) BETWEEN ? AND ?", [$sqlDateFrom, $sqlDateTo])
                    ->whereIn('api_log.client_name', $organizations)
                    ->groupBy('client_name', 'api_name')
                    ->get();
                    
                } else {
                    $data = DB::table('api_log')
                        ->selectRaw('UPPER(client_name) AS client_name')
                        ->selectRaw('UPPER(api_name) AS api_name')
                        ->selectRaw('SUM(CASE WHEN response_status_code = 200 THEN 1 ELSE 0 END) AS success_count')
                        ->selectRaw('SUM(CASE WHEN response_status_code != 200 THEN 1 ELSE 0 END) AS failed_count')
                        ->whereRaw("DATE(api_log.created_at) BETWEEN ? AND ?", [$sqlDateFrom, $sqlDateTo])
                        ->whereIn('api_log.client_name', $org)
                        ->groupBy('client_name', 'api_name')
                        ->get();
                }
            }

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
                $file_url = $baseUrl . "/public/storage/uploads/rcbulk/" . $filename;
        
                rename($tempFilePath, $filePath);
        

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
        if(($module == 'rc' || $module == 'challan' )&& $vendor == 'authbridge')
        {
            $input = $jsonData['docNumber'];
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
        return $input;
    }

    public function getSummaryBillingReportCsv(Request $request){

        $dateFrom = $request->input('date_from');
        $dateTo = $request->input('date_to');
        $sessionData = session('data');
        $sqlDateFrom = date('Y-m-d', strtotime($dateFrom));
        $sqlDateTo = date('Y-m-d', strtotime($dateTo));
        $org = $request->input('org');
        $all_value = $org[0];

        if (isset($dateFrom) && isset($dateTo)) {
            if($sessionData['userRole'] == 'super_admin')
            {
                if ($all_value === "All") {
                    $organizations = Company::whereIn('del_status', [1, 0])->pluck('name')->toArray();
                    $data = DB::table('api_log')
                    ->join('users', 'users.id', '=', 'api_log.user_id')
                    ->select('api_log.client_name', 'api_log.response_status_code', 'api_log.created_at', 'api_log.vender as vendor','api_log.request', 'api_log.api_name as Module', 'users.name')
                    ->whereRaw("DATE(api_log.created_at) BETWEEN ? AND ?", [$sqlDateFrom, $sqlDateTo])
                    ->whereIn('api_log.client_name', $organizations)
                    ->get();
                    
                } else {
                    $data = DB::table('api_log')
                        ->join('users', 'users.id', '=', 'api_log.user_id')
                        ->select('api_log.client_name', 'api_log.response_status_code', 'api_log.created_at', 'api_log.vender as vendor','api_log.request', 'api_log.api_name as Module', 'users.name')
                        ->whereRaw("DATE(api_log.created_at) BETWEEN ? AND ?", [$sqlDateFrom, $sqlDateTo])
                        ->whereIn('api_log.client_name', $org)
                        ->get();
                }
            }

            foreach ($data as $item) {
                $jsonData = json_decode($item->request, true);
                $item->request = $this->getInput($item->Module, $item->vendor, $jsonData);
            }

            $csvarray = [];

            $csvarray[] = ['Client Name','User','Type API', 'Client Input', 'Timestamp','Status code'];

            foreach ($data as $row) {
                $csvarray[] = [
                    $row->client_name,
                    $row->name,
                    $row->Module,
                    $row->request,
                    $row->created_at,
                    $row->response_status_code,
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
                $file_url = $baseUrl . "/public/storage/uploads/rcbulk/" . $filename;
        
                rename($tempFilePath, $filePath);
        

        return response()->json(['download' => '1', 'file_url' => $file_url, 'file_name' => $filename], 200);

        }


    }
}
