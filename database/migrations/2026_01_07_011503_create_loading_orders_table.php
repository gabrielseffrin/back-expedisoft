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
        Schema::create('loading_orders', function (Blueprint $table) {
            $table->uuid('id')->primary();

            // Dados da Ordem
            $table->string('external_id')->unique()->comment('ID da Ordem no ERP');
            $table->date('issue_date');
            $table->string('status')->default('pendente')->comment('pendente, em_andamento, concluido');

            // Relacionamentos Externos (Logística)
            $table->foreignUuid('customer_id')->constrained('customers');
            $table->foreignUuid('destination_id')->constrained('destinations');
            $table->foreignUuid('carrier_id')->constrained('carriers');
            $table->foreignUuid('vehicle_id')->constrained('vehicles');
            $table->foreignUuid('driver_id')->constrained('drivers');

            // Doca (Pode ser nula se ainda não foi agendada)
            $table->foreignUuid('dock_id')->nullable()->constrained('docks');

            // Relacionamentos Internos (Quem fez o quê)
            $table->foreignUuid('created_by')->nullable()->constrained('users')->comment('Gestor que criou/agendou');
            $table->foreignUuid('operator_id')->nullable()->constrained('users')->comment('Operador responsável');

            // Auditoria e Detalhes
            $table->text('justification')->nullable();
            $table->text('observations')->nullable();

            // Controle de Tempo
            $table->timestamp('scheduled_at')->nullable();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('loading_orders');
    }
};
