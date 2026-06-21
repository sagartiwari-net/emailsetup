<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Subscriber extends Model
{
    protected $fillable = [
        'subscriber_list_id',
        'email',
        'name',
        'status',
        'unsubscribe_token',
        'unsubscribed_at',
    ];

    protected function casts(): array
    {
        return [
            'unsubscribed_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (Subscriber $subscriber) {
            if (! $subscriber->unsubscribe_token) {
                $subscriber->unsubscribe_token = Str::random(48);
            }
        });
    }

    public function list(): BelongsTo
    {
        return $this->belongsTo(SubscriberList::class, 'subscriber_list_id');
    }

    public function campaignLogs(): HasMany
    {
        return $this->hasMany(CampaignLog::class);
    }

    public function unsubscribeUrl(): string
    {
        return url('/unsubscribe/'.$this->unsubscribe_token);
    }

    public function markUnsubscribed(): void
    {
        $this->update([
            'status' => 'unsubscribed',
            'unsubscribed_at' => now(),
        ]);
    }

    public function markBounced(): void
    {
        $this->update(['status' => 'bounced']);
    }
}
