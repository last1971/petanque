<?php


namespace App\Services;


use App\Event;
use Illuminate\Support\Facades\Auth;

class EventService
{
    public function index(Array $request)
    {
        return Event::query()
            ->when(isset($request['see_own']) && $request['see_own'], function($query ){
                $query->whereUserId(Auth::user()->id);
            });
    }

    public function store(Array $data)
    {
        return Event::query()->create($data);
    }

    public function destroy(int $id)
    {
        return Event::destroy($id);
    }
}
