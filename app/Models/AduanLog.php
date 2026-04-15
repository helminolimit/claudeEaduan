<?php

namespace App\Models;

use App\Enums\ComplaintStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AduanLog extends Model
{
    public $timestamps = false;

    /** @var array<int, string> */
    protected $fillable = [
        'complaint_id',
        'user_id',
        'old_status',
        'new_status',
        'notes',
        'created_at',
    ];

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'old_status' => ComplaintStatus::class,
            'new_status' => ComplaintStatus::class,
            'created_at' => 'datetime',
        ];
    }

    public function complaint(): BelongsTo
    {
        return $this->belongsTo(Complaint::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
