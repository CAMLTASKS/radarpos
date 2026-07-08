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
        Schema::table('products', function (Blueprint $table) {
            $table->boolean('is_addon')->default(false)->after('category');
        });

        // Modificar la tabla de order_item_addons para apuntar a products en lugar de addons
        // Dado que SQLite tiene limitaciones con dropForeign, y MySQL sí lo permite,
        // Haremos drop constraint si es posible, o sino lo dejamos y agregamos una nueva columna.
        Schema::table('order_item_addons', function (Blueprint $table) {
            // Renombrar addon_id a product_id no es tan trivial por el FK, agreguemos una nueva columna.
            $table->foreignId('product_id')->nullable()->constrained('products')->onDelete('cascade')->after('order_item_id');
        });
    }

    public function down(): void
    {
        Schema::table('order_item_addons', function (Blueprint $table) {
            $table->dropForeign(['product_id']);
            $table->dropColumn('product_id');
        });

        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn('is_addon');
        });
    }
};
