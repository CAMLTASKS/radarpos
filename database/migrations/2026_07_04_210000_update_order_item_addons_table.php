<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('order_item_addons', function (Blueprint $table) {
            // Intenta eliminar la clave foránea anterior de addon_id
            try {
                $table->dropForeign(['addon_id']);
            } catch (\Exception $e) {}

            // Agrega las columnas faltantes que espera el OrderController
            if (!Schema::hasColumn('order_item_addons', 'custom_name')) {
                $table->string('custom_name')->nullable()->after('addon_id');
            }
            if (!Schema::hasColumn('order_item_addons', 'quantity')) {
                $table->integer('quantity')->default(1)->after('custom_name');
            }
            if (!Schema::hasColumn('order_item_addons', 'created_at')) {
                $table->timestamps();
            }
        });
    }

    public function down(): void
    {
        Schema::table('order_item_addons', function (Blueprint $table) {
            $table->dropColumn(['custom_name', 'quantity', 'created_at', 'updated_at']);
        });
    }
};
