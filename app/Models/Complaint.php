<?php

namespace App\Models;

use App\Enums\ComplaintPriority;
use App\Enums\ComplaintStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Complaint extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'aduan_no',
        'user_id',
        'category_id',
        'officer_id',
        'title',
        'description',
        'location',
        'status',
        'priority',
    ];

    protected function casts(): array
    {
        return [
            'status' => ComplaintStatus::class,
            'priority' => ComplaintPriority::class,
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function officer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'officer_id');
    }

    /**
     * Generate a unique complaint reference number.
     */
    public static function generateAduanNo(): string
    {
        $date = now()->format('Ymd');
        $prefix = "ADU-{$date}-";
        $last = static::withTrashed()
            ->where('aduan_no', 'like', "{$prefix}%")
            ->orderByDesc('aduan_no')
            ->value('aduan_no');

        $sequence = $last ? (int) substr($last, -4) + 1 : 1;

        return $prefix.str_pad($sequence, 4, '0', STR_PAD_LEFT);
    }
}
