<div>
    <flux:main>
        <div class="mb-6 flex items-center justify-between">
            <div>
                <flux:heading size="xl">{{ __('Admin Dashboard') }}</flux:heading>
                <flux:subheading>{{ __('System-wide overview.') }}</flux:subheading>
            </div>
            <div class="flex gap-2">
                <flux:button :href="route('admin.users.index')" wire:navigate variant="outline" icon="users" size="sm">
                    {{ __('Manage Users') }}
                </flux:button>
                <flux:button :href="route('admin.reports')" wire:navigate variant="outline" icon="chart-bar" size="sm">
                    {{ __('Reports') }}
                </flux:button>
            </div>
        </div>

        {{-- Stat Cards --}}
        <div class="mb-6 grid grid-cols-2 gap-4 sm:grid-cols-3 lg:grid-cols-6">
            @php
                $cards = [
                    ['label' => __('Total'), 'value' => $this->stats['total'], 'color' => 'text-zinc-800 dark:text-zinc-100'],
                    ['label' => __('Pending'), 'value' => $this->stats['pending'], 'color' => 'text-yellow-600'],
                    ['label' => __('In Review'), 'value' => $this->stats['in_review'], 'color' => 'text-blue-600'],
                    ['label' => __('In Progress'), 'value' => $this->stats['in_progress'], 'color' => 'text-cyan-600'],
                    ['label' => __('Resolved'), 'value' => $this->stats['resolved'], 'color' => 'text-green-600'],
                    ['label' => __('Unassigned'), 'value' => $this->stats['unassigned'], 'color' => 'text-red-600'],
                ];
            @endphp
            @foreach ($cards as $card)
                <div class="rounded-xl border border-zinc-200 bg-white p-4 dark:border-zinc-700 dark:bg-zinc-900">
                    <p class="text-xs text-zinc-500">{{ $card['label'] }}</p>
                    <p class="mt-1 text-2xl font-bold {{ $card['color'] }}">{{ $card['value'] }}</p>
                </div>
            @endforeach
        </div>

        <div class="grid gap-6 lg:grid-cols-5">
            {{-- Officer Workload --}}
            <div class="rounded-xl border border-zinc-200 bg-white dark:border-zinc-700 dark:bg-zinc-900 lg:col-span-2">
                <div class="border-b border-zinc-100 px-5 py-3 dark:border-zinc-700">
                    <flux:heading size="sm">{{ __('Officer Workload') }}</flux:heading>
                </div>
                @forelse ($this->officerWorkload as $officer)
                    <div class="flex items-center justify-between border-b border-zinc-50 px-5 py-3 last:border-0 dark:border-zinc-800">
                        <div>
                            <p class="text-sm font-medium">{{ $officer->name }}</p>
                            <p class="text-xs text-zinc-400">{{ $officer->assigned_complaints_count }} {{ __('total') }}</p>
                        </div>
                        <div class="text-right">
                            <p class="text-lg font-bold text-blue-600">{{ $officer->open_count }}</p>
                            <p class="text-xs text-zinc-400">{{ __('open') }}</p>
                        </div>
                    </div>
                @empty
                    <p class="px-5 py-8 text-center text-sm text-zinc-400">{{ __('No officers yet.') }}</p>
                @endforelse
            </div>

            {{-- Recent Complaints --}}
            <div class="rounded-xl border border-zinc-200 bg-white dark:border-zinc-700 dark:bg-zinc-900 lg:col-span-3">
                <div class="flex items-center justify-between border-b border-zinc-100 px-5 py-3 dark:border-zinc-700">
                    <flux:heading size="sm">{{ __('Recent Complaints') }}</flux:heading>
                    <flux:link :href="route('admin.aduan.index')" wire:navigate class="text-sm">
                        {{ __('View all') }}
                    </flux:link>
                </div>
                <flux:table>
                    <flux:table.columns>
                        <flux:table.column>{{ __('Reference') }}</flux:table.column>
                        <flux:table.column>{{ __('Title') }}</flux:table.column>
                        <flux:table.column>{{ __('Status') }}</flux:table.column>
                        <flux:table.column>{{ __('Officer') }}</flux:table.column>
                        <flux:table.column></flux:table.column>
                    </flux:table.columns>
                    <flux:table.rows>
                        @forelse ($this->recentComplaints as $complaint)
                            <flux:table.row :key="$complaint->id" wire:key="admin-recent-{{ $complaint->id }}">
                                <flux:table.cell class="font-mono text-sm text-zinc-500">
                                    {{ $complaint->aduan_no }}
                                </flux:table.cell>
                                <flux:table.cell variant="strong">{{ $complaint->title }}</flux:table.cell>
                                <flux:table.cell>
                                    <flux:badge variant="solid" color="{{ $complaint->status->color() }}" size="sm">
                                        {{ $complaint->status->label() }}
                                    </flux:badge>
                                </flux:table.cell>
                                <flux:table.cell class="text-sm text-zinc-400">
                                    {{ $complaint->officer?->name ?? '—' }}
                                </flux:table.cell>
                                <flux:table.cell>
                                    <flux:button :href="route('aduan.show', $complaint)" wire:navigate variant="ghost" size="sm" icon="eye" />
                                </flux:table.cell>
                            </flux:table.row>
                        @empty
                            <flux:table.row>
                                <flux:table.cell colspan="5" class="py-10 text-center text-zinc-400">
                                    {{ __('No complaints yet.') }}
                                </flux:table.cell>
                            </flux:table.row>
                        @endforelse
                    </flux:table.rows>
                </flux:table>
            </div>
        </div>
    </flux:main>
</div>
