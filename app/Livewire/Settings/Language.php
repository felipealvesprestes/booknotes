<?php

namespace App\Livewire\Settings;

use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Livewire\Component;

class Language extends Component
{
    public string $locale = '';

    /**
     * @var array<string, array<string, string>>
     */
    public array $locales = [];

    /**
     * Mount the component.
     */
    public function mount(): void
    {
        $this->locales = config('localization.supported', []);
        $this->locale = Auth::user()->locale ?? config('app.locale', 'en');
    }

    /**
     * Persist the selected language for the current user.
     */
    public function updateLanguage(): void
    {
        $available = array_keys($this->locales);

        $validated = $this->validate([
            'locale' => ['required', Rule::in($available)],
        ]);

        $user = Auth::user();

        $user->forceFill(['locale' => $validated['locale']])->save();

        App::setLocale($validated['locale']);
        session()->put('locale', $validated['locale']);
        session()->flash('language_reload_notice', __('Language preference updated. Page reloaded to apply the new language.'));

        $this->redirectRoute('settings.language');
    }

    public function render()
    {
        return view('livewire.settings.language', [
            'locales' => $this->locales,
        ]);
    }
}
