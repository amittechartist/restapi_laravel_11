<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Wallet extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'balance',
        'currency',
    ];

    // Each wallet belongs to a user
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // A wallet has many transactions
    public function transactions()
    {
        return $this->hasMany(WalletTransaction::class);
    }
}
