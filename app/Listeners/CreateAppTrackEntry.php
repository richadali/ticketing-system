<?php

namespace App\Listeners;

use App\Events\AdvertisementSaved;
use App\Models\AppTrack;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class CreateAppTrackEntry implements ShouldQueue
{
    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     *
     * @param  \App\Events\AdvertisementSaved  $event
     * @return void
     */
    public function handle(AdvertisementSaved $event)
    {
        $advertisement = $event->advertisement;

        $appTrack = new AppTrack();
        $appTrack->advertisement_id = $advertisement->id;
        // Set the values for next_user_id, process_date, and process_flag according to your requirements
        $appTrack->next_user_id = 1;
        $appTrack->process_date = now()->toDateString();
        $appTrack->process_flag = 'N';
        $appTrack->save();
    }
}
