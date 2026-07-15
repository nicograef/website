<?php

declare(strict_types=1);

/**
 * @return list<Project>
 */
function loadProjects(string $lang = 'en'): array
{
    $json = file_get_contents(__DIR__ . '/../content/projects.json');
    /** @var list<Project> $projects */
    $projects = json_decode($json === false ? '[]' : $json, true);

    foreach ($projects as &$p) {
        foreach (['title', 'description', 'linkTitle'] as $field) {
            $deKey = $field . '_de';
            if ($lang === 'de' && !empty($p[$deKey])) {
                $p[$field] = $p[$deKey];
            }
            unset($p[$deKey]);
        }
    }
    unset($p);

    return $projects;
}
