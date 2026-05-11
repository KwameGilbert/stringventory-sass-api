<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Purchase extends Model
{
    protected $table = 'purchases';
    public $timestamps = true;

    const CREATED_AT = 'createdAt';
    const UPDATED_AT = 'updatedAt';

    protected $fillable = [
        'businessId',
        'supplierId',
        'purchaseNumber',
        'waybillNumber',
        'batchNumber',
        'purchaseDate',
        'dueDate',
        'expectedDeliveryDate',
        'receivedDate',
        'subtotal',
        'tax',
        'shippingCost',
        'totalAmount',
        'status',
        'paymentStatus',
        'paymentMethodId',
        'notes',
        'createdBy',
        'currency',
    ];

    protected $casts = [
        'businessId' => 'integer',
        'supplierId' => 'integer',
        'purchaseDate' => 'datetime',
        'dueDate' => 'datetime',
        'expectedDeliveryDate' => 'datetime',
        'receivedDate' => 'datetime',
        'subtotal' => 'float',
        'tax' => 'float',
        'shippingCost' => 'float',
        'totalAmount' => 'float',
        'createdAt' => 'datetime',
        'updatedAt' => 'datetime',
        'createdBy' => 'integer',
        'currency' => 'string',
        'paymentMethodId' => 'integer',
    ];

    public function getCreatedByAttribute($value): string|int|null
    {
        if ($this->relationLoaded('creator') && $this->creator) {
            return trim($this->creator->firstName . ' ' . $this->creator->lastName);
        }
        return $value;
    }

    public function supplier()
    {
        return $this->belongsTo(Supplier::class, 'supplierId');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'createdBy');
    }

    public function items()
    {
        return $this->hasMany(PurchaseItem::class, 'purchaseId');
    }

    public function transactions()
    {
        return $this->hasMany(Transaction::class, 'purchaseId');
    }

    public function paymentMethod()
    {
        return $this->belongsTo(PaymentMethod::class, 'paymentMethodId');
    }

    public function business()
    {
        return $this->belongsTo(Business::class, 'businessId');
    }
}
