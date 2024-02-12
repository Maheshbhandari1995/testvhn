<?php

namespace App\Http\Controllers;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use App\Models\Company;
use App\Models\Users;
use Illuminate\Validation\Rule;
use App\Models\Rcdetails;
use Yajra\DataTables\Buttons\Button;
use Yajra\DataTables\Buttons\DatatableButton;
use Illuminate\Support\Facades\Session;

class DashboardreportController extends Controller
{
    public function getDashBoardReportList(Request $request)
    {
        ini_set('memory_limit', '512M');
        $sessionData = session('data');

        if ($request->ajax()) {

            if(isset($sessionData) && $sessionData['userRole'] == 'user')
            {
                $data = DB::table('api_log')
                ->leftJoin('clients', 'api_log.client_id', '=', 'clients.id')
                ->select('api_log.*', 'clients.max_count as max_count')
                ->whereIn('api_log.status', [0,1])
                ->where('user_id', $sessionData['userID'])
                ->latest()
                ->get();
            }
            else if($sessionData['userRole'] == 'super_admin')
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
                        $data_apilog = DB::table('api_log')
                        ->leftJoin('clients', 'api_log.client_id', '=', 'clients.id')
                        ->leftJoin('users', 'api_log.user_id', '=', 'users.id') // Added join with the users table
                        ->select('api_log.client_name as Organization','users.name as Username','api_log.vender as Vendor', DB::raw("UPPER(api_log.api_name) as Module"),'api_log.input as Request','api_log.response_status_code', DB::raw("
                        CASE
                            WHEN api_log.api_name = 'rc' AND api_log.request_type = 1 THEN 'Single rc'
                            WHEN api_log.api_name = 'rc' AND api_log.request_type = 2 THEN 'Bulk rc'
                            ELSE NULL
                        END
                        as Rcdetails"
                        ),'api_log.created_at as Timestamp')
                        ->whereIn('api_log.status', [0, 1])
                        ->whereRaw("DATE(api_log.created_at) BETWEEN ? AND ?", [$sqlDateFrom, $sqlDateTo])
                        ->limit(10000)
                        ->whereIn('api_log.client_name', $organizations);
                        // ->orderBy('api_log.created_at', 'desc');
                        // ->get();

                        $data_apiarchivelog = DB::table('api_log_archive')
                        ->leftJoin('clients', 'api_log_archive.client_id', '=', 'clients.id')
                        ->leftJoin('users', 'api_log_archive.user_id', '=', 'users.id') // Added join with the users table
                        ->select('api_log_archive.client_name as Organization','users.name as Username','api_log_archive.vender as Vendor',DB::raw("UPPER(api_log_archive.api_name) as Module"),'api_log_archive.input as Request','api_log_archive.response_status_code',DB::raw("
                        CASE
                            WHEN api_log_archive.api_name = 'rc' AND api_log_archive.request_type = 1 THEN 'Single rc'
                            WHEN api_log_archive.api_name = 'rc' AND api_log_archive.request_type = 2 THEN 'Bulk rc'
                            ELSE NULL
                        END
                        as Rcdetails"
                        ),'api_log_archive.created_at as Timestamp')
                        ->whereIn('api_log_archive.status', [0, 1])
                        ->whereRaw("DATE(api_log_archive.created_at) BETWEEN ? AND ?", [$sqlDateFrom, $sqlDateTo])
                        ->limit(10000)
                        ->whereIn('api_log_archive.client_name', $organizations);
                        // ->orderBy('api_log_archive.created_at', 'desc');
                        // ->get();

                        // $data = $data_apiarchivelog->union($data_apilog)->get();
                        $data = $data_apilog->union($data_apiarchivelog)
                            ->orderBy('Timestamp', 'desc') // Ordering by 'Timestamp' column in descending order
                            ->get();
                        //$data = $data_apiarchivelog->merge($data_apilog); 

                        // $data = $data_apiarchivelog->get();
                        

                    }else{
                        //echo 3;
                        $data_apilog = DB::table('api_log')
                        ->leftJoin('clients', 'api_log.client_id', '=', 'clients.id')
                        ->leftJoin('users', 'api_log.user_id', '=', 'users.id') // Added join with the users table
                        ->select('api_log.client_name as Organization','users.name as Username','api_log.vender as Vendor',DB::raw("UPPER(api_log.api_name) as Module"),'api_log.input as Request','api_log.response_status_code',DB::raw("
                        CASE
                            WHEN api_log.api_name = 'rc' AND api_log.request_type = 1 THEN 'Single rc'
                            WHEN api_log.api_name = 'rc' AND api_log.request_type = 2 THEN 'Bulk rc'
                            ELSE NULL
                        END
                        as Rcdetails"
                        ),'api_log.created_at as Timestamp')
                        ->whereIn('api_log.status', [0, 1])
                        ->whereRaw("DATE(api_log.created_at) BETWEEN ? AND ?", [$sqlDateFrom, $sqlDateTo])
                        ->limit(10000)
                        ->whereIn('api_log.client_name', $org);
                        // ->orderBy('api_log.created_at', 'desc');
                        // ->get();

                        $data_apiarchivelog = DB::table('api_log_archive')
                        ->leftJoin('clients', 'api_log_archive.client_id', '=', 'clients.id')
                        ->leftJoin('users', 'api_log_archive.user_id', '=', 'users.id') // Added join with the users table
                        ->select('api_log_archive.client_name as Organization','users.name as Username','api_log_archive.vender as Vendor',DB::raw("UPPER(api_log_archive.api_name) as Module"),'api_log_archive.input as Request','api_log_archive.response_status_code',DB::raw("
                        CASE
                            WHEN api_log_archive.api_name = 'rc' AND api_log_archive.request_type = 1 THEN 'Single rc'
                            WHEN api_log_archive.api_name = 'rc' AND api_log_archive.request_type = 2 THEN 'Bulk rc'
                            ELSE NULL
                        END
                        as Rcdetails"
                        ),'api_log_archive.created_at as Timestamp')
                        ->whereIn('api_log_archive.status', [0, 1])
                        ->whereRaw("DATE(api_log_archive.created_at) BETWEEN ? AND ?", [$sqlDateFrom, $sqlDateTo])
                        ->limit(10000)
                        ->whereIn('api_log_archive.client_name', $org);
                        // ->orderBy('api_log_archive.created_at', 'desc');
                        // ->get();

                        // $data = $data_apiarchivelog->union($data_apilog)->get();
                        $data = $data_apilog->union($data_apiarchivelog)
                            ->orderBy('Timestamp', 'desc') // Ordering by 'Timestamp' column in descending order
                            ->get();

                    }
                }
                else{
                    //echo 4;
                    $data = DB::table('api_log')
                    ->leftJoin('clients', 'api_log.client_id', '=', 'clients.id')
                    ->leftJoin('users', 'api_log.user_id', '=', 'users.id') // Added join with the users table
                    ->select('api_log.client_name as Organization','users.name as Username','api_log.vender as Vendor',DB::raw("UPPER(api_log.api_name) as Module"),'api_log.input as Request','api_log.response_status_code', DB::raw("
                    CASE
                        WHEN api_log.api_name = 'rc' AND api_log.request_type = 1 THEN 'Single rc'
                        WHEN api_log.api_name = 'rc' AND api_log.request_type = 2 THEN 'Bulk rc'
                        ELSE NULL
                    END
                    as Rcdetails"
                    ),'api_log.created_at as Timestamp')
                        ->whereIn('api_log.status', [0,1])
                        ->orderBy('api_log.created_at', 'desc')
                        ->limit(10000)
                        ->get();
                }
            }
            else if(isset($sessionData) && $sessionData['userRole'] == 'admin'){

                $sessionData = session('data');
                // print_r($sessionData);
                // exit;
                $clientid = $sessionData['Client_id'];
                $userid = $sessionData['userID'];
                $dateFrom = $request->input('date_from');
                $dateTo = $request->input('date_to');
                $sqlDateFrom = date('Y-m-d', strtotime($dateFrom));
                $sqlDateTo = date('Y-m-d', strtotime($dateTo));
                $org = $request->input('org');
                $all_value = $org[0];

                if(isset($dateFrom) && isset($dateTo))
                {   
                        if($all_value === 'All'){

                            $username = Users::where('role', 'user')->where('client_id','=',$clientid)->pluck('name')->toArray();
                            
                            $data_apilog = DB::table('api_log')
                            ->leftJoin('clients', 'api_log.client_id', '=', 'clients.id')
                            ->leftJoin('users', 'api_log.user_id', '=', 'users.id') // Added join with the users table
                        ->select('api_log.client_name as Organization','users.name as Username','api_log.vender as Vendor',DB::raw("UPPER(api_log.api_name) as Module"),'api_log.input as Request','api_log.response_status_code', DB::raw("
                        CASE
                            WHEN api_log.api_name = 'rc' AND api_log.request_type = 1 THEN 'Single rc'
                            WHEN api_log.api_name = 'rc' AND api_log.request_type = 2 THEN 'Bulk rc'
                            ELSE NULL
                        END
                        as Rcdetails"
                    ),'api_log.created_at as Timestamp','api_log.response_message as Remark')
                            ->whereIn('api_log.status', [0, 1])
                            ->whereRaw("DATE(api_log.created_at) BETWEEN ? AND ?", [$sqlDateFrom, $sqlDateTo])
                            ->limit(10000)
                            ->whereIn('users.name', $username);
                            // ->orderBy('api_log.created_at', 'desc');
                            // ->get();

                            $data_apiarchivelog = DB::table('api_log_archive')
                            ->leftJoin('clients', 'api_log_archive.client_id', '=', 'clients.id')
                            ->leftJoin('users', 'api_log_archive.user_id', '=', 'users.id') // Added join with the users table
                        ->select('api_log_archive.client_name as Organization','users.name as Username','api_log_archive.vender as Vendor',DB::raw("UPPER(api_log_archive.api_name) as Module"),'api_log_archive.input as Request','api_log_archive.response_status_code', DB::raw("
                        CASE
                            WHEN api_log_archive.api_name = 'rc' AND api_log_archive.request_type = 1 THEN 'Single rc'
                            WHEN api_log_archive.api_name = 'rc' AND api_log_archive.request_type = 2 THEN 'Bulk rc'
                            ELSE NULL
                        END
                        as Rcdetails"
                    ),'api_log_archive.created_at as Timestamp','api_log_archive.response_message as Remark')
                            ->whereIn('api_log_archive.status', [0, 1])
                            ->whereRaw("DATE(api_log_archive.created_at) BETWEEN ? AND ?", [$sqlDateFrom, $sqlDateTo])
                            ->limit(10000)
                            ->whereIn('users.name', $username);
                            // ->orderBy('api_log_archive.created_at', 'desc');
                            // ->get();

                            //$data = $data_apiarchivelog->union($data_apilog)->get();
                            $data = $data_apilog->union($data_apiarchivelog)
                            ->orderBy('Timestamp', 'desc') // Ordering by 'Timestamp' column in descending order
                            ->get();

                        }else{

                            $data_apilog = DB::table('api_log')
                            ->leftJoin('clients', 'api_log.client_id', '=', 'clients.id')
                            ->leftJoin('users', 'api_log.user_id', '=', 'users.id') // Added join with the users table
                        ->select('api_log.client_name as Organization','users.name as Username','api_log.vender as Vendor',DB::raw("UPPER(api_log.api_name) as Module"),'api_log.input as Request','api_log.response_status_code', DB::raw("
                        CASE
                            WHEN api_log.api_name = 'rc' AND api_log.request_type = 1 THEN 'Single rc'
                            WHEN api_log.api_name = 'rc' AND api_log.request_type = 2 THEN 'Bulk rc'
                            ELSE NULL
                        END
                        as Rcdetails"
                    ),'api_log.created_at as Timestamp','api_log.response_message as Remark')
                            ->whereIn('api_log.status', [0, 1])
                            ->whereRaw("DATE(api_log.created_at) BETWEEN ? AND ?", [$sqlDateFrom, $sqlDateTo])
                            ->limit(10000)
                            ->whereIn('users.name', $org);
                            // ->orderBy('api_log.created_at', 'desc');
                            // ->get();

                            $data_apiarchivelog = DB::table('api_log_archive')
                            ->leftJoin('clients', 'api_log_archive.client_id', '=', 'clients.id')
                            ->leftJoin('users', 'api_log_archive.user_id', '=', 'users.id') // Added join with the users table
                        ->select('api_log_archive.client_name as Organization','users.name as Username','api_log_archive.vender as Vendor',DB::raw("UPPER(api_log_archive.api_name) as Module"),'api_log_archive.input as Request','api_log_archive.response_status_code', DB::raw("
                        CASE
                            WHEN api_log_archive.api_name = 'rc' AND api_log_archive.request_type = 1 THEN 'Single rc'
                            WHEN api_log_archive.api_name = 'rc' AND api_log_archive.request_type = 2 THEN 'Bulk rc'
                            ELSE NULL
                        END
                        as Rcdetails"
                    ),'api_log_archive.created_at as Timestamp','api_log_archive.response_message as Remark')
                            ->whereIn('api_log_archive.status', [0, 1])
                            ->whereRaw("DATE(api_log_archive.created_at) BETWEEN ? AND ?", [$sqlDateFrom, $sqlDateTo])
                            ->limit(10000)
                            ->whereIn('users.name', $org);
                            // ->orderBy('api_log_archive.created_at', 'desc');
                            // ->get();

                            //$data = $data_apiarchivelog->union($data_apilog)->get();

                            //$data = $data_apilog->union($data_apiarchivelog)->get();
                            $data = $data_apilog->union($data_apiarchivelog)
                            ->orderBy('Timestamp', 'desc') // Ordering by 'Timestamp' column in descending order
                            ->get();
                        }

                       
                }
                else{

                    $username = Users::where('role', 'user')->where('client_id','=',$clientid)->pluck('name')->toArray();

                    $data = DB::table('api_log')
                    ->leftJoin('clients', 'api_log.client_id', '=', 'clients.id')
                    ->leftJoin('users', 'api_log.user_id', '=', 'users.id') // Added join with the users table
                    ->select('api_log.client_name as Organization','users.name as Username','api_log.vender as Vendor', DB::raw("UPPER(api_log.api_name) as Module"),'api_log.input as Request','api_log.response_status_code', DB::raw("
                    CASE
                        WHEN api_log.api_name = 'rc' AND api_log.request_type = 1 THEN 'Single rc'
                        WHEN api_log.api_name = 'rc' AND api_log.request_type = 2 THEN 'Bulk rc'
                        ELSE NULL
                    END
                    as Rcdetails"
                ),'api_log.created_at as Timestamp','api_log.response_message as Remark')
                    ->whereIn('api_log.status', [0,1])
                    ->whereIn('users.name', $username)
                    ->where('api_log.client_id','=',$clientid)
                    ->orderBy('api_log.created_at', 'desc')
                    ->limit(10000)
                    ->get();
                }
            }
            
            //return $data;

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
        else if($moudle == 'rc_chassis'){
            $input = $jsonData['chassisNumber'];
        }
        return $input;
    }

    public function getOrganizationNames()
    {   
        $sessionData = session('data');
        $organizations = Company::whereIn('del_status', [1,0])->pluck('name')->toArray();

        return response()->json($organizations);
    }

    public function getOrganizationAdminNames(){
        $sessionData = session('data');
        $clientid = $sessionData['Client_id'];
        $userid = $sessionData['userID'];
        
        $clientName = DB::select('
        SELECT clients.name
        FROM users
        JOIN clients ON users.client_id = clients.id
        WHERE users.role = ? AND users.client_id = ? AND users.id = ? and users.status = ?
    ', ['admin', $clientid, $userid,'1']);
    
        if (!empty($clientName)) {
           
            $clientName = $clientName[0]->name;
        } else {
            
            $clientName = 'No client found';
        }

        return response()->json($clientName);
    }

    public function getUserNames(){
        $sessionData = session('data');
        $clientid = $sessionData['Client_id'];
        // $username =  DB::table('users')->select('username')->where('users.client_id','=',$clientid)->where('users.role','=','user')->get();
        $username = Users::where('users.client_id','=',$clientid)->where('users.role','=','user')->pluck('name')->toArray();

        // return $username;
       
        return response()->json($username);
    }


    public function getDashboardReportCsv(Request $request)
{   
    ini_set('memory_limit', '512M');
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
                $data_apilog = DB::table('api_log')
                ->leftJoin('clients', 'api_log.client_id', '=', 'clients.id')
                ->leftJoin('users', 'api_log.user_id', '=', 'users.id') // Added join with the users table
                ->select('api_log.client_name as Organization','users.name as Username','api_log.vender as Vendor',DB::raw("UPPER(api_log.api_name) as Module"),'api_log.input as Request','api_log.response_status_code',DB::raw("
                CASE
                    WHEN api_log.api_name = 'rc' AND api_log.request_type = 1 THEN 'Single rc'
                    WHEN api_log.api_name = 'rc' AND api_log.request_type = 2 THEN 'Bulk rc'
                    ELSE NULL
                END
                as Rcdetails"
                ),'api_log.created_at as Timestamp')
                ->whereIn('api_log.status', [0, 1])
                ->whereRaw("DATE(api_log.created_at) BETWEEN ? AND ?", [$sqlDateFrom, $sqlDateTo])
                ->whereIn('api_log.client_name', $organizations);
                // ->orderBy('api_log.created_at', 'desc')
                // ->get();

                $data_apiarchivelog = DB::table('api_log_archive')
                ->leftJoin('clients', 'api_log_archive.client_id', '=', 'clients.id')
                ->leftJoin('users', 'api_log_archive.user_id', '=', 'users.id') // Added join with the users table
                ->select('api_log_archive.client_name as Organization','users.name as Username','api_log_archive.vender as Vendor',DB::raw("UPPER(api_log_archive.api_name) as Module"),'api_log_archive.input as Request','api_log_archive.response_status_code',DB::raw("
                CASE
                    WHEN api_log_archive.api_name = 'rc' AND api_log_archive.request_type = 1 THEN 'Single rc'
                    WHEN api_log_archive.api_name = 'rc' AND api_log_archive.request_type = 2 THEN 'Bulk rc'
                    ELSE NULL
                END
                as Rcdetails"
                ),'api_log_archive.created_at as Timestamp')
                ->whereIn('api_log_archive.status', [0, 1])
                ->whereRaw("DATE(api_log_archive.created_at) BETWEEN ? AND ?", [$sqlDateFrom, $sqlDateTo])
                ->whereIn('api_log_archive.client_name', $organizations);
                // ->orderBy('api_log_archive.created_at', 'desc')
                // ->get();

                //$data = $data_apiarchivelog->merge($data_apilog);
                $data = $data_apilog->union($data_apiarchivelog)
                            ->orderBy('Timestamp', 'desc') // Ordering by 'Timestamp' column in descending order
                            ->get();


            } else {
                $data_apilog = DB::table('api_log')
                ->leftJoin('clients', 'api_log.client_id', '=', 'clients.id')
                ->leftJoin('users', 'api_log.user_id', '=', 'users.id') // Added join with the users table
                ->select('api_log.client_name as Organization','users.name as Username','api_log.vender as Vendor',DB::raw("UPPER(api_log.api_name) as Module"),'api_log.input as Request','api_log.response_status_code',DB::raw("
                CASE
                    WHEN api_log.api_name = 'rc' AND api_log.request_type = 1 THEN 'Single rc'
                    WHEN api_log.api_name = 'rc' AND api_log.request_type = 2 THEN 'Bulk rc'
                    ELSE NULL
                END
                as Rcdetails"
                ),'api_log.created_at as Timestamp')
                ->whereIn('api_log.status', [0, 1])
                ->whereRaw("DATE(api_log.created_at) BETWEEN ? AND ?", [$sqlDateFrom, $sqlDateTo])
                ->whereIn('api_log.client_name', $org);
                    // ->orderBy('api_log.created_at', 'desc')
                    // ->get();

                $data_apiarchivelog = DB::table('api_log_archive')
                ->leftJoin('clients', 'api_log_archive.client_id', '=', 'clients.id')
                ->leftJoin('users', 'api_log_archive.user_id', '=', 'users.id') // Added join with the users table
                ->select('api_log_archive.client_name as Organization','users.name as Username','api_log_archive.vender as Vendor',DB::raw("UPPER(api_log_archive.api_name) as Module"),'api_log_archive.input as Request','api_log_archive.response_status_code',DB::raw("
                CASE
                    WHEN api_log_archive.api_name = 'rc' AND api_log_archive.request_type = 1 THEN 'Single rc'
                    WHEN api_log_archive.api_name = 'rc' AND api_log_archive.request_type = 2 THEN 'Bulk rc'
                    ELSE NULL
                END
                as Rcdetails"
                ),'api_log_archive.created_at as Timestamp')
                ->whereIn('api_log_archive.status', [0, 1])
                ->whereRaw("DATE(api_log_archive.created_at) BETWEEN ? AND ?", [$sqlDateFrom, $sqlDateTo])
                ->whereIn('api_log_archive.client_name', $org);
                    // ->orderBy('api_log_archive.created_at', 'desc')
                    // ->get();



                    //$data = $data_apiarchivelog->merge($data_apilog);
                    $data = $data_apilog->union($data_apiarchivelog)
                            ->orderBy('Timestamp', 'desc') // Ordering by 'Timestamp' column in descending order
                            ->get();
                   
            }
        }
        else if($sessionData['userRole'] == 'admin'){

            $sessionData = session('data');
            $clientid = $sessionData['Client_id'];
            $userid = $sessionData['userID'];
            $sqlDateFrom = date('Y-m-d', strtotime($dateFrom));
            $sqlDateTo = date('Y-m-d', strtotime($dateTo));
            $org = $request->input('org');
            $all_value = $org[0];

            if($org[0] != 'All' )
            {   
                    
                    $data_apilog = DB::table('api_log')
                    ->leftJoin('clients', 'api_log.client_id', '=', 'clients.id')
                    ->leftJoin('users', 'api_log.user_id', '=', 'users.id') // Added join with the users table
                    ->select('api_log.client_name as Organization','users.name as Username','api_log.vender as Vendor',DB::raw("UPPER(api_log.api_name) as Module"),'api_log.input as Request','api_log.response_status_code',DB::raw("
                    CASE
                        WHEN api_log.api_name = 'rc' AND api_log.request_type = 1 THEN 'Single rc'
                        WHEN api_log.api_name = 'rc' AND api_log.request_type = 2 THEN 'Bulk rc'
                        ELSE NULL
                    END
                        as Rcdetails"
                    ),'api_log.created_at as Timestamp')
                            //->whereIn('api_log.status', [0, 1])
                            ->whereRaw("DATE(api_log.created_at) BETWEEN ? AND ?", [$sqlDateFrom, $sqlDateTo])
                            ->whereIn('users.name', $org);

                    $data_apiarchivelog = DB::table('api_log_archive')
                    ->leftJoin('clients', 'api_log_archive.client_id', '=', 'clients.id')
                    ->leftJoin('users', 'api_log_archive.user_id', '=', 'users.id') // Added join with the users table
                    ->select('api_log_archive.client_name as Organization','users.name as Username','api_log_archive.vender as Vendor',DB::raw("UPPER(api_log_archive.api_name) as Module"),'api_log_archive.input as Request','api_log_archive.response_status_code',DB::raw("
                    CASE
                        WHEN api_log_archive.api_name = 'rc' AND api_log_archive.request_type = 1 THEN 'Single rc'
                        WHEN api_log_archive.api_name = 'rc' AND api_log_archive.request_type = 2 THEN 'Bulk rc'
                        ELSE NULL
                    END
                        as Rcdetails"
                    ),'api_log_archive.created_at as Timestamp')
                            //->whereIn('api_log_archive.status', [0, 1])
                            ->whereRaw("DATE(api_log_archive.created_at) BETWEEN ? AND ?", [$sqlDateFrom, $sqlDateTo])
                            ->whereIn('users.name', $org);

                //  $data = $data_apiarchivelog->union($data_apilog)->get();
                 $data = $data_apilog->union($data_apiarchivelog)
                            ->orderBy('Timestamp', 'desc') // Ordering by 'Timestamp' 
                            ->get();

                 
            }else if($all_value ==  'All'){

                $username = Users::where('role', 'user')->where('client_id','=',$clientid)->pluck('name')->toArray();
                
                $data_apilog = DB::table('api_log')
                ->leftJoin('clients', 'api_log.client_id', '=', 'clients.id')
                ->leftJoin('users', 'api_log.user_id', '=', 'users.id') // Added join with the users table
                ->select('api_log.client_name as Organization','users.name as Username','api_log.vender as Vendor',DB::raw("UPPER(api_log.api_name) as Module"),'api_log.input as Request','api_log.response_status_code',DB::raw("
                CASE
                    WHEN api_log.api_name = 'rc' AND api_log.request_type = 1 THEN 'Single rc'
                    WHEN api_log.api_name = 'rc' AND api_log.request_type = 2 THEN 'Bulk rc'
                    ELSE NULL
                END
                as Rcdetails"
                ),'api_log.created_at as Timestamp')
                //->whereIn('api_log.status', [0, 1])
                ->whereRaw("DATE(api_log.created_at) BETWEEN ? AND ?", [$sqlDateFrom, $sqlDateTo])
                ->whereIn('users.name', $username);


                $data_apiarchivelog = DB::table('api_log_archive')
                ->leftJoin('clients', 'api_log_archive.client_id', '=', 'clients.id')
                ->leftJoin('users', 'api_log_archive.user_id', '=', 'users.id') // Added join with the users table
                ->select('api_log_archive.client_name as Organization','users.name as Username','api_log_archive.vender as Vendor',DB::raw("UPPER(api_log_archive.api_name) as Module"),'api_log_archive.input as Request','api_log_archive.response_status_code',DB::raw("
                CASE
                    WHEN api_log_archive.api_name = 'rc' AND api_log_archive.request_type = 1 THEN 'Single rc'
                    WHEN api_log_archive.api_name = 'rc' AND api_log_archive.request_type = 2 THEN 'Bulk rc'
                    ELSE NULL
                END
                as Rcdetails"
                ),'api_log_archive.created_at as Timestamp')
                    //->whereIn('api_log_archive.status', [0, 1])
                    ->whereRaw("DATE(api_log_archive.created_at) BETWEEN ? AND ?", [$sqlDateFrom, $sqlDateTo])
                    ->whereIn('users.name', $username);


                // $data = $data_apiarchivelog->union($data_apilog)->get();
                $data = $data_apilog->union($data_apiarchivelog)
                                ->orderBy('Timestamp', 'desc') // Ordering by 'Timestamp' descending order
                            ->get();

            }
            else{

                $username = Users::where('role', 'user')->where('client_id','=',$clientid)->pluck('name')->toArray();

                $data = DB::table('api_log')
                ->leftJoin('clients', 'api_log.client_id', '=', 'clients.id')
                ->leftJoin('users', 'api_log.user_id', '=', 'users.id') // Added join with the users table
                ->select('api_log.client_name as Organization','users.name as Username','api_log.vender as Vendor',DB::raw("UPPER(api_log.api_name) as Module"),'api_log.input as Request','api_log.response_status_code',DB::raw("
                CASE
                    WHEN api_log.api_name = 'rc' AND api_log.request_type = 1 THEN 'Single rc'
                    WHEN api_log.api_name = 'rc' AND api_log.request_type = 2 THEN 'Bulk rc'
                    ELSE NULL
                END
                as Rcdetails"
                ),'api_log.created_at as Timestamp')
                //->whereIn('api_log.status', [0,1])
                ->whereIn('users.name', $username)
                ->where('users.client_id','=',$clientid)
                ->orderBy('api_log.created_at', 'desc')
                ->get();
            }
        }

      

        $csvarray = [];

        $sessionData = $request->session()->get('data');
        $userRole = $sessionData['userRole'] ?? '';

        if ($userRole == 'admin') {
            $csvarray[] = ['name', 'Module', 'request', 'response_status_code','source','timestamp','Remark'];
            
            foreach ($data as $row) {
                $status = ($row->response_status_code == 200) ? "success" : "failed";
                $remark = ($row->response_status_code == 200) ? "success" : "Data not found";

                $csvarray[] = [
                    $row->Username,
                    $row->Module,
                    $row->Request,
                    $status,
                    $row->Rcdetails,
                    $row->Timestamp,
                    $remark,
                ];
            }
        } else {
            $csvarray[] = ['organization','name','vendor', 'Module', 'request', 'response_status_code','source','timestamp'];

            foreach ($data as $row) {
                $csvarray[] = [
                    $row->Organization,
                    $row->Username,
                    $row->Vendor,
                    $row->Module,
                    $row->Request,
                    $row->response_status_code,
                    $row->Rcdetails,
                    $row->Timestamp,
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
                // $file_url = $baseUrl . "/public/storage/uploads/rcbulk/" . $filename;   
                $file_url = $baseUrl . "/storage/app/public/uploads/rcbulk/" . $filename;        
                rename($tempFilePath, $filePath);
				chmod($filePath, 0755);
        

        return response()->json(['download' => '1', 'file_url' => $file_url, 'file_name' => $filename], 200);
    }
}


}
