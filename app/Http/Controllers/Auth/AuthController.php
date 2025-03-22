<?php
namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Mail\VerifyOTP;
use App\Models\User;
use App\Notifications\NewProviderNotification;
use App\Notifications\NewUserNotification;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Tymon\JWTAuth\Facades\JWTAuth;

class AuthController extends Controller
{
    //get user profile
    public function ownProfile()
    {
        $user = Auth::user();

        if (! $user) {
            return response()->json(['status' => false, 'message' => 'User Not Found'], 422);
        }

        return response()->json(['status' => true, 'data' => $user]);
    }
    //get profile for provider
    public function providerProfile($id)
    {
        $user = Auth::user();
        if (! $user) {
            return response()->json(['status' => false, 'message' => 'User Not Found'], 422);
        }

        // Fetch the authenticated user with their associated service categories
        $userWithCategories = User::with('serviceCategories')->find($id);
        if (! $userWithCategories) {
            return response()->json(['status' => false, 'message' => 'User Not Found'], 422);
        }

        return response()->json(['status' => true, 'data' => $userWithCategories]);
    }

    //signup or registration
    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'full_name'            => 'required|string|max:255',
            'email'                => 'required|string|email|unique:users,email',
            'password'             => 'required|string|min:6',
            'address'              => 'nullable|string|max:255',
            'contact'              => 'nullable|string|max:15',
            'role'                 => 'nullable|string|in:super_admin,provider,user',
            'image'                => 'nullable|image',
            'provider_description' => 'nullable|string',
            'uaepass_id'           => 'nullable|string|unique:users,uaepass_id',
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => false, 'message' => $validator->errors()], 422);
        }

        // Determine role
        $isProviderDescriptionProvided = $request->filled('provider_description');

        if ($isProviderDescriptionProvided) {
            $role = 'provider';
        } else {
            $role = 'user';
        }

        $new_name = null;
        if ($request->hasFile('image')) {
            $image     = $request->file('image');
            $extension = $image->getClientOriginalExtension();
            $new_name  = time() . '.' . $extension;
            $image->move(public_path('uploads/profile_images'), $new_name);
        }

        $otp            = rand(100000, 999999);
        $otp_expires_at = now()->addMinutes(10);

        $user = User::create([
            'full_name'            => $request->full_name,
            'email'                => $request->email,
            'provider_description' => $role === 'provider' ? $request->provider_description : null,
            'uaepass_id'           => $request->uaepass_id,
            'address'              => $request->address,
            'contact'              => $request->contact,
            'password'             => Hash::make($request->password),
            'role'                 => $role,
            'image'                => $new_name,
            'otp'                  => $otp,
            'otp_expires_at'       => $otp_expires_at,
            'status'               => 'inactive',
        ]);

        // Notify Admin
        $admin = User::where('role', 'super_admin')->first();
        if ($admin) {
            if ($user->role === 'user') {
                $admin->notify(new NewUserNotification($user));
            } elseif ($user->role === 'provider') {
                $admin->notify(new NewProviderNotification($user));
            }
        }

        // Send OTP Email
        try {
            Mail::to($user->email)->send(new VerifyOTP($otp));
        } catch (Exception $e) {
            Log::error($e->getMessage());
        }

        $token = JWTAuth::fromUser($user);

        return response()->json([
            'status'       => true,
            'message'      => 'Registration successful. Please verify your email!',
            'access_token' => $token,
            'token_type'   => 'bearer',
        ], 200);
    }

    // verify email
    public function verify(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'otp' => 'required|numeric',
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => false, 'message' => $validator->errors()], 422);
        }
        $user = User::where('otp', $request->otp)->first();

        if ($user) {
            $user->otp               = null;
            $user->email_verified_at = now();
            $user->status            = 'active';
            $user->save();

            $token = JWTAuth::fromUser($user);

            return response()->json([
                'status'  => true,
                'message' => 'OTP verified successfully.',
                'access_token' => $token,
            ], 200);
        }

        return response()->json([
            'status' => 'error',
            'error'  => 'Invalid OTP.'], 401);
    }
    //login
    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email'    => 'required|email',
            'password' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => false, 'message' => $validator->errors()->first()], 422);
        }
        $credentials = $request->only('email', 'password');

        $user = User::where('email', $request->email)->first();
        if (! $user) {
            return response()->json(['status' => false, 'message' => 'Email or password is incorrect.'], 403);
        }

        if (! $token = Auth::guard('api')->attempt($credentials)) {
            return response()->json(['status' => false, 'message' => 'Email or password is incorrect.'], 401);
        }

        return response()->json([
            'status'           => true,
            'message'=>'Login Successfully',
            'access_token'     => $token,
            'token_type'       => 'bearer',
            'user_information' => $user
        ], 200);

    }
    //uae pass login
    public function socialLogin(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'full_name'  => 'required|string|max:255',
            'email'      => 'required|email|max:255',
            'uaepass_id' => 'required|string',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $existingUser = User::where('email', $request->email)->first();

        if ($existingUser) {
            if ($existingUser->uaepass_id === $request->uaepass_id) {
                // UAE Pass user already exists and matches
                $token = JWTAuth::fromUser($existingUser);
                return redirect()->route('user.dashboard')->with('success', 'Login successful!')->with('token', $token);
            } elseif (is_null($existingUser->uaepass_id)) {
                return redirect()->back()->withErrors(['email' => 'User already exists. Sign in manually.']);
            } else {
                // Update existing user with UAE Pass ID
                $existingUser->update([
                    'uaepass_id' => $request->uaepass_id,
                ]);
                $token = JWTAuth::fromUser($existingUser);
                return redirect()->route('user.dashboard')->with('success', 'Login successful!')->with('token', $token);
            }
        }

        // Create new user
        $user = User::create([
            'name'         => $request->name,
            'email'        => $request->email,
            'password'     => Hash::make(Str::random(16)),
            'role'         => 'provider',
            'uaepass_id'   => $request->uaepass_id,
            'address'      => $request->address ?? null,
            'verify_email' => true,
            'status'       => 'active',
        ]);

        $token = JWTAuth::fromUser($user);

        return response()->json([
            'access_token' => $token,
            'token_type'   => 'bearer',
            'name'         => $user->name,
            'email'        => $user->email,
            'role'         => $user->role,
        ]);
    }

    public function guard()
    {
        return Auth::guard('api');
    }
    // update profile
    public function updateProfile(Request $request)
    {
        $user = Auth::guard('api')->user();

        if (! $user) {
            return response()->json(['status' => 'error', 'message' => 'User not authenticated.'], 401);
        }

        $validator = Validator::make($request->all(), [
            'full_name'            => 'nullable|string|max:255',
            'provider_description' => 'nullable|string',
            'address'              => 'nullable|string|max:255',
            'contact'              => 'nullable|string|max:16',
            'password'             => 'nullable|string|min:6|confirmed',
            'image'                => 'nullable|file',
            'about_yourself'                => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => false, 'message' => $validator->errors()], 422);
        }

        $validatedData = $validator->validated();

        $user->full_name            = $validatedData['full_name'] ?? $user->full_name;
        $user->address              = $validatedData['address'] ?? $user->address;
        $user->contact              = $validatedData['contact'] ?? $user->contact;
        $user->provider_description = $validatedData['provider_description'] ?? $user->provider_description;
        $user->about_yourself = $validatedData['about_yourself'] ?? $user->about_yourself;

        if (! empty($validatedData['password'])) {
            $user->password = Hash::make($validatedData['password']);
        }

        if ($request->hasFile('image')) {
            $existingImage = $user->image;

            if ($existingImage) {
                $oldImage = parse_url($existingImage);
                $filePath = ltrim($oldImage['path'], '/');
                if (file_exists($filePath)) {
                    unlink($filePath); // Delete the existing image
                }
            }

            // Upload new image
            $image     = $request->file('image');
            $extension = $image->getClientOriginalExtension();
            $newName   = time() . '.' . $extension;
            $image->move(public_path('uploads/profile_images'), $newName);

            $user->image = $newName;
        }
        $user->save();

        return response()->json([
            'status'  => true,
            'message' => 'Profile updated successfully.',
            'data'    => $user,
        ], 200);

    }

    //change password
    public function changePassword(Request $request)
    {

        $request->validate([
            'current_password' => 'required|string|',
            'new_password' => 'required|string|min:6|confirmed',
        ]);

        $user = Auth::user();

        if (! $user) {
            return response()->json(['status'=>false,'message' => 'User not authenticated.'], 401);
        }
        if (!Hash::check($request->current_password, $user->password)) {
            return response()->json(['status'=>false,'message' => 'Current password is incorrect.'],400);
        }
        $user->password = Hash::make($request->new_password);
        $user->save();

        return response()->json([
            'status'  => true,
            'message' => 'Password changed successfully']);
    }
    // forgote password
    public function forgotPassword(Request $request)
    {
        $request->validate(['email' => 'required|email']);

        $user = User::where('email', $request->email)->first();

        if (! $user) {
            return response()->json(['error' => 'Email not registered.'], 404);
        }
        $otp = rand(100000, 999999);

        DB::table('users')->updateOrInsert(
            ['email' => $request->email],
            ['otp' => $otp, 'created_at' => now()]
        );

        try {
            Mail::to($request->email)->send(new VerifyOTP($otp));
        } catch (Exception $e) {
            Log::error($e->getMessage());
            return response()->json(['error' => 'Failed to send OTP.'], 500);
        }

        return response()->json([
            'status'  => true,
            'message' => 'OTP sent to your email.'], 200);
    }

    // reset password
    public function resetPassword(Request $request)
    {
        // return $request;
        $request->validate([
            'email'    => 'required|email',
            'password' => 'required|string|min:6|confirmed',
        ]);

        $user = User::where('email', $request->email)->first();
        if (! $user) {
            return response()->json(['status'=>false,'message' => 'User not found.'], 200);
        }

        $user->password = bcrypt($request->password);
        $user->save();

        return response()->json([
            'status'  => true,
            'message' => 'Password reset successful.'], 200);
    }

    //resend otp
    public function resendOtp(Request $request)
    {
        $request->validate(['email' => 'required|email']);

        $user = User::where('email', $request->email)->first();

        if (! $user) {
            return response()->json(['error' => 'Email not registered.'], 401);
        }

        $otp = rand(100000, 999999);

        DB::table('users')->updateOrInsert(
            ['email' => $request->email],
            ['otp' => $otp, 'created_at' => now()]
        );

        try {
            Mail::to($request->email)->send(new VerifyOTP($otp));
        } catch (Exception $e) {
            Log::error($e->getMessage());
            return response()->json(['error' => 'Failed to resend OTP.'], 500);
        }

        return response()->json([
            'status'  => true,
            'message' => 'OTP resent to your email.'], 200);
    }
    //logout
    public function logout()
    {
        if (! auth('api')->check()) {
            return response()->json([
                'status'  => 'error',
                'message' => 'User is not authenticated.',
            ], 401);
        }

        auth('api')->logout();

        return response()->json([
            'status'  => true,
            'message' => 'Successfully logged out.',
        ]);
    }

}
