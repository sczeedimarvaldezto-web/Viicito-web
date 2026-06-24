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
        Schema::table('users', function (Blueprint $table) {
            // Agregamos la columna 'estado'. Le ponemos 'Activo' por defecto 
            // basándonos en lo que intenta insertar tu controlador.
            $table->string('estado')->default('Activo')->after('role_id');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Revierte el cambio eliminando la columna
            $table->dropColumn('estado');
        });
    }
};
