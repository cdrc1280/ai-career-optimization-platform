<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Plan extends Model
{
    protected $fillable = ['slug', 'name', 'price_monthly_cents', 'limits', 'features', 'is_active'];

    protected function casts(): array
    {
        return ['limits' => 'array', 'features' => 'array', 'is_active' => 'boolean'];
    }

    public function subscriptions(): HasMany
    {
        return $this->hasMany(Subscription::class);
    }

    public function limitFor(string $metric): ?int
    {
        // null = unlimited
        return $this->limits[$metric] ?? 0;
    }
}
