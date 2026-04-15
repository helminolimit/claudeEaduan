<div>
    <flux:main>
        <div class="mb-6 flex items-center justify-between">
            <div>
                <flux:heading size="xl">{{ __('Dashboard') }}</flux:heading>
                <flux:subheading>{{ __('Welcome back, :name', ['name' => auth()->user()->name]) }}</flux:subheading>
            </div>
            <flux:button :href="route('aduan.create')" wire:navigate variant="primary" icon="plus">
                {{ __('Submit Complaint') }}
            </flux:button>
        </div>

        {{-- Stat Cards --}}
        <div class="mb-6 grid grid-cols-2 gap-4 lg:grid-cols-4">
            @php
                $cards = [
                    ['label' => __('Total Submitted'), 'value' => $this->stats['total'], 'color' => 'text-zinc-800 dark:text-zinc-100'],
                    ['label' => __('Pending Review'), 'value' => $this->stats['submitted'], 'color' => 'text-yellow-600'],
                    ['label' => __('In Progress'), 'value' => $this->stats['in_progress'], 'color' => 'text-blue-600'],
                    ['label' => __('Resolved'), 'value' => $this->stats['resolved'], 'color' => 'text-green-600'],
                ];
            @endphp
            @foreach ($cards as $card)
                <div class="rounded-xl border border-zinc-200 bg-white p-4 dark:border-zinc-700 dark:bg-zinc-900">
                    <p class="text-xs text-zinc-500">{{ $card['label'] }}</p>
                    <p class="mt-1 text-2xl font-bold {{ $card['color'] }}">{{ $card['value'] }}</p>
                </div>
            @endforeach
        </div>

        {{-- Recent Complaints --}}
        <div class="rounded-xl border border-zinc-200 bg-white dark:border-zinc-700 dark:bg-zinc-900">
            <div class="flex items-center justify-between border-b border-zinc-100 px-5 py-3 dark:border-zinc-700">
                <flux:heading size="sm">{{ __('Recent Complaints') }}</flux:heading>
                <flux:link :href="route('my.aduan.index')" wire:navigate class="text-sm">
                    {{ __('View all') }}
                </flux:link>
            </div>
            <flux:table>
                <flux:table.columns>
                    <flux:table.column>{{ __('Reference') }}</flux:table.column>
                    <flux:table.column>{{ __('Title') }}</flux:table.column>
                    <flux:table.column>{{ __('Category') }}</flux:table.column>
                    <flux:table.column>{{ __('Status') }}</flux:table.column>
                    <flux:table.column>{{ __('Date') }}</flux:table.column>
                    <flux:table.column></flux:table.column>
                </flux:table.columns>
                <flux:table.rows>
                    @forelse ($this->recentComplaints as $complaint)
                        <flux:table.row :key="$complaint->id" wire:key="recent-{{ $complaint->id }}">
                            <flux:table.cell class="font-mono text-sm text-zinc-500">
                                {{ $complaint->aduan_no }}
                            </flux:table.cell>
                            <flux:table.cell variant="strong">{{ $complaint->title }}</flux:table.cell>
                            <flux:table.cell class="text-zinc-500">{{ $complaint->category?->name ?? '—' }}</flux:table.cell>
                            <flux:table.cell>
                                <flux:badge variant="solid" color="{{ $complaint->status->color() }}" size="sm">
                                    {{ $complaint->status->label() }}
                                </flux:badge>
                            </flux:table.cell>
                            <flux:table.cell class="text-sm text-zinc-400">
                                {{ $complaint->created_at->diffForHumans() }}
                            </flux:table.cell>
                            <flux:table.cell>
                                <flux:button :href="route('aduan.show', $complaint)" wire:navigate variant="ghost" size="sm" icon="eye" />
                            </flux:table.cell>
                        </flux:table.row>
                    @empty
                        <flux:table.row>
                            <flux:table.cell colspan="6" class="py-10 text-center text-zinc-400">
                                {{ __('No complaints yet.') }}
                                <flux:link :href="route('aduan.create')" wire:navigate class="ml-1">
                                    {{ __('Submit your first complaint.') }}
                                </flux:link>
                            </flux:table.cell>
                        </flux:table.row>
                    @endforelse
                </flux:table.rows>
            </flux:table>
        </div>
    </flux:main>
</div>
