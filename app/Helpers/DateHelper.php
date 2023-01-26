<?php

namespace App\Helpers;

use Carbon\Carbon;

class DateHelper{

    function getAge($date){
            $now = Carbon::now();
            $years = $now->diffInYears($date, $now);
            $months = $now->diffInMonths($date, $now);
            $days = $now->diffInDays($date, $now);

    
            $months = $months-$years*12;
            $days = $days-$months*30.5-$years*365;
    
            $age = $years.' años, '.$months.' meses, y '.$days.' días aproximadamente';
            return $age;
        }
}