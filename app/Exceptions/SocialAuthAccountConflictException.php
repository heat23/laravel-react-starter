<?php

namespace App\Exceptions;

use App\Models\User;

class SocialAuthAccountConflictException extends \RuntimeException
{
    public function __construct(public readonly User $existingUser)
    {
        parent::__construct('oauth-email-conflict-requires-linking');
    }
}
