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
            'message'     => 'required|string',
            'image'     => 'nullable',
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => false, 'message' => $validator->errors()], 422);
        }
        $new_name = null;
        if ($request->hasFile('image')) {
            $image     = $request->file('image');
            $extension = $image->getClientOriginalExtension();
            $new_name  = time() . '.' . $extension;
            $image->move(public_path('uploads/message_images'), $new_name);
        }

        $message = Message::create([
            'sender_id'   => auth()->id(),
            'receiver_id' => $request->receiver_id,
            'message'     => $request->message,
            'image'     => $new_name,
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


    //messagelist
    public function messageList(Request $request)
    {
        $user_id = Auth::id();
        $role    = $request->role;
        $search  = $request->search;

        // Fetch messages where the user is either sender or receiver
        $message_list = Message::with(['receiver:id,full_name,image', 'sender:id,full_name,image'])
            ->where(function ($query) use ($user_id) {
                $query->where('sender_id', $user_id)
                    ->orWhere('receiver_id', $user_id);
            });

            // return $request;
        // Filter by role
        if ($role) {
            $message_list->whereHas('receiver', function ($query) use ($role, $search) {
                $query->where('role', $role);
                if ($search) {
                    $query->where('full_name', 'like', '%' . $search . '%');
                }
            });
        }

        // Get latest messages and remove duplicates based on sender/receiver pair
        $message_list = $message_list->latest('created_at')->get()->unique(function ($msg) use ($user_id) {
            return $msg->sender_id === $user_id ? $msg->receiver_id : $msg->sender_id;
        })->values();

        return response()->json([
            'status'  => true,
            'message' => 'Messages Fetched successfully.',
            'data'    => $message_list,
        ]);
    }

}
