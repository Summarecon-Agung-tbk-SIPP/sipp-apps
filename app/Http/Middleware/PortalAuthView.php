<?php

namespace App\Http\Middleware;

use App\Http\Controllers\Sys\SysController;

use Closure;

class PortalAuthView
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
        $sysController = new SysController();

        $user_id    = $request->session()->get('user_id');
        $controller = $request->segment(1).'/'.$request->segment(2);

        $valid = $sysController->portal_auth_view($user_id, $controller);
        if($valid){
            $kd_unit    = $request->session()->get('kd_unit');
            $kd_lokasi  = $request->session()->get('kd_lokasi');
            
            if($kd_unit == "" || $kd_lokasi == ""){
                $set_first_unit = $sysController->set_first_unit($request);

                $kd_unit    = $request->session()->get('kd_unit');
                $kd_lokasi  = $request->session()->get('kd_lokasi');

                if($kd_unit == "" || $kd_lokasi == ""){
                    abort(401);
                }else{
                    return $next($request);
                }
            }else{
                return $next($request);
            }
        }else{
            abort(401);
        }
    }
}