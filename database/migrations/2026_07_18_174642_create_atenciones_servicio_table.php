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
        Schema::create('atenciones_servicio', function (Blueprint $table) {
            $table->id();

            $table->foreignId('servicio_farmacia_id')
                ->constrained('servicios_farmacia')
                ->restrictOnDelete();

            $table->foreignId('sucursal_id')
                ->constrained('sucursales')
                ->restrictOnDelete();

            $table->foreignId('usuario_id')
                ->constrained('users')
                ->restrictOnDelete();

            $table->foreignId('cliente_id')
                ->nullable()
                ->constrained('clientes')
                ->nullOnDelete();

            $table->foreignId('caja_id')
                ->constrained('cajas')
                ->restrictOnDelete();

            $table->foreignId('metodo_pago_id')
                ->constrained('metodos_pago')
                ->restrictOnDelete();

            $table->dateTime('fecha_hora');

            $table->integer('cantidad')->default(1);
            $table->decimal('precio_unitario', 10, 2);
            $table->decimal('subtotal', 10, 2);
            $table->decimal('descuento', 10, 2)->default(0);
            $table->decimal('total', 10, 2);

            $table->enum('estado', ['completada', 'anulada'])->default('completada');

            $table->text('observacion')->nullable();
            $table->text('motivo_anulacion')->nullable();
            $table->timestamp('fecha_anulacion')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('atenciones_servicio');
    }
};
