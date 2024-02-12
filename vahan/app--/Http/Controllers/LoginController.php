<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use App\Models\Company;
use Illuminate\Support\Facades\Hash;
use App\Models\Session_Log;
use Carbon\Carbon;
use App\Models\Rcdetails;

class LoginController extends Controller
{
    public function indexfun(Request $request)
    {   
        $sessionData = Session::get('data');
        if(session()->has('data'))
        {	
            if (isset($sessionData) && ($sessionData['userRole'] == 'user' || $sessionData['userRole'] == 'admin')) {

                if($sessionData['userRole'] == 'admin')
                {
                    $Client_id = $sessionData['Client_id'];
                    $maxCount = DB::table('clients')
                    ->select('clients.max_count')
                    ->join('users', 'clients.id', '=', 'users.client_id')
                    ->where('users.client_id', '=', $Client_id)
                    ->value('clients.max_count');

                    $successCount = Rcdetails::where('response_status_code', 200)
                    ->join('users', 'users.id', '=', 'api_log.user_id')
                    ->where('users.client_id', $Client_id)
                    ->count();

                    $utliziedCount = Rcdetails::where('users.client_id', $Client_id)
                    ->join('users', 'users.id', '=', 'api_log.user_id')
                    ->count();

                    $failcount =Rcdetails::where('response_status_code', '!=', 200)
                    ->join('users', 'users.id', '=', 'api_log.user_id')
                    ->where('users.client_id', $Client_id)
                    ->count();
                }
                else{
                    $user = $sessionData['userID'];

                    $maxCount = DB::table('clients')
                    ->select('clients.max_count')
                    ->join('users', 'clients.id', '=', 'users.client_id')
                    ->where('users.id', '=', $user)
                    ->value('clients.max_count');

                    $successCount = Rcdetails::where('response_status_code', 200)
                    ->join('users', 'users.id', '=', 'api_log.user_id')
                    ->where('users.id', $user)
                    ->count();

                    $utliziedCount = Rcdetails::where('users.id', $user)
                    ->join('users', 'users.id', '=', 'api_log.user_id')
                    ->count();

                    $failcount =Rcdetails::where('response_status_code', '!=', 200)
                    ->join('users', 'users.id', '=', 'api_log.user_id')
                    ->where('users.id', $user)
                    ->count();
                }  
                //return $utliziedCount;

                return view('dashboard', compact('maxCount','successCount','utliziedCount','failcount'));
            // return view('dashboard');
            }else{
                $companyCount = DB::select("SELECT COUNT(*) AS count FROM clients WHERE del_status = 1")[0]->count;
                
                $successCounts = Rcdetails::where('response_status_code', 200)
                ->selectRaw('COUNT(*) as count')
                ->get();

                $failCounts = Rcdetails::where('response_status_code', '!=', 200)
                    ->selectRaw('COUNT(*) as count')
                    ->get();
                
                // $sum = Company::where('status', 1)
                // ->sum('max_count');
                //echo "<pre>"; print_r($successCounts[0]['count']);die;
                $sum = $successCounts[0]['count'] + $failCounts[0]['count'];
                
                return view('dashboard', compact('companyCount','successCounts','failCounts','sum'));
           }
            	//return view('dashboard');
        }
        else{
            return view('login');
        }
    }
    
    public function signin(Request $request)
    {
         // $password = Hash::make($password);
        //in core php $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        //check session active or not
        $this->isSessionActive();  
        $sessionData    = session('data');
        //echo "sessionData : <pre>"; print_r($sessionData);//die;
        $currentDateTime = Carbon::now('Asia/Kolkata');
        $formattedDateTime = $currentDateTime->format('Y-m-d H:i:s');
        $ip = $request->ip();
        
        $username = $request->input('username');
        $password = $request->input('passwd');

        //  $userExist = DB::select("SELECT * FROM `users` WHERE password='$password' AND username='$username' AND status='1'");

         $userExist = DB::table('users')
         ->leftJoin('clients', 'users.client_id', '=', 'clients.id')
         ->select('users.*', 'clients.id as Client_id', 'clients.name as clientName', 'clients.email as clientEmail', 'clients.website as clientWebsite', 'clients.file as clientFile')
         ->whereIn('users.status', [0,1])
        // ->whereIn('clients.status', [0,1])
         ->where('users.username', $username)
        //  ->where('users.password', $password)
         ->latest()
         ->first();

        if($userExist)
        {
            if($userExist->role == 'user' && isset($userExist->Client_id) && empty($userExist->Client_id))
            {
                return Redirect()->back()->with('error','Not Authorized');
            }
            if(!password_verify($password, $userExist->password))
            {
                return Redirect()->back()->with('error','invalid username or password!');
            }

            $apiArr = [];
            $API_LIST = DB::select("SELECT id, apiname, api_alias, view_filename FROM `api_list` WHERE client_id='$userExist->Client_id' AND status='1' AND del_status = '1'");
            if(!empty($API_LIST))
            {
                foreach($API_LIST as $k => $api)
                {
                    $apiArr[$api->id]['id'] = $api->id;
                    $apiArr[$api->id]['name'] = $api->apiname;
                    $apiArr[$api->id]['api_alias'] = $api->api_alias;
                    $apiArr[$api->id]['view_filename'] = $api->view_filename;
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
                'api_list' => $apiArr,
            ];
            $request->session()->put('data', $data);
            $checkUserSession = DB::select("SELECT * FROM `session_log` WHERE user_id='$userID' AND login_status = 1 ORDER BY id DESC LIMIT 1");
            if($checkUserSession)
            {
                DB::table('session_log')
                    ->where('user_id', $userID)
                    ->update([
                        'login_status' => '2'
                    ]);
            }
            $this->logInSession();

            DB::table('users')
                ->where('id', $userID)
                ->update([
                    'series_id' => Str::random(16),
                    'remember_token' => Hash::make(Str::random(20)),
                ]);
            
                return redirect('/dashboard');
            
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

    public function isSessionActive()
    {
        $sessionData    = session('data');
        $userSessionID  = $sessionData['userSessionID'];
        $userID         = $sessionData['userID'];
       // echo "sdfasdf<pre>"; print_r($sessionData);die;
        if(isset($sessionData['userSessionID'])) {
            $SessionTableData = DB::select("SELECT user_id, session_id, login_status FROM `session_log` WHERE session_id='$userSessionID' and user_id='$userID' AND login_status='1' ORDER BY id DESC");
            if($SessionTableData)
            {
                return redirect('/dashboard');
            }
        }
        return true;
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
        Session::flush();
        return DB::table('session_log')
            ->where('login_status', 1)
            ->where('user_id', $sessionData['userID'])
            ->update([
                'login_status' => 2
            ]);
       
        
    }

    public function availableBalance()
    {
        $availbleBalance = 0;
        if(session()->has('data'))
        {
            $sessionData = Session::get('data');
            if (isset($sessionData) && ($sessionData['userRole'] == 'user' || $sessionData['userRole'] == 'admin')) {
                $Client_id = $sessionData['Client_id'];
                $availbleBalance = DB::table('clients')
                ->select('max_count')
                ->where('id', '=', $Client_id)
                ->value('max_count');
            }
        }
        
        return $availbleBalance;
    }
}
