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
        Schema::create('detalle_reembolsos', function (Blueprint $table) {
            $table->id();

            $table->foreignId('reembolso_id')->constrained('reembolsos')->cascadeOnDelete();
            $table->foreignId('detalle_venta_id')->constrained('detalle_ventas');
            $table->foreignId('producto_id')->constrained('productos');
            $table->foreignId('producto_presentacion_id')->nullable()->constrained('producto_presentaciones');
            $table->foreignId('lote_id')->nullable()->constrained('lotes');

            $table->integer('cantidad_devuelta')->default(1);
            $table->integer('unidades_devueltas')->default(1);

            $table->decimal('monto_devuelto', 10, 2)->default(0);

            $table->timestamps();

            $table->index(['reembolso_id', 'producto_id']);
            $table->index('lote_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('detalle_reembolsos');
    }
};
