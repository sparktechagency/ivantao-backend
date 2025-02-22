<?php
namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Subscriber;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class SubscribeController extends Controller
{
    public function subscribeJoin(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|string|email|unique:subscribers,email',
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => false, 'message' => $validator->errors()], 422);
        }

        $subscribe = Subscriber::create([
            'email' => $request->email,
        ]);

        return response()->json([
            'status'  => true,
            'message' => 'Email submitted successfully.',
            'data'    => $subscribe,
        ], 201);
    }

    //report listing
    public function subscribeList()
    {
        $subscribe_list = Subscriber::paginate();

        if ($subscribe_list->isEmpty()) {
            return response()->json(['status' => false, 'message' => 'There are no subscribers available.'], 200);
        }

        return response()->json(['status' => true, 'data' => $subscribe_list], 200);
    }

    public function deleteSubscribe($id)
    {
        $subscribe = Subscriber::find($id);

        if (! $subscribe) {
            return response()->json(['status' => false, 'message' => 'Subscribe Not Found'], 401);
        }

        $subscribe->delete();

        return response()->json([
            'status'  => true,
            'message' => 'Subscriber deleted successfully']);
    }

}
