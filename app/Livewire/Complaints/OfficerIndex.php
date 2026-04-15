<?php

namespace App\Livewire\Complaints;

use App\Enums\ComplaintPriority;
use App\Enums\ComplaintStatus;
use App\Models\Complaint;
use Flux\Flux;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

#[Title('My Assigned Complaints')]
class OfficerIndex extends Component
{
    use WithPagination;

    #[Url]
    public string $search = '';

    #[Url]
    public string $filterStatus = '';

    #[Url]
    public string $filterPriority = '';

    #[Url]
    public string $sortBy = 'created_at';

    #[Url]
    public string $sortDirection = 'desc';

    public int $perPage = 10;

    // Status update form
    public ?int $statusComplaintId = null;

    public string $newStatus = '';

    public string $rejectionReason = '';

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

    public function updatedFilterPriority(): void
    {
        $this->resetPage();
    }

    public function updatedPerPage(): void
    {
        $this->resetPage();
    }

    public function openStatusModal(Complaint $complaint): void
    {
        $this->statusComplaintId = $complaint->id;
        $this->newStatus = $complaint->status->value;
        $this->rejectionReason = '';
        Flux::modal('update-status')->show();
    }

    public function updateStatus(): void
    {
        $this->validate([
            'newStatus' => ['required', 'string', 'in:'.implode(',', array_column(ComplaintStatus::cases(), 'value'))],
            'rejectionReason' => ['required_if:newStatus,rejected', 'nullable', 'string', 'max:1000'],
        ]);

        $complaint = Complaint::findOrFail($this->statusComplaintId);
        $oldStatus = $complaint->status->value;
        $complaint->update(['status' => $this->newStatus]);

        $complaint->logs()->create([
            'user_id' => auth()->id(),
            'old_status' => $oldStatus,
            'new_status' => $this->newStatus,
            'notes' => $this->newStatus === ComplaintStatus::Rejected->value ? $this->rejectionReason : null,
            'created_at' => now(),
        ]);

        Flux::modal('update-status')->close();
        Flux::toast(variant: 'success', text: 'Status updated.');
        $this->statusComplaintId = null;
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

    #[Computed]
    public function complaints()
    {
        return Complaint::query()
            ->with(['user', 'category' => fn ($q) => $q->withTrashed()])
            ->where('officer_id', auth()->id())
            ->when($this->search, fn ($q) => $q->where(function ($q) {
                $q->where('title', 'like', "%{$this->search}%")
                    ->orWhere('aduan_no', 'like', "%{$this->search}%");
            }))
            ->when($this->filterStatus, fn ($q) => $q->where('status', $this->filterStatus))
            ->when($this->filterPriority, fn ($q) => $q->where('priority', $this->filterPriority))
            ->orderBy($this->sortBy, $this->sortDirection)
            ->paginate($this->perPage);
    }

    public function render()
    {
        return view('livewire.complaints.officer-index');
    }
}
