---
paths:
  - "app/Livewire/**/*.php"
  - "resources/views/livewire/**/*.blade.php"
---

# Datatable Pattern

When building any table that displays a list of records, always implement the full datatable pattern using Livewire + Flux UI. Do not build static HTML tables.

## Storyboard — Datatable Layout

```
INDIVIDUAL FIELD SEARCH (ADVANCED FILTER — optional per module)
+------------------------------------------------------------------+
|  Field 1: [___________]   Field 2: [___________]                |
|  Field 3: [___________]   Field 4: [___________]                |
+------------------------------------------------------------------+

DOWNLOAD BUTTONS (optional — add when module requires export)
+------------------------------------------------------------------+
|  [↓ Excel]  [↓ PDF]  [↓ CSV]                                   |
+------------------------------------------------------------------+

+------------------------------------------------------------------+
|  [ 10 v ] entries per page              Cari: [______________]  |
+------------------------------------------------------------------+

DYNAMIC COLUMNS (based on current module)
+-----+----------+----------+----------+----------+--------+-----------+
| Bil | COLUMN_1 | COLUMN_2 | COLUMN_3 | COLUMN_4 | STATUS |  Action   |
+-----+----------+----------+----------+----------+--------+-----------+
|  1  | Data A   | Data B   | Data C   | Data D   | [blue] | V | E | D |
|  2  | Data A   | Data B   | Data C   | Data D   | [ylw]  | V | E | D |
|  3  | Data A   | Data B   | Data C   | Data D   | [grn]  | V | E | D |
|  4  | Data A   | Data B   | Data C   | Data D   | [red]  | V         |
+-----+----------+----------+----------+----------+--------+-----------+

  Showing 1 to 10 of 57 entries

  [ First ] [ Prev ]   [ 1 ] [ 2 ] [ 3 ] [ 4 ] [ 5 ]   [ Next ] [ Last ]
```

**Status badge colours** (use `flux:badge` with `color` from enum):
- `blue` — Baru
- `yellow` — Dalam Proses
- `green` — Selesai
- `red` — Ditolak

**Action buttons** (V=View, E=Edit, D=Delete) are role-based — only render actions the current user is authorised to perform.

---

## Required Features

Every datatable must include:

- **Global search** — a single text input that filters across all relevant columns using `LIKE` queries
- **Individual field search** — per-column filter inputs (dropdowns or inputs) when the module requires advanced filtering
- **Sorting** — clickable column headers with ascending/descending toggle
- **Pagination** — server-side pagination via `WithPagination` and `:paginate` on `flux:table`
- **Per-page selector** — allow the user to choose how many rows to display (e.g. 10, 25, 50, 100)

---

## Download Export (Excel / PDF / CSV)

Export buttons respect the **current search and filter state** — only export records matching the active filters, not the full table.

### Package Dependencies

- **Excel & CSV**: `maatwebsite/laravel-excel` — `composer require maatwebsite/excel`
- **PDF**: `barryvdh/laravel-dompdf` — `composer require barryvdh/laravel-dompdf`

### Export Class Pattern (Excel & CSV)

Create an export class via `php artisan make:export ExampleExport --model=Example`:

```php
namespace App\Exports;

use App\Models\Example;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class ExampleExport implements FromQuery, WithHeadings, WithMapping, ShouldAutoSize
{
    public function __construct(
        public readonly string $search = '',
        public readonly string $filterStatus = '',
    ) {}

    public function query()
    {
        return Example::query()
            ->when($this->search, fn ($q) => $q->where(function ($q) {
                $q->where('name', 'like', "%{$this->search}%")
                  ->orWhere('reference_no', 'like', "%{$this->search}%");
            }))
            ->when($this->filterStatus, fn ($q) => $q->where('status', $this->filterStatus))
            ->orderBy('created_at', 'desc');
    }

    public function headings(): array
    {
        return ['#', 'Nama', 'Status', 'Tarikh Dicipta'];
    }

    public function map($record): array
    {
        return [
            $record->id,
            $record->name,
            $record->status->label(),
            $record->created_at->format('d/m/Y'),
        ];
    }
}
```

