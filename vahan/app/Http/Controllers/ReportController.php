<?php
 
namespace App\Http\Controllers;

use Illuminate\Http\Request;
namespace App\Http\Controllers;
use Yajra\DataTables\Facades\DataTables;
use App\Models\Company;
use App\Models\Users;
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
        ini_set('memory_limit', '512M');
        if ($request->ajax()) {

            if(isset($sessionData) && $sessionData['userRole'] == 'admin')
            {
                
                $dateFrom = $request->input('date_from');
                $dateTo = $request->input('date_to');
                $sqlDateFrom = date('Y-m-d', strtotime($dateFrom));
                $sqlDateTo = date('Y-m-d', strtotime($dateTo));
                $org = $request->input('org');
                $all_value = $org[0];
                $clientid = $sessionData['Client_id'];

                if(isset($dateFrom) && isset($dateTo))
                {   
                    if($all_value === "All"){
                        //echo 2;
                        $username = Users::where('users.client_id','=',$clientid)->where('users.role','=','user')->pluck('name')->toArray();

                        $data = DB::table('session_log')
                        ->leftjoin('users', 'users.id', '=', 'session_log.user_id')
                        ->leftjoin('clients', 'users.client_id', '=', 'clients.id')
                        ->where('session_log.status', '=', 1)
                        ->whereNotIn('users.role', ['super_admin','admin'])
                        ->where('users.client_id', $sessionData['Client_id'])
                        ->select('clients.name as Organization','users.name as Name', 'users.role as Role', 'session_log.ip_address as Ip_Address','session_log.login_status as Login_status','session_log.created_at as Login_time','session_log.updated_at as Logout_time')
                        ->whereRaw("DATE(session_log.created_at) BETWEEN ? AND ?", [$sqlDateFrom, $sqlDateTo])
                        ->whereIn('users.name', $username)
                        ->orderBy('Login_time', 'desc')
                        ->get();
                    }
                    else{
                        $data = DB::table('session_log')
                        ->leftjoin('users', 'users.id', '=', 'session_log.user_id')
                        ->leftjoin('clients', 'users.client_id', '=', 'clients.id')
                        ->where('session_log.status', '=', 1)
                        ->whereNotIn('users.role', ['super_admin','admin'])
                        ->where('users.client_id', $sessionData['Client_id'])
                        ->select('clients.name as Organization','users.name as Name', 'users.role as Role', 'session_log.ip_address as Ip_Address','session_log.login_status as Login_status','session_log.created_at as Login_time','session_log.updated_at as Logout_time')
                        ->whereRaw("DATE(session_log.created_at) BETWEEN ? AND ?", [$sqlDateFrom, $sqlDateTo])
                        ->whereIn('users.name', $org)
                        ->orderBy('Login_time', 'desc')
                        ->get();
                    }
                }else{
                    $data = DB::table('session_log')
                        ->leftjoin('users', 'users.id', '=', 'session_log.user_id')
                        ->leftjoin('clients', 'users.client_id', '=', 'clients.id')
                        ->where('session_log.status', '=', 1)
                        ->whereNotIn('users.role', ['super_admin','admin','mis'])
                        ->where('users.client_id', $sessionData['Client_id'])
                        ->select('clients.name as Organization', 'session_log.*', 'users.name as Name', 'users.role as role')
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
                        ->select('clients.name as Organization','users.name as Name', 'users.role as Role', 'session_log.ip_address as Ip_Address','session_log.login_status as Login_status','session_log.created_at as Login_time','session_log.updated_at as Logout_time')
                        ->whereRaw("DATE(session_log.created_at) BETWEEN ? AND ?", [$sqlDateFrom, $sqlDateTo])
                        ->whereIn('clients.name', $organizations)
                        ->orderBy('Login_time', 'desc')
                        ->get();

                    }else{

                        $data = DB::table('session_log')
                        ->leftjoin('users', 'users.id', '=', 'session_log.user_id')
                        ->leftjoin('clients', 'users.client_id', '=', 'clients.id')
                        ->where('session_log.status', '=', 1)
                        ->whereNotIn('users.role', ['super_admin'])
                        ->select('clients.name as Organization','users.name as Name', 'users.role as Role', 'session_log.ip_address as Ip_Address','session_log.login_status as Login_status','session_log.created_at as Login_time','session_log.updated_at as Logout_time')
                        ->whereRaw("DATE(session_log.created_at) BETWEEN ? AND ?", [$sqlDateFrom, $sqlDateTo])
                        ->whereIn('clients.name', $org)
                        ->orderBy('Login_time', 'desc')
                        ->get();

                    }

                }else{

                    $data = DB::table('session_log')
                    ->leftjoin('users', 'users.id', '=', 'session_log.user_id')
                    ->leftjoin('clients', 'users.client_id', '=', 'clients.id')
                    ->where('session_log.status', '=', 1)
                    ->whereNotIn('users.role', ['super_admin'])
                    ->select('clients.name as Organization', 'session_log.*', 'users.name as Name', 'users.role as role')
                    ->latest()
                    ->get();
                }
            }
        
            return DataTables::of($data)
                ->make(true);
        }

        return abort(404);

    }

    public function getUserNames(){
        $sessionData = session('data');
        $clientid = $sessionData['Client_id'];
        // $username =  DB::table('users')->select('username')->where('users.client_id','=',$clientid)->where('users.role','=','user')->get();
        $username = Users::where('users.client_id','=',$clientid)->where('users.role','=','user')->pluck('name')->toArray();

        // return $username;
       
        return response()->json($username);
    }

    public function getOrganizationNames()
    {
        $organizations = Company::whereIn('del_status', [1,0])->pluck('name')->toArray();

        return response()->json($organizations);
    }

    public function getLoginActivityReportCsv(Request $request)
    {
        $sessionData = session('data');
        ini_set('memory_limit', '512M');   

        if(isset($sessionData) && $sessionData['userRole'] == 'admin')
        {   
            $dateFrom = $request->input('date_from');
            $dateTo = $request->input('date_to');
            $sqlDateFrom = date('Y-m-d', strtotime($dateFrom));
            $sqlDateTo = date('Y-m-d', strtotime($dateTo));
            $org = $request->input('org');
            $all_value = $org[0];
            $clientid = $sessionData['Client_id'];

            if(isset($dateFrom) && isset($dateTo))
            {   
                if($all_value === "All"){

                        $username = Users::where('users.client_id','=',$clientid)->where('users.role','=','user')->pluck('name')->toArray();

                        $data = DB::table('session_log')
                        ->leftjoin('users', 'users.id', '=', 'session_log.user_id')
                        ->leftjoin('clients', 'users.client_id', '=', 'clients.id')
                        ->where('session_log.status', '=', 1)
                        ->whereIn('users.name', $username)
                        ->whereIn('users.role', ['user'])
                        ->where('users.client_id', $sessionData['Client_id'])
                        ->select('clients.name as Organization','users.name as Name', 'users.role as Role', 'session_log.ip_address as Ip_Address','session_log.login_status as Login_status','session_log.created_at as Login_time','session_log.updated_at as Logout_time')
                        ->whereRaw("DATE(session_log.created_at) BETWEEN ? AND ?", [$sqlDateFrom, $sqlDateTo])
                        ->orderBy('Login_time', 'desc')
                        ->get();


                }else{

                        $data = DB::table('session_log')
                        ->leftjoin('users', 'users.id', '=', 'session_log.user_id')
                        ->leftjoin('clients', 'users.client_id', '=', 'clients.id')
                        ->where('session_log.status', '=', 1)
                        ->whereIn('users.name', $org)
                        ->whereIn('users.role', ['user'])
                        ->where('users.client_id', $sessionData['Client_id'])
                        ->select('clients.name as Organization','users.name as Name', 'users.role as Role', 'session_log.ip_address as Ip_Address','session_log.login_status as Login_status','session_log.created_at as Login_time','session_log.updated_at as Logout_time')
                        ->whereRaw("DATE(session_log.created_at) BETWEEN ? AND ?", [$sqlDateFrom, $sqlDateTo])
                        ->orderBy('Login_time', 'desc')
                        ->get();

                }
            }else{

                $data = DB::table('session_log')
                ->leftjoin('users', 'users.id', '=', 'session_log.user_id')
                ->leftjoin('clients', 'users.client_id', '=', 'clients.id')
                ->whereIn('users.role', ['user'])
                ->where('users.client_id', $sessionData['Client_id'])
                ->select('clients.name as Organization','users.name as Name', 'users.role as Role', 'session_log.ip_address as Ip_Address','session_log.login_status as Login_status','session_log.created_at as Login_time','session_log.updated_at as Logout_time')
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

                        $organizations = Company::whereIn('del_status', [1,0])->pluck('name')->toArray();
                        $data = DB::table('session_log')
                        ->leftjoin('users', 'users.id', '=', 'session_log.user_id')
                        ->leftjoin('clients', 'users.client_id', '=', 'clients.id')
                        ->where('session_log.status', '=', 1)
                        ->whereNotIn('users.role', ['super_admin'])
                        ->select('clients.name as Organization','users.name as Name', 'users.role as Role', 'session_log.ip_address as Ip_Address','session_log.login_status as Login_status','session_log.created_at as Login_time','session_log.updated_at as Logout_time')
                        ->whereRaw("DATE(session_log.created_at) BETWEEN ? AND ?", [$sqlDateFrom, $sqlDateTo])
                        ->whereIn('clients.name', $organizations)
                        ->orderBy('Login_time', 'desc')
                        ->get();

                    }else{

                        $data = DB::table('session_log')
                        ->leftjoin('users', 'users.id', '=', 'session_log.user_id')
                        ->leftjoin('clients', 'users.client_id', '=', 'clients.id')
                        ->where('session_log.status', '=', 1)
                        ->whereNotIn('users.role', ['super_admin'])
                        ->select('clients.name as Organization','users.name as Name', 'users.role as Role', 'session_log.ip_address as Ip_Address','session_log.login_status as Login_status','session_log.created_at as Login_time','session_log.updated_at as Logout_time')
                        ->whereRaw("DATE(session_log.created_at) BETWEEN ? AND ?", [$sqlDateFrom, $sqlDateTo])
                        ->whereIn('clients.name', $org)
                        ->orderBy('Login_time', 'desc')
                        ->get();

                    }

                }else{

                    $data = DB::table('session_log')
                    ->leftjoin('users', 'users.id', '=', 'session_log.user_id')
                    ->leftjoin('clients', 'users.client_id', '=', 'clients.id')
                    ->where('session_log.status', '=', 1)
                    ->whereNotIn('users.role', ['super_admin'])
                    ->select('clients.name as Organization','users.name as Name', 'users.role as Role', 'session_log.ip_address as Ip_Address','session_log.login_status as Login_status','session_log.created_at as Login_time','session_log.updated_at as Logout_time')
                    ->latest()
                    ->get();
                }
        }

        $csvarray = [];

        $sessionData = $request->session()->get('data');
        $userRole = $sessionData['userRole'] ?? '';

        if ($userRole == 'super_admin') {

            $csvarray[] = ['Organization', 'User', 'Role','IP Address','Login Status', 'Login Time', 'Logout Time'];

            foreach ($data as $row) {
                $csvarray[] = [
                    $row->Organization,
                    $row->Name,
                    $row->Role,
                    $row->Ip_Address,
                    ($row->Login_status == 1) ? 'Active' : 'Logged Out',
                    $row->Login_time,
                    $row->Logout_time,
                ];
            }

        }else{

            $csvarray[] = ['User', 'Role','IP Address','Login Status', 'Login Time', 'Logout Time'];

            foreach ($data as $row) {
                $csvarray[] = [
                    $row->Name,
                    $row->Role,
                    $row->Ip_Address,
                    ($row->Login_status == 1) ? 'Active' : 'Logged Out',
                    $row->Login_time,
                    $row->Logout_time,
                ];
            }
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
        //$file_url = $baseUrl . "/public/storage/uploads/rcbulk/" . $filename;
        $file_url = $baseUrl . "/storage/app/public/uploads/rcbulk/" . $filename;

        rename($tempFilePath, $filePath);
        chmod($filePath, 0755);

        return response()->json(['download' => '1', 'file_url' => $file_url, 'file_name' => $filename], 200);
        
    }
}
