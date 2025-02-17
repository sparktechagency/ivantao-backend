<?php
namespace App\Http\Controllers\Message;

use App\Http\Controllers\Controller;
use App\Models\Message;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class MessageController extends Controller
{
    //message send
    public function sendMessage(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'receiver_id' => 'required|exists:users,id',
            'content'     => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => false, 'message' => $validator->errors()], 422);
        }

        $message = Message::create([
            'sender_id'   => auth()->id(),
            'receiver_id' => $request->receiver_id,
            'content'     => $request->content,
            'is_read'     => false,
        ]);

        return response()->json([
            'status'  => true,
            'message' => 'Message sent successfully!!!',
            'data'    => $message,
        ], 200);
    }

    //get message receiver from sender
    public function getMessage(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'receiver_id' => 'required|exists:users,id',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 422);
        }

        $sender_id   = Auth::id();
        $receiver_id = $request->receiver_id;

        // Fetch messages only between the authenticated user and the receiver
        $messages = Message::where(function ($query) use ($sender_id, $receiver_id) {

            $query->where('sender_id', $sender_id)->where('receiver_id', $receiver_id);

        })->orWhere(function ($query) use ($sender_id, $receiver_id) {

            $query->where('sender_id', $receiver_id)->where('receiver_id', $sender_id);

        })->orderBy('created_at', 'asc')->paginate();

        return response()->json(['status' => true, 'data' => $messages], 200);
    }
    //read message for receiver
    public function readMessage(Request $request)
    {
        $sender_id   = $request->sender_id;
        $receiver_id = Auth::id();

        // Ensure the authenticated user is the receiver
        if ($sender_id == $receiver_id) {
            return response(['status' => false, 'message' => 'You cannot mark your own sent messages as read.'], 401);
        }

        $message = Message::where('sender_id', $sender_id)
            ->where('receiver_id', $receiver_id)
            ->where('is_read', 0)
            ->update(['is_read' => 1]);

        if ($message) {
            return response(['status' => true, 'message' => 'Message read successfully']);
        }

        return response(['status' => false, 'message' => 'No unread messages found.'], 422);
    }

    //serach user based on name
    public function searchUser(Request $request)
    {
        $users = User::where('full_name', 'like', '%' . $request->search . '%')->get();

        if ($users->isEmpty()) {
            return response()->json(['status' => false, 'message' => 'No users found matching the search criteria.']);
        }

        return response()->json([
            'status'  => true,
            'message' => 'Users found',
            'data'    => $users,
        ]);
    }
    

}
