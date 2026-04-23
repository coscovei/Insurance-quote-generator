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
        Schema::create('addresses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('person_id')->constrained('people')->onDelete('cascade');
            $table->string('country')->default('RO');
            $table->string('county');
            $table->string('city');
            $table->integer('city_code');
            $table->string('street');
            $table->string('house_number');
            $table->string('building')->nullable();
            $table->string('staircase')->nullable();
            $table->string('apartment')->nullable();
            $table->string('floor')->nullable();
            $table->string('postcode')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('addresses');
    }
};
