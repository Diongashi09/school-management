<?php
// File: database/migrations/2025_09_17_130200_create_fee_payments_table.php

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
        Schema::create('fee_payments', function (Blueprint $table) {
            $table->id();
            $table->string('payment_receipt_number')->unique(); // e.g., "RCP2024001"
            $table->foreignId('student_fee_id')->constrained()->cascadeOnDelete();
            $table->foreignId('student_id')->constrained()->cascadeOnDelete();
            $table->decimal('amount_paid', 10, 2);
            $table->decimal('remaining_amount', 10, 2)->default(0);
            $table->enum('payment_method', ['cash', 'bank_transfer', 'cheque', 'card', 'online', 'other'])->default('cash');
            $table->string('payment_reference')->nullable(); // Bank reference, cheque number, etc.
            $table->date('payment_date');
            $table->foreignId('received_by')->constrained('users')->cascadeOnDelete(); // Staff member who received payment
            $table->text('notes')->nullable();
            $table->boolean('is_verified')->default(false);
            $table->timestamps();

            // Indexes for better performance
            $table->index(['student_id', 'payment_date']);
            $table->index(['payment_date', 'payment_method']);
            $table->index('received_by');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('fee_payments');
    }
};