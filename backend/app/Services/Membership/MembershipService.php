<?php

namespace App\Services\Membership;

use App\Enums\MembershipStatus;
use App\Enums\PaymentPurpose;
use App\Models\MembershipPlan;
use App\Models\MembershipRenewal;
use App\Models\User;
use App\Services\Payments\PaymentService;
use App\Services\Wallet\WalletService;
use Illuminate\Support\Facades\DB;

class MembershipService
{
    public function __construct(
        private readonly PaymentService $payments,
        private readonly WalletService $wallets,
    )
    {
    }

    public function initiateRenewal(User $member, MembershipPlan $plan): array
    {
        return DB::transaction(function () use ($member, $plan) {
            $renewal = MembershipRenewal::create([
                'member_id' => $member->id,
                'membership_plan_id' => $plan->id,
                'amount' => $plan->amount,
                'currency' => $plan->currency,
                'status' => MembershipStatus::Pending->value,
            ]);

            $walletTransaction = $this->wallets->debit(
                $member,
                (float) $renewal->amount,
                PaymentPurpose::MembershipRenewal->value,
                'Membership renewal',
                ['renewal_id' => $renewal->id, 'membership_plan_id' => $plan->id],
            );

            $payment = $this->payments->createWalletPayment(
                $renewal,
                PaymentPurpose::MembershipRenewal->value,
                (float) $renewal->amount,
                $renewal->currency,
                $walletTransaction,
            );

            return [
                'renewal' => $renewal->fresh(['plan']),
                'payment' => $payment,
                'wallet_transaction' => $walletTransaction->fresh(),
            ];
        });
    }
}
