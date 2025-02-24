<?php
namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Stripe\Account;
use Stripe\AccountLink;
use Stripe\Stripe;

class ConnectedAccountController extends Controller
{
    //for connected account

    public function createAccount(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'type'  => 'required|in:express,standard,custom',
            'email' => 'required|email|exists:users,email',
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => false, 'message' => $validator->errors()], 422);
        }

        try {
            Stripe::setApiKey(env('STRIPE_SECRET'));

            $user = User::where('email', $request->email)->where('role', 'provider')->first();

            if (! $user) {
                return response()->json(['error' => 'Only providers can create connected accounts.'], 403);
            }

            // Create a connected account
            $account = Account::create([
                'type'         => 'express',
                'email'        => $request->email,
                'capabilities' => [
                    'card_payments' => ['requested' => true],
                    'transfers'     => ['requested' => true],
                ],
            ]);

            // Save the connected account ID
            $user->update(['stripe_connect_id' => $account->id]);

            $url = url("account-success?status=success&email={$user->email}&account_id={$account->id}");

            // Generate an onboarding link
            $accountLink = AccountLink::create([
                'account'     => $account->id,
                'refresh_url' => 'https://yourwebsite.com/reauth',
                'return_url'  => $url, // use the generated URL
                'type'        => 'account_onboarding',
            ]);

            return response()->json([
                'account_id'     => $account->id,
                'onboarding_url' => $accountLink->url,
            ]);
        } catch (\Stripe\Exception\ApiErrorException $e) {
            return response()->json(['error' => $e->getMessage()]);
        }
    }

    public function refreshAccount()
    {
        return redirect()->route('account-create');
    }

    public function successAccount()
    {
        return view('stripe.success');
    }
    //all account
    public function showAccount()
    {
        Stripe::setApiKey(env('STRIPE_SECRET'));

        // Get only providers who have a Stripe Connect ID
        $providers = User::where('role', 'provider')
            ->whereNotNull('stripe_connect_id')
            ->get();

        $accounts = [];

        foreach ($providers as $provider) {
            try {
                $account    = Account::retrieve($provider->stripe_connect_id);
                $accounts[] = $account;
            } catch (\Stripe\Exception\ApiErrorException $e) {
                return response()->json(['status' => false, 'error' => $e->getMessage()], 500);
            }
        }

        return response()->json(['status' => true, 'data' => $accounts]);
    }

    //delete
    public function deleteAccount($accountId)
    {
        try {
            Stripe::setApiKey(env('STRIPE_SECRET'));

            $account = Account::retrieve($accountId);
            $account->delete();

            // Optionally, update your local database to reflect the deletion
            User::where('stripe_connect_id', $accountId)->update(['stripe_connect_id' => null]);

            return response()->json(['status' => true, 'message' => 'Account deleted successfully.']);
        } catch (\Stripe\Exception\ApiErrorException $e) {
            return response()->json(['status' => false, 'error' => $e->getMessage()], 500);
        }
    }
    // public function deleteAccount($providerId)
    // {
    //     $user = User::where('id', $providerId)->where('role', 'provider')->first();

    //     if (! $user || ! $user->stripe_connect_id) {
    //         return response()->json(['error' => 'Provider does not have a connected account.'], 401);
    //     }

    //     try {
    //         $account = Account::retrieve($user->stripe_connect_id);
    //         $account->delete();

    //         $user->update(['stripe_connect_id' => null, 'completed_stripe_onboarding' => false]);

    //         return response()->json(['status' => true, 'message' => 'Connected account deleted successfully.']);
    //     } catch (\Exception $e) {
    //         return response()->json(['error' => $e->getMessage()], 500);
    //     }
    // }
}
