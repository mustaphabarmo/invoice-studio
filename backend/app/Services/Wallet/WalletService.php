<?php

namespace App\Services\Wallet;

use App\Enums\PaymentPurpose;
use App\Enums\PaymentStatus;
use App\Models\MemberWallet;
use App\Models\User;
use App\Models\WalletTransaction;
use App\Services\Payments\PaymentService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use RuntimeException;

class WalletService
{
    public function __construct(private readonly PaymentService $payments)
    {
    }

    public function walletFor(User $member): MemberWallet
    {
        return MemberWallet::firstOrCreate(
            ['member_id' => $member->id],
            [
                'balance' => 0,
                'currency' => 'NGN',
                'account_name' => trim(($member->first_name ?? '') . ' ' . ($member->last_name ?? '')) ?: $member->email,
            ],
        );
    }

    public function initiateDeposit(User $member, float $amount, string $currency = 'NGN'): array
    {
        if ($amount <= 0) {
            throw new RuntimeException('Enter a valid deposit amount.');
        }

        return DB::transaction(function () use ($member, $amount, $currency) {
            $wallet = $this->walletFor($member);
            $transaction = WalletTransaction::create([
                'member_wallet_id' => $wallet->id,
                'member_id' => $member->id,
                'type' => 'deposit',
                'purpose' => PaymentPurpose::WalletDeposit->value,
                'reference' => $this->nextReference('WDEP'),
                'amount' => $amount,
                'balance_before' => $wallet->balance,
                'balance_after' => $wallet->balance,
                'currency' => $currency,
                'status' => PaymentStatus::Pending->value,
                'description' => 'Wallet deposit',
            ]);

            $payment = $this->payments->createXpouchPayment(
                $transaction,
                PaymentPurpose::WalletDeposit->value,
                $amount,
                $currency,
            );

            $transaction->update(['payment_id' => $payment->id]);

            return [
                'wallet' => $wallet->fresh(),
                'transaction' => $transaction->fresh(['payment']),
                'payment' => $payment,
            ];
        });
    }

    public function debit(User $member, float $amount, string $purpose, string $description, array $metadata = []): WalletTransaction
    {
        if ($amount <= 0) {
            throw new RuntimeException('Amount must be greater than zero.');
        }

        return DB::transaction(function () use ($member, $amount, $purpose, $description, $metadata) {
            $wallet = MemberWallet::where('member_id', $member->id)->lockForUpdate()->first()
                ?? $this->walletFor($member);
            $balance = (float) $wallet->balance;

            if ($balance < $amount) {
                throw new RuntimeException('Insufficient wallet balance. Fund your wallet and try again.');
            }

            $after = $balance - $amount;
            $wallet->update(['balance' => $after]);

            return WalletTransaction::create([
                'member_wallet_id' => $wallet->id,
                'member_id' => $member->id,
                'type' => 'debit',
                'purpose' => $purpose,
                'reference' => $this->nextReference('WDR'),
                'amount' => $amount,
                'balance_before' => $balance,
                'balance_after' => $after,
                'currency' => $wallet->currency,
                'status' => PaymentStatus::Successful->value,
                'description' => $description,
                'metadata' => $metadata,
                'completed_at' => now(),
            ]);
        });
    }

    public function creditDeposit(WalletTransaction $transaction): WalletTransaction
    {
        if ($transaction->status === PaymentStatus::Successful->value) {
            return $transaction->fresh();
        }

        return DB::transaction(function () use ($transaction) {
            $wallet = MemberWallet::whereKey($transaction->member_wallet_id)->lockForUpdate()->firstOrFail();
            $before = (float) $wallet->balance;
            $after = $before + (float) $transaction->amount;

            $wallet->update(['balance' => $after]);
            $transaction->update([
                'balance_before' => $before,
                'balance_after' => $after,
                'status' => PaymentStatus::Successful->value,
                'completed_at' => now(),
            ]);

            return $transaction->fresh();
        });
    }

    private function nextReference(string $prefix): string
    {
        return $prefix . '-' . now()->format('YmdHis') . '-' . Str::upper(Str::random(8));
    }
}
