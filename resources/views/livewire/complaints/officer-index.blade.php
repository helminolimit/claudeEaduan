<div>
    <flux:main>
        <div class="mb-6 flex items-center justify-between">
            <flux:heading size="xl">{{ __('My Assigned Complaints') }}</flux:heading>
        </div>

        {{-- Filters --}}
        <div class="mb-4 flex flex-wrap gap-3">
            <div class="min-w-48 flex-1">
                <flux:input
                    wire:model.live.debounce.300ms="search"
                    icon="magnifying-glass"
                    placeholder="{{ __('Search by title or reference...') }}"
                    clearable
                />
            </div>
            <flux:select wire:model.live="filterStatus" placeholder="{{ __('All statuses') }}">
                @foreach ($this->statuses as $status)
                    <flux:select.option value="{{ $status->value }}">{{ $status->label() }}</flux:select.option>
                @endforeach
            </flux:select>
            <flux:select wire:model.live="filterPriority" placeholder="{{ __('All priorities') }}">
                @foreach ($this->priorities as $priority)
                    <flux:select.option value="{{ $priority->value }}">{{ $priority->label() }}</flux:select.option>
                @endforeach
            </flux:select>
        </div>

        {{-- Per-page --}}
        <div class="mb-4 flex items-center gap-2">
            <flux:select wire:model.live="perPage" class="w-20">
                <flux:select.option value="10">10</flux:select.option>
                <flux:select.option value="25">25</flux:select.option>
                <flux:select.option value="50">50</flux:select.option>
            </flux:select>
            <span class="text-sm text-zinc-500">{{ __('entries per page') }}</span>
        </div>

        <flux:table :paginate="$this->complaints">
            <flux:table.columns>
                <flux:table.column class="w-12">{{ __('Bil') }}</flux:table.column>
                <flux:table.column
                    sortable
                    :sorted="$sortBy === 'aduan_no'"
                    :direction="$sortDirection"
                    wire:click="sort('aduan_no')"
                >{{ __('Reference') }}</flux:table.column>
                <flux:table.column
                    sortable
                    :sorted="$sortBy === 'title'"
                    :direction="$sortDirection"
                    wire:click="sort('title')"
                >{{ __('Title') }}</flux:table.column>
                <flux:table.column>{{ __('Category') }}</flux:table.column>
                <flux:table.column>{{ __('Status') }}</flux:table.column>
                <flux:table.column>{{ __('Priority') }}</flux:table.column>
                <flux:table.column>{{ __('Filed By') }}</flux:table.column>
                <flux:table.column
                    sortable
                    :sorted="$sortBy === 'created_at'"
                    :direction="$sortDirection"
                    wire:click="sort('created_at')"
                >{{ __('Date') }}</flux:table.column>
                <flux:table.column />
            </flux:table.columns>

            <flux:table.rows>
                @forelse ($this->complaints as $loop_index => $complaint)
                    <flux:table.row :key="$complaint->id" wire:key="complaint-{{ $complaint->id }}">
                        <flux:table.cell class="text-sm tabular-nums text-zinc-400">
                            {{ $this->complaints->firstItem() + $loop_index }}
                        </flux:table.cell>
                        <flux:table.cell class="font-mono text-sm text-zinc-500 dark:text-zinc-400">
                            {{ $complaint->aduan_no }}
                        </flux:table.cell>
                        <flux:table.cell variant="strong">{{ $complaint->title }}</flux:table.cell>
                        <flux:table.cell>{{ $complaint->category?->name ?? '—' }}</flux:table.cell>
                        <flux:table.cell>
                            <flux:badge variant="solid" color="{{ $complaint->status->color() }}" size="sm">
                                {{ $complaint->status->label() }}
                            </flux:badge>
                        </flux:table.cell>
                        <flux:table.cell>
                            <flux:badge variant="outline" color="{{ $complaint->priority->color() }}" size="sm">
                                {{ $complaint->priority->label() }}
                            </flux:badge>
                        </flux:table.cell>
                        <flux:table.cell class="text-zinc-500 dark:text-zinc-400">
                            {{ $complaint->user?->name ?? '—' }}
                        </flux:table.cell>
                        <flux:table.cell class="whitespace-nowrap text-zinc-500 dark:text-zinc-400">
                            {{ $complaint->created_at->diffForHumans() }}
                        </flux:table.cell>
                        <flux:table.cell>
                            <flux:dropdown>
                                <flux:button variant="ghost" size="sm" icon="ellipsis-horizontal" inset="top bottom" />
                                <flux:menu>
                                    <flux:menu.item :href="route('aduan.show', $complaint)" wire:navigate icon="eye">
                                        {{ __('View') }}
                                    </flux:menu.item>
                                    <flux:menu.separator />
                                    <flux:menu.item icon="arrow-path" wire:click="openStatusModal({{ $complaint->id }})">
                                        {{ __('Update Status') }}
                                    </flux:menu.item>
                                </flux:menu>
                            </flux:dropdown>
                        </flux:table.cell>
                    </flux:table.row>
                @empty
                    <flux:table.row>
                        <flux:table.cell colspan="9" class="py-12 text-center text-zinc-500 dark:text-zinc-400">
                            {{ __('No complaints assigned to you.') }}
                        </flux:table.cell>
                    </flux:table.row>
                @endforelse
            </flux:table.rows>
        </flux:table>
    </flux:main>

    {{-- Update Status Modal --}}
    <flux:modal
        name="update-status"
        class="min-w-[28rem]"
        :closable="false"
        x-data="{ loading: false }"
        x-on:cancel="loading && $event.preventDefault()"
        x-on:livewire:commit.window="loading = false"
    >
        <div class="relative space-y-6">
            <div wire:loading wire:target="updateStatus" class="absolute inset-0 z-10 flex items-center justify-center rounded-lg bg-white/80 dark:bg-zinc-900/80">
                <flux:icon name="arrow-path" class="size-6 animate-spin text-zinc-500" />
            </div>
            <div>
                <flux:heading size="lg">{{ __('Update Status') }}</flux:heading>
            </div>
            <flux:select wire:model.live="newStatus" :label="__('New Status')" required>
                @foreach ($this->statuses as $status)
                    <flux:select.option value="{{ $status->value }}">{{ $status->label() }}</flux:select.option>
                @endforeach
            </flux:select>
            @if ($newStatus === 'rejected')
                <flux:textarea
                    wire:model="rejectionReason"
                    :label="__('Rejection Reason')"
                    rows="3"
                    required
                />
            @endif
            <div class="flex gap-2">
                <flux:spacer />
                <flux:modal.close>
                    <flux:button variant="ghost">{{ __('Cancel') }}</flux:button>
                </flux:modal.close>
                <flux:button variant="primary" wire:click="updateStatus" @click="loading = true">
                    {{ __('Update') }}
                </flux:button>
            </div>
        </div>
    </flux:modal>
</div>
