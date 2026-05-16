<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
class StockMovement extends Model
{
    use HasFactory;
    protected $fillable = ['item_id','user_id','type','qty','stock_before','stock_after','note','movement_date'];
    protected function casts(): array { return ['movement_date' => 'date']; }
    public function item() { return $this->belongsTo(Item::class); }
    public function user() { return $this->belongsTo(User::class); }
}
