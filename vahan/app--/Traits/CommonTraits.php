<?php
namespace App\Traits;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;

trait CommonTraits	
{
    public function updateUtilizedCredit($clientId)
    {
        //echo $clientId;
        $query = DB::table('clients')
            ->where('id', $clientId)
            ->where('status', 1)
            ->where('del_status', 1)
            ->update([
                'used_credit' => DB::raw('used_credit + 1'),
                'max_count' => DB::raw('max_count - 1'),
            ]);
            // $bindings = $query->getBindings();
            // $updateQuery = vsprintf(str_replace('?', '%s', $query->toSql()), $bindings);
            
            // echo $updateQuery;
    }

    public function checkCredit($clientID = '')
    {
        if(empty($clientID))
        {
            $sessionData = session('data');
            $clientID = (isset($sessionData['Client_id'])) ? $sessionData['Client_id'] : '' ;
        }
        
        // echo "SELECT max_count as max_count, envtype FROM `clients` WHERE id = '$clientID'";
        // echo "SELECT count(*) as cnt FROM `api_log` WHERE client_id='$clientID'";die;
        $maxCnt = DB::select("SELECT max_count as max_count, envtype FROM `clients` WHERE id = '$clientID'");
        if($maxCnt[0]->max_count > 0)
        {
            return true;
        }
        else{
            return false;
        }
        // if($maxCnt[0]->envtype == 'preproduction')
        // {
            
        //     // $apiCnt = DB::select("SELECT count(*) as cnt FROM `api_log` WHERE client_id='$clientID'");
        //     // if($apiCnt[0]->cnt < $maxCnt[0]->max_count)
        //     // {
        //     //     return true;
        //     // }
        //     // else{
        //     //     return false;
        //     // }
        // }
        // else{
        //     return true;
        // }
    }
}