<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Parents extends Model
{
    protected $table = 'parents';
    
    public function animal(){
        return $this->belongsTo('App\Animals','animal_id');
    }
}
