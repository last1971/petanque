<?php


namespace App\Services;


use App\Event;
use Illuminate\Support\Facades\Auth;

class EventService
{
    /**
     * @param array $request
     * @return \Illuminate\Database\Eloquent\Builder|mixed
     */
    public function index(Array $request)
    {
        return Event::query()
            ->when(isset($request['see_own']) && $request['see_own'], function($query ){
                $query->whereUserId(Auth::user()->id);
            });
    }

    /**
     * @param array $data
     * @return \Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Eloquent\Model
     */
    public function store(Array $data)
    {
        return Event::query()->create($data);
    }

    /**
     * @param int $id
     * @return int
     */
    public function destroy(int $id)
    {
        return Event::destroy($id);
    }
}
