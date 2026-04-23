<?php

namespace App\Webhooks;

class UrlPolicy
{
    private array $resolvedIps = [];

    /**
     * Check whether a webhook URL is safe to dispatch to.
     *
     * Returns an error message string when the URL is blocked, or null when it is safe.
     */
    public function check(string $url): ?string
    {
        $parsed = parse_url($url);

        if (! $parsed || ! isset($parsed['host'])) {
            return 'Invalid URL';
        }

        $scheme = strtolower($parsed['scheme'] ?? '');

        if (! in_array($scheme, ['http', 'https'])) {
            return 'Scheme must be http or https';
        }

        if (app()->environment('production') && $scheme !== 'https') {
            return 'HTTPS required in production';
        }

        $host = $parsed['host'];
        $port = $parsed['port'] ?? ($scheme === 'https' ? 443 : 80);

        $blockedPorts = [22, 25, 3306, 5432, 6379, 9200, 11211];

        if (! app()->environment(['local', 'testing']) && in_array($port, $blockedPorts)) {
            return "Port {$port} is not allowed";
        }

        $ips = gethostbynamel($host);

        if (empty($ips)) {
            return 'Hostname does not resolve';
        }

        $this->resolvedIps = $ips;

        foreach ($ips as $ip) {
            if ($error = $this->checkIp($ip)) {
                return $error;
            }
        }

        return null;
    }

    /**
     * Return the IPs resolved during the last successful check() call.
     *
     * @return list<string>
     */
    public function resolvedIps(): array
    {
        return $this->resolvedIps;
    }

    /**
     * Check whether a single IPv4 address falls in a blocked range.
     */
    private function checkIp(string $ip): ?string
    {
        $ranges = [
            '127.0.0.0/8',
            '10.0.0.0/8',
            '172.16.0.0/12',
            '192.168.0.0/16',
            '169.254.0.0/16',
            '100.64.0.0/10',
            '0.0.0.0/8',
        ];

        foreach ($ranges as $range) {
            if ($this->ipInRange($ip, $range)) {
                return "IP {$ip} is in a blocked range ({$range})";
            }
        }

        if (
            in_array($ip, ['::1'])
            || str_starts_with($ip, 'fc')
            || str_starts_with($ip, 'fd')
            || str_starts_with($ip, 'fe80')
        ) {
            return "IPv6 address {$ip} is blocked";
        }

        return null;
    }

    /**
     * Test whether an IPv4 address falls within a CIDR range.
     */
    private function ipInRange(string $ip, string $range): bool
    {
        [$network, $bits] = explode('/', $range);

        $ip32 = ip2long($ip);
        $net32 = ip2long($network);

        if ($ip32 === false || $net32 === false) {
            return false;
        }

        $mask = -1 << (32 - (int) $bits);

        return ($ip32 & $mask) === ($net32 & $mask);
    }
}
