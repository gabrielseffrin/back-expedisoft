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
        Schema::create('scan_logs', function (Blueprint $table) {
            $table->uuid('id')->primary();

            $table->foreignUuid('loading_order_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignUuid('operator_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignUuid('package_id')->nullable()->constrained()->nullOnDelete();

            $table->string('scanned_code');

            $table->json('payload')->nullable();

            $table->enum('status', ['success', 'error']);

            $table->string('error_message')->nullable();

            $table->timestamp('scanned_at')->useCurrent();

            $table->timestamps();

            $table->index(['loading_order_id']);
            $table->index(['operator_id']);
            $table->index(['package_id']);
            $table->index(['status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('scan_logs');
    }
};
