<?php

namespace App\Http\Controllers\API\V1\Webhook;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use App\Services\Payments\PaymentService;
use App\Services\Payments\XpouchPaymentService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class XpouchWebhookController extends Controller
{
    /**
     * Process xPouch payment webhook.
     *
     * Confirms successful xPouch payments, activates renewals or purchases, and generates digital receipts.
     */
    public function __invoke(Request $request, XpouchPaymentService $xpouch, PaymentService $payments): JsonResponse
    {
        $payload = $request->all();
        $reference = data_get($payload, 'data.reference')
            ?? data_get($payload, 'data.transaction_reference')
            ?? data_get($payload, 'reference')
            ?? data_get($payload, 'transaction_reference');

        if (! $reference) {
            Log::warning('xPouch webhook missing reference', ['payload' => $payload]);

            return response()->json(['success' => false, 'message' => 'Missing reference'], 400);
        }

        $payment = Payment::where('reference', $reference)
            ->orWhere('provider_reference', $reference)
            ->first();

        if (! $payment) {
            Log::warning('xPouch webhook payment not found', ['reference' => $reference, 'payload' => $payload]);

            return response()->json(['success' => false, 'message' => 'Payment not found'], 404);
        }

        if ($payment->status === 'successful') {
            return response()->json(['success' => true, 'message' => 'Payment already processed']);
        }

        if ($xpouch->isSuccessfulPayload($payload)) {
            $payments->markSuccessful($payment, $payload);

            return response()->json(['success' => true, 'message' => 'Payment confirmed']);
        }

        $payments->markFailed($payment, $payload);

        return response()->json(['success' => true, 'message' => 'Payment marked failed']);
    }
}
