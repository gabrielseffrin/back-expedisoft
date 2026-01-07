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
        Schema::create('integration_logs', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('endpoint');
            $table->jsonb('payload');
            $table->integer('http_status');
            $table->text('error_message')->nullable();
            $table->timestamp('received_at')->useCurrent();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('integration_logs');
    }
};
