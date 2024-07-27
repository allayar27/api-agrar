<?php

use App\Models\Group;
use App\Models\StudentSchedule;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('student_schedule_days', function (Blueprint $table) {
            $table->id();
            $table->enum('day', ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday']);
            $table->date('date');
            $table->foreignIdFor(StudentSchedule::class);
            $table->foreignIdFor(Group::class);
            $table->time('time_in');
            $table->time('time_out');
            $table->unsignedBigInteger('enter_building_id');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('student_schedule_days');
    }
};