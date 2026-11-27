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
        if (!Schema::hasColumn('shipments', 'currency')) {
            Schema::table('shipments', function (Blueprint $table) {
                $table->string('currency', 3)->default('IDR');
            });
        }
    }

    public function down()
    {
        if (Schema::hasColumn('shipments', 'currency')) {
            Schema::table('shipments', function (Blueprint $table) {
                $table->dropColumn('currency');
            });
        }
    }
};
