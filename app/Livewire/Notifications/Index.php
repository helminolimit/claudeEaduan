<?php

namespace App\Livewire\Notifications;

use App\Models\Notification;
use Flux\Flux;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

#[Title('Notifications')]
class Index extends Component
{
    use WithPagination;

    #[Url]
    public string $search = '';

    #[Url]
    public string $filterRead = '';

    public int $perPage = 10;

    public function markRead(int $id): void
    {
        $notification = Notification::where('user_id', auth()->id())->findOrFail($id);
        $notification->markAsRead();

        if ($notification->complaint_id) {
            $this->redirect(route('aduan.show', $notification->complaint_id), navigate: true);
        }
    }

    public function markAllRead(): void
    {
        Notification::where('user_id', auth()->id())
            ->where('is_read', false)
            ->update(['is_read' => true]);

        Flux::toast(variant: 'success', text: 'Semua notifikasi telah ditandai sebagai dibaca.');
        $this->resetPage();
    }

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function updatedFilterRead(): void
    {
        $this->resetPage();
    }

    public function updatedPerPage(): void
    {
        $this->resetPage();
    }

    #[Computed]
    public function notifications()
    {
        return Notification::where('user_id', auth()->id())
            ->when($this->search, fn ($q) => $q->where('message', 'like', "%{$this->search}%"))
            ->when($this->filterRead !== '', fn ($q) => $q->where('is_read', (bool) $this->filterRead))
            ->latest()
            ->paginate($this->perPage);
    }

    #[Computed]
    public function unreadCount(): int
    {
        return Notification::where('user_id', auth()->id())
            ->where('is_read', false)
            ->count();
    }

    public function render()
    {
        return view('livewire.notifications.index');
    }
}
