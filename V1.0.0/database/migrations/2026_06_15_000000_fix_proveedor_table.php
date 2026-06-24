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
        // Verificar si la tabla proveedor existe
        if (Schema::hasTable('proveedor')) {
            Schema::table('proveedor', function (Blueprint $table) {
                // Renombrar columna 'contacto' a 'contacto_nombre' si existe
                if (Schema::hasColumn('proveedor', 'contacto') && !Schema::hasColumn('proveedor', 'contacto_nombre')) {
                    $table->renameColumn('contacto', 'contacto_nombre');
                }

                // Agregar columnas faltantes si no existen
                if (!Schema::hasColumn('proveedor', 'email')) {
                    $table->string('email', 100)->nullable()->after('contacto_nombre');
                }

                if (!Schema::hasColumn('proveedor', 'ciudad')) {
                    $table->string('ciudad', 50)->nullable()->after('email');
                }

                if (!Schema::hasColumn('proveedor', 'estado_proveedor')) {
                    $table->enum('estado_proveedor', ['Activo', 'Inactivo', 'Suspendido'])
                        ->default('Activo')
                        ->after('ciudad');
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('proveedor')) {
            Schema::table('proveedor', function (Blueprint $table) {
                // Revertir cambios
                if (Schema::hasColumn('proveedor', 'estado_proveedor')) {
                    $table->dropColumn('estado_proveedor');
                }

                if (Schema::hasColumn('proveedor', 'ciudad')) {
                    $table->dropColumn('ciudad');
                }

                if (Schema::hasColumn('proveedor', 'email')) {
                    $table->dropColumn('email');
                }

                if (Schema::hasColumn('proveedor', 'contacto_nombre') && !Schema::hasColumn('proveedor', 'contacto')) {
                    $table->renameColumn('contacto_nombre', 'contacto');
                }
            });
        }
    }
};
