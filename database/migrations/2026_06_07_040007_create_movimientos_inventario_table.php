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
        Schema::create('movimientos_inventario', function (Blueprint $table) {
            $table->id();

            $table->foreignId('producto_id')
                ->constrained('productos')
                ->cascadeOnDelete();

            $table->foreignId('sucursal_id')
                ->constrained('sucursales')
                ->cascadeOnDelete();

            $table->foreignId('lote_id')
                ->nullable()
                ->constrained('lotes')
                ->nullOnDelete();

            $table->foreignId('usuario_id')
                ->constrained('users')
                ->cascadeOnDelete();

            $table->string('tipo_movimiento', 50);
            $table->integer('cantidad');

            $table->integer('stock_anterior')->default(0);
            $table->integer('stock_nuevo')->default(0);

            $table->text('motivo')->nullable();

            $table->string('referencia_tipo', 80)->nullable();
            $table->unsignedBigInteger('referencia_id')->nullable();

            $table->timestamps();

            $table->index(['producto_id', 'sucursal_id', 'lote_id']);
            $table->index('usuario_id');
            $table->index('tipo_movimiento');
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('movimientos_inventario');
    }
};
