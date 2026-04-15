<?php

namespace App\Services;

use App\Enums\ComplaintStatus;
use App\Enums\UserRole;
use App\Models\Complaint;
use App\Models\Notification;
use App\Models\User;

class NotificationService
{
    public function notify(int $userId, string $type, string $message, ?int $complaintId = null): void
    {
        Notification::create([
            'user_id' => $userId,
            'complaint_id' => $complaintId,
            'type' => $type,
            'message' => $message,
        ]);
    }

    public function complaintSubmitted(Complaint $complaint): void
    {
        $this->notify(
            $complaint->user_id,
            'complaint_submitted',
            "Aduan #{$complaint->aduan_no} telah berjaya dihantar.",
            $complaint->id
        );

        User::where('role', UserRole::Admin)->get()->each(
            fn (User $admin) => $this->notify(
                $admin->id,
                'new_complaint',
                "Aduan baru #{$complaint->aduan_no} diterima: {$complaint->title}",
                $complaint->id
            )
        );
    }

    public function complaintAssigned(Complaint $complaint): void
    {
        if (! $complaint->officer_id) {
            return;
        }

        $this->notify(
            $complaint->officer_id,
            'complaint_assigned',
            "Aduan #{$complaint->aduan_no} telah diagihkan kepada anda.",
            $complaint->id
        );
    }

    public function statusChanged(Complaint $complaint): void
    {
        $message = match ($complaint->status) {
            ComplaintStatus::Resolved => "Aduan #{$complaint->aduan_no} telah diselesaikan.",
            ComplaintStatus::Closed => "Aduan #{$complaint->aduan_no} telah ditutup.",
            ComplaintStatus::Rejected => "Aduan #{$complaint->aduan_no} telah ditolak.",
            default => "Status aduan #{$complaint->aduan_no} telah dikemaskini kepada {$complaint->status->label()}.",
        };

        $this->notify($complaint->user_id, 'status_changed', $message, $complaint->id);
    }

    public function commentAdded(Complaint $complaint, int $commenterId): void
    {
        if ($complaint->user_id !== $commenterId) {
            $this->notify(
                $complaint->user_id,
                'comment_added',
                "Komen baru ditambah pada aduan #{$complaint->aduan_no}.",
                $complaint->id
            );
        }

        if ($complaint->officer_id && $complaint->officer_id !== $commenterId) {
            $this->notify(
                $complaint->officer_id,
                'comment_added',
                "Komen baru ditambah pada aduan #{$complaint->aduan_no}.",
                $complaint->id
            );
        }
    }
}
