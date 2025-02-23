<?php
namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\Request;

class TransactionController extends Controller
{
    public function TransactionforProvider()
    {
        $order_list = Order::with([
            'user:id,full_name,image', 'service:id,title,address', 'provider:id,full_name',
        ])->where('provider_id', auth()->id())->paginate();

        if ($order_list->isEmpty()) {
            return response()->json(['status' => false, 'message' => 'There is no data in the order list'], 502);
        }

        $formattedOrders = $order_list->map(function ($order) {
            $platformFee = $order->platform_fee;
            $totalAmount = $order->amount + $platformFee; // Calculate the total amount

            return [
                'service_title'      => $order->service->title,
                'address'            => $order->service->address,
                'user_name'          => $order->user->full_name,
                'date'               => date('d/m/Y', strtotime($order->start_date)),
                'time'               => date('h:i a', strtotime($order->start_time)),
                'price'              => '$' . number_format($order->amount, 2),
                'platform_fee'       => '$' . number_format($platformFee, 2),
                'platform_fee_count' => '-8% ($' . number_format($platformFee, 2) . ')',
                'total_amount'       => '$' . number_format($totalAmount, 2),
            ];
        });

        // Return the formatted orders with pagination
        return response()->json([
            'status'     => true,
            'data'       => $formattedOrders->values(), // Reset keys
            'pagination' => [
                'current_page' => $order_list->currentPage(),
                'last_page'    => $order_list->lastPage(),
                'per_page'     => $order_list->perPage(),
                'total'        => $order_list->total(),
            ],
        ], 200);
    }

    public function Transaction()
    {
        $order_list = Order::with([
            'user:id,full_name,image', 'service:id,title,image', 'provider:id,full_name',
        ])->where('provider_id', auth()->id())->paginate();

        if ($order_list->isEmpty()) {
            return response()->json(['status' => false, 'message' => 'There is no data in the order list'], 502);
        }

        $formattedOrders = $order_list->map(function ($order) {
            $platformFee = $order->platform_fee;
            $totalAmount = $order->amount + $platformFee; // Calculate the total amount

            return [
                'service_title'      => $order->service->title,
                'image'            => $order->service->image,
                'user_name'          => $order->user->full_name,
                'provider'          => $order->provider->full_name,
                'price'              => '$' . number_format($order->amount, 2),
                'platform_fee'       => '$' . number_format($platformFee, 2),
                'platform_fee_count' => '+8% ($' . number_format($platformFee, 2) . ')',
                'total_amount'       => '$' . number_format($totalAmount, 2),
            ];
        });

        // Return the formatted orders with pagination
        return response()->json([
            'status'     => true,
            'data'       => $formattedOrders->values(), // Reset keys
            'pagination' => [
                'current_page' => $order_list->currentPage(),
                'last_page'    => $order_list->lastPage(),
                'per_page'     => $order_list->perPage(),
                'total'        => $order_list->total(),
            ],
        ], 200);
    }

}
