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
        Schema::create('reports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained('users')->cascadeOnDelete()->comment('Reporter');
            $table->foreignId('reported_user_id')->nullable()->constrained('users')->cascadeOnDelete()->comment('Reported user');
            $table->foreignId('service_id')->nullable()->constrained('services')->cascadeOnDelete()->comment('Reported service');
            $table->string('reason');
            $table->text('description')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reports');
    }
};
