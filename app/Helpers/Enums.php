<?php

namespace App\Helpers;

class Enums
{     

    public static function UserState(){
        return array( 
            'Active' => 1,
            'Inactive' => 2,
            'Pending' => 3,
        );
    }
}

?>