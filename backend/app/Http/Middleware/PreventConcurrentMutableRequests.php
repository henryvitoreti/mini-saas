<?php

namespace App\Http\Middleware;

use App\Traits\ApiResponseTrait;
use Closure;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Symfony\Component\HttpFoundation\Response;

class PreventConcurrentMutableRequests
{
    use ApiResponseTrait;

    private const int DECAY_SECONDS = 2;

    private const int MAX_ATTEMPTS = 1;

    /**
     * @param  Closure(Request): Response  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (!$this->isMutableRequest($request)) {
            return $next($request);
        }

        $key = $this->getRateLimitKey($request);

        if (RateLimiter::tooManyAttempts($key, self::MAX_ATTEMPTS)) {
            return $this->tooManyRequestsResponse();
        }

        RateLimiter::hit($key, self::DECAY_SECONDS);

        return $next($request);
    }

    private function isMutableRequest(Request $request): bool
    {
        return in_array($request->method(), ['POST', 'PUT', 'PATCH', 'DELETE'], true);
    }

    private function getRateLimitKey(Request $request): string
    {
        return implode(':', [
            'mutable-request-rate-limit',
            tenant('id') ?? 'central',
            $this->getRequesterKey($request),
        ]);
    }

    private function getRequesterKey(Request $request): string
    {
        $userId = $request->user()?->getAuthIdentifier();

        if ($userId !== null) {
            return 'user-'.$userId;
        }

        $bearerToken = $request->bearerToken();

        if ($bearerToken !== null) {
            return 'token-'.sha1($bearerToken);
        }

        return 'ip-'.sha1((string) $request->ip());
    }

    private function tooManyRequestsResponse(): JsonResponse
    {
        return $this->errorResponse(
            message: 'Múltiplas requisições detectadas. Aguarde alguns instantes.',
            code: Response::HTTP_TOO_MANY_REQUESTS
        );
    }
}
