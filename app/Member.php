<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Member extends Model
{
    //
    protected $fillable = [ 'game_id', 'team_id', 'points', 'winner' ];

    protected $casts = [ 'winner' => 'boolean' ];

    public function game()
    {
        return $this->belongsTo('App\Game');
    }

    public function team()
    {
        return $this->belongsTo('App\Team');
    }
}
