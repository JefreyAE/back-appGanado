<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Images_Animals extends Model
{
    protected $table = 'images_animals';
    
    public function animal(){
      return $this->belongsTo('App\Animals','animal_id');
    }
}