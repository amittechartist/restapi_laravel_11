<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\SiteSetting;

class SiteSettingSeeder extends Seeder
{
    public function run()
    {
        SiteSetting::create([
            'site_name'       => 'My Awesome Site',
            'short_info'      => 'Welcome to my awesome website built with Laravel!',
            'logo'            => 'default-logo.png', // Update with your default logo URL or path
            'footer_logo'     => 'default-footer-logo.png', // Update with your footer logo URL or path
            'contact_email'   => 'contact@example.com',
            'contact_phone'   => '123-456-7890',
            'contact_address' => '123 Main St, City, Country',
            'social_links'    => [
                'facebook'  => [
                    'url'  => 'https://facebook.com/yourpage',
                    'icon' => 'https://example.com/images/facebook-icon.png',
                ],
                'twitter'   => [
                    'url'  => 'https://twitter.com/yourprofile',
                    'icon' => 'https://example.com/images/twitter-icon.png',
                ],
                'instagram' => [
                    'url'  => 'https://instagram.com/yourprofile',
                    'icon' => 'https://example.com/images/instagram-icon.png',
                ],
            ],
        ]);
    }
}
