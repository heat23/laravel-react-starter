<?php

namespace App\Exceptions;

use App\Models\User;
use Exception;

class SocialAuthAccountConflictException extends Exception
{
    public function __construct(public readonly User $existingUser)
    {
        parent::__construct('oauth-email-conflict-requires-linking');
    }
}
