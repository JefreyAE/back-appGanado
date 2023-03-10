<?php

namespace App\Http\Middleware;

use Closure;

class ApiAuthMiddleware
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
        //Comprobar usuario identificado
        try{
            $token = $request->header('Authorization');
            $jwtAuth = new \App\Helpers\JwtAuth();
            $checkToken = $jwtAuth->checkToken($token);
        }catch(\Exception $e){
            $data = array(
                'code'    => 400,
                'status'  => 'error',
                'message' => $e->getMessage()         
            );
            return response()->json($data,$data['code']);
        }
        
        if($checkToken){  
            return $next($request);
        }else{
            $data = array(
               'code'    => 400,
               'status'  => 'error',
               'message' => 'Usuario no identificado.'         
            );
            return response()->json($data,$data['code']);
        } 
    }
}
