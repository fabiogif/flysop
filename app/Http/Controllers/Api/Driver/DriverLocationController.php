<?php

namespace App\Http\Controllers\Api\Driver;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\DriverPosition;
use App\Events\DriverPositionUpdated;
use Carbon\Carbon;

class DriverLocationController extends Controller
{
    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        $request->validate([
            'driver_id' => 'required|integer|exists:drivers,id',
            'occurrence_id' => 'nullable|integer|exists:occurrences,id',
            'latitude' => 'required|numeric',
            'longitude' => 'required|numeric',
        ]);


        $position = new DriverPosition();
        $position->driver_id = $request->input('driver_id');
        $position->occurrence_id = $request->input('occurrence_id');
        $position->latitude = $request->input('latitude');
        $position->longitude = $request->input('longitude');
        $position->created_at = Carbon::now();
        $position->save();

        if ($position->occurrence_id) {
            event(new DriverPositionUpdated($position));
        }

        return response()->json([
            'message' => 'Position updated successfully',
            'position' => $position
        ], 201);
    }
}
