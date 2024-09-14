<?php

use App\Models\Device;
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
        Schema::create('users_logs', function (Blueprint $table) {
            $table->id();
            $table->string('full_name');
            $table->bigInteger('hemis_id');
            $table->string('PersonGroup');
            $table->dateTime('date_time')->nullable();
            $table->string('device_name')->nullable();
            $table->foreignIdFor(Device::class)->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users_logs');
    }
};
