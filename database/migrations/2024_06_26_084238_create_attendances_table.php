<?php

use App\Models\Device;
use App\Models\Faculty;
use App\Models\Group;
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
        Schema::create('attendances', function (Blueprint $table) {
            $table->id();
            $table->morphs('attendanceable');
            $table->foreignIdFor(Group::class)->nullable();
            $table->foreignIdFor(Faculty::class);
            $table->enum('type',['in','out'])->default('in');
            $table->date('date');
            $table->time('time');
            $table->dateTime('date_time');
            $table->foreignIdFor(Device::class);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('attendances');
    }
};
