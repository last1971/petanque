<?php


namespace App\Services;


use App\Game;
use App\Group;
use App\Member;
use App\Round;
use App\Team;

class RoundService
{
    /**
     * @param int $group_id
     * @return mixed
     */
    public function index(int $group_id)
    {
        //
        return Round::whereGroupId($group_id)->orderBy('number');
    }

    public function makeNextRound(Group $group)
    {
        throw_if(
            $group->teams()->count() - 1 == $group->rounds()->count(),
            new \Exception('Все уже наигрались')
        );
        $round = $group->rounds()->get()->last();
        if ($round) {
            $round = Round::create([
                'number' => $round->number + 1,
                'group_id' => $group->id
            ]);
        } else {
            $round = Round::create([
                'number' => 1,
                'group_id' => $group->id
            ]);
        }
        return $round;
    }

    public function create_v2($group_id)
    {
        $group = Group::find($group_id);
        $round = $this->makeNextRound($group);
        $gs = new GroupService();
        $teams = $gs->rating($group_id)->get();
        foreach ($teams as $team) {
            $team->closed = false;
        }
        $wins = $teams[0]->winner;
        $pools = collect();
        while ($wins > -1) {
            $pools->put($wins, $teams->where('winner', '=', $wins));
            $wins--;
        }
        $wins = $teams[0]->winner;
        $additional = collect();
        $tracks = $group->event->tracks()->get();
        while ($wins > -1) {
            $next_pool = $pools->get($wins)->values();
            if ($additional->count() < $next_pool->count() && $additional->count() != 0)
            {
                foreach ($additional as $add) {
                    $this->create_game_v2($add, $next_pool, 0, $next_pool->count(), $group, $round, $tracks);
                }
                $additional = $additional->where('closed', '=', false);
                throw_if($additional->count() > 0, new \Exception('Не удачный сдвиг'));
                $next_pool = $next_pool->where('closed', '=', false);
            }
            $pool = $additional->merge($next_pool);
            $half = (int)($pool->count() /2);
            for ($i = 0; $i < $half; $i++)
            {
                $this->create_game_v2($pool[$i], $pool, $half, $pool->count(), $group, $round, $tracks);
                if (!$pool[$i]->closed) { //тут копия предыдущего
                    $this->create_game_v2($pool[$i], $pool, $i + 1, $half, $group, $round, $tracks); //тут возможно перебирать весь массив до середины
                }
            }
            $additional = $pool->where('closed', '=', false);
            $wins--;
        }
        return $round;
    }

    public function create($group_id)
    {
        $group = Group::find($group_id);
        $round = $this->makeNextRound($group);
        $gs = new GroupService();
        $teams = $gs->rating($group_id)->get();
        foreach ($teams as $team) {
            $team->closed = false;
        }
        $flag = true;
        $iter = 0;
        $tracks = $group->event->tracks()->get();
        while ($flag) {
            $start = 0;
            while ($start < $teams->count()) {
                $stop = $start + (int)ceil($teams->where('winner', '=', $teams[$start]->winner)->count() / 2);
                for ($i = $start; $i < $stop; $i++) {
                    if (!$teams[$i]->closed) {
                        $ailibale_tracks = $tracks->whereNotIn('id', $this->used_tracks($teams[$i], $group));
                        $was = $this->was($teams[$i]->id, $group->id);
                        for ($j = $stop; $j < $teams->count(); $j++) {
                            if (!$teams[$j]->closed && !$was->contains($teams[$j]->id)) {
                                $ailibale_tracks = $ailibale_tracks->whereNotIn('id', $this->used_tracks($teams[$j], $group));
                                $track = $ailibale_tracks->isEmpty() ? $tracks->pop() : $ailibale_tracks->first();
                                $tracks = $tracks->filter(function ($value, $key) use ($track) {
                                    return $value->id != $track->id;
                                });
                                $this->create_game($teams[$i],$teams[$j], $round->id, $track->id );
                                break;
                            }
                        }
                    }
                }
                $start += $teams->where('winner', '=', $teams[$start]->winner)->count();
            }
            if ($tracks->count() > 0) {
                $ostatki = $teams->where('closed', '=', false);
                $was = $this->was($ostatki->first()->id, $group->id);
                if (!$was->contains($ostatki->last()->id)) {
                    $track = $tracks->pop();
                    $team1 = $ostatki->first();
                    $team2 = $ostatki->last();
                    $this->create_game($team1,$team2, $round->id, $track->id);
                    $flag = false;
                } else {
                    $round->load('games');
                    foreach ($round->games as $game) {
                        $game->members()->delete();
                    }
                    $round->games()->delete();
                    foreach ($teams as $team) {
                        $team->closed = false;
                    }
                    $was = $this->was($teams[0]->id, $group->id);
                    $not_was = $teams->filter(function ($team) use ($was, $teams) {
                        return !$was->contains($team->id) && $team->id != $teams[0]->id;
                    });
                    $tracks = $group->event->tracks()->get();
                    $track = $tracks->pop();
                    $team1 = $teams[0];
                    $team2 = $not_was[$not_was->keys()[$iter]];
                    $iter++;
                    $this->create_game($team1, $team2, $round->id, $track->id);
                }
            } else {
                $flag = false;
            }
        }
        return $round;
    }

