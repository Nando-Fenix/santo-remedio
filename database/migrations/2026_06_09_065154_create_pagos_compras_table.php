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
        Schema::create('pagos_compras', function (Blueprint $table) {
            $table->id();

            $table->foreignId('compra_id')->constrained('compras')->cascadeOnDelete();
            $table->foreignId('usuario_id')->constrained('users');

            $table->dateTime('fecha_pago');
            $table->decimal('monto', 10, 2);

            $table->enum('metodo_pago', ['efectivo', 'qr', 'transferencia', 'otro'])->default('efectivo');
            $table->string('referencia')->nullable();
            $table->text('observacion')->nullable();

            $table->timestamps();

            $table->index(['compra_id', 'fecha_pago']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pagos_compras');
    }
};
