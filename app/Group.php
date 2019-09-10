<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Group extends Model
{
    //
    protected $fillable = ['name', 'event_id'];

    public function event()
    {
        return $this->belongsTo('App\Event');
    }

    public function teams()
    {
        return $this->belongsToMany('App\Team', 'group_team')->withPivot('odd');
    }

    public function rounds()
    {
        return $this->hasMany('App\Round')->orderBy('number');
    }
}
