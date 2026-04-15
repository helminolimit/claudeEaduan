<?php

namespace App\Livewire\Reports;

use App\Enums\ComplaintPriority;
use App\Enums\ComplaintStatus;
use App\Enums\UserRole;
use App\Exports\ComplaintsExport;
use App\Models\Category;
use App\Models\Complaint;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Cache;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\StreamedResponse;

#[Title('Reports')]
class Dashboard extends Component
{
    #[Url]
    public string $dateFrom = '';

    #[Url]
    public string $dateTo = '';

    #[Url]
    public string $filterStatus = '';

    #[Url]
    public string $filterCategory = '';

    #[Url]
    public string $filterPriority = '';

    #[Url]
    public string $filterOfficer = '';

    #[Url]
    public string $activeTab = 'summary';

    public function mount(): void
    {
        $this->dateFrom = now()->subMonths(3)->format('Y-m-d');
        $this->dateTo = now()->format('Y-m-d');
    }

    public function resetFilters(): void
    {
        $this->dateFrom = now()->subMonths(3)->format('Y-m-d');
        $this->dateTo = now()->format('Y-m-d');
        $this->filterStatus = '';
        $this->filterCategory = '';
        $this->filterPriority = '';
        $this->filterOfficer = '';
    }

    // ── Queries ──────────────────────────────────────────────────────────────

    private function baseQuery(): Builder
    {
        return Complaint::query()
            ->when($this->dateFrom, fn ($q) => $q->whereDate('complaints.created_at', '>=', $this->dateFrom))
            ->when($this->dateTo, fn ($q) => $q->whereDate('complaints.created_at', '<=', $this->dateTo))
            ->when($this->filterStatus, fn ($q) => $q->where('status', $this->filterStatus))
            ->when($this->filterCategory, fn ($q) => $q->where('category_id', $this->filterCategory))
            ->when($this->filterPriority, fn ($q) => $q->where('priority', $this->filterPriority))
            ->when($this->filterOfficer, fn ($q) => $q->where('officer_id', $this->filterOfficer));
    }

    private function cacheKey(string $prefix): string
    {
        return 'reports:'.$prefix.':'.md5(implode('|', [
            $this->dateFrom, $this->dateTo,
            $this->filterStatus, $this->filterCategory,
            $this->filterPriority, $this->filterOfficer,
        ]));
    }

    // ── Computed: Summary ─────────────────────────────────────────────────────

    #[Computed]
    public function summaryStats(): array
    {
        return Cache::remember($this->cacheKey('stats'), 600, function () {
            $counts = $this->baseQuery()
                ->selectRaw('status, COUNT(*) as count')
                ->groupBy('status')
                ->pluck('count', 'status')
                ->toArray();

            return [
                'total' => array_sum($counts),
                'pending' => $counts[ComplaintStatus::Pending->value] ?? 0,
                'in_review' => $counts[ComplaintStatus::InReview->value] ?? 0,
                'in_progress' => $counts[ComplaintStatus::InProgress->value] ?? 0,
                'resolved' => $counts[ComplaintStatus::Resolved->value] ?? 0,
                'closed' => $counts[ComplaintStatus::Closed->value] ?? 0,
                'rejected' => $counts[ComplaintStatus::Rejected->value] ?? 0,
            ];
        });
    }

    #[Computed]
    public function statusChartData(): array
    {
        return Cache::remember($this->cacheKey('status_chart'), 600, function () {
            $counts = $this->baseQuery()
                ->selectRaw('status, COUNT(*) as count')
                ->groupBy('status')
                ->pluck('count', 'status')
                ->toArray();

            $colorMap = [
                'pending' => '#eab308',
                'in_review' => '#3b82f6',
                'in_progress' => '#06b6d4',
                'resolved' => '#22c55e',
                'closed' => '#71717a',
                'rejected' => '#ef4444',
            ];

            $statuses = ComplaintStatus::cases();

            return [
                'labels' => array_map(fn ($s) => $s->label(), $statuses),
                'datasets' => [[
                    'data' => array_map(fn ($s) => $counts[$s->value] ?? 0, $statuses),
                    'backgroundColor' => array_map(fn ($s) => $colorMap[$s->value], $statuses),
                    'borderWidth' => 2,
                    'borderColor' => '#ffffff',
                ]],
            ];
        });
    }

    #[Computed]
    public function categoryChartData(): array
    {
        return Cache::remember($this->cacheKey('category_chart'), 600, function () {
            $rows = $this->baseQuery()
                ->join('categories', 'complaints.category_id', '=', 'categories.id')
                ->selectRaw('categories.name as category_name, COUNT(complaints.id) as count')
                ->groupBy('categories.name')
                ->orderByDesc('count')
                ->limit(8)
                ->get();

            return [
                'labels' => $rows->pluck('category_name')->all(),
                'datasets' => [[
                    'label' => 'Complaints',
                    'data' => $rows->pluck('count')->all(),
                    'backgroundColor' => 'rgba(59, 130, 246, 0.75)',
                    'borderRadius' => 4,
                ]],
            ];
        });
    }

