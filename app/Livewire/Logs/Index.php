<?php

namespace App\Livewire\Logs;

use App\Models\Discipline;
use App\Models\FlashcardSession;
use App\Models\Log;
use App\Models\Note;
use App\Models\Notebook;
use Illuminate\Contracts\View\View;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Livewire\Component;
use Livewire\WithPagination;

class Index extends Component
{
    use WithPagination;

    public string $search = '';

    public ?string $actionFilter = null;

    public int $perPage = 10;

    protected array $perPageOptions = [10, 30, 50];

    protected $queryString = [
        'search' => ['except' => ''],
        'actionFilter' => ['except' => null],
        'perPage' => ['except' => 10],
    ];

    public function mount(): void
    {
        if (! in_array($this->perPage, $this->perPageOptions, true)) {
            $this->perPage = $this->perPageOptions[0];
        }

        if ($this->actionFilter === '') {
            $this->actionFilter = null;
        }
    }

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingActionFilter(): void
    {
        $this->resetPage();
    }

    public function updatedActionFilter($value): void
    {
        $this->actionFilter = $value ?: null;
    }

    public function updatedPerPage($value): void
    {
        $perPage = (int) $value;

        if (! in_array($perPage, $this->perPageOptions, true)) {
            $perPage = $this->perPageOptions[0];
        }

        $this->perPage = $perPage;

        $this->resetPage();
    }

    protected function logsQuery()
    {
        return Log::query()
            ->latest()
            ->when($this->actionFilter, fn ($query) => $query->where('action', $this->actionFilter))
            ->when($this->search, function ($query) {
                $term = '%' . $this->search . '%';

                $query->where(function ($inner) use ($term) {
                    $inner->where('action', 'like', $term)
                        ->orWhere('context->title', 'like', $term)
                        ->orWhere('context->before->title', 'like', $term)
                        ->orWhere('context->after->title', 'like', $term);
                });
            });
    }

    public function getLogsProperty(): LengthAwarePaginator
    {
        return $this->logsQuery()->paginate($this->perPage);
    }

    public function render(): View
    {
        $logs = $this->logs;

        $related = $this->gatherRelatedModels($logs);

        $transformed = $this->transformLogs($logs, $related);

        $actionOptions = Log::query()
            ->select('action')
            ->distinct()
            ->orderBy('action')
            ->pluck('action')
            ->map(fn (string $action) => [
                'value' => $action,
                'label' => $this->labelForAction($action),
            ])
            ->values();

        return view('livewire.logs.index', [
            'logs' => $transformed,
            'perPageOptions' => $this->perPageOptions,
            'actionOptions' => $actionOptions,
        ])->layout('layouts.app', [
            'title' => __('Activity log'),
        ]);
    }

    protected function gatherRelatedModels(LengthAwarePaginator $logs): array
    {
        $noteIds = [];
        $disciplineIds = [];
        $notebookIds = [];
        $sessionIds = [];

        foreach ($logs->items() as $log) {
            $context = $log->context ?? [];

            $noteIds[] = Arr::get($context, 'note_id');
            $noteIds[] = Arr::get($context, 'before.note_id');
            $noteIds[] = Arr::get($context, 'after.note_id');

            $disciplineIds[] = Arr::get($context, 'discipline_id');
            $disciplineIds[] = Arr::get($context, 'before.discipline_id');
            $disciplineIds[] = Arr::get($context, 'after.discipline_id');

            $notebookIds[] = Arr::get($context, 'notebook_id');
            $notebookIds[] = Arr::get($context, 'before.notebook_id');
            $notebookIds[] = Arr::get($context, 'after.notebook_id');

            $sessionIds[] = Arr::get($context, 'session_id');
        }

        return [
            'notes' => Note::query()
                ->whereIn('id', $this->uniqueIds($noteIds))
                ->get()
                ->keyBy('id'),
            'disciplines' => Discipline::query()
                ->whereIn('id', $this->uniqueIds($disciplineIds))
                ->get()
                ->keyBy('id'),
            'notebooks' => Notebook::query()
                ->whereIn('id', $this->uniqueIds($notebookIds))
                ->get()
                ->keyBy('id'),
            'sessions' => FlashcardSession::query()
                ->with('discipline')
                ->whereIn('id', $this->uniqueIds($sessionIds))
                ->get()
                ->keyBy('id'),
        ];
    }

