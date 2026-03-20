<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations - Create Viicito database structure
     */
    public function up(): void
    {
        // Skip if database doesn't exist - will be created first
        $this->createDatabase();
        $this->createTables();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Drop all viicito tables
        Schema::dropIfExists('detalle_venta');
        Schema::dropIfExists('detalle_compra');
        Schema::dropIfExists('venta');
        Schema::dropIfExists('compra');
        Schema::dropIfExists('producto');
        Schema::dropIfExists('proveedor');
        Schema::dropIfExists('cliente');
        Schema::dropIfExists('usuario');
        Schema::dropIfExists('categoria');
    }

    private function createDatabase(): void
    {
        // Database creation is handled by Laravel's connection settings
        // This method is a placeholder for documentation
    }

    private function createTables(): void
    {
        // Categoria table
        if (!Schema::hasTable('categoria')) {
            Schema::create('categoria', function (Blueprint $table) {
                $table->integer('id_categoria')->primary()->autoIncrement();
                $table->string('nombre_categoria', 50);
                $table->timestamps();
            });
        }

        // Cliente table
        if (!Schema::hasTable('cliente')) {
            Schema::create('cliente', function (Blueprint $table) {
                $table->integer('id_cliente')->primary()->autoIncrement();
                $table->string('nombre_razon_social', 100);
                $table->string('nit_ci', 30)->nullable();
                $table->string('telefono', 20)->nullable();
                $table->timestamps();
            });
        }

        // Usuario table
        if (!Schema::hasTable('usuario')) {
            Schema::create('usuario', function (Blueprint $table) {
                $table->integer('id_usuario')->primary()->autoIncrement();
                $table->string('nombre_completo', 100);
                $table->string('username', 50)->unique();
                $table->string('password_hash', 255);
                $table->string('rol', 20);
                $table->string('estado', 15)->default('Activo');
                $table->timestamps();
            });
        }

        // Proveedor table
        if (!Schema::hasTable('proveedor')) {
            Schema::create('proveedor', function (Blueprint $table) {
                $table->integer('id_proveedor')->primary()->autoIncrement();
                $table->string('nombre_empresa', 100);
                $table->string('contacto', 100)->nullable();
                $table->string('telefono', 20)->nullable();
                $table->timestamps();
            });
        }

        // Producto table
        if (!Schema::hasTable('producto')) {
            Schema::create('producto', function (Blueprint $table) {
                $table->integer('id_producto')->primary()->autoIncrement();
                $table->integer('id_categoria')->nullable();
                $table->string('codigo_barras', 50)->nullable()->unique();
                $table->string('nombre_producto', 100);
                $table->decimal('precio_compra', 10, 2);
                $table->decimal('precio_venta', 10, 2);
                $table->integer('stock_actual')->default(0);
                $table->integer('stock_minimo')->default(5);
                $table->string('estado', 15)->default('Activo');
                $table->timestamps();

                // Foreign keys
                $table->foreign('id_categoria')
                    ->references('id_categoria')
                    ->on('categoria')
                    ->onDelete('restrict');
            });
        }

        // Compra table
        if (!Schema::hasTable('compra')) {
            Schema::create('compra', function (Blueprint $table) {
                $table->integer('id_compra')->primary()->autoIncrement();
                $table->integer('id_usuario')->nullable();
                $table->integer('id_proveedor')->nullable();
                $table->dateTime('fecha_hora')->useCurrent();
                $table->decimal('total_compra', 10, 2);
                $table->timestamps();

                // Foreign keys
                $table->foreign('id_usuario')
                    ->references('id_usuario')
                    ->on('usuario')
                    ->onDelete('restrict');
                $table->foreign('id_proveedor')
                    ->references('id_proveedor')
                    ->on('proveedor')
                    ->onDelete('restrict');
            });
        }

        // Detalle_compra table
        if (!Schema::hasTable('detalle_compra')) {
            Schema::create('detalle_compra', function (Blueprint $table) {
                $table->integer('id_detalle_compra')->primary()->autoIncrement();
                $table->integer('id_compra')->nullable();
                $table->integer('id_producto')->nullable();
                $table->integer('cantidad');
                $table->decimal('costo_unitario', 10, 2);
                $table->decimal('subtotal', 10, 2);
                $table->timestamps();

                // Foreign keys
                $table->foreign('id_compra')
                    ->references('id_compra')
                    ->on('compra')
                    ->onDelete('cascade');
                $table->foreign('id_producto')
                    ->references('id_producto')
                    ->on('producto')
                    ->onDelete('restrict');
            });
        }

        // Venta table
        if (!Schema::hasTable('venta')) {
            Schema::create('venta', function (Blueprint $table) {
                $table->integer('id_venta')->primary()->autoIncrement();
                $table->integer('id_usuario')->nullable();
                $table->integer('id_cliente')->nullable();
                $table->dateTime('fecha_hora')->useCurrent();
                $table->decimal('total_venta', 10, 2);
                $table->string('metodo_pago', 20)->nullable();
                $table->string('estado', 15)->default('Completada');
                $table->timestamps();

                // Foreign keys
                $table->foreign('id_usuario')
                    ->references('id_usuario')
                    ->on('usuario')
                    ->onDelete('restrict');
                $table->foreign('id_cliente')
                    ->references('id_cliente')
                    ->on('cliente')
                    ->onDelete('restrict');
            });
        }

        // Detalle_venta table
        if (!Schema::hasTable('detalle_venta')) {
            Schema::create('detalle_venta', function (Blueprint $table) {
                $table->integer('id_detalle')->primary()->autoIncrement();
                $table->integer('id_venta')->nullable();
                $table->integer('id_producto')->nullable();
                $table->integer('cantidad');
                $table->decimal('precio_unitario', 10, 2);
                $table->decimal('subtotal', 10, 2);
                $table->timestamps();

                // Foreign keys
                $table->foreign('id_venta')
                    ->references('id_venta')
                    ->on('venta')
                    ->onDelete('cascade');
                $table->foreign('id_producto')
                    ->references('id_producto')
                    ->on('producto')
                    ->onDelete('restrict');
            });
        }
    }
};
