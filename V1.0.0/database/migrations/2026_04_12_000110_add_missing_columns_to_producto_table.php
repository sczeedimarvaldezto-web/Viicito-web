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
            if (!Schema::hasColumn('producto', 'stock_maximo')) {
                $table->integer('stock_maximo')->nullable()->default(0)->after('stock_minimo');
            }
            if (!Schema::hasColumn('producto', 'descripcion')) {
                $table->text('descripcion')->nullable()->after('nombre_producto');
            }
            if (!Schema::hasColumn('producto', 'volumen_ml')) {
                $table->integer('volumen_ml')->nullable()->after('grado_alcohol');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('producto', function (Blueprint $table) {
            $table->dropColumn(['stock_maximo', 'descripcion', 'volumen_ml']);
        });
    }
};
