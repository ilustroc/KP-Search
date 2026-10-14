<?php

namespace Tests\Feature\Console;

use App\Models\SearchContext;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Tests\TestCase;

class PruneExpiredSearchContextsTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_deletes_only_expired_search_contexts(): void
    {
        $expired = SearchContext::factory()->expired()->create();
        $active = SearchContext::factory()->create();

        $this->artisan('search:contexts:prune')->assertSuccessful();

        $this->assertModelMissing($expired);
        $this->assertModelExists($active);
    }
}
