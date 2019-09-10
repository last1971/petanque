<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Team extends Model
{
    //
    protected $fillable = ['name'];

    public function groups()
    {
        return $this->belongsToMany('App\Group', 'group_team')->withPivot('odd');
    }

    public function members()
    {
        return $this->hasMany('App\Member');
    }
}
