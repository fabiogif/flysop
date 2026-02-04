<?php

namespace App\Observers;

use App\Models\Occurrences;
use Illuminate\Support\Str;


class OccurrenceObserver
{
    /**
     * Handle the Occurrences "created" event.
     *
     * @param  \App\Models\Occurrences  $Occurrences
     * @return void
     */
    public function created(Occurrences $Occurrences)
    {
    //
    }

    /**
     * Handle the Occurrences "updated" event.
     *
     * @param  \App\Models\Occurrences  $Occurrences
     * @return void
     */
    public function updated(Occurrences $Occurrences)
    {
    //
    }

    /**
     * Handle the Occurrences "deleted" event.
     *
     * @param  \App\Models\Occurrences  $Occurrences
     * @return void
     */
    public function deleted(Occurrences $Occurrences)
    {
    //
    }

    /**
     * Handle the Occurrences "restored" event.
     *
     * @param  \App\Models\Occurrences  $Occurrences
     * @return void
     */
    public function restored(Occurrences $Occurrences)
    {
    //
    }

    /**
     * Handle the Occurrences "force deleted" event.
     *
     * @param  \App\Models\Occurrences  $Occurrences
     * @return void
     */
    public function forceDeleted(Occurrences $Occurrences)
    {
    //
    }
    /**
     * Handle the Occurrences "creating" event.
     *
     * @param  \App\Models\Models\Occurrences  $Occurrences
     * @return void
     */
    public function creating(Occurrences $Occurrences)
    {
    //$Occurrences->flag = Str::kebab($Occurrences->title);
    }


    /**
     * Handle the Plan "updating" event.
     *
     * @param  \App\Models\Models\Occurrences  $Occurrences
     * @return void
     */
    public function updating(Occurrences $Occurrences)
    {
    // $Occurrences->flag = Str::kebab($Occurrences->title);
    }
}
