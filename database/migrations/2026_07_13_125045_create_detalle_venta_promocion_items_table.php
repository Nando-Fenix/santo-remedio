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
        Schema::create('detalle_venta_promocion_items', function (Blueprint $table) {
            $table->id();

            $table->foreignId('detalle_venta_promocion_id')
                ->constrained('detalle_venta_promociones')
                ->cascadeOnDelete();

            $table->foreignId('promocion_item_id')
                ->constrained('promocion_items')
                ->restrictOnDelete();

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
        Schema::dropIfExists('detalle_venta_promocion_items');
    }
};
