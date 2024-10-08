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
        Schema::create('fee_details', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->foreignId('service_id')->constrained('services')->onDelete('cascade');
            $table->foreignId('fee_id')->constrained('fees')->onDelete('cascade');
            $table->double('price');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('fee_details');
    }
};
