<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::create('site_settings', function (Blueprint $table) {
            $table->id();
            // Basic site information
            $table->string('site_name')->default('Your Site Name');
            $table->text('short_info')->nullable();
            // Logo URLs or paths
            $table->string('logo')->nullable();
            $table->string('footer_logo')->nullable();
            // Contact information
            $table->string('contact_email')->nullable();
            $table->string('contact_phone')->nullable();
            $table->string('contact_address')->nullable();
            // Social links stored as JSON for flexibility
            $table->json('social_links')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('site_settings');
    }
};
