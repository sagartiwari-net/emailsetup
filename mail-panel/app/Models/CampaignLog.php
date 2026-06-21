<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CampaignLog extends Model
{
    protected $fillable = [
        'campaign_id',
        'subscriber_id',
        'mail_log_id',
        'run_number',
        'status',
        'error',
        'sent_at',
    ];

    protected function casts(): array
    {
        return [
            'run_number' => 'integer',
            'sent_at' => 'datetime',
        ];
    }

    public function campaign(): BelongsTo
    {
        return $this->belongsTo(Campaign::class);
    }

    public function subscriber(): BelongsTo
    {
        return $this->belongsTo(Subscriber::class);
    }

    public function mailLog(): BelongsTo
    {
        return $this->belongsTo(MailLog::class);
    }
}
