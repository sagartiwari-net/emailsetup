<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Campaign extends Model
{
    protected $fillable = [
        'tenant_id',
        'subscriber_list_id',
        'api_key_id',
        'name',
        'template_slug',
        'subject',
        'schedule_type',
        'schedule_config',
        'status',
        'run_number',
        'sent_count',
        'failed_count',
        'skipped_count',
        'next_run_at',
        'last_run_at',
        'last_batch_at',
        'started_at',
        'completed_at',
    ];

    protected function casts(): array
    {
        return [
            'schedule_config' => 'array',
            'run_number' => 'integer',
            'sent_count' => 'integer',
            'failed_count' => 'integer',
            'skipped_count' => 'integer',
            'next_run_at' => 'datetime',
            'last_run_at' => 'datetime',
            'last_batch_at' => 'datetime',
            'started_at' => 'datetime',
            'completed_at' => 'datetime',
        ];
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function subscriberList(): BelongsTo
    {
        return $this->belongsTo(SubscriberList::class);
    }

    public function apiKey(): BelongsTo
    {
        return $this->belongsTo(ApiKey::class);
    }

    public function logs(): HasMany
    {
        return $this->hasMany(CampaignLog::class);
    }

    public function isRunnable(): bool
    {
        return in_array($this->status, ['running', 'scheduled'], true);
    }

    public function canProcessBatch(): bool
    {
        if ($this->status === 'paused' || $this->status === 'cancelled') {
            return false;
        }

        if ($this->last_batch_at && $this->last_batch_at->gt(now()->subMinutes(5))) {
            return false;
        }

        return true;
    }
}
