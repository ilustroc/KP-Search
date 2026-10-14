<?php

namespace App\Services\Search;

use App\Models\SearchContext;
use App\Models\User;

class SearchContextResolver
{
    public function resolveForUser(string $token, User $user): SearchContext
    {
        abort_unless(preg_match('/\A[A-Za-z0-9]{64}\z/', $token) === 1, 404);

        $searchContext = SearchContext::query()
            ->whereBelongsTo($user)
            ->where('token_hash', hash('sha256', $token))
            ->where('expires_at', '>', now())
            ->firstOrFail();

        $searchContext->forceFill(['last_accessed_at' => now()])->saveQuietly();

        return $searchContext;
    }
}
