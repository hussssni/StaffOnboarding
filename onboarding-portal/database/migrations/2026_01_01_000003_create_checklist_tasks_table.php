<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// Migration 3 of 6 — standalone master list of onboarding tasks
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('checklist_tasks', function (Blueprint $table) {
            $table->id();
            $table->string('name');                       // e.g. 'IT Account Setup'
            $table->string('description', 500)->nullable();
            $table->integer('sort_order')->default(0);    // display order
            // No timestamps — this is reference data seeded once
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('checklist_tasks');
    }
};
