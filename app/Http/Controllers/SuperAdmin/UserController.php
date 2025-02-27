<?php
namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;

class UserController extends Controller
{
    //user list with filter in super admin dashboard
    public function userList(Request $request)
    {
        $users = User::where('role', 'user')
            ->when($request->name, fn($q) => $q->where('full_name', 'like', "%{$request->name}%"))
            ->when($request->email, fn($q) => $q->where('email', 'like', "%{$request->email}%"))
            ->when($request->user_id, fn($q) => $q->where('id', $request->user_id))
            ->get();

        return response()->json([
            'status'  => $users->isNotEmpty(),
            'message' => $users->isNotEmpty() ? 'User list fetched successfully!' : 'No users found',
            'data'    => $users,
        ], 200);
    }
    //user details in super admin dashboard
    public function userDetails($id)
    {
        $user = User::where('role', 'user')->withCount('services')->find($id);

        return response()->json([
            'status'  => (bool) $user,
            'message' => $user ? 'User found' : 'No user found',
            'data'    => $user ?? (object) [],
        ], 200);
    }
//user delete

    public function userDelete($id)
    {
        $user = User::where('role', 'user')->find($id);

        if (! $user) {
            return response()->json(['status' => false, 'message' => 'User Not Found'], 200);
        }

        $user->delete();

        return response()->json([
            'status'  => true,
            'message' => 'User deleted successfully',
        ], 200);
    }

    //provider list
    public function providerList(Request $request)
    {
        $providers = User::where('role', 'provider')
            ->when($request->name, fn($q) => $q->where('full_name', 'like', "%{$request->name}%"))
            ->when($request->email, fn($q) => $q->where('email', 'like', "%{$request->email}%"))
            ->when($request->user_id, fn($q) => $q->where('id', $request->user_id))
            ->get();

        return response()->json([
            'status'  => $providers->isNotEmpty(),
            'message' => $providers->isNotEmpty() ? 'Provider list fetched successfully!' : 'No providers found',
            'data'    => $providers,
        ], 200);
    }

    //provider details in super admin dashboard
    public function providerDetails($id)
    {
        $provider = User::where('role', 'provider')->withCount('services')->find($id);

        return response()->json([
            'status'  => (bool) $provider,
            'message' => $provider ? 'Provider found' : 'No provider found',
            'data'    => $user ?? (object) [],
        ], 200);
    }
    //provider delete
    public function providerDelete($id)
    {
        $provider = User::where('role', 'provider')->find($id);

        if (! $provider) {
            return response()->json(['status' => false, 'message' => 'Provider Not Found'], 200);
        }

        $provider->delete();

        return response()->json([
            'status'  => true,
            'message' => 'Provider deleted successfully',
        ], 200);
    }

}
