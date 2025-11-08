## Localization strategy

This project now supports per-user language preferences that are applied on every request. The high-level pieces are:

1. `users.locale` stores the preferred language for each account.
2. `App\Http\Middleware\SetUserLocale` runs on every web request and sets the current locale using the persisted preference (falling back to the default locale).
3. `config/localization.php` centralizes the list of supported locales and their display labels.
4. `resources/lang/pt_BR.json` contains the first batch of Portuguese translations. Strings that are not translated yet will gracefully fall back to English.
5. The *Settings -> Language* Livewire screen lets users pick between English and Portuguese.

### How to add or update strings

1. Wrap any hard-coded text in PHP/Blade with the `__()` helper (or `@lang`) so Laravel knows the string is translatable.
2. Add the translated text to `resources/lang/<locale>.json`. JSON files work best for "key equals original string" style translations that are already used across the UI.
3. If you add structured translation keys instead, you can create `resources/lang/<locale>/app.php` (or a similar file) and return an associative array.
4. Run the UI in the new locale and verify the translated copy appears as expected (Livewire's `@entangle` data updates immediately after saving the language preference).

### Outstanding work

- **Translate the remaining strings:** Running a quick search for `__(` shows roughly 450 UI strings that still need Portuguese copy. The most visible areas (navigation and settings) are covered now, but everything else will fall back to English until we populate `pt_BR.json` with the remaining entries.
- **QA the full workflow in Portuguese:** After the translation file is complete, do a full smoke test (dashboard, notes, notebooks, study sessions, exports, etc.) to make sure the UI stays readable and nothing overflows.
- **Plan for future locales:** Adding a third language only requires: (a) extending `config/localization.php`, (b) updating the `Language` settings view if you want to surface additional context, and (c) filling out a new `resources/lang/<locale>.json`.
- **Content updates:** Any time we introduce new copy, repeat the steps above so that English and Portuguese stay in sync.

### Adding another locale (example workflow)

1. Append the new locale code and labels to `config/localization.php`.
2. Create `resources/lang/<locale>.json` (you can duplicate `en.json` or `pt_BR.json` as a starting point).
3. Update the translations with the new language.
4. Visit *Settings -> Language* and you should see the additional option immediately.