    // ── Computed: Trend ───────────────────────────────────────────────────────

    #[Computed]
    public function trendChartData(): array
    {
        return Cache::remember($this->cacheKey('trend_chart'), 600, function () {
            $rows = $this->baseQuery()
                ->selectRaw("DATE_FORMAT(complaints.created_at, '%Y-%m') as month, COUNT(*) as count")
                ->groupBy('month')
                ->orderBy('month')
                ->pluck('count', 'month')
                ->toArray();

            return [
                'labels' => array_keys($rows),
                'datasets' => [[
                    'label' => 'Complaints Submitted',
                    'data' => array_values($rows),
                    'borderColor' => 'rgb(59, 130, 246)',
                    'backgroundColor' => 'rgba(59, 130, 246, 0.1)',
                    'fill' => true,
                    'tension' => 0.4,
                    'pointRadius' => 4,
                ]],
            ];
        });
    }

    // ── Computed: Resolution ──────────────────────────────────────────────────

    #[Computed]
    public function resolutionData(): Collection
    {
        return Cache::remember($this->cacheKey('resolution'), 600, function () {
            return $this->baseQuery()
                ->join('categories', 'complaints.category_id', '=', 'categories.id')
                ->whereIn('complaints.status', ['resolved', 'closed'])
                ->selectRaw('categories.name as category_name, COUNT(complaints.id) as count, ROUND(AVG(DATEDIFF(complaints.updated_at, complaints.created_at)), 1) as avg_days')
                ->groupBy('categories.name')
                ->orderByDesc('count')
                ->get();
        });
    }

    // ── Computed: Officer Performance ─────────────────────────────────────────

    #[Computed]
    public function officerData(): Collection
    {
        return Cache::remember($this->cacheKey('officers'), 600, function () {
            return $this->baseQuery()
                ->join('users', 'complaints.officer_id', '=', 'users.id')
                ->whereNotNull('complaints.officer_id')
                ->selectRaw("users.name as officer_name, COUNT(complaints.id) as total, SUM(complaints.status IN ('resolved','closed')) as resolved, ROUND(AVG(CASE WHEN complaints.status IN ('resolved','closed') THEN DATEDIFF(complaints.updated_at, complaints.created_at) END), 1) as avg_days")
                ->groupBy('users.name')
                ->orderByDesc('total')
                ->get();
        });
    }

    // ── Computed: Filter options ──────────────────────────────────────────────

    #[Computed]
    public function categories(): Collection
    {
        return Category::where('is_active', true)->orderBy('name')->get();
    }

    #[Computed]
    public function officers(): Collection
    {
        return User::where('role', UserRole::Officer)->orderBy('name')->get();
    }

    #[Computed]
    public function statuses(): array
    {
        return ComplaintStatus::cases();
    }

    #[Computed]
    public function priorities(): array
    {
        return ComplaintPriority::cases();
    }

    // ── Chart re-render key ───────────────────────────────────────────────────

    #[Computed]
    public function chartKey(): string
    {
        return substr(md5(implode('|', [
            $this->dateFrom, $this->dateTo,
            $this->filterStatus, $this->filterCategory,
            $this->filterPriority, $this->filterOfficer,
        ])), 0, 8);
    }

    // ── Exports ───────────────────────────────────────────────────────────────

    public function exportExcel(): BinaryFileResponse
    {
        return Excel::download(
            new ComplaintsExport(
                $this->dateFrom,
                $this->dateTo,
                $this->filterStatus,
                $this->filterCategory,
                $this->filterPriority,
                $this->filterOfficer,
            ),
            'laporan-aduan-'.now()->format('Ymd-His').'.xlsx'
        );
    }

    public function exportPdf(): StreamedResponse
    {
        ini_set('memory_limit', '512M');

        $query = $this->baseQuery()
            ->with(['user', 'category', 'officer'])
            ->orderByDesc('created_at');

        $total = $query->count();
        $limited = $total > 500;
        $complaints = $query->limit(500)->get();

        $content = Pdf::loadView('exports.complaints-pdf', [
            'complaints' => $complaints,
            'dateFrom' => $this->dateFrom,
            'dateTo' => $this->dateTo,
            'stats' => $this->summaryStats,
            'limited' => $limited,
            'total' => $total,
        ])
            ->setPaper('a4', 'landscape')
            ->setOption('isHtml5ParserEnabled', true)
            ->setOption('isRemoteEnabled', false)
            ->output();

        return response()->streamDownload(
            fn () => print ($content),
            'laporan-aduan-'.now()->format('Ymd-His').'.pdf',
            ['Content-Type' => 'application/pdf']
        );
    }

    public function render()
    {
        return view('livewire.reports.dashboard');
    }
}