    private function create_game_v2(&$first, &$second, $start, $end, $group, $round, &$tracks)
    {
        $ailibale_tracks = $tracks->whereNotIn('id', $this->used_tracks($first, $group));
        $result = false;
        $was = $this->was($first->id, $group->id);
        for ($j = $start; $j < $end; $j++) { //Ниже в if копия кода
            if (!$second[$j]->closed && !$was->contains($second[$j]->id)) {
                $ailibale_tracks = $ailibale_tracks->whereNotIn('id', $this->used_tracks($second[$j], $group));
                $track = $ailibale_tracks->isEmpty() ? $tracks->pop() : $ailibale_tracks->first();
                $tracks = $tracks->filter(function ($value, $key) use ($track) {
                    return $value->id != $track->id;
                });
                $this->create_game($first,$second[$j], $round->id, $track->id );
                $result = true;
                break;
            }
        }
        return $result;
    }

    private function create_game(&$team_i, &$team_j, $round_id, $track_id)
    {
        $game = Game::create([
            'name' => $team_i->name . ' - ' . $team_j->name,
            'round_id' => $round_id,
            'track_id' => $track_id
        ]);
        Member::create([
            'game_id' => $game->id,
            'team_id' => $team_i->id,
        ]);
        Member::create([
            'game_id' => $game->id,
            'team_id' => $team_j->id,
        ]);
        $team_i->closed = true;
        $team_j->closed = true;
    }

    private function was(int $team_id, int $group_id)
    {
        return Member::query()
            ->whereTeamId($team_id)
            ->whereIn('members.game_id', function ($q1) use ($group_id) {
                $q1->select('id')->from('games')->whereIn('round_id', function ($q2) use ($group_id) {
                    $q2->select('id')->from('rounds')->whereGroupId($group_id);
                });
            })
            ->selectRaw('(SELECT a.team_id FROM members a WHERE a.team_id != members.team_id AND a.game_id = members.game_id) as member_id')
            ->get()
            ->map(function ($value, $key) {
                return $value->member_id;
            });
    }

    private function used_tracks(Team $team, Group $group)
    {
        $games = collect(Game::query()
            ->join('members', 'games.id', '=', 'members.game_id')
            ->join('rounds', 'games.round_id', '=', 'rounds.id')
            ->where('members.team_id', '=', $team->id)
            ->where('rounds.group_id', '=', $group->id)
            ->select('games.track_id')
            ->get()
        );
        return $games->map(function($item, $key) {
                return $item->track_id;
            });
    }
}
