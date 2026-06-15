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
        Schema::create('productos', function (Blueprint $table) {
            $table->id();

            $table->string('nombre_comercial', 150);
            $table->string('nombre_generico', 150)->nullable();
            $table->text('descripcion')->nullable();
            $table->string('concentracion', 100)->nullable();

            $table->foreignId('categoria_id')
                ->nullable()
                ->constrained('categorias')
                ->nullOnDelete();

            $table->foreignId('laboratorio_id')
                ->nullable()
                ->constrained('laboratorios')
                ->nullOnDelete();

            $table->foreignId('proveedor_id')
                ->nullable()
                ->constrained('proveedores')
                ->nullOnDelete();

            $table->string('estado', 20)->default('activo');

            $table->timestamps();

            $table->index('nombre_comercial');
            $table->index('nombre_generico');
            $table->index('estado');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('productos');
    }
};
