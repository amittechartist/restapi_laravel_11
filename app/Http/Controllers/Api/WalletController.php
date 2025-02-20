<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Wallet;
use App\Models\WalletTransaction;
use Illuminate\Support\Facades\DB;

class WalletController extends Controller
{
    /**
     * Display the wallet details for the authenticated user.
     * If a wallet doesn't exist, create one.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function show(Request $request)
    {
        $user = $request->user();

        // If wallet doesn't exist, create one with default values.
        $wallet = Wallet::firstOrCreate(
            ['user_id' => $user->id],
            ['balance' => 0, 'currency' => 'USD']
        );

        return response()->json([
            'success' => true,
            'data' => $wallet
        ]);
    }

    /**
     * Self deposit funds into the wallet.
     * (This is a simplified version that doesn't include actual payment gateway integration.)
     *
     * Expected JSON payload:
     * {
     *     "amount": 50.00,
     *     "description": "Deposit via gateway XYZ",
     *     "reference": "PAYMENT_REF_123"
     * }
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function addFunds(Request $request)
    {
        $request->validate([
            'amount'      => 'required|numeric|min:0.01',
            'description' => 'nullable|string',
            'reference'   => 'nullable|string',
        ]);

        $user = $request->user();

        // Wrap in transaction for consistency.
        DB::transaction(function () use ($request, $user, &$wallet) {
            // Get or create the wallet.
            $wallet = Wallet::firstOrCreate(
                ['user_id' => $user->id],
                ['balance' => 0, 'currency' => 'USD']
            );

            // Update wallet balance.
            $wallet->balance += $request->amount;
            $wallet->save();

            // Log the transaction.
            WalletTransaction::create([
                'wallet_id'   => $wallet->id,
                'user_id'     => $user->id,
                'type'        => 'self_add',
                'amount'      => $request->amount,
                'description' => $request->description ?? 'Self deposit',
                'reference'   => $request->reference,
            ]);
        });

        return response()->json([
            'success' => true,
            'message' => 'Funds added successfully.',
            'data' => $wallet
        ]);
    }

    /**
     * Admin adjusts (credits or debits) a user's wallet.
     *
     * Expected JSON payload:
     * {
     *     "user_id": 123,
     *     "amount": 50.00,    // positive for credit, negative for debit
     *     "type": "admin_credit", // or "admin_debit"
     *     "description": "Manual adjustment by admin"
     * }
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function adjustWallet(Request $request)
    {
        // Ensure only admin users can use this method (apply admin middleware in routes)
        $request->validate([
            'user_id'     => 'required|integer|exists:users,id',
            'amount'      => 'required|numeric|not_in:0',
            'type'        => 'required|in:admin_credit,admin_debit',
            'description' => 'nullable|string',
        ]);

        // You might want to fetch the target user and verify additional admin rules.
        $userId = $request->input('user_id');

        DB::transaction(function () use ($request, $userId, &$wallet) {
            $wallet = Wallet::firstOrCreate(
                ['user_id' => $userId],
                ['balance' => 0, 'currency' => 'USD']
            );

            $amount = $request->input('amount');

            // If type is admin_debit, the amount should be negative.
            if ($request->input('type') == 'admin_debit' && $amount > 0) {
                $amount = -1 * $amount;
            }

            $wallet->balance += $amount;
            $wallet->save();

            WalletTransaction::create([
                'wallet_id'   => $wallet->id,
                'user_id'     => $userId,
                'type'        => $request->input('type'),
                'amount'      => $amount,
                'description' => $request->input('description') ?? 'Admin adjustment',
            ]);
        });

        return response()->json([
            'success' => true,
            'message' => 'Wallet adjusted successfully.',
            'data' => $wallet
        ]);
    }
    /**
     * Retrieve the transaction history for the authenticated user's wallet.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function transactions(Request $request)
    {
        $user = $request->user();

        // Retrieve the wallet for the user (create if not exists)
        $wallet = Wallet::firstOrCreate(
            ['user_id' => $user->id],
            ['balance' => 0, 'currency' => 'USD']
        );

        // Retrieve transactions ordered by latest first.
        $transactions = $wallet->transactions()->orderBy('created_at', 'desc')->get();

        return response()->json([
            'success' => true,
            'data' => $transactions,
        ]);
    }
    public function useFunds(Request $request)
    {
        // Validate the requested amount.
        $request->validate([
            'amount' => 'required|numeric|min:0.01'
        ]);

        $user = $request->user();

        // Get or create the wallet.
        $wallet = Wallet::firstOrCreate(
            ['user_id' => $user->id],
            ['balance' => 0, 'currency' => 'USD']
        );

        // Check for sufficient balance.
        if ($wallet->balance < $request->amount) {
            return response()->json([
                'success' => false,
                'message' => 'Insufficient wallet balance.'
            ], 400);
        }

        // Deduct funds in a database transaction.
        DB::transaction(function () use ($wallet, $request, $user) {
            // Deduct the amount.
            $wallet->balance -= $request->amount;
            $wallet->save();

            // Log the transaction.
            WalletTransaction::create([
                'wallet_id'   => $wallet->id,
                'user_id'     => $user->id,
                'type'        => 'wallet_usage',
                'amount'      => -$request->amount,
                'description' => 'Funds used for purchase',
            ]);
        });

        return response()->json([
            'success' => true,
            'message' => 'Wallet balance deducted successfully.',
            'data'    => $wallet
        ]);
    }
}
