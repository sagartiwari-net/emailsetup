<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Website extends Model
{
    protected $fillable = [
        'tenant_id',
        'name',
        'url',
        'domain_id',
        'api_key_id',
        'integration_status',
        'last_send_at',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'last_send_at' => 'datetime',
        ];
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function domain(): BelongsTo
    {
        return $this->belongsTo(Domain::class);
    }

    public function apiKey(): BelongsTo
    {
        return $this->belongsTo(ApiKey::class);
    }

    public function isConnected(): bool
    {
        return $this->integration_status === 'connected';
    }
}
