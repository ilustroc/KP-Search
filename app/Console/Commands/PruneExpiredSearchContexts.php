<?php

namespace App\Console\Commands;

use App\Models\SearchContext;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('search:contexts:prune')]
#[Description('Delete expired temporary search contexts')]
class PruneExpiredSearchContexts extends Command
{
    public function handle(): int
    {
        $deleted = SearchContext::query()
            ->where('expires_at', '<=', now())
            ->delete();

        $this->info("Expired search contexts deleted: {$deleted}");

        return self::SUCCESS;
    }
}
