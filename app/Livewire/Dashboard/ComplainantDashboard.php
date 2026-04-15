<?php

namespace App\Livewire\Dashboard;

use App\Enums\ComplaintStatus;
use App\Models\Complaint;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Title('Dashboard')]
class ComplainantDashboard extends Component
{
    #[Computed]
    public function stats(): array
    {
        $counts = Complaint::where('user_id', auth()->id())
            ->selectRaw('status, COUNT(*) as count')
            ->groupBy('status')
            ->pluck('count', 'status')
            ->toArray();

        return [
            'total' => array_sum($counts),
            'submitted' => ($counts[ComplaintStatus::Pending->value] ?? 0) + ($counts[ComplaintStatus::InReview->value] ?? 0),
            'in_progress' => $counts[ComplaintStatus::InProgress->value] ?? 0,
            'resolved' => ($counts[ComplaintStatus::Resolved->value] ?? 0) + ($counts[ComplaintStatus::Closed->value] ?? 0),
        ];
    }

    #[Computed]
    public function recentComplaints()
    {
        return Complaint::where('user_id', auth()->id())
            ->with('category')
            ->latest()
            ->limit(5)
            ->get();
    }

    public function render()
    {
        return view('livewire.dashboard.complainant-dashboard');
    }
}
