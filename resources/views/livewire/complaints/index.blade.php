<div>
    <flux:main>
        <div class="flex items-center justify-between mb-6">
            <flux:heading size="xl">{{ __('Complaints') }}</flux:heading>
            <flux:button variant="primary" icon="plus" wire:click="openCreateModal">
                {{ __('New Complaint') }}
            </flux:button>
        </div>

        <div class="flex flex-wrap gap-3 mb-4">
            <div class="flex-1 min-w-48">
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

        <flux:table :paginate="$complaints">
            <flux:table.columns>
                <flux:table.column>{{ __('Reference') }}</flux:table.column>
                <flux:table.column>{{ __('Title') }}</flux:table.column>
                <flux:table.column>{{ __('Category') }}</flux:table.column>
                <flux:table.column>{{ __('Status') }}</flux:table.column>
                <flux:table.column>{{ __('Priority') }}</flux:table.column>
                <flux:table.column>{{ __('Filed By') }}</flux:table.column>
                <flux:table.column>{{ __('Date') }}</flux:table.column>
                <flux:table.column />
            </flux:table.columns>

            <flux:table.rows>
                @forelse ($complaints as $complaint)
                    <flux:table.row :key="$complaint->id">
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
                        <flux:table.cell class="text-zinc-500 dark:text-zinc-400 whitespace-nowrap">
                            {{ $complaint->created_at->diffForHumans() }}
                        </flux:table.cell>
                        <flux:table.cell>
                            <div class="flex items-center gap-2 justify-end">
                                <flux:button
                                    size="sm"
                                    icon="pencil"
                                    wire:click="openEditModal({{ $complaint->id }})"
                                >
                                    {{ __('Edit') }}
                                </flux:button>

                                <flux:modal.trigger name="delete-{{ $complaint->id }}">
                                    <flux:button size="sm" icon="trash" variant="danger">
                                        {{ __('Delete') }}
                                    </flux:button>
                                </flux:modal.trigger>

                                <flux:modal name="delete-{{ $complaint->id }}" class="min-w-[22rem]">
                                    <div class="space-y-6">
                                        <div>
                                            <flux:heading size="lg">{{ __('Delete complaint?') }}</flux:heading>
                                            <flux:text class="mt-2">
                                                {{ __('Are you sure you want to delete ":title"? This action cannot be undone.', ['title' => $complaint->title]) }}
                                            </flux:text>
                                        </div>
                                        <div class="flex gap-2">
                                            <flux:spacer />
                                            <flux:modal.close>
                                                <flux:button variant="ghost">{{ __('Cancel') }}</flux:button>
                                            </flux:modal.close>
                                            <flux:button
                                                variant="danger"
                                                wire:click="delete({{ $complaint->id }})"
                                            >
                                                {{ __('Delete') }}
                                            </flux:button>
                                        </div>
                                    </div>
                                </flux:modal>
                            </div>
                        </flux:table.cell>
                    </flux:table.row>
                @empty
                    <flux:table.row>
                        <flux:table.cell colspan="8" class="text-center text-zinc-500 dark:text-zinc-400 py-8">
                            {{ __('No complaints found.') }}
                        </flux:table.cell>
                    </flux:table.row>
                @endforelse
            </flux:table.rows>
        </flux:table>
    </flux:main>

    <flux:modal name="complaint-form" class="min-w-[36rem]">
        <div class="space-y-6">
            <flux:heading size="lg">
                {{ $editingId ? __('Edit Complaint') : __('New Complaint') }}
            </flux:heading>

            <flux:input
                wire:model="title"
                :label="__('Title')"
                required
                autofocus
            />

            <flux:textarea
                wire:model="description"
                :label="__('Description')"
                rows="4"
                required
            />

            <flux:input
                wire:model="location"
                :label="__('Location')"
                required
            />

            <div class="grid grid-cols-2 gap-4">
                <flux:select wire:model="categoryId" :label="__('Category')" required>
                    <flux:select.option value="">{{ __('Select category') }}</flux:select.option>
                    @foreach ($this->categories as $category)
                        <flux:select.option value="{{ $category->id }}">{{ $category->name }}</flux:select.option>
                    @endforeach
                </flux:select>

                <flux:select wire:model="officerId" :label="__('Assigned Officer')">
                    <flux:select.option value="">{{ __('Unassigned') }}</flux:select.option>
                    @foreach ($this->officers as $officer)
                        <flux:select.option value="{{ $officer->id }}">{{ $officer->name }}</flux:select.option>
                    @endforeach
                </flux:select>
            </div>

            <div class="grid grid-cols-2 gap-4">
                <flux:select wire:model="status" :label="__('Status')" required>
                    @foreach ($this->statuses as $statusOption)
                        <flux:select.option value="{{ $statusOption->value }}">{{ $statusOption->label() }}</flux:select.option>
                    @endforeach
                </flux:select>

                <flux:select wire:model="priority" :label="__('Priority')" required>
                    @foreach ($this->priorities as $priorityOption)
                        <flux:select.option value="{{ $priorityOption->value }}">{{ $priorityOption->label() }}</flux:select.option>
                    @endforeach
                </flux:select>
            </div>

            <div class="flex gap-2">
                <flux:spacer />
                <flux:modal.close>
                    <flux:button variant="ghost">{{ __('Cancel') }}</flux:button>
                </flux:modal.close>
                <flux:button variant="primary" wire:click="save">
                    {{ $editingId ? __('Update') : __('Create') }}
                </flux:button>
            </div>
        </div>
    </flux:modal>
</div>
