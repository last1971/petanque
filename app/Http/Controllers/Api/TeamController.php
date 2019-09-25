<?php

namespace App\Http\Controllers\Api;

use App\Event;
use App\Group;
use App\Services\TeamService;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class TeamController extends Controller
{

    protected $service;

    public function __construct()
    {
        $this->service = new TeamService();
    }

    /**
     * @param Request $request
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    public function index(Request $request)
    {
        //
        $teams = $this->service->rating($request->event_id)->paginate($request->per_page);
        foreach ($teams as $team) {
            $team->was_names = $this->service->was($team->id, $request->event_id);
            $team->mega_buhgolc = $teams->filter(function($value) use ($team) {
                return $team->was_names->contains($value->name);
            })->reduce(function($mega_buhgolc, $value) {
                return $mega_buhgolc + $value->buhgolc;
            });
        }
        return response()->json([
            'data' => $teams->sortByMulti([
                'winner' => 'DESC',
                'buhgolc' => 'DESC',
                'mega_buhgolc' => 'DESC',
                'points' => 'DESC'
            ])->values()
        ]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
        $team = $this->service->store($request->name);
        $event = Event::find($request->event_id);
        if (!$event->teams->contains($team)) {
            $event->teams()->attach($team->id);
        }
        return $team;
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
        $ids = explode(',', $id);
        $event = Event::find($ids[0]);
        $event->teams()->detach($ids[1]);
    }
}
