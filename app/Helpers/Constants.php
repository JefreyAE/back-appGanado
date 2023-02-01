<?php

namespace App\Helpers;

/**
 * Description of Constants
 *
 * @author Jefrey
 */
class Constants {
   
    var $urlAPI;
    public function __construct() {
        //Modidificar esta variable para especificar la ruta de la API
        //$this->urlAPI = 'http://apirestlaravel.erpsolutionscr.com';
        $this->urlAPI ='http://localhost/back-appGanado/public';
        $this->urlFront ='http://localhost/front-appGanado/public';
    }
    
    public function apiUrl(){
        return $this->urlAPI;
    }

    public function frontUrl(){
        return $this->urlFront;
    }
}
