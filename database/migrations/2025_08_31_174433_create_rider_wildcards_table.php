<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('rider_wildcards', function (Blueprint $table) {
            $table->id();
            $table->foreignId('rider_id')->constrained('riders')->onDelete('cascade');
            $table->foreignId('forecast_id')->constrained('forecasts')->onDelete('cascade');
            $table->unsignedInteger('tokens')->default(0);
            $table->timestamps();
            $table->unique(['rider_id', 'forecast_id']);
        });
    }
    public function down(): void { Schema::dropIfExists('rider_wildcards'); }
};
