<div class="space-y-10">
    <div class="rounded-2xl border border-zinc-200 bg-gradient-to-br from-zinc-50 to-white p-6 sm:p-8">
        <div class="flex flex-col gap-6 lg:flex-row lg:items-center lg:justify-between">
            <div class="space-y-4">
                <span class="inline-flex items-center gap-2 rounded-full bg-emerald-100 px-3 py-1 text-xs font-semibold uppercase tracking-wide text-emerald-600">
                    <flux:icon.sparkles class="h-3.5 w-3.5" />
                    {{ __('Help center') }}
                </span>

                <div>
                    <h1 class="text-3xl font-semibold text-zinc-900">{{ __('Explore every part of the platform') }}</h1>
                    <p class="mt-2 max-w-2xl text-sm text-zinc-600">
                        {{ __('Use this guide to understand the full flow—from notebooks to study modes—and know exactly what to do on each screen.') }}
                    </p>
                </div>

                <ul class="list-disc space-y-1 ps-5 text-sm text-zinc-600">
                    <li>{{ __('Main flow summarized in four clear steps.') }}</li>
                    <li>{{ __('Quick explanations for every sidebar area.') }}</li>
                    <li>{{ __('Shortcuts to important sections without leaving your current context.') }}</li>
                </ul>
            </div>

            <div class="flex flex-col gap-3 sm:flex-row sm:items-center">
                <flux:button
                    variant="primary"
                    icon="plus"
                    :href="route('notebooks.create')"
                    wire:navigate
                >
                    {{ __('Create a notebook now') }}
                </flux:button>

                <flux:button
                    variant="ghost"
                    icon="play"
                    href="#main-flow"
                >
                    {{ __('View main flow') }}
                </flux:button>
            </div>
        </div>
    </div>

    <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-4">
        @foreach ($featureHighlights as $feature)
            <div class="flex h-full flex-col rounded-xl border border-zinc-200 bg-white p-5">
                <div class="flex items-center gap-3">
                    <span class="flex h-12 w-12 items-center justify-center rounded-xl bg-zinc-50">
                        <flux:icon :icon="$feature['icon']" class="h-5 w-5 text-zinc-500" />
                    </span>
                    <div>
                        <p class="text-sm font-semibold text-zinc-900">{{ $feature['title'] }}</p>
                        <p class="text-xs text-zinc-500">{{ __('Sidebar area') }}</p>
                    </div>
                </div>

                <p class="mt-4 text-sm text-zinc-600">{{ $feature['description'] }}</p>

                <div class="mt-4 flex flex-wrap gap-2">
                    @foreach ($feature['links'] as $link)
                        <flux:button
                            size="xs"
                            variant="ghost"
                            icon="arrow-top-right-on-square"
                            :href="route($link['route'])"
                            wire:navigate
                        >
                            {{ $link['label'] }}
                        </flux:button>
                    @endforeach
                </div>
            </div>
        @endforeach
    </div>

    <div id="main-flow" class="grid gap-6 lg:grid-cols-[2fr_1fr]">
        <div class="rounded-2xl border border-zinc-200 bg-white p-6">
            <div class="flex items-center justify-between gap-4">
                <div>
                    <h2 class="text-xl font-semibold text-zinc-900">{{ __('Four-step main flow') }}</h2>
                    <p class="mt-1 text-sm text-zinc-600">
                        {{ __('Follow this path whenever you need to understand where to start or what to do next.') }}
                    </p>
                </div>
                <flux:badge color="emerald">{{ __('Quick tip') }}</flux:badge>
            </div>

            <ol class="mt-6 space-y-4">
                @foreach ($flowSteps as $index => $step)
                    <li class="flex gap-4 rounded-xl border border-zinc-100 bg-zinc-50/60 p-4">
                        <span class="mt-1 flex h-8 w-8 items-center justify-center rounded-full bg-zinc-900 text-sm font-semibold text-white">
                            {{ $index + 1 }}
                        </span>
                        <div>
                            <p class="text-sm font-semibold text-zinc-900">{{ $step['title'] }}</p>
                            <p class="mt-1 text-sm text-zinc-600">{{ $step['description'] }}</p>
                        </div>
                    </li>
                @endforeach
            </ol>

            <div class="mt-6 grid gap-4 md:grid-cols-2">
                <div class="rounded-xl border border-emerald-100 bg-emerald-50/70 p-4">
                    <p class="text-sm font-semibold text-emerald-900">{{ __('Need help choosing what to do next?') }}</p>
                    <p class="mt-1 text-xs text-emerald-800">
                        {{ __('Open the Dashboard to measure progress and receive smart suggestions any time.') }}
                    </p>
                    <flux:button
                        class="mt-3"
                        size="sm"
                        variant="ghost"
                        color="emerald"
                        icon="home"
                        :href="route('dashboard')"
                        wire:navigate
                    >
                        {{ __('Open dashboard') }}
                    </flux:button>
                </div>

                <div class="rounded-xl border border-indigo-100 bg-indigo-50/70 p-4">
                    <p class="text-sm font-semibold text-indigo-900">{{ __('Want to review something quickly?') }}</p>
                    <p class="mt-1 text-xs text-indigo-800">
                        {{ __('Use the "How it works" FAB near the footer or jump into the study modes to see the next cards.') }}
                    </p>
                    <flux:button
                        class="mt-3"
                        size="sm"
                        variant="ghost"
                        color="indigo"
                        icon="sparkles"
                        :href="route('study.flashcards')"
                        wire:navigate
                    >
                        {{ __('Go to flashcards') }}
                    </flux:button>
                </div>
            </div>
        </div>

        <div class="space-y-6">
            <div class="rounded-2xl border border-zinc-200 bg-white p-6 self-start">
                <h3 class="text-lg font-semibold text-zinc-900">{{ __('Quick checklist') }}</h3>
                <p class="mt-1 text-sm text-zinc-600">
                    {{ __('Use this checklist before leaving the screen to guarantee every action was recorded.') }}
                </p>

                <ul class="mt-5 space-y-3 text-sm text-zinc-700">
                    <li class="flex items-start gap-3">
                        <flux:icon.check-circle class="mt-0.5 h-4 w-4 text-emerald-500" />
                        <span>{{ __('Did I add notes and mark which ones became flashcards?') }}</span>
                    </li>
                    <li class="flex items-start gap-3">
                        <flux:icon.check-circle class="mt-0.5 h-4 w-4 text-emerald-500" />
                        <span>{{ __('Did I update the correct discipline or should I move this content?') }}</span>
                    </li>
                    <li class="flex items-start gap-3">
                        <flux:icon.check-circle class="mt-0.5 h-4 w-4 text-emerald-500" />
                        <span>{{ __('Did I record important logs for future reference?') }}</span>
                    </li>
                    <li class="flex items-start gap-3">
                        <flux:icon.check-circle class="mt-0.5 h-4 w-4 text-emerald-500" />
                        <span>{{ __('Is there any study session pending to finish?') }}</span>
                    </li>
                </ul>
            </div>

            <div class="rounded-2xl border border-zinc-200 bg-white p-6 self-start">
                <h3 class="text-lg font-semibold text-zinc-900">{{ __('More help resources') }}</h3>
                <p class="mt-1 text-sm text-zinc-600">
                    {{ __('Use these quick references when you need extra help or must document an issue.') }}
                </p>

                <ul class="mt-5 space-y-4 text-sm text-zinc-700">
                    <li class="flex items-start gap-3">
                        <flux:icon.book-open class="mt-0.5 h-4 w-4 text-sky-500" />
                        <div>
                            <p class="text-sm font-semibold text-zinc-900">{{ __('Review the guide sections') }}</p>
                            <p class="text-sm text-zinc-600">
                                {{ __('Open this help page in another tab and highlight the screen related to your question before contacting support.') }}
                            </p>
                        </div>
                    </li>
                    <li class="flex items-start gap-3">
                        <flux:icon.queue-list class="mt-0.5 h-4 w-4 text-sky-500" />
                        <div>
                            <p class="text-sm font-semibold text-zinc-900">{{ __('Confirm data in the activity log') }}</p>
                            <p class="text-sm text-zinc-600">
                                {{ __('Filter by notebook, discipline, or action type to confirm the activity before repeating the step.') }}
                            </p>
                        </div>
                    </li>
                    <li class="flex items-start gap-3">
                        <flux:icon.chat-bubble-bottom-center-text class="mt-0.5 h-4 w-4 text-sky-500" />
                        <div>
                            <p class="text-sm font-semibold text-zinc-900">{{ __('Prepare context for support') }}</p>
                            <p class="text-sm text-zinc-600">
                                {{ __('Send screenshots or a short clip with timestamps and notebook names when emailing support.') }}
                            </p>
                        </div>
                    </li>
                </ul>
            </div>
        </div>
    </div>

    <div class="space-y-6">
        <div>
            <h2 class="text-xl font-semibold text-zinc-900">{{ __('Screen guide') }}</h2>
            <p class="mt-1 text-sm text-zinc-600">{{ __('Pick any area below to quickly understand what the screen does and which actions are available.') }}</p>
        </div>

        <div class="grid gap-4 md:grid-cols-2">
            @foreach ($screenGuides as $guide)
                <div class="rounded-2xl border border-zinc-200 bg-white p-5">
                    <div class="flex items-center justify-between">
                        <p class="text-base font-semibold text-zinc-900">{{ $guide['title'] }}</p>
                        <flux:badge>{{ __('Dedicated flow') }}</flux:badge>
                    </div>
                    <ul class="mt-4 space-y-3 text-sm text-zinc-600">
                        @foreach ($guide['tips'] as $tip)
                            <li class="flex items-start gap-3">
                                <span class="mt-1 h-1.5 w-1.5 rounded-full bg-zinc-400"></span>
                                <span>{{ $tip }}</span>
                            </li>
                        @endforeach
                    </ul>
                </div>
            @endforeach
        </div>
    </div>

    <div class="rounded-2xl border border-dashed border-zinc-300 bg-zinc-50 p-6 text-center">
        <p class="text-base font-semibold text-zinc-900">{{ __('Still have questions?') }}</p>
        <p class="mt-1 text-sm text-zinc-600">{{ __('Review the history in Logs or contact support with screenshots of this guide.') }}</p>
        <div class="mt-4 flex flex-wrap justify-center gap-3">
            <flux:button
                variant="ghost"
                icon="queue-list"
                :href="route('logs.index')"
                wire:navigate
            >
                {{ __('Open activity log') }}
            </flux:button>
            <flux:button
                variant="primary"
                icon="chat-bubble-left-right"
                href="mailto:contato@booknotes.com.br"
            >
                {{ __('Contact support') }}
            </flux:button>
        </div>
    </div>
</div>
