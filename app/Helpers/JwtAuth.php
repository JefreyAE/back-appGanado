<?php

namespace App\Helpers;

use Firebase\JWT\JWT;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Config;
use App\User;

class JwtAuth {

    public $key;

    public function __construct() {
        $this->key = Config::get('app.token_key');
    }

    public function signup($email, $password, $getToken = null) {
        //Buscar si existe el usuario con sus credenciales

        try {
            $user = User::where([
                        'email' => $email,
                        'password' => $password
                    ])->first();
        } catch (\Exception $e) {
            return $e->getMessage();
        }

        //Comprobar si son correctas
        $signup = false;
        if (is_object($user)) {
            $signup = true;
        }
        //Generar el token con los datos
        if ($signup) {
            $token = array(
                'id' => $user->id,
                'email' => $user->email,
                'name' => $user->name,
                'surname' => $user->surname,
                'role' => $user->role,
                'state' => $user->state,
                'type' => $user->type,
                'iat' => time(),
                'exp' => time() + (7 * 24 * 60 * 60)
            );

            $jwt = JWT::encode($token, $this->key, 'HS256');
            $decoded = JWT::decode($jwt, $this->key, ['HS256']);
            //Devolver lo datos decodificados o el token en funcion de un parametro.

            if($user->state == 'blocked'){
                $data = array(
                    'status' => 'error',
                    'message' => 'Tu cuenta se encuentra bloqueada.'
                );
            }else{
                if (is_null($getToken)) {
                    $data = $jwt;
                } else {
                    $data = $decoded;
                }

            }
        } else {
            $data = array(
                'status' => 'error',
                'message' => 'El correo o la contraseña no son correctos.'
            );
        }
        return $data;
    }

    public function checkToken($jwt, $getIdentity = false) {
        $auth = false;

        try {
            $jwt = str_replace('"', "", $jwt);
            $decoded = JWT::decode($jwt, $this->key, ['HS256']);
        } catch (\DomainException $e) {
            $auth = false;
        } catch (\UnexpectedValueException $ex) {
            $auth = false;
        }

        if (!empty($decoded) && is_object($decoded) && isset($decoded->id)) {
            $auth = true;
        } else {
            $auth = false;
        }

        if ($getIdentity) {
            return $decoded;
        }

        return $auth;
    }

}
