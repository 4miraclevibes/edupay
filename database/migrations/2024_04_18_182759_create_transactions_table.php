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
        Schema::create('transactions', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->foreignId('payment_id')->nullable()->constrained('payments')->onDelete('cascade');
            $table->string('transaction_code');
            $table->double('total');
            $table->enum('case', ['transaction', 'topup', 'payment']);
            $table->enum('status', ['pending', 'success', 'danied', 'cancel']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transactions');
    }
};
