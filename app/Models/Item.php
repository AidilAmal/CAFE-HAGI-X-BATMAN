<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Item extends Model
{
    use HasFactory;

    protected $fillable = [
        'category_id',
        'supplier_id',
        'name',
        'code',
        'barcode',
        'unit',
        'stock',
        'min_stock',
        'expired_at',
        'price',
        'image',
        'description',
        'status',
    ];

    protected $appends = ['stock_status', 'expiry_status', 'expiry_label'];

    protected function casts(): array
    {
        return [
            'expired_at' => 'date',
        ];
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }

    public function stockMovements()
    {
        return $this->hasMany(StockMovement::class);
    }

    public function menus()
    {
        return $this->belongsToMany(Menu::class, 'menu_item')
            ->withPivot('qty_required')
            ->withTimestamps();
    }

    public function getStockStatusAttribute(): string
    {
        if ($this->stock <= 0) {
            return 'out';
        }

        if ($this->stock <= $this->min_stock) {
            return 'low';
        }

        return 'safe';
    }

    public function getExpiryStatusAttribute(): string
    {
        if (! $this->expired_at) {
            return 'none';
        }

        $daysLeft = (int) Carbon::today()->diffInDays($this->expired_at->copy()->startOfDay(), false);

        if ($daysLeft < 0) {
            return 'expired';
        }

        if ($daysLeft <= 2) {
            return 'urgent';
        }

        if ($daysLeft <= 7) {
            return 'warning';
        }

        return 'safe';
    }

    public function getExpiryLabelAttribute(): string
    {
        if (! $this->expired_at) {
            return '-';
        }

        $daysLeft = (int) Carbon::today()->diffInDays($this->expired_at->copy()->startOfDay(), false);

        if ($daysLeft < 0) {
            return 'Expired ' . abs($daysLeft) . ' hari lalu';
        }

        if ($daysLeft === 0) {
            return 'Expired hari ini';
        }

        if ($daysLeft <= 7) {
            return 'Expired ' . $daysLeft . ' hari lagi';
        }

        return 'Aman sampai ' . $this->expired_at->format('d M Y');
    }
}
