<?php
namespace App\Traits;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;

trait CommonTraits	
{
    public function updateUtilizedCredit($clientId)
    {
        $query = DB::table('clients')
            ->where('id', $clientId)
            ->where('status', 1)
            ->where('del_status', 1)
            ->update([
                'used_credit' => DB::raw('used_credit + 1'),
                'max_count' => DB::raw('max_count - 1'),
            ]);
    }

    public function checkCredit($clientID = '')
    {
        if(empty($clientID))
        {
            $sessionData = session('data');
            $clientID = (isset($sessionData['Client_id'])) ? $sessionData['Client_id'] : '' ;
        }
        $maxCnt = DB::select("SELECT max_count as max_count, envtype FROM `clients` WHERE id = '$clientID'");
        if($maxCnt[0]->max_count > 0)
        {
            return true;
        }
        else{
            return false;
        }
    }

    public function getCredit($clientID = '')
    {
        if(empty($clientID))
        {
            $sessionData = session('data');
            $clientID = (isset($sessionData['Client_id'])) ? $sessionData['Client_id'] : '' ;
        }
        $maxCnt = DB::select("SELECT max_count as max_count, envtype FROM `clients` WHERE id = '$clientID'");
        
        return $maxCnt[0]->max_count;
    }

    public function validateVehicleNumber($vehicleNumber)
    {
        // Regular expression pattern for vehicle number validation
        // $regex = '/^[A-Z]{2}[0-9]{1,2}[A-Z]{1,2}[0-9]{1,4}$/';
        $regex = '/^[A-Z]{2}[0-9]{1,2}[A-Z0-9]{1,3}[0-9]{1,4}$/';
    
        // Test the vehicle number against the regex pattern
        $isValid = preg_match($regex, $vehicleNumber);
    
        return $isValid === 1;
    }

    public function validateChassisNumber($Number)
    {
        // Regular expression pattern for vehicle number validation
        $regex = '/^[A-HJ-NPR-Z0-9]{17}$/i';
    
        // Test the vehicle number against the regex pattern
        $isValid = preg_match($regex, $Number);
    
        return $isValid === 1;
    }

    public function filterVehicleNumber($vehicleNumber)
    {
        $vehicleNumber  = trim($vehicleNumber);
        $vehicleStart   = substr($vehicleNumber, 0, 2);
        if (strtoupper($vehicleStart) == 'DL' && (strlen($vehicleNumber) == 11 || strlen($vehicleNumber) == 10) && $vehicleNumber[2] === '0') {
            $vehicleNumber = substr_replace($vehicleNumber, '', 2, 1);
        }
        return $vehicleNumber;
    }

}