<?php

return [
    'title' => 'Study route',
    'heading' => 'Study route',
    'description' => 'Configure your weekly study routine and jump into each mode with direct links.',

    'today_label' => 'Today\'s script',
    'today_description' => 'Complete the tasks below to finish your study plan for the day.',
    'today_empty_title' => 'No tasks scheduled for today yet.',
    'today_empty_description' => 'Add disciplines to your plan and we will automatically create the next tasks.',

    'upcoming_label' => 'Next days',
    'upcoming_title' => 'Upcoming sessions',
    'upcoming_description' => 'We pre-schedule the next couple of days so you always know what comes next.',
    'upcoming_count' => '{1} :count study|[2,*] :count studies',
    'upcoming_empty_title' => 'Upcoming sessions will appear here soon.',
    'upcoming_empty_description' => 'Once you configure your plan we keep the next days aligned automatically.',

    'plan_label' => 'Plan settings',
    'plan_title' => 'Configure your study routine',
    'plan_description' => 'Set your weekly rhythm and keep a simple, calendar-free study route.',

    'stats' => [
        'pending_today' => 'Pending tasks',
        'completed_today' => 'Completed today',
        'disciplines' => 'Disciplines in the plan',
        'weekly_sessions' => 'Weekly sessions',
    ],

    'modes' => [
        'flashcards' => 'Flashcards',
        'true_false' => 'True or False',
        'fill_blank' => 'Fill in the blanks',
        'multiple_choice' => 'Multiple choice',
        'simulated' => 'Simulated test',
    ],

    'statuses' => [
        'pending' => 'Pending',
        'completed' => 'Completed',
        'cancelled' => 'Cancelled',
    ],

    'labels' => [
        'overdue' => 'Overdue',
        'any_discipline' => 'Any discipline',
        'scheduled_for' => 'Scheduled for :date',
        'mode_prefix' => 'Mode:',
        'days_suffix' => 'days',
        'today_compact' => 'TODAY',
        'reset_warning' => 'This will remove all generated routes/tasks for your planner. Continue?',
    ],

    'units' => [
        'cards' => '{1} :count card|[2,*] :count cards',
        'questions' => '{1} :count question|[2,*] :count questions',
        'gaps' => '{1} :count blank|[2,*] :count blanks',
        'exam' => '{1} :count mock exam|[2,*] :count mock exams',
        'items' => '{1} :count item|[2,*] :count items',
    ],

    'form' => [
        'study_days' => 'Study days per week',
        'disciplines' => 'Disciplines in this plan',
        'selected_count' => ':count selected',
        'disciplines_hint' => 'Only disciplines with flashcards are listed here.',
        'empty_disciplines_title' => 'You have no disciplines yet.',
        'empty_disciplines_description' => 'Create a discipline to start configuring your planner.',
    ],

    'actions' => [
        'refresh' => 'Refresh tasks',
        'new_discipline' => 'New discipline',
        'reset' => 'Reset route',
        'complete' => 'Mark as done',
        'cancel' => 'Skip',
        'reopen' => 'Reopen',
        'restore' => 'Restore',
        'start' => 'Start study',
        'save_plan' => 'Save plan',
    ],

    'messages' => [
        'plan_saved' => 'Planner updated successfully.',
        'tasks_refreshed' => 'Daily script updated.',
    ],

    'reminder' => [
        'title' => 'You have :count task(s) for today.',
        'subtitle' => 'Wrap up your study route before moving on.',
        'cta' => "Open today's route",
    ],

    'validation' => [
        'select_discipline' => 'Select at least one discipline with flashcards.',
    ],
];
