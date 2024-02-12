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
       // echo "<pre>";print_r($sessionData);die; 
        if ($request->ajax()) {

            $count = Rcdetails::whereNotNull('api_name')
                ->where('status', 1)
                ->count();

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
            else{
                $data = DB::table('api_log')
                ->leftJoin('clients', 'api_log.client_id', '=', 'clients.id')
                ->select('api_log.*', 'clients.max_count as max_count')
                ->whereNotNull('api_log.api_name') // Check if api_name is not null
                ->whereIn('api_log.status', [0,1])
                ->latest()
                ->get();
            }

            // $data = DB::table('api_log')
            // ->leftJoin('clients', 'api_log.client_id', '=', 'clients.id')
            // ->select('api_log.*', 'clients.max_count as max_count')
            // ->whereNotNull('api_log.api_name') // Check if api_name is not null
            // ->whereIn('api_log.status', [0,1])
            // ->latest()
            // ->get();


            return DataTables::of($data)
                // ->addColumn('max_count', function($row) use ($maxCount) {
                //     return $maxCount;
                // })
                ->addColumn('count', function() use ($count) {
                    return $count;
                })
                ->make(true);
        }

        return abort(404);

    }
}
