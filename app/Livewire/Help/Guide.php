<?php

namespace App\Livewire\Help;

use Illuminate\Contracts\View\View;
use Livewire\Component;

class Guide extends Component
{
    public function render(): View
    {
        $flowSteps = [
            [
                'title' => __('Organize your study space'),
                'description' => __('Create notebooks to separate courses, classes, or exams and give each one a goal.'),
            ],
            [
                'title' => __('Structure disciplines and content'),
                'description' => __('Inside every notebook, add disciplines so notes, files, and flashcards stay segmented.'),
            ],
            [
                'title' => __('Capture notes and promote them to flashcards'),
                'description' => __('Write detailed notes, highlight key points, and promote the essentials to flashcards.'),
            ],
            [
                'title' => __('Study, track, and adjust'),
                'description' => __('Use the study modes and review the activity log to stay on pace and plan revisions.'),
            ],
            [
                'title' => __('Repeat to reinforce'),
                'description' => __('Repeat the cycle often to adapt plans and keep your study rhythm on track.'),
            ],
        ];

        $featureHighlights = [
            [
                'icon' => 'book-open',
                'title' => __('Workspace'),
                'description' => __('Each notebook centralizes disciplines, notes, and metrics in one organized hub.'),
                'links' => [
                    ['label' => __('View notebooks'), 'route' => 'notebooks.index'],
                    ['label' => __('Create notebook'), 'route' => 'notebooks.create'],
                ],
            ],
            [
                'icon' => 'document-text',
                'title' => __('Notes library'),
                'description' => __('Search, export, and share notes without leaving your main flow.'),
                'links' => [
                    ['label' => __('Open library'), 'route' => 'notes.library'],
                    ['label' => __('Export to PDF'), 'route' => 'notes.export'],
                ],
            ],
            [
                'icon' => 'sparkles',
                'title' => __('Guided practice'),
                'description' => __('Flashcards, exercises, and different review modes keep learning active.'),
                'links' => [
                    ['label' => __('Flashcards'), 'route' => 'study.flashcards'],
                    ['label' => __('Exercises'), 'route' => 'study.exercises'],
                ],
            ],
            [
                'icon' => 'chart-bar-square',
                'title' => __('Activity and logs'),
                'description' => __('Logs and metrics show what was created, reviewed, and completed in every session.'),
                'links' => [
                    ['label' => __('View activity'), 'route' => 'logs.index'],
                ],
            ],
        ];

        $screenGuides = [
            [
                'title' => __('Dashboard'),
                'tips' => [
                    __('Daily summary with metric cards and shortcuts for quick actions.'),
                    __('Top-of-page alerts highlight pending items like incomplete profiles or active sessions.'),
                ],
            ],
            [
                'title' => __('Notebooks and disciplines'),
                'tips' => [
                    __('Use the persistent search and inline actions to edit, delete, or open entries fast.'),
                    __('Detail pages surface tabs for notes, flashcards, and progress tracking.'),
                ],
            ],
            [
                'title' => __('Notes and flashcards'),
                'tips' => [
                    __('Editor supports lightweight markdown, plus dedicated question and answer fields.'),
                    __('Empty states guide you when no notes or flashcards exist yet.'),
                ],
            ],
            [
                'title' => __('Document library'),
                'tips' => [
                    __('Uploads stay organized with discipline filters and title search.'),
                    __('Preview PDFs without leaving the screen and download them for offline study.'),
                ],
            ],
            [
                'title' => __('Study and practice'),
                'tips' => [
                    __('Session controls at the top show how many cards remain.'),
                    __('Every mode explains the question type before the round begins.'),
                ],
            ],
            [
                'title' => __('Activity logs'),
                'tips' => [
                    __('Chronological table highlights author and context for each action.'),
                    __('Quick filters help isolate specific actions like deletions or exports.'),
                ],
            ],
        ];

        return view('livewire.help.guide', [
            'flowSteps' => $flowSteps,
            'featureHighlights' => $featureHighlights,
            'screenGuides' => $screenGuides,
        ])->layout('layouts.app', [
            'title' => __('Help center'),
        ]);
    }
}
