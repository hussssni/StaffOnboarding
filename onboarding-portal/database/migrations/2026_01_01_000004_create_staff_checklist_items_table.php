<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// Migration 4 of 6 — depends on staff_records, checklist_tasks, and users
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('staff_checklist_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('staff_record_id')->constrained('staff_records')->cascadeOnDelete();
            $table->foreignId('task_id')->constrained('checklist_tasks');
            // BIT column: 0 = not complete, 1 = complete
            $table->boolean('is_complete')->default(false);
            // Nullable — only set when the item is marked complete
            $table->dateTime('completed_at')->nullable();
            // The admin who marked it complete (nullable)
            $table->foreignId('completed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('staff_checklist_items');
    }
};
