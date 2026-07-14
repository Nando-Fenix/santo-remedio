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
        Schema::create('promocion_items', function (Blueprint $table) {
            $table->id();

            $table->foreignId('promocion_id')
                ->constrained('promociones')
                ->cascadeOnDelete();

            $table->foreignId('producto_id')
                ->constrained('productos')
                ->cascadeOnDelete();

            $table->foreignId('producto_presentacion_id')
                ->constrained('producto_presentaciones')
                ->cascadeOnDelete();

            $table->foreignId('lote_id')
                ->nullable()
                ->constrained('lotes')
                ->nullOnDelete();

            $table->integer('cantidad')->default(1);
            $table->integer('unidades_necesarias')->default(1);

            $table->decimal('precio_referencia', 10, 2)->default(0);

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('promocion_items');
    }
};
