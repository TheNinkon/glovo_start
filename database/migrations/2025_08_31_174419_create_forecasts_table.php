<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('forecasts', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->date('week_start')->index();
            $table->date('week_end');
            $table->dateTime('selection_deadline_at');
            $table->enum('status', ['draft', 'published', 'closed'])->default('draft')->index();
            $table->timestamps();
        });
    }
    public function down(): void { Schema::dropIfExists('forecasts'); }
};
