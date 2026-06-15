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
        Schema::create('reembolsos', function (Blueprint $table) {
            $table->id();

            $table->foreignId('venta_id')->constrained('ventas');
            $table->foreignId('usuario_id')->constrained('users');
            $table->foreignId('sucursal_id')->constrained('sucursales');
            $table->foreignId('caja_id')->nullable()->constrained('cajas');

            $table->string('numero_reembolso')->unique();
            $table->dateTime('fecha_reembolso');

            $table->decimal('monto_total', 10, 2)->default(0);

            $table->text('motivo');
            $table->enum('estado', ['registrado', 'anulado'])->default('registrado');

            $table->timestamps();

            $table->index(['venta_id', 'fecha_reembolso']);
            $table->index(['sucursal_id', 'fecha_reembolso']);
            $table->index('estado');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reembolsos');
    }
};
