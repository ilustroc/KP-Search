<?php

namespace App\Services\Security;

use LogicException;

class DocumentFingerprint
{
    public function make(string $document): string
    {
        $key = config('search.audit_hmac_key');

        if (! is_string($key) || mb_strlen($key) < 32) {
            throw new LogicException('SEARCH_AUDIT_HMAC_KEY must contain at least 32 characters.');
        }

        return hash_hmac('sha256', $document, $key);
    }
}
