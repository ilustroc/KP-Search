<?php

namespace App\Actions\Search;

use App\Models\SearchContext;
use App\Models\User;
use App\Services\Audit\AuditRecorder;
use App\Services\Security\DocumentFingerprint;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class CreateSearchContext
{
    public function __construct(
        private DocumentFingerprint $documentFingerprint,
        private AuditRecorder $auditRecorder,
    ) {}

    /**
     * @return array{context: SearchContext, token: string}
     */
    public function handle(User $user, string $document, Request $request): array
    {
        return DB::transaction(function () use ($user, $document, $request): array {
            $token = Str::random(64);
            $ttlMinutes = max(1, (int) config('search.context_ttl_minutes', 15));

            $searchContext = SearchContext::query()->create([
                'user_id' => $user->getKey(),
                'token_hash' => hash('sha256', $token),
                'document_type' => mb_strlen($document) === 8 ? 'DNI' : 'RUC',
                'document_ciphertext' => $document,
                'document_hmac' => $this->documentFingerprint->make($document),
                'expires_at' => now()->addMinutes($ttlMinutes),
            ]);

            $this->auditRecorder->record(
                request: $request,
                eventType: 'search.created',
                outcome: 'success',
                searchContext: $searchContext,
                user: $user,
            );

            return ['context' => $searchContext, 'token' => $token];
        });
    }
}
