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
        Schema::create('codigos_barras', function (Blueprint $table) {
           $table->id();

            $table->foreignId('producto_id')
                ->constrained('productos')
                ->cascadeOnDelete();

            $table->foreignId('producto_presentacion_id')
                ->nullable()
                ->constrained('producto_presentaciones')
                ->nullOnDelete();

            $table->string('codigo', 100)->unique();

            $table->string('tipo_codigo', 30)->default('fabricante');
            $table->boolean('generado_por_sistema')->default(false);

            $table->string('estado', 20)->default('activo');

            $table->timestamps();

            $table->index('codigo');
            $table->index('tipo_codigo');
            $table->index('estado');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('codigos_barras');
    }
};
