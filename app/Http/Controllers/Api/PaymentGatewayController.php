<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Wallet;
use App\Models\WalletTransaction;
use App\Models\PaymentGatewayLog;
use Illuminate\Support\Facades\DB;

class PaymentGatewayController extends Controller
{
    /**
     * Payment gateway callback.
     * This endpoint simulates a callback from a payment gateway.
     *
     * Expected JSON payload:
     * {
     *    "wallet_id": 1,
     *    "amount": 100.00,
     *    "status": "success",  // or "failed", "pending"
     *    "gateway_name": "Stripe",
     *    "reference": "STRIPE_REF_98765"
     * }
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function callback(Request $request)
    {
        $data = $request->validate([
            'wallet_id'    => 'required|exists:wallets,id',
            'amount'       => 'required|numeric|min:0.01',
            'status'       => 'required|string',
            'gateway_name' => 'required|string',
            'reference'    => 'nullable|string',
        ]);
        
        DB::transaction(function () use ($data, $request) {
            $wallet = Wallet::find($data['wallet_id']);
            
            // If the payment succeeded, update the wallet balance.
            if ($data['status'] === 'success') {
                $wallet->balance += $data['amount'];
                $wallet->save();
                
                // Create a wallet transaction for a self deposit.
                WalletTransaction::create([
                    'wallet_id'   => $wallet->id,
                    'user_id'     => $wallet->user_id,
                    'type'        => 'self_add',
                    'amount'      => $data['amount'],
                    'description' => 'Payment gateway deposit',
                    'reference'   => $data['reference'] ?? null,
                ]);
            }
            
            // Log the payment gateway callback.
            PaymentGatewayLog::create([
                'wallet_txn_id'  => null, // Optionally, link to a wallet transaction ID
                'gateway_name'   => $data['gateway_name'],
                'status'         => $data['status'],
                'request_data'   => $request->all(),
                'response_data'  => null,
            ]);
        });
        
        return response()->json([
            'success' => true,
            'message' => 'Payment processed successfully.',
        ]);
    }
    
    /**
     * Retrieve payment gateway logs (for admin auditing).
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function logs(Request $request)
    {
        // You may add filters here as needed.
        $logs = PaymentGatewayLog::orderBy('created_at', 'desc')->get();
        
        return response()->json([
            'success' => true,
            'data' => $logs,
        ]);
    }
}
