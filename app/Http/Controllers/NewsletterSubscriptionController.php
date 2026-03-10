<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Mail\WelcomeSubscriberMail;
use App\Models\NewsletterSubscription;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

final class NewsletterSubscriptionController extends Controller
{
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'email' => ['required', 'email', 'max:191'],
            'first_name' => ['nullable', 'string', 'max:50'],
        ]);

        $isNew = !NewsletterSubscription::withTrashed()->where('email', $validated['email'])->exists();

        $subscription = NewsletterSubscription::withTrashed()->firstOrCreate(
            ['email' => $validated['email']],
            ['first_name' => $validated['first_name'] ?? null],
        );
        $subscription->restore();

        if ($isNew) {
            $firstName = $subscription->first_name ?? 'Movie Fan';
            Mail::to($subscription->email)->queue(new WelcomeSubscriberMail($firstName, $subscription->email));
        }

        return back()->with('success', 'You have successfully subscribed.');
    }

    public function unsubscribe(Request $request): RedirectResponse
    {
        $request->validate([
            'email' => ['required', 'email', 'max:191'],
        ]);

        $subscription = NewsletterSubscription::where('email', $request->input('email'))->first();

        if ($subscription) {
            $subscription->delete();
        }

        return redirect('/')->with('success', 'You have successfully unsubscribed.');
    }
}
