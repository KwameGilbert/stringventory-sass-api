<?php

declare(strict_types=1);

namespace App\Models;

use App\Traits\Tenantable;
use Illuminate\Database\Eloquent\Model;

/**
 * Category Model
 * 
 * @property int $id
 * @property int $businessId
 * @property string $name
 * @property string|null $image
 * @property string|null $description
 * @property string $status
 * @property \Illuminate\Support\Carbon|null $createdAt
 * @property \Illuminate\Support\Carbon|null $updatedAt
 */
class Category extends Model
{
    use Tenantable;

    protected $table = 'categories';
    public $timestamps = true;
    const CREATED_AT = 'createdAt';
    const UPDATED_AT = 'updatedAt';

    protected $fillable = [
        'businessId',
        'name',
        'image',
        'description',
        'status',
    ];

    protected $casts = [
        'businessId' => 'integer',
        'createdAt' => 'datetime',
    ];

    public function products()
    {
        return $this->hasMany(Product::class, 'categoryId');
    }

    public function business()
    {
        return $this->belongsTo(Business::class, 'businessId');
    }
}
