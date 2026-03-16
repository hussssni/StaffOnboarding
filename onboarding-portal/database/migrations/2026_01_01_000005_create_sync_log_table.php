<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// Migration 5 of 6 — log of every CSV sync row action
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sync_log', function (Blueprint $table) {
            $table->id();
            $table->string('filename');                   // name of the uploaded CSV file
            $table->string('action', 20);                 // inserted | updated | skipped
            $table->string('record_identifier');          // email or row ID from CSV
            $table->string('notes', 500)->nullable();     // e.g. 'No changes detected'
            $table->foreignId('synced_by')->constrained('users');
            // No updated_at — this is append-only
            $table->timestamp('created_at')->useCurrent();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sync_log');
    }
};
