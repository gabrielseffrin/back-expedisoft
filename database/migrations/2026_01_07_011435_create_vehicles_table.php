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
        Schema::create('vehicles', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('external_id')->nullable()->unique();
            $table->string('source_system')->nullable();
            $table->string('vehiclePlate')->unique()->comment('Placa do veículo');
            $table->string('model')->nullable();

            $table->foreignUuid('carrier_id')->constrained('carriers')->onDelete('cascade');

            $table->timestamps();

            $table->index(['external_id', 'source_system'] );
            $table->index('vehiclePlate');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vehicles');
    }
};
