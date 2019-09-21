<?php


namespace App\Services;


use App\Game;

class GameService
{
    /**
     * @param int $round_id
     * @return mixed
     */
    public function index(int $round_id)
    {
        return Game::whereRoundId($round_id)->orderBy('track_id');
    }

    /**
     * @param array $data
     * @return mixed
     */
    public function update(Array $data)
    {
        $game = Game::find($data['id']);
        return $game->update($data);
    }
}
