<?php

namespace App\Console\Commands;

use App\Services\Gold\GoldContractChecker;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('search:gold-contract:check')]
#[Description('Validate the configured Gold contract without querying business data')]
class CheckGoldContract extends Command
{
    public function handle(GoldContractChecker $goldContractChecker): int
    {
        $results = $goldContractChecker->check();

        $this->table(
            ['Module', 'Object', 'Status', 'Detail'],
            array_map(
                fn (array $result): array => [
                    $result['module'],
                    $result['object'],
                    $result['status'],
                    $result['detail'],
                ],
                $results,
            ),
        );

        return collect($results)->every(
            fn (array $result): bool => $result['status'] === 'available',
        ) ? self::SUCCESS : self::FAILURE;
    }
}