    protected function uniqueIds(array $values): array
    {
        return array_values(array_unique(array_filter($values, fn ($value) => $value !== null && $value !== '')));
    }

    protected function transformLogs(LengthAwarePaginator $logs, array $related): LengthAwarePaginator
    {
        $items = collect($logs->items())->map(fn (Log $log) => $this->transformLog($log, $related));

        $logs->setCollection($items);

        return $logs;
    }

    protected function transformLog(Log $log, array $related): array
    {
        $context = $log->context ?? [];

        $label = $this->labelForAction($log->action);
        $icon = $this->iconForAction($log->action);
        $iconClasses = $this->toneClassesForAction($log->action);

        $description = $this->descriptionForLog($log, $related);
        $tags = $this->tagsForLog($log, $related);
        $meta = $this->metaForLog($log, $related);
        $changes = $this->changesForLog($log, $related);

        $createdAt = $log->created_at;

        return [
            'id' => $log->id,
            'label' => $label,
            'description' => $description,
            'icon' => $icon,
            'icon_classes' => $iconClasses,
            'tags' => $tags,
            'meta' => $meta,
            'changes' => $changes,
            'timestamp' => $createdAt ? $createdAt->translatedFormat('d M Y, H:i') : null,
            'ago' => $createdAt ? $createdAt->diffForHumans() : null,
            'action' => $log->action,
            'context' => $context,
        ];
    }

    protected function descriptionForLog(Log $log, array $related): string
    {
        $context = $log->context ?? [];
        $note = $this->related($related['notes'], Arr::get($context, 'note_id'));
        $discipline = $this->related($related['disciplines'], Arr::get($context, 'discipline_id'));
        $notebook = $this->related($related['notebooks'], Arr::get($context, 'notebook_id'));

        $sessionCards = Arr::get($context, 'total_cards');
        $disciplineTitle = $discipline?->title;

        $description = match ($log->action) {
            'note.created' => $note
                ? __('Created the note “:title”.', ['title' => $note->title])
                : __('Created a note.'),
            'note.updated' => $note
                ? __('Updated the note “:title”.', ['title' => $note->title])
                : __('Updated a note.'),
            'note.deleted' => ($context['title'] ?? null)
                ? __('Deleted the note “:title”.', ['title' => $context['title']])
                : ($note?->title
                    ? __('Deleted the note “:title”.', ['title' => $note->title])
                    : __('Deleted a note.')),
            'note.converted_to_flashcard' => $note
                ? __('Converted “:title” into a flashcard.', ['title' => $note->title])
                : __('Converted a note into a flashcard.'),
            'note.reverted_from_flashcard' => $note
                ? __('Reverted “:title” to a regular note.', ['title' => $note->title])
                : __('Reverted a flashcard to a note.'),
            'discipline.created' => ($context['title'] ?? null)
                ? __('Created the discipline “:title”.', ['title' => $context['title']])
                : __('Created a discipline.'),
            'discipline.updated' => Arr::get($context, 'after.title')
                ? __('Updated the discipline “:title”.', ['title' => Arr::get($context, 'after.title')])
                : (Arr::get($context, 'before.title')
                    ? __('Updated the discipline “:title”.', ['title' => Arr::get($context, 'before.title')])
                    : __('Updated a discipline.')),
            'discipline.deleted' => ($context['title'] ?? null)
                ? __('Deleted the discipline “:title”.', ['title' => $context['title']])
                : __('Deleted a discipline.'),
            'notebook.created' => ($context['title'] ?? null)
                ? __('Created the notebook “:title”.', ['title' => $context['title']])
                : __('Created a notebook.'),
            'notebook.updated' => Arr::get($context, 'after.title')
                ? __('Updated the notebook “:title”.', ['title' => Arr::get($context, 'after.title')])
                : (Arr::get($context, 'before.title')
                    ? __('Updated the notebook “:title”.', ['title' => Arr::get($context, 'before.title')])
                    : __('Updated a notebook.')),
            'notebook.deleted' => ($context['title'] ?? null)
                ? __('Deleted the notebook “:title”.', ['title' => $context['title']])
                : __('Deleted a notebook.'),
            'flashcard.session_started' => $disciplineTitle
                ? ($sessionCards
                    ? __('Started a session with :count cards for “:discipline”.', [
                        'count' => $sessionCards,
                        'discipline' => $disciplineTitle,
                    ])
                    : __('Started a study session for “:discipline”.', [
                        'discipline' => $disciplineTitle,
                    ]))
                : ($sessionCards
                    ? __('Started a session with :count cards.', ['count' => $sessionCards])
                    : __('Started a study session.')),
            'flashcard.answered' => match (Arr::get($context, 'result')) {
                'correct' => __('Marked a flashcard as correct.'),
                'incorrect' => __('Marked a flashcard for review.'),
                default => __('Recorded a flashcard answer.'),
            },
            default => Str::headline(str_replace('.', ' ', $log->action)),
        };

        return Str::limit($description, 90);
    }

