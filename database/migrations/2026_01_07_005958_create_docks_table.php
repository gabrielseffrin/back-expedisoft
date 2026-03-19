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
        Schema::create('docks', function (Blueprint $table) {
            $table->string('external_id')->nullable()->unique();
            $table->string('source_system')->nullable();
            $table->uuid('id')->primary();
            $table->string('dock_code')->unique()->comment('ex: DOCK-01');
            $table->string('description')->nullable();
            $table->string('location')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('docks');
    }
};
