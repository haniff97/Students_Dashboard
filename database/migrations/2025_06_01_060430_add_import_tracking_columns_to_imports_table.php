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
    Schema::table('imports', function (Blueprint $table) {
        $table->unsignedInteger('processed_rows')->default(0);
        $table->unsignedInteger('successful_rows')->default(0);
        $table->unsignedInteger('failed_rows')->default(0);
    });
    }

    public function down()
    {
    Schema::table('imports', function (Blueprint $table) {
        $table->dropColumn(['processed_rows', 'successful_rows', 'failed_rows']);
    });
    }

};