    protected function metaForLog(Log $log, array $related): array
    {
        $context = $log->context ?? [];
        $meta = [];

        $note = $this->related($related['notes'], Arr::get($context, 'note_id'));
        $discipline = $this->related($related['disciplines'], Arr::get($context, 'discipline_id'));
        $notebook = $this->related($related['notebooks'], Arr::get($context, 'notebook_id'));
        $session = $this->related($related['sessions'], Arr::get($context, 'session_id'));

        $appendDiscipline = function (?Discipline $discipline) use (&$meta): void {
            if ($discipline) {
                $meta[] = [
                    'label' => __('Discipline'),
                    'value' => $discipline->title,
                ];
            }
        };

        $appendNotebook = function (?Notebook $notebook) use (&$meta): void {
            if ($notebook) {
                $meta[] = [
                    'label' => __('Notebook'),
                    'value' => $notebook->title,
                ];
            }
        };

        switch ($log->action) {
            case 'note.created':
            case 'note.updated':
            case 'note.deleted':
            case 'note.converted_to_flashcard':
            case 'note.reverted_from_flashcard':
                if ($note) {
                    $meta[] = [
                        'label' => __('Note'),
                        'value' => $note->title,
                    ];
                } elseif (Arr::get($context, 'title')) {
                    $meta[] = [
                        'label' => __('Note'),
                        'value' => Arr::get($context, 'title'),
                    ];
                }

                $appendDiscipline($discipline);

                if (Arr::has($context, 'is_flashcard')) {
                    $meta[] = [
                        'label' => __('Type'),
                        'value' => Arr::get($context, 'is_flashcard') ? __('Flashcard') : __('Note'),
                    ];
                } elseif ($log->action === 'note.converted_to_flashcard') {
                    $meta[] = [
                        'label' => __('Type'),
                        'value' => __('Flashcard'),
                    ];
                } elseif ($log->action === 'note.reverted_from_flashcard') {
                    $meta[] = [
                        'label' => __('Type'),
                        'value' => __('Note'),
                    ];
                }

                break;
            case 'discipline.created':
            case 'discipline.updated':
            case 'discipline.deleted':
                $meta[] = [
                    'label' => __('Discipline'),
                    'value' => Arr::get($context, 'title') ?? Arr::get($context, 'after.title') ?? __('(unnamed)'),
                ];

                $appendNotebook($notebook ?: $this->related($related['notebooks'], Arr::get($context, 'after.notebook_id')));
                break;
            case 'notebook.created':
            case 'notebook.updated':
            case 'notebook.deleted':
                $meta[] = [
                    'label' => __('Notebook'),
                    'value' => Arr::get($context, 'title') ?? Arr::get($context, 'after.title') ?? __('(unnamed)'),
                ];
                break;
            case 'flashcard.session_started':
                if ($session) {
                    $meta[] = [
                        'label' => __('Session'),
                        'value' => __('Session #:id', ['id' => $session->id]),
                    ];
                }

                $appendDiscipline($discipline ?: ($session?->discipline));

                if (Arr::has($context, 'total_cards')) {
                    $meta[] = [
                        'label' => __('Cards in queue'),
                        'value' => (string) Arr::get($context, 'total_cards'),
                    ];
                }
                break;
            case 'flashcard.answered':
                if ($note) {
                    $meta[] = [
                        'label' => __('Note'),
                        'value' => $note->title,
                    ];
                }

                if ($session) {
                    $meta[] = [
                        'label' => __('Session'),
                        'value' => __('Session #:id', ['id' => $session->id]),
                    ];

                    if ($session->discipline) {
                        $appendDiscipline($session->discipline);
                    }
                }
                break;
        }

        return $meta;
    }

