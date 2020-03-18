<?php

namespace App\Http\Middleware;

use Closure;

class AuthKey
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
        $token1 = $request->header('header-key');
        $token2 = $request->header('oath-key');
        
        if($token1 != env('header_key') || $token2 != env('oath_key')){
            return response()->json([
            'error_flag' => 404,
            'message' => 'Not Authenicated !',
            'result'=> NULL
          ]);
        }
        return $next($request);
    }
}
