<?php

namespace App\Http\Controllers\Orders;

use App\Models\PromoCode;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Requests\PromoCodeRequest;

class PromoCodeController extends Controller
{
    //


    public function store(PromoCodeRequest $request)
    {

        $validatedData = $request->validated();
        $promoCode = PromoCode::create([
            'name' => $validatedData['name'],
            'value' => $validatedData['value'],

        ]);

        return response()->json($promoCode, 200);
    }

    public function delete($id)
    {
        $promoCode = PromoCode::find($id);

        if (!$promoCode) {
            return response()->json(['error' => 'Promo code not found'],200);
        }

        $promoCode->delete();

        return response()->json(['message' => 'Promo code deleted successfully'],200);
    }





}
