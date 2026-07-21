<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use RuntimeException;
use Stancl\Tenancy\Database\Models\Domain as BaseDomain;

class Domain extends BaseDomain
{
    protected function frontendDomain(): Attribute
    {
        return Attribute::get(
            fn (): string => self::toFrontendDomain($this->domain, self::frontendPort())
        );
    }

    public static function buildApiDomain(string $tenantId): string
    {
        $host = parse_url((string) config('app.url'), PHP_URL_HOST);

        if (!is_string($host) || $host === '') {
            throw new RuntimeException('APP_URL não está configurada corretamente.');
        }

        return strtolower($tenantId.'.'.$host);
    }

    public static function toFrontendDomain(string $domain, int|string|null $port = null): string
    {
        $domain = strtolower($domain);

        if (str_starts_with($domain, 'api.')) {
            $domain = substr($domain, 4);
        } else {
            $domain = str_replace('.api.', '.', $domain);
        }

        if ($port === null || $port === '') {
            return $domain;
        }

        return "$domain:$port";
    }

    private static function frontendPort(): string|null
    {
        $configuredPort = parse_url((string) config('app.url'), PHP_URL_PORT);

        if (is_int($configuredPort)) {
            return (string)$configuredPort;
        }

        if (!app()->bound('request')) {
            return null;
        }

        $requestPort = request()->getPort();

        if (in_array($requestPort, [80, 443], true)) {
            return null;
        }

        return (string)$requestPort;
    }
}
