<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Injectables extends Model
{
    protected $table = 'injectables';
    
    public function animal(){
        return $this->belongsTo('App\animals','animal_id');
    }
}
