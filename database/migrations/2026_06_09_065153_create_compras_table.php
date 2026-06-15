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
        Schema::create('compras', function (Blueprint $table) {
            $table->id();

            $table->foreignId('proveedor_id')->constrained('proveedores');
            $table->foreignId('sucursal_id')->constrained('sucursales');
            $table->foreignId('usuario_id')->constrained('users');

            $table->string('numero_compra')->unique();
            $table->dateTime('fecha_compra');

            $table->decimal('subtotal', 10, 2)->default(0);
            $table->decimal('descuento_total', 10, 2)->default(0);
            $table->decimal('total', 10, 2)->default(0);

            $table->decimal('monto_pagado', 10, 2)->default(0);
            $table->decimal('saldo_pendiente', 10, 2)->default(0);

            $table->enum('tipo_pago', ['contado', 'credito'])->default('contado');
            $table->enum('estado_pago', ['pagado', 'pendiente', 'parcial'])->default('pagado');
            $table->enum('estado', ['registrada', 'anulada'])->default('registrada');

            $table->text('observacion')->nullable();

            $table->timestamps();

            $table->index(['proveedor_id', 'fecha_compra']);
            $table->index(['sucursal_id', 'fecha_compra']);
            $table->index('estado_pago');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('compras');
    }
};
