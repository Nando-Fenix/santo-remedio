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
        Schema::create('detalle_venta_lotes', function (Blueprint $table) {
        $table->id();

        $table->foreignId('detalle_venta_id')
            ->constrained('detalle_ventas')
            ->cascadeOnDelete();

        $table->foreignId('lote_id')
            ->nullable()
            ->constrained('lotes')
            ->nullOnDelete();

        $table->integer('unidades_descontadas');

        $table->timestamps();

        $table->index('detalle_venta_id');
        $table->index('lote_id');
    });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('detalle_venta_lotes');
    }
};
