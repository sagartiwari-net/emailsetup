<?php

namespace App\Services;

use App\Models\Subscriber;
use App\Models\SubscriberList;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Validator;

class SubscriberImportService
{
    public function importFromCsv(SubscriberList $list, UploadedFile $file): array
    {
        $handle = fopen($file->getRealPath(), 'r');
        $added = 0;
        $skipped = 0;
        $invalid = 0;
        $row = 0;

        while (($data = fgetcsv($handle)) !== false) {
            $row++;

            if ($row === 1 && $this->looksLikeHeader($data)) {
                continue;
            }

            $email = strtolower(trim($data[0] ?? ''));
            $name = trim($data[1] ?? '') ?: null;

            if (! Validator::make(['email' => $email], ['email' => 'required|email'])->passes()) {
                $invalid++;

                continue;
            }

            $exists = Subscriber::query()
                ->where('subscriber_list_id', $list->id)
                ->where('email', $email)
                ->exists();

            if ($exists) {
                $skipped++;

                continue;
            }

            Subscriber::create([
                'subscriber_list_id' => $list->id,
                'email' => $email,
                'name' => $name,
                'status' => 'active',
            ]);

            $added++;
        }

        fclose($handle);

        return compact('added', 'skipped', 'invalid');
    }

    public function addManual(SubscriberList $list, string $email, ?string $name = null): Subscriber
    {
        $email = strtolower(trim($email));

        return Subscriber::query()->updateOrCreate(
            [
                'subscriber_list_id' => $list->id,
                'email' => $email,
            ],
            [
                'name' => $name,
                'status' => 'active',
                'unsubscribed_at' => null,
            ]
        );
    }

    private function looksLikeHeader(array $data): bool
    {
        $first = strtolower($data[0] ?? '');

        return str_contains($first, 'email') || str_contains($first, 'mail');
    }
}
