<div>
    <flux:main>
    <flux:heading size="xl" class="mb-1">{{ __('Notifications') }}</flux:heading>
    <flux:subheading class="mb-6">{{ __('Your notification history.') }}</flux:subheading>

    {{-- Filter row --}}
    <div class="mb-4 flex flex-wrap items-center gap-3">
        <flux:select wire:model.live="filterRead" class="w-44">
            <flux:select.option value="">{{ __('All') }}</flux:select.option>
            <flux:select.option value="0">{{ __('Unread') }}</flux:select.option>
            <flux:select.option value="1">{{ __('Read') }}</flux:select.option>
        </flux:select>

        @if ($this->unreadCount > 0)
            <flux:button wire:click="markAllRead" size="sm" variant="ghost" icon="check-circle">
                {{ __('Mark all read') }}
            </flux:button>
        @endif

        <div class="ml-auto flex items-center gap-2">
            <span class="text-sm text-zinc-500">{{ __('Cari:') }}</span>
            <div class="w-48">
                <flux:input wire:model.live.debounce.300ms="search" placeholder="{{ __('Search...') }}" clearable size="sm" />
            </div>
        </div>
    </div>

    {{-- Per-page + count row --}}
    <div class="mb-4 flex items-center gap-2">
        <flux:select wire:model.live="perPage" class="w-24">
            <flux:select.option value="10">10</flux:select.option>
            <flux:select.option value="25">25</flux:select.option>
            <flux:select.option value="50">50</flux:select.option>
            <flux:select.option value="100">100</flux:select.option>
        </flux:select>
        <span class="text-sm text-zinc-500">{{ __('entries per page') }}</span>
    </div>

    <flux:table :paginate="$this->notifications">
        <flux:table.columns>
            <flux:table.column class="w-12">{{ __('Bil') }}</flux:table.column>
            <flux:table.column>{{ __('Message') }}</flux:table.column>
            <flux:table.column class="w-32">{{ __('Status') }}</flux:table.column>
            <flux:table.column class="w-44">{{ __('Date') }}</flux:table.column>
            <flux:table.column class="w-12"></flux:table.column>
        </flux:table.columns>

        <flux:table.rows>
            @forelse ($this->notifications as $loop_index => $notification)
                <flux:table.row
                    :key="$notification->id"
                    wire:key="notif-row-{{ $notification->id }}"
                    class="{{ $notification->is_read ? 'opacity-60' : '' }}"
                >
                    <flux:table.cell class="text-sm tabular-nums text-zinc-400">
                        {{ $this->notifications->firstItem() + $loop_index }}
                    </flux:table.cell>

                    <flux:table.cell>
                        <div class="flex items-start gap-2">
                            @if (! $notification->is_read)
                                <span class="mt-1.5 size-2 shrink-0 rounded-full bg-blue-500"></span>
                            @else
                                <span class="mt-1.5 size-2 shrink-0"></span>
                            @endif
                            <span class="text-sm {{ $notification->is_read ? '' : 'font-medium' }}">
                                {{ $notification->message }}
                            </span>
                        </div>
                    </flux:table.cell>

                    <flux:table.cell>
                        @if ($notification->is_read)
                            <flux:badge color="zinc" size="sm">{{ __('Read') }}</flux:badge>
                        @else
                            <flux:badge color="blue" size="sm">{{ __('Unread') }}</flux:badge>
                        @endif
                    </flux:table.cell>

                    <flux:table.cell class="text-sm text-zinc-400">
                        {{ $notification->created_at->format('d/m/Y H:i') }}
                    </flux:table.cell>

                    <flux:table.cell>
                        @if ($notification->complaint_id)
                            <flux:button
                                wire:click="markRead({{ $notification->id }})"
                                variant="ghost"
                                size="sm"
                                icon="arrow-top-right-on-square"
                            />
                        @elseif (! $notification->is_read)
                            <flux:button
                                wire:click="markRead({{ $notification->id }})"
                                variant="ghost"
                                size="sm"
                                icon="check"
                            />
                        @endif
                    </flux:table.cell>
                </flux:table.row>
            @empty
                <flux:table.row>
                    <flux:table.cell colspan="5" class="py-12 text-center text-zinc-500">
                        {{ __('No notifications found.') }}
                    </flux:table.cell>
                </flux:table.row>
            @endforelse
        </flux:table.rows>
    </flux:table>
    </flux:main>
</div>
