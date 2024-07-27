<?php

use App\Models\Faculty;
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
        Schema::create('faculty_education_days', function (Blueprint $table) {
            $table->id();
            $table->date('day');
            $table->foreignIdFor(Faculty::class);
            $table->bigInteger('all_students')->default(0);
            $table->bigInteger('come_students')->default(0);
            $table->bigInteger('late_students')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('faculty_education_days');
    }
};
