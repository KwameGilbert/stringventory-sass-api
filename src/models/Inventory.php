<?php

declare(strict_types=1);

namespace App\Models;

use App\Traits\Tenantable;
use Illuminate\Database\Eloquent\Model;

/**
 * Inventory Model
 * 
 * @property int $id
 * @property int $businessId
 * @property int $productId
 * @property int $quantity
 * @property string|null $warehouseLocation
 * @property string $status
 * @property \Illuminate\Support\Carbon|null $createdAt
 */
class Inventory extends Model
{
    use Tenantable;

    protected $table = 'inventory';
    public $timestamps = false;

    const CREATED_AT = 'createdAt';

    protected $fillable = [
        'businessId',
        'productId',
        'quantity',
        'warehouseLocation',
        'status',
        'lastUpdated',
    ];

    protected $casts = [
        'businessId' => 'integer',
        'productId' => 'integer',
        'quantity' => 'integer',
        'createdAt' => 'datetime',
    ];

    protected $appends = ['soonestExpiryDate'];

    public function getSoonestExpiryDateAttribute(): ?string
    {
        return $this->product ? $this->product->soonestExpiryDate : null;
    }

    public function product()
    {
        return $this->belongsTo(Product::class, 'productId');
    }

    public function business()
    {
        return $this->belongsTo(Business::class, 'businessId');
    }
}
