<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $activeIds = DB::table('management_periods')
            ->where('is_active', true)
            ->orderByDesc('start_year')
            ->orderByDesc('id')
            ->pluck('id');

        if ($activeIds->count() > 1) {
            DB::table('management_periods')
                ->whereIn('id', $activeIds->slice(1)->all())
                ->update(['is_active' => false]);
        }

        // MySQL has no partial unique index. A generated nullable key provides
        // the same guarantee: only the active row produces the value 1.
        DB::statement(<<<'SQL'
            ALTER TABLE management_periods
            ADD active_guard TINYINT
            GENERATED ALWAYS AS (CASE WHEN is_active = 1 THEN 1 ELSE NULL END) STORED,
            ADD UNIQUE INDEX management_periods_single_active_unique (active_guard)
        SQL);
    }

    public function down(): void
    {
        DB::statement(<<<'SQL'
            ALTER TABLE management_periods
            DROP INDEX management_periods_single_active_unique,
            DROP COLUMN active_guard
        SQL);
    }
};
