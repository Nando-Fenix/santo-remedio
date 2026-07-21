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
        Schema::create('servicios_farmacia', function (Blueprint $table) {
            $table->id();

            $table->string('nombre', 150);
            $table->text('descripcion')->nullable();

            $table->decimal('precio', 10, 2)->default(0);

            $table->enum('tipo', [
                'inyectable',
                'control',
                'curacion',
                'nebulizacion',
                'orientacion',
                'otro',
            ])->default('otro');

            $table->enum('estado', ['activo', 'inactivo'])->default('activo');

            $table->foreignId('creado_por')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->timestamps();
        });
    }


    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('servicios_farmacia');
    }
};
