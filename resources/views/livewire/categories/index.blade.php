<div>
    <flux:main>
        <div class="flex items-center justify-between mb-6">
            <flux:heading size="xl">{{ __('Categories') }}</flux:heading>
            <flux:button variant="primary" icon="plus" wire:click="openCreateModal">
                {{ __('New Category') }}
            </flux:button>
        </div>

        <div class="mb-4">
            <flux:input
                wire:model.live.debounce.300ms="search"
                icon="magnifying-glass"
                placeholder="{{ __('Search categories...') }}"
                clearable
            />
        </div>

        <flux:table :paginate="$categories">
            <flux:table.columns>
                <flux:table.column>{{ __('Name') }}</flux:table.column>
                <flux:table.column>{{ __('Description') }}</flux:table.column>
                <flux:table.column>{{ __('Status') }}</flux:table.column>
                <flux:table.column>{{ __('Created') }}</flux:table.column>
                <flux:table.column />
            </flux:table.columns>

            <flux:table.rows>
                @forelse ($categories as $category)
                    <flux:table.row :key="$category->id">
                        <flux:table.cell variant="strong">{{ $category->name }}</flux:table.cell>
                        <flux:table.cell class="text-zinc-500 dark:text-zinc-400">
                            {{ $category->description ?: '—' }}
                        </flux:table.cell>
                        <flux:table.cell>
                            @if ($category->is_active)
                                <flux:badge variant="solid" color="green" size="sm">{{ __('Active') }}</flux:badge>
                            @else
                                <flux:badge variant="solid" color="zinc" size="sm">{{ __('Inactive') }}</flux:badge>
                            @endif
                        </flux:table.cell>
                        <flux:table.cell class="text-zinc-500 dark:text-zinc-400">
                            {{ $category->created_at->diffForHumans() }}
                        </flux:table.cell>
                        <flux:table.cell>
                            <div class="flex items-center gap-2 justify-end">
                                <flux:button
                                    size="sm"
                                    icon="pencil"
                                    wire:click="openEditModal({{ $category->id }})"
                                >
                                    {{ __('Edit') }}
                                </flux:button>

                                <flux:modal.trigger name="delete-{{ $category->id }}">
                                    <flux:button size="sm" icon="trash" variant="danger">
                                        {{ __('Delete') }}
                                    </flux:button>
                                </flux:modal.trigger>

                                <flux:modal name="delete-{{ $category->id }}" class="min-w-[22rem]">
                                    <div class="space-y-6">
                                        <div>
                                            <flux:heading size="lg">{{ __('Delete category?') }}</flux:heading>
                                            <flux:text class="mt-2">
                                                {{ __('Are you sure you want to delete ":name"? This action cannot be undone.', ['name' => $category->name]) }}
                                            </flux:text>
                                        </div>
                                        <div class="flex gap-2">
                                            <flux:spacer />
                                            <flux:modal.close>
                                                <flux:button variant="ghost">{{ __('Cancel') }}</flux:button>
                                            </flux:modal.close>
                                            <flux:button
                                                variant="danger"
                                                wire:click="delete({{ $category->id }})"
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
                        <flux:table.cell colspan="5" class="text-center text-zinc-500 dark:text-zinc-400 py-8">
                            {{ __('No categories found.') }}
                        </flux:table.cell>
                    </flux:table.row>
                @endforelse
            </flux:table.rows>
        </flux:table>
    </flux:main>

    <flux:modal name="category-form" class="min-w-[28rem]">
        <div class="space-y-6">
            <flux:heading size="lg">
                {{ $editingId ? __('Edit Category') : __('New Category') }}
            </flux:heading>

            <flux:input
                wire:model="name"
                :label="__('Name')"
                required
                autofocus
            />

            <flux:textarea
                wire:model="description"
                :label="__('Description')"
                rows="3"
            />

            <flux:checkbox
                wire:model="isActive"
                :label="__('Active')"
            />

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
