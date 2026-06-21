<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Domain extends Model
{
    protected $fillable = [
        'tenant_id',
        'domain_name',
        'from_email',
        'from_name',
        'dkim_verified',
    ];

    protected function casts(): array
    {
        return [
            'dkim_verified' => 'boolean',
        ];
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function apiKeys(): HasMany
    {
        return $this->hasMany(ApiKey::class);
    }

    public function mailLogs(): HasMany
    {
        return $this->hasMany(MailLog::class);
    }

    public function defaultFromEmail(): string
    {
        return $this->from_email ?? 'noreply@'.$this->domain_name;
    }

    public function defaultFromName(): string
    {
        return $this->from_name ?? $this->domain_name;
    }
}
