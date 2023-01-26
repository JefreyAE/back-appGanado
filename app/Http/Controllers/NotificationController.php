<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Notifications;
use Carbon\Carbon;
use App\Injectables;
use App\User;
use App\Animals;

class NotificationController extends Controller {

    public function index(Request $request) {
        //Comprobar usuario identificado
        $token = $request->header('Authorization');
        $jwtAuth = new \App\Helpers\JwtAuth();
        $cont = 0;

        $user_token = $jwtAuth->checkToken($token, true);
        try {
            $listActive = Notifications::where('user_id', $user_token->id)
                    ->where('notification_state', "Active")
                    ->orderBy('created_at')
                    ->get();
            foreach($listActive as $notification){
                if($notification['notification_type'] == 'Destete'){
                    $animal = Animals::where('id', $notification['code'])->first();
                    $listActive[$cont]['animal'] = $animal;
                }
                $cont++;
            }
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
            'listActive' => $listActive
        );

        return response()->json($data, $data['code']);
    }

    public function generate(Request $request) {
        //Comprobar usuario identificado
        $token = $request->header('Authorization');
        $jwtAuth = new \App\Helpers\JwtAuth();

        $user_token = $jwtAuth->checkToken($token, true);
        try {

            //********************************Notificaciones de injectables
            //Saca los injectables aplicados en los últimos 6 meses
            $listInjectables = User::join('animals', 'users.id', '=', 'animals.user_id')
                    ->where('user_id', $user_token->id)
                    ->join('injectables', 'animals.id', '=', 'injectables.animal_id')
                    ->where('injectables.application_date', '>', Carbon::now()->subDays(180))
                    ->select('injectables.*')
                    ->orderBy('application_date', 'DESC')
                    ->orderBy('creation_time')
                    ->get();

            //$suborder['payment_date'] = $createdAt->format('M d Y');

            foreach ($listInjectables as $injectable) {
                $days = $injectable['effective_time'];

                $due = Carbon::parse($injectable['application_date'])->addDays($days);

                $current = Carbon::now();
                $diff = abs($current->diffInDays($due));

                if ($diff <= 5) {
                    //Usa el creation_time como identificador único
                    $code = $injectable['creation_time'];
                    $notification = new Notifications();
                    $exists = $notification::where('code', $code)
                            ->exists();

                    if (!$exists) {
                        $notification->user_id = $user_token->id;
                        $notification->notification_date = $due;
                        $notification->notification_type = "Injectable";
                        $notification->notification_state = 'Active';
                        $notification->description = "Vence la efectividad " . $injectable['description'];
                        $notification->code = $code;
                        $notification->save();
                    }
                }
            }

            //$queryResult = Carbon::now()->subDays(180);
            /* $queryResult = Notifications::where('user_id',$user_token->id)
              ->where( 'created_at', '<', Carbon::now()->subDays(180))
              ->get(); */


//***************************Notificaciones de destetes           
            $listAnimals = Animals::where('user_id', $user_token->id)
                    ->where('animal_state', "Activo")
                    ->where('birth_date', '>', Carbon::now()->subDays(240))
                    ->orderBy('created_at')
                    ->get();

            foreach ($listAnimals as $animal) {
                $days = 210; // 7 meses

                $due = Carbon::parse($animal['birth_date'])->addDays($days);

                $current = Carbon::now();
                $diff = abs($current->diffInDays($due));

                if ($diff <= 30) {

                    $code = $animal['id'];
                    $notification1 = new Notifications();
                    $exists = $notification1::where('code', $code)
                            ->exists();

                    if (!$exists) {
                        $notification1->user_id = $user_token->id;
                        $notification1->notification_date = $due;
                        $notification1->notification_type = "Destete";
                        $notification1->notification_state = 'Active';
                        $notification1->description = "El animal se encuentra en periodo de destete";
                        $notification1->code = $code;
                        $notification1->save();
                    }
                }
            }

            $queryResult = true;
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
            'listActive' => $queryResult //'vacia'; //$listActive
        );

        return response()->json($data, $data['code']);
    }

    public function indexAll(Request $request) {
        //Comprobar usuario identificado
        $token = $request->header('Authorization');
        $jwtAuth = new \App\Helpers\JwtAuth();
        $cont = 0;

        $user_token = $jwtAuth->checkToken($token, true);
        try {
            $listAll = Notifications::where('user_id', $user_token->id)
                    ->orderBy('created_at')
                    ->get();
            foreach($listAll as $notification){
                if($notification['notification_type'] == 'Destete'){
                    $animal = Animals::where('id', $notification['code'])->first();
                    $listAll[$cont]['animal'] = $animal;
                }
                $cont++;
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
            'listAll' => $listAll
        );

        return response()->json($data, $data['code']);
    }

    public function checked(Request $request) {
        //Comprobar usuario identificado
        $token = $request->header('Authorization');
        $jwtAuth = new \App\Helpers\JwtAuth();
        $cont = 0;

        $user_token = $jwtAuth->checkToken($token, true);
        try {
            $listChecked = Notifications::where('user_id', $user_token->id)
                    ->where('notification_state', "Checked")
                    ->orderBy('created_at')
                    ->get();
            foreach($listChecked as $notification){
                if($notification['notification_type'] == 'Destete'){
                    $animal = Animals::where('id', $notification['code'])->first();
                    $listChecked[$cont]['animal'] = $animal;
                }
                $cont++;
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
            'listChecked' => $listChecked
        );

        return response()->json($data, $data['code']);
    }

    public function state(Request $request) {
        //Comprobar usuario identificado
        $token = $request->header('Authorization');
        $jwtAuth = new \App\Helpers\JwtAuth();
        $id = $request->route('id');
        try {
            $notification = Notifications::where('id', $id)->first();
            $notification->notification_state = "Checked";
            $notification->save();
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
            'message' => 'Cambio de estado exitoso'
        );

        return response()->json($data, $data['code']);
    }

}
