<?php

namespace App\Http\Controllers\Provider;

use Exception;
use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Models\ProviderService;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class ProviderController extends Controller
{
    public function addProviderService(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'category_id' => 'required|exists:service_categories,id',
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => false, 'message' => $validator->errors()], 422);
        }
        $already_exist=ProviderService::where('user_id',Auth::id())->where('service_category_id',$request->category_id)->exists();
        if($already_exist){
               return response()->json([
            'status' => true,
            'message' => 'This provider already have this service',
            'data' => null,
        ], 201);
        }
        $service = ProviderService::create([
            'user_id' => Auth::user()->id,
            'service_category_id' => $request->category_id
        ]);

        return response()->json([
            'status' => true,
            'message' => 'Service add to the provider successfully',
            'data' => $service,
        ], 201);
    }

    public function deleteProviderService($id)
    {
        try {
            $service = ProviderService::findOrFail($id);
            $service->delete();
            return response()->json([
                'status' => true,
                'message' => 'Service add to the provider is deleted successfully',
                'data' => $service,
            ], 201);

        } catch (Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Data not found.',
                'data' => null,
            ], 201);
        }
    }

    public function addupdateWorkingHour(Request $request)
    {
        $validated = $request->validate([
            'working_time_from' => 'nullable|date_format:h:i A',
            'working_time_to' => 'required_with:working_time_from|date_format:h:i A',
         ]);

        $user = Auth::user();
        $from = $request->working_time_from
            ? Carbon::createFromFormat('h:i A', $request->working_time_from)->format('H:i:s')
            : null;

        $to = $request->working_time_to
            ? Carbon::createFromFormat('h:i A', $request->working_time_to)->format('H:i:s')
            : null;

        $user->update([
            'working_time_from' => $from ?? $user->working_time_from,
            'working_time_to' => $to ?? $user->working_time_to,
            'days' => $request->days ?? $user->days,
        ]);

        return response()->json([
            'status' => true,
            'message' => 'Provider profile updated successfully.',
            'data' => $user,
        ], 201);
    }
}
