<?php

namespace App\Livewire\Dashboard;

use App\Enums\ComplaintStatus;
use App\Enums\UserRole;
use App\Models\Complaint;
use App\Models\User;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Title('Admin Dashboard')]
class AdminDashboard extends Component
{
    #[Computed]
    public function stats(): array
    {
        $counts = Complaint::selectRaw('status, COUNT(*) as count')
            ->groupBy('status')
            ->pluck('count', 'status')
            ->toArray();

        $unassigned = Complaint::whereNull('officer_id')
            ->whereNotIn('status', [ComplaintStatus::Closed->value, ComplaintStatus::Rejected->value])
            ->count();

        return [
            'total' => array_sum($counts),
            'pending' => $counts[ComplaintStatus::Pending->value] ?? 0,
            'in_review' => $counts[ComplaintStatus::InReview->value] ?? 0,
            'in_progress' => $counts[ComplaintStatus::InProgress->value] ?? 0,
            'resolved' => ($counts[ComplaintStatus::Resolved->value] ?? 0) + ($counts[ComplaintStatus::Closed->value] ?? 0),
            'unassigned' => $unassigned,
        ];
    }

    #[Computed]
    public function officerWorkload()
    {
        return User::where('role', UserRole::Officer)
            ->withCount([
                'assignedComplaints',
                'assignedComplaints as open_count' => fn ($q) => $q->whereNotIn('status', [
                    ComplaintStatus::Resolved->value,
                    ComplaintStatus::Closed->value,
                    ComplaintStatus::Rejected->value,
                ]),
            ])
            ->orderByDesc('open_count')
            ->get();
    }

    #[Computed]
    public function recentComplaints()
    {
        return Complaint::with(['user', 'category', 'officer'])
            ->latest()
            ->limit(8)
            ->get();
    }

    public function render()
    {
        return view('livewire.dashboard.admin-dashboard');
    }
}
