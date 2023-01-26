<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Purchases extends Model
{
    protected $table = 'purchases';
    
    public function animal(){
        return $this->belongsTo('App\Animals','animal_id');
    }
}