<?php

namespace App\Services\Publications;

use App\Enums\PaymentPurpose;
use App\Enums\PaymentStatus;
use App\Models\Publication;
use App\Models\PublicationPurchase;
use App\Models\User;
use App\Services\Payments\PaymentService;
use App\Services\Wallet\WalletService;
use Illuminate\Support\Facades\DB;

class PublicationPurchaseService
{
    public function __construct(
        private readonly PaymentService $payments,
        private readonly WalletService $wallets,
    )
    {
    }

    public function purchase(User $member, Publication $publication): array
    {
        return DB::transaction(function () use ($member, $publication) {
            $purchase = PublicationPurchase::firstOrCreate(
                [
                    'member_id' => $member->id,
                    'publication_id' => $publication->id,
                ],
                [
                    'amount' => $publication->price,
                    'currency' => $publication->currency,
                    'status' => ((float) $publication->price) <= 0 ? PaymentStatus::Successful->value : PaymentStatus::Pending->value,
                    'paid_at' => ((float) $publication->price) <= 0 ? now() : null,
                ],
            );

            if ($purchase->status === PaymentStatus::Successful->value) {
                return ['purchase' => $purchase->fresh(['publication']), 'payment' => null];
            }

            $walletTransaction = $this->wallets->debit(
                $member,
                (float) $purchase->amount,
                PaymentPurpose::PublicationPurchase->value,
                'Publication purchase',
                ['publication_purchase_id' => $purchase->id, 'publication_id' => $publication->id],
            );

            $payment = $this->payments->createWalletPayment(
                $purchase,
                PaymentPurpose::PublicationPurchase->value,
                (float) $purchase->amount,
                $purchase->currency,
                $walletTransaction,
            );

            return [
                'purchase' => $purchase->fresh(['publication']),
                'payment' => $payment,
                'wallet_transaction' => $walletTransaction->fresh(),
            ];
        });
    }
}
