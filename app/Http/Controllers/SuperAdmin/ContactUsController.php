<?php
namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\ContactUs;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ContactUsController extends Controller
{
    public function createContact(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'title'       => 'required|string|max:255',
            'description' => 'required|string',
            'image'       => 'nullable|image',
            'email'       => 'nullable|array',
            'location'    => 'nullable|string',
            'phone'       => 'nullable|array',
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => false, 'message' => $validator->errors()], 401);
        }

        // Find the existing contact record
        $contact = ContactUs::where('title', $request->title)->first();

        $newImage = null;

        if ($request->hasFile('image')) {
            if ($contact && $contact->image) {
                $oldImage = parse_url($contact->image);
                $filePath = ltrim($oldImage['path'], '/');
                if (file_exists(public_path($filePath))) {
                    unlink(public_path($filePath)); // Delete old image
                }
            }

            // Upload new image
            $image     = $request->file('image');
            $extension = $image->getClientOriginalExtension();
            $newImage  = time() . '.' . $extension;
            $image->move(public_path('uploads/contact_images'), $newImage);

        }
        // Update or create the contact
        $contact = ContactUs::updateOrCreate(
            ['title' => $request->title], // Find by title
            [
                'description' => $request->description,
                'image'       => $newImage ?? ($contact->image ?? null),
                'location'    => $request->location,
                'email'       => json_encode($request->email ?? null),
                'phone'       => json_encode($request->phone ?? null),
            ]
        );
        return response()->json([
            'status'  => true,
            'message' => 'Contact information saved successfully.',
            'data'    => $contact,
        ]);
    }
    public function contactShow()
    {
        $contacts = ContactUs::all();

        if ($contacts->isEmpty()) {
            return response()->json(['status' => false, 'message' => "Contact not found."], 401);
        }

        // Decode `email` and `phone` before returning response
        $contacts = $contacts->map(function ($contact) {
            $contact->email = json_decode($contact->email) ?? null;
            $contact->phone = json_decode($contact->phone) ?? null;
            return $contact;
        });

        return response()->json([
            'status'  => true,
            'message' => $contacts,
        ], 200);
    }

}
