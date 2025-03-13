<?php
namespace App\Http\Controllers\Provider;

use App\Http\Controllers\Controller;
use App\Models\Schedule;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class ScheduleController extends Controller
{
    public function addSchedule(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'days'       => 'nullable|array',
            'start_time' => 'nullable|date_format:H:i',
            'end_time'   => 'nullable|date_format:H:i|after:start_time',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $user = Auth::user();
        $type = $request->has('days') ? 'weekly' : 'daily';

        // Delete existing schedule for the user
        $schedule = Schedule::where('provider_id', $user->id)->delete();

        // Create the new schedule
        $schedule = Schedule::create([
            'provider_id' => $user->id,
            'type'        => $type,
            'days'        => $request->days ? json_encode($request->days) : null,
            'start_time'  => $request->start_time,
            'end_time'    => $request->end_time,
        ]);

        return response()->json([
            'status'  => true,'message' => 'Schedule saved successfully','data'    => $schedule], 201
        );
    }
    // Update an existing schedule
    public function updateSchedule(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'days'       => 'nullable|array',
            'start_time' => 'nullable|date_format:H:i',
            'end_time'   => 'nullable|date_format:H:i|after:start_time',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $user = Auth::user();

        // Find schedule by ID and ensure it belongs to the logged-in provider
        $schedule = Schedule::where('provider_id', $user->id)->where('id', $id)->first();

        if (! $schedule) {
            return response()->json(['status'  => false,'message' => 'Schedule not found',], 422);
        }

        $type = $request->has('days') ? 'weekly' : 'daily';

        $schedule->update([
            'type'       => $type,
            'days'       => $request->days ? json_encode($request->days) : null,
            'start_time' => $request->start_time,
            'end_time'   => $request->end_time,
        ]);

        return response()->json([
            'status'  => true,
            'message' => 'Schedule updated successfully',
            'data'    => $schedule,
        ]);
    }


    public function getSchedule()
    {
        $user      = Auth::user();
        $schedules = Schedule::where('provider_id', $user->id)->get();

        // Decode days array from JSON
        $schedules->each(function ($schedule) {
            $schedule->days = $schedule->days ? json_decode($schedule->days) : null;
        });

        return response()->json(['satus'=>true,'schedules' => $schedules]);
    }
}
