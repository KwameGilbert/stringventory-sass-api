<?php

declare(strict_types=1);

namespace App\Models;

use App\Traits\Tenantable;
use Illuminate\Database\Eloquent\Model;

/**
 * ExpenseSchedule Model
 * 
 * @property int $id
 * @property int $businessId
 * @property int $expenseCategoryId
 * @property float $amount
 * @property string|null $description
 * @property string $frequency
 * @property \Illuminate\Support\Carbon $startDate
 * @property \Illuminate\Support\Carbon|null $nextDueDate
 * @property \Illuminate\Support\Carbon|null $endDate
 * @property bool $isActive
 * @property int|null $paymentMethodId
 * @property \Illuminate\Support\Carbon|null $createdAt
 */
class ExpenseSchedule extends Model
{
    use Tenantable;

    protected $table = 'expenseSchedules';
    public $timestamps = false;

    const CREATED_AT = 'createdAt';

    protected $fillable = [
        'businessId',
        'expenseCategoryId',
        'amount',
        'description',
        'frequency',
        'startDate',
        'nextDueDate',
        'endDate',
        'isActive',
        'paymentMethodId',
    ];

    protected $casts = [
        'businessId' => 'integer',
        'expenseCategoryId' => 'integer',
        'amount' => 'float',
        'startDate' => 'date',
        'nextDueDate' => 'date',
        'endDate' => 'date',
        'isActive' => 'boolean',
        'paymentMethodId' => 'integer',
        'createdAt' => 'datetime',
    ];

    public function category()
    {
        return $this->belongsTo(ExpenseCategory::class, 'expenseCategoryId');
    }

    public function expenses()
    {
        return $this->hasMany(Expense::class, 'expenseScheduleId');
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