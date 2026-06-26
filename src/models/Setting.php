<?php

declare(strict_types=1);

namespace App\Models;

use App\Traits\Tenantable;
use Illuminate\Database\Eloquent\Model;

/**
 * Setting Model
 * 
 * @property int $id
 * @property int $businessId
 * @property string $category
 * @property array $data
 * @property \Illuminate\Support\Carbon|null $updatedAt
 */
class Setting extends Model
{
    use Tenantable;

    protected $table = 'settings';
    public $timestamps = false; // Using updatedAt only via DB

    protected $fillable = [
        'businessId',
        'category',
        'data',
    ];

    protected $casts = [
        'businessId' => 'integer',
        'data' => 'array',
        'updatedAt' => 'datetime',
    ];

    /**
     * Get settings by category
     */
    public static function getByCategory(int $businessId, string $category): ?array
    {
        $setting = self::where('businessId', $businessId)->where('category', $category)->first();
        return $setting ? $setting->data : null;
    }

    /**
     * Update settings for a category
     */
    public static function updateCategory(int $businessId, string $category, array $data): bool
    {
        $setting = self::where('businessId', $businessId)->where('category', $category)->first();
        if ($setting) {
            return $setting->update(['data' => $data]);
        }
        
        return (bool) self::create([
            'businessId' => $businessId,
            'category' => $category,
            'data' => $data
        ]);
    }

    public function business()
    {
        return $this->belongsTo(Business::class, 'businessId');
    }
}

