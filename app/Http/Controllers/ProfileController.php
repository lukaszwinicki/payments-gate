<?php

namespace App\Http\Controllers;

use App\Http\Requests\MerchantRequest;
use Illuminate\Http\JsonResponse;

class ProfileController extends Controller
{
    public function getApiCredentials(MerchantRequest $request): JsonResponse
    {
        $merchant = $request->merchant();

        return response()->json([
            'apiKey' => $merchant->api_key,
            'secretKey' => $merchant->secret_key
        ]);
    }
}