<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
Use App\User;
use App\Helpers\Enums;
use App\Mail\ConfirmationEmail;
use App\Helpers\Constants;
use Mail; 

class UserController extends Controller {

    public function validateAccount(Request $request) {

        $validation = $request->route('validation');
        try {
            $validate = \Validator::make(['validation' => $validation], [
                    'validation' => 'required|regex:/^[a-zA-Z0-9\sÀ-ÿ\u00f1\u00d1]+$/u'
            ]);
        } catch (\Exception $e) {
            $data = array(
                'code' => 404,
                'status' => 'error',
                'message' => $e->getMessage()
            );
            return response()->json($data, $data['code']);
        }
        try {
            /* Datos generales */
            $user = User::where('validation_token', $validation)->first();

            $enums = new Enums(); 
            $constants = new Constants();
            
            if($user){
                $user->state = $enums::UserState()['Active'];
                $user->save();

                return view('users.userValidated', ['url' => $constants->frontUrl()]);

                $data = array(
                    'code' => 200,
                    'status' => 'success',
                    'message' => 'Su cuenta a sido activada correctamente'
                );  
            }else{
                $data = array(
                    'code' => 500,
                    'status' => 'error',
                    'message' => 'El link de validación es inválido.'
                );
            }  
        } catch (\Exception $e) {
            $data = array(
                'code' => 500,
                'status' => 'error',
                'message' => $e->getMessage()
            );
            return response()->json($data, $data['code']);
        }

        return response()->json($data, $data['code']);
    }

    public function register(Request $request) {

        //Recoger datos del usuario por post
        try{
            $json = $request->input('json', null);
            if (is_array($json)) {
                $params = $json;
                $params_array = $json;
            } else {
                $params = json_decode($json);
                $params_array = json_decode($json, true);
            }

            if (!empty($params_array) && !empty($params)) {
                //Validar los datos 
                $validate = \Validator::make($params_array, [
                            'name' => 'required|regex:/^[a-zA-Z0-9\sÀ-ÿ\u00f1\u00d1]+$/u',
                            'surname' => 'required|regex:/^[a-zA-Z0-9\sÀ-ÿ\u00f1\u00d1]+$/u',
                            'email' => 'required|email|unique:users',
                            'password' => 'required',
                ]);

                //Limpiar blancos
                $params_array = array_map('trim', $params_array);

                $enums = new Enums();
                $constants = new Constants();
                
                if ($validate->fails()) {
                    $data = array(
                        'status' => 'error',
                        'code' => '400',
                        'message' => 'Error al crear el usuario.',
                        'errors' => $validate->errors()
                    );
                } else {
                    //Cifrar la contraseña
                    $pwd = hash('sha256', $params_array['password']);
                    $validation_token = hash('sha256', $params_array['email']);

                    //Crear el usuario
                    $user = new User();
                    $user->name = $params_array['name'];
                    $user->surname = $params_array['surname'];
                    $user->email = $params_array['email'];
                    $user->role = 'ROLE_USER';
                    $user->validation_token = $validation_token;
                    $user->state = $enums::UserState()['Pending'];
                    $user->password = $pwd;

                    //Guardar el usuario
                    $user->save();

                    Mail::to($user->email)->send(new ConfirmationEmail($constants->apiUrl()."/api/user/".$validation_token));

                    $data = array(
                        'status' => 'success',
                        'code' => '200',
                        'message' => 'Usuario se ha creado correctamente. Por favor confirme su correo electronico.',
                        'user' => $user
                    );
                }
            } else {
                $data = array(
                    'status' => 'error',
                    'code' => '400',
                    'message' => 'Los datos enviados no son correctos.',
                );
            }
        }catch(Exception $e){
            $data = array(
                'status' => 'error',
                'code' => '500',
                'message' => $e
            );
            return Response()->json($data, $data['code']);
        }

        return Response()->json($data, $data['code']);
    }

