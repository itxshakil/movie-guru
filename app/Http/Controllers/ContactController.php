<?php

namespace App\Http\Controllers;

use App\Models\Contact;
use App\Rules\BlockTemporaryEmailRule;
use App\Rules\SpamKeywordRule;
use Illuminate\Http\Request;

class ContactController extends Controller
{
    public function store(Request $request)
    {
        if ($request->filled('website')) {
            logger()->warning('bot detected', [
                'website' => $request->website,
                'ip' => $request->ip(),
            ]);

            return back()->with('success', 'Thanks for your message. We\'ll be in touch.');
        }

        $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'email' => ['required', 'email', 'max:100', new BlockTemporaryEmailRule],
            'message' => ['required', 'string', 'max:255', new SpamKeywordRule],
        ]);

        Contact::create($request->only('email', 'name', 'message'));

        return back()->with('success', 'Thanks for your message. We\'ll be in touch.');
    }
}
