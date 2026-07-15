<?php

declare(strict_types=1);

/**
 * Load and localize CV data from content/cv.json.
 *
 * @return CvData
 */
function loadCV(string $lang): array
{
    $json = file_get_contents(__DIR__ . '/../content/cv.json');
    /** @var array<string, mixed> $data */
    $data = json_decode($json === false ? '{}' : $json, true);
    /** @var CvData $cv */
    $cv = resolveLocale($data, $lang);
    return $cv;
}

/**
 * Recursively resolve _de fields: if $lang is 'de' and a key_de sibling
 * exists, replace key with key_de, then drop the _de field.
 *
 * @param  array<array-key, mixed> $data
 * @return array<array-key, mixed>
 */
function resolveLocale(array $data, string $lang): array
{
    $resolved = [];
    foreach ($data as $key => $value) {
        if (is_string($key) && substr($key, -3) === '_de') {
            continue; // handled when processing the base key
        }
        if ($lang === 'de' && array_key_exists($key . '_de', $data)) {
            $value = $data[$key . '_de'];
        }
        if (is_array($value)) {
            // Indexed array (list) vs associative array
            if (array_values($value) === $value) {
                $value = array_map(
                    fn($item) => is_array($item) ? resolveLocale($item, $lang) : $item,
                    $value
                );
            } else {
                $value = resolveLocale($value, $lang);
            }
        }
        $resolved[$key] = $value;
    }
    return $resolved;
}

/**
 * Format a YYYY-MM or YYYY date string for display.
 */
function formatCVDate(string $date, string $lang): string
{
    if (strlen($date) === 4) {
        return $date;
    }
    $months_en = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
    $months_de = ['Jan', 'Feb', 'Mär', 'Apr', 'Mai', 'Jun', 'Jul', 'Aug', 'Sep', 'Okt', 'Nov', 'Dez'];
    $parts = explode('-', $date);
    $monthIndex = (int)$parts[1] - 1;
    $months = $lang === 'de' ? $months_de : $months_en;
    return $months[$monthIndex] . ' ' . $parts[0];
}

/**
 * Return all UI labels for the CV page in the given language.
 *
 * @return array<string, string>
 */
function cvLabels(string $lang): array
{
    $de = $lang === 'de';
    return [
        'present'        => $de ? 'heute' : 'present',
        'experience'     => $de ? 'Erfahrung' : 'Experience',
        'education'      => $de ? 'Ausbildung' : 'Education',
        'certifications' => $de ? 'Zertifikate' : 'Certificates',
        'languages'      => $de ? 'Sprachen' : 'Languages',
        'volunteering'   => $de ? 'Ehrenamt' : 'Volunteering',
        'grade'          => $de ? 'Note' : 'Grade',
    ];
}
