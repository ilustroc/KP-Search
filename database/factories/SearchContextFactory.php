<?php

namespace Database\Factories;

use App\Models\SearchContext;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<SearchContext>
 */
class SearchContextFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $document = fake()->numerify('########');

        return [
            'user_id' => User::factory(),
            'token_hash' => hash('sha256', Str::random(64)),
            'document_type' => 'DNI',
            'document_ciphertext' => $document,
            'document_hmac' => hash_hmac('sha256', $document, 'factory-only-key'),
            'last_accessed_at' => null,
            'expires_at' => now()->addMinutes(15),
        ];
    }

    public function expired(): static
    {
        return $this->state(fn (array $attributes): array => [
            'expires_at' => now()->subMinute(),
        ]);
    }
}
