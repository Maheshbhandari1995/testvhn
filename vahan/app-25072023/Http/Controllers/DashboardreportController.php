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

class DashboardreportController extends Controller
{
    public function getDashBoardReportList(Request $request)
    {
        
        $sessionData = session('data');

        if ($request->ajax()) {

            if(isset($sessionData) && $sessionData['userRole'] == 'user')
            {
                $data = DB::table('api_log')
                ->leftJoin('clients', 'api_log.client_id', '=', 'clients.id')
                ->select('api_log.*', 'clients.max_count as max_count')
                ->whereNotNull('api_log.api_name') // Check if api_name is not null
                ->whereIn('api_log.status', [0,1])
                ->where('user_id', $sessionData['userID'])
                ->latest()
                ->get();
            }
            else if(isset($sessionData) && $sessionData['userRole'] == 'super_admin' || isset($sessionData) && $sessionData['userRole'] == 'admin')
            {   
                $dateFrom = $request->input('date_from');
                $dateTo = $request->input('date_to');
                $sqlDateFrom = date('Y-m-d', strtotime($dateFrom));
                $sqlDateTo = date('Y-m-d', strtotime($dateTo));
                $org = $request->input('org');
                $all_value = $org[0];


                if(isset($dateFrom) && isset($dateTo))
                {   
                    if($all_value === "All"){
                        //echo 2;
                        $organizations = Company::whereIn('del_status', [1,0])->pluck('name')->toArray();
                        $data = DB::table('api_log')
                        ->leftJoin('clients', 'api_log.client_id', '=', 'clients.id')
                        ->leftJoin('users', 'clients.id', '=', 'users.client_id') // Added join with the users table
                    ->select('api_log.client_name as organization','users.username as username','api_log.vender as vendor','api_log.api_name as Module','api_log.request','api_log.response_status_code','api_log.created_at as timestamp')
                        ->whereNotNull('api_log.api_name')
                        ->whereIn('api_log.status', [0, 1])
                        ->whereRaw("DATE(api_log.created_at) BETWEEN ? AND ?", [$sqlDateFrom, $sqlDateTo])
                        ->whereIn('clients.name', $organizations)
                        ->get();

                    }else{
                        //echo 3;
                        $data = DB::table('api_log')
                        ->leftJoin('clients', 'api_log.client_id', '=', 'clients.id')
                        ->leftJoin('users', 'clients.id', '=', 'users.client_id') // Added join with the users table
                    ->select('api_log.client_name as organization','users.username as username','api_log.vender as vendor','api_log.api_name as Module','api_log.request','api_log.response_status_code','api_log.created_at as timestamp')
                        ->whereNotNull('api_log.api_name')
                        ->whereIn('api_log.status', [0, 1])
                        ->whereRaw("DATE(api_log.created_at) BETWEEN ? AND ?", [$sqlDateFrom, $sqlDateTo])
                        ->whereIn('clients.name', $org)
                        ->get();

                    }
                }
                else{
                    //echo 4;
                    $data = DB::table('api_log')
                    ->leftJoin('clients', 'api_log.client_id', '=', 'clients.id')
                    ->leftJoin('users', 'clients.id', '=', 'users.client_id') // Added join with the users table
                    ->select('api_log.client_name as organization','users.username as username','api_log.vender as vendor','api_log.api_name as Module','api_log.request','api_log.response_status_code','api_log.created_at as timestamp')
                    ->whereNotNull('api_log.api_name') // Check if api_name is not null
                    ->whereIn('api_log.status', [0,1])
                    ->get();
                }
            } 
            
            foreach ($data as $item) {
                $jsonData = json_decode($item->request, true);
                $item->request = $this->getInput($item->Module, $item->vendor, $jsonData);
               
            }

            return DataTables::of($data)
                ->make(true);
        }

        return abort(404);

    }
 
