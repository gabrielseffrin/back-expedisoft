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
        Schema::create('drivers', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('external_id')->nullable()->unique();
            $table->string('source_system')->nullable();
            $table->string('name');
            $table->string('document')->unique();
            $table->string('phone')->nullable();

            $table->foreignUuid('carrier_id')->constrained('carriers')->onDelete('cascade');

            $table->timestamps();

            $table->index(['external_id', 'source_system'] );
            $table->index('document');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('drivers');
    }
};
