<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Game extends Model
{
    //
    protected $fillable = [ 'name', 'round_id', 'track_id' ];

    public function round()
    {
        return $this->belongsTo('App\Round');
    }

    public function members()
    {
        return $this->hasMany('App\Member');
    }

    public function track()
    {
        return $this->belongsTo('App\Track');
    }
}
