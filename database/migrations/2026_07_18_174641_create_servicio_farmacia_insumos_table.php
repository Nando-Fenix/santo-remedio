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
        Schema::create('servicio_farmacia_insumos', function (Blueprint $table) {
            $table->id();

            $table->foreignId('servicio_farmacia_id')
                ->constrained('servicios_farmacia')
                ->cascadeOnDelete();

            $table->foreignId('producto_id')
                ->constrained('productos')
                ->restrictOnDelete();

            $table->foreignId('producto_presentacion_id')
                ->constrained('producto_presentaciones')
                ->restrictOnDelete();

            $table->integer('cantidad')->default(1);
            $table->integer('unidades_necesarias')->default(1);

            $table->timestamps();
        });
    }


    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('servicio_farmacia_insumos');
    }
};
