<?php

namespace App\Services;

use App\Jobs\SendMailJob;
use App\Models\ApiKey;
use App\Models\Domain;
use App\Models\MailLog;
use App\Models\Subscriber;
use App\Models\Template;
use App\Models\Tenant;
use App\Models\Website;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Mail;
use RuntimeException;

class MailSendService
{
    public function __construct(
        private readonly TemplateRenderer $templateRenderer,
        private readonly WarmupLimiter $warmupLimiter,
    ) {}

    public function queueSend(
        ApiKey $apiKey,
        string $to,
        string $templateSlug,
        array $data = [],
        ?string $subjectOverride = null,
        bool $useQueue = true,
    ): MailLog {
        $tenant = $apiKey->tenant;
        $domain = $apiKey->domain;

        if (! $this->warmupLimiter->canSend($tenant)) {
            throw new RuntimeException('Daily send cap reached. Try again tomorrow.');
        }

        $template = $this->resolveTemplate($tenant, $templateSlug);
        $subject = $subjectOverride ?: $this->templateRenderer->render($template->subject, $data);
        $htmlBody = $this->templateRenderer->render($template->html_body, $data);
        $textBody = $template->text_body
            ? $this->templateRenderer->render($template->text_body, $data)
            : strip_tags($htmlBody);

        $mailLog = MailLog::create([
            'message_id' => MailLog::generateMessageId(),
            'tenant_id' => $tenant->id,
            'domain_id' => $domain->id,
            'api_key_id' => $apiKey->id,
            'to_email' => $to,
            'subject' => $subject,
            'template_slug' => $templateSlug,
            'status' => 'queued',
        ]);

        if ($useQueue && config('queue.default') !== 'sync') {
            SendMailJob::dispatch($mailLog->id, $htmlBody, $textBody);

            return $mailLog;
        }

        $this->sendNow($mailLog, $domain, $htmlBody, $textBody);

        return $mailLog->fresh();
    }

    public function sendNow(MailLog $mailLog, Domain $domain, string $htmlBody, string $textBody): void
    {
        if ($mailLog->status === 'sent') {
            return;
        }

        try {
            $this->configureMailer($domain);

            Mail::html($htmlBody, function ($message) use ($mailLog, $domain, $textBody) {
                $message->to($mailLog->to_email)
                    ->subject($mailLog->subject)
                    ->from($domain->defaultFromEmail(), $domain->defaultFromName())
                    ->text($textBody);
            });

            $mailLog->update([
                'status' => 'sent',
                'sent_at' => now(),
                'error' => null,
            ]);

            $this->warmupLimiter->increment($mailLog->tenant);

            Website::query()
                ->where('api_key_id', $mailLog->api_key_id)
                ->update(['last_send_at' => now()]);
        } catch (\Throwable $exception) {
            $mailLog->update([
                'status' => 'failed',
                'error' => $exception->getMessage(),
            ]);

            if (str_contains(strtolower($exception->getMessage()), 'bounce')
                || str_contains(strtolower($exception->getMessage()), 'invalid')) {
                Subscriber::query()
                    ->where('email', $mailLog->to_email)
                    ->where('status', 'active')
                    ->update(['status' => 'bounced']);
            }

            throw $exception;
        }
    }

    private function resolveTemplate(Tenant $tenant, string $slug): Template
    {
        $template = Template::query()
            ->where('slug', $slug)
            ->where(function ($query) use ($tenant) {
                $query->whereNull('tenant_id')
                    ->orWhere('tenant_id', $tenant->id);
            })
            ->orderByRaw('tenant_id IS NULL ASC')
            ->first();

        if (! $template) {
            throw new RuntimeException("Template '{$slug}' not found.");
        }

        return $template;
    }

    private function configureMailer(Domain $domain): void
    {
        Config::set('mail.from.address', $domain->defaultFromEmail());
        Config::set('mail.from.name', $domain->defaultFromName());

        if (env('MAIL_MAILER', 'log') === 'log') {
            Config::set('mail.default', 'log');

            return;
        }

        Config::set('mail.default', 'smtp');
    }
}
