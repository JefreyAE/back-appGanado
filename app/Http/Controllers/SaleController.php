<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Animals;
use App\Sales;
use App\User;

class SaleController extends Controller {

    public function index(Request $request) {
        //Comprobar usuario identificado
        $token = $request->header('Authorization');
        $jwtAuth = new \App\Helpers\JwtAuth();

        //Usuario identificado
        $user_token = $jwtAuth->checkToken($token, true);
        try {

            $listSales = User::join('animals', 'users.id', '=', 'animals.user_id')
                    ->where('user_id', $user_token->id)
                    ->join('sales', 'animals.id', '=', 'sales.animal_id')
                    ->select('sales.*', 'animals.*', 'sales.id as sale_id','animals.id as animal_id')
                    ->orderBy('sale_date', 'DESC')
                    ->get();
        } catch (\Exception $e) {
            $data = array(
                'code' => 500,
                'status' => 'error',
                'message' => $e->getMessage()
            );
            return response()->json($data, $data['code']);
        }
        $data = array(
            'code' => 200,
            'status' => 'success',
            'message' => 'Lista cargada correctamente.',
            'listSales' => $listSales
        );

        return response()->json($data, $data['code']);
    }

    public function getSale(Request $request) {
        $id = $request->route('id');

        try {
            /* Datos generales */
            $sale = Sales::where('id', $id)->first();
        } catch (\Exception $e) {
            $data = array(
                'code' => 500,
                'status' => 'error',
                'message' => $e->getMessage()
            );
            return response()->json($data, $data['code']);
        }
        $data = array(
            'code' => 200,
            'status' => 'success',
            'sale' => $sale
        );

        return response()->json($data, $data['code']);
    }

    public function create(Request $request) {

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
            if (!isset($params_array['weight'])) {
                $params_array['weight'] = 0;
            }
            if (!isset($params_array['price_kg'])) {
                $params_array['price_kg'] = 0;
            }
            if (!isset($params_array['auction_commission'])) {
                $params_array['auction_commission'] = 0;
            }
            if (!isset($params_array['auction_name'])) {
                $params_array['auction_name'] = '';
            }
            $validate = \Validator::make($params_array, [
                        'animal_id' => 'required|numeric',
                        'sale_type' => 'required|regex:/^[a-zA-Z0-9\s]+$/u',
                        'weight' => 'nullable|numeric',
                        'price_total' => 'required|numeric',
                        'price_kg' => 'nullable|numeric',
                        'auction_commission' => 'nullable|numeric',
                        'auction_name' => 'nullable|regex:/^[a-zA-Z0-9.\s\-\/À-ÿ\u00f1\u00d1]+$/u',
                        'sale_date' => 'required|date',
                        'description' => 'required|regex:/^[a-zA-Z0-9,.\s\-\/À-ÿ\u00f1\u00d1]+$/u'
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
                'message' => 'Ocurrio un error durante la validación'
            );
        } else {
            try {
                $user_token = $jwtAuth->checkToken($token, true);

                $sale = new Sales();
                $sale->animal_id = $params_array['animal_id'];
                $sale->sale_type = $params_array['sale_type'];
                $sale->weight = $params_array['weight'];
                $sale->price_total = $params_array['price_total'];
                $sale->price_kg = $params_array['price_kg'];
                $sale->auction_commission = $params_array['auction_commission'];
                $sale->auction_name = $params_array['auction_name'];
                $sale->sale_date = $params_array['sale_date'];
                $sale->description = $params_array['description'];
                $data = array(
                    'code' => 200,
                    'status' => 'success',
                    'message' => 'Venta registrada correctamente.'
                );

                $animal = Animals::where('id', $params_array['animal_id'])->first();
                $animal->animal_state = "Vendido";
                $animal->save();

                $result = $sale->save();
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

    public function find(Request $request) {

        $token = $request->header('Authorization');
        $jwtAuth = new \App\Helpers\JwtAuth();
        $json = $request->input('json', null);
        //Recibir datos
        //Usuario identificado
        $user_token = $jwtAuth->checkToken($token, true);

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
                        'date1' => 'required|date',
                        'date2' => 'required|date'
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
                'message' => $validate->errors()
            );
        } else {
            try {
                $user_token = $jwtAuth->checkToken($token, true);

                $date1 = $params_array['date1'];
                $date2 = $params_array['date2'];

                $listSales = User::join('animals', 'users.id', '=', 'animals.user_id')
                        ->where('user_id', $user_token->id)
                        ->join('sales', 'animals.id', '=', 'sales.animal_id')
                        ->whereBetween('sales.sale_date', [$date1, $date2])
                        ->select('sales.*', 'animals.*', 'sales.id as sale_id','animals.id as animal_id')
                        ->orderBy('sale_date', 'DESC')
                        ->get();

                $data = array(
                    'code' => 200,
                    'status' => 'success',
                    'message' => 'Lista cargada correctamente.',
                    'listSales' => $listSales
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
                        'sale_id' => 'required|numeric'
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
            return response()->json($data, $data['code']);
        } else {
            try {

                $animal_id = $params_array['animal_id'];
                $sale_id = $params_array['sale_id'];

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

                //Actualiza el estado del animal
                $animal->animal_state = "Activo";
                $animal->save();

                $sale = Sales::where('animal_id', $animal_id)->where('id', $sale_id)->first();
                $sale->delete();
              
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
                'message' => 'Registro de venta borrado correctamente'
            );
        }

        return response()->json($data, $data['code']);
    }

    public function update(Request $request) {

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
            if (!isset($params_array['weight'])) {
                $params_array['weight'] = 0;
            }
            if (!isset($params_array['price_kg'])) {
                $params_array['price_kg'] = 0;
            }
            if (!isset($params_array['auction_commission'])) {
                $params_array['auction_commission'] = 0;
            }
            if (!isset($params_array['auction_name'])) {
                $params_array['auction_name'] = '-';
            }
            $validate = \Validator::make($params_array, [
                        'sale_id' => 'required|numeric',
                        'sale_type' => 'required|regex:/^[a-zA-Z0-9\s]+$/u',
                        'weight' => 'nullable|numeric',
                        'price_total' => 'required|numeric',
                        'price_kg' => 'nullable|numeric',
                        'auction_commission' => 'nullable|numeric',
                        'auction_name' => 'nullable|regex:/^[a-zA-Z0-9.\s\-\/À-ÿ\u00f1\u00d1]+$/u',
                        'sale_date' => 'required|date',
                        'description' => 'required|regex:/^[a-zA-Z0-9,.\s\-\/À-ÿ\u00f1\u00d1]+$/u'
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
                'code' => 400,
                'status' => 'error',
                'validationErrors' => $validate->errors(),
                'message' => 'Ocurrio un error durante la validación'
            );
        } else {
            try {
                $user_token = $jwtAuth->checkToken($token, true);

                $sale = Sales::find($params_array['sale_id']);
                $sale->sale_type = $params_array['sale_type'];
                $sale->weight = $params_array['weight'];
                $sale->price_total = $params_array['price_total'];
                $sale->price_kg = $params_array['price_kg'];
                $sale->auction_commission = $params_array['auction_commission'];
                $sale->auction_name = $params_array['auction_name'];
                $sale->sale_date = $params_array['sale_date'];
                $sale->description = $params_array['description'];
                $data = array(
                    'code' => 200,
                    'status' => 'success',
                    'message' => 'Venta actualizada correctamente.'
                );

                $result = $sale->save();
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
}
