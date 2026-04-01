<?php

namespace App\Console\Commands;

use App\Models\Permit;
use App\Models\PermitLog;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Symfony\Component\Console\Command\Command as SymfonyCommand;

class UpdatePermitStatuses extends Command
{
    protected $signature = 'permits:update-statuses';
    protected $description = 'Activate scheduled permits for today, auto-complete in-progress permits after 24hrs, cancel missed permits.';

    public function handle()
    {
        $this->info('Starting permit status update...');

        $today    = now()->startOfDay();
        $tomorrow = now()->endOfDay();

        // ----------------------------------------------------------------
        // 1. Scheduled → In Progress (visit date is today)
        // ----------------------------------------------------------------
        $scheduled = Permit::where('status', Permit::STATUS_SCHEDULED)
            ->whereDate('date_of_visit', $today)
            ->get();

        foreach ($scheduled as $permit) {
            $permit->update(['status' => Permit::STATUS_IN_PROGRESS]);
            PermitLog::create([
                'permit_id'  => $permit->id,
                'status'     => Permit::STATUS_IN_PROGRESS,
                'action'     => PermitLog::ACTION_ACCEPTED,
                'changed_by' => $permit->created_by,
                'message'    => 'Automatically activated — visit date reached.',
                'red_alert'  => (bool) $permit->red_alert,
            ]);
        }

        $this->info("Activated {$scheduled->count()} permits to In Progress.");

        // ----------------------------------------------------------------
        // 2. In Progress → Completed (auto-complete after 24hrs)
        //    Only if: status is In Progress, visit date was yesterday or earlier,
        //    and not On Hold
        // ----------------------------------------------------------------
        $autoComplete = Permit::where('status', Permit::STATUS_IN_PROGRESS)
            ->whereDate('date_of_visit', '<', $today)
            ->get();

        foreach ($autoComplete as $permit) {
            $permit->update([
                'status'       => Permit::STATUS_COMPLETED,
                'completed_at' => now(),
            ]);
            PermitLog::create([
                'permit_id'  => $permit->id,
                'status'     => Permit::STATUS_COMPLETED,
                'action'     => PermitLog::ACTION_COMPLETED,
                'changed_by' => $permit->created_by,
                'message'    => 'Automatically completed — 24-hour visit period has elapsed.',
                'red_alert'  => (bool) $permit->red_alert,
            ]);
        }

        $this->info("Auto-completed {$autoComplete->count()} permits.");

        // ----------------------------------------------------------------
        // 3. Scheduled → Cancelled (missed — visit date passed, never activated)
        //    Only Scheduled permits with past dates (shouldn't normally happen
        //    since step 1 activates them, but handles edge cases like downtime)
        // ----------------------------------------------------------------
        $missed = Permit::where('status', Permit::STATUS_SCHEDULED)
            ->whereDate('date_of_visit', '<', $today)
            ->get();

        foreach ($missed as $permit) {
            $permit->update(['status' => Permit::STATUS_CANCELLED]);
            PermitLog::create([
                'permit_id'  => $permit->id,
                'status'     => Permit::STATUS_CANCELLED,
                'action'     => PermitLog::ACTION_CANCELLED,
                'changed_by' => $permit->created_by,
                'message'    => 'Automatically cancelled — visit date passed without activation.',
                'red_alert'  => (bool) $permit->red_alert,
            ]);
        }

        $this->info("Cancelled {$missed->count()} missed permits.");

        $total = $scheduled->count() + $autoComplete->count() + $missed->count();
        $this->info("Done. Total permits updated: {$total}");

        Log::info('Permit statuses updated', [
            'activated'      => $scheduled->count(),
            'auto_completed' => $autoComplete->count(),
            'missed_cancelled' => $missed->count(),
            'total'          => $total,
            'run_at'         => now()->toDateTimeString(),
        ]);

        return SymfonyCommand::SUCCESS;
    }
}