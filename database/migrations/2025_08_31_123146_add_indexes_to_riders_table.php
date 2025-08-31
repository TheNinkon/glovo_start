<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('riders', function (Blueprint $table) {
            // Añadir índices a las columnas que se usarán para filtrar y buscar
            $table->index('status');
            $table->index('name');
        });
    }

    public function down(): void
    {
        Schema::table('riders', function (Blueprint $table) {
            $table->dropIndex(['status']);
            $table->dropIndex(['name']);
        });
    }
};
