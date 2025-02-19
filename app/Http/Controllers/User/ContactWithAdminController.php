<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Mail;
use App\Mail\ContactFormSubmitted;
use App\Models\Contact;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ContactWithAdminController extends Controller
{
    public function contactMessage(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name'    => 'required|string|max:255',
            'email'   => 'required|email|max:255',
            'phone'   => 'required|string|max:15',
            'subject' => 'required|string|max:255',
            'message' => 'required|string',
        ]);
        if ($validator->fails()) {
            return response()->json(['status' => false, 'message' => $validator->errors()], 401);
        }

        $contact = Contact::create([
            'name'    => $request->name,
            'email'   => $request->email,
            'phone'   => $request->phone,
            'subject' => $request->subject,
            'message' => $request->message,
        ]);

        $superAdmin = User::where('role', 'super_admin')->first();
        if ($superAdmin) {
            // Send an email to the super admin
            Mail::to($superAdmin->email)->send(new ContactFormSubmitted($contact));
        }

        return response()->json(['status' => true, 'message' => 'Your message has been sent successfully.'], 200);
    }
}
