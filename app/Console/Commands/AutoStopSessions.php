<?php

namespace App\Console\Commands;

use App\Models\SessionBilliard;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class AutoStopSessions extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'sessions:auto-stop';

    /**
     * The console command description.
     */
    protected $description = 'Automatically stop expired sessions';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $expiredSessions = SessionBilliard::needsAutoStop()->with('table')->get();

        if ($expiredSessions->isEmpty()) {
            $this->info('No expired sessions found.');
            return Command::SUCCESS;
        }

        $this->info("Found {$expiredSessions->count()} expired session(s).");

        foreach ($expiredSessions as $session) {
            DB::beginTransaction();
            try {
                $session->update(['status' => 'finished']);
                $session->table->update(['status' => 'available']);

                DB::commit();

                $this->info("Session #{$session->id} (Table {$session->table->table_number}) stopped.");
                Log::info("Auto-stopped session", [
                    'session_id' => $session->id,
                    'table_number' => $session->table->table_number,
                ]);

                // TODO: Broadcast event for real-time update
                // event(new SessionEnded($session));

            } catch (\Exception $e) {
                DB::rollBack();
                $this->error("Failed to stop session #{$session->id}: {$e->getMessage()}");
                Log::error("Failed to auto-stop session", [
                    'session_id' => $session->id,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        return Command::SUCCESS;
    }
}
