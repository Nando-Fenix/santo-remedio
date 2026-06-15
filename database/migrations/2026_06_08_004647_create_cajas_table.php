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
        Schema::create('cajas', function (Blueprint $table) {
            $table->id();

            $table->foreignId('sucursal_id')
                ->constrained('sucursales')
                ->restrictOnDelete();

            $table->foreignId('usuario_id')
                ->constrained('users')
                ->restrictOnDelete();

            $table->foreignId('turno_id')
                ->nullable()
                ->constrained('turnos')
                ->nullOnDelete();

            $table->timestamp('fecha_apertura');
            $table->timestamp('fecha_cierre')->nullable();

            $table->decimal('monto_inicial', 10, 2)->default(0);

            $table->decimal('total_efectivo', 10, 2)->default(0);
            $table->decimal('total_qr', 10, 2)->default(0);
            $table->decimal('total_egresos', 10, 2)->default(0);
            $table->decimal('total_reembolsos', 10, 2)->default(0);
            $table->decimal('total_final', 10, 2)->default(0);

            $table->string('estado', 20)->default('abierta');
            $table->text('observacion')->nullable();

            $table->timestamps();

            $table->index(['sucursal_id', 'usuario_id', 'estado']);
            $table->index('fecha_apertura');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cajas');
    }
};
