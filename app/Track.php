<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Track extends Model
{
    //
    protected $fillable = [ 'name' ];

    public function events()
    {
        return $this->belongsToMany('App\Event', 'event_track');
    }

    public  function games()
    {
        return $this->hasMany('App\Game');
    }
}
