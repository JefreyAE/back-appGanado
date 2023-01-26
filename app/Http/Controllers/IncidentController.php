<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Incidents;
use App\Animals;
use App\User;

class IncidentController extends Controller {

    public function index(Request $request) {
        //Comprobar usuario identificado
        $token = $request->header('Authorization');
        $jwtAuth = new \App\Helpers\JwtAuth();

        //Obteniendo datos del usuario identificado
        $user_token = $jwtAuth->checkToken($token, true);
        try {

            $listIncidents = User::join('animals', 'users.id', '=', 'animals.user_id')
                    ->where('user_id', $user_token->id)
                    ->join('incidents', 'animals.id', '=', 'incidents.animal_id')
                    ->select('incidents.*', 'animals.*','incidents.id as incident_id')
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
            'message' => 'Lista cargada correctamente.',
            'listIncidents' => $listIncidents
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

            $validate = \Validator::make($params_array, [
                        'animal_id'     => 'required|numeric',
                        'incident_date' => 'required|date',
                        'incident_type' => 'required|regex:/^[a-zA-Z0-9,.\s\-\/À-ÿ\u00f1\u00d1]+$/u',
                        'description'   => 'required|regex:/^[a-zA-Z0-9,.\s\-\/À-ÿ\u00f1\u00d1]+$/u'
            ]);

            //Limpiar blancos
            $params_array = array_map('trim', $params_array);
        } catch (\Exception $e) {
            $data = array(
                'code' => 400,
                'status' => 'error',
                'message' => $e->getMessage()
            );
            return response()->json($data, $data['code']);
        }
        if ($validate->fails()) {
            $data = array(
                'status' => 'error',
                'code' => '400',
                'validationErrors' => $validate->errors(),
                'message' => $params_array['incident_type']
            );
        } else {
            try {
                $user_token = $jwtAuth->checkToken($token, true);

                $incident = new Incidents();
                $incident->animal_id = $params_array['animal_id'];
                $incident->incident_date = $params_array['incident_date'];
                $incident->incident_type = $params_array['incident_type'];
                $incident->description = $params_array['description'];
                $incident->save();

                $data = array(
                    'code' => 200,
                    'status' => 'success',
                    'message' => 'Incidente registrado correctamente.'
                );

                if ($params_array['incident_type'] == 'Muerte') {
                    $animal = Animals::where('id', $params_array['animal_id'])->first();
                    $animal->animal_state = "Muerto";
                    $animal->save();
                }
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

    public function pruebas(Request $request) {
        return "Accion de pruebas de UserController";
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
                        'incident_id' => 'required|numeric'
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
                $incident_id = $params_array['incident_id'];

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

                $incident = Incidents::where('animal_id', $animal_id)->where('id', $incident_id)->first();
                $incident->delete();
              
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
                'message' => 'Incidente borrado correctamente'
            );
        }

        return response()->json($data, $data['code']);
    }

}
