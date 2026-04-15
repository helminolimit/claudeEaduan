<?php

namespace App\Enums;

enum ComplaintStatus: string
{
    case Pending = 'pending';
    case InReview = 'in_review';
    case InProgress = 'in_progress';
    case Resolved = 'resolved';
    case Closed = 'closed';
    case Rejected = 'rejected';

    public function label(): string
    {
        return match ($this) {
            self::Pending => 'Pending',
            self::InReview => 'In Review',
            self::InProgress => 'In Progress',
            self::Resolved => 'Resolved',
            self::Closed => 'Closed',
            self::Rejected => 'Rejected',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::Pending => 'yellow',
            self::InReview => 'blue',
            self::InProgress => 'cyan',
            self::Resolved => 'green',
            self::Closed => 'zinc',
            self::Rejected => 'red',
        };
    }
}
