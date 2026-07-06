<?php

namespace App\Services\Payments;

use App\Enums\PaymentStatus;
use App\Models\MembershipRenewal;
use App\Models\MemberWallet;
use App\Models\Payment;
use App\Models\PublicationPurchase;
use App\Models\WalletTransaction;
use App\Services\Receipts\ReceiptService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class PaymentService
{
    public function __construct(
        private readonly XpouchPaymentService $xpouch,
        private readonly ReceiptService $receipts,
    ) {
    }

    public function createXpouchPayment(Model $payable, string $purpose, float $amount, string $currency = 'NGN'): Payment
    {
        $member = $payable->member;

        return DB::transaction(function () use ($payable, $purpose, $amount, $currency, $member) {
            $payment = Payment::create([
                'member_id' => $member?->id,
                'payable_type' => $payable::class,
                'payable_id' => $payable->id,
                'purpose' => $purpose,
                'provider' => 'xpouch',
                'reference' => $this->nextReference(),
                'amount' => $amount,
                'currency' => $currency,
                'status' => PaymentStatus::Pending->value,
            ]);

            $response = $this->xpouch->initiate($payment, [
                'name' => trim(($member->first_name ?? '') . ' ' . ($member->last_name ?? '')),
                'email' => $member->email ?? null,
                'phone' => $member->phone_number ?? null,
            ]);

            $payment->update([
                'provider_reference' => $this->xpouch->extractProviderReference($response),
                'checkout_url' => $this->xpouch->extractCheckoutUrl($response),
                'provider_payload' => $response,
                'status' => PaymentStatus::Processing->value,
            ]);

            return $payment->fresh();
        });
    }

    public function createWalletPayment(Model $payable, string $purpose, float $amount, string $currency = 'NGN', ?WalletTransaction $walletTransaction = null): Payment
    {
        $member = $payable->member;

        $payment = Payment::create([
            'member_id' => $member?->id,
            'payable_type' => $payable::class,
            'payable_id' => $payable->id,
            'purpose' => $purpose,
            'provider' => 'wallet',
            'reference' => $this->nextReference(),
            'amount' => $amount,
            'currency' => $currency,
            'status' => PaymentStatus::Pending->value,
            'provider_payload' => [
                'wallet_transaction_id' => $walletTransaction?->id,
                'wallet_reference' => $walletTransaction?->reference,
            ],
        ]);

        if ($walletTransaction) {
            $walletTransaction->update(['payment_id' => $payment->id]);
        }

        return $this->markSuccessful($payment);
    }

    public function markSuccessful(Payment $payment, array $payload = []): Payment
    {
        return DB::transaction(function () use ($payment, $payload) {
            $payment->update([
                'status' => PaymentStatus::Successful->value,
                'provider_payload' => array_merge($payment->provider_payload ?? [], ['confirmation' => $payload]),
                'paid_at' => $payment->paid_at ?: now(),
            ]);

            $payable = $payment->payable;
            if ($payable instanceof WalletTransaction && $payable->type === 'deposit') {
                if ($payable->status !== PaymentStatus::Successful->value) {
                    $wallet = MemberWallet::whereKey($payable->member_wallet_id)->lockForUpdate()->firstOrFail();
                    $before = (float) $wallet->balance;
                    $after = $before + (float) $payable->amount;

                    $wallet->update(['balance' => $after]);
                    $payable->update([
                        'payment_id' => $payment->id,
                        'balance_before' => $before,
                        'balance_after' => $after,
                        'status' => PaymentStatus::Successful->value,
                        'completed_at' => now(),
                    ]);
                }
            } elseif ($payable instanceof MembershipRenewal) {
                $startsAt = now()->toDateString();
                $expiresAt = optional($payable->plan)->duration_months
                    ? now()->addMonths($payable->plan->duration_months)->toDateString()
                    : now()->addYear()->toDateString();

                $payable->update([
                    'status' => 'active',
                    'starts_at' => $payable->starts_at ?: $startsAt,
                    'expires_at' => $payable->expires_at ?: $expiresAt,
                    'paid_at' => $payable->paid_at ?: now(),
                ]);
            } elseif ($payable instanceof PublicationPurchase) {
                $payable->update([
                    'status' => PaymentStatus::Successful->value,
                    'paid_at' => $payable->paid_at ?: now(),
                ]);
            }

            $this->receipts->issueForPayment($payment);

            return $payment->fresh(['receipt']);
        });
    }

    public function markFailed(Payment $payment, array $payload = []): Payment
    {
        $payment->update([
            'status' => PaymentStatus::Failed->value,
            'provider_payload' => array_merge($payment->provider_payload ?? [], ['failure' => $payload]),
        ]);

        return $payment->fresh();
    }

    private function nextReference(): string
    {
        return 'NIMN-' . now()->format('YmdHis') . '-' . Str::upper(Str::random(8));
    }
}
