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
        Schema::create('insurance_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('policyholder_id')->constrained('people');
            $table->foreignId('owner_id')->constrained('people');
            $table->foreignId('vehicle_id')->constrained();
            $table->string('target_provider')->default('asirom');
            $table->date('start_date');
            $table->integer('term_time')->default(12);
            $table->boolean('has_casco')->default(false);
            $table->integer('coordinator_id')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('insurance_requests');
    }
};
