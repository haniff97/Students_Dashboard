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
        Schema::table('classes', function (Blueprint $table) {
            $table->string('bil');
            $table->string('tingkatan');
            $table->string('subject');
            $table->string('bilangan_calon');
            $table->string('calon_ambil');
            $table->string('th');
            $table->integer('a_plus_bil')->nullable();
            $table->decimal('a_plus_percent', 5, 2)->nullable();
            $table->integer('a_bil')->nullable();
            $table->decimal('a_percent', 5, 2)->nullable();
            $table->integer('a_minus_bil')->nullable();
            $table->decimal('a_minus_percent', 5, 2)->nullable();
            $table->integer('b_plus_bil')->nullable();
            $table->decimal('b_plus_percent', 5, 2)->nullable();
            $table->integer('b_bil')->nullable();
            $table->decimal('b_percent', 5, 2)->nullable();
            $table->integer('c_plus_bil')->nullable();
            $table->decimal('c_plus_percent', 5, 2)->nullable();
            $table->integer('c_bil')->nullable();
            $table->decimal('c_percent', 5, 2)->nullable();
            $table->integer('d_bil')->nullable();
            $table->decimal('d_percent', 5, 2)->nullable();
            $table->integer('e_bil')->nullable();
            $table->decimal('e_percent', 5, 2)->nullable();
            $table->integer('g_bil')->nullable();
            $table->decimal('g_percent', 5, 2)->nullable();
            $table->decimal('gp', 5, 2)->nullable();
            $table->decimal('lulus_percent', 5, 2)->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('classes', function (Blueprint $table) {
            //
        });
    }
};
