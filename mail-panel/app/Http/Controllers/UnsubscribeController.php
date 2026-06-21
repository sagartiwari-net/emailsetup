<?php

namespace App\Http\Controllers;

use App\Models\Subscriber;
use Illuminate\View\View;

class UnsubscribeController extends Controller
{
    public function __invoke(string $token): View
    {
        $subscriber = Subscriber::query()->where('unsubscribe_token', $token)->first();

        if (! $subscriber) {
            return view('unsubscribe', ['success' => false, 'message' => 'Invalid unsubscribe link.']);
        }

        if ($subscriber->status !== 'unsubscribed') {
            $subscriber->markUnsubscribed();
        }

        return view('unsubscribe', [
            'success' => true,
            'message' => 'You have been unsubscribed successfully.',
            'email' => $subscriber->email,
        ]);
    }
}
