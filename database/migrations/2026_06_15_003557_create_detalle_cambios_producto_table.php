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
        Schema::create('detalle_cambios_producto', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('cambio_producto_id');
            $table->unsignedBigInteger('detalle_venta_devuelto_id');

            $table->unsignedBigInteger('producto_devuelto_id');
            $table->unsignedBigInteger('producto_presentacion_devuelta_id')->nullable();
            $table->unsignedBigInteger('lote_devuelto_id')->nullable();

            $table->integer('cantidad_devuelta')->default(1);
            $table->integer('unidades_devueltas')->default(1);
            $table->decimal('monto_devuelto', 10, 2)->default(0);

            $table->unsignedBigInteger('producto_nuevo_id');
            $table->unsignedBigInteger('producto_presentacion_nueva_id');
            $table->unsignedBigInteger('lote_nuevo_id')->nullable();

            $table->integer('cantidad_nueva')->default(1);
            $table->integer('unidades_nuevas')->default(1);
            $table->decimal('precio_unitario_nuevo', 10, 2)->default(0);
            $table->decimal('monto_nuevo', 10, 2)->default(0);

            $table->timestamps();

            $table->foreign('cambio_producto_id', 'fk_dcp_cambio')
                ->references('id')
                ->on('cambios_producto')
                ->cascadeOnDelete();

            $table->foreign('detalle_venta_devuelto_id', 'fk_dcp_det_venta_dev')
                ->references('id')
                ->on('detalle_ventas');

            $table->foreign('producto_devuelto_id', 'fk_dcp_prod_dev')
                ->references('id')
                ->on('productos');

            $table->foreign('producto_presentacion_devuelta_id', 'fk_dcp_pres_dev')
                ->references('id')
                ->on('producto_presentaciones');

            $table->foreign('lote_devuelto_id', 'fk_dcp_lote_dev')
                ->references('id')
                ->on('lotes');

            $table->foreign('producto_nuevo_id', 'fk_dcp_prod_nuevo')
                ->references('id')
                ->on('productos');

            $table->foreign('producto_presentacion_nueva_id', 'fk_dcp_pres_nueva')
                ->references('id')
                ->on('producto_presentaciones');

            $table->foreign('lote_nuevo_id', 'fk_dcp_lote_nuevo')
                ->references('id')
                ->on('lotes');

            $table->index('cambio_producto_id', 'idx_dcp_cambio');
            $table->index('producto_devuelto_id', 'idx_dcp_prod_dev');
            $table->index('producto_nuevo_id', 'idx_dcp_prod_nuevo');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('detalle_cambios_producto');
    }
};
