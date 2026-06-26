<?php

declare(strict_types=1);

namespace App\Models;

use App\Traits\Tenantable;
use Illuminate\Database\Eloquent\Model;

/**
 * Discount Model
 * 
 * @property int $id
 * @property int $businessId
 * @property string $name
 * @property string|null $description
 * @property float|null $discount
 * @property float|null $discountAmount
 * @property string $discountType
 * @property string $discountCode
 * @property \Illuminate\Support\Carbon|null $startDate
 * @property \Illuminate\Support\Carbon|null $endDate
 * @property string $status
 * @property int|null $maxUses
 * @property int $uses
 * @property \Illuminate\Support\Carbon|null $createdAt
 * @property \Illuminate\Support\Carbon|null $updatedAt
 */
class Discount extends Model
{
    use Tenantable;

    protected $table = 'discounts';
    public $timestamps = false;

    const CREATED_AT = 'createdAt';
    const UPDATED_AT = 'updatedAt';

    protected $fillable = [
        'businessId',
        'name',
        'description',
        'discount',
        'discountAmount',
        'discountType',
        'discountCode',
        'startDate',
        'endDate',
        'status',
        'maxUses',
        'uses',
    ];

    protected $casts = [
        'businessId' => 'integer',
        'discount' => 'float',
        'discountAmount' => 'float',
        'startDate' => 'datetime',
        'endDate' => 'datetime',
        'maxUses' => 'integer',
        'uses' => 'integer',
        'createdAt' => 'datetime',
        'updatedAt' => 'datetime',
    ];

    public function orders()
    {
        return $this->hasMany(Order::class, 'discountId');
    }

    public function business()
    {
        return $this->belongsTo(Business::class, 'businessId');
    }
}
