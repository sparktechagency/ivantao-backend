<?php
namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\User;

class UserController extends Controller
{
    //user list in super admin dashboard
    public function userList()
    {
        $userlist = User::where('role', 'user')->get();

        if (! $userlist) {
            return response()->json(['status' => false, 'message' => 'User Not Found'], 401);
        }

        return response()->json([
            'status'  => true,
            'message' => 'User list fetched successfully!',
            'data'    => $userlist,
        ], 200);
    }
    //user details in super admin dashboard
    public function userDetails($id)
    {
        $user = User::where('role', 'user')->withCount('services')->find($id);

        if (! $user) {
            return response()->json(['status' => false, 'message' => 'User Not Found'], 401);
        }

        return response()->json([
            'status' => true,
            'data'   => $user,
        ], 200);
    }
//user delete

    public function userDelete($id)
    {
        $user = User::where('role', 'user')->find($id);

        if (! $user) {
            return response()->json(['status' => false, 'message' => 'User Not Found'], 401);
        }

        $user->delete();

        return response()->json([
            'status'  => true,
            'message' => 'User deleted successfully',
        ], 200);
    }

    //provider list
    public function providerList()
    {
        $providerlist = User::where('role', 'provider')->get();

        if (! $providerlist) {
            return response()->json(['status' => false, 'message' => 'Provider Not Found'], 401);
        }

        return response()->json([
            'status'  => true,
            'message' => 'Provider list fetched successfully!',
            'data'    => $providerlist,
        ], 200);
    }
    //provider details in super admin dashboard
    public function providerDetails($id)
    {
        $user = User::where('role', 'provider')
        // ->withCount('servicesTaken')
            ->find($id);

        if (! $user) {
            return response()->json(['status' => false, 'message' => 'User Not Found'], 401);
        }

        return response()->json([
            'status' => true,
            'data'   => $user,
        ], 200);
    }
    //provider delete
    public function providerDelete($id)
    {
        $provider = User::where('role', 'provider')->find($id);

        if (! $provider) {
            return response()->json(['status' => false, 'message' => 'Provider Not Found'], 401);
        }

        $provider->delete();

        return response()->json([
            'status'  => true,
            'message' => 'Provider deleted successfully',
        ], 200);
    }

}
