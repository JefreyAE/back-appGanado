<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Purchases;
use App\User;
use App\Images_Animals;
use App\Animals;
use App\Parents;
use App\Incidents;
use App\Sales;
use App\Injectables;

class PurchaseController extends Controller {

    public function index(Request $request) {
        //Comprobar usuario identificado
        $token = $request->header('Authorization');
        $jwtAuth = new \App\Helpers\JwtAuth();

        //Usuario identificado
        $user_token = $jwtAuth->checkToken($token, true);
        try {

            $listPurchases = User::join('animals', 'users.id', '=', 'animals.user_id')
                    ->where('user_id', $user_token->id)
                    ->join('purchases', 'animals.id', '=', 'purchases.animal_id')
                    ->select('purchases.*', 'animals.*','purchases.id as purchase_id')
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
            'listPurchases' => $listPurchases
        );

        return response()->json($data, $data['code']);
    }

    public function getPurchase(Request $request) {
        $id = $request->route('id');

        try {
            /* Datos generales */
            $purchase = Purchases::where('id', $id)->first();
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
            'purchase' => $purchase
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
                $params_array['auction_name'] = '-';
            }
            $validate = \Validator::make($params_array, [
                        'animal_id' => 'required|numeric',
                        'purchase_type' => 'required|regex:/^[a-zA-Z0-9\s]+$/u',
                        'weight' => 'nullable|numeric',
                        'price_total' => 'required|numeric',
                        'price_kg' => 'nullable|numeric',
                        'auction_commission' => 'nullable|numeric',
                        'auction_name' => 'nullable|regex:/^[a-zA-Z0-9.\s\-\/À-ÿ\u00f1\u00d1]+$/u',
                        'purchase_date' => 'required|date',
                        'description' => 'required|regex:/^[a-zA-Z0-9.\s\-\/À-ÿ\u00f1\u00d1]+$/u'
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

                $purchase = new Purchases();
                $purchase->animal_id = $params_array['animal_id'];
                $purchase->purchase_type = $params_array['purchase_type'];
                $purchase->weight = $params_array['weight'];
                $purchase->price_total = $params_array['price_total'];
                $purchase->price_kg = $params_array['price_kg'];
                $purchase->auction_commission = $params_array['auction_commission'];
                $purchase->auction_name = $params_array['auction_name'];
                $purchase->purchase_date = $params_array['purchase_date'];
                $purchase->description = $params_array['description'];
                $data = array(
                    'code' => 200,
                    'status' => 'success',
                    'message' => 'Compra registrada correctamente.'
                );

                $result = $purchase->save();
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
                'validationErrors' => $validate->errors(),
                'message' => 'Ocurrio un error durante la validación'
            );
        } else {
            try {
                $user_token = $jwtAuth->checkToken($token, true);

                $date1 = $params_array['date1'];
                $date2 = $params_array['date2'];

                $listPurchases = User::join('animals', 'users.id', '=', 'animals.user_id')
                        ->where('user_id', $user_token->id)
                        ->join('purchases', 'animals.id', '=', 'purchases.animal_id')
                        ->whereBetween('purchases.purchase_date', [$date1, $date2])
                        ->select('purchases.*', 'animals.*')
                        ->orderBy('purchase_date', 'DESC')
                        ->get();

                $data = array(
                    'code' => 200,
                    'status' => 'success',
                    'message' => 'Lista cargada correctamente.',
                    'listPurchases' => $listPurchases
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
                        'purchase_id' => 'required|numeric'
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
                $purchase_id = $params_array['purchase_id'];

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

                if($animal['sex'] =='Macho'){
                    $is_parent = Parents::where('father_id', $animal_id)->first();
                }else{
                    $is_parent = Parents::where('mother_id', $animal_id)->first();
                }

                if($is_parent){
                    $data = array(
                        'code' => 400,
                        'status' => 'error',
                        'message' => 'No es posible borrar un registro con descendencia asociada'
                    );
                    return response()->json($data, $data['code']);
                }

                $injectables = Injectables::where('animal_id', $animal_id);
                $injectables->delete();
                $incidents = Incidents::where('animal_id', $animal_id);
                $incidents->delete();
                $purchases = Purchases::where('animal_id', $animal_id);
                $purchases->delete();
                $sales = Sales::where('animal_id', $animal_id);
                $sales->delete();
                $parents = Parents::where('animal_id', $animal_id);
                $parents->delete();

                $images_animal = Images_Animals::where('animal_id', $animal_id)->get();

                foreach($images_animal as $image){
                   
                    if(\Storage::disk('animals')->exists($image['image_name'])){
                        \Storage::disk('animals')->delete($image['image_name']); 
                    }
                }
                $image_animal = Images_Animals::where('animal_id', $animal_id)->delete();

                $animal->delete();
                     
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
                'message' => 'Registro de compra borrado correctamente'
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
                        'purchase_id' => 'required|numeric',
                        'purchase_type' => 'required|regex:/^[a-zA-Z0-9\s]+$/u',
                        'weight' => 'nullable|numeric',
                        'price_total' => 'required|numeric',
                        'price_kg' => 'nullable|numeric',
                        'auction_commission' => 'nullable|numeric',
                        'auction_name' => 'nullable|regex:/^[a-zA-Z0-9.\s\-\/À-ÿ\u00f1\u00d1]+$/u',
                        'purchase_date' => 'required|date',
                        'description' => 'required|regex:/^[a-zA-Z0-9.\s\-\/À-ÿ\u00f1\u00d1]+$/u'
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

                $purchase = Purchases::find($params_array['purchase_id']);
                $purchase->purchase_type = $params_array['purchase_type'];
                $purchase->weight = $params_array['weight'];
                $purchase->price_total = $params_array['price_total'];
                $purchase->price_kg = $params_array['price_kg'];
                $purchase->auction_commission = $params_array['auction_commission'];
                $purchase->auction_name = $params_array['auction_name'];
                $purchase->purchase_date = $params_array['purchase_date'];
                $purchase->description = $params_array['description'];
                $data = array(
                    'code' => 200,
                    'status' => 'success',
                    'message' => 'Compra actualizada correctamente.'
                );

                $result = $purchase->save();
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
