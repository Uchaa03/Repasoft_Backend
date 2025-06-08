<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {
            // General attributes
            $table->id();
            $table->string('name');
            $table->string('email')->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');

            // Attributes for roles and auth
            $table->boolean('password_changed')->default(false);

            // Attributes for Technicians and Clients
            $table->string('dni')->nullable()->unique();;
            $table->string('address')->nullable();
            $table->string('phone')->nullable();

            // Attributes for Technicians
            $table->string('profile_photo')->nullable();
            $table->float('rating')->nullable()->default(0);
            $table->integer('repairs_count')->nullable()->default(0);
            $table->foreignId('store_id')->nullable()->constrained('stores')->onDelete('set null');

            // Attribute for store added
            $table->foreignId('store_id')->nullable()->constrained('stores')->onDelete('set null');

            // Attributes for laravel
            $table->rememberToken();
            $table->timestamps();
        });


        Schema::create('password_reset_tokens', function (Blueprint $table) {
            $table->string('email')->primary();
            $table->string('token');
            $table->timestamp('created_at')->nullable();
        });

        Schema::create('sessions', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->foreignId('user_id')->nullable()->index();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->longText('payload');
            $table->integer('last_activity')->index();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sessions');
        Schema::dropIfExists('password_reset_tokens');
        Schema::dropIfExists('users');
    }
};
