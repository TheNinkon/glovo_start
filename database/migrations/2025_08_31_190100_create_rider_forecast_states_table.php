<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('rider_forecast_states', function (Blueprint $table) {
            $table->id();
            $table->foreignId('rider_id')->constrained('riders')->onDelete('cascade');
            $table->foreignId('forecast_id')->constrained('forecasts')->onDelete('cascade');
            $table->unsignedInteger('required_weekly_minutes');
            $table->unsignedInteger('reserved_weekly_minutes')->default(0);
            $table->dateTime('locked_at')->nullable();
            $table->unsignedInteger('wildcards_remaining')->default(5);
            $table->timestamps();
            $table->unique(['rider_id', 'forecast_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('rider_forecast_states');
    }
};

