<?php

namespace App\Http\Controllers\API\V1\Member;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ReceiptController extends Controller
{
    /**
     * List current member receipts.
     *
     * Returns digital receipts generated for successful member payments.
     */
    public function index(Request $request): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => $request->user()->receipts()->with('payment')->latest()->paginate(20),
        ]);
    }
}
