<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Business Model
 * 
 * @property int $id
 * @property string $name
 * @property string $email
 * @property string|null $domain
 * @property string $status
 * @property float $usedStorageMb
 * @property \Illuminate\Support\Carbon|null $createdAt
 * @property \Illuminate\Support\Carbon|null $updatedAt
 */
class Business extends Model
{
    protected $table = 'businesses';
    public $timestamps = true;

    const CREATED_AT = 'createdAt';
    const UPDATED_AT = 'updatedAt';

    protected $fillable = [
        'name',
        'email',
        'domain',
        'status',
        'usedStorageMb',
    ];

    protected $casts = [
        'usedStorageMb' => 'float',
        'createdAt' => 'datetime',
        'updatedAt' => 'datetime',
    ];

    public function users()
    {
        return $this->hasMany(User::class, 'businessId');
    }

    public function subscription()
    {
        return $this->hasOne(Subscription::class, 'businessId');
    }

    public function settings()
    {
        return $this->hasMany(Setting::class, 'businessId');
    }
}