### Livewire Component — Export Methods

Add these methods to the existing Livewire component (pass current filter state to the export):

```php
use App\Exports\ExampleExport;
use Maatwebsite\Excel\Facades\Excel;
use Barryvdh\DomPDF\Facade\Pdf;

public function exportExcel(): \Symfony\Component\HttpFoundation\BinaryFileResponse
{
    return Excel::download(
        new ExampleExport($this->search, $this->filterStatus),
        'example-' . now()->format('Ymd-His') . '.xlsx'
    );
}

public function exportCsv(): \Symfony\Component\HttpFoundation\BinaryFileResponse
{
    return Excel::download(
        new ExampleExport($this->search, $this->filterStatus),
        'example-' . now()->format('Ymd-His') . '.csv',
        \Maatwebsite\Excel\Excel::CSV,
    );
}

public function exportPdf(): \Symfony\Component\HttpFoundation\StreamedResponse
{
    $records = \App\Models\Example::query()
        ->when($this->search, fn ($q) => $q->where(function ($q) {
            $q->where('name', 'like', "%{$this->search}%")
              ->orWhere('reference_no', 'like', "%{$this->search}%");
        }))
        ->when($this->filterStatus, fn ($q) => $q->where('status', $this->filterStatus))
        ->orderBy('created_at', 'desc')
        ->get();

    return Pdf::loadView('exports.example-pdf', ['records' => $records])
        ->download('example-' . now()->format('Ymd-His') . '.pdf');
}
```

### PDF Export — Large Data Limit

DomPDF renders HTML into memory. Exporting thousands of rows causes memory exhaustion (black/corrupt PDF). Apply this pattern to every `exportPdf` method:

```php
public function exportPdf(): StreamedResponse
{
    ini_set('memory_limit', '512M');
    ini_set('max_execution_time', '120');

    $query = Example::query()
        ->when(/* filters */)
        ->orderBy($this->sortBy, $this->sortDirection);

    $total = $query->count();
    $limited = $total > 500;
    $records = $query->limit(500)->get();

    if ($limited) {
        Flux::toast(variant: 'warning', text: "PDF dihadkan kepada 500 rekod daripada {$total}. Guna Excel/CSV untuk semua rekod.");
    }

    $content = Pdf::loadView('exports.example-pdf', ['records' => $records, 'limited' => $limited, 'total' => $total])
        ->setPaper('a4', 'landscape')
        ->setOption('isHtml5ParserEnabled', true)
        ->setOption('isRemoteEnabled', false)
        ->output();

    return response()->streamDownload(fn () => print($content), 'file.pdf', ['Content-Type' => 'application/pdf']);
}
```

### PDF Blade View Rules

- Use `@page { margin: 15mm; }` — required for proper DomPDF page sizing.
- Never use `tr:nth-child(even)` — unreliable in DomPDF across page breaks. Use PHP loop index instead: `{{ $i % 2 === 1 ? 'even' : '' }}` with a `.even td { background-color: #f9fafb; }` class.
- Add `tr { page-break-inside: avoid; }` to prevent rows splitting across pages.
- Show the limit notice in the `.meta` line: `{{ ($limited ?? false) ? ' (dihadkan daripada '.$total.' rekod)' : '' }}`.

### PDF Blade View

Create `resources/views/exports/example-pdf.blade.php` — keep it simple, plain HTML with inline styles (DomPDF does not support Tailwind):

