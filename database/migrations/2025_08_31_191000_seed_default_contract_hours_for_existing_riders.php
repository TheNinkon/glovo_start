<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        DB::table('riders')
            ->where(function($q){ $q->whereNull('contract_hours_per_week')->orWhere('contract_hours_per_week', 0); })
            ->update(['contract_hours_per_week' => 30]);
    }

    public function down(): void
    {
        // Intentionally left blank
    }
};

