<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Services\Auth\TokenService;
use Illuminate\Console\Command;

final class CleanupExpiredTokens extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'tokens:cleanup 
                            {--dry-run : Show what would be deleted without actually deleting}
                            {--days=30 : Delete tokens older than X days}';

    /**
     * The console command description.
     */
    protected $description = 'Clean up expired and revoked OAuth2 tokens';

    public function __construct(
        private readonly TokenService $tokenService
    ) {
        parent::__construct();
    }

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $dryRun = $this->option('dry-run');
        $days = (int) $this->option('days');

        $this->info("🧹 Starting token cleanup process...");
        
        if ($dryRun) {
            $this->warn("🔍 DRY RUN MODE - No tokens will actually be deleted");
        }

        try {
            if ($dryRun) {
                // In dry run mode, just count what would be deleted
                $count = $this->tokenService->countExpiredTokens($days);
                $this->info("📊 Found {$count} expired/revoked tokens that would be deleted");
            } else {
                // Actually delete the tokens
                $count = $this->tokenService->cleanupExpiredTokens();
                $this->info("✅ Successfully cleaned up {$count} expired/revoked tokens");
            }

            return self::SUCCESS;

        } catch (\Exception $e) {
            $this->error("❌ Error during token cleanup: " . $e->getMessage());
            return self::FAILURE;
        }
    }
}
