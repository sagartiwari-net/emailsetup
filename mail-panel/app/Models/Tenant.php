<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Tenant extends Model
{
    protected $fillable = [
        'name',
        'daily_limit',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'daily_limit' => 'integer',
        ];
    }

    public function domains(): HasMany
    {
        return $this->hasMany(Domain::class);
    }

    public function apiKeys(): HasMany
    {
        return $this->hasMany(ApiKey::class);
    }

    public function mailLogs(): HasMany
    {
        return $this->hasMany(MailLog::class);
    }

    public function templates(): HasMany
    {
        return $this->hasMany(Template::class);
    }

    public function dailySendCounts(): HasMany
    {
        return $this->hasMany(DailySendCount::class);
    }

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    public function campaigns(): HasMany
    {
        return $this->hasMany(Campaign::class);
    }

    public function subscriberLists(): HasMany
    {
        return $this->hasMany(SubscriberList::class);
    }

    public function websites(): HasMany
    {
        return $this->hasMany(Website::class);
    }

    public function isActive(): bool
    {
        return $this->status === 'active';
    }
}
