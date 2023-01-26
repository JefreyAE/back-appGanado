<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Incidents extends Model
{
    protected $table = 'incidents';
    
    public function animal(){
      return $this->belongsTo('App\Animals','animal_id');
    }
}
