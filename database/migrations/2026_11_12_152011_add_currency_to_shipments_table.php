<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::table('shipments', function (Blueprint $table) {
            // Menambahkan kolom currency (IDR / USD)
            $table->string('currency', 3)->default('IDR')->after('shipping_cost');
        });
    }

    public function down()
    {
        Schema::table('shipments', function (Blueprint $table) {
            $table->dropColumn('currency');
        });
    }
};
