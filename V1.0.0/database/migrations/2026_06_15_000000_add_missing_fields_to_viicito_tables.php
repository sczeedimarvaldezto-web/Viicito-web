<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations - Add missing fields to existing tables
     * 
     * This migration adds fields that improve data richness and auditing
     * without breaking existing functionality. All new fields have safe defaults.
     */
    public function up(): void
    {
        // ============================================
        // TABLA PRODUCTO - Agregar campos relacionados a licores
        // ============================================
        Schema::table('producto', function (Blueprint $table) {
            // Descripción detallada del producto
            if (!Schema::hasColumn('producto', 'descripcion')) {
                $table->text('descripcion')->nullable()->after('nombre_producto');
            }

            // Volumen en mililitros (para botellas de licores)
            if (!Schema::hasColumn('producto', 'volumen_ml')) {
                $table->integer('volumen_ml')->nullable()->after('stock_minimo');
            }

            // Stock máximo permitido en inventario
            if (!Schema::hasColumn('producto', 'stock_maximo')) {
                $table->integer('stock_maximo')->unsigned()->default(100)->after('stock_minimo');
            }
        });

        // ============================================
        // TABLA VENTA - Agregar desglose de precios y observaciones
        // ============================================
        Schema::table('venta', function (Blueprint $table) {
            // Subtotal sin impuestos
            if (!Schema::hasColumn('venta', 'subtotal')) {
                $table->decimal('subtotal', 12, 2)->nullable()->after('total_venta');
            }

            // Monto de impuesto/IVA
            if (!Schema::hasColumn('venta', 'impuesto')) {
                $table->decimal('impuesto', 12, 2)->default(0)->after('subtotal');
            }

            // Observaciones de la venta
            if (!Schema::hasColumn('venta', 'observacion')) {
                $table->text('observacion')->nullable()->after('estado');
            }
        });

        // ============================================
        // TABLA DETALLE_COMPRA - Agregar control de recepción
        // ============================================
        Schema::table('detalle_compra', function (Blueprint $table) {
            // Cantidad realmente recibida (vs cantidad_ordenada)
            if (!Schema::hasColumn('detalle_compra', 'cantidad_recibida')) {
                $table->integer('cantidad_recibida')->unsigned()->default(0)->after('cantidad');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // ============================================
        // REVERTIR TABLA PRODUCTO
        // ============================================
        Schema::table('producto', function (Blueprint $table) {
            if (Schema::hasColumn('producto', 'descripcion')) {
                $table->dropColumn('descripcion');
            }
            if (Schema::hasColumn('producto', 'volumen_ml')) {
                $table->dropColumn('volumen_ml');
            }
            if (Schema::hasColumn('producto', 'stock_maximo')) {
                $table->dropColumn('stock_maximo');
            }
        });

        // ============================================
        // REVERTIR TABLA VENTA
        // ============================================
        Schema::table('venta', function (Blueprint $table) {
            if (Schema::hasColumn('venta', 'subtotal')) {
                $table->dropColumn('subtotal');
            }
            if (Schema::hasColumn('venta', 'impuesto')) {
                $table->dropColumn('impuesto');
            }
            if (Schema::hasColumn('venta', 'observacion')) {
                $table->dropColumn('observacion');
            }
        });

        // ============================================
        // REVERTIR TABLA DETALLE_COMPRA
        // ============================================
        Schema::table('detalle_compra', function (Blueprint $table) {
            if (Schema::hasColumn('detalle_compra', 'cantidad_recibida')) {
                $table->dropColumn('cantidad_recibida');
            }
        });
    }
};
