<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('rider_schedules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('rider_id')->constrained('riders')->onDelete('cascade');
            $table->foreignId('forecast_slot_id')->constrained('forecast_slots')->onDelete('cascade');
            $table->enum('status', ['reserved', 'cancelled'])->default('reserved');
            $table->timestamps();
            $table->unique(['rider_id', 'forecast_slot_id']);
        });
    }
    public function down(): void { Schema::dropIfExists('rider_schedules'); }
};
