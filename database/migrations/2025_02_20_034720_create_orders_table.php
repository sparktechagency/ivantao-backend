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
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete()->comment('customer user');
            $table->foreignId('provider_id')->constrained('users')->cascadeOnDelete()->comment('service provider');
            $table->foreignId('service_id')->constrained('services')->cascadeOnDelete();
            $table->string('transaction_id')->nullable();
            $table->decimal('amount', 8, 2);
            $table->decimal('platform_fee', 8, 2);
            $table->date('date')->nullable();
            $table->time('start_time')->nullable();
            $table->time('end_time')->nullable();
            $table->enum('status',['completed','canceled','pending'])->default('pending');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
