<?php
namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\OfferService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class OfferPriceController extends Controller
{
    //offer price
    public function offerPrice(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'service_id'   => 'required|integer|exists:services,id',
            'offer_price'  => 'required|string',
            'offer_status' => 'nullable|string|in:pending,accepted,canceled',
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => false, 'message' => $validator->errors()], 422);
        }

        $status = $request->offer_status ?? 'pending';

        $offer_price = OfferService::create([
            'user_id'      => auth()->id(),
            'service_id'   => $request->service_id,
            'offer_price'  => $request->offer_price,
            'offer_status' => $status,
        ]);

        return response()->json([
            'status'      => true,
            'message'     => 'Offer price submitted successfully.',
            'offer_price' => $offer_price,
        ], 201);
    }
    // Provider accepts or rejects an offer
    public function updateOfferStatus(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'offer_status' => 'required|string|in:accepted,canceled',
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => false, 'message' => $validator->errors()], 422);
        }

        $offer = OfferService::findOrFail($id);

        $offer->update(['offer_status' => $request->offer_status]);

        return response()->json([
            'status'  => true,
            'message' => "Offer status updated to {$request->offer_status}.",
            'offer'   => $offer,
        ]);
    }
    public function getOfferPrice()
    {
        $offer_price_list = OfferService::with(['user:id,full_name,image', 'service:id,title'])
            ->paginate();

        if ($offer_price_list->isEmpty()) {
            return response()->json(['status' => false, 'message' => 'There is no data in the offer price list'], 401);
        }

        return response()->json(['status' => true, 'data' => $offer_price_list], 200);
    }

}
