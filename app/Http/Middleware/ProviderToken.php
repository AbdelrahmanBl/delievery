<?php

namespace App\Http\Middleware;

use Closure;
use DB;

class ProviderToken
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
        $token = $request->header('remember-token');
        $User  = DB::table('providers')->where('remember_token',$token)->first();
        if(!$User)
            return response()->json([
            'error_flag'    => 1,
            'message' => 'remember_token Not Found',
            'result'  => NULL,
        ]);
        return $next($request);
    }
}
