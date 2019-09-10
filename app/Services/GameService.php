<?php


namespace App\Services;


use App\Game;

class GameService
{
    public function index(int $round_id)
    {
        return Game::whereRoundId($round_id);
    }

    public function update(Array $data)
    {
        $game = Game::find($data['id']);
        return $game->update($data);
    }
}
