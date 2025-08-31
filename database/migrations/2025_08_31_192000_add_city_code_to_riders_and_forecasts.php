<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('riders', function (Blueprint $table) {
            $table->string('city_code', 10)->nullable()->after('contract_hours_per_week');
            $table->index('city_code');
        });
        Schema::table('forecasts', function (Blueprint $table) {
            $table->string('city_code', 10)->nullable()->after('status');
            $table->index('city_code');
        });
    }

    public function down(): void
    {
        Schema::table('riders', function (Blueprint $table) {
            $table->dropIndex(['city_code']);
            $table->dropColumn('city_code');
        });
        Schema::table('forecasts', function (Blueprint $table) {
            $table->dropIndex(['city_code']);
            $table->dropColumn('city_code');
        });
    }
};

