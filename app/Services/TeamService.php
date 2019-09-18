<?php


namespace App\Services;


use App\Group;
use App\Member;
use App\Team;

class TeamService
{
    public function index(Array $request)
    {
        if (isset($request['renumber'])) {
            $this->renumber($request['group_id']);
        }
        If (isset($request['group_id'])) {
            $s = new GroupService();
            return $s->rating($request['group_id']);
        }
        return Team::query()
            /*->when(isset($request['group_id']), function($query) use ($request) {
                $query->whereIn('id', function ($q) use ($request) {
                    $q->select('team_id')->from('group_team')->whereGroupId($request['group_id']);
                });
            })*/
            ->when(isset($request['not_group_id']), function($query) use ($request) {
                $query->whereNotIn('id', function ($q) use ($request) {
                    $q->select('team_id')->from('group_team')->whereGroupId($request['group_id']);
                });
            });
    }

    public function store(string $name)
    {
        return Team::query()->firstOrCreate([
            'name' => $name
        ]);
    }

    public function renumber($id)
    {
        $group = Group::find($id);
        $i = 0;
        foreach ($group->teams as $team)
        {
            $group->teams()->updateExistingPivot($team->id, [ 'odd' => $i++ >= $group->teams->count() / 2 ]);
        }
    }

    public function was(int $team_id, int $group_id)
    {
        return Member::query()
            ->whereTeamId($team_id)
            ->whereIn('members.game_id', function ($q1) use ($group_id) {
                $q1->select('id')->from('games')->whereIn('round_id', function ($q2) use ($group_id) {
                    $q2->select('id')->from('rounds')->whereGroupId($group_id);
                });
            })
            ->selectRaw('(SELECT b.name FROM members a JOIN teams b ON b.id = a.team_id WHERE a.team_id != members.team_id AND a.game_id = members.game_id) as name')
            ->orderBy('name')
            ->get()
            ->pluck('name');
    }
}
