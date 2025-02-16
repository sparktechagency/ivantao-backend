<?php
namespace App\Http\Controllers\Message;

use App\Http\Controllers\Controller;
use App\Models\Message;
use Illuminate\Http\Request;
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
            'serder_id'   => auth()->id(),
            'receiver_id' => $request->receiver_id,
            'content'     => $request->content,
            'is_read'     => false,
        ]);

        return response()->json(['status' => true, 'message' => 'Message sent successfully!', 'data' => $message], 200);
    }
    public function getMessage()
    {
        
    }

}
