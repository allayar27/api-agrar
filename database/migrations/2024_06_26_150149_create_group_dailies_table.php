<?php

use App\Models\Group;
use App\Models\StudentScheduleDay;
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
        Schema::create('group_dailies', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(Group::class);
            $table->integer('lessons');
            $table->date('date');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('group_dailies');
    }
};
