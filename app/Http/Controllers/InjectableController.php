<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Injectables;
use App\Animals;
use App\User;

class InjectableController extends Controller {

    public function index(Request $request) {
        //Comprobar usuario identificado
        $token = $request->header('Authorization');
        $jwtAuth = new \App\Helpers\JwtAuth();

        $user_token = $jwtAuth->checkToken($token, true);
        try {

            $listOne = array();

            $listInjectables = User::join('animals', 'users.id', '=', 'animals.user_id')
                    ->where('user_id', $user_token->id)
                    ->join('injectables', 'animals.id', '=', 'injectables.animal_id')
                    ->select('injectables.*', 'animals.*')
                    ->orderBy('application_date', 'DESC')
                    ->orderBy('creation_time')
                    ->get();
                    $creation_time = "";
            foreach($listInjectables as $array => $injectable){

                if($creation_time != $injectable['creation_time']){
                    array_push($listOne, $injectable);
                }

                $creation_time = $injectable['creation_time'];
            }

        } catch (\Exception $e) {
            $data = array(
                'code' => 500,
                'status' => 'error',
                'message' => $e->getMessage()
            );
        }
        $data = array(
            'code' => 200,
            'status' => 'success',
            'listInjectables' => $listOne 
        );

        return response()->json($data, $data['code']);
    }

    public function create(Request $request) {
        //Comprobar usuario identificado

        $token = $request->header('Authorization');
        $jwtAuth = new \App\Helpers\JwtAuth();
        $json = $request->input('json', null);
        //Recibir datos

        try {
            if (is_array($json)) {
                $params = $json;
                $params_array = $json;
                $es = array(
                    'array' => 'si'
                );
            } else {
                $params = json_decode($json);
                $params_array = json_decode($json, true);
                $es = array(
                    'array' => 'no'
                );
            }
            //Validar lo datos
            if (!isset($params_array['injectable_name'])) {
                $params_array['injectable_name'] = '';
            }
            if (!isset($params_array['injectable_brand'])) {
                $params_array['injectable_brand'] = '';
            }
            if (!isset($params_array['description'])) {
                $params_array['description'] = '';
            }

            $validate = \Validator::make($params_array, [
                        'animal_id' => 'required',
                        'injectable_type' => 'required|regex:/^[a-zA-Z0-9\s\-\/À-ÿ\u00f1\u00d1]+$/u',
                        'application_date' => 'required|date',
                        'injectable_name' => 'nullable|regex:/^[a-zA-Z0-9\s\-\/À-ÿ\u00f1\u00d1]+$/u',
                        'injectable_brand' => 'nullable|regex:/^[a-zA-Z0-9\s\-\/À-ÿ\u00f1\u00d1]+$/u',
                        'withdrawal_time' => 'required|numeric',
                        'effective_time' => 'required|numeric',
                        'description' => 'nullable|regex:/^[a-zA-Z0-9\s\-\/À-ÿ\u00f1\u00d1]+$/u'
            ]);

            //Limpiar blancos
            $params_array = array_map('trim', $params_array);
        } catch (\Exception $e) {
            $data = array(
                'code' => 403,
                'status' => 'error',
                'message' => $e->getMessage()
            );
        }

        if ($validate->fails()) {
            $data = array(
                'status' => 'error',
                'code' => 403,
                'validationErrors' => $validate->errors(),
                'message' => 'Ocurrio un error durante la validación'
            );
        } else {
            try {
                $user_token = $jwtAuth->checkToken($token, true);

                if ($params_array['animal_id'] != "all") {

                    $injectable = new Injectables();
                    $injectable->creation_time = time();
                    $injectable->animal_id = $params_array['animal_id'];
                    $injectable->injectable_type = $params_array['injectable_type'];
                    $injectable->application_date = $params_array['application_date'];
                    $injectable->injectable_name = $params_array['injectable_name'];
                    $injectable->injectable_brand = $params_array['injectable_brand'];
                    $injectable->withdrawal_time = $params_array['withdrawal_time'];
                    $injectable->effective_time = $params_array['effective_time'];
                    $injectable->description = $params_array['description'];
                    $data = array(
                        'code' => 200,
                        'status' => 'success',
                        'message' => 'Inyectable registrado correctamente.'
                    );
                    $result = $injectable->save();
                } else {

                    $user_token = $jwtAuth->checkToken($token, true);
                    try {
                        $listAnimalsActive = Animals::where('user_id', $user_token->id)->where('animal_state', "Activo")->get();
                    } catch (\Exception $e) {
                        $data = array(
                            'code' => 500,
                            'status' => 'error',
                            'message' => $e->getMessage()
                        );
                        return response()->json($data, $data['code']);
                    }
                    $time = time();
                    foreach ($listAnimalsActive as $animal) {
                        $injectable = new Injectables();
                        $injectable->animal_id = $animal['id'];
                        $injectable->creation_time = $time;
                        $injectable->injectable_type = $params_array['injectable_type'];
                        $injectable->application_date = $params_array['application_date'];
                        $injectable->injectable_name = $params_array['injectable_name'];
                        $injectable->injectable_brand = $params_array['injectable_brand'];
                        $injectable->withdrawal_time = $params_array['withdrawal_time'];
                        $injectable->effective_time = $params_array['effective_time'];
                        $injectable->description = $params_array['description'];

                        $result = $injectable->save();
                        if ($result != true) {
                            $data = array(
                                'code' => 400,
                                'status' => 'error',
                                'message' => 'La consulta a fallado'
                            );
                            return response()->json($data, $data['code']);
                        }
                    }
                }

                //return response()->json($listAnimalsActive, 200);
                $data = array(
                    'code' => 200,
                    'status' => 'success',
                    'message' => 'Inyectable registrado correctamente.'
                );
            } catch (\Exception $e) {
                $data = array(
                    'code' => 400,
                    'status' => 'error',
                    'message' => $e->getMessage()
                );
                return response()->json($data, $data['code']);
            }
        }
        return response()->json($data, $data['code']);
    }

