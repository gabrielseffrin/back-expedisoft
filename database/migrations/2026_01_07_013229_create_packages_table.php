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
        Schema::create('packages', function (Blueprint $table) {
            $table->uuid('id')->primary();

            // Pertence a um item do pedido (ex: 1 cadeira dentro de um pedido de 10 cadeiras)
            $table->foreignUuid('order_item_id')->constrained('order_items')->onDelete('cascade');

            $table->string('unique_package_code')->unique()->comment('O código QR/Barra específico deste volume');
            $table->integer('quantity_in_package')->default(1)->comment('Qtd de itens dentro deste pacote');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('packages');
    }
};
