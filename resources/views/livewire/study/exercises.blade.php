@php
$modeLabels = [
'true_false' => __('True or False'),
'fill_blank' => __('Fill in the blanks'),
'multiple_choice' => __('Multiple choice'),
];

$modeDescriptions = [
'true_false' => __('Decide if the answer below matches the card.'),
'fill_blank' => __('Type the missing word to complete the idea.'),
'multiple_choice' => __('Choose the correct alternative.'),
];

$modeOverviewDescriptions = [
'true_false' => __('Quick gut checks to validate definitions or concepts.'),
'fill_blank' => __('Hide four key terms at once to reinforce precise recall.'),
'multiple_choice' => __('Compare similar answers and spot nuances across cards.'),
];
@endphp

<div class="space-y-8">
    <div class="flex flex-col gap-4 md:flex-row md:items-end md:justify-between">
        <div>
            <h1 class="text-2xl font-semibold text-zinc-900">{{ __('Exercises') }}</h1>
            <p class="mt-1 text-sm text-zinc-500">
                {{ __('Mix true or false, fill in the blanks, and multiple choice without affecting your flashcard stats.') }}
            </p>
        </div>

        <div class="flex items-center gap-2">
            <select
                wire:model.live="disciplineId"
                class="w-full rounded-md border border-zinc-300 bg-white px-3 py-2 text-sm text-zinc-700 focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500 md:w-60">
                <option value="">{{ __('All disciplines') }}</option>
                @foreach ($disciplines as $discipline)
                <option value="{{ $discipline->id }}">{{ $discipline->title }}</option>
                @endforeach
            </select>
        </div>
    </div>

    <div class="grid gap-6 lg:grid-cols-[2fr_1fr]">
        <div class="rounded-md border border-zinc-200 bg-white/95 p-6 ring-zinc-100 space-y-6">
            <div>
                <p class="mt-1 text-sm text-zinc-500">
                    {{ __('Use your flashcards as the source of every drill and rotate the modes to stay sharp.') }}
                </p>
            </div>

            <div class="grid gap-3 sm:grid-cols-3">
                <div class="rounded-md border border-zinc-100 bg-zinc-50 p-4">
                    <dt class="text-xs font-semibold uppercase tracking-wide text-zinc-500">{{ __('Exercises answered') }}</dt>
                    <dd class="mt-2 text-2xl font-semibold text-zinc-900">{{ $stats['answered'] }}</dd>
                </div>

                <div class="rounded-md border border-zinc-100 bg-zinc-50 p-4">
                    <dt class="text-xs font-semibold uppercase tracking-wide text-green-600">{{ __('Correct answers') }}</dt>
                    <dd class="mt-2 text-2xl font-semibold text-zinc-900">{{ $stats['correct'] }}</dd>
                </div>

                <div class="rounded-md border border-zinc-100 bg-zinc-50 p-4">
                    <dt class="text-xs font-semibold uppercase tracking-wide text-indigo-600">{{ __('Accuracy') }}</dt>
                    <dd class="mt-2 text-2xl font-semibold text-zinc-900">
                        {{ $accuracy !== null ? $accuracy . '%' : '—' }}
                    </dd>
                </div>
            </div>

            @if ($exercise)
            <div class="space-y-4 rounded-lg border border-dashed border-zinc-200 bg-zinc-50 p-5">
                <div class="flex flex-col gap-1">
                    <p class="text-xs font-semibold uppercase tracking-wide text-zinc-500">{{ $modeDescriptions[$mode] ?? '' }}</p>
                    <h3 class="text-base font-semibold text-zinc-900">{{ $exercise['question'] }}</h3>
                </div>

                {{-- TRUE/FALSE --}}
                @if ($mode === 'true_false')
                <div class="rounded-md border border-zinc-200 bg-white px-4 py-3 text-sm text-zinc-700">
                    {{ $exercise['statement'] }}
                </div>

                <div class="flex flex-col gap-3 sm:flex-row">
                    <flux:button
                        class="flex-1"
                        variant="ghost"
                        color="zinc"
                        icon="x-mark"
                        wire:click="submitTrueFalse(false)"
                        :disabled="$answeredCorrectly !== null">
                        {{ __('False') }}
                    </flux:button>

                    <flux:button
                        class="flex-1"
                        variant="primary"
                        icon="check-circle"
                        wire:click="submitTrueFalse(true)"
                        :disabled="$answeredCorrectly !== null">
                        {{ __('True') }}
                    </flux:button>
                </div>
                @endif

                {{-- FILL BLANK --}}
                @if ($mode === 'fill_blank')
                <div class="rounded-md border border-zinc-200 bg-white px-4 py-3">
                    <p class="text-sm text-zinc-700 whitespace-pre-wrap leading-relaxed">
                        @foreach ($exercise['segments'] ?? [] as $segment)
                        @if (($segment['type'] ?? '') === 'blank')
                        <span class="inline-flex items-center justify-center rounded-md border border-dashed border-indigo-200 bg-indigo-50 px-2 py-0.5 text-xs font-semibold text-indigo-700 mx-1 my-0.5 align-middle">
                            #{{ $segment['label'] }}
                        </span>
                        @else
                        {{ $segment['value'] ?? '' }}
                        @endif
                        @endforeach
                    </p>
                </div>

                <form wire:submit.prevent="submitFillBlank" class="space-y-4">
                    <div class="grid gap-3 sm:grid-cols-2">
                        @foreach ($exercise['blanks'] ?? [] as $blank)
                        <label class="flex flex-col gap-1 text-xs font-medium text-zinc-500">
                            <span>{{ $blank['label'] }}</span>
                            <input
                                type="text"
                                wire:model.defer="fillGuesses.{{ $blank['index'] }}"
                                class="w-full rounded-md border border-zinc-200 bg-white px-3 py-2 text-sm text-zinc-700 focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500"
                                placeholder="{{ __('Type the missing word') }}"
                                @disabled($answeredCorrectly !==null)>
                        </label>
                        @endforeach
                    </div>

                    <flux:button
                        type="submit"
                        variant="primary"
                        icon="sparkles"
                        :disabled="$answeredCorrectly !== null">
                        {{ __('Check answer') }}
                    </flux:button>
                </form>
                @endif

                {{-- MULTIPLE CHOICE --}}
                @if ($mode === 'multiple_choice')
                <div class="grid gap-2">
                    @foreach ($exercise['options'] as $option)
                    <button
                        type="button"
                        wire:click="submitMultipleChoice('{{ $option['key'] }}')"
                        class="flex items-start gap-3 rounded-md border border-zinc-200 bg-white px-4 py-3 text-left text-sm text-zinc-700 transition hover:border-indigo-200 hover:bg-white/80 focus:outline-none focus:ring-2 focus:ring-indigo-500 {{ $answeredCorrectly !== null ? 'pointer-events-none opacity-60' : '' }}">
                        <span class="text-xs font-semibold uppercase tracking-wide text-indigo-600">{{ $option['key'] }}</span>
                        <span class="flex-1">{{ $option['text'] }}</span>
                    </button>
                    @endforeach
                </div>
                @endif

                @if (! is_null($answeredCorrectly))
                <div class="space-y-3 rounded-md border {{ $answeredCorrectly ? 'border-emerald-200 bg-emerald-50' : 'border-amber-200 bg-amber-50' }} px-4 py-3">
                    <div>
                        <p class="text-sm font-semibold {{ $answeredCorrectly ? 'text-emerald-700' : 'text-amber-700' }}">
                            {{ $feedbackTitle }}
                        </p>
                        @if ($feedbackBody)
                        <p class="text-xs text-zinc-600">{{ $feedbackBody }}</p>
                        @endif
                    </div>

                    <div class="text-xs text-zinc-600">
                        <span class="font-medium text-zinc-800">{{ __('Correct answer') }}:</span>
                        <span>{{ $exercise['correct_answer'] }}</span>
                    </div>

                    <div class="flex flex-col gap-2 sm:flex-row sm:justify-between">
                        <p class="text-xs text-zinc-500">
                            {{ __('Need a new challenge?') }}
                        </p>
                        <flux:button variant="ghost" color="indigo" size="sm" wire:click="nextExercise" icon="arrow-path">
                            {{ __('Next exercise') }}
                        </flux:button>
                    </div>
                </div>
                @endif
            </div>
            @else
            <div class="flex flex-col items-center gap-3 rounded-lg border border-dashed border-zinc-200 bg-zinc-50 px-6 py-10 text-center text-sm text-zinc-500">
                <flux:icon.sparkles class="h-10 w-10 text-zinc-300" />
                <p class="text-sm font-medium text-zinc-700">
                    {{ $warningMessage }}
                </p>
                <p class="text-xs text-zinc-500">
                    {{ __('Create or update flashcards in your disciplines to practice here.') }}
                </p>
            </div>
            @endif
        </div>

        <section class="rounded-md border border-zinc-200 bg-white/80 p-6 space-y-4">
            <p class="text-xs font-semibold uppercase tracking-wide text-zinc-500">{{ __('Modes overview') }}</p>

            <fieldset class="space-y-3">
                @foreach ($modeLabels as $modeKey => $label)
                @php
                $isActive = $mode === $modeKey;
                @endphp

                <button
                    type="button"
                    wire:click="switchMode('{{ $modeKey }}')"
                    @class([ 'flex w-full cursor-pointer flex-col rounded-lg border px-4 py-3 text-left text-sm transition-all focus:outline-none focus-visible:outline-none' , 'border-indigo-500 bg-indigo-50 text-indigo-900 shadow-none'=> $isActive,
                    'border-zinc-200 text-zinc-600 hover:border-indigo-300 hover:bg-zinc-50' => ! $isActive,
                    ])
                    >
                    <span class="flex items-center justify-between">
                        <span class="font-semibold">{{ $label }}</span>
                        @if ($isActive)
                        <flux:icon.check class="h-4 w-4 text-indigo-600" />
                        @endif
                    </span>
                    <span class="mt-2 text-xs leading-relaxed text-inherit">
                        {{ $modeOverviewDescriptions[$modeKey] ?? '' }}
                    </span>
                </button>
                @endforeach
            </fieldset>
        </section>
    </div>
</div>