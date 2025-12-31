<?php

namespace App\Livewire\Onboarding;

use App\Exceptions\AiFlashcardGenerationException;
use App\Exceptions\AiFlashcardsLimitException;
use App\Livewire\Concerns\HandlesAiFlashcardGenerator;
use App\Models\Discipline;
use App\Models\FlashcardSession;
use App\Models\Log;
use App\Models\Note;
use App\Models\Notebook;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Livewire\Component;

class StudyWizard extends Component
{
    use HandlesAiFlashcardGenerator;

    public ?Discipline $discipline = null;

    public int $step = 1;

    public string $notebookTitle = '';

    public string $disciplineTitle = '';

    public bool $showStudyModeModal = false;

    public string $selectedStudyMode = 'flashcards';

    public ?int $notebookId = null;

    public ?int $disciplineId = null;

    public ?int $redirectSessionId = null;

    public ?string $generationError = null;

    public bool $isGenerating = false;

    public int $generatedFlashcards = 0;

    protected $queryString = [
        'step' => ['except' => 1],
    ];

    public function mount(): void
    {
        $this->bootAiFlashcardGenerator();

        $preferredQuantity = 10;

        if (in_array($preferredQuantity, $this->aiQuantityOptions, true) && $this->aiRemainingToday >= $preferredQuantity) {
            $this->aiQuantity = $preferredQuantity;
        }

        $this->aiDescription = '';
    }

    public function updatedStep($value): void
    {
        $this->step = $this->normalizeStep((int) $value);
    }

    public function nextStep(): void
    {
        $this->validateCurrentStep();

        $this->step = $this->normalizeStep($this->step + 1);
    }

    public function previousStep(): void
    {
        $this->step = $this->normalizeStep($this->step - 1);
    }

    public function submit(): void
    {
        $this->validate($this->rulesForSubmission(), [], $this->validationAttributes());

        $this->generationError = null;
        $this->isGenerating = true;

        $user = Auth::user();

        if (! $user) {
            $this->generationError = __('Unable to generate your flashcards right now. Please try again.');
            $this->isGenerating = false;

            return;
        }

        $notebook = $this->findOrCreateNotebook();
        $discipline = $this->findOrCreateDiscipline($notebook);

        $this->discipline = $discipline;
        $this->notebookId = $notebook->id;
        $this->disciplineId = $discipline->id;

        try {
            $created = $this->generateFlashcards($discipline);
        } catch (AiFlashcardsLimitException $exception) {
            $this->generationError = $this->getErrorBag()->first('aiQuantity') ?? $exception->getMessage();
            $this->isGenerating = false;

            return;
        } catch (AiFlashcardGenerationException $exception) {
            $this->generationError = $exception->getMessage();
            $this->isGenerating = false;

            return;
        } catch (\Throwable) {
            $this->generationError = __('Unable to generate your flashcards right now. Please try again.');
            $this->isGenerating = false;

            return;
        }

        if ($created <= 0) {
            $this->generationError = __('ai_flashcards.save_error');
            $this->isGenerating = false;

            return;
        }

        $this->generatedFlashcards = $created;
        $this->redirectSessionId = $this->createFlashcardSession($discipline);

        $this->selectedStudyMode = 'flashcards';
        $this->showStudyModeModal = true;
        $this->isGenerating = false;
    }

    public function startStudy(): void
    {
        if (! $this->disciplineId) {
            return;
        }

        $routes = [
            'flashcards' => route('study.flashcards', array_filter([
                'discipline' => $this->disciplineId,
                'session' => $this->redirectSessionId,
            ])),
            'exercises' => route('study.exercises', ['disciplineId' => $this->disciplineId]),
            'simulated' => route('study.simulated', [
                'disciplineId' => $this->disciplineId,
                'scopeType' => 'discipline',
                'autostart' => 1,
            ]),
        ];

        $target = $routes[$this->selectedStudyMode] ?? $routes['flashcards'];

        $this->redirect($target, navigate: true);
    }

    public function render(): View
    {
        return view('livewire.onboarding.study-wizard', [
            'steps' => $this->wizardSteps(),
            'studyModes' => $this->studyModes(),
        ])->layout('layouts.app', [
            'title' => __('Create your first study'),
        ]);
    }

    protected function validateCurrentStep(): array
    {
        return $this->validate($this->rulesForStep($this->step), [], $this->validationAttributes());
    }

    protected function rulesForSubmission(): array
    {
        return array_merge(
            $this->rulesForStep(1),
            $this->rulesForStep(2),
            $this->rulesForStep(3),
            $this->rulesForStep(4),
        );
    }

