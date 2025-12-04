<span class="inline-flex">
    @if ($hasUnread)
        <span
            class="relative inline-flex h-2.5 w-2.5"
            role="status"
            aria-label="{{ __('You have unread notifications') }}"
        >
            <span class="absolute inline-flex h-full w-full rounded-full bg-rose-300 opacity-75 animate-ping"></span>
            <span class="relative inline-flex h-2.5 w-2.5 rounded-full bg-rose-500"></span>
        </span>
    @endif
</span>
