<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('repairs', function (Blueprint $table) {
            $table->id();
            $table->string('ticket_number')->unique(); // Custom ticket
            $table->enum('status', ['pending', 'in_progress', 'completed']);
            // $table->foreignId('store_id')->constrained('stores')->onDelete('cascade');
            $table->foreignId('client_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('technician_id')->nullable()->constrained('users')->onDelete('set null');
            $table->integer('hours')->default(0);
            $table->decimal('labor_cost', 8, 2)->default(0);
            $table->decimal('parts_cost', 8, 2)->default(0);
            $table->decimal('total_cost', 8, 2)->default(0);
            $table->boolean('is_warranty')->default(false);
            $table->float('rating')->nullable()->default(0);
            $table->text('description')->nullable();
            $table->timestamp('finished_at')->nullable();
            $table->timestamps();
        });
    }


    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('repairs');
    }
};

