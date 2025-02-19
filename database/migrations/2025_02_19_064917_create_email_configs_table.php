<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::create('email_configs', function (Blueprint $table) {
            $table->id();
            $table->string('mail_driver')->default('smtp');
            $table->string('host');
            $table->unsignedInteger('port');
            $table->string('username');
            $table->text('password'); // Sensitive data, will be encrypted by model cast
            $table->string('encryption')->nullable(); // e.g., 'ssl' or 'tls'
            $table->string('from_address');
            $table->string('from_name');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('email_configs');
    }
};
