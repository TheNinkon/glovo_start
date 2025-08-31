<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('riders', function (Blueprint $table) {
            $table->unsignedInteger('contract_hours_per_week')->default(0)->after('user_id');
            $table->index('contract_hours_per_week');
        });
    }

    public function down(): void
    {
        Schema::table('riders', function (Blueprint $table) {
            $table->dropIndex(['contract_hours_per_week']);
            $table->dropColumn('contract_hours_per_week');
        });
    }
};

