<?php

namespace App;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class Event extends Model
{
    //
    protected $fillable = [ 'date', 'name', 'rounds', 'user_id' ];

    protected $casts = [ 'date' => 'date:d/m/Y' ];

    public function setDateAttribute( $value )
    {
        $this->attributes['date'] = Carbon::createFromFormat('d/m/Y', $value);
    }

    public function user()
    {
        return $this->belongsTo('App\User');
    }

    public function groups()
    {
        return $this->hasMany('App\Group')->orderBy('name');
    }

    public function tracks()
    {
        return $this->belongsToMany('App\Track', 'event_track');
    }
}
