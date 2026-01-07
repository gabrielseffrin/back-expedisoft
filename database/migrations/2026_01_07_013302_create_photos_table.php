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
        Schema::create('photos', function (Blueprint $table) {
            $table->uuid('id')->primary();

            $table->foreignUuid('loading_order_id')->constrained('loading_orders')->onDelete('cascade');

            $table->string('storage_path');
            $table->string('mime')->comment("ex: 'image/jpeg'");

            $table->foreignUuid('uploaded_by')->constrained('users');
            $table->timestamp('uploaded_at')->useCurrent();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('photos');
    }
};
