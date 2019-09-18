<?php

namespace App\Http\Controllers\Api;

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
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        //
        $teams = $this->service->index($request->all())->paginate($request->per_page);
        foreach ($teams as $team) {
            $team->was_names = $this->service->was($team->id, $request->group_id);
        }
        return $teams;
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
        $group = Group::find($request->group_id);
        if (!$group->teams->contains($team)) {
            $group->teams()->attach($team->id);
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
        $group = Group::find($ids[0]);
        $group->teams()->detach($ids[1]);
    }
}
