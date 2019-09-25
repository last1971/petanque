<?php


namespace App\Services;


use App\Event;
use App\Game;
use App\Member;
use App\Round;
use App\Track;

class RoundService
{
    /**
     * @param int $event_id
     * @return mixed
     */
    public function index(int $event_id)
    {
        //
        return Round::whereEventId($event_id)->orderBy('number');
    }

    /**
     * @param Event $event
     * @return mixed
     * @throws \Throwable
     */
    public function makeNextRound(Event $event)
    {
        throw_if(
            $event->teams()->count() - 1 == $event->rounds()->count(),
            new \Exception('Все уже наигрались')
        );
        $round = $event->rounds()->get()->last();
        if ($round) {
            $round = Round::create([
                'number' => $round->number + 1,
                'event_id' => $event->id
            ]);
        } else {
            $round = Round::create([
                'number' => 1,
                'event_id' => $event->id
            ]);
        }
        return $round;
    }

    /**
     * @param $id
     * @return string
     * @throws \Exception
     */
    public function destroy($id)
    {
        $round = Round::with('games')->find($id);
        Member::query()->whereIn(
            'game_id', $round->games->pluck('id'))->delete();
        $round->games()->delete();
        $round->delete();
        return 'Ok';
    }

    public function create($event_id)
    {
        $event = Event::find($event_id);
        $teams = (new TeamService())->rating($event_id)->get();
        $teams->each(function($value) use ($event_id, $teams) {
            $value->old_pairs = $this->was($value->id, $event_id);
            $value->mega_buhgolc = $teams->filter(function($val) use ($value) {
                return $value->old_pairs->contains($val->id);
            })->reduce(function($mega_buhgolc, $val) {
                return $mega_buhgolc + $val->buhgolc;
            });
        });
        $teams = $teams->sortByMulti([
            'winner' => 'DESC',
            'buhgolc' => 'DESC',
            'mega_buhgolc' => 'DESC',
            'points' => 'DESC'
        ])->values();
        $wins = $teams[0]->winner;
        $pools = collect();
        $add = null;
        while ($wins > -1) {
            $next = $teams->where('winner', '=', $wins);
            if ($next->count() > 0) {
                if (!$add && $next->count() == 1) {
                    $add = $next->values();
                } else {
                    $pool = new Pool($next, $wins);
                    if ($add) {
                        $pool->set_additional_teams($add);
                        $add = null;
                    }
                    $pools->push($pool);
                }
            }
            $wins--;
        }
        if ($add) {
            $pool = new Pool($add, $add[0]->winner);
            $pools->push($pool);
        }
        //логика
        $index = 0;
        $add = true;
        while ($index < $pools->count()) {
            $pool = $pools->get($index);
            //
            $t = $pool->teams->pluck('name');
            $s1 = $pool->sub1->pluck('name');
            $s2 = $pool->sub2_get()->pluck('name');
            //
            $add = $add ? $pool->pre_pairing() : $pool->next_variant();
            if (!$add) {
                if ($add === null) {
                    $index--;
                    if ($index< 0) {
                        $index = 0;
                        $add = $pool->teams;
                        $pools->shift();
                        $pools->get($index)->set_additional_teams($add);
                    }
                }
            } else {
                //
                $t = $pool->teams->pluck('name');
                $s1 = $pool->sub1->pluck('name');
                $s2 = $pool->sub2_get()->pluck('name');
                //
                if ($add->count() == 1) {
                    if ($index == $pools->count() - 1) {
                        if (!$add->first()->old_pairs->contains(null)) {
                            if ($add != $pool->teams) {
                                $pools->push(new Pool($add, 0));
                                $index = $index + 2;
                            } else {
                                $index++;
                            }
                        } else {
                            if ($add != $pool->teams) {
                                $add = false;
                            } else {
                                $pools->pop();
                                $index--;
                                $new_teams = $pools[$index]->teams->merge($add);
                                $pools->pop();
                                $pools->push(new Pool($new_teams, $add[0]->winner));
                            }
                        }
                    } else {
                        $index++;
                        $pools->get($index)->set_additional_teams($add);
                    }
                } else if ($add->count() > 1) {
                    $new_pool = new Pool($add, $pool->get_wins());
                    $index++;
                    if ($index < $pools->count() && $pool->get_wins() == $pools->get($index)->get_wins()) {
                        $pools->put($index, $new_pool);
                    } else {
                        $pools->splice($index, 0, [ $new_pool ]);
                    }
                } else {
                    $index++;
                }
            }
        }
        //
        $round = $this->makeNextRound($event);
        $tracks = Track::query()->take($event->teams()->count() / 2)->get();
        foreach ($pools as $pool) {
            if (!$pool->not_sub()) {
                foreach ($pool->get_pairs() as $pair) {
                    $first = $pair->first();
                    $last = $pair->last();
                    $ailibale_tracks = $tracks->whereNotIn('id', $this->used_tracks($first->id, $event_id));
                    $ailibale_tracks = $ailibale_tracks->whereNotIn('id', $this->used_tracks($last->id, $event_id));
                    $track = $ailibale_tracks->isEmpty() ? $tracks->pop() : $ailibale_tracks->first();
                    $tracks = $tracks->filter(function ($value) use ($track) {
                        return $value->id != $track->id;
                    });
                    $this->create_game($first, $last, $round->id, $track->id);
                }
            } else {
                $game = Game::create([
                    'name' => 'Без игры',
                    'round_id' => $round->id,
                ]);
                Member::create([
                    'game_id' => $game->id,
                    'team_id' => $pool->teams->first()->id,
                    'winner'  => true
                ]);
            }
        }
        return $round;
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
    }

    private function was(int $team_id, int $event_id)
    {
        return Member::query()
            ->whereTeamId($team_id)
            ->whereIn('members.game_id', function ($q1) use ($event_id) {
                $q1->select('id')->from('games')->whereIn('round_id', function ($q2) use ($event_id) {
                    $q2->select('id')->from('rounds')->whereEventId($event_id);
                });
            })
            ->selectRaw('(SELECT a.team_id FROM members a WHERE a.team_id != members.team_id AND a.game_id = members.game_id) as member_id')
            ->get()
            ->pluck('member_id');
    }

    private function used_tracks(int $team_id, int $event_id)
    {
        $games = collect(Game::query()
            ->join('members', 'games.id', '=', 'members.game_id')
            ->join('rounds', 'games.round_id', '=', 'rounds.id')
            ->where('members.team_id', '=', $team_id)
            ->where('rounds.event_id', '=', $event_id)
            ->select('games.track_id')
            ->get()
        );
        return $games->map(function($item) {
                return $item->track_id;
            });
    }
}
