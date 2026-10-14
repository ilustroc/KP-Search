<?php

namespace App\Models;

use Database\Factories\SearchContextFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'user_id',
    'token_hash',
    'document_type',
    'document_ciphertext',
    'document_hmac',
    'last_accessed_at',
    'expires_at',
])]
#[Hidden(['token_hash', 'document_ciphertext', 'document_hmac'])]
class SearchContext extends Model
{
    /** @use HasFactory<SearchContextFactory> */
    use HasFactory, HasUlids;

    public $incrementing = false;

    protected $keyType = 'string';

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function isExpired(): bool
    {
        return $this->expires_at->isPast();
    }

    protected function casts(): array
    {
        return [
            'document_ciphertext' => 'encrypted',
            'last_accessed_at' => 'datetime',
            'expires_at' => 'datetime',
        ];
    }
}
