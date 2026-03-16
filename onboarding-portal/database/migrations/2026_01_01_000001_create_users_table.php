<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// Migration 1 of 6 — users table must be created first as other tables reference it
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {
            // SQL Server: INT IDENTITY is the auto-increment equivalent
            $table->id();                          // INT IDENTITY PRIMARY KEY
            $table->string('name');                // NVARCHAR(255)
            $table->string('email')->unique();     // NVARCHAR(255) UNIQUE
            $table->string('password');            // NVARCHAR(255) — bcrypt hash
            $table->string('role', 20)->default('Staff'); // 'Admin' or 'Staff'
            $table->rememberToken();               // NVARCHAR(100) nullable
            $table->timestamps();                  // created_at, updated_at DATETIME2
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};
