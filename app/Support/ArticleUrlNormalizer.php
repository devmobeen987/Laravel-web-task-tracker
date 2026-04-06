<?php

namespace App\Support;

class ArticleUrlNormalizer
{
    /**
     * Stable key for "same article" when only path date segments (year/month/day) change.
     */
    public static function key(string $url): string
    {
        $trimmed = trim($url);
        $parsed = parse_url($trimmed);
        if ($parsed === false || ! isset($parsed['host'])) {
            return hash('sha256', strtolower($trimmed));
        }

        $host = strtolower($parsed['host']);
        $path = $parsed['path'] ?? '';
        $path = trim($path, '/');
        $segments = $path === '' ? [] : explode('/', $path);

        $kept = [];
        foreach ($segments as $segment) {
            if ($segment === '') {
                continue;
            }
            if (preg_match('/^\d{4}$/', $segment)) {
                continue;
            }
            if (preg_match('/^\d{1,2}$/', $segment)) {
                continue;
            }
            $kept[] = $segment;
        }

        return $host.'/'.implode('/', $kept);
    }
}
