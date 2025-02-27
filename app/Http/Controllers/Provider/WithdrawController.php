<?php
namespace App\Http\Controllers\Provider;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\WithdrawMoney;
use App\Notifications\WithdrawMoneyNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Stripe\Balance;
use Stripe\Stripe;
use Stripe\Transfer;

class WithdrawController extends Controller
{
    //get provider balance in order
    public function getWithdrawMoney()
    {
        $provider = auth()->user();

        if ($provider->role !== 'provider') {
            return response()->json(['status' => false, 'message' => 'Only providers can view their balance.'], 403);
        }

        if (! $provider->stripe_connect_id) {
            return response()->json(['status' => false, 'message' => 'No Stripe account connected.'], 403);
        }

        Stripe::setApiKey(env('STRIPE_SECRET'));

        try {
            // Fetch actual balance from Stripe
            $stripeBalance    = Balance::retrieve(['stripe_account' => $provider->stripe_connect_id]);
            $availableBalance = $stripeBalance->available[0]->amount / 100; // Convert cents to dollars

            // Calculate total earnings from completed service orders (grouped by service title)
            $earningsPerService = DB::table('orders')
                ->join('services', 'services.id', '=', 'orders.service_id')
                ->where('orders.provider_id', $provider->id)
                ->where('orders.status', 'completed')
                ->select('services.title', DB::raw('SUM(orders.amount) as total_amount'))
                ->groupBy('services.title')
                ->get();

            // Ensure earningsPerService always returns valid values
            if ($earningsPerService->isEmpty()) {
                $earningsPerService = collect([
                    ['title' => 'No Completed Orders', 'total_amount' => 0.00],
                ]);
            }

            // Calculate total earnings
            $withdrawableAmount = $earningsPerService->sum('total_amount');

            // Calculate total withdrawn amount
            $totalWithdrawn = WithdrawMoney::where('provider_id', $provider->id)
                ->where('status', 'approved')
                ->sum('amount');

            // Calculate remaining withdrawable amount
            $remainingWithdrawable = $withdrawableAmount - $totalWithdrawn;

            return response()->json([
                'status'               => true,
                // 'available_balance' => number_format($availableBalance, 2, '.', ''),
                'available_balance'    => number_format($withdrawableAmount, 2, '.', ''),
                // 'remaining_withdrawable' => number_format($remainingWithdrawable, 2, '.', ''),
                'earnings_per_service' => $earningsPerService,
            ]);
        } catch (\Exception $e) {
            return response()->json(['status' => false, 'message' => 'Failed to fetch data.', 'error' => $e->getMessage()], 500);
        }
    }

    //request for money withdraw
    public function requestWithdraw(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'amount' => 'required|numeric|min:1',
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => false, 'message' => $validator->errors()], 422);
        }

        $provider = auth()->user();

        if ($provider->role !== 'provider') {
            return response()->json(['status' => false, 'message' => 'Only providers can request withdrawals.'], 403);
        }

        if (! $provider->stripe_connect_id) {
            return response()->json(['status' => false, 'message' => 'No Stripe account connected.'], 403);
        }

        // Fetch actual balance from Stripe
        Stripe::setApiKey(env('STRIPE_SECRET'));

        try {
            $stripeBalance    = Balance::retrieve(['stripe_account' => $provider->stripe_connect_id]);
            $availableBalance = $stripeBalance->available[0]->amount / 100; // Convert cents to dollars

            // if ($request->amount > $availableBalance) {
            //     return response()->json(['status' => false, 'message' => 'Insufficient balance in Stripe account.'], 401);
            // }
        } catch (\Exception $e) {
            return response()->json(['status' => false, 'message' => 'Failed to fetch Stripe balance.', 'error' => $e->getMessage()], 500);
        }

        // Create withdrawal request
        $withdraw_money = WithdrawMoney::create([
            'amount'      => $request->amount,
            'status'      => 'pending',
            'provider_id' => $provider->id,
        ]);
        // Dispatch notification to admin
        $super_admin = User::where('role', 'super_admin')->first(); // Assuming 'admin' is the role
        $super_admin->notify(new WithdrawMoneyNotification($withdraw_money, $provider));


        return response()->json([
            'status'  => true,
            'message' => 'Withdrawal request submitted with notification. Waiting for admin approval.',
            'data'    => $withdraw_money,
        ]);
    }
    //approved money withdraw by admin
    public function approveWithdraw($withdrawId)
    {
        Stripe::setApiKey(env('STRIPE_SECRET'));

        // Fetch the withdrawal request
        $withdraw_money = WithdrawMoney::find($withdrawId);
        if (! $withdraw_money) {
            return response()->json(['status' => false, 'message' => 'Withdrawal request not found.'], 422);
        }

        // Log the current status for debugging
        Log::info('Approval attempt for withdrawal', [
            'withdrawId' => $withdrawId,
            'status'     => $withdraw_money->status,
        ]);

        // Prevent approving already processed withdrawals
        if (trim(strtolower($withdraw_money->status)) !== 'pending') {
            return response()->json(['status' => false, 'message' => 'This withdrawal request has already been processed.'], 400);
        }

        $provider = User::find($withdraw_money->provider_id);
        if (! $provider || ! $provider->stripe_connect_id) {
            return response()->json(['status' => false, 'message' => 'Provider does not have a Stripe connected account.'], 400);
        }

        try {
            // Transfer money from admin to provider
            $transfer = Transfer::create([
                'amount'      => $withdraw_money->amount * 100, // Convert to cents
                'currency'    => 'usd',
                'destination' => $provider->stripe_connect_id,
                'description' => 'Withdrawal payment to provider',
            ]);

            $withdraw_money->update(['status' => 'approved']);

            return response()->json([
                'status'   => true,
                'message'  => 'Withdrawal approved and transferred successfully!',
                'transfer' => $transfer,
            ]);
        } catch (\Exception $e) {
            return response()->json(['status' => false, 'message' => $e->getMessage()], 500);
        }
    }
    //withdraw history
    public function withdrawHistory()
    {
        $provider = auth()->user();

        if ($provider->role !== 'provider') {
            return response()->json(['status' => false, 'message' => 'Only providers can view withdrawal history.'], 403);
        }

        $withdrawals = WithdrawMoney::with([
            'provider:id,full_name',
            'order.service:id,title,address',
        ])->orderBy('created_at', 'desc')->get();

        //recent withdrawals
        $recentThreshold = Carbon::now()->subHours(24);

        // Separate recent and previous
        $recentWithdrawals = $withdrawals->filter(function ($withdrawal) use ($recentThreshold) {
            return Carbon::parse($withdrawal->created_at)->greaterThanOrEqualTo($recentThreshold);
        });

        $previousWithdrawals = $withdrawals->filter(function ($withdrawal) use ($recentThreshold) {
            return Carbon::parse($withdrawal->created_at)->lessThan($recentThreshold);
        });

        return response()->json([
            'status'               => true,
            'recent_withdrawals'   => $recentWithdrawals->values(),
            'previous_withdrawals' => $previousWithdrawals->values(),
        ]);
    }

}
