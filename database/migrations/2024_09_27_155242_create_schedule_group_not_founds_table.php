<?php

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
        Schema::create('schedule_group_not_founds', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(Group::class);
            $table->date('day');
            $table->integer('counter')->default(0);
            $table->json('students')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('schedule_group_not_founds');
    }
};
