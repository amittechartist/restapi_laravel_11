<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\EmailConfig;

class EmailConfigSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        EmailConfig::create([
            'mail_driver'   => 'smtp',
            'host'          => 'smtp.example.com',
            'port'          => 587,
            'username'      => 'smtpdevtestmail2002@gmail.com',
            'password'      => 'dodulmnwogqhuixu',
            'encryption'    => 'tls',
            'from_address'  => 'smtpdevtestmail2002@gmail.com',
            'from_name'     => 'Example App',
        ]);
    }
}