    public function detail(Request $request) {
        //Comprobar usuario identificado
        $token = $request->header('Authorization');
        $jwtAuth = new \App\Helpers\JwtAuth();

        $user_token = $jwtAuth->checkToken($token, true);
        $creation_time = $request->route('creation_time');

        try {
            $validate = \Validator::make(['creation_time' => $creation_time], [
                        'creation_time' => 'required|numeric'
            ]);
        } catch (\Exception $e) {
            $data = array(
                'code' => 403,
                'status' => 'error',
                'message' => $e->getMessage()
            );
            return response()->json($data, $data['code']);
        }

        if ($validate->fails()) {
            $data = array(
                'status' => 'error',
                'code' => 403,
                'validationErrors' => $validate->errors(),
                'message' => 'Ocurrio un error durante la validación'
            );
            return response()->json($data, $data['code']);
        }

        try {
            $listInjectables = User::join('animals', 'users.id', '=', 'animals.user_id')
                    ->where('user_id', $user_token->id)
                    ->join('injectables', 'animals.id', '=', 'injectables.animal_id')
                    ->where('injectables.creation_time', '=', $creation_time)
                    ->select('injectables.*', 'animals.*')
                    ->orderBy('application_date', 'DESC')
                    ->orderBy('creation_time')
                    ->get();
        } catch (\Exception $e) {
            $data = array(
                'code' => 500,
                'status' => 'error',
                'message' => $e->getMessage()
            );
        }
        $data = array(
            'code' => 200,
            'status' => 'success',
            'listInjectables' => $listInjectables
        );

        return response()->json($data, $data['code']);
    }

