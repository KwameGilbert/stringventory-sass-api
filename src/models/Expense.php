<?php

declare(strict_types=1);

namespace App\Models;

use App\Traits\Tenantable;
use Illuminate\Database\Eloquent\Model;

/**
 * Expense Model
 * 
 * @property int $id
 * @property int $businessId
 * @property int|null $expenseScheduleId
 * @property int $expenseCategoryId
 * @property float $amount
 * @property \Illuminate\Support\Carbon $transactionDate
 * @property string|null $notes
 * @property string $status
 * @property int|null $paymentMethodId
 * @property \Illuminate\Support\Carbon|null $createdAt
 */
class Expense extends Model
{
    use Tenantable;


    protected $table = 'expenses';
    public $timestamps = false;

    const CREATED_AT = 'createdAt';

    protected $fillable = [
        'businessId',
        'expenseScheduleId',
        'expenseCategoryId',
        'amount',
        'transactionDate',
        'notes',
        'status',
        'createdBy',
        'currency',
        'evidence',
        'reference',
        'paymentMethodId',
    ];

    protected $appends = ['paymentMethod'];

    protected $casts = [
        'businessId' => 'integer',
        'expenseScheduleId' => 'integer',
        'expenseCategoryId' => 'integer',
        'amount' => 'float',
        'transactionDate' => 'datetime',
        'createdAt' => 'datetime',
        'createdBy' => 'integer',
        'currency' => 'string',
        'evidence' => 'string',
        'reference' => 'string',
        'paymentMethodId' => 'integer',
    ];

    public function getPaymentMethodAttribute(): ?string
    {
        if ($this->relationLoaded('paymentMethod') && $this->paymentMethod) {
            return $this->paymentMethod->name;
        }
        if ($this->relationLoaded('transaction')) {
            return $this->transaction?->paymentMethod?->name;
        }
        return null;
    }

    public function getCreatedByAttribute($value): string|int|null
    {
        if ($this->relationLoaded('creator') && $this->creator) {
            return trim($this->creator->firstName . ' ' . $this->creator->lastName);
        }
        return $value;
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'createdBy');
    }

    public function category()
    {
        return $this->belongsTo(ExpenseCategory::class, 'expenseCategoryId');
    }

    public function schedule()
    {
        return $this->belongsTo(ExpenseSchedule::class, 'expenseScheduleId');
    }

    public function transaction()
    {
        return $this->hasOne(Transaction::class, 'expenseId');
    }

    public function transactions()
    {
        return $this->hasMany(Transaction::class, 'expenseId');
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