    private function getInput($moudle, $vendor, $jsonData)
    {
        $input = '';
        if(($moudle == 'rc' || $moudle == 'challan' )&& $vendor == 'authbridge')
        {
            $input = $jsonData['docNumber'];
        }
        else if(($moudle == 'rc' || $moudle == 'challan') && $vendor == 'signzy')
        {
            $input = $jsonData['essentials']['vehicleNumber'];
        }
        else if($moudle == 'license' && $vendor == 'signzy'){
            $input = $jsonData['essentials']['number'];
        }
        else if($moudle == 'license' && $vendor == 'authbridge'){
            $input = $jsonData['essentials']['number'];
        }
        else if($moudle == 'challan' && $vendor == 'rto'){
            $input = $jsonData['vehicle_number'];
        }
        else if($moudle == 'rc_logic' && $vendor == 'edas_internal'){
            $input = $jsonData['Vehicle_No'];
        }
        return $input;
    }

    public function getOrganizationNames()
    {
        $organizations = Company::whereIn('del_status', [1,0])->pluck('name')->toArray();

        return response()->json($organizations);
    }

    public function getDashboardReportCsv(Request $request)
{
    $dateFrom = $request->input('date_from');
    $dateTo = $request->input('date_to');
    $sqlDateFrom = date('Y-m-d', strtotime($dateFrom));
    $sqlDateTo = date('Y-m-d', strtotime($dateTo));
    $org = $request->input('org');
    $all_value = $org[0];

    if (isset($dateFrom) && isset($dateTo)) {
        if ($all_value === "All") {
            $organizations = Company::whereIn('del_status', [1, 0])->pluck('name')->toArray();
            $data = DB::table('api_log')
                ->leftJoin('clients', 'api_log.client_id', '=', 'clients.id')
                ->leftJoin('users', 'clients.id', '=', 'users.client_id') // Added join with the users table
                    ->select('api_log.client_name as organization','users.username as username','api_log.vender as vendor','api_log.api_name as Module','api_log.request','api_log.response_status_code','api_log.created_at as timestamp')
                // ->select('api_log.client_name as organization', 'api_log.vender as vendor', 'api_log.api_name as Module','api_log.request','api_log.response_status_code')
                ->whereNotNull('api_log.api_name')
                ->whereIn('api_log.status', [0, 1])
                ->whereRaw("DATE(api_log.created_at) BETWEEN ? AND ?", [$sqlDateFrom, $sqlDateTo])
                ->whereIn('clients.name', $organizations)
                ->get();
        } else {
            $data = DB::table('api_log')
                ->leftJoin('clients', 'api_log.client_id', '=', 'clients.id')
                ->leftJoin('users', 'clients.id', '=', 'users.client_id') // Added join with the users table
                    ->select('api_log.client_name as organization','users.username as username','api_log.vender as vendor','api_log.api_name as Module','api_log.request','api_log.response_status_code','api_log.created_at as timestamp')
                // ->select('api_log.client_name as organization', 'api_log.vender as vendor', 'api_log.api_name as Module','api_log.request','api_log.response_status_code')
                ->whereNotNull('api_log.api_name')
                ->whereIn('api_log.status', [0, 1])
                ->whereRaw("DATE(api_log.created_at) BETWEEN ? AND ?", [$sqlDateFrom, $sqlDateTo])
                ->whereIn('clients.name', $org)
                ->get();
        }

        foreach ($data as $item) {
            $jsonData = json_decode($item->request, true);
            $item->request = $this->getInput($item->Module, $item->vendor, $jsonData);
        }

        $csvarray = [];

        $sessionData = $request->session()->get('data');
        $userRole = $sessionData['userRole'] ?? '';

        if ($userRole == 'admin') {
            $csvarray[] = ['organization','username', 'Module', 'request', 'response_status_code','timestamp'];

            foreach ($data as $row) {
                $csvarray[] = [
                    $row->organization,
                    $row->username,
                    $row->Module,
                    $row->request,
                    $row->response_status_code,
                    $row->timestamp,
                ];
            }
        } else {
            $csvarray[] = ['organization','username','vendor', 'Module', 'request', 'response_status_code','timestamp'];

            foreach ($data as $row) {
                $csvarray[] = [
                    $row->organization,
                    $row->username,
                    $row->vendor,
                    $row->Module,
                    $row->request,
                    $row->response_status_code,
                    $row->timestamp,
                ];
            }
        }

        $timestamp = date('Y_m_d_H_i_s');
                $filename = 'report_' . $timestamp . '.csv';
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
