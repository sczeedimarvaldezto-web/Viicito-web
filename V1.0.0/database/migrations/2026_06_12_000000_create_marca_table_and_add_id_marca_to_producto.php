<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('marca')) {
            Schema::create('marca', function (Blueprint $table) {
                $table->integer('id_marca')->primary()->autoIncrement();
                $table->string('nombre_marca', 50)->unique();
                $table->timestamps();
            });
        }

        if (Schema::hasTable('producto') && !Schema::hasColumn('producto', 'id_marca')) {
            Schema::table('producto', function (Blueprint $table) {
                $table->integer('id_marca')->nullable()->after('id_categoria');
                $table->foreign('id_marca')
                    ->references('id_marca')
                    ->on('marca')
                    ->onDelete('restrict');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('producto') && Schema::hasColumn('producto', 'id_marca')) {
            Schema::table('producto', function (Blueprint $table) {
                $table->dropForeign(['id_marca']);
                $table->dropColumn('id_marca');
            });
        }

        Schema::dropIfExists('marca');
    }
};
