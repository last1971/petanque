<?php

namespace App\Http\Controllers\Api;

use App\Event;
use App\Services\TrackService;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class TrackController extends Controller
{
    protected $service;

    public function __construct()
    {
        $this->service = new TrackService();
    }
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        //
        if ($request->event_id) {
            return Event::find($request->event_id)->tracks()->paginate();
        }
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
        return $this->service->store($request->event_id);
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
        $event = Event::find($id);
        $track = $event->tracks()->get()->last();
        $event->tracks()->detach($track->id);
    }
}
