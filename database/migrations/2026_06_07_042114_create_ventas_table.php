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
        Schema::create('ventas', function (Blueprint $table) {
            $table->id();

            $table->string('numero_venta', 50)->unique();

            $table->foreignId('usuario_id')
                ->constrained('users')
                ->restrictOnDelete();

            $table->foreignId('sucursal_id')
                ->constrained('sucursales')
                ->restrictOnDelete();

            $table->foreignId('cliente_id')
                ->nullable()
                ->constrained('clientes')
                ->nullOnDelete();

            // Lo dejamos nullable por ahora. Cuando creemos cajas, lo conectaremos.
            $table->unsignedBigInteger('caja_id')->nullable()->index();

            $table->timestamp('fecha_hora');

            $table->decimal('subtotal', 10, 2)->default(0);
            $table->decimal('descuento_total', 10, 2)->default(0);
            $table->decimal('total', 10, 2)->default(0);
            $table->decimal('monto_recibido', 10, 2)->default(0);
            $table->decimal('cambio', 10, 2)->default(0);

            $table->string('estado', 40)->default('completada');
            $table->text('observacion')->nullable();

            $table->timestamps();

            $table->index('fecha_hora');
            $table->index('usuario_id');
            $table->index('sucursal_id');
            $table->index('estado');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ventas');
    }
};
