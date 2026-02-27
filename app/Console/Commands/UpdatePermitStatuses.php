<?php

namespace App\Console\Commands;

use App\Models\Permit;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Symfony\Component\Console\Command\Command as SymfonyCommand;

class UpdatePermitStatuses extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'permits:update-statuses';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update permit statuses based on visit dates - today becomes in progress, past dates become cancelled (unless completed)';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting permit status update...');
        
        $today = now()->startOfDay();
        $updatedCount = 0;
        
        // Update permits with visit date today to "In Progress" (status 1)
        // Only if they are currently "Scheduled" (status 0)
        $todayPermits = Permit::whereDate('date_of_visit', $today)
            ->where('status', 0) // Only update Scheduled permits
            ->update(['status' => 1]); // Set to In Progress
            
        $updatedCount += $todayPermits;
        $this->info("Updated {$todayPermits} permits to 'In Progress' for today.");
        
        // Update permits with past visit dates to "Cancelled" (status 3)
        // Only if they are not already "Completed" (status 2) or "Cancelled" (status 3)
        $pastPermits = Permit::whereDate('date_of_visit', '<', $today)
            ->whereNotIn('status', [2, 3]) // Exclude Completed and Cancelled
            ->update(['status' => 3]); // Set to Cancelled
            
        $updatedCount += $pastPermits;
        $this->info("Updated {$pastPermits} permits to 'Cancelled' for past dates.");
        
        $this->info("Permit status update completed. Total permits updated: {$updatedCount}");
        
        // Log the update for audit purposes
        Log::info('Permit statuses updated', [
            'today_in_progress' => $todayPermits,
            'past_cancelled' => $pastPermits,
            'total_updated' => $updatedCount,
            'run_date' => $today->toDateString()
        ]);
        
        return SymfonyCommand::SUCCESS;
    }
}
