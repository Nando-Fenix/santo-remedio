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
        Schema::create('bajas_inventario', function (Blueprint $table) {
            $table->id();

            $table->foreignId('producto_id')
                ->constrained('productos')
                ->restrictOnDelete();

            $table->foreignId('sucursal_id')
                ->constrained('sucursales')
                ->restrictOnDelete();

            $table->foreignId('lote_id')
                ->nullable()
                ->constrained('lotes')
                ->nullOnDelete();

            $table->foreignId('usuario_id')
                ->constrained('users')
                ->restrictOnDelete();

            $table->enum('motivo', [
                'vencimiento',
                'danado',
                'perdido',
                'ajuste_autorizado',
                'otro',
            ]);

            $table->integer('cantidad');
            $table->integer('stock_anterior');
            $table->integer('stock_nuevo');

            $table->text('observacion')->nullable();

            $table->enum('estado', ['registrado', 'anulado'])
                ->default('registrado');

            $table->foreignId('usuario_anulacion_id')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->timestamp('fecha_anulacion')->nullable();
            $table->text('motivo_anulacion')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bajas_inventario');
    }

};
