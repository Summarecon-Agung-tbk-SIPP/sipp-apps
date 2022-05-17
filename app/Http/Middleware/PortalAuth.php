<?php

namespace App\Http\Middleware;

use Closure;

class PortalAuth
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        if(
            ($request->session()->get('login') != 1) 
            || 
            trim($request->session()->get('user_id') == "")
        ){
            return redirect('signin');
        }

        return $next($request);
    }
}