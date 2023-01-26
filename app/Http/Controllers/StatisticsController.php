<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Animals;
use App\Users;
//use App\Services\StatisticsService;

class StatisticsController extends Controller
{
    
    public function __construct() {
        $this->_statisticsService = new \App\Services\StatisticsService();
    }

    public function index(Request $request) {
        //Comprobar usuario identificado
        $token = $request->header('Authorization');
        

        //Obteniendo datos del usuario identificado
        $jwtAuth = new \App\Helpers\JwtAuth();
        $user_token = $jwtAuth->checkToken($token, true);

        try {

            $listStatisticsGlobal = $this->_statisticsService->getStatisticsGlobals($user_token->id);

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
            'message' => 'Estadísticas enviadas correctamente.',
            'listStatisticsGlobal' => $listStatisticsGlobal
        );

        return response()->json($data, $data['code']);
    }

    public function auctions(){
        try {

            $listStatisticsAuctions = $this->_statisticsService->getStatisticsAuctions();
            
        } catch (\Exception $e) {
           
        }
        $data = array(
            'code' => 200,
            'status' => 'success',
            'message' => 'Estadísticas enviadas correctamente.',
            'listStatisticsAuctions' => $listStatisticsAuctions
        );

        return response()->json($data, $data['code']);
    }
}
