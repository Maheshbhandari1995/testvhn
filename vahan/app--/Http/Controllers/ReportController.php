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
        
        if ($request->ajax()) {

            // $count = Rcdetails::whereNotNull('api_name')
            //     ->where('status', 1)
            //     ->count();

            $data = DB::table('session_log')
            ->leftjoin('users', 'users.id', '=', 'session_log.user_id')
            ->leftjoin('clients', 'users.client_id', '=', 'clients.id')
            ->where('session_log.status', '=', 1)
            ->select('clients.name as client_name', 'session_log.*', 'users.name as user_name')
            ->get();
        
            return DataTables::of($data)
                // ->addColumn('max_count', function($row) use ($maxCount) {
                //     return $maxCount;
                // })
                // ->addColumn('count', function() use ($count) {
                //     return $count;
                // })
                ->make(true);
        }

        return abort(404);

    }
}
