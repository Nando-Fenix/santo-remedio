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
        Schema::create('atencion_servicio_insumos', function (Blueprint $table) {
            $table->id();

            $table->foreignId('atencion_servicio_id')
                ->constrained('atenciones_servicio')
                ->cascadeOnDelete();

            $table->foreignId('servicio_farmacia_insumo_id')
                ->nullable()
                ->constrained('servicio_farmacia_insumos')
                ->nullOnDelete();

            $table->foreignId('producto_id')
                ->constrained('productos')
                ->restrictOnDelete();

            $table->foreignId('producto_presentacion_id')
                ->constrained('producto_presentaciones')
                ->restrictOnDelete();

            $table->foreignId('lote_id')
                ->nullable()
                ->constrained('lotes')
                ->nullOnDelete();

            $table->integer('unidades_descontadas');

            $table->timestamps();
        });
    }


    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('atencion_servicio_insumos');
    }
};
