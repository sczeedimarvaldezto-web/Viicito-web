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
        Schema::table('producto', function (Blueprint $table) {
            // Agregar columnas faltantes si no existen
            if (!Schema::hasColumn('producto', 'descripcion')) {
                $table->text('descripcion')->nullable()->after('nombre_producto');
            }
            
            if (!Schema::hasColumn('producto', 'sku')) {
                $table->string('sku', 50)->nullable()->unique()->after('codigo_barras');
            }
            
            if (!Schema::hasColumn('producto', 'stock_maximo')) {
                $table->integer('stock_maximo')->default(100)->after('stock_minimo');
            }
            
            if (!Schema::hasColumn('producto', 'volumen_ml')) {
                $table->integer('volumen_ml')->nullable()->after('stock_maximo');
            }
            
            if (!Schema::hasColumn('producto', 'grado_alcohol')) {
                $table->decimal('grado_alcohol', 5, 2)->nullable()->after('volumen_ml');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('producto', function (Blueprint $table) {
            if (Schema::hasColumn('producto', 'descripcion')) {
                $table->dropColumn('descripcion');
            }
            
            if (Schema::hasColumn('producto', 'sku')) {
                $table->dropColumn('sku');
            }
            
            if (Schema::hasColumn('producto', 'stock_maximo')) {
                $table->dropColumn('stock_maximo');
            }
            
            if (Schema::hasColumn('producto', 'volumen_ml')) {
                $table->dropColumn('volumen_ml');
            }
            
            if (Schema::hasColumn('producto', 'grado_alcohol')) {
                $table->dropColumn('grado_alcohol');
            }
        });
    }
};
