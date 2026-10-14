<?php

namespace Tests\Unit\Gold;

use App\Services\Gold\GoldContractChecker;
use Tests\TestCase;

class GoldContractCheckerTest extends TestCase
{
    public function test_reports_every_module_as_unconfigured_without_opening_gold_connection(): void
    {
        config()->set('search.gold_contract.modules', [
            'summary' => null,
            'contact' => null,
            'addresses' => null,
        ]);

        $results = app(GoldContractChecker::class)->check();

        $this->assertCount(3, $results);
        $this->assertSame(['unconfigured', 'unconfigured', 'unconfigured'], array_column($results, 'status'));
    }
}
