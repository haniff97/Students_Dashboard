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
    Schema::table('students', function (Blueprint $table) {
        $table->integer('tov_m')->nullable();
        $table->integer('tov_g')->nullable();
        $table->integer('pa1_m')->nullable();
        $table->integer('pa1_g')->nullable();
        $table->integer('ppt_m')->nullable();
        $table->integer('ppt_g')->nullable();
        $table->integer('uasa_m')->nullable();
        $table->integer('uasa_g')->nullable();
        $table->integer('etr_m')->nullable();
        $table->integer('etr_g')->nullable();
    });
}

public function down(): void
{
    Schema::table('students', function (Blueprint $table) {
        $table->dropColumn([
            'tov_m',
            'tov_g',
            'pa1_m',
            'pa1_g',
            'ppt_m',
            'ppt_g',
            'uasa_m',
            'uasa_g',
            'etr_m',
            'etr_g',
        ]);
    });
}

};
