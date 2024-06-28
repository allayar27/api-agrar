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
            $table->string('lastname');
            $table->string('fathername');
            $table->foreignIdFor(Faculty::class)->nullable();
            $table->enum('type',['teacher','worker']);
            $table->foreignIdFor(TeacherSchedule::class);
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
