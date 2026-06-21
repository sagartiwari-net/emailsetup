<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class ApiKey extends Model
{
    protected $fillable = [
        'tenant_id',
        'domain_id',
        'label',
        'key_prefix',
        'key_hash',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
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

    public function mailLogs(): HasMany
    {
        return $this->hasMany(MailLog::class);
    }

    public static function generate(string $label, Tenant $tenant, Domain $domain): array
    {
        $plainKey = 'mk_'.Str::random(40);
        $prefix = substr($plainKey, 0, 12);

        $apiKey = static::create([
            'tenant_id' => $tenant->id,
            'domain_id' => $domain->id,
            'label' => $label,
            'key_prefix' => $prefix,
            'key_hash' => hash('sha256', $plainKey),
            'is_active' => true,
        ]);

        return ['model' => $apiKey, 'plain_key' => $plainKey];
    }

    public static function findByPlainKey(string $plainKey): ?self
    {
        $prefix = substr($plainKey, 0, 12);

        $apiKey = static::query()
            ->where('key_prefix', $prefix)
            ->where('is_active', true)
            ->first();

        if (! $apiKey || ! hash_equals($apiKey->key_hash, hash('sha256', $plainKey))) {
            return null;
        }

        return $apiKey->load(['tenant', 'domain']);
    }
}