```blade
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 11px; }
        table { width: 100%; border-collapse: collapse; }
        th { background: #f3f4f6; text-align: left; padding: 6px 8px; border: 1px solid #d1d5db; }
        td { padding: 5px 8px; border: 1px solid #e5e7eb; }
        tr:nth-child(even) td { background: #f9fafb; }
        h2 { font-size: 14px; margin-bottom: 8px; }
        .meta { font-size: 10px; color: #6b7280; margin-bottom: 12px; }
    </style>
</head>
<body>
    <h2>Senarai Rekod</h2>
    <p class="meta">Dicetak pada: {{ now()->format('d/m/Y H:i') }}</p>
    <table>
        <thead>
            <tr>
                <th>#</th>
                <th>Nama</th>
                <th>Status</th>
                <th>Tarikh Dicipta</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($records as $record)
                <tr>
                    <td>{{ $record->id }}</td>
                    <td>{{ $record->name }}</td>
                    <td>{{ $record->status->label() }}</td>
                    <td>{{ $record->created_at->format('d/m/Y') }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>
```

### Blade Template — Download Buttons

Place the download button group **above the per-page / search row**:

```blade
{{-- Download buttons (optional) --}}
<div class="mb-4 flex items-center gap-2">
    <flux:button wire:click="exportExcel" icon="arrow-down-tray" size="sm" variant="outline">
        Excel
    </flux:button>
    <flux:button wire:click="exportPdf" icon="arrow-down-tray" size="sm" variant="outline">
        PDF
    </flux:button>
    <flux:button wire:click="exportCsv" icon="arrow-down-tray" size="sm" variant="outline">
        CSV
    </flux:button>
</div>
```

### Rules

- Export methods must use the **same filter/search state** as the datatable query — never export the full unfiltered table unless explicitly required.
- Name downloaded files with the module name + timestamp: `modulename-YYYYMMDD-HHmmss.ext`.
- PDF export uses a dedicated Blade view under `resources/views/exports/` with **inline styles only** — DomPDF does not support Tailwind or external CSS.
- Use `ShouldAutoSize` on all Excel exports to improve readability.
- Gate download buttons with `@can` if the export action requires a specific permission.
- Do not add download buttons unless the user explicitly requests export functionality.

---

## Livewire Component Pattern

```php
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Url;

class ExampleTable extends Component
{
    use WithPagination;

    // Global search
    #[Url]
    public string $search = '';

    // Individual field filters — add per module as needed
    #[Url]
    public string $filterStatus = '';

    #[Url]
    public string $sortBy = 'created_at';

    #[Url]
    public string $sortDirection = 'desc';

    public int $perPage = 10;

    public function sort(string $column): void
    {
        if ($this->sortBy === $column) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortBy = $column;
            $this->sortDirection = 'asc';
        }

        $this->resetPage();
    }

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function updatedFilterStatus(): void
    {
        $this->resetPage();
    }

    public function updatedPerPage(): void
    {
        $this->resetPage();
    }

    #[Computed]
    public function records()
    {
        return \App\Models\Example::query()
            ->when($this->search, fn ($q) => $q->where(function ($q) {
                // Global search — cover all relevant text columns
                $q->where('name', 'like', "%{$this->search}%")
                  ->orWhere('reference_no', 'like', "%{$this->search}%");
            }))
            ->when($this->filterStatus, fn ($q) => $q->where('status', $this->filterStatus))
            ->orderBy($this->sortBy, $this->sortDirection)
            ->paginate($this->perPage);
    }
}
```

---

## Blade Template Pattern

