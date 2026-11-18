<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Eksekusi penambahan shipping_cost secara terpisah
        if (!Schema::hasColumn('shipments', 'shipping_cost')) {
            Schema::table('shipments', function (Blueprint $table) {
                $table->integer('shipping_cost')->default(0);
            });
        }
        
        // 2. Eksekusi penambahan currency secara terpisah
        if (!Schema::hasColumn('shipments', 'currency')) {
            Schema::table('shipments', function (Blueprint $table) {
                $table->string('currency', 3)->default('IDR');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('shipments', 'shipping_cost')) {
            Schema::table('shipments', function (Blueprint $table) {
                $table->dropColumn('shipping_cost');
            });
        }
        
        if (Schema::hasColumn('shipments', 'currency')) {
            Schema::table('shipments', function (Blueprint $table) {
                $table->dropColumn('currency');
            });
        }
    }
};