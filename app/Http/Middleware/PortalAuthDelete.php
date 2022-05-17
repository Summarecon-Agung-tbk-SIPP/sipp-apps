<?php

namespace App\Http\Middleware;

use App\Http\Controllers\Sys\SysController;

use Closure;

class PortalAuthDelete
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

        $valid = $sysController->portal_auth_delete($user_id, $controller);
        if($valid){
            return $next($request);
        }else{
            http_response_code(401);
            exit(json_encode(['message' => 'Anda tidak memiliki akses untuk menghapus']));
        }
    }
}
