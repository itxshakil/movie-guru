<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\NewsletterSubscription;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

final class NewsletterSubscriptionController extends Controller
{
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'email' => ['required', 'email', 'max:191'],
        ]);

        NewsletterSubscription::withTrashed()->firstOrCreate($request->only(['email']))->restore();

        return back()->with('success', 'You have successfully subscribed.');
    }

    public function unsubscribe(Request $request): RedirectResponse
    {
        $request->validate([
            'email' => ['required', 'email', 'max:191', 'exists:newsletter_subscriptions'],
        ]);

        NewsletterSubscription::firstWhere('email', $request->input(['email']))->delete();

        return back()->with('success', 'You have successfully unsubscribed.');
    }
}
