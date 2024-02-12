<?php

namespace App\Http\Controllers;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Http\Request;
use App\Models\Company;
use Illuminate\Validation\Rule;
use Illuminate\Support\Collection;
use Illuminate\Database\Query\Builder;
use App\Models\Rcdetails;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\Buttons\Button;
use Yajra\DataTables\Buttons\DatatableButton;
use Illuminate\Support\Facades\Session;

class DashboardController extends Controller
{
    
    public function getDashboardList(Request $request)
    {
        
        $sessionData = session('data');
       // echo "<pre>";print_r($sessionData);die; 
        if ($request->ajax()) {

            // $count = Rcdetails::whereNotNull('api_name')
            //     ->where('status', 1)
            //     ->count();

            if(isset($sessionData) && $sessionData['userRole'] == 'user')
            {

            }
            else{
                // $data = DB::table('api_log')
                // ->leftJoin('clients', 'api_log.client_id', '=', 'clients.id')
                // ->select('api_log.client_id as clientid', 'clients.max_count as max_count', 'clients.name as client_name')
                // ->whereNotNull('api_log.api_name')
                // ->whereIn('api_log.status', [0, 1])
                // ->groupBy('clientid')
                // ->orderBy('clientid', 'asc')
                // ->get();

                $data = DB::table('clients')
                ->select('id as clientid','name as client_name', 'max_count', 'status')
                // ->whereIn('status', [0, 1])
                ->whereIn('del_status', [0, 1])
                ->orderBy('created_at', 'desc')
                ->get();

                $count = Rcdetails::selectRaw('client_name, COUNT(*) as utilized_count')
                ->groupBy('client_name')
                ->get()
                ->toArray();

                $successCounts = Rcdetails::where('response_status_code', 200)
                ->groupBy('client_name')
                ->selectRaw('client_name, COUNT(*) as count')
                ->get()
                ->toArray();
            

                $failCounts = Rcdetails::where('response_status_code', '!=', 200)
                ->groupBy('client_name')
                ->selectRaw('client_name, COUNT(*) as count')
                ->get()
                ->toArray();
                    // echo "<pre>"; print_r($data);die;

                return DataTables::of($data)
                ->addColumn('successCounts', function() use ($successCounts) {
                    return $successCounts;
                })
                ->addColumn('failcounts', function() use ($failCounts) {
                    return $failCounts;
                })
                ->addColumn('utilized_count', function() use ($count) {
                    return $count;
                })
                ->make(true);
            }

        }

        return abort(404);

    }

    public function getUserDashboardList(Request $request)
    {
        if ($request->ajax()) {
            $sessionData = session('data');
            if (isset($sessionData) && $sessionData['userRole'] == 'user') {
                $user = $sessionData['userID'];
                //dd($user);
                
                $data = DB::table('api_log')
                    ->join('users', function ($join) use ($user) {
                        $join->on('users.id', '=', 'api_log.user_id')
                            ->on('users.client_id','=','api_log.client_id')
                             ->where('api_log.user_id', '=', $user);
                    })
                    ->select('api_log.api_name', 'api_log.api_url', 'api_log.response_status_code', 'api_log.created_at')
                    ->get();

                    return DataTables::of($data)
                    ->make(true);
        
            }
            else if (isset($sessionData) && $sessionData['userRole'] == 'admin') {
                $Client_id = $sessionData['Client_id'];  

                $data = DB::table('api_log')
                ->join('users', function ($join) use ($Client_id) {
                    $join->on('users.id', '=', 'api_log.user_id')
                        ->on('users.client_id', '=', 'api_log.client_id')
                        ->where('users.client_id', '=', $Client_id)
                        ->where('users.role', '!=', 'super_admin');
                })
                ->select('users.username', 'api_log.status',
                    DB::raw('COUNT(*) as total_count'),
                    DB::raw('SUM(CASE WHEN api_log.response_status_code = 200 THEN 1 ELSE 0 END) as success_count'),
                    DB::raw('SUM(CASE WHEN api_log.response_status_code != 200 THEN 1 ELSE 0 END) as fail_count')
                )
                ->where('api_log.client_id', '=', $Client_id)
                ->groupBy('users.username', 'api_log.status')
                ->get();


                    return DataTables::of($data)
                
                    ->make(true);
        
            }
        }
        
    }

}
