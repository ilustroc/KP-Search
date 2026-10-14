<?php

namespace Database\Factories;

use App\Models\AuditEvent;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<AuditEvent>
 */
class AuditEventFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'event_id' => (string) Str::uuid(),
            'user_id' => User::factory(),
            'search_context_id' => null,
            'event_type' => 'test.event',
            'module' => null,
            'outcome' => 'success',
            'document_hmac' => null,
            'ip_address' => '127.0.0.1',
            'user_agent' => 'PHPUnit',
            'request_id' => (string) Str::uuid(),
            'metadata' => [],
            'occurred_at' => now(),
        ];
    }
}
