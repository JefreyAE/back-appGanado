<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Sales extends Model
{
    protected $table = 'sales';
    
    public function animal(){
        return $this->belongsTo('App\Animals','animal_id');
    }
}
