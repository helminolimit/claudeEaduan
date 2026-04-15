<div>
    <flux:main>
        <div class="mb-6 flex items-center justify-between">
            <flux:heading size="xl">{{ __('Manage Complaints') }}</flux:heading>
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

        {{-- Per-page + entry count --}}
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
                <flux:table.column>{{ __('Officer') }}</flux:table.column>
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
                        <flux:table.cell class="text-zinc-500 dark:text-zinc-400">
                            {{ $complaint->officer?->name ?? '—' }}
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
                                    <flux:menu.item icon="user-plus" wire:click="openAssignModal({{ $complaint->id }})">
                                        {{ __('Assign Officer') }}
                                    </flux:menu.item>
                                    <flux:menu.item icon="arrow-path" wire:click="openStatusModal({{ $complaint->id }})">
                                        {{ __('Update Status') }}
                                    </flux:menu.item>
                                    <flux:menu.item icon="flag" wire:click="openPriorityModal({{ $complaint->id }})">
                                        {{ __('Set Priority') }}
                                    </flux:menu.item>
                                    <flux:menu.separator />
                                    <flux:modal.trigger name="delete-{{ $complaint->id }}">
                                        <flux:menu.item variant="danger" icon="trash">{{ __('Delete') }}</flux:menu.item>
                                    </flux:modal.trigger>
                                </flux:menu>
                            </flux:dropdown>

                            {{-- Delete confirmation --}}
                            <flux:modal
                                name="delete-{{ $complaint->id }}"
                                class="min-w-[22rem]"
                                :closable="false"
                                x-data="{ loading: false }"
                                x-on:cancel="loading && $event.preventDefault()"
                                x-on:livewire:commit.window="loading = false"
                            >
                                <div class="relative space-y-6">
                                    <div wire:loading wire:target="delete({{ $complaint->id }})" class="absolute inset-0 z-10 flex items-center justify-center rounded-lg bg-white/80 dark:bg-zinc-900/80">
                                        <flux:icon name="arrow-path" class="size-6 animate-spin text-zinc-500" />
                                    </div>
                                    <div>
                                        <flux:heading size="lg">{{ __('Delete complaint?') }}</flux:heading>
                                        <flux:subheading>{{ __('This action cannot be undone.') }}</flux:subheading>
                                    </div>
                                    <div class="flex gap-2">
                                        <flux:spacer />
                                        <flux:modal.close>
                                            <flux:button variant="ghost">{{ __('Cancel') }}</flux:button>
                                        </flux:modal.close>
                                        <flux:button variant="danger" wire:click="delete({{ $complaint->id }})" @click="loading = true">
                                            {{ __('Delete') }}
                                        </flux:button>
                                    </div>
                                </div>
                            </flux:modal>
                        </flux:table.cell>
                    </flux:table.row>
                @empty
                    <flux:table.row>
                        <flux:table.cell colspan="10" class="py-12 text-center text-zinc-500 dark:text-zinc-400">
                            {{ __('No complaints found.') }}
                        </flux:table.cell>
                    </flux:table.row>
                @endforelse
            </flux:table.rows>
        </flux:table>
    </flux:main>

    {{-- Assign Officer Modal --}}
    <flux:modal
        name="assign-officer"
        class="min-w-[28rem]"
        :closable="false"
        x-data="{ loading: false }"
        x-on:cancel="loading && $event.preventDefault()"
        x-on:livewire:commit.window="loading = false"
    >
        <div class="relative space-y-6">
            <div wire:loading wire:target="assign" class="absolute inset-0 z-10 flex items-center justify-center rounded-lg bg-white/80 dark:bg-zinc-900/80">
                <flux:icon name="arrow-path" class="size-6 animate-spin text-zinc-500" />
            </div>
            <div>
                <flux:heading size="lg">{{ __('Assign Officer') }}</flux:heading>
                <flux:subheading>{{ __('Select an officer to handle this complaint.') }}</flux:subheading>
            </div>
            <flux:select wire:model="assignOfficerId" :label="__('Officer')" required>
                <flux:select.option value="">{{ __('Select officer') }}</flux:select.option>
                @foreach ($this->officers as $officer)
                    <flux:select.option value="{{ $officer->id }}">{{ $officer->name }}</flux:select.option>
                @endforeach
            </flux:select>
            <div class="flex gap-2">
                <flux:spacer />
                <flux:modal.close>
                    <flux:button variant="ghost">{{ __('Cancel') }}</flux:button>
                </flux:modal.close>
                <flux:button variant="primary" wire:click="assign" @click="loading = true">
                    {{ __('Assign') }}
                </flux:button>
            </div>
        </div>
    </flux:modal>

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

    {{-- Update Priority Modal --}}
    <flux:modal
        name="update-priority"
        class="min-w-[24rem]"
        :closable="false"
        x-data="{ loading: false }"
        x-on:cancel="loading && $event.preventDefault()"
        x-on:livewire:commit.window="loading = false"
    >
        <div class="relative space-y-6">
            <div wire:loading wire:target="updatePriority" class="absolute inset-0 z-10 flex items-center justify-center rounded-lg bg-white/80 dark:bg-zinc-900/80">
                <flux:icon name="arrow-path" class="size-6 animate-spin text-zinc-500" />
            </div>
            <div>
                <flux:heading size="lg">{{ __('Set Priority') }}</flux:heading>
            </div>
            <flux:select wire:model="newPriority" :label="__('Priority')" required>
                @foreach ($this->priorities as $priority)
                    <flux:select.option value="{{ $priority->value }}">{{ $priority->label() }}</flux:select.option>
                @endforeach
            </flux:select>
            <div class="flex gap-2">
                <flux:spacer />
                <flux:modal.close>
                    <flux:button variant="ghost">{{ __('Cancel') }}</flux:button>
                </flux:modal.close>
                <flux:button variant="primary" wire:click="updatePriority" @click="loading = true">
                    {{ __('Save') }}
                </flux:button>
            </div>
        </div>
    </flux:modal>
</div>
