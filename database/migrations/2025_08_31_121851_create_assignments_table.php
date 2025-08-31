<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('assignments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('rider_id')->constrained('riders')->onDelete('cascade');
            $table->foreignId('account_id')->constrained('accounts')->onDelete('cascade');
            $table->date('start_date');
            $table->date('end_date')->nullable();
            $table->timestamps();

            $table->index(['rider_id', 'account_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('assignments');
    }
};
