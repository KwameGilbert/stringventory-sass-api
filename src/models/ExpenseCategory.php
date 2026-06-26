<?php

declare(strict_types=1);

namespace App\Models;

use App\Traits\Tenantable;
use Illuminate\Database\Eloquent\Model;

/**
 * ExpenseCategory Model
 * 
 * @property int $id
 * @property int $businessId
 * @property string $name
 * @property string|null $description
 * @property string $status
 * @property \Illuminate\Support\Carbon|null $createdAt
 * @property \Illuminate\Support\Carbon|null $updatedAt
 */
class ExpenseCategory extends Model
{
    use Tenantable;

    protected $table = 'expenseCategories';
    public $timestamps = false;

    const CREATED_AT = 'createdAt';
    const UPDATED_AT = 'updatedAt';

    protected $fillable = [
        'businessId',
        'name',
        'description',
        'status',
    ];

    protected $casts = [
        'businessId' => 'integer',
        'createdAt' => 'datetime',
        'updatedAt' => 'datetime',
    ];

    public function schedules()
    {
        return $this->hasMany(ExpenseSchedule::class, 'expenseCategoryId');
    }

    public function expenses()
    {
        return $this->hasMany(Expense::class, 'expenseCategoryId');
    }

    public function business()
    {
        return $this->belongsTo(Business::class, 'businessId');
    }
}
