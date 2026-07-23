<?php

namespace App\Http\Middleware;

use Illuminate\Auth\AuthenticationException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class OverlaySecretMiddleware {
    /**
     * Handle an incoming request.
     *
     * @param Request $request
     * @param Closure(Request): (Response|RedirectResponse) $next
     * @return Response|RedirectResponse|JsonResponse
     *
     * @throws AuthenticationException
     */
    public function handle(Request $request, \Closure $next) {
        $secret = (string) config('service.overlay_secret');

        if ($secret !== '' && hash_equals($secret, (string) $request->header('X-Overlay-Secret'))) {
            return $next($request);
        }

        throw new AuthenticationException();
    }
}
