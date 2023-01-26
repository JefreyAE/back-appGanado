<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Animals extends Model
{
    protected $table = 'animals';
    
    public function user(){
      return $this->belongsTo('App\User','user_id');
    }
    
    public function injectables(){
        return $this->hasMany('App\Injectables');
    }
    
    public function incidents(){
        return $this->hasMany('App\Incidents');
    }
    
    public function sale(){
        return $this->hasOne('App\Sales');
    }
    
    public function parents(){
        return $this->hasOne('App\Parents');
    }
}
