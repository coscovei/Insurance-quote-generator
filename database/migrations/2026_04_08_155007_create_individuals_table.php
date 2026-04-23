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
        Schema::create('individuals', function (Blueprint $table) {
            $table->id();
            $table->string('first_name');
            $table->string('last_name');
            $table->string('tax_id')->unique();
            $table->date('birthdate');
            $table->string('gender', 1);
            $table->string('nationality')->default('RO');
            $table->string('id_type')->default('CI');
            $table->string('id_number');
            $table->string('issue_authority')->nullable();
            $table->date('issue_date')->nullable();
            $table->boolean('is_retired')->default(false);
            $table->boolean('has_disability')->default(false);
            $table->date('driving_license_issue_date')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('individuals');
    }
};
