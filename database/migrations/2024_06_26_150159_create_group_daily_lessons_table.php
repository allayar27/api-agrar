<?php

use App\Models\GroupDaily;
use App\Models\StudentLesson;
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
        Schema::create('group_daily_lessons', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(StudentLesson::class);
            $table->foreignIdFor(GroupDaily::class);
            $table->integer('all_students');
            $table->integer('come_students');
            $table->integer('late_students');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('group_daily_lessons');
    }
};
