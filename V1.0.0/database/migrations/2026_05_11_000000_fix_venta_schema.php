<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations - Agregar campos faltantes a tabla venta y renombrar columnas en detalle_venta
     */
    public function up(): void
    {
        // Agregar campos faltantes a tabla venta
        if (Schema::hasTable('venta')) {
            Schema::table('venta', function (Blueprint $table) {
                // Agregar campos solo si no existen
                if (!Schema::hasColumn('venta', 'numero_documento')) {
                    $table->string('numero_documento', 50)->unique()->after('id_venta');
                }
                if (!Schema::hasColumn('venta', 'subtotal')) {
                    $table->decimal('subtotal', 10, 2)->default(0)->after('total_venta');
                }
                if (!Schema::hasColumn('venta', 'impuesto')) {
                    $table->decimal('impuesto', 10, 2)->default(0)->after('subtotal');
                }
                if (!Schema::hasColumn('venta', 'observacion')) {
                    $table->text('observacion')->nullable()->after('impuesto');
                }
            });
        }

        // Renombrar id_detalle a id_detalle_venta en detalle_venta para consistencia
        if (Schema::hasTable('detalle_venta')) {
            Schema::table('detalle_venta', function (Blueprint $table) {
                // Agregar columna descuento si no existe
                if (!Schema::hasColumn('detalle_venta', 'descuento')) {
                    $table->decimal('descuento', 10, 2)->default(0)->after('precio_unitario');
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('venta')) {
            Schema::table('venta', function (Blueprint $table) {
                $table->dropColumnIfExists('numero_documento');
                $table->dropColumnIfExists('subtotal');
                $table->dropColumnIfExists('impuesto');
                $table->dropColumnIfExists('observacion');
            });
        }

        if (Schema::hasTable('detalle_venta')) {
            Schema::table('detalle_venta', function (Blueprint $table) {
                $table->dropColumnIfExists('descuento');
            });
        }
    }
};
