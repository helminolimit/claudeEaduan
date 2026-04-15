<?php

namespace App\Livewire\Complaints;

use App\Enums\ComplaintPriority;
use App\Enums\ComplaintStatus;
use App\Enums\UserRole;
use App\Models\Complaint;
use App\Models\User;
use App\Services\NotificationService;
use Flux\Flux;
use Illuminate\Database\Eloquent\Collection;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Title('Complaint Detail')]
class Show extends Component
{
    public Complaint $complaint;

    public string $newStatus = '';

    public string $rejectionReason = '';

    public int|string $assignOfficerId = '';

    public string $newPriority = '';

    public string $commentBody = '';

    public function mount(Complaint $complaint): void
    {
        $complaint->load(['user', 'category', 'officer', 'attachments']);
        $this->complaint = $complaint;
        $this->newStatus = $complaint->status->value;
        $this->assignOfficerId = $complaint->officer_id ?? '';
        $this->newPriority = $complaint->priority->value;
    }

    public function updateStatus(): void
    {
        $this->validate([
            'newStatus' => ['required', 'string', 'in:'.implode(',', array_column(ComplaintStatus::cases(), 'value'))],
            'rejectionReason' => ['required_if:newStatus,rejected', 'nullable', 'string', 'max:1000'],
        ]);

        $oldStatus = $this->complaint->status->value;
        $this->complaint->update(['status' => $this->newStatus]);

        $this->complaint->logs()->create([
            'user_id' => auth()->id(),
            'old_status' => $oldStatus,
            'new_status' => $this->newStatus,
            'notes' => $this->newStatus === ComplaintStatus::Rejected->value ? $this->rejectionReason : null,
            'created_at' => now(),
        ]);

        $this->complaint->refresh();
        app(NotificationService::class)->statusChanged($this->complaint);

        $this->rejectionReason = '';
        Flux::modal('update-status')->close();
        Flux::toast(variant: 'success', text: 'Status updated.');
    }

    public function assign(): void
    {
        $this->validate([
            'assignOfficerId' => ['required', 'integer', 'exists:users,id'],
        ]);

        $this->complaint->update(['officer_id' => $this->assignOfficerId]);
        $this->complaint->refresh();
        app(NotificationService::class)->complaintAssigned($this->complaint);
        Flux::modal('assign-officer')->close();
        Flux::toast(variant: 'success', text: 'Officer assigned.');
    }

    public function updatePriority(): void
    {
        $this->validate([
            'newPriority' => ['required', 'string', 'in:'.implode(',', array_column(ComplaintPriority::cases(), 'value'))],
        ]);

        $this->complaint->update(['priority' => $this->newPriority]);
        $this->complaint->refresh();
        Flux::modal('update-priority')->close();
        Flux::toast(variant: 'success', text: 'Priority updated.');
    }

    public function addComment(): void
    {
        $this->validate([
            'commentBody' => ['required', 'string', 'max:2000'],
        ]);

        $this->complaint->comments()->create([
            'user_id' => auth()->id(),
            'body' => $this->commentBody,
        ]);

        app(NotificationService::class)->commentAdded($this->complaint, auth()->id());

        $this->commentBody = '';
        unset($this->comments);
        Flux::toast(variant: 'success', text: 'Comment added.');
    }

    public function delete(): void
    {
        $this->complaint->delete();
        Flux::modals()->close();
        $this->redirect(route('admin.aduan.index'), navigate: true);
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

    #[Computed]
    public function comments()
    {
        return $this->complaint->comments()->with('user')->get();
    }

    #[Computed]
    public function logs()
    {
        return $this->complaint->logs()->with('user')->get();
    }

    public function render()
    {
        return view('livewire.complaints.show');
    }
}
