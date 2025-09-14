<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('student_parents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained()->onDelete('cascade');
            $table->foreignId('parent_id')->constrained()->onDelete('cascade');
            $table->string('relationship')->default('parent'); // parent, guardian, step-parent, etc.
            $table->boolean('is_primary_contact')->default(false);
            $table->boolean('is_emergency_contact')->default(false);
            $table->boolean('can_pickup')->default(true);
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->unique(['student_id', 'parent_id'], 'sp_unique_relationship');
            $table->index(['student_id']);
            $table->index(['parent_id']);
            $table->index(['is_primary_contact']);
            $table->index(['is_emergency_contact']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('student_parents');
    }
};