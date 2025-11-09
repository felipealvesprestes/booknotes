<?php

use App\Http\Controllers\NoteExportDownloadController;
use App\Livewire\Dashboard\Overview as DashboardOverview;
use App\Livewire\Disciplines\CreateDiscipline;
use App\Livewire\Disciplines\EditDiscipline;
use App\Livewire\Disciplines\Index as DisciplineIndex;
use App\Livewire\Disciplines\ShowDiscipline;
use App\Livewire\Notes\CreateNote;
use App\Livewire\Notes\ExportNotes;
use App\Livewire\Notes\EditNote;
use App\Livewire\Notes\Index as NoteIndex;
use App\Http\Controllers\PdfDocumentStreamController;
use App\Livewire\Notes\Library as NotesLibrary;
use App\Livewire\Notes\ShowNote;
use App\Livewire\Pdfs\Library as PdfLibrary;
use App\Livewire\Logs\Index as LogsIndex;
use App\Livewire\Notebooks\CreateNotebook;
use App\Livewire\Notebooks\EditNotebook;
use App\Livewire\Notebooks\Index as NotebookIndex;
use App\Livewire\Notebooks\ShowNotebook;
use App\Livewire\Study\Index as StudyIndex;
use App\Livewire\Settings\Billing;
use App\Livewire\Settings\Language;
use App\Livewire\Settings\Password;
use App\Livewire\Settings\Profile;
use App\Livewire\Settings\TwoFactor;
use Illuminate\Support\Facades\Route;
use Laravel\Fortify\Features;
use Laravel\Cashier\Http\Controllers\WebhookController;

Route::get('/', function () {
    return view('welcome');
})->name('home');

Route::view('politica-de-privacidade', 'privacy')->name('privacy');

Route::get('dashboard', DashboardOverview::class)
    ->middleware(['auth', 'verified', 'subscribed'])
    ->name('dashboard');

Route::middleware(['auth'])->group(function () {
    Route::redirect('settings', 'settings/profile');

    Route::get('settings/profile', Profile::class)->name('profile.edit');
    Route::get('settings/language', Language::class)->name('settings.language');
    Route::get('settings/password', Password::class)->name('user-password.edit');
    Route::get('settings/billing', Billing::class)->name('settings.billing');

    Route::get('settings/two-factor', TwoFactor::class)
        ->middleware(
            when(
                Features::canManageTwoFactorAuthentication()
                    && Features::optionEnabled(Features::twoFactorAuthentication(), 'confirmPassword'),
                ['password.confirm'],
                [],
            ),
        )
        ->name('two-factor.show');

    Route::middleware(['subscribed'])->group(function () {
        Route::get('notebooks', NotebookIndex::class)->name('notebooks.index');
        Route::get('notebooks/create', CreateNotebook::class)->name('notebooks.create');
        Route::get('notebooks/{notebook}/edit', EditNotebook::class)->name('notebooks.edit');
        Route::get('notebooks/{notebook}', ShowNotebook::class)->name('notebooks.show');

        Route::get('study', StudyIndex::class)->name('study.index');

        Route::get('disciplines', DisciplineIndex::class)->name('disciplines.index');
        Route::get('disciplines/create', CreateDiscipline::class)->name('disciplines.create');
        Route::get('disciplines/{discipline}/edit', EditDiscipline::class)->name('disciplines.edit');
        Route::get('disciplines/{discipline}', ShowDiscipline::class)->name('disciplines.show');

        Route::get('disciplines/{discipline}/notes', NoteIndex::class)->name('notes.index');
        Route::get('disciplines/{discipline}/notes/create', CreateNote::class)->name('notes.create');
        Route::get('disciplines/{discipline}/notes/{note}/edit', EditNote::class)->name('notes.edit');
        Route::get('disciplines/{discipline}/notes/{note}', ShowNote::class)->name('notes.show');

        Route::get('notes/export', ExportNotes::class)->name('notes.export');
        Route::get('notes/export/{noteExport}', NoteExportDownloadController::class)->name('notes.export.download');

        Route::get('notes', NotesLibrary::class)->name('notes.library');

        Route::get('pdfs', PdfLibrary::class)->name('pdfs.index');
        Route::get('pdfs/{pdfDocument}/preview', PdfDocumentStreamController::class)->name('pdfs.preview');

        Route::get('logs', LogsIndex::class)->name('logs.index');
    });
});

Route::post('stripe/webhook', [WebhookController::class, 'handleWebhook'])
    ->name('stripe.webhook');
