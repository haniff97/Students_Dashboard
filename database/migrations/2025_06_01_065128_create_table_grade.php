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
    Schema::create('grades', function (Blueprint $table) {
        $table->id();
        $table->unsignedTinyInteger('min_mark'); // Minimum mark for the grade
        $table->string('grade');                // Grade code (A, B+, etc.)
        $table->unsignedTinyInteger('rank');    // For comparing grades
        $table->timestamps();
    });
    }

    public function down()
    {
        Schema::dropIfExists('grades');
    }
};
