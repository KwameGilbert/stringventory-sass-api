<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * PaymentMethod Model
 * 
 * @property int $id
 * @property int $businessId
 * @property string $name
 * @property string $type
 * @property bool $enabled
 * @property string $provider
 * @property \Illuminate\Support\Carbon|null $createdAt
 * @property \Illuminate\Support\Carbon|null $updatedAt
 */
class PaymentMethod extends Model
{
    protected $table = 'payment_methods';
    protected $primaryKey = 'id';
    public $timestamps = false; // Using custom datetime columns via migrations

    const CREATED_AT = 'createdAt';
    const UPDATED_AT = 'updatedAt';

    protected $fillable = [
        'id',
        'businessId',
        'methodCode',
        'name',
        'type',
        'enabled',
        'provider',
    ];

    protected $casts = [
        'businessId' => 'integer',
        'enabled' => 'boolean',
        'createdAt' => 'datetime',
        'updatedAt' => 'datetime',
    ];

    public function business()
    {
        return $this->belongsTo(Business::class, 'businessId');
    }
}
