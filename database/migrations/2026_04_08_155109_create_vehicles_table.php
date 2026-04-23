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
        Schema::create('vehicles', function (Blueprint $table) {
            $table->id();
            $table->string('license_plate');
            $table->string('registration_type')->default('registered');
            $table->string('vin', 17)->unique();
            $table->string('vehicle_type');
            $table->string('brand');
            $table->string('model');
            $table->integer('year_of_construction');
            $table->integer('engine_displacement');
            $table->integer('engine_power');
            $table->integer('total_weight');
            $table->integer('seats');
            $table->string('fuel_type');
            $table->date('first_registration');
            $table->string('usage_type')->default('personal');
            $table->string('civ_number');
            $table->integer('current_mileage')->default(0);
            $table->boolean('has_mobility_modifications')->default(false);
            $table->boolean('is_leased')->default(false);
            $table->boolean('is_new')->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vehicles');
    }
};
