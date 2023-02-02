<?php

namespace App\Http\Middleware;

use Closure;

class AuthorizationMiddleware
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
         //Obtener usuario identificado
        try{
            $token = $request->header('Authorization');
            $jwtAuth = new \App\Helpers\JwtAuth();
            $user = $jwtAuth->checkToken($token, true);
            
        }catch(\Exception $e){
            $data = array(
                'code'    => 400,
                'status'  => 'error',
                'message' => $e->getMessage()         
            );
            return response()->json($data,$data['code']);
        }
        
        if($user){  
            if($user->role == 'DEMO'){
                $data = array(
                    'code'    => 401,
                    'status'  => 'error',
                    'message' => 'No cuenta con autorización para realizar modificaciones o registros.'         
                );
                 
                return response()->json($data,$data['code']);
            }
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
