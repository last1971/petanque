<?php


namespace App\Services;


use App\Event;
use App\Group;
use App\Team;
use Illuminate\Support\Facades\DB;
use mysql_xdevapi\Exception;
use phpDocumentor\Reflection\Types\Boolean;

class GroupService
{
    protected $alpa = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';

    public function index(Array $request)
    {
        return Group::query()
            ->when(!empty($request['event_id']), function ($query) use ($request) {
                $query->whereEventId($request['event_id']);
            })
            ->orderBy('name');
    }

    private function next($name)
    {
        $s = substr($name, -1);
        $n = strpos($this->alpa, $s);
        throw_if($n == 25, new \Exception('Больше нельзя создать группу'));
        return substr($this->alpa, $n + 1, 1);
    }

    public function store(int $event_id)
    {
        $group = Event::find($event_id)->groups()->get()->last();
        if ($group) {
            $group = Group::query()->create([
                'name' => 'Группа ' . $this->next($group->name),
                'event_id' => $event_id
            ]);
        } else {
            $group = Group::create([
                'name' => 'Группа A',
                'event_id' => $event_id
            ]);
        }
        return $group;
    }

    public function destroy(int $id)
    {
        return Group::destroy($id);
    }

    public function rating(int $id)
    {
        $query = Team::query()
            ->join('group_team', 'group_team.team_id', '=', 'teams.id')
            ->whereIn('teams.id', function($q) use ($id) {
                $q->select('team_id')->from('group_team')->whereGroupId($id);
            })
            ->where('group_team.group_id', $id)
            ->selectRaw(
                'teams.*, group_team.odd, group_team.id as rank,
                (SELECT SUM(diff) FROM members WHERE members.team_id = teams.id AND members.game_id IN (SELECT id FROM games WHERE round_id IN  (SELECT id FROM rounds WHERE group_id=?))) AS points,
                COALESCE((SELECT SUM(winner) FROM members WHERE members.team_id = teams.id AND members.game_id IN (SELECT id FROM games WHERE round_id IN  (SELECT id FROM rounds WHERE group_id=?))), 0) AS winner,
                (SELECT sum(winner) from members where members.game_id in ((SELECT id FROM games WHERE round_id IN  (SELECT id FROM rounds WHERE group_id=?))) and members.team_id in 
                (SELECT a.team_id FROM members a where a.game_id in (select game_id from members where members.team_id = teams.id AND members.game_id IN (SELECT id FROM games WHERE round_id IN  (SELECT id FROM rounds WHERE group_id=?))) AND members.team_id != teams.id)) as buhgolc',
                [$id, $id, $id, $id]

            )
            //(SELECT SUM(points) FROM members WHERE members.team_id = teams.id AND members.game_id IN (SELECT id FROM games WHERE round_id IN  (SELECT id FROM rounds WHERE group_id=?)))
            ->orderByDesc('winner')
            ->orderByDesc('buhgolc')
            ->orderByDesc('points')
            ->orderByDesc('rank');

        return $query;
    }
}
