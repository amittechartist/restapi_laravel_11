<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class PaymentGatewayLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'wallet_txn_id',
        'gateway_name',
        'status',
        'request_data',
        'response_data',
    ];

    protected $casts = [
        'request_data'  => 'array',
        'response_data' => 'array',
    ];

    public function walletTransaction()
    {
        return $this->belongsTo(WalletTransaction::class, 'wallet_txn_id');
    }
}
