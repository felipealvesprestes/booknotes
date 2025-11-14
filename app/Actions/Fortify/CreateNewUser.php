<?php

namespace App\Actions\Fortify;

use App\Models\User;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Laravel\Fortify\Contracts\CreatesNewUsers;

class CreateNewUser implements CreatesNewUsers
{
    use PasswordValidationRules;

    /**
     * Validate and create a newly registered user.
     *
     * @param  array<string, string>  $input
     */
    public function create(array $input): User
    {
        Validator::make($input, [
            'name' => ['required', 'string', 'max:255'],
            'email' => [
                'required',
                'string',
                'email',
                'max:255',
                Rule::unique(User::class),
            ],
            'password' => $this->passwordRules(),
        ])->validate();

        $trialDays = (int) config('services.stripe.trial_days', 14);

        $user = User::create([
            'name' => $input['name'],
            'email' => $input['email'],
            'password' => $input['password'],
            'locale' => config('localization.default', 'pt_BR'),
            'trial_starts_at' => now(),
            'trial_ends_at' => now()->addDays($trialDays),
        ]);

        if ($this->shouldGrantLifetimeAccess($user->email)) {
            $user->forceFill([
                'is_lifetime' => true,
                'trial_ends_at' => null,
            ])->save();
        }

        return $user;
    }

    private function shouldGrantLifetimeAccess(string $email): bool
    {
        $emails = array_map('strtolower', config('services.stripe.lifetime_emails', []));

        return in_array(strtolower($email), $emails, true);
    }
}