    protected function rulesForStep(int $step): array
    {
        $quantityRule = Rule::in($this->aiQuantityOptions);

        return match ($step) {
            1 => [
                'notebookTitle' => ['required', 'string', 'max:255'],
            ],
            2 => [
                'disciplineTitle' => ['required', 'string', 'max:255'],
            ],
            3 => [
                'aiTopic' => ['required', 'string', 'min:4', 'max:255'],
                'aiDescription' => ['required', 'string', 'min:10', 'max:1200'],
            ],
            default => [
                'aiQuantity' => ['required', 'integer', $quantityRule],
            ],
        };
    }

    protected function validationAttributes(): array
    {
        return [
            'notebookTitle' => __('Notebook name'),
            'disciplineTitle' => __('Discipline name'),
            'aiTopic' => __('Topic'),
            'aiDescription' => __('Study focus'),
            'aiQuantity' => __('Number of flashcards to generate'),
        ];
    }

    protected function wizardSteps(): array
    {
        return [
            1 => [
                'label' => __('Notebook'),
                'description' => __('Organize where this study will live.'),
            ],
            2 => [
                'label' => __('Discipline'),
                'description' => __('Name the subject you are focusing on.'),
            ],
            3 => [
                'label' => __('Content focus'),
                'description' => __('Tell the AI what to generate.'),
            ],
            4 => [
                'label' => __('Flashcards'),
                'description' => __('Set the starting volume.'),
            ],
        ];
    }

    protected function studyModes(): array
    {
        return [
            [
                'value' => 'flashcards',
                'title' => __('Flashcards'),
                'description' => __('Review one card at a time with spaced practice.'),
            ],
            [
                'value' => 'exercises',
                'title' => __('Exercises'),
                'description' => __('Mix true or false, fill in the blanks, and multiple choice drills.'),
            ],
            [
                'value' => 'simulated',
                'title' => __('Simulated test'),
                'description' => __('Assemble a quick mock exam with your new flashcards.'),
            ],
        ];
    }

    protected function generateFlashcards(Discipline $discipline): int
    {
        $user = Auth::user();

        if (! $user) {
            return 0;
        }

        try {
            $this->aiUsageService()->ensureWithinLimit($user, $this->aiQuantity);
        } catch (AiFlashcardsLimitException $exception) {
            $this->handleAiLimitError($exception);

            throw $exception;
        }

        $flashcards = $this->generatorService()->generate(
            $user,
            $discipline,
            $this->aiTopic,
            $this->aiDescription,
            $this->aiQuantity,
        );

        $created = $this->persistAiFlashcards($flashcards);

        $this->aiUsageService()->increment($user, $created);
        $this->reloadAiUsage(adjustQuantity: true);

        return $created;
    }

    protected function findOrCreateNotebook(): Notebook
    {
        $title = trim($this->notebookTitle);

        $existing = Notebook::query()
            ->whereRaw('LOWER(title) = ?', [mb_strtolower($title)])
            ->first();

        if ($existing) {
            return $existing;
        }

        $notebook = Notebook::create([
            'title' => $title,
        ]);

        Log::create([
            'action' => 'notebook.created',
            'context' => [
                'notebook_id' => $notebook->id,
                'title' => $notebook->title,
            ],
        ]);

        return $notebook;
    }

    protected function findOrCreateDiscipline(Notebook $notebook): Discipline
    {
        $title = trim($this->disciplineTitle);

        $existing = Discipline::query()
            ->where('notebook_id', $notebook->id)
            ->whereRaw('LOWER(title) = ?', [mb_strtolower($title)])
            ->first();

        if ($existing) {
            return $existing;
        }

        $discipline = Discipline::create([
            'title' => $title,
            'notebook_id' => $notebook->id,
        ]);

        Log::create([
            'action' => 'discipline.created',
            'context' => [
                'discipline_id' => $discipline->id,
                'title' => $discipline->title,
                'notebook_id' => $discipline->notebook_id,
            ],
        ]);

        return $discipline;
    }

    protected function createFlashcardSession(Discipline $discipline): ?int
    {
        $noteIds = Note::query()
            ->where('is_flashcard', true)
            ->where('discipline_id', $discipline->id)
            ->orderByDesc('updated_at')
            ->pluck('id')
            ->shuffle()
            ->values()
            ->all();

        if (empty($noteIds)) {
            return null;
        }

        $session = FlashcardSession::create([
            'status' => 'active',
            'total_cards' => count($noteIds),
            'current_index' => 0,
            'correct_count' => 0,
            'incorrect_count' => 0,
            'accuracy' => 0,
            'note_ids' => $noteIds,
            'studied_at' => now(),
            'discipline_id' => $discipline->id,
        ]);

        Log::create([
            'action' => 'flashcard.session_started',
            'context' => [
                'session_id' => $session->id,
                'discipline_id' => $discipline->id,
                'total_cards' => $session->total_cards,
            ],
        ]);

        return $session->id;
    }

    protected function normalizeStep(int $value): int
    {
        return max(1, min(4, $value));
    }
}
