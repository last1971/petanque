<?php

namespace App;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class Event extends Model
{
    //
    protected $fillable = [ 'date', 'name', 'rounds', 'user_id' ];

    protected $casts = [ 'date' => 'date:d/m/Y' ];

    /**
     * @param $value
     */
    public function setDateAttribute( $value )
    {
        $this->attributes['date'] = Carbon::createFromFormat('d/m/Y', $value);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user()
    {
        return $this->belongsTo('App\User');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function rounds()
    {
        return $this->hasMany('App\Round')->orderBy('id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function teams()
    {
        return $this->belongsToMany('App\Team', 'event_team');
    }
}
