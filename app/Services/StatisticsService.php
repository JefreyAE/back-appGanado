<?php

namespace App\Services;

use App\Incidents;
use App\Animals;
use App\User;
use App\Sales;
use App\Purchases;
use Carbon\Carbon;
use phpDocumentor\Reflection\DocBlock\Tags\Var_;

class StatisticsService
{

    private $listStatisticsGlobal = array();

    public function getStatisticsGlobals($user_id)
    {

        $salesDataByYear = array();
        $purchasesDataByYear = array();
        $birthsDataByYear = array();

        $activeAnimalNumber = Animals::where('user_id', $user_id)
            ->where('animal_state', "Activo")
            ->count();

        $dateNow = Carbon::now();
        $monthNow = $dateNow->format('m');
        $dayNow = $dateNow->format('d');
        $yearNow = $dateNow->format('Y');
        for ($year = $yearNow; $year >= 2019; $year--) {

            $start = $year . "-1-1";
            $end   = ($year) . "-12-31";

            $salesNumberByDate = User::join('animals', 'animals.user_id', '=', 'users.id')
                ->where('animals.user_id', '=', $user_id)
                ->join('sales', 'sales.animal_id', '=', 'animals.id')
                ->whereBetween('sales.sale_date', [$start, $end])
                ->count();

            $purchasesNumberByDate = User::join('animals', 'animals.user_id', '=', 'users.id')
                ->where('animals.user_id', '=', $user_id)
                ->join('purchases', 'purchases.animal_id', '=', 'animals.id')
                ->whereBetween('purchases.purchase_date', [$start, $end])
                ->count();

            $birthsNumberByDate = User::join('animals', 'animals.user_id', '=', 'users.id')
                ->where('animals.user_id', '=', $user_id)
                ->whereBetween('animals.birth_date', [$start, $end])
                ->count();

            array_push($birthsDataByYear, array(
                'year'               => $year,
                'birthsNumberByDate' => $birthsNumberByDate
            ));

            $salesAmountByDate = User::join('animals', 'animals.user_id', '=', 'users.id')
                ->where('animals.user_id', '=', $user_id)
                ->join('sales', 'sales.animal_id', '=', 'animals.id')
                ->whereBetween('sales.sale_date', [$start, $end])
                ->sum('sales.price_total');

            array_push($salesDataByYear, array(
                'year'              => $year,
                'salesNumberByDate' => $salesNumberByDate,
                'salesAmountByDate' => $salesAmountByDate
            ));

            $purchasesAmountByDate = User::join('animals', 'animals.user_id', '=', 'users.id')
                ->where('animals.user_id', '=', $user_id)
                ->join('purchases', 'purchases.animal_id', '=', 'animals.id')
                ->whereBetween('purchases.purchase_date', [$start, $end])
                ->sum('purchases.price_total');

            array_push($purchasesDataByYear, array(
                'year'                  => $year,
                'purchasesNumberByDate' => $purchasesNumberByDate,
                'purchasesAmountByDate' => $purchasesAmountByDate
            ));
        }


        $SalesNumber = User::join('animals', 'animals.user_id', '=', 'users.id')
            ->where('animals.user_id', '=', $user_id)
            ->join('sales', 'sales.animal_id', '=', 'animals.id')->count();

        $listStatisticsGlobal = array(
            'activeAnimalNumber'  => $activeAnimalNumber,
            'SalesNumber'         => $SalesNumber,
            'salesDataByYear'     => $salesDataByYear,
            'purchasesDataByYear' => $purchasesDataByYear,
            'birthsDataByYear'    => $birthsDataByYear
        );

        return $listStatisticsGlobal;
    }

