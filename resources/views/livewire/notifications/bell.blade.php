<div>
    <flux:dropdown position="bottom" align="end">
        <flux:button
            icon="bell"
            variant="ghost"
            size="sm"
            :label="__('Notifications')"
            class="relative"
        >
            @if ($this->unreadCount > 0)
                <span class="absolute top-0.5 right-0.5 flex h-4 min-w-4 items-center justify-center rounded-full bg-red-500 px-1 text-[10px] font-bold text-white leading-none pointer-events-none">
                    {{ $this->unreadCount > 99 ? '99+' : $this->unreadCount }}
                </span>
            @endif
        </flux:button>

        <flux:menu class="w-80">
            <div class="flex items-center justify-between border-b border-zinc-100 px-3 py-2 dark:border-zinc-700">
                <flux:heading size="sm">{{ __('Notifications') }}</flux:heading>
                @if ($this->unreadCount > 0)
                    <button
                        wire:click="markAllRead"
                        class="text-xs text-blue-500 hover:text-blue-700 dark:text-blue-400 dark:hover:text-blue-300"
                    >
                        {{ __('Mark all read') }}
                    </button>
                @endif
            </div>

            <div class="max-h-80 overflow-y-auto">
                @forelse ($this->recentNotifications as $notification)
                    <div
                        wire:key="bell-notif-{{ $notification->id }}"
                        wire:click="markRead({{ $notification->id }})"
                        class="flex cursor-pointer items-start gap-2 border-b border-zinc-50 px-3 py-2.5 hover:bg-zinc-50 dark:border-zinc-800 dark:hover:bg-zinc-800/60 {{ $notification->is_read ? 'opacity-60' : '' }}"
                    >
                        <span class="mt-1.5 size-2 shrink-0 rounded-full {{ $notification->is_read ? '' : 'bg-blue-500' }}"></span>
                        <div class="min-w-0 flex-1">
                            <p class="text-sm leading-snug {{ $notification->is_read ? '' : 'font-medium' }}">
                                {{ $notification->message }}
                            </p>
                            <p class="mt-0.5 text-xs text-zinc-400">
                                {{ $notification->created_at->diffForHumans() }}
                            </p>
                        </div>
                    </div>
                @empty
                    <div class="px-3 py-8 text-center text-sm text-zinc-400">
                        {{ __('No notifications.') }}
                    </div>
                @endforelse
            </div>

            <flux:menu.separator />

            <flux:menu.item :href="route('notifications.index')" wire:navigate icon="inbox">
                {{ __('View all notifications') }}
            </flux:menu.item>
        </flux:menu>
    </flux:dropdown>
</div>
