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
        // Crear tabla para registrar cierres de caja
        if (!Schema::hasTable('cierre_caja')) {
            Schema::create('cierre_caja', function (Blueprint $table) {
                $table->bigIncrements('id_cierre');
                $table->date('fecha_cierre')->unique();
                $table->decimal('total_efectivo', 12, 2)->default(0);
                $table->decimal('total_qr', 12, 2)->default(0);
                $table->decimal('total_ventas', 12, 2)->default(0);
                $table->integer('cantidad_transacciones')->default(0);
                $table->text('observaciones')->nullable();
                $table->string('estado', 20)->default('Completado'); // Completado, Pendiente, Cancelado
                $table->timestamps();
                
                $table->index('fecha_cierre');
                $table->index('estado');
            });
        }

        // Crear tabla para auditoría de reinicio de sistema
        if (!Schema::hasTable('auditoria_reinicio')) {
            Schema::create('auditoria_reinicio', function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->datetime('fecha_reinicio');
                $table->integer('id_usuario')->nullable();
                $table->integer('total_ventas_borradas')->default(0);
                $table->text('razon')->nullable();
                $table->timestamps();
                
                $table->index('fecha_reinicio');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('auditoria_reinicio');
        Schema::dropIfExists('cierre_caja');
    }
};
