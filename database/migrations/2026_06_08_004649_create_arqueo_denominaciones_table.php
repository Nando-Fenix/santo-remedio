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
        Schema::create('arqueo_denominaciones', function (Blueprint $table) {
            $table->id();

            $table->foreignId('cierre_caja_id')
                ->constrained('cierres_caja')
                ->cascadeOnDelete();

            $table->decimal('denominacion', 10, 2);
            $table->integer('cantidad');
            $table->decimal('total', 10, 2);

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('arqueo_denominaciones');
    }
};
