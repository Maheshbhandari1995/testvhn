<?php
 
namespace App\Http\Controllers;

use Illuminate\Http\Request;
namespace App\Http\Controllers;
use Yajra\DataTables\Facades\DataTables;
use App\Models\Company;
use Illuminate\Validation\Rule;
use App\Models\Rcdetails;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\Buttons\Button;
use Yajra\DataTables\Buttons\DatatableButton;

class ReportController extends Controller
{
    public function getReportList(Request $request)
    {
        $sessionData = session('data');
        if ($request->ajax()) {

            if(isset($sessionData) && $sessionData['userRole'] == 'user')
            {
                $data = DB::table('session_log')
                ->leftjoin('users', 'users.id', '=', 'session_log.user_id')
                ->leftjoin('clients', 'users.client_id', '=', 'clients.id')
                ->where('session_log.status', '=', 1)
                ->whereNotIn('users.role', ['super_admin'])
                ->where('users.id', $sessionData['userID'])
                ->select('clients.name as client_name', 'session_log.*', 'users.name as user_name', 'users.role as role')
                ->latest()
                ->get();
            }
            else if(isset($sessionData) && $sessionData['userRole'] == 'admin')
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
                        $data = DB::table('session_log')
                        ->leftjoin('users', 'users.id', '=', 'session_log.user_id')
                        ->leftjoin('clients', 'users.client_id', '=', 'clients.id')
                        ->where('session_log.status', '=', 1)
                        ->whereNotIn('users.role', ['super_admin'])
                        ->where('users.client_id', $sessionData['Client_id'])
                        ->select('clients.name as Org_name','users.name as User_Name', 'users.role as Role', 'session_log.ip_address as Ip_Address','session_log.login_status as Login_status','session_log.created_at as Login_time','session_log.updated_at as Logout_time')
                        ->whereRaw("DATE(session_log.created_at) BETWEEN ? AND ?", [$sqlDateFrom, $sqlDateTo])
                        ->whereIn('clients.name', $organizations)
                        ->get();
                    }
                    else{
                        $data = DB::table('session_log')
                        ->leftjoin('users', 'users.id', '=', 'session_log.user_id')
                        ->leftjoin('clients', 'users.client_id', '=', 'clients.id')
                        ->where('session_log.status', '=', 1)
                        ->whereNotIn('users.role', ['super_admin'])
                        ->where('users.client_id', $sessionData['Client_id'])
                        ->select('clients.name as Org_name','users.name as User_Name', 'users.role as Role', 'session_log.ip_address as Ip_Address','session_log.login_status as Login_status','session_log.created_at as Login_time','session_log.updated_at as Logout_time')
                        ->whereRaw("DATE(session_log.created_at) BETWEEN ? AND ?", [$sqlDateFrom, $sqlDateTo])
                        ->whereIn('clients.name', $org)
                        ->get();
                    }
                }else{
                    $data = DB::table('session_log')
                        ->leftjoin('users', 'users.id', '=', 'session_log.user_id')
                        ->leftjoin('clients', 'users.client_id', '=', 'clients.id')
                        ->where('session_log.status', '=', 1)
                        ->whereNotIn('users.role', ['super_admin'])
                        ->where('users.client_id', $sessionData['Client_id'])
                        ->select('clients.name as client_name', 'session_log.*', 'users.name as user_name', 'users.role as role')
                        ->latest()
                        ->get();
                }
            }
            else if(isset($sessionData) && $sessionData['userRole'] == 'super_admin')
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
                        $data = DB::table('session_log')
                        ->leftjoin('users', 'users.id', '=', 'session_log.user_id')
                        ->leftjoin('clients', 'users.client_id', '=', 'clients.id')
                        ->where('session_log.status', '=', 1)
                        ->whereNotIn('users.role', ['super_admin'])
                        ->select('clients.name as Org_name','users.name as User_Name', 'users.role as Role', 'session_log.ip_address as Ip_Address','session_log.login_status as Login_status','session_log.created_at as Login_time','session_log.updated_at as Logout_time')
                        ->whereRaw("DATE(session_log.created_at) BETWEEN ? AND ?", [$sqlDateFrom, $sqlDateTo])
                        ->whereIn('clients.name', $organizations)
                        ->get();

                    }else{

                        $data = DB::table('session_log')
                        ->leftjoin('users', 'users.id', '=', 'session_log.user_id')
                        ->leftjoin('clients', 'users.client_id', '=', 'clients.id')
                        ->where('session_log.status', '=', 1)
                        ->whereNotIn('users.role', ['super_admin'])
                        ->select('clients.name as Org_name','users.name as User_Name', 'users.role as Role', 'session_log.ip_address as Ip_Address','session_log.login_status as Login_status','session_log.created_at as Login_time','session_log.updated_at as Logout_time')
                        ->whereRaw("DATE(session_log.created_at) BETWEEN ? AND ?", [$sqlDateFrom, $sqlDateTo])
                        ->whereIn('clients.name', $org)
                        ->get();

                    }

                }else{

                    $data = DB::table('session_log')
                    ->leftjoin('users', 'users.id', '=', 'session_log.user_id')
                    ->leftjoin('clients', 'users.client_id', '=', 'clients.id')
                    ->where('session_log.status', '=', 1)
                    ->whereNotIn('users.role', ['super_admin'])
                    ->select('clients.name as client_name', 'session_log.*', 'users.name as user_name', 'users.role as role')
                    ->latest()
                    ->get();
                }
            }
        
            return DataTables::of($data)
                ->make(true);
        }

        return abort(404);

    }

    public function getOrganizationNames()
    {
        $organizations = Company::whereIn('del_status', [1,0])->pluck('name')->toArray();

        return response()->json($organizations);
    }

    public function getLoginActivityReportCsv(Request $request)
    {
        $sessionData = session('data');        
        if(isset($sessionData) && $sessionData['userRole'] == 'user')
        {
            $data = DB::table('session_log')
            ->leftjoin('users', 'users.id', '=', 'session_log.user_id')
            ->leftjoin('clients', 'users.client_id', '=', 'clients.id')
            ->where('session_log.status', '=', 1)
            ->whereNotIn('users.role', ['super_admin'])
            ->where('users.id', $sessionData['userID'])
            ->select('clients.name as client_name', 'session_log.*', 'users.name as user_name')
            ->latest()
            ->get();
        }
        else if(isset($sessionData) && $sessionData['userRole'] == 'admin')
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
                    $data = DB::table('session_log')
                        ->leftjoin('users', 'users.id', '=', 'session_log.user_id')
                        ->leftjoin('clients', 'users.client_id', '=', 'clients.id')
                        ->where('session_log.status', '=', 1)
                        ->whereNotIn('users.role', ['super_admin'])
                        ->where('users.client_id', $sessionData['Client_id'])
                        ->select('clients.name as client_name','users.name as user_name', 'users.role as role', 'session_log.ip_address as ip_address','session_log.login_status as login_status','session_log.created_at as created_at','session_log.updated_at as updated_at')
                        ->whereRaw("DATE(session_log.created_at) BETWEEN ? AND ?", [$sqlDateFrom, $sqlDateTo])
                        ->whereIn('clients.name', $organizations)
                        ->get();

                }else{

                    $data = DB::table('session_log')
                        ->leftjoin('users', 'users.id', '=', 'session_log.user_id')
                        ->leftjoin('clients', 'users.client_id', '=', 'clients.id')
                        ->where('session_log.status', '=', 1)
                        ->whereNotIn('users.role', ['super_admin'])
                        ->where('users.client_id', $sessionData['Client_id'])
                        ->select('clients.name as client_name','users.name as user_name', 'users.role as role', 'session_log.ip_address as ip_address','session_log.login_status as login_status','session_log.created_at as created_at','session_log.updated_at as updated_at')
                        ->whereRaw("DATE(session_log.created_at) BETWEEN ? AND ?", [$sqlDateFrom, $sqlDateTo])
                        ->whereIn('clients.name', $org)
                        ->get();

                }
            }else{

                $data = DB::table('session_log')
                ->leftjoin('users', 'users.id', '=', 'session_log.user_id')
                ->leftjoin('clients', 'users.client_id', '=', 'clients.id')
                ->where('session_log.status', '=', 1)
                ->whereNotIn('users.role', ['super_admin'])
                ->where('users.client_id', $sessionData['Client_id'])
                ->select('clients.name as client_name', 'session_log.*', 'users.name as user_name')
                ->latest()
                ->get();
            }
        }
        else if(isset($sessionData) && $sessionData['userRole'] == 'super_admin')
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
                        $data = DB::table('session_log')
                        ->leftjoin('users', 'users.id', '=', 'session_log.user_id')
                        ->leftjoin('clients', 'users.client_id', '=', 'clients.id')
                        ->where('session_log.status', '=', 1)
                        ->whereNotIn('users.role', ['super_admin'])
                        ->select('clients.name as client_name','users.name as user_name', 'users.role as role', 'session_log.ip_address as ip_address','session_log.login_status as login_status','session_log.created_at as created_at','session_log.updated_at as updated_at')
                        ->whereRaw("DATE(session_log.created_at) BETWEEN ? AND ?", [$sqlDateFrom, $sqlDateTo])
                        ->whereIn('clients.name', $organizations)
                        ->get();

                    }else{

                        $data = DB::table('session_log')
                        ->leftjoin('users', 'users.id', '=', 'session_log.user_id')
                        ->leftjoin('clients', 'users.client_id', '=', 'clients.id')
                        ->where('session_log.status', '=', 1)
                        ->whereNotIn('users.role', ['super_admin'])
                        ->select('clients.name as Org_name','users.name as User_Name', 'users.role as Role', 'session_log.ip_address as ip_address','session_log.login_status as login_status','session_log.created_at as created_at','session_log.updated_at as updated_at')
                        ->whereRaw("DATE(session_log.created_at) BETWEEN ? AND ?", [$sqlDateFrom, $sqlDateTo])
                        ->whereIn('clients.name', $org)
                        ->get();

                    }

                }else{
                $data = DB::table('session_log')
                ->leftjoin('users', 'users.id', '=', 'session_log.user_id')
                ->leftjoin('clients', 'users.client_id', '=', 'clients.id')
                ->where('session_log.status', '=', 1)
                ->whereNotIn('users.role', ['super_admin'])
                ->select('clients.name as client_name', 'session_log.*', 'users.name as user_name', 'users.role as role')
                ->latest()
                ->get();
                }
        }

        $csvarray = [];
        $csvarray[] = ['organization', 'User', 'Role','IP Address','Login Status', 'Login Time', 'Logout Time'];

        foreach ($data as $row) {
            $csvarray[] = [
                $row->client_name,
                $row->user_name,
                $row->role,
                $row->ip_address,
                ($row->login_status == 1) ? 'Active' : 'Logged Out',
                $row->created_at,
                $row->updated_at,
            ];
        }

        $timestamp = date('Y_m_d_H_i_s');
        $filename = 'LoginActivityReport_' . $timestamp . '.csv';
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"$filename\""
        ];
        $tempFilePath = tempnam(sys_get_temp_dir(), 'LoginActivityReport');
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
