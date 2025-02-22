<?php
namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\Services;
use Illuminate\Http\Request;

class TransactionController extends Controller
{
    public function Transaction(Request $request)
    {
        $transactions = Services::with([
            'provider:id,full_name',
            'orders.user:id,full_name',
        ])->orderBy('created_at', 'desc')->paginate();

        if (!$transactions) {
            return response()->json(['status'=>false,'message'=>'Transaction data not found'],401);
        }

        return response()->json([
            'status'       => true,
            'transactions' => $transactions,
        ]);
    }

}
