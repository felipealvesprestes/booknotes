<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        @include('partials.head')
    </head>
    <body class="min-h-screen bg-white">
        <flux:sidebar sticky stashable collapsible class="border-e border-zinc-200 bg-white">
            <flux:sidebar.header class="mb-2">
                <div class="flex items-center gap-2 in-data-flux-sidebar-collapsed-desktop:hidden">
                    <flux:sidebar.toggle class="lg:hidden" icon="x-mark" />

                    <a href="{{ route('dashboard') }}" class="me-5 flex items-center space-x-2 rtl:space-x-reverse in-data-flux-sidebar-collapsed-desktop:hidden" wire:navigate>
                        <x-app-logo />
                    </a>
                </div>

                <flux:sidebar.collapse class="hidden lg:flex" :tooltip="__('Toggle sidebar')" />
            </flux:sidebar.header>

            <flux:navlist variant="outline" class="space-y-6">
                <flux:navlist.group :heading="__('Workspace')" class="sidebar-nav-group">
                    <flux:navlist.item icon="home" :href="route('dashboard')" :current="request()->routeIs('dashboard')" wire:navigate>{{ __('Dashboard') }}</flux:navlist.item>
                    <flux:navlist.item icon="book-open" :href="route('notebooks.index')" :current="request()->routeIs('notebooks.*')" wire:navigate>{{ __('Notebooks') }}</flux:navlist.item>
                    <flux:navlist.item icon="book-open-text" :href="route('disciplines.index')" :current="request()->routeIs('disciplines.*')" wire:navigate>{{ __('Disciplines') }}</flux:navlist.item>
                </flux:navlist.group>

                <flux:navlist.group :heading="__('Library')" class="sidebar-nav-group">
                    <flux:navlist.item
                        icon="document"
                        :href="route('notes.library')"
                        :current="request()->routeIs('notes.*') && ! request()->routeIs('notes.export')"
                        wire:navigate
                    >
                        {{ __('Notes') }}
                    </flux:navlist.item>
                    <flux:navlist.item icon="arrow-down-tray" :href="route('notes.export')" :current="request()->routeIs('notes.export')" wire:navigate>{{ __('PDF exports') }}</flux:navlist.item>
                    <flux:navlist.item icon="document-text" :href="route('pdfs.index')" :current="request()->routeIs('pdfs.*')" wire:navigate>{{ __('Document library') }}</flux:navlist.item>
                </flux:navlist.group>

                <flux:navlist.group :heading="__('Practice')" class="sidebar-nav-group">
                    <flux:navlist.item icon="bolt" :href="route('study.flashcards')" :current="request()->routeIs('study.flashcards')" wire:navigate>{{ __('Flashcards') }}</flux:navlist.item>
                    <flux:navlist.item icon="sparkles" :href="route('study.exercises')" :current="request()->routeIs('study.exercises')" wire:navigate>{{ __('Exercises') }}</flux:navlist.item>
                    <flux:navlist.item icon="clipboard-document-check" :href="route('study.simulated')" :current="request()->routeIs('study.simulated')" wire:navigate>{{ __('Simulated test') }}</flux:navlist.item>
                </flux:navlist.group>

                <flux:navlist.group :heading="__('Activity')" class="sidebar-nav-group">
                    <flux:navlist.item icon="queue-list" :href="route('logs.index')" :current="request()->routeIs('logs.*')" wire:navigate>{{ __('Logs') }}</flux:navlist.item>
                </flux:navlist.group>

                <flux:navlist.group :heading="__('Help')" class="sidebar-nav-group">
                    <flux:navlist.item
                        icon="question-mark-circle"
                        :href="route('help.guide')"
                        :current="request()->routeIs('help.guide')"
                        wire:navigate
                    >
                        {{ __('Platform guide') }}
                    </flux:navlist.item>
                </flux:navlist.group>
            </flux:navlist>

            <flux:spacer />

            <!-- Desktop User Menu -->
            <flux:dropdown class="hidden lg:block" position="bottom" align="start" data-desktop-user-menu>
                <flux:profile
                    :name="auth()->user()->name"
                    :initials="auth()->user()->initials()"
                    icon:trailing="chevrons-up-down"
                />

                <flux:menu class="w-[220px]">
                    <flux:menu.radio.group>
                        <div class="p-0 text-sm font-normal">
                            <div class="flex items-center gap-2 px-1 py-1.5 text-start text-sm">
                                <span class="relative flex h-8 w-8 shrink-0 overflow-hidden rounded-lg">
                                    <span class="flex h-full w-full items-center justify-center rounded-lg bg-neutral-200 text-black">
                                        {{ auth()->user()->initials() }}
                                    </span>
                                </span>

                                <div class="grid flex-1 text-start text-sm leading-tight">
                                    <span class="truncate font-semibold">{{ auth()->user()->name }}</span>
                                    <span class="truncate text-xs">{{ auth()->user()->email }}</span>
                                </div>
                            </div>
                        </div>
                    </flux:menu.radio.group>

                    <flux:menu.separator />

                    <flux:menu.radio.group>
                        <flux:menu.item :href="route('profile.edit')" icon="cog" wire:navigate>{{ __('Settings') }}</flux:menu.item>
                    </flux:menu.radio.group>

                    <flux:menu.separator />

                    <form method="POST" action="{{ route('logout') }}" class="w-full">
                        @csrf
                        <flux:menu.item as="button" type="submit" icon="arrow-right-start-on-rectangle" class="w-full">
                            {{ __('Log Out') }}
                        </flux:menu.item>
                    </form>
                </flux:menu>
            </flux:dropdown>
        </flux:sidebar>

        <!-- Mobile User Menu -->
        <flux:header class="lg:hidden">
            <flux:sidebar.toggle class="lg:hidden" icon="bars-2" inset="left" />

            <flux:spacer />

            <flux:dropdown position="top" align="end">
                <flux:profile
                    class="border border-zinc-200 bg-white/95 py-1 gap-1.5 hover:bg-white focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-indigo-500/40"
                    :initials="auth()->user()->initials()"
                    icon-trailing="chevron-down"
                />

                <flux:menu>
                    <flux:menu.radio.group>
                        <div class="p-0 text-sm font-normal">
                            <div class="flex items-center gap-2 px-1 py-1.5 text-start text-sm">
                                <span class="relative flex h-8 w-8 shrink-0 overflow-hidden rounded-lg">
                                    <span class="flex h-full w-full items-center justify-center rounded-lg bg-neutral-200 text-black">
                                        {{ auth()->user()->initials() }}
                                    </span>
                                </span>

                                <div class="grid flex-1 text-start text-sm leading-tight">
                                    <span class="truncate font-semibold">{{ auth()->user()->name }}</span>
                                    <span class="truncate text-xs">{{ auth()->user()->email }}</span>
                                </div>
                            </div>
                        </div>
                    </flux:menu.radio.group>

                    <flux:menu.separator />

                    <flux:menu.radio.group>
                        <flux:menu.item :href="route('profile.edit')" icon="cog" wire:navigate>{{ __('Settings') }}</flux:menu.item>
                    </flux:menu.radio.group>

                    <flux:menu.separator />

                    <form method="POST" action="{{ route('logout') }}" class="w-full">
                        @csrf
                        <flux:menu.item as="button" type="submit" icon="arrow-right-start-on-rectangle" class="w-full">
                            {{ __('Log Out') }}
                        </flux:menu.item>
                    </form>
                </flux:menu>
            </flux:dropdown>
        </flux:header>

        {{ $slot }}

        <x-help.how-it-works-fab />

        @fluxScripts
    </body>
</html>
