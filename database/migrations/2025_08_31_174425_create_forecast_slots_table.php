<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('forecast_slots', function (Blueprint $table) {
            $table->id();
            $table->foreignId('forecast_id')->constrained('forecasts')->onDelete('cascade');
            $table->date('date')->index();
            $table->time('start_time');
            $table->time('end_time');
            $table->unsignedInteger('capacity');
            $table->timestamps();
            $table->unique(['forecast_id', 'date', 'start_time', 'end_time']);
        });
    }
    public function down(): void { Schema::dropIfExists('forecast_slots'); }
};
