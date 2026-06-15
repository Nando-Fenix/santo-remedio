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
        Schema::create('producto_presentaciones', function (Blueprint $table) {
            $table->id();

            $table->foreignId('producto_id')
                ->constrained('productos')
                ->cascadeOnDelete();

            $table->foreignId('presentacion_id')
                ->constrained('presentaciones')
                ->restrictOnDelete();

            $table->string('nombre_mostrado', 150);

            // Ejemplo: caja x 100 = 100, blister x 10 = 10, unidad = 1
            $table->unsignedInteger('unidades_equivalentes')->default(1);

            $table->decimal('precio_compra', 10, 2)->default(0);
            $table->decimal('precio_venta', 10, 2)->default(0);

            // La presentación rápida para ventas. Normalmente unidad.
            $table->boolean('es_principal')->default(false);

            $table->string('estado', 20)->default('activo');

            $table->timestamps();

            $table->index(['producto_id', 'presentacion_id']);
            $table->index('es_principal');
            $table->index('estado');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('producto_presentaciones');
    }
};
