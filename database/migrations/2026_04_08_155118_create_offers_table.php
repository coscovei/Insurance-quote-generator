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
        Schema::create('offers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('insurance_request_id')->constrained()->onDelete('cascade');
            $table->string('insurer_name');
            $table->decimal('price', 10, 2);
            $table->string('currency')->default('RON');
            $table->string('bm_class')->nullable();
            $table->string('external_id')->nullable();
            $table->json('raw_data');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('offers');
    }
};
