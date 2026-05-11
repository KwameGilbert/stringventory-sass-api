<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Subscription Model
 * 
 * @property int $id
 * @property int $businessId
 * @property int $planId
 * @property string $billingCycle
 * @property float $mrr
 * @property string $status
 * @property \Illuminate\Support\Carbon|null $trialEndsAt
 * @property \Illuminate\Support\Carbon|null $currentPeriodStart
 * @property \Illuminate\Support\Carbon|null $currentPeriodEnd
 * @property bool $cancelAtPeriodEnd
 * @property string|null $gatewayCustomerId
 * @property string|null $gatewaySubscriptionId
 * @property string|null $paymentMethodBrand
 * @property string|null $paymentMethodLast4
 * @property \Illuminate\Support\Carbon|null $createdAt
 * @property \Illuminate\Support\Carbon|null $updatedAt
 */
class Subscription extends Model
{
    protected $table = 'subscriptions';
    public $timestamps = true;

    const CREATED_AT = 'createdAt';
    const UPDATED_AT = 'updatedAt';

    protected $fillable = [
        'businessId',
        'planId',
        'billingCycle',
        'mrr',
        'status',
        'trialEndsAt',
        'currentPeriodStart',
        'currentPeriodEnd',
        'cancelAtPeriodEnd',
        'gatewayCustomerId',
        'gatewaySubscriptionId',
        'paymentMethodBrand',
        'paymentMethodLast4'
    ];

    protected $casts = [
        'businessId' => 'integer',
        'planId' => 'integer',
        'mrr' => 'float',
        'cancelAtPeriodEnd' => 'boolean',
        'trialEndsAt' => 'datetime',
        'currentPeriodStart' => 'datetime',
        'currentPeriodEnd' => 'datetime',
        'createdAt' => 'datetime',
        'updatedAt' => 'datetime',
    ];

    public function business()
    {
        return $this->belongsTo(Business::class, 'businessId');
    }

    public function plan()
    {
        return $this->belongsTo(Plan::class, 'planId');
    }
}
