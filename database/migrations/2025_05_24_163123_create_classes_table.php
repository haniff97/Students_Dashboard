<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('classes', function (Blueprint $table) {
            $table->id();
            $table->string('name');           // class name (e.g. "Class A", "Math 101")
            $table->string('email')->unique(); // class email (optional - similar to students)
            $table->string('class');           // could be a category or section
            $table->integer('age')->nullable(); // optional field
            $table->integer('marks')->nullable(); // optional field
            $table->string('grade')->nullable();  // optional field
            $table->integer('year')->nullable();  // optional field
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('classes');
    }
};
