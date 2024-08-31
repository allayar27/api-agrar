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
        Schema::create('employee_education_days', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('all_teachers');
            $table->bigInteger('come_teachers');
            $table->bigInteger('late_teachers');
            $table->date('date');
            $table->enum('type',['work_day','none']);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('employee_education_days');
    }
};