    public function deleteOne(Request $request){
        //Comprobar usuario identificado        
        $token = $request->header('Authorization');
        $jwtAuth = new \App\Helpers\JwtAuth();
       
        $json = $request->input('json', null);

        try {
            if (is_array($json)) {
                $params = $json;
                $params_array = $json;    
            } else {
                $params = json_decode($json);
                $params_array = json_decode($json, true);      
            }

            $validate = \Validator::make($params_array, [
                        'animal_id' => 'required|numeric',
                        'creation_time' => 'required|numeric' 
            ]);

            //Limpiar blancos
            $params_array = array_map('trim', $params_array);

        } catch (\Exception $e) {
            $data = array(
                'code' => 403,
                'status' => 'error',
                'message' => $e->getMessage()
            );
            return response()->json($data, $data['code']);
        }

        if ($validate->fails()) {
            $data = array(
                'status' => 'error',
                'code' => 403,
                'validationErrors' => $validate->errors(),
                'message' => 'Ocurrio un error durante la validación'
            );
        } else {
            try {

                $animal_id = $params_array['animal_id'];
                $creation_time = $params_array['creation_time'];

                $user_token = $jwtAuth->checkToken($token, true);
 
                $animal = Animals::where('id', $animal_id)->where('user_id', $user_token->id)->first();

                if(!$animal){
                    $data = array(
                        'code' => 500,
                        'status' => 'error',
                        'message' => 'No fue posible borrar el registro'
                    );
                    return response()->json($data, $data['code']);
                }

                $injectable = Injectables::where('animal_id', $animal_id)->where('creation_time', $creation_time)->first();
                $injectable->delete();
              
            } catch (\Exception $e) {
                $data = array(
                    'code' => 400,
                    'status' => 'error',
                    'message' => $e->getMessage()
                );
                return response()->json($data, $data['code']);
            }
         
            $data = array(
                'code' => 200,
                'status' => 'success',
                'message' => 'Inyectable borrado correctamente'
            );
        }

        return response()->json($data, $data['code']);
 
    }

    public function delete(Request $request){
        //Comprobar usuario identificado        
        $token = $request->header('Authorization');
        $jwtAuth = new \App\Helpers\JwtAuth();
       
        $json = $request->input('json', null);

        try {
            if (is_array($json)) {
                //$params = $json;
                $params_array = $json;    
            } else {
                //$params = json_decode($json);
                $params_array = json_decode($json, true);               
            }

            $validate = \Validator::make($params_array, [
                        'creation_time' => 'required|numeric' 
            ]);

            //Limpiar blancos
            $params_array = array_map('trim', $params_array);

        } catch (\Exception $e) {
            $data = array(
                'code' => 403,
                'status' => 'error',
                'message' => $e->getMessage()
            );
            return response()->json($data, $data['code']);
        }

        if ($validate->fails()) {
            $data = array(
                'status' => 'error',
                'code' => 403,
                'validationErrors' => $validate->errors(),
                'message' => 'Ocurrio un error durante la validación'
            );
        } else {
            try {

                $creation_time = $params_array['creation_time'];

                $user_token = $jwtAuth->checkToken($token, true);
                $first_injectable = Injectables::where('creation_time', $creation_time)->first();
                $animal_id = $first_injectable->animal_id;

                $animal = Animals::where('id', $animal_id)->where('user_id', $user_token->id)->first();

                if(!$animal){
                    $data = array(
                        'code' => 500,
                        'status' => 'error',
                        'message' => 'No fue posible borrar el registro'
                    );
                    return response()->json($data, $data['code']);
                }

                $injectable = Injectables::where('creation_time', $creation_time)->delete();

            } catch (\Exception $e) {
                $data = array(
                    'code' => 400,
                    'status' => 'error',
                    'message' => $e->getMessage()
                );
                return response()->json($data, $data['code']);
            }
         
            $data = array(
                'code' => 200,
                'status' => 'success',
                'message' => 'Inyectable borrado correctamente'
            );
        }

        return response()->json($data, $data['code']);
    }


}
