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
        Schema::table('students', function (Blueprint $table) {
            $table->string('pa1_g', 10)->change();
            $table->string('ppt_g', 10)->change();
            $table->string('uasa_g', 10)->change();
        });
    }

    public function down()
    {
        Schema::table('students', function (Blueprint $table) {
            $table->integer('pa1_g')->change();
            $table->integer('ppt_g')->change();
            $table->integer('uasa_g')->change();
        });
    }
};
