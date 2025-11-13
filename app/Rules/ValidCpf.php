<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class ValidCpf implements ValidationRule
{
    /**
     * Validate the CPF value.
     *
     * @param  Closure(string): void  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $digits = preg_replace('/\D/', '', (string) $value);

        if (strlen($digits) !== 11 || preg_match('/^(\\d)\\1{10}$/', $digits)) {
            $fail(__('validation.invalid_cpf'));

            return;
        }

        if (! $this->hasValidChecksum($digits)) {
            $fail(__('validation.invalid_cpf'));
        }
    }

    protected function hasValidChecksum(string $digits): bool
    {
        for ($t = 9; $t < 11; $t++) {
            $sum = 0;

            for ($i = 0; $i < $t; $i++) {
                $sum += (int) $digits[$i] * (($t + 1) - $i);
            }

            $result = ((10 * $sum) % 11) % 10;

            if ((int) $digits[$t] !== $result) {
                return false;
            }
        }

        return true;
    }
}
