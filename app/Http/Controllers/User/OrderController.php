<?php
namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Fees;
use App\Models\Order;
use App\Models\Services;
use App\Models\User;
use App\Notifications\NewOrderNotification;
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
            'service_id'     => 'required|exists:services,id',
            'payment_method' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => false, 'message' => $validator->errors()], 422);
        }

        Stripe::setApiKey(env('STRIPE_SECRET'));

        try {
            $service = Services::find($request->service_id);
            if (! $service) {
                return response()->json(['status' => false, 'message' => 'Service not found.'], 401);
            }

            $amount = $service->price;

            // Get the platform fee percentage from the Fees table
            $fee = Fees::first();
            if (! $fee) {
                return response()->json(['status' => false, 'message' => 'Platform fee not set.'], 500);
            }

            // Calculate platform fee amount
            $platformFee = ($amount * $fee->platform_fee) / 100;
            $totalAmount = $amount + $platformFee; // Total charge

            // Convert to cents for Stripe
            $totalAmountInCents = $totalAmount * 100;

            // Create the Payment Intent
            $paymentIntent = PaymentIntent::create([
                'amount'         => $totalAmountInCents,
                'currency'       => 'usd',
                'payment_method' => $request->payment_method,
                'confirm'        => false,
            ]);

            return response()->json([
                'status' => true,
                'data'   => $paymentIntent,
                'cost'   =>
                [
                    'payment_intent' => $paymentIntent->id,
                    'amount'         => $amount,
                    'platform_fee'   => $platformFee,
                    'total'          => $totalAmount,
                ],
            ], 200);

        } catch (Exception $e) {
            Log::error($e->getMessage());
            return response()->json(['status' => false, 'message' => 'Error creating Payment Intent: ' . $e->getMessage()], 500);
        }
    }

    // Order creation for service after payment success
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
            // Retrieve the payment intent from Stripe
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

                $amount = $service->price;

                // Retrieve the platform fee from the Fee table
                $fee = Fees::first();
                if (! $fee) {
                    return response()->json(['status' => false, 'message' => 'Platform fee not set.'], 500);
                }

                $platformFeePercentage = $fee->platform_fee;
                $platformFee           = ($amount * $platformFeePercentage) / 100;
                $finalAmount           = $amount - $platformFee;

                // Create the order with the correct pricing
                $order = Order::create([
                    'user_id'        => $request->user_id,
                    'provider_id'    => $service->provider_id,
                    'service_id'     => $request->service_id,
                    'transaction_id' => $paymentIntent->id,
                    'amount'         => $finalAmount,
                    'platform_fee'   => $platformFee,
                    'status'         => 'completed',
                    'start_date'     => $request->start_date,
                    'end_date'       => $request->end_date,
                    'start_time'     => $request->start_time,
                    'end_time'       => $request->end_time,
                ]);

                // Increment the service's booking count
                $service->increment('booking_count');
                $service->save();

                $provider = $service->provider;
                $provider->notify(new NewOrderNotification($order));

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
        $order_list = Order::with(['user:id,full_name,image', 'service:id,title', 'provider:id,full_name'])
            ->paginate();

        if ($order_list->isEmpty()) {
            return response()->json(['status' => false, 'message' => 'There is no data in the order list'], 401);
        }

        return response()->json(['status' => true, 'data' => $order_list], 200);
    }
    //order details
    public function orderDetails($id)
    {
        $order = Order::with(['user:id,full_name', 'service:id,title', 'provider:id,name'])->find($id);

        if (! $order) {
            return response()->json(['status' => false, 'message' => 'Order not found.'], 401);
        }

        $providerServices = $order->provider->services()->pluck('title')->toArray();

        return response()->json([
            'status' => true,
            'data'   => [
                'order_id'               => '#' . $order->id,
                'time'                   => date('H:i A', strtotime($order->start_time)),
                'date'                   => date('d-m-Y', strtotime($order->start_date)),
                'service'                => $order->service->title,
                'cost'                   => '$' . $order->amount,
                'fee'                    => '$' . $order->platform_fee,
                'total'                  => '$' . ($order->amount + $order->platform_fee),
                'provider_id'            => $order->provider->id,
                'provider_name'          => $order->provider->name,
                'provider_services'      => $providerServices,
                'service_provided_count' => count($providerServices) . '+',
            ],
        ], 200);
    }
    //order history for user
    public function orderlistUser()
    {
        $user = auth()->user();

        $order_list = Order::with(['service:id,title', 'provider:id,full_name'])
            ->where('user_id', $user->id) // Fetch only orders of the logged-in user
            ->orderBy('created_at', 'desc')
            ->paginate();

        if ($order_list->isEmpty()) {
            return response()->json(['status' => false, 'message' => 'No orders found'], 404);
        }

        return response()->json(['status' => true, 'data' => $order_list], 200);
    }

}
