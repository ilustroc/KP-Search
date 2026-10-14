<?php

namespace App\Services\Audit;

use App\Models\AuditEvent;
use App\Models\SearchContext;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class AuditRecorder
{
    /**
     * @param  array<string, bool|int|string|null>  $metadata
     */
    public function record(
        Request $request,
        string $eventType,
        string $outcome,
        ?SearchContext $searchContext = null,
        ?string $module = null,
        array $metadata = [],
        ?User $user = null,
    ): AuditEvent {
        return AuditEvent::query()->create([
            'event_id' => (string) Str::uuid(),
            'user_id' => ($user ?? $request->user())?->getKey(),
            'search_context_id' => $searchContext?->getKey(),
            'event_type' => $eventType,
            'module' => $module,
            'outcome' => $outcome,
            'document_hmac' => $searchContext?->document_hmac,
            'ip_address' => $request->ip(),
            'user_agent' => Str::limit((string) $request->userAgent(), 512, ''),
            'request_id' => (string) Str::uuid(),
            'metadata' => $metadata,
            'occurred_at' => now(),
        ]);
    }
}
