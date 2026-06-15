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
        Schema::create('cierres_caja', function (Blueprint $table) {
            $table->id();

            $table->foreignId('caja_id')
                ->constrained('cajas')
                ->cascadeOnDelete();

            $table->foreignId('usuario_id')
                ->constrained('users')
                ->restrictOnDelete();

            $table->foreignId('sucursal_id')
                ->constrained('sucursales')
                ->restrictOnDelete();

            $table->foreignId('turno_id')
                ->nullable()
                ->constrained('turnos')
                ->nullOnDelete();

            $table->timestamp('fecha_cierre');

            $table->decimal('efectivo_sistema', 10, 2)->default(0);
            $table->decimal('efectivo_contado', 10, 2)->default(0);
            $table->decimal('diferencia_efectivo', 10, 2)->default(0);

            $table->decimal('qr_sistema', 10, 2)->default(0);
            $table->decimal('qr_verificado', 10, 2)->default(0);
            $table->decimal('diferencia_qr', 10, 2)->default(0);

            $table->decimal('total_egresos', 10, 2)->default(0);
            $table->decimal('total_reembolsos', 10, 2)->default(0);

            $table->decimal('monto_retiro_ahorro', 10, 2)->default(0);
            $table->decimal('efectivo_final_despues_ahorro', 10, 2)->default(0);

            $table->decimal('total_sistema', 10, 2)->default(0);
            $table->decimal('total_verificado', 10, 2)->default(0);

            $table->string('estado', 30)->default('correcto');
            $table->text('observacion')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cierres_caja');
    }
};
