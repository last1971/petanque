<?php


namespace App\Services;


use App\Group;
use App\Member;
use App\Team;

class TeamService
{
    public function rating(int $id)
    {
        $query = Team::query()
            ->join('event_team', 'event_team.team_id', '=', 'teams.id')
            ->whereIn('teams.id', function($q) use ($id) {
                $q->select('team_id')->from('event_team')->whereEventId($id);
            })
            ->where('event_team.event_id', $id)
            ->selectRaw(
                'teams.*, event_team.id as rank,
                (SELECT SUM(diff) FROM members WHERE members.team_id = teams.id AND members.game_id IN (SELECT id FROM games WHERE round_id IN (SELECT id FROM rounds WHERE event_id = ?))) AS points,
                COALESCE((SELECT SUM(winner) FROM members WHERE members.team_id = teams.id AND members.game_id IN (SELECT id FROM games WHERE round_id IN (SELECT id FROM rounds WHERE event_id = ?))), 0) AS winner,
                (SELECT sum(winner) from members where members.game_id in ((SELECT id FROM games WHERE round_id IN (SELECT id FROM rounds WHERE event_id = ?))) and members.team_id in 
                (SELECT a.team_id FROM members a where a.game_id in (select game_id from members where members.team_id = teams.id AND members.game_id IN (SELECT id FROM games WHERE round_id IN (SELECT id FROM rounds WHERE event_id = ?))) AND members.team_id != teams.id)) as buhgolc',
                [$id, $id, $id, $id]

            )
            ->orderByDesc('winner')
            ->orderByDesc('buhgolc')
            ->orderByDesc('points')
            ->orderByDesc('rank');
        return $query;
    }

    public function index(Array $request)
    {
        return Team::query();
    }

    public function store(string $name)
    {
        return Team::query()->firstOrCreate([
            'name' => $name
        ]);
    }

    public function was(int $team_id, int $event_id)
    {
        return Member::query()
            ->whereTeamId($team_id)
            ->whereIn('members.game_id', function ($q1) use ($event_id) {
                $q1->select('id')->from('games')->whereIn('round_id', function ($q2) use ($event_id) {
                    $q2->select('id')->from('rounds')->whereEventId($event_id);
                });
            })
            ->selectRaw('(SELECT b.name FROM members a JOIN teams b ON b.id = a.team_id WHERE a.team_id != members.team_id AND a.game_id = members.game_id) as name')
            ->orderBy('name')
            ->get()
            ->pluck('name');
    }
}
