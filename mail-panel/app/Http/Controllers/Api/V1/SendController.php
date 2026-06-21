<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\MailLog;
use App\Services\MailSendService;
use App\Services\WarmupLimiter;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SendController extends Controller
{
    public function send(Request $request, MailSendService $mailSendService): JsonResponse
    {
        $validated = $request->validate([
            'to' => ['required', 'email'],
            'template' => ['required', 'string', 'max:100'],
            'data' => ['nullable', 'array'],
            'subject' => ['nullable', 'string', 'max:255'],
        ]);

        $apiKey = $request->attributes->get('api_key');

        try {
            $mailLog = $mailSendService->queueSend(
                apiKey: $apiKey,
                to: $validated['to'],
                templateSlug: $validated['template'],
                data: $validated['data'] ?? [],
                subjectOverride: $validated['subject'] ?? null,
            );

            return response()->json([
                'success' => true,
                'message_id' => $mailLog->message_id,
                'status' => $mailLog->status,
            ]);
        } catch (\Throwable $exception) {
            return response()->json([
                'success' => false,
                'message' => $exception->getMessage(),
            ], 422);
        }
    }

    public function status(Request $request, string $messageId): JsonResponse
    {
        $apiKey = $request->attributes->get('api_key');

        $mailLog = MailLog::query()
            ->where('message_id', $messageId)
            ->where('tenant_id', $apiKey->tenant_id)
            ->first();

        if (! $mailLog) {
            return response()->json([
                'success' => false,
                'message' => 'Message not found.',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'message_id' => $mailLog->message_id,
            'status' => $mailLog->status,
            'to' => $mailLog->to_email,
            'subject' => $mailLog->subject,
            'sent_at' => $mailLog->sent_at?->toIso8601String(),
            'error' => $mailLog->error,
        ]);
    }

    public function todayStats(Request $request, WarmupLimiter $warmupLimiter): JsonResponse
    {
        $tenant = $request->attributes->get('tenant');

        return response()->json([
            'success' => true,
            'daily_cap' => $warmupLimiter->dailyCap($tenant),
            'sent_today' => $warmupLimiter->todayCount($tenant),
            'remaining' => $warmupLimiter->remaining($tenant),
        ]);
    }
}
