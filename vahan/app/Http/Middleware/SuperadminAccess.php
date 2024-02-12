<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Models\Session_Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken as BaseVerifier;


class SuperadminAccess extends BaseVerifier
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle($request, Closure $next)
    {
       // echo "<pre>"; print_r(session('data'));//die;
        if ($request->session()->exists('data') && session('data.userRole') == 'super_admin') {
            $sessionId = session('data.userSessionID');
            $data = Session_Log::where('session_id', $sessionId)->where('login_status', 1)->first();
            
            if($data)
            {
                return $next($request);
            }
            else{
                Session::flush();
                return redirect('/login');
            }
        }
        else{
            return redirect('/login');
        }
        
        
    }
}
