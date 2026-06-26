<?php

declare(strict_types=1);

namespace App\Models;

use App\Traits\Tenantable;
use Illuminate\Database\Eloquent\Model;

/**
 * Transaction Model
 * 
 * @property int $id
 * @property int $businessId
 * @property int|null $orderId
 * @property int|null $expenseId
 * @property int|null $purchaseId
 * @property int|null $adjustmentId
 * @property int|null $refundId
 * @property string $transactionType
 * @property int|null $paymentMethodId
 * @property float|null $amount
 * @property string $status
 * @property \Illuminate\Support\Carbon|null $createdAt
 */
class Transaction extends Model
{
    use Tenantable;

    protected $table = 'transactions';
    public $timestamps = false;

    const CREATED_AT = 'createdAt';

    // Transaction Types
    const TYPE_ORDER = 'order';
    const TYPE_PURCHASE = 'purchase';
    const TYPE_EXPENSE = 'expense';
    const TYPE_ADJUSTMENT = 'adjustment';
    const TYPE_REFUNDS = 'refunds';
    const TYPE_STOCK_LOSS = 'stock_loss';

    // Statuses
    const STATUS_PENDING = 'pending';
    const STATUS_COMPLETED = 'completed';
    const STATUS_CANCELLED = 'cancelled';
    const STATUS_FAILED = 'failed';

    protected $fillable = [
        'businessId',
        'orderId',
        'expenseId',
        'purchaseId',
        'adjustmentId',
        'refundId',
        'transactionType',
        'paymentMethodId',
        'amount',
        'status',
        'currency',
    ];

    protected $casts = [
        'businessId' => 'integer',
        'orderId' => 'integer',
        'expenseId' => 'integer',
        'purchaseId' => 'integer',
        'adjustmentId' => 'integer',
        'refundId' => 'integer',
        'paymentMethodId' => 'integer',
        'amount' => 'float',
        'createdAt' => 'datetime',
        'currency' => 'string',
    ];

    public function order()
    {
        return $this->belongsTo(Order::class, 'orderId');
    }

    public function expense()
    {
        return $this->belongsTo(Expense::class, 'expenseId');
    }

    public function refund()
    {
        return $this->belongsTo(Refund::class, 'refundId');
    }

    public function purchase()
    {
        return $this->belongsTo(Purchase::class, 'purchaseId');
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
