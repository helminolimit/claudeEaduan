<div>
    <flux:main>
        <div class="mb-6">
            <flux:heading size="xl">{{ __('Officer Dashboard') }}</flux:heading>
            <flux:subheading>{{ __('Your assigned complaints overview.') }}</flux:subheading>
        </div>

        {{-- Stat Cards --}}
        <div class="mb-6 grid grid-cols-2 gap-4 lg:grid-cols-4">
            @php
                $cards = [
                    ['label' => __('Total Assigned'), 'value' => $this->stats['total'], 'color' => 'text-zinc-800 dark:text-zinc-100'],
                    ['label' => __('Pending / Review'), 'value' => $this->stats['pending'], 'color' => 'text-yellow-600'],
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

        <div class="grid gap-6 lg:grid-cols-3">
            {{-- Priority Breakdown --}}
            <div class="rounded-xl border border-zinc-200 bg-white dark:border-zinc-700 dark:bg-zinc-900">
                <div class="border-b border-zinc-100 px-5 py-3 dark:border-zinc-700">
                    <flux:heading size="sm">{{ __('Open by Priority') }}</flux:heading>
                </div>
                <div class="divide-y divide-zinc-100 dark:divide-zinc-700">
                    @foreach ($this->priorityBreakdown as $row)
                        <div class="flex items-center justify-between px-5 py-3">
                            <flux:badge variant="outline" color="{{ $row['priority']->color() }}" size="sm">
                                {{ $row['priority']->label() }}
                            </flux:badge>
                            <span class="text-sm font-semibold">{{ $row['count'] }}</span>
                        </div>
                    @endforeach
                </div>
            </div>

            {{-- Recent Assigned Complaints --}}
            <div class="rounded-xl border border-zinc-200 bg-white dark:border-zinc-700 dark:bg-zinc-900 lg:col-span-2">
                <div class="flex items-center justify-between border-b border-zinc-100 px-5 py-3 dark:border-zinc-700">
                    <flux:heading size="sm">{{ __('Recent Assigned') }}</flux:heading>
                    <flux:link :href="route('officer.aduan.index')" wire:navigate class="text-sm">
                        {{ __('View all') }}
                    </flux:link>
                </div>
                <flux:table>
                    <flux:table.columns>
                        <flux:table.column>{{ __('Reference') }}</flux:table.column>
                        <flux:table.column>{{ __('Title') }}</flux:table.column>
                        <flux:table.column>{{ __('Priority') }}</flux:table.column>
                        <flux:table.column>{{ __('Status') }}</flux:table.column>
                        <flux:table.column></flux:table.column>
                    </flux:table.columns>
                    <flux:table.rows>
                        @forelse ($this->recentComplaints as $complaint)
                            <flux:table.row :key="$complaint->id" wire:key="officer-recent-{{ $complaint->id }}">
                                <flux:table.cell class="font-mono text-sm text-zinc-500">
                                    {{ $complaint->aduan_no }}
                                </flux:table.cell>
                                <flux:table.cell variant="strong">{{ $complaint->title }}</flux:table.cell>
                                <flux:table.cell>
                                    <flux:badge variant="outline" color="{{ $complaint->priority->color() }}" size="sm">
                                        {{ $complaint->priority->label() }}
                                    </flux:badge>
                                </flux:table.cell>
                                <flux:table.cell>
                                    <flux:badge variant="solid" color="{{ $complaint->status->color() }}" size="sm">
                                        {{ $complaint->status->label() }}
                                    </flux:badge>
                                </flux:table.cell>
                                <flux:table.cell>
                                    <flux:button :href="route('aduan.show', $complaint)" wire:navigate variant="ghost" size="sm" icon="eye" />
                                </flux:table.cell>
                            </flux:table.row>
                        @empty
                            <flux:table.row>
                                <flux:table.cell colspan="5" class="py-10 text-center text-zinc-400">
                                    {{ __('No complaints assigned yet.') }}
                                </flux:table.cell>
                            </flux:table.row>
                        @endforelse
                    </flux:table.rows>
                </flux:table>
            </div>
        </div>
    </flux:main>
</div>
