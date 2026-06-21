<?php

namespace App\Jobs;

use App\Models\Domain;
use App\Models\MailLog;
use App\Services\MailSendService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class SendMailJob implements ShouldQueue
{
    use Queueable;

    public int $tries = 3;

    public function __construct(
        public int $mailLogId,
        public string $htmlBody,
        public string $textBody,
    ) {}

    public function handle(MailSendService $mailSendService): void
    {
        $mailLog = MailLog::query()->with(['domain', 'tenant'])->find($this->mailLogId);

        if (! $mailLog || $mailLog->status === 'sent') {
            return;
        }

        $domain = $mailLog->domain ?? Domain::find($mailLog->domain_id);

        if (! $domain) {
            $mailLog->update([
                'status' => 'failed',
                'error' => 'Domain not found.',
            ]);

            return;
        }

        $mailSendService->sendNow($mailLog, $domain, $this->htmlBody, $this->textBody);
    }
}
