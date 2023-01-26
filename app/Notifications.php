<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Notifications extends Model
{
    protected $table = 'notifications';
    
    public function user(){
        return $this->belongsTo("App\User",'user_id');
    }
}
