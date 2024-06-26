<?php

use App\Models\TeacherSchedule;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('teacher_schedule_days', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(TeacherSchedule::class);
            $table->time('time_in');
            $table->time('time_out');
            $table->date('date');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('teacher_schedule_days');
    }
};
