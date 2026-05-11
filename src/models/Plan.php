<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Plan Model
 * 
 * @property int $id
 * @property string $name
 * @property string $description
 * @property string $themeColor
 * @property bool $isPopular
 * @property float $monthlyPrice
 * @property float $yearlyPrice
 * @property int $trialDays
 * @property int $maxUsers
 * @property int $maxProducts
 * @property int $maxOrdersPerMonth
 * @property int $maxCategories
 * @property int $maxSuppliers
 * @property int $maxCustomers
 * @property int $maxLocations
 * @property int $maxStorageMb
 * @property array|null $marketingFeatures
 * @property array|null $systemCapabilities
 * @property string $status
 * @property \Illuminate\Support\Carbon|null $createdAt
 * @property \Illuminate\Support\Carbon|null $updatedAt
 */
class Plan extends Model
{
    protected $table = 'plans';
    public $timestamps = true;

    const CREATED_AT = 'createdAt';
    const UPDATED_AT = 'updatedAt';

    protected $fillable = [
        'name',
        'description',
        'themeColor',
        'isPopular',
        'monthlyPrice',
        'yearlyPrice',
        'trialDays',
        'maxUsers',
        'maxProducts',
        'maxOrdersPerMonth',
        'maxCategories',
        'maxSuppliers',
        'maxCustomers',
        'maxLocations',
        'maxStorageMb',
        'marketingFeatures',
        'systemCapabilities',
        'status',
    ];

    protected $casts = [
        'isPopular' => 'boolean',
        'monthlyPrice' => 'float',
        'yearlyPrice' => 'float',
        'trialDays' => 'integer',
        'maxUsers' => 'integer',
        'maxProducts' => 'integer',
        'maxOrdersPerMonth' => 'integer',
        'maxCategories' => 'integer',
        'maxSuppliers' => 'integer',
        'maxCustomers' => 'integer',
        'maxLocations' => 'integer',
        'maxStorageMb' => 'integer',
        'marketingFeatures' => 'array',
        'systemCapabilities' => 'array',
        'createdAt' => 'datetime',
        'updatedAt' => 'datetime',
    ];

    public function subscriptions()
    {
        return $this->hasMany(Subscription::class, 'planId');
    }
}
