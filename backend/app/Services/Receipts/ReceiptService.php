<?php

namespace App\Services\Receipts;

use App\Models\Payment;
use App\Models\Receipt;
use Illuminate\Support\Str;

class ReceiptService
{
    public function issueForPayment(Payment $payment): Receipt
    {
        if ($payment->receipt) {
            return $payment->receipt;
        }

        return Receipt::create([
            'payment_id' => $payment->id,
            'member_id' => $payment->member_id,
            'receipt_number' => $this->nextReceiptNumber(),
            'amount' => $payment->amount,
            'currency' => $payment->currency,
            'issued_at' => now(),
        ]);
    }

    private function nextReceiptNumber(): string
    {
        return 'NIMN-RCPT-' . now()->format('Ymd') . '-' . Str::upper(Str::random(8));
    }
}
