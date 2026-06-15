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
        Schema::table('cambios_producto', function (Blueprint $table) {
            $table->foreignId('usuario_anulacion_id')
                ->nullable()
                ->after('estado')
                ->constrained('users');

            $table->dateTime('fecha_anulacion')
                ->nullable()
                ->after('usuario_anulacion_id');

            $table->text('motivo_anulacion')
                ->nullable()
                ->after('fecha_anulacion');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('cambios_producto', function (Blueprint $table) {
            $table->dropForeign(['usuario_anulacion_id']);
            $table->dropColumn([
                'usuario_anulacion_id',
                'fecha_anulacion',
                'motivo_anulacion',
            ]);
        });
    }
};
