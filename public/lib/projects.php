<?php

function loadProjects(string $lang = 'en'): array
{
    $json = file_get_contents(__DIR__ . '/../content/projects.json');
    $projects = json_decode($json, true);

    foreach ($projects as &$p) {
        foreach (['title', 'description', 'linkTitle'] as $field) {
            $deKey = $field . '_de';
            if ($lang === 'de' && !empty($p[$deKey])) {
                $p[$field] = $p[$deKey];
            }
            unset($p[$deKey]);
        }
    }

    return $projects;
}
