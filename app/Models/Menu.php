<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Menu extends Model
{
    use HasFactory;

    protected $fillable = [
        'menu_category_id',
        'name',
        'slug',
        'price',
        'image',
        'description',
        'recipe_notes',
        'availability_status',
        'is_visible',
    ];

    protected $appends = ['availability_label', 'image_url'];

    protected function casts(): array
    {
        return ['is_visible' => 'boolean'];
    }

    public function category()
    {
        return $this->belongsTo(MenuCategory::class, 'menu_category_id');
    }

    public function ingredients()
    {
        return $this->belongsToMany(Item::class, 'menu_item')
            ->withPivot('qty_required')
            ->withTimestamps();
    }

    public function orderItems()
    {
        return $this->hasMany(CustomerOrderItem::class);
    }

    public function completedOrderItems()
    {
        return $this->hasMany(CustomerOrderItem::class)
            ->whereHas('order', function ($query) {
                $query->where('status', CustomerOrder::STATUS_COMPLETED);
            });
    }

    public function getAvailabilityStatusAttribute($value): string
    {
        $ingredients = $this->relationLoaded('ingredients')
            ? $this->ingredients
            : $this->ingredients()->get();

        if ($ingredients->isEmpty()) {
            return $value ?: 'available';
        }

        $hasLow = false;

        foreach ($ingredients as $item) {
            $required = max((int) ($item->pivot->qty_required ?? 1), 1);

            if ($item->stock <= 0 || $item->stock < $required) {
                return 'out';
            }

            if ($item->stock <= $item->min_stock) {
                $hasLow = true;
            }
        }

        return $hasLow ? 'low' : 'available';
    }

    public function getAvailabilityLabelAttribute(): string
    {
        return match ($this->availability_status) {
            'available' => 'Tersedia',
            'low' => 'Hampir Habis',
            default => 'Habis',
        };
    }

    public function getImageUrlAttribute(): ?string
    {
        if (! $this->image) {
            return null;
        }

        return asset('storage/' . $this->image) . '?v=' . optional($this->updated_at)->timestamp;
    }
}