    protected function changesForLog(Log $log, array $related): array
    {
        $context = $log->context ?? [];
        $before = Arr::get($context, 'before', []);
        $after = Arr::get($context, 'after', []);

        if (! is_array($before) || ! is_array($after)) {
            return [];
        }

        return $this->formatChanges($before, $after, $related);
    }

    protected function tagsForLog(Log $log, array $related): array
    {
        $context = $log->context ?? [];
        $tags = [];

        if ($log->action === 'note.created' && Arr::get($context, 'is_flashcard')) {
            $tags[] = $this->tag(__('Flashcard'), 'amber');
        }

        if ($log->action === 'note.converted_to_flashcard') {
            $tags[] = $this->tag(__('Flashcard'), 'amber');
        }

        if ($log->action === 'note.reverted_from_flashcard') {
            $tags[] = $this->tag(__('Converted back to note'), 'zinc');
        }

        if ($log->action === 'flashcard.answered') {
            $result = Arr::get($context, 'result');

            if ($result === 'correct') {
                $tags[] = $this->tag(__('Correct'), 'emerald');
            } elseif ($result === 'incorrect') {
                $tags[] = $this->tag(__('To review'), 'amber');
            }
        }

        return $tags;
    }

    protected function formatChanges(array $before, array $after, array $related): array
    {
        $changes = [];

        foreach ($after as $key => $value) {
            $previous = $before[$key] ?? null;

            if ($previous == $value) {
                continue;
            }

            $changes[] = [
                'label' => $this->labelForKey($key),
                'from' => $this->formatValueForKey($key, $previous, $related),
                'to' => $this->formatValueForKey($key, $value, $related),
            ];
        }

        return $changes;
    }

    protected function formatValueForKey(string $key, $value, array $related): string
    {
        if ($value === null || $value === '') {
            return '—';
        }

        return match ($key) {
            'notebook_id' => ($this->related($related['notebooks'], (int) $value)?->title) ?? '#' . $value,
            'discipline_id' => ($this->related($related['disciplines'], (int) $value)?->title) ?? '#' . $value,
            'note_id' => ($this->related($related['notes'], (int) $value)?->title) ?? '#' . $value,
            'is_flashcard' => $value ? __('Flashcard') : __('Note'),
            'content' => $this->expandedText($value),
            'description' => $this->expandedText($value),
            'flashcard_question' => $this->expandedText($value),
            'flashcard_answer' => $this->expandedText($value),
            default => is_bool($value)
                ? ($value ? __('Yes') : __('No'))
                : (string) $value,
        };
    }

