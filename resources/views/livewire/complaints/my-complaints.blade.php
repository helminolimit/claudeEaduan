<div>
    <flux:main>
        <div class="mb-6 flex items-center justify-between">
            <flux:heading size="xl">{{ __('My Complaints') }}</flux:heading>
            <flux:button :href="route('aduan.create')" wire:navigate variant="primary" icon="plus">
                {{ __('New Complaint') }}
            </flux:button>
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
                <flux:table.column
                    sortable
                    :sorted="$sortBy === 'created_at'"
                    :direction="$sortDirection"
                    wire:click="sort('created_at')"
                >{{ __('Submitted') }}</flux:table.column>
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
                        <flux:table.cell class="whitespace-nowrap text-zinc-500 dark:text-zinc-400">
                            {{ $complaint->created_at->format('d M Y') }}
                        </flux:table.cell>
                        <flux:table.cell>
                            <flux:button
                                :href="route('aduan.show', $complaint)"
                                wire:navigate
                                variant="ghost"
                                size="sm"
                                icon="eye"
                            >
                                {{ __('View') }}
                            </flux:button>
                        </flux:table.cell>
                    </flux:table.row>
                @empty
                    <flux:table.row>
                        <flux:table.cell colspan="7" class="py-12 text-center text-zinc-500 dark:text-zinc-400">
                            <div class="flex flex-col items-center gap-3">
                                <flux:icon name="chat-bubble-left-ellipsis" class="size-10 text-zinc-300" />
                                <p>{{ __('You have not submitted any complaints yet.') }}</p>
                                <flux:button :href="route('aduan.create')" wire:navigate variant="primary" size="sm" icon="plus">
                                    {{ __('Submit your first complaint') }}
                                </flux:button>
                            </div>
                        </flux:table.cell>
                    </flux:table.row>
                @endforelse
            </flux:table.rows>
        </flux:table>
    </flux:main>
</div>
