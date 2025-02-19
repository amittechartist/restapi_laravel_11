<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EmailConfig extends Model
{
    protected $fillable = [
        'mail_driver',
        'host',
        'port',
        'username',
        'password',
        'encryption',
        'from_address',
        'from_name',
    ];

    /**
     * Encrypts and decrypts the password attribute automatically.
     * Laravel 11 supports the "encrypted" cast natively.
     */
    protected $casts = [
        'password' => 'encrypted',
    ];
}
