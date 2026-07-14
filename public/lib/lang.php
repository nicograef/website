<?php

/**
 * Resolve the page language from the Accept-Language header.
 *
 * Any German entry wins, then any English entry; everything else
 * (no header, other languages) defaults to German. Entries rejected
 * via q=0 (RFC 9110: "not acceptable") are ignored.
 */
function detectLang(): string
{
    $header = $_SERVER['HTTP_ACCEPT_LANGUAGE'] ?? '';
    $header = preg_replace('/[^,]*;\s*q=0(?:\.0{1,3})?\s*(?=,|$)/i', '', $header);
    if (preg_match('/(^|,)\s*de\b/i', $header)) {
        return 'de';
    }
    if (preg_match('/(^|,)\s*en\b/i', $header)) {
        return 'en';
    }
    return 'de';
}