    protected function labelForKey(string $key): string
    {
        return match ($key) {
            'title' => __('Title'),
            'description' => __('Description'),
            'notebook_id' => __('Notebook'),
            'discipline_id' => __('Discipline'),
            'content' => __('Content'),
            'is_flashcard' => __('Type'),
            'flashcard_question' => __('Flashcard question'),
            'flashcard_answer' => __('Flashcard answer'),
            default => Str::headline(str_replace('_', ' ', $key)),
        };
    }

    protected function expandedText(?string $value): string
    {
        return trim(preg_replace('/\s+/', ' ', strip_tags((string) $value)));
    }

    protected function labelForAction(string $action): string
    {
        return match ($action) {
            'note.created' => __('Note created'),
            'note.updated' => __('Note updated'),
            'note.deleted' => __('Note deleted'),
            'note.converted_to_flashcard' => __('Note converted to flashcard'),
            'note.reverted_from_flashcard' => __('Flashcard reverted to note'),
            'discipline.created' => __('Discipline created'),
            'discipline.updated' => __('Discipline updated'),
            'discipline.deleted' => __('Discipline deleted'),
            'notebook.created' => __('Notebook created'),
            'notebook.updated' => __('Notebook updated'),
            'notebook.deleted' => __('Notebook deleted'),
            'flashcard.session_started' => __('Study session started'),
            'flashcard.answered' => __('Flashcard answered'),
            default => Str::headline(str_replace('.', ' ', $action)),
        };
    }

    protected function iconForAction(string $action): string
    {
        return match ($action) {
            'note.created' => 'document-check',
            'note.updated' => 'pencil-square',
            'note.deleted' => 'trash',
            'note.converted_to_flashcard' => 'bolt',
            'note.reverted_from_flashcard' => 'arrow-uturn-left',
            'discipline.created' => 'bookmark-square',
            'discipline.updated' => 'bookmark-square',
            'discipline.deleted' => 'bookmark-slash',
            'notebook.created' => 'rectangle-stack',
            'notebook.updated' => 'rectangle-stack',
            'notebook.deleted' => 'archive-box-x-mark',
            'flashcard.session_started' => 'play',
            'flashcard.answered' => 'check',
            default => 'sparkles',
        };
    }

    protected function toneClassesForAction(string $action): string
    {
        return match ($action) {
            'note.created', 'discipline.created', 'notebook.created' => 'bg-emerald-100 text-emerald-600',
            'note.updated', 'discipline.updated', 'notebook.updated' => 'bg-indigo-100 text-indigo-600',
            'note.deleted', 'discipline.deleted', 'notebook.deleted' => 'bg-rose-100 text-rose-600',
            'note.converted_to_flashcard', 'flashcard.session_started' => 'bg-amber-100 text-amber-600',
            'note.reverted_from_flashcard' => 'bg-zinc-200 text-zinc-700',
            'flashcard.answered' => 'bg-blue-100 text-blue-600',
            default => 'bg-zinc-100 text-zinc-600',
        };
    }

    protected function tag(string $text, string $tone = 'zinc'): array
    {
        $tones = [
            'emerald' => 'border border-emerald-200 bg-emerald-50 text-emerald-700',
            'amber' => 'border border-amber-200 bg-amber-50 text-amber-700',
            'indigo' => 'border border-indigo-200 bg-indigo-50 text-indigo-700',
            'rose' => 'border border-rose-200 bg-rose-50 text-rose-700',
            'blue' => 'border border-blue-200 bg-blue-50 text-blue-700',
            'zinc' => 'border border-zinc-200 bg-zinc-100 text-zinc-600',
        ];

        return [
            'text' => $text,
            'classes' => $tones[$tone] ?? $tones['zinc'],
        ];
    }

    protected function related(Collection $collection, ?int $id)
    {
        return $id ? $collection->get($id) : null;
    }
}
