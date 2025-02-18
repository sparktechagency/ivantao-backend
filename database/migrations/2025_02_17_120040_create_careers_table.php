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
        Schema::create('careers', function (Blueprint $table) {
            $table->id();
            $table->string('job_role')->nullable();
            $table->string('job_category')->nullable();
            $table->longText('description')->nullable();
            $table->enum('job_type', ['full_time', 'part_time', 'full_time_on_site', 'full_time_remote','part_time_on_site', 'part_time_remote'])->default('full_time');
            $table->string('address')->nullable();
            $table->date('deadline')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('careers');
    }
};
