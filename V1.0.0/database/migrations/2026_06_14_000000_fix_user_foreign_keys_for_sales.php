<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    protected function foreignKeyName(string $table, string $column): ?string
    {
        $rows = DB::select("
            SELECT CONSTRAINT_NAME
            FROM information_schema.KEY_COLUMN_USAGE
            WHERE TABLE_SCHEMA = DATABASE()
              AND TABLE_NAME = ?
              AND COLUMN_NAME = ?
              AND REFERENCED_TABLE_NAME IS NOT NULL
        ", [$table, $column]);

        return $rows[0]->CONSTRAINT_NAME ?? null;
    }

    protected function dropForeignIfExists(string $table, string $column): void
    {
        $fk = $this->foreignKeyName($table, $column);

        if (! $fk) {
            return;
        }

        try {
            Schema::table($table, function (Blueprint $table) use ($fk) {
                $table->dropForeign($fk);
            });
        } catch (\Throwable) {
            // Ignore if the key is already absent or the DB engine does not expose it.
        }
    }

    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $this->dropForeignIfExists('venta', 'id_usuario');
        $this->dropForeignIfExists('compra', 'id_usuario');

        if (Schema::hasColumn('venta', 'id_usuario')) {
            Schema::table('venta', function (Blueprint $table) {
                $table->unsignedBigInteger('id_usuario')->nullable()->change();
            });
        }

        if (Schema::hasColumn('compra', 'id_usuario')) {
            Schema::table('compra', function (Blueprint $table) {
                $table->unsignedBigInteger('id_usuario')->nullable()->change();
            });
        }

        Schema::table('venta', function (Blueprint $table) {
            $table->foreign('id_usuario')
                ->references('id')
                ->on('users')
                ->onDelete('restrict');
        });

        Schema::table('compra', function (Blueprint $table) {
            $table->foreign('id_usuario')
                ->references('id')
                ->on('users')
                ->onDelete('restrict');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('venta', function (Blueprint $table) {
            $table->dropForeign(['id_usuario']);
            $table->foreign('id_usuario')
                ->references('id_usuario')
                ->on('usuario')
                ->onDelete('restrict');
        });

        Schema::table('compra', function (Blueprint $table) {
            $table->dropForeign(['id_usuario']);
            $table->foreign('id_usuario')
                ->references('id_usuario')
                ->on('usuario')
                ->onDelete('restrict');
        });
    }
};
