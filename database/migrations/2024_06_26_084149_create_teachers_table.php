<?php

use App\Models\Faculty;
use App\Models\TeacherSchedule;
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
        Schema::create('teachers', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('firstname')->nullable();
            $table->string('secondname')->nullable();
            $table->string('thirdname')->nullable();
            $table->foreignIdFor(TeacherSchedule::class);
            $table->enum('kind',['employee','teacher','other','administrative' ])->default('teacher');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('teachers');
    }
};
