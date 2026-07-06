<?php

namespace App\Http\Controllers\API\V1\Admin;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PaymentController extends Controller
{
    /**
     * List all payments.
     *
     * Returns all member payments with optional status and purpose filters.
     */
    public function index(Request $request): JsonResponse
    {
        $query = Payment::with(['member', 'receipt'])->latest();

        if ($request->filled('status')) {
            $query->where('status', $request->string('status'));
        }

        if ($request->filled('purpose')) {
            $query->where('purpose', $request->string('purpose'));
        }

        return response()->json(['success' => true, 'data' => $query->paginate(25)]);
    }

    /**
     * Get payment details.
     *
     * Returns a payment record with member, receipt, and payable item details.
     */
    public function show(Payment $payment): JsonResponse
    {
        return response()->json(['success' => true, 'data' => $payment->load(['member', 'receipt', 'payable'])]);
    }
}
