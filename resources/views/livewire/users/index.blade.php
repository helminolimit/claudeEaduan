<div>
    <flux:main>
        <div class="mb-6 flex items-center justify-between">
            <flux:heading size="xl">{{ __('User Management') }}</flux:heading>
            <flux:modal.trigger name="create-user">
                <flux:button variant="primary" icon="user-plus">{{ __('Add User') }}</flux:button>
            </flux:modal.trigger>
        </div>

        {{-- Filters --}}
        <div class="mb-4 flex flex-wrap gap-3">
            <div class="min-w-48 flex-1">
                <flux:input
                    wire:model.live.debounce.300ms="search"
                    icon="magnifying-glass"
                    placeholder="{{ __('Search by name or email...') }}"
                    clearable
                />
            </div>
            <flux:select wire:model.live="filterRole" placeholder="{{ __('All roles') }}" class="w-44">
                @foreach ($this->roles as $role)
                    <flux:select.option value="{{ $role->value }}">{{ ucfirst($role->value) }}</flux:select.option>
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

        <flux:table :paginate="$this->users">
            <flux:table.columns>
                <flux:table.column class="w-12">{{ __('Bil') }}</flux:table.column>
                <flux:table.column
                    sortable
                    :sorted="$sortBy === 'name'"
                    :direction="$sortDirection"
                    wire:click="sort('name')"
                >{{ __('Name') }}</flux:table.column>
                <flux:table.column
                    sortable
                    :sorted="$sortBy === 'email'"
                    :direction="$sortDirection"
                    wire:click="sort('email')"
                >{{ __('Email') }}</flux:table.column>
                <flux:table.column>{{ __('Role') }}</flux:table.column>
                <flux:table.column
                    sortable
                    :sorted="$sortBy === 'created_at'"
                    :direction="$sortDirection"
                    wire:click="sort('created_at')"
                >{{ __('Registered') }}</flux:table.column>
                <flux:table.column />
            </flux:table.columns>

            <flux:table.rows>
                @forelse ($this->users as $loop_index => $user)
                    <flux:table.row :key="$user->id" wire:key="user-{{ $user->id }}">
                        <flux:table.cell class="text-sm tabular-nums text-zinc-400">
                            {{ $this->users->firstItem() + $loop_index }}
                        </flux:table.cell>
                        <flux:table.cell variant="strong">{{ $user->name }}</flux:table.cell>
                        <flux:table.cell class="text-zinc-500">{{ $user->email }}</flux:table.cell>
                        <flux:table.cell>
                            @php
                                $roleColor = match($user->role->value) {
                                    'admin' => 'red',
                                    'officer' => 'blue',
                                    default => 'zinc',
                                };
                            @endphp
                            <flux:badge color="{{ $roleColor }}" size="sm">{{ ucfirst($user->role->value) }}</flux:badge>
                        </flux:table.cell>
                        <flux:table.cell class="text-sm text-zinc-400">
                            {{ $user->created_at->format('d/m/Y') }}
                        </flux:table.cell>
                        <flux:table.cell>
                            <flux:dropdown>
                                <flux:button variant="ghost" size="sm" icon="ellipsis-horizontal" inset="top bottom" />
                                <flux:menu>
                                    <flux:menu.item icon="pencil" wire:click="openEditModal({{ $user->id }})">
                                        {{ __('Edit') }}
                                    </flux:menu.item>
                                    @if ($user->id !== auth()->id())
                                        <flux:menu.separator />
                                        <flux:modal.trigger name="delete-{{ $user->id }}">
                                            <flux:menu.item variant="danger" icon="trash">{{ __('Delete') }}</flux:menu.item>
                                        </flux:modal.trigger>
                                    @endif
                                </flux:menu>
                            </flux:dropdown>

                            {{-- Delete confirmation --}}
                            <flux:modal
                                name="delete-{{ $user->id }}"
                                class="min-w-[22rem]"
                                :closable="false"
                                x-data="{ loading: false }"
                                x-on:cancel="loading && $event.preventDefault()"
                                x-on:livewire:commit.window="loading = false"
                            >
                                <div class="relative space-y-6">
                                    <div wire:loading wire:target="delete({{ $user->id }})" class="absolute inset-0 z-10 flex items-center justify-center rounded-lg bg-white/80 dark:bg-zinc-900/80">
                                        <flux:icon name="arrow-path" class="size-6 animate-spin text-zinc-500" />
                                    </div>
                                    <div>
                                        <flux:heading size="lg">{{ __('Delete user?') }}</flux:heading>
                                        <flux:subheading>{{ __('This will permanently remove :name.', ['name' => $user->name]) }}</flux:subheading>
                                    </div>
                                    <div class="flex gap-2">
                                        <flux:spacer />
                                        <flux:modal.close>
                                            <flux:button variant="ghost">{{ __('Cancel') }}</flux:button>
                                        </flux:modal.close>
                                        <flux:button variant="danger" wire:click="delete({{ $user->id }})" @click="loading = true">
                                            {{ __('Delete') }}
                                        </flux:button>
                                    </div>
                                </div>
                            </flux:modal>
                        </flux:table.cell>
                    </flux:table.row>
                @empty
                    <flux:table.row>
                        <flux:table.cell colspan="6" class="py-12 text-center text-zinc-500">
                            {{ __('No users found.') }}
                        </flux:table.cell>
                    </flux:table.row>
                @endforelse
            </flux:table.rows>
        </flux:table>
    </flux:main>

    {{-- Create User Modal --}}
    <flux:modal
        name="create-user"
        class="min-w-[32rem]"
        :closable="false"
        x-data="{ loading: false }"
        x-on:cancel="loading && $event.preventDefault()"
        x-on:livewire:commit.window="loading = false"
    >
        <div class="relative space-y-6">
            <div wire:loading wire:target="createUser" class="absolute inset-0 z-10 flex items-center justify-center rounded-lg bg-white/80 dark:bg-zinc-900/80">
                <flux:icon name="arrow-path" class="size-6 animate-spin text-zinc-500" />
            </div>
            <div>
                <flux:heading size="lg">{{ __('Add User') }}</flux:heading>
                <flux:subheading>{{ __('Create a new officer or admin account.') }}</flux:subheading>
            </div>
            <div class="space-y-4">
                <flux:input wire:model="createName" :label="__('Name')" required />
                <flux:input wire:model="createEmail" :label="__('Email')" type="email" required />
                <flux:select wire:model="createRole" :label="__('Role')" required>
                    <flux:select.option value="">{{ __('Select role') }}</flux:select.option>
                    @foreach ($this->roles as $role)
                        <flux:select.option value="{{ $role->value }}">{{ ucfirst($role->value) }}</flux:select.option>
                    @endforeach
                </flux:select>
                <flux:input wire:model="createPassword" :label="__('Password')" type="password" required />
            </div>
            <div class="flex gap-2">
                <flux:spacer />
                <flux:modal.close>
                    <flux:button variant="ghost">{{ __('Cancel') }}</flux:button>
                </flux:modal.close>
                <flux:button variant="primary" wire:click="createUser" @click="loading = true">
                    {{ __('Create User') }}
                </flux:button>
            </div>
        </div>
    </flux:modal>

    {{-- Edit User Modal --}}
    <flux:modal
        name="edit-user"
        class="min-w-[32rem]"
        :closable="false"
        x-data="{ loading: false }"
        x-on:cancel="loading && $event.preventDefault()"
        x-on:livewire:commit.window="loading = false"
    >
        <div class="relative space-y-6">
            <div wire:loading wire:target="updateUser" class="absolute inset-0 z-10 flex items-center justify-center rounded-lg bg-white/80 dark:bg-zinc-900/80">
                <flux:icon name="arrow-path" class="size-6 animate-spin text-zinc-500" />
            </div>
            <div>
                <flux:heading size="lg">{{ __('Edit User') }}</flux:heading>
            </div>
            <div class="space-y-4">
                <flux:input wire:model="editName" :label="__('Name')" required />
                <flux:input wire:model="editEmail" :label="__('Email')" type="email" required />
                <flux:select wire:model="editRole" :label="__('Role')" required>
                    @foreach ($this->roles as $role)
                        <flux:select.option value="{{ $role->value }}">{{ ucfirst($role->value) }}</flux:select.option>
                    @endforeach
                </flux:select>
            </div>
            <div class="flex gap-2">
                <flux:spacer />
                <flux:modal.close>
                    <flux:button variant="ghost">{{ __('Cancel') }}</flux:button>
                </flux:modal.close>
                <flux:button variant="primary" wire:click="updateUser" @click="loading = true">
                    {{ __('Save Changes') }}
                </flux:button>
            </div>
        </div>
    </flux:modal>
</div>