    public function getStatisticsAuctions()
    {

        $listAuctions = [
            "Subasta Ganadera Rio Blanco",
            "Subasta ExpoPococi",
            "Subasta de Valle la Estrella",
            "Cámara de Ganaderos Unidos del Sur",
            "Subasta Ganadera UPAP",
            "Subasta Ganadera Sancarleña S.A.",
            "Subasta Ganadera Maleco Guatuso S.A.",
            "Subasta Ganadera Montecillos, Upala",
            "Grupo de Subastas Sarapiquí PJ",
            "Subasta Ganadera El Progreso de Nicoya",
            "Subasta Cámara de Ganaderos de Santa Cruz",
            "Subasta Cámara de Ganaderos de Cañas",
            "Subasta Limonal",
            "Subasta de Tilarán",
            "Subasta Cámara de Ganaderos de Hojancha",
            "Subasta de Ganadera Liberia 07",
            "Subasta Ganadera AGAINPA",
            "Subasta Ganadera El Progreso",
            "Subasta Ganadera de Parrita",
            "Subasta Salamá",
            "Subasta San Vito",
            "Otro"
        ];

        $price_byWeight = array();

        foreach ($listAuctions as $auction) {

            $auct50100 = array();
            $auct100200 = array();
            $auct200300 = array();
            $auct300400 = array();
            $auct400600 = array();
            $auct600900 = array();
            $auct9001200 = array();

            $start = Carbon::now();
            try {
                for ($i = 0; $i < 10; $i++) {

                    $start = $start->subDays(1);
                    $date = $start->toDateString();

                    $countSales = Sales::where('sale_date', '=', $date)
                        ->where('auction_name', '=', $auction)
                        ->count();

                    if ($countSales != 0) {
                        $r1 = $this->dataByWeight($auction, $date, 50, 100);
                        $r2 = $this->dataByWeight($auction, $date, 100, 200);
                        $r3 = $this->dataByWeight($auction, $date, 200, 300);
                        $r4 = $this->dataByWeight($auction, $date, 300, 400);
                        $r5 = $this->dataByWeight($auction, $date, 400, 600);
                        $r6 = $this->dataByWeight($auction, $date, 600, 900);
                        $r7 = $this->dataByWeight($auction, $date, 900, 1200);

                        if ($r1) {
                            array_push($auct50100, $r1);
                        }
                        if ($r2) {
                            array_push($auct100200,  $r2);
                        }
                        if ($r3) {
                            array_push($auct200300,  $r3);
                        }
                        if ($r4) {
                            array_push($auct300400,  $r4);
                        }
                        if ($r5) {
                            array_push($auct400600,  $r5);
                        }
                        if ($r6) {
                            array_push($auct600900,  $r6);
                        }
                        if ($r7) {
                            array_push($auct9001200, $r7);
                        }
                    }
                }
            } catch (\Exception $e) {
                $data = array(
                    'code' => 500,
                    'status' => 'error xx',
                    'message' => $e->getMessage()
                );
               // return $e->getMessage();
            }

            $limpio = array();

            if ($auct50100 != []) {
                array_push($limpio,  ['50-100' => $auct50100]);
            }
            if ($auct100200 != []) {
                array_push($limpio, ['100-200' => $auct100200]);
            }
            if ($auct200300 != []) {
                array_push($limpio, ['200-300' => $auct200300]);
            }
            if ($auct300400 != []) {
                array_push($limpio, ['300-400' => $auct300400]);
            }
            if ($auct400600 != []) {
                array_push($limpio, ['400-600' => $auct400600]);
            }
            if ($auct600900 != []) {
                array_push($limpio, ['600-900' => $auct600900]);
            }
            if ($auct9001200 != []) {
                array_push($limpio, ['900-1200' => $auct9001200]);
            }

            array_push($price_byWeight, [$auction => $limpio]);
        }

        return  $price_byWeight;
    }

    public function queryMaxPrice($auction, $date, $min, $max, $sex)
    {
        $query = User::join('animals', 'animals.user_id', '=', 'users.id')
            ->where('users.type', '=', 'colaborator')
            ->join('sales', 'sales.animal_id', '=', 'animals.id')
            ->where('animals.sex', '=', $sex)
            ->where('sales.sale_date', '=', $date)
            ->where('sales.sale_type', '=', "Subasta")
            ->where('sales.auction_name', '=', $auction)
            ->whereBetween('sales.weight', [$min, $max])
            ->orderBy('sales.price_kg', 'DESC')
            ->first();
        //return var_dump($query);
        return $query;
    }

    public function queryMinPrice($auction, $date, $min, $max, $sex)
    {
        $query = User::join('animals', 'animals.user_id', '=', 'users.id')
            ->where('users.type', '=', 'colaborator')
            ->join('sales', 'sales.animal_id', '=', 'animals.id')
            ->where('animals.sex', '=', $sex)
            ->where('sales.sale_date', '=', $date)
            ->where('sales.auction_name', '=', $auction)
            ->where('sales.sale_type', '=', "Subasta")
            ->whereBetween('sales.weight', [$min, $max])
            ->orderBy('sales.price_kg', 'ASC')
            ->first();

        return $query;
    }

    public function queryAvgPrice($auction, $date, $min, $max, $sex)
    {
        $query = User::join('animals', 'animals.user_id', '=', 'users.id')
            ->where('users.type', '=', 'colaborator')
            ->join('sales', 'sales.animal_id', '=', 'animals.id')
            ->where('animals.sex', '=', $sex)
            ->where('sales.sale_date', '=', $date)
            ->where('sales.auction_name', '=', $auction)
            ->where('sales.sale_type', '=', "Subasta")
            ->whereBetween('sales.weight', [$min, $max])
            ->avg('sales.price_kg');

        return $query;
    }

    public function dataByWeight($auction, $date, $min, $max)
    {
        try {
            $max_price_male = $this->queryMaxPrice($auction, $date, $min, $max, "Macho");

            if(is_object($max_price_male)){
                $max_price_male = $max_price_male->price_kg;
            }
            $max_price_female = $this->queryMaxPrice($auction, $date, $min, $max, "Hembra");
            if(is_object($max_price_female)){
                $max_price_female = $max_price_female->price_kg;
            }
            //$min_price_male = $this->queryMinPrice($auction, $date, $min, $max, "Macho");
            //$min_price_female = $this->queryMinPrice($auction, $date, $min, $max, "Hembra");

            $avg_price_male = $this->queryAvgPrice($auction, $date, $min, $max, "Macho");
            if(is_object($avg_price_male)){
                $avg_price_male = $avg_price_male->price_kg;
            }
            $avg_price_female = $this->queryAvgPrice($auction, $date, $min, $max, "Hembra");
            if(is_object($avg_price_female)){
                $avg_price_female = $avg_price_female->price_kg;
            }
            if (!$max_price_male && !$max_price_female && !$avg_price_male && !$avg_price_female) {
                return false;
            }
        } catch (\Exception $e) {
            
        }

        return [
            $date => [
                'max_price_male' => $max_price_male, 'max_price_female' => $max_price_female,
                //'min_price_male' => $min_price_male, 'min_price_female' => $min_price_female,
                'avg_price_male' => $avg_price_male, 'avg_price_female' => $avg_price_female
            ]
        ];
    }
}
