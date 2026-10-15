<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\ProtocolLinkRequest;
use App\Services\ProtocolLinkResolver;
use Illuminate\Http\RedirectResponse;

/**
 * Handles web+movieguru:// links registered through the manifest's protocol_handlers.
 */
final class ProtocolLinkController extends Controller
{
    public function show(ProtocolLinkRequest $request, ProtocolLinkResolver $protocolLinkResolver): RedirectResponse
    {
        /** @var array{url: string} $validated */
        $validated = $request->validated();

        return redirect()->to($protocolLinkResolver->resolve($validated['url']));
    }
}
