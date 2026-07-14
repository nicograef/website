<?php

/**
 * Resolve the page language from the Accept-Language header.
 *
 * Any German entry wins, then any English entry; everything else
 * (no header, other languages) defaults to German.
 */
function detectLang(): string
{
    $header = $_SERVER['HTTP_ACCEPT_LANGUAGE'] ?? '';
    if (preg_match('/(^|,)\s*de\b/i', $header)) {
        return 'de';
    }
    if (preg_match('/(^|,)\s*en\b/i', $header)) {
        return 'en';
    }
    return 'de';
}
