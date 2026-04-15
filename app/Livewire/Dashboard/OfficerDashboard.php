<?php

namespace App\Livewire\Dashboard;

use App\Enums\ComplaintPriority;
use App\Enums\ComplaintStatus;
use App\Models\Complaint;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Title('Officer Dashboard')]
class OfficerDashboard extends Component
{
    #[Computed]
    public function stats(): array
    {
        $counts = Complaint::where('officer_id', auth()->id())
            ->selectRaw('status, COUNT(*) as count')
            ->groupBy('status')
            ->pluck('count', 'status')
            ->toArray();

        return [
            'total' => array_sum($counts),
            'pending' => ($counts[ComplaintStatus::Pending->value] ?? 0) + ($counts[ComplaintStatus::InReview->value] ?? 0),
            'in_progress' => $counts[ComplaintStatus::InProgress->value] ?? 0,
            'resolved' => ($counts[ComplaintStatus::Resolved->value] ?? 0) + ($counts[ComplaintStatus::Closed->value] ?? 0),
        ];
    }

    #[Computed]
    public function priorityBreakdown(): array
    {
        $counts = Complaint::where('officer_id', auth()->id())
            ->whereNotIn('status', [ComplaintStatus::Resolved->value, ComplaintStatus::Closed->value, ComplaintStatus::Rejected->value])
            ->selectRaw('priority, COUNT(*) as count')
            ->groupBy('priority')
            ->pluck('count', 'priority')
            ->toArray();

        return array_map(fn (ComplaintPriority $p) => [
            'priority' => $p,
            'count' => $counts[$p->value] ?? 0,
        ], ComplaintPriority::cases());
    }

    #[Computed]
    public function recentComplaints()
    {
        return Complaint::where('officer_id', auth()->id())
            ->with(['user', 'category'])
            ->latest()
            ->limit(10)
            ->get();
    }

    public function render()
    {
        return view('livewire.dashboard.officer-dashboard');
    }
}
