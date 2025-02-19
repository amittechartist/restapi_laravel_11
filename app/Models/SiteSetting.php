<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SiteSetting extends Model
{
    protected $fillable = [
        'site_name',
        'short_info',
        'logo',
        'footer_logo',
        'contact_email',
        'contact_phone',
        'contact_address',
        'social_links',
    ];

    // Automatically cast the social_links JSON column to a PHP array
    protected $casts = [
        'social_links' => 'array',
    ];
}
