<?php

/**
 * Language detection based on browser Accept-Language header.
 * Returns 'de' if the user's preferred language is German, otherwise 'en'.
 */
function detectLang(): string
{
    $header = $_SERVER['HTTP_ACCEPT_LANGUAGE'] ?? '';
    if ($header === '') {
        return 'en';
    }

    $preferences = [];
    foreach (explode(',', $header) as $part) {
        $part = trim($part);
        if ($part === '') {
            continue;
        }

        $lang = $part;
        $q = 1.0;

        if (strpos($part, ';') !== false) {
            [$langPart, $params] = array_map('trim', explode(';', $part, 2));
            $lang = $langPart;
            if (preg_match('/q=([0-9.]+)/i', $params, $matches)) {
                $q = (float) $matches[1];
            }
        }

        $primary = strtolower(explode('-', $lang)[0]);
        if ($primary === '') {
            continue;
        }

        $preferences[] = ['lang' => $primary, 'q' => $q];
    }

    usort($preferences, fn($a, $b) => $b['q'] <=> $a['q']);

    foreach ($preferences as $pref) {
        if ($pref['lang'] === 'de') {
            return 'de';
        }
        if ($pref['lang'] === 'en' || $pref['lang'] === '*') {
            return 'en';
        }
    }

    return 'en';
}
