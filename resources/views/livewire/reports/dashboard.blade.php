<div>
    {{-- Chart.js CDN (loads once; wire:ignore on canvases handles re-renders) --}}
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4/dist/chart.umd.min.js" defer></script>

    <flux:main>
        <div class="mb-6 flex items-center justify-between">
            <flux:heading size="xl">{{ __('Reports') }}</flux:heading>
            <flux:button variant="ghost" size="sm" wire:click="resetFilters" icon="arrow-path">
                {{ __('Reset Filters') }}
            </flux:button>
        </div>

        {{-- ── Filter Panel ── --}}
        <div class="mb-6 rounded-xl border border-zinc-200 bg-white p-4 dark:border-zinc-700 dark:bg-zinc-900">
            <div class="grid grid-cols-2 gap-3 sm:grid-cols-3 lg:grid-cols-6">
                <flux:input
                    wire:model.live="dateFrom"
                    type="date"
                    :label="__('From')"
                />
                <flux:input
                    wire:model.live="dateTo"
                    type="date"
                    :label="__('To')"
                />
                <flux:select wire:model.live="filterStatus" :label="__('Status')" placeholder="{{ __('All') }}">
                    @foreach ($this->statuses as $status)
                        <flux:select.option value="{{ $status->value }}">{{ $status->label() }}</flux:select.option>
                    @endforeach
                </flux:select>
                <flux:select wire:model.live="filterCategory" :label="__('Category')" placeholder="{{ __('All') }}">
                    @foreach ($this->categories as $category)
                        <flux:select.option value="{{ $category->id }}">{{ $category->name }}</flux:select.option>
                    @endforeach
                </flux:select>
                <flux:select wire:model.live="filterPriority" :label="__('Priority')" placeholder="{{ __('All') }}">
                    @foreach ($this->priorities as $priority)
                        <flux:select.option value="{{ $priority->value }}">{{ $priority->label() }}</flux:select.option>
                    @endforeach
                </flux:select>
                <flux:select wire:model.live="filterOfficer" :label="__('Officer')" placeholder="{{ __('All') }}">
                    @foreach ($this->officers as $officer)
                        <flux:select.option value="{{ $officer->id }}">{{ $officer->name }}</flux:select.option>
                    @endforeach
                </flux:select>
            </div>
        </div>

        {{-- ── Stat Cards ── --}}
        <div class="mb-6 grid grid-cols-2 gap-4 sm:grid-cols-4 lg:grid-cols-7">
            @php
                $cards = [
                    ['label' => __('Total'), 'value' => $this->summaryStats['total'], 'color' => 'text-zinc-800 dark:text-zinc-100'],
                    ['label' => __('Pending'), 'value' => $this->summaryStats['pending'], 'color' => 'text-yellow-600'],
                    ['label' => __('In Review'), 'value' => $this->summaryStats['in_review'], 'color' => 'text-blue-600'],
                    ['label' => __('In Progress'), 'value' => $this->summaryStats['in_progress'], 'color' => 'text-cyan-600'],
                    ['label' => __('Resolved'), 'value' => $this->summaryStats['resolved'], 'color' => 'text-green-600'],
                    ['label' => __('Closed'), 'value' => $this->summaryStats['closed'], 'color' => 'text-zinc-500'],
                    ['label' => __('Rejected'), 'value' => $this->summaryStats['rejected'], 'color' => 'text-red-600'],
                ];
            @endphp
            @foreach ($cards as $card)
                <div class="rounded-xl border border-zinc-200 bg-white p-4 dark:border-zinc-700 dark:bg-zinc-900">
                    <p class="text-xs text-zinc-500">{{ $card['label'] }}</p>
                    <p class="mt-1 text-2xl font-bold {{ $card['color'] }}">{{ $card['value'] }}</p>
                </div>
            @endforeach
        </div>

        {{-- ── Tabs ── --}}
        <div class="mb-4 flex gap-1 rounded-lg border border-zinc-200 bg-zinc-50 p-1 dark:border-zinc-700 dark:bg-zinc-800/50">
            @foreach ([
                'summary' => __('Summary'),
                'trend' => __('Trend'),
                'resolution' => __('Resolution'),
                'officers' => __('Officers'),
                'export' => __('Export'),
            ] as $tab => $label)
                <button
                    wire:click="$set('activeTab', '{{ $tab }}')"
                    @class([
                        'flex-1 rounded-md px-3 py-1.5 text-sm font-medium transition',
                        'bg-white shadow text-zinc-800 dark:bg-zinc-900 dark:text-zinc-100' => $activeTab === $tab,
                        'text-zinc-500 hover:text-zinc-700 dark:text-zinc-400 dark:hover:text-zinc-200' => $activeTab !== $tab,
                    ])
                >{{ $label }}</button>
            @endforeach
        </div>

        {{-- ── Tab: Summary ── --}}
        @if ($activeTab === 'summary')
            <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
                {{-- Status doughnut --}}
                <div class="rounded-xl border border-zinc-200 bg-white p-5 dark:border-zinc-700 dark:bg-zinc-900"
                    wire:key="status-chart-{{ $this->chartKey }}"
                    x-data="{
                        init() {
                            const data = @js($this->statusChartData);
                            new Chart(this.$refs.canvas, {
                                type: 'doughnut',
                                data: data,
                                options: {
                                    plugins: { legend: { position: 'bottom', labels: { boxWidth: 12, padding: 12 } } },
                                    cutout: '65%',
                                    maintainAspectRatio: true,
                                }
                            });
                        }
                    }"
                >
                    <flux:heading size="sm" class="mb-4">{{ __('Complaints by Status') }}</flux:heading>
                    <canvas x-ref="canvas" height="260"></canvas>
                </div>

                {{-- Category bar --}}
                <div class="rounded-xl border border-zinc-200 bg-white p-5 dark:border-zinc-700 dark:bg-zinc-900"
                    wire:key="category-chart-{{ $this->chartKey }}"
                    x-data="{
                        init() {
                            const data = @js($this->categoryChartData);
                            new Chart(this.$refs.canvas, {
                                type: 'bar',
                                data: data,
                                options: {
                                    indexAxis: 'y',
                                    plugins: { legend: { display: false } },
                                    scales: { x: { beginAtZero: true, ticks: { stepSize: 1 } } },
                                    maintainAspectRatio: true,
                                }
                            });
                        }
                    }"
                >
                    <flux:heading size="sm" class="mb-4">{{ __('Complaints by Category') }}</flux:heading>
                    <canvas x-ref="canvas" height="260"></canvas>
                </div>
            </div>
        @endif

        {{-- ── Tab: Trend ── --}}
        @if ($activeTab === 'trend')
            <div class="rounded-xl border border-zinc-200 bg-white p-5 dark:border-zinc-700 dark:bg-zinc-900"
                wire:key="trend-chart-{{ $this->chartKey }}"
                x-data="{
                    init() {
                        const data = @js($this->trendChartData);
                        new Chart(this.$refs.canvas, {
                            type: 'line',
                            data: data,
                            options: {
                                plugins: { legend: { display: false } },
                                scales: { y: { beginAtZero: true, ticks: { stepSize: 1 } } },
                                maintainAspectRatio: false,
                            }
                        });
                    }
                }"
            >
                <flux:heading size="sm" class="mb-4">{{ __('Submission Trend (Monthly)') }}</flux:heading>
                <div class="h-72">
                    <canvas x-ref="canvas" style="height:100%;width:100%;"></canvas>
                </div>
            </div>
        @endif

        {{-- ── Tab: Resolution ── --}}
        @if ($activeTab === 'resolution')
            <div class="rounded-xl border border-zinc-200 bg-white dark:border-zinc-700 dark:bg-zinc-900">
                <div class="border-b border-zinc-100 p-5 dark:border-zinc-800">
                    <flux:heading size="sm">{{ __('Average Resolution Time by Category') }}</flux:heading>
                    <flux:subheading>{{ __('Only resolved and closed complaints are counted.') }}</flux:subheading>
                </div>
                @if ($this->resolutionData->isEmpty())
                    <p class="py-12 text-center text-sm text-zinc-500">{{ __('No resolved complaints in the selected period.') }}</p>
                @else
                    <flux:table>
                        <flux:table.columns>
                            <flux:table.column class="w-12">{{ __('Bil') }}</flux:table.column>
                            <flux:table.column>{{ __('Category') }}</flux:table.column>
                            <flux:table.column>{{ __('Resolved / Closed') }}</flux:table.column>
                            <flux:table.column>{{ __('Avg. Days to Resolve') }}</flux:table.column>
                        </flux:table.columns>
                        <flux:table.rows>
                            @foreach ($this->resolutionData as $i => $row)
                                <flux:table.row :key="$i">
                                    <flux:table.cell class="text-sm tabular-nums text-zinc-400">{{ $i + 1 }}</flux:table.cell>
                                    <flux:table.cell variant="strong">{{ $row->category_name }}</flux:table.cell>
                                    <flux:table.cell>{{ $row->count }}</flux:table.cell>
                                    <flux:table.cell>
                                        <span @class([
                                            'font-semibold',
                                            'text-green-600' => $row->avg_days <= 7,
                                            'text-yellow-600' => $row->avg_days > 7 && $row->avg_days <= 14,
                                            'text-red-600' => $row->avg_days > 14,
                                        ])>{{ $row->avg_days ?? '—' }} {{ $row->avg_days ? __('days') : '' }}</span>
                                    </flux:table.cell>
                                </flux:table.row>
                            @endforeach
                        </flux:table.rows>
                    </flux:table>
                @endif
            </div>
        @endif

        {{-- ── Tab: Officers ── --}}
        @if ($activeTab === 'officers')
            <div class="rounded-xl border border-zinc-200 bg-white dark:border-zinc-700 dark:bg-zinc-900">
                <div class="border-b border-zinc-100 p-5 dark:border-zinc-800">
                    <flux:heading size="sm">{{ __('Officer Performance') }}</flux:heading>
                </div>
                @if ($this->officerData->isEmpty())
                    <p class="py-12 text-center text-sm text-zinc-500">{{ __('No assigned complaints in the selected period.') }}</p>
                @else
                    <flux:table>
                        <flux:table.columns>
                            <flux:table.column class="w-12">{{ __('Bil') }}</flux:table.column>
                            <flux:table.column>{{ __('Officer') }}</flux:table.column>
                            <flux:table.column>{{ __('Total Assigned') }}</flux:table.column>
                            <flux:table.column>{{ __('Resolved / Closed') }}</flux:table.column>
                            <flux:table.column>{{ __('Resolution Rate') }}</flux:table.column>
                            <flux:table.column>{{ __('Avg. Days') }}</flux:table.column>
                        </flux:table.columns>
                        <flux:table.rows>
                            @foreach ($this->officerData as $i => $row)
                                <flux:table.row :key="$i">
                                    <flux:table.cell class="text-sm tabular-nums text-zinc-400">{{ $i + 1 }}</flux:table.cell>
                                    <flux:table.cell variant="strong">{{ $row->officer_name }}</flux:table.cell>
                                    <flux:table.cell>{{ $row->total }}</flux:table.cell>
                                    <flux:table.cell>{{ $row->resolved }}</flux:table.cell>
                                    <flux:table.cell>
                                        @php $rate = $row->total > 0 ? round(($row->resolved / $row->total) * 100) : 0; @endphp
                                        <div class="flex items-center gap-2">
                                            <div class="h-1.5 w-20 overflow-hidden rounded-full bg-zinc-200">
                                                <div class="h-full rounded-full bg-green-500" style="width:{{ $rate }}%"></div>
                                            </div>
                                            <span class="text-sm">{{ $rate }}%</span>
                                        </div>
                                    </flux:table.cell>
                                    <flux:table.cell class="text-zinc-500">
                                        {{ $row->avg_days ? $row->avg_days.' '.__('days') : '—' }}
                                    </flux:table.cell>
                                </flux:table.row>
                            @endforeach
                        </flux:table.rows>
                    </flux:table>
                @endif
            </div>
        @endif

        {{-- ── Tab: Export ── --}}
        @if ($activeTab === 'export')
            <div class="rounded-xl border border-zinc-200 bg-white p-6 dark:border-zinc-700 dark:bg-zinc-900">
                <flux:heading size="sm" class="mb-2">{{ __('Export Complaints Data') }}</flux:heading>
                <flux:subheading class="mb-6">
                    {{ __('Exports apply all active filters. For large datasets, use Excel — PDF is limited to 500 rows.') }}
                </flux:subheading>

                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                    <div class="rounded-lg border border-zinc-200 p-5 dark:border-zinc-700">
                        <div class="mb-3 flex items-center gap-3">
                            <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-green-100 dark:bg-green-900/30">
                                <flux:icon name="table-cells" class="size-5 text-green-600" />
                            </div>
                            <div>
                                <p class="font-medium">Excel (.xlsx)</p>
                                <p class="text-xs text-zinc-500">{{ __('All rows, all columns, auto-sized') }}</p>
                            </div>
                        </div>
                        <flux:button variant="outline" wire:click="exportExcel" icon="arrow-down-tray" class="w-full">
                            <span wire:loading.remove wire:target="exportExcel">{{ __('Download Excel') }}</span>
                            <span wire:loading wire:target="exportExcel">{{ __('Preparing...') }}</span>
                        </flux:button>
                    </div>

                    <div class="rounded-lg border border-zinc-200 p-5 dark:border-zinc-700">
                        <div class="mb-3 flex items-center gap-3">
                            <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-red-100 dark:bg-red-900/30">
                                <flux:icon name="document-text" class="size-5 text-red-600" />
                            </div>
                            <div>
                                <p class="font-medium">PDF</p>
                                <p class="text-xs text-zinc-500">{{ __('Summary + max 500 rows, A4 landscape') }}</p>
                            </div>
                        </div>
                        <flux:button variant="outline" wire:click="exportPdf" icon="arrow-down-tray" class="w-full">
                            <span wire:loading.remove wire:target="exportPdf">{{ __('Download PDF') }}</span>
                            <span wire:loading wire:target="exportPdf">{{ __('Generating...') }}</span>
                        </flux:button>
                    </div>
                </div>

                {{-- Active filter summary --}}
                @php
                    $activeFilters = array_filter([
                        $dateFrom ? "From: {$dateFrom}" : null,
                        $dateTo ? "To: {$dateTo}" : null,
                        $filterStatus ? "Status: {$filterStatus}" : null,
                        $filterPriority ? "Priority: {$filterPriority}" : null,
                    ]);
                @endphp
                @if (count($activeFilters) > 0)
                    <div class="mt-4 rounded-lg bg-zinc-50 p-3 text-xs text-zinc-600 dark:bg-zinc-800 dark:text-zinc-400">
                        <span class="font-medium">{{ __('Active filters:') }}</span>
                        {{ implode(' · ', $activeFilters) }}
                    </div>
                @endif
            </div>
        @endif

    </flux:main>
</div>