    public function login(Request $request) {

        $jwtAuth = new \App\Helpers\JwtAuth();

        //Recibir datos

        $json = $request->input('json', null);
        if (is_array($json)) {
            $params = $json;
            $params_array = $json;
        } else {
            $params = json_decode($json);
            $params_array = json_decode($json, true);
        }

        //Validar lo datos
        $validate = \Validator::make($params_array, [
                    'email' => 'required|email',
                    'password' => 'required|regex:/^[a-zA-Z0-9]+$/u',
        ]);
        //Limpiar blancos
        $params_array = array_map('trim', $params_array);

        if ($validate->fails()) {
            $signup = array(
                'status' => 'error',
                'code' => '400',
                'message' => 'A ocurrido un error de validación.',
                'errors' => $validate->errors()
            );
        } else {
            //Cifrar la contraseña
            $pwd = hash('sha256', $params_array['password']);

            $user = User::where('email', $params_array['email'])->first();
            $enums = new Enums();

            $state = $user->state;
            if($state != $enums::UserState()['Active']){
                $signup = array(
                    'status' => 'error',
                    'code' => '400',
                    'message' => 'Debe validar su correo electronico.'
                );
            }else{
                 //Devolver token o datos  
                $signup = $jwtAuth->signup($params_array['email'], $pwd);
                if (!empty($params->gettoken)) {
                    $signup = $jwtAuth->signup($params_array['email'], $pwd, true);
                }
            }        
        }
        return response()->json($signup, 200);
    }

    public function debugger() {
        $data = Debugger::getDatadebug();
        return view('welcome', array('data' => $data));
    }

    public function update(Request $request) {

        //Comprobar usuario identificado
        $token = $request->header('Authorization');
        $jwtAuth = new \App\Helpers\JwtAuth();
        $json = $request->input('json', null);

        $user_token = $jwtAuth->checkToken($token, true);


        if (is_array($json)) {
            $params = $json;
            $params_array = $json;
        } else {
            $params = json_decode($json);
            $params_array = json_decode($json, true);
        }

        if (!empty($params_array) && !empty($params)) {

            //Usuario identificado

            $validate = \Validator::make($params_array, [
                        'passwordNew' => 'required|regex:/^[a-zA-Z0-9]+$/u',
                        'passwordRepeat' => 'required|regex:/^[a-zA-Z0-9]+$/u',
                        'passwordCurrent' => 'required|regex:/^[a-zA-Z0-9]+$/u'
            ]);

            if ($validate->fails()) {
                $data = array(
                    'status' => 'error',
                    'code' => '400',
                    'message' => 'Error al modificar los datos.',
                    'errors' => $validate->errors()
                );
            } else {

                //Cifrar la contraseña
                $pwd = hash('sha256', $params_array['passwordCurrent']);
                //Devolver token o datos

                $signup = $jwtAuth->signup($user_token->email, $pwd);
                if (is_array($signup)) {
                    $data = array(
                        'code' => 400,
                        'status' => 'error',
                        'message' => 'La contraseña actual ingresada no es correcta.'
                    );
                } else {

                    if ($params_array['passwordNew'] == $params_array['passwordRepeat']) {
                        $pwd = hash('sha256', $params_array['passwordNew']);
                        $params_array['password'] = $pwd;

                        unset($params_array['id']);
                        unset($params_array['role']);
                        unset($params_array['created_at']);
                        unset($params_array['remember_token']);
                        unset($params_array['passwordNew']);
                        unset($params_array['passwordRepeat']);
                        unset($params_array['emailRepeat']);
                        unset($params_array['passwordCurrent']);

                        $user_updated = User::where('id', $user_token->id)->update($params_array);

                        $signup = $jwtAuth->signup($user_token->email, $pwd);

                        $data = array(
                            'code' => 200,
                            'status' => 'success',
                            'message' => 'Contraseña cambiada correctamente.',
                            'token' => $signup
                        );
                    } else {
                        $data = array(
                            'code' => 400,
                            'status' => 'error',
                            'message' => 'Las nuevas contraseñas o correos ingresados no coinciden.'
                        );
                    }
                }
            }
        } else {
            $data = array(
                'code' => 400,
                'status' => 'error',
                'message' => 'Usuario no identificado.'
            );
        }

        return response()->json($data, $data['code']);
    }

}
