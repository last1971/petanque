<?php


namespace App\Services;


use App\Event;
use App\Track;

class TrackService
{
    public function store(int $event_id)
    {
        $event = Event::find($event_id);
        $track = $event->tracks()->get()->last();
        if ($track) {
            throw_if($track->id == 32, new \Exception('Больше нельзя добавить'));
            $track = Track::find($track->id + 1);
            $event->tracks()->save($track);
        } else {
            $track = Track::find(1);
            $event->tracks()->save($track);
        }
        return $track;
    }

}
