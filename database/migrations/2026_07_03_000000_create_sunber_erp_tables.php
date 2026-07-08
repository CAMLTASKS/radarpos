<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Insumos e Inventario
        Schema::create('ingredients', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('unit');
            $table->decimal('stock', 8, 2)->default(0.00);
            $table->decimal('cost', 10, 2)->default(0.00);
            $table->timestamps();
        });

        // 2. Productos y Variaciones
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->decimal('price', 10, 2)->default(0.00);
            $table->string('image')->nullable();
            $table->string('category')->default('General');
            $table->timestamps();
        });

        Schema::create('product_variations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->onDelete('cascade');
            $table->string('name');
            $table->decimal('price_modifier', 10, 2)->default(0.00);
            $table->timestamps();
        });

        // 3. Receta / Escandallo
        Schema::create('product_ingredients', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->onDelete('cascade');
            $table->foreignId('ingredient_id')->constrained()->onDelete('cascade');
            $table->decimal('quantity_required', 8, 2)->default(0.00);
            // We use 'quantity' as well because the previous Pivot used it, let's keep it safe.
            // Wait, schema.sql has 'quantity_required', but earlier the error mentioned 'quantity' in ingredient_product.
            // Let's add 'quantity' as a synonym just in case, or rename it.
            $table->decimal('quantity', 8, 2)->default(0.00);
            $table->timestamps();
        });

        // 4. Adicionales Globales
        Schema::create('addons', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->decimal('price', 10, 2)->default(0.00);
            $table->foreignId('ingredient_id')->nullable()->constrained('ingredients')->onDelete('set null');
            $table->decimal('ingredient_quantity', 8, 2)->default(0.00);
            $table->decimal('cost', 10, 2)->default(0.00);
            $table->timestamps();
        });

        // 1.1 Configuración / Parámetros
        Schema::create('settings', function (Blueprint $table) {
            $table->id();
            $table->string('key_name', 100)->unique();
            $table->text('value')->nullable();
            $table->string('description')->nullable();
            $table->timestamps();
        });

        // 1.2 Repartidores
        Schema::create('delivery_drivers', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('phone', 50)->nullable();
            $table->string('vehicle_plate', 50)->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // 5. Clientes y Fidelización
        Schema::create('customers', function (Blueprint $table) {
            $table->id();
            $table->string('phone', 20)->unique();
            $table->string('name')->nullable();
            $table->integer('points')->default(0);
            $table->integer('orders_count')->default(0);
            $table->timestamps();
        });

        // 5.1 Cajas Registradoras
        Schema::create('cash_registers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('set null');
            $table->decimal('opening_amount', 10, 2)->default(0.00);
            $table->decimal('closing_amount', 10, 2)->nullable();
            $table->enum('status', ['open', 'closed'])->default('open');
            $table->timestamp('opened_at')->nullable();
            $table->timestamp('closed_at')->nullable();
            $table->timestamps();
        });

        // 6. Órdenes y Detalles
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cash_register_id')->nullable()->constrained('cash_registers')->onDelete('set null');
            $table->foreignId('customer_id')->nullable()->constrained('customers')->onDelete('set null');
            $table->string('customer_name')->nullable();
            $table->string('customer_phone', 20)->nullable();
            $table->string('delivery_address', 500)->nullable();
            $table->string('delivery_driver')->nullable();
            $table->enum('delivery_type', ['local', 'delivery'])->default('local');
            $table->enum('payment_method', ['efectivo', 'nequi', 'daviplata'])->default('efectivo');
            $table->decimal('subtotal', 10, 2)->default(0.00);
            $table->decimal('delivery_fee', 10, 2)->default(0.00);
            $table->decimal('total', 10, 2)->default(0.00);
            $table->string('status', 50)->default('pending');
            $table->string('notes')->nullable();
            $table->boolean('whatsapp_sent')->default(false);
            $table->timestamps();
        });

        Schema::create('order_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->onDelete('cascade');
            $table->foreignId('product_id')->constrained()->onDelete('cascade');
            $table->foreignId('variation_id')->nullable()->constrained('product_variations')->onDelete('set null');
            $table->integer('quantity')->default(1);
            $table->decimal('price', 10, 2);
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        Schema::create('order_item_addons', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_item_id')->constrained()->onDelete('cascade');
            $table->foreignId('addon_id')->constrained()->onDelete('cascade');
            $table->integer('quantity')->default(1);
            $table->decimal('price', 10, 2);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('order_item_addons');
        Schema::dropIfExists('order_items');
        Schema::dropIfExists('orders');
        Schema::dropIfExists('cash_registers');
        Schema::dropIfExists('customers');
        Schema::dropIfExists('delivery_drivers');
        Schema::dropIfExists('settings');
        Schema::dropIfExists('addons');
        Schema::dropIfExists('product_ingredients');
        Schema::dropIfExists('product_variations');
        Schema::dropIfExists('products');
        Schema::dropIfExists('ingredients');
    }
};
