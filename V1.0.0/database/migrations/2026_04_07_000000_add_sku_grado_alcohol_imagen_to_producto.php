<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('producto', function (Blueprint $table) {
            if (!Schema::hasColumn('producto', 'sku')) {
                $table->string('sku', 50)->nullable()->unique()->after('codigo_barras');
            }
            if (!Schema::hasColumn('producto', 'grado_alcohol')) {
                $table->decimal('grado_alcohol', 5, 2)->nullable()->after('stock_actual');
            }
            if (!Schema::hasColumn('producto', 'imagen_url')) {
                $table->string('imagen_url', 255)->nullable()->after('grado_alcohol');
            }
        });
    }

    public function down()
    {
        Schema::table('producto', function (Blueprint $table) {
            if (Schema::hasColumn('producto', 'sku')) {
                $table->dropUnique(['sku']);
            }
            $table->dropColumn(['sku', 'grado_alcohol', 'imagen_url']);
        });
    }
};
