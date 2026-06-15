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
        Schema::create('cambios_producto', function (Blueprint $table) {
            $table->id();

            $table->foreignId('venta_id')->constrained('ventas');
            $table->foreignId('usuario_id')->constrained('users');
            $table->foreignId('sucursal_id')->constrained('sucursales');
            $table->foreignId('caja_id')->nullable()->constrained('cajas');

            $table->string('numero_cambio')->unique();
            $table->dateTime('fecha_cambio');

            $table->decimal('monto_devuelto', 10, 2)->default(0);
            $table->decimal('monto_nuevo', 10, 2)->default(0);
            $table->decimal('diferencia', 10, 2)->default(0);

            $table->enum('tipo_diferencia', [
                'cliente_paga',
                'farmacia_devuelve',
                'sin_diferencia'
            ])->default('sin_diferencia');

            $table->text('motivo');
            $table->enum('estado', ['registrado', 'anulado'])->default('registrado');

            $table->timestamps();

            $table->index(['venta_id', 'fecha_cambio']);
            $table->index(['sucursal_id', 'fecha_cambio']);
            $table->index('estado');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cambios_producto');
    }
};
