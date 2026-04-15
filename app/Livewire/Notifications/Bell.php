<?php

namespace App\Livewire\Notifications;

use App\Models\Notification;
use Flux\Flux;
use Livewire\Attributes\Computed;
use Livewire\Component;

class Bell extends Component
{
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
    }

    #[Computed]
    public function unreadCount(): int
    {
        return Notification::where('user_id', auth()->id())
            ->where('is_read', false)
            ->count();
    }

    #[Computed]
    public function recentNotifications()
    {
        return Notification::where('user_id', auth()->id())
            ->latest()
            ->limit(10)
            ->get();
    }

    public function render()
    {
        return view('livewire.notifications.bell');
    }
}
