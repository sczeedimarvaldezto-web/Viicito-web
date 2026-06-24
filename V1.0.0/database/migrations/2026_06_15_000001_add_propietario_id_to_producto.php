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
            // Agregar campo propietario_id después de id_marca
            $table->unsignedBigInteger('propietario_id')->nullable()->after('id_marca');
            $table->foreign('propietario_id')->references('id')->on('users')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('producto', function (Blueprint $table) {
            $table->dropForeign(['propietario_id']);
            $table->dropColumn('propietario_id');
        });
    }
};
