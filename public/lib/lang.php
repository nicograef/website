<?php

/**
 * Resolve the page language from the Accept-Language header.
 *
 * German is the canonical default: a German entry, or a request with no
 * language signal at all (crawlers, direct hits), yields German. Any other
 * stated language (English, French, Spanish, …) yields English, so
 * non-German visitors read the bilingual pages in English rather than German.
 * Entries rejected via q=0 (RFC 9110: "not acceptable") are ignored.
 */
function detectLang(): string
{
    $header = $_SERVER['HTTP_ACCEPT_LANGUAGE'] ?? '';
    $header = preg_replace('/[^,]*;\s*q=0(?:\.0{1,3})?\s*(?=,|$)/i', '', $header);
    if (trim($header) === '' || preg_match('/(^|,)\s*de\b/i', $header)) {
        return 'de';
    }
    return 'en';
}
