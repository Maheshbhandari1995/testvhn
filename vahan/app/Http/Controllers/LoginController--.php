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
        if (!session()->has('data')) {
            return redirect('/login');
        }
       
        $sessionData = Session::get('data');
        $jsonData1 = $sessionData['userSessionID']; 
         $logoutUserSession1 = DB::select("SELECT * FROM `session_log` WHERE session_id='$jsonData1'  AND login_status='2' ORDER BY id DESC ");
        if($logoutUserSession1){
            return $this->checkLoginStatus($request);
        }
        if (session()->has('data')) {
            return view('index'); // Redirect to the home page if session exists
        }else{
            return view('login');
        }
    }

    
    public function signin(Request $request)
    {
        $currentDateTime = Carbon::now('Asia/Kolkata');
        $formattedDateTime = $currentDateTime->format('Y-m-d H:i:s');
         $ip = $request->ip();
        // return $request;
        $email = $request->input('username');
        $password = $request->input('passwd');

         $userExist = DB::select("SELECT * FROM `users` WHERE password='$password' AND email='$email' AND status='1' ");

        if($userExist)
        {
            

            foreach($userExist as $key=>$value)
            {
                $userID = $value->id;
                $userRole = $value->role;
                $Client_id = $value->client_id;
                $Name = $value->name;
                $Username = $value->username;
                $userEmail = $value->email;
                $userMobile = $value->mobile;
                $userGender = $value->gender;

                $getClientData = DB::select("SELECT * FROM `clients` WHERE id='$Client_id'");
                if($getClientData)
                {
                    foreach($getClientData as $clientValue)
                    {
                        $Client_id = $clientValue->id;
                        $clientName = $clientValue->name;
                        $clientEmail = $clientValue->email;
                        $clientWebsite = $clientValue->website;
                        $clientFile = $clientValue->file;
                    }
                }
                
                $checkUserSession = DB::select("SELECT * FROM `session_log` WHERE user_id='$userID' ORDER BY id DESC LIMIT 1");
                if($checkUserSession)
                {
                    foreach($checkUserSession as $value1)
                    {
                        $sessionLogID = $value1->id;
                        DB::table('session_log')
                        ->where('id', $sessionLogID)
                        ->update([
                            'login_status' => '2',
                        ]);
                    }
                }

                $series_id = Str::random(16);
                $remember_token = Str::random(20);
                $encrypted_remember_token = Hash::make($remember_token);
                DB::table('users')
                    ->where('email', $email)
                    ->update([
                        'series_id' => $series_id,
                        'remember_token' => $encrypted_remember_token,
                        // add more columns and their new values as needed
                    ]);
                    $userSessionID = $userEmail.'_'.$series_id;
                $data = [
                    'userID' => $userID,
                    'userRole' => $userRole,
                    'Client_id' => $Client_id,
                    'Name' => $Name,
                    'Username' => $Username,
                    'userEmail' => $userEmail,
                    'userMobile' => $userMobile,
                    'userGender' => $userGender,
                    'series_id' => $series_id,
                    'userSessionID' => $userSessionID,
                    'clientName' => $clientName,
                    'clientEmail' => $clientEmail,
                    'clientWebsite' => $clientWebsite,
                    'clientFile' => $clientFile,
                ];


                // $userExistNew = DB::select("SELECT * FROM `users` WHERE id='$userID'");
                // $request->session()->put('email', $email);
                $request->session()->put('data', $data);
                // Session::flash('success', 'Login successful!');
                //  $getsession = Session::get('email');
                
                //  $request->session()->put('mysession', $sessionID);
                $session_log = new Session_Log();
                // $session_log->id = '12';
                $session_log->user_id = $userID;
                $session_log->session_id = $userSessionID;
                // $session_log->ip_address = bcrypt('secret');
                $session_log->ip_address = $ip;
                // $session_log->login_status = '1';
                $session_log->save();
                //  return Session::save();
                return redirect()->intended('/')->with('success','Login successful!'); 
            }
            
        }else{
            return Redirect()->back()->with('error','Wrong Username or Password');
        }

        
    }

    public function signout(Request $request)
    {
        // return $jsonData = Session::get('userdata');
        // $this->checkLoginStatus($request);
        Session::flush();
        return redirect('/login');
    }

    public function checkLoginStatus(Request $request)
    {
            if (Session::has('data')) {
                $sessionData = Session::get('data');
                $userid = $sessionData['userID'];
                $email = $sessionData['userEmail'];
                // $series_id = $val->series_id;
                $session_id = $sessionData['userSessionID']; 
                  $logoutUserSession = DB::select("SELECT * FROM `session_log` WHERE session_id='$session_id' and user_id='$userid' AND login_status='2' ORDER BY id DESC ");
                if($logoutUserSession){
                    Session::flush();

                   
                }
                return redirect('/login');
            }

            
            
    }


    public function challan(Request $request)
    {
        if (!session()->has('data')) {
            return redirect('/login');
        }
        $sessionData = Session::get('data');
        $jsonData1 = $sessionData['userSessionID']; 
        $logoutUserSession1 = DB::select("SELECT * FROM `session_log` WHERE session_id='$jsonData1'  AND login_status='2' ORDER BY id DESC ");
        if($logoutUserSession1){
            return $this->checkLoginStatus($request);
        }
        return  view('challan_details');
        // return redirect('')->back();
    }

    public function rc(Request $request)
    {
        if (!session()->has('data')) {
            return redirect('/login');
        }
        $sessionData = Session::get('data');
        $jsonData1 = $sessionData['userSessionID']; 
        $logoutUserSession1 = DB::select("SELECT * FROM `session_log` WHERE session_id='$jsonData1'  AND login_status='2' ORDER BY id DESC ");
        if($logoutUserSession1){
            return $this->checkLoginStatus($request);
        }
        return  view('rc_details');
        // return redirect('')->back();
    }

    public function dl(Request $request)
    {
        if (!session()->has('data')) {
            return redirect('/login');
        }
       
        $sessionData = Session::get('data');
        $jsonData1 = $sessionData['userSessionID']; 
        $logoutUserSession1 = DB::select("SELECT * FROM `session_log` WHERE session_id='$jsonData1'  AND login_status='2' ORDER BY id DESC ");
        if($logoutUserSession1){
            return $this->checkLoginStatus($request);
        }
        return  view('dl_details');
        // return redirect('')->back();
    }
    
}