```blade
<div>
    {{-- Individual field search (optional — add when module needs advanced filtering) --}}
    <div class="mb-4 grid gap-3 sm:grid-cols-2 lg:grid-cols-4">
        <flux:input wire:model.live.debounce.300ms="filterField1" placeholder="Field 1..." clearable />
        <flux:input wire:model.live.debounce.300ms="filterField2" placeholder="Field 2..." clearable />
        {{-- add more per-field filters as needed --}}
    </div>

    {{-- Per-page (left) + Global search (right) --}}
    <div class="mb-4 flex items-center justify-between">
        <div class="flex items-center gap-2">
            <flux:select wire:model.live="perPage" class="w-24">
                <flux:select.option value="10">10</flux:select.option>
                <flux:select.option value="25">25</flux:select.option>
                <flux:select.option value="50">50</flux:select.option>
                <flux:select.option value="100">100</flux:select.option>
            </flux:select>
            <span class="text-sm text-zinc-500">entries per page</span>
        </div>
        <div class="flex items-center gap-2">
            <span class="text-sm text-zinc-500">Cari:</span>
            <div class="w-48">
                <flux:input wire:model.live.debounce.300ms="search" placeholder="Cari..." clearable size="sm" />
            </div>
        </div>
    </div>

    <flux:table :paginate="$this->records">
        <flux:table.columns>
            <flux:table.column class="w-12">Bil</flux:table.column>
            <flux:table.column
                sortable
                :sorted="$sortBy === 'name'"
                :direction="$sortDirection"
                wire:click="sort('name')"
            >Name</flux:table.column>
            <flux:table.column>Status</flux:table.column>
            <flux:table.column></flux:table.column>
        </flux:table.columns>
        <flux:table.rows>
            @forelse ($this->records as $loop_index => $record)
                <flux:table.row :key="$record->id" wire:key="record-{{ $record->id }}">
                    <flux:table.cell class="text-zinc-400 text-sm tabular-nums">
                        {{ $this->records->firstItem() + $loop_index }}
                    </flux:table.cell>
                    <flux:table.cell>{{ $record->name }}</flux:table.cell>
                    <flux:table.cell>
                        <flux:badge color="{{ $record->status->color() }}" size="sm">
                            {{ $record->status->label() }}
                        </flux:badge>
                    </flux:table.cell>
                    <flux:table.cell>
                        <flux:dropdown>
                            <flux:button variant="ghost" size="sm" icon="ellipsis-horizontal" inset="top bottom" />
                            <flux:menu>
                                <flux:menu.item :href="route('records.show', $record)" wire:navigate icon="eye">Lihat</flux:menu.item>
                                @can('update', $record)
                                    <flux:menu.item :href="route('records.edit', $record)" wire:navigate icon="pencil">Kemaskini</flux:menu.item>
                                @endcan
                                @can('delete', $record)
                                    <flux:menu.separator />
                                    <flux:modal.trigger name="delete-{{ $record->id }}">
                                        <flux:menu.item variant="danger" icon="trash">Padam</flux:menu.item>
                                    </flux:modal.trigger>
                                @endcan
                            </flux:menu>
                        </flux:dropdown>
                    </flux:table.cell>
                </flux:table.row>
            @empty
                <flux:table.row>
                    <flux:table.cell colspan="3" class="py-12 text-center text-zinc-500">
                        Tiada rekod ditemui.
                    </flux:table.cell>
                </flux:table.row>
            @endforelse
        </flux:table.rows>
    </flux:table>
</div>
```

---

## Pagination — First & Last Links

The Flux pagination view has been customised (published to `resources/views/flux/pagination.blade.php`) to add `«` (First) and `»` (Last) buttons flanking the standard `<` `>` prev/next buttons. This applies globally to every `flux:table :paginate` in the app — no per-table code is needed.

---

## Rules

- Use `#[Url]` on `$search`, `$sortBy`, `$sortDirection`, and any `$filter*` properties so the table state is bookmarkable.
- Use `wire:model.live.debounce.300ms` on all text search inputs to avoid firing on every keystroke.
- Use `wire:model.live` (no debounce) on dropdowns and select filters for immediate response.
- Always call `$this->resetPage()` in every `updated*` method for search, filters, and perPage.
- Use `#[Computed]` for the query method — never store paginated results in a public property.
- Use `@forelse` with an `@empty` fallback row in every table.
- Always add `wire:key="record-{{ $record->id }}"` on every `flux:table.row` in a loop.
- Always include a **Bil** (row number) column as the first column. Use `$this->records->firstItem() + $loop_index` so the number continues correctly across pages (e.g. page 2 starts at 16, not 1). Use `tabular-nums` and `text-zinc-400` on the cell. The column header should be `Bil` with `class="w-12"`.
- Action buttons must be role/policy gated — use `@can` or role checks before rendering Edit/Delete.
- Do not use client-side libraries (e.g. datatables.net jQuery plugin). All filtering, sorting, and pagination must be server-side via Livewire.
