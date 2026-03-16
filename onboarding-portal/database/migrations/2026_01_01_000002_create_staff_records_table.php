<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// Migration 2 of 6 — depends on users table
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('staff_records', function (Blueprint $table) {
            $table->id();
            // user_id is nullable — a staff record can exist before the person has a portal account
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('full_name');
            $table->string('email');
            $table->string('department');
            $table->date('start_date');
            // Pending = not yet started, Active = working, Offboarded = left
            $table->string('status', 20)->default('Pending');
            // manual = created via form, csv_import = created via sync tool
            $table->string('source', 50)->default('manual');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('staff_records');
    }
};
