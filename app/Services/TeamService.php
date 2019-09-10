<?php


namespace App\Services;


use App\Group;
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
}
