<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class FullName implements ValidationRule
{
    /**
     * Ensure the provided value contains at least two words.
     *
     * @param  Closure(string): void  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $words = preg_split('/\s+/u', trim((string) $value), -1, PREG_SPLIT_NO_EMPTY);

        if (count($words) < 2) {
            $fail(__('validation.full_name'));
        }
    }
}
