<?php

namespace App\Http\Controllers\API\V1\Member;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PaymentController extends Controller
{
    /**
     * List current member payments.
     *
     * Returns the authenticated member's payment history with receipt information.
     */
    public function index(Request $request): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => $request->user()->payments()->with('receipt')->latest()->paginate(20),
        ]);
    }
}
