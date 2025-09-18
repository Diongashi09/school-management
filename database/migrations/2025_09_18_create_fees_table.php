<?php
// File: database/migrations/2025_09_17_130000_create_fees_table.php

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
        Schema::create('fees', function (Blueprint $table) {
            $table->id();
            $table->string('fee_code')->unique(); // e.g., "FEE2024001"
            $table->string('name');
            $table->text('description')->nullable();
            $table->decimal('amount', 10, 2);
            $table->enum('fee_type', ['tuition', 'transport', 'hostel', 'library', 'exam', 'sports', 'miscellaneous'])->default('tuition');
            $table->enum('payment_frequency', ['one_time', 'monthly', 'quarterly', 'semester', 'yearly'])->default('yearly');
            $table->foreignId('academic_year_id')->constrained()->cascadeOnDelete();
            $table->foreignId('class_id')->nullable()->constrained('classes')->nullOnDelete();
            $table->boolean('is_mandatory')->default(true);
            $table->boolean('is_active')->default(true);
            $table->date('due_date')->nullable();
            $table->integer('late_fee_amount')->default(0); // Late fee in currency
            $table->integer('late_fee_days')->default(0); // Days after due date for late fee
            $table->timestamps();

            // Indexes for better performance
            $table->index(['academic_year_id', 'class_id']);
            $table->index(['fee_type', 'is_active']);
            $table->index('due_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('fees');
    }
};
-----------------------------
<?php
// File: database/migrations/2025_09_17_130100_create_student_fees_table.php

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
        Schema::create('student_fees', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained()->cascadeOnDelete();
            $table->foreignId('fee_id')->constrained()->cascadeOnDelete();
            $table->foreignId('academic_year_id')->constrained()->cascadeOnDelete();
            $table->decimal('amount', 10, 2);
            $table->decimal('discount_amount', 10, 2)->default(0);
            $table->decimal('late_fee_amount', 10, 2)->default(0);
            $table->decimal('total_amount', 10, 2); // amount + late_fee - discount
            $table->enum('status', ['pending', 'paid', 'partial', 'overdue', 'cancelled'])->default('pending');
            $table->date('due_date');
            $table->date('paid_date')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            // Ensure one fee record per student per fee per academic year
            $table->unique(['student_id', 'fee_id', 'academic_year_id'], 'unique_student_fee');
           
            // Indexes for better performance
            $table->index(['student_id', 'status']);
            $table->index(['fee_id', 'status']);
            $table->index(['academic_year_id', 'status']);
            $table->index('due_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('student_fees');
    }
};