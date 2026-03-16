<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// Migration 6 of 6 — permanent, append-only record of all data changes
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('audit_log', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users');
            $table->string('action', 20);           // created | updated | deleted
            $table->string('target_table', 100);    // which table was affected
            $table->integer('target_id');            // which row was affected
            // JSON blob — before/after values for updates, full record for create/delete
            $table->text('changed_data')->nullable();
            // No updated_at — this table is append-only, never modified
            $table->timestamp('created_at')->useCurrent();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('audit_log');
    }
};
