<?php

namespace App\Http\Controllers\Provider;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\WithdrawMoney;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Stripe\Exception\ApiErrorException;
use Stripe\Stripe;
use Stripe\Transfer;

class WithdrawController extends Controller
{
    public function requestWithdraw(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'amount' => 'required|numeric|min:1',
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => false, 'message' => $validator->errors()], 422);
        }

        $provider = auth()->user();

        if ($provider->role !== 'provider' || !$provider->stripe_connect_id) {
            return response()->json(['status' => false, 'message' => 'Unauthorized or no connected account.'], 403);
        }

        // Check if the provider has enough balance
        if ($request->amount > $provider->balance) {
            return response()->json(['status' => false, 'message' => 'Insufficient balance.'], 401);
        }

        $withdraw_money = $provider->withdrawMoney()->create([
            'amount' => $request->amount,
            'status' => 'pending',
        ]);

        return response()->json([
            'status'  => true,
            'message' => 'Withdrawal request submitted. Waiting for admin approval.',
            'data' => $withdraw_money
        ]);
    }

    public function approveWithdraw(Request $request, $withdrawalId)
    {
        Stripe::setApiKey(env('STRIPE_SECRET'));

        $withdraw_money = WithdrawMoney::findOrFail($withdrawalId);

        if ($withdraw_money->status !== 'pending') {
            return response()->json(['status' => false, 'message' => 'Invalid request.'], 401);
        }

        $provider = User::find($withdraw_money->provider_id);

        if (!$provider || !$provider->stripe_connect_id) {
            return response()->json(['status' => false, 'message' => 'Provider does not have a Stripe connected account.'], 400);
        }

        try {
            // Transfer money from admin to provider
            $transfer = Transfer::create([
                'amount'      => $withdraw_money->amount * 100,
                'currency'    => 'usd',
                'destination' => $provider->stripe_connect_id,
                'description' => 'Payment for service provided',
            ]);

            // Update withdrawal request as completed
            $withdraw_money->update(['status' => 'approved']);

            return response()->json([
                'status'   => true,
                'message'  => 'Withdrawal approved and transferred successfully!',
                'transfer' => $transfer,
            ]);
        } catch (ApiErrorException $e) {
            return response()->json(['status' => false, 'message' => $e->getMessage()], 500);
        }
    }
}
