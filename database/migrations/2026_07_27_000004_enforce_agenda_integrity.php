<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('agendas')
            ->where('publication_status', 'draft')
            ->whereNotNull('published_at')
            ->update(['published_at' => null]);

        DB::table('agendas')
            ->where('publication_status', 'published')
            ->where(function ($query): void {
                $query
                    ->whereNull('published_at')
                    ->orWhere(function ($query): void {
                        $query
                            ->whereNotNull('ends_at')
                            ->whereColumn('published_at', '>', 'ends_at');
                    });
            })
            ->update([
                'publication_status' => 'draft',
                'published_at' => null,
            ]);

        DB::statement(
            "ALTER TABLE agendas
                ADD CONSTRAINT agendas_event_status_check
                CHECK (event_status IN ('scheduled', 'ongoing', 'completed', 'cancelled'))"
        );
        DB::statement(
            "ALTER TABLE agendas
                ADD CONSTRAINT agendas_publication_status_check
                CHECK (publication_status IN ('draft', 'published'))"
        );
        DB::statement(
            'ALTER TABLE agendas
                ADD CONSTRAINT agendas_schedule_order_check
                CHECK (ends_at IS NULL OR ends_at > starts_at)'
        );
        DB::statement(
            "ALTER TABLE agendas
                ADD CONSTRAINT agendas_publication_timestamp_check
                CHECK (
                    (publication_status = 'draft' AND published_at IS NULL)
                    OR (
                        publication_status = 'published'
                        AND published_at IS NOT NULL
                        AND (ends_at IS NULL OR published_at <= ends_at)
                    )
                )"
        );
    }

    public function down(): void
    {
        DB::statement('ALTER TABLE agendas DROP CHECK agendas_publication_timestamp_check');
        DB::statement('ALTER TABLE agendas DROP CHECK agendas_schedule_order_check');
        DB::statement('ALTER TABLE agendas DROP CHECK agendas_publication_status_check');
        DB::statement('ALTER TABLE agendas DROP CHECK agendas_event_status_check');
    }
};
