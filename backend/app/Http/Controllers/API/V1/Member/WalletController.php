<?php

namespace App\Http\Controllers\API\V1\Member;

use App\Http\Controllers\Controller;
use App\Services\Wallet\WalletService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class WalletController extends Controller
{
    /**
     * Get member wallet.
     *
     * Returns the member wallet balance, virtual/deposit details when available, and recent wallet transactions.
     */
    public function show(Request $request, WalletService $wallets): JsonResponse
    {
        $wallet = $wallets->walletFor($request->user());

        return response()->json([
            'success' => true,
            'data' => [
                'wallet' => $wallet,
                'transactions' => $wallet->transactions()
                    ->with('payment')
                    ->latest()
                    ->paginate(20),
            ],
        ]);
    }

    /**
     * Initiate wallet deposit.
     *
     * Starts an xPouch wallet funding request. The wallet is credited after xPouch confirms payment by webhook.
     */
    public function deposit(Request $request, WalletService $wallets): JsonResponse
    {
        $data = $request->validate([
            'amount' => ['required', 'numeric', 'min:1'],
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Wallet deposit initiated',
            'data' => $wallets->initiateDeposit($request->user(), (float) $data['amount']),
        ], 201);
    }
}
