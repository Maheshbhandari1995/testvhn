<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;
use App\Models\Session_Log;
use Carbon\Carbon;
class LoginController extends Controller
{

    public function indexfun(Request $request)
    {
       // $this->isSessionActive();
        return redirect('/login');
    }

    
    public function signin(Request $request)
    {
        $currentDateTime = Carbon::now('Asia/Kolkata');
        $formattedDateTime = $currentDateTime->format('Y-m-d H:i:s');
        $ip = $request->ip();

        //$hashedPassword = Hash::make($password); for hashing a password
        $username = $request->input('username');
        $password = $request->input('passwd');
       //die;
        // $password = Hash::make($password);

        //  $userExist = DB::select("SELECT * FROM `users` WHERE password='$password' AND username='$username' AND status='1'");

         $userExist = DB::table('users')
         ->leftJoin('clients', 'users.client_id', '=', 'clients.id')
         ->select('users.*', 'clients.id as Client_id', 'clients.name as clientName', 'clients.email as clientEmail', 'clients.website as clientWebsite', 'clients.file as clientFile')
         ->whereIn('users.status', [0,1])
        // ->whereIn('clients.status', [0,1])
         ->where('users.password', $password)
         ->where('users.username', $username)
         ->latest()
         ->first();

        if($userExist)
        {
            if($userExist->role == 'user' && isset($userExist->Client_id) && empty($userExist->Client_id))
            {
                return Redirect()->back()->with('error','Not Authorized');
            }

            $apiArr = [];
            $API_LIST = DB::select("SELECT id, apiname FROM `api_list` WHERE client_id='$userExist->Client_id' AND status='1' AND del_status = '1'");
            if(!empty($API_LIST))
            {
                
                foreach($API_LIST as $k => $api)
                {
                    //echo $api->apiname."<pre>"; print_r($api);die;
                    $apiArr[$api->id] = $api->apiname;
                }
            }


            $userID         = $userExist->id;
            $userSessionID  = Session::getId();
         
            $data = [
                'userID' => $userID,
                'userRole' => $userExist->role,
                'Client_id' => $userExist->Client_id,
                'Name' => $userExist->name,
                'Username' => $userExist->username,
                'userEmail' => $userExist->email,
                'userMobile' => $userExist->mobile,
                'userGender' => $userExist->gender,
                'userSessionID' => $userSessionID,
                'clientName' => $userExist->clientName,
                'clientEmail' => $userExist->clientEmail,
                'clientWebsite' => $userExist->clientWebsite,
                'clientFile' => $userExist->clientFile,
                'ip_address' => $ip,
                'api_list' => implode(',', $apiArr),
            ];
            $request->session()->put('data', $data);
            // echo "session<pre>"; print_r(session('data'));
            //echo "userExist<pre>"; print_r($data);die;
            
            $checkUserSession = DB::select("SELECT * FROM `session_log` WHERE user_id='$userID' AND login_status = 1 ORDER BY id DESC LIMIT 1");
            if($checkUserSession)
            {
                DB::table('session_log')
                    ->where('user_id', $userID)
                    ->update([
                        'login_status' => '2'
                    ]);
            }

            DB::table('users')
                ->where('id', $userID)
                ->update([
                    'series_id' => Str::random(16),
                    'remember_token' => Hash::make(Str::random(20)),
                ]);
            
            return redirect('/login');
            
        }else{
            return Redirect()->back()->with('error','Not Authorized!');
        }
    }

    public function signout(Request $request)
    {
        $this->logOutSession();
        Session::flush();
        return redirect('/login');
    }

    // public function checkLoginStatus(Request $request)
    // {
    //     $sessionData = Session::get('data');
    //     if($sessionData->userSessionID) {



    //         // $sessionData = Session::get('data');
    //         // $userid = $sessionData['userID'];
    //         // $email = $sessionData['userEmail'];
    //         // // $series_id = $val->series_id;
    //         // $session_id = $sessionData['userSessionID']; 
    //         //     $logoutUserSession = DB::select("SELECT * FROM `session_log` WHERE session_id='$session_id' and user_id='$userid' AND login_status='2' ORDER BY id DESC ");
    //         // if($logoutUserSession){
    //         //     Session::flush();
    //         // }
    //         // return redirect('/login');
    //     }
    // }


    public function isSessionActive()
    {
        $sessionData = session('data');
        $userSessionID = $sessionData['userSessionID'];
        $userID        = $sessionData['userID'];
       // echo "sdfasdf<pre>"; print_r($sessionData['userSessionID']);die;
        if($sessionData['userSessionID']) {
            // echo "SELECT user_id, session_id, login_status FROM `session_log` WHERE session_id='$userSessionID' and user_id='$userID' AND login_status='1' ORDER BY id DESC";
           $SessionTableData = DB::select("SELECT user_id, session_id, login_status FROM `session_log` WHERE session_id='$userSessionID' and user_id='$userID' AND login_status='1' ORDER BY id DESC");
            if($SessionTableData)
            {
                //return redirect()->route('users.index');
                return redirect('/login');
            }
            else{
                $this->logOutSession();
                Session::flush();
                return redirect('/login');
            }
        }
        else
        { 
            $this->logOutSession();
            Session::flush();
            //echo "123";
            dd('Session flushed');
            return redirect('/login');
            
        }
    }

   

    public function logInSession()
    {
        $sessionData = Session::get('data');
        $session_log = new Session_Log();
        $session_log->user_id = $sessionData['userID'];
        $session_log->session_id = $sessionData['userSessionID'];
        $session_log->login_status = 1;
        $session_log->STATUS = 1;
        $session_log->ip_address = $sessionData['ip_address'];
        $session_log->save();
    }
    public function logOutSession()
    {
        $sessionData = Session::get('data');
        return DB::table('session_log')
            ->where('login_status', 1)
            ->where('user_id', $sessionData['userID'])
            ->update([
                'login_status' => 2
            ]);
        
    }

}
