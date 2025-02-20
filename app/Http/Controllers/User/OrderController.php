<?php
namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Services;
use App\Models\User;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Stripe\PaymentIntent;
use Stripe\Stripe;

class OrderController extends Controller
{
    //create payment intent
    public function createPaymentIntent(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'amount'         => 'required|numeric|min:1',
            'payment_method' => 'required|string',

        ]);

        if ($validator->fails()) {
            return response()->json(['status' => false, 'message' => $validator->errors()], 400);
        }

        Stripe::setApiKey(env('STRIPE_SECRET'));

        try {
            $paymentIntent = PaymentIntent::create([
                'amount'         => $request->amount * 100,
                'currency'       => 'usd',
                'payment_method' => $request->payment_method,
                // 'confirmation_method' => 'manual',
                'confirm'        => false,
            ]);

            return response()->json([
                'status' => true,
                'data'   => $paymentIntent,
            ], 200);

        } catch (Exception $e) {
            Log::error($e->getMessage());
            return response()->json(['status' => false, 'message' => 'Error creating Payment Intent: ' . $e->getMessage()], 500);
        }
    }

    //order create for service
    public function paymentSuccess(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'payment_intent_id' => 'required|string',
            'user_id'           => 'required|exists:users,id',
            'service_id'        => 'required|exists:services,id',
            'start_date'        => 'required|date|after_or_equal:today',
            'end_date'          => 'required|date|after_or_equal:start_date',
            'start_time'        => 'required|date_format:H:i',
            'end_time'          => 'required|date_format:H:i|after:start_time',
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => false, 'message' => $validator->errors()], 400);
        }

        Stripe::setApiKey(env('STRIPE_SECRET'));

        try {
            $paymentIntent = PaymentIntent::retrieve($request->payment_intent_id);

            if ($paymentIntent->status === 'succeeded') {
                $service = Services::find($request->service_id);
                if (! $service) {
                    return response()->json(['status' => false, 'message' => 'Service not found.'], 401);
                }

                $user = User::find($request->user_id);
                if (! $user) {
                    return response()->json(['status' => false, 'message' => 'User not found.'], 401);
                }

                $order = Order::create([
                    'user_id'        => $request->user_id,
                    'provider_id'    => $service->provider_id,
                    'service_id'     => $request->service_id,
                    'transaction_id' => $paymentIntent->id,
                    'amount'         => $paymentIntent->amount / 100, // Convert cents to dollars
                    'status'         => 'completed',
                    'start_date'     => $request->start_date,
                    'end_date'       => $request->end_date,
                    'start_time'     => $request->start_time,
                    'end_time'       => $request->end_time,
                ]);

                $service->increment('booking_count');
                $service->save();

                return response()->json([
                    'status'  => true,
                    'message' => 'Payment recorded successfully',
                    'data'    => $order,
                ], 200);
            } else {
                return response()->json([
                    'status'  => false,
                    'message' => 'Payment failed or not yet confirmed. Status: ' . $paymentIntent->status,
                ], 400);
            }
        } catch (Exception $e) {
            Log::error($e->getMessage());
            return response()->json([
                'status'  => false,
                'message' => 'Payment recording failed: ' . $e->getMessage(),
            ], 500);
        }
    }
    //order list
    public function orderlist()
    {
        $order_list = Order::with(['user:id,full_name,image', 'service:id,title'])
            ->paginate();

        if ($order_list->isEmpty()) {
            return response()->json(['status' => false, 'message' => 'There is no data in the order list'], 401);
        }

        return response()->json(['status' => true, 'data' => $order_list], 200);
    }
}
