<?php

namespace App\Rules;

use App\Webhooks\UrlPolicy;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Translation\PotentiallyTranslatedString;

class PublicHttpsUrl implements ValidationRule
{
    /**
     * Run the validation rule.
     *
     * @param  Closure(string, ?string=): PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (! is_string($value)) {
            $fail('The :attribute must be a valid URL.');

            return;
        }

        $policy = new UrlPolicy;
        $error = $policy->check($value);

        if ($error !== null) {
            $fail("The :attribute is not allowed: {$error}.");
        }
    }
}
