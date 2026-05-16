<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CustomerOrder extends Model
{
    use HasFactory;

    public const STATUS_PENDING = 'pending';
    public const STATUS_PROCESSING = 'processing';
    public const STATUS_COMPLETED = 'completed';
    public const STATUS_CANCELLED = 'cancelled';

    protected $fillable = [
        'order_code',
        'customer_name',
        'total_qty',
        'total_amount',
        'status',
        'ordered_at',
        'processing_at',
        'completed_at',
        'cancelled_at',
        'stock_applied_at',
    ];

    protected $appends = ['status_label', 'status_badge_class'];

    protected function casts(): array
    {
        return [
            'ordered_at' => 'datetime',
            'processing_at' => 'datetime',
            'completed_at' => 'datetime',
            'cancelled_at' => 'datetime',
            'stock_applied_at' => 'datetime',
        ];
    }

    public function items()
    {
        return $this->hasMany(CustomerOrderItem::class);
    }

    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            self::STATUS_PENDING => 'Pending',
            self::STATUS_PROCESSING => 'Diproses',
            self::STATUS_COMPLETED => 'Selesai',
            self::STATUS_CANCELLED => 'Dibatalkan',
            default => ucfirst((string) $this->status),
        };
    }

    public function getStatusBadgeClassAttribute(): string
    {
        return match ($this->status) {
            self::STATUS_PENDING => 'status-pending',
            self::STATUS_PROCESSING => 'status-processing',
            self::STATUS_COMPLETED => 'status-safe',
            self::STATUS_CANCELLED => 'status-muted',
            default => 'status-muted',
        };
    }
}
