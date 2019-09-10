<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Round extends Model
{
    //
    protected $fillable = ['number', 'group_id'];

    public function group()
    {
        return $this->belongsTo('App\Group');
    }

    public function games()
    {
        return $this->hasMany('App\Game');
    }
}
