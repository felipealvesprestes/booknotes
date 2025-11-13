<?php

namespace App\Livewire\Settings;

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Session;
use Illuminate\Validation\Rule;
use Livewire\Component;

class Profile extends Component
{
    public string $name = '';

    public string $email = '';

    public string $cpf = '';

    public string $cep = '';

    public string $address_street = '';

    public string $address_number = '';

    public string $address_neighborhood = '';

    public string $address_city = '';

    public string $address_state = '';

    public string $address_country = 'Brasil';

    /**
     * Mount the component.
     */
    public function mount(): void
    {
        $user = Auth::user();

        $this->name = (string) $user->name;
        $this->email = (string) $user->email;
        $this->cpf = (string) ($user->cpf ?? '');
        $this->cep = $this->formatCep((string) ($user->cep ?? ''));
        $this->address_street = (string) ($user->address_street ?? '');
        $this->address_number = (string) ($user->address_number ?? '');
        $this->address_neighborhood = (string) ($user->address_neighborhood ?? '');
        $this->address_city = (string) ($user->address_city ?? '');
        $this->address_state = (string) ($user->address_state ?? '');
        $this->address_country = (string) ($user->address_country ?? 'Brasil');
    }

    /**
     * Update the profile information for the currently authenticated user.
     */
    public function updateProfileInformation(): void
    {
        $user = Auth::user();

        $validated = $this->validate([
            'name' => ['required', 'string', 'max:255'],

            'email' => [
                'required',
                'string',
                'lowercase',
                'email',
                'max:255',
                Rule::unique(User::class)->ignore($user->id),
            ],
            'cep' => ['required', 'string', 'regex:/^\d{5}-\d{3}$/'],
            'cpf' => ['required', 'string', 'max:14'],
            'address_street' => ['required', 'string', 'max:255'],
            'address_number' => ['required', 'string', 'max:20'],
            'address_neighborhood' => ['required', 'string', 'max:255'],
            'address_city' => ['required', 'string', 'max:255'],
            'address_state' => ['required', 'string', 'max:255'],
            'address_country' => ['required', 'string', 'max:255'],
        ]);

        $user->fill($validated);

        if ($user->isDirty('email')) {
            $user->email_verified_at = null;
        }

        $user->save();

        $this->dispatch('profile-updated', name: $user->name);
    }

    public function updatedCep(?string $value): void
    {
        $this->cep = $this->formatCep($value);
    }

    public function lookupCep(): void
    {
        $digits = preg_replace('/\D/', '', $this->cep);

        if (strlen($digits) !== 8) {
            $this->addError('cep', __('Please enter a valid CEP.'));

            return;
        }

        try {
            $this->resetErrorBag(['cep']);
            $response = Http::timeout(5)->get("https://viacep.com.br/ws/{$digits}/json/");
        } catch (\Throwable $th) {
            $this->addError('cep', __('Unable to fetch address data at the moment.'));

            return;
        }

        if ($response->failed() || $response->json('erro')) {
            $this->addError('cep', __('CEP not found.'));

            return;
        }

        $data = $response->json();

        if (! empty($data['logradouro'])) {
            $this->address_street = (string) $data['logradouro'];
        }

        if (! empty($data['bairro'])) {
            $this->address_neighborhood = (string) $data['bairro'];
        }

        if (! empty($data['localidade'])) {
            $this->address_city = (string) $data['localidade'];
        }

        if (! empty($data['uf'])) {
            $this->address_state = (string) $data['uf'];
        }

        $this->address_country = 'Brasil';

        $this->cep = $this->formatCep($digits);
    }

    protected function formatCep(?string $value): string
    {
        $digits = preg_replace('/\D/', '', (string) $value);
        $digits = substr($digits, 0, 8);

        if (strlen($digits) <= 5) {
            return $digits;
        }

        return substr($digits, 0, 5) . '-' . substr($digits, 5);
    }

    /**
     * Send an email verification notification to the current user.
     */
    public function resendVerificationNotification(): void
    {
        $user = Auth::user();

        if ($user->hasVerifiedEmail()) {
            $this->redirectIntended(default: route('dashboard', absolute: false));

            return;
        }

        $user->sendEmailVerificationNotification();

        Session::flash('status', 'verification-link-sent');
    }
}
