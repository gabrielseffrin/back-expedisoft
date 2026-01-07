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
        Schema::create('checklist_entries', function (Blueprint $table) {
            $table->uuid('id')->primary();

            $table->foreignUuid('loading_order_id')->constrained('loading_orders')->onDelete('cascade');

            // Pode ser nulo se o operador bipar um código que não existe no sistema
            $table->foreignUuid('package_id')->nullable()->constrained('packages');

            $table->foreignUuid('scanned_by')->constrained('users');

            $table->timestamp('scanned_at')->useCurrent();
            $table->string('scanned_code')->comment('O código exato que foi lido pelo leitor');
            $table->string('result')->comment("ex: 'OK', 'ERRADO', 'DUPLICADO'");
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('checklist_entries');
    }
};
