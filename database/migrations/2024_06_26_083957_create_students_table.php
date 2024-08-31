<?php

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
        Schema::create('students', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->bigInteger('hemis_id')->unique();
            $table->string('firstname')->nullable();
            $table->string('secondname')->nullable();
            $table->string('thirdname')->nullable();;
            $table->foreignIdFor(Group::class);
            $table->foreignIdFor(Faculty::class);
            $table->dateTime('start_date')->default(now());
            $table->dateTime('end_date')->nullable();
            $table->timestamps();

            $table->index('name');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('students');
    }
};
