<?php

namespace App\Console\Commands;

use App\Models\AdminActivityLog;
use Illuminate\Console\Command;

class PruneAdminActivityLogs extends Command
{
    protected $signature = 'admin:prune-activity-logs {--days= : Override masa retensi dalam hari}';

    protected $description = 'Menghapus activity log admin yang melewati masa retensi';

    public function handle(): int
    {
        $days = max(30, (int) ($this->option('days') ?: config('admin.activity_log_retention_days', 180)));
        $deleted = AdminActivityLog::query()
            ->where('created_at', '<', now()->subDays($days))
            ->delete();

        $this->info("{$deleted} activity log yang lebih lama dari {$days} hari dihapus.");

        return self::SUCCESS;
    }
}
