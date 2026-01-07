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
        Schema::create('order_status_history', function (Blueprint $table) {$table->uuid('id')->primary();

            $table->foreignUuid('loading_order_id')->constrained('loading_orders')->onDelete('cascade');

            $table->string('old_status')->nullable();
            $table->string('new_status');

            $table->foreignUuid('changed_by')->constrained('users');
            $table->timestamp('changed_at')->useCurrent();
            $table->text('note')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('order_status_history');
    }
};
