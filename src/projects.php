<?php

/**
 * Project data. Values are stored raw and escaped at the template layer.
 */
class Project
{
    public string $title;
    public string $description;
    public string $image;
    public array $tags;
    public ?string $linkTitle;
    public ?string $linkUrl;

    public function __construct(array $data, string $lang = 'en')
    {
        $this->title = ($lang === 'de' && !empty($data['title_de']))
            ? $data['title_de']
            : $data['title'];
        $this->description = ($lang === 'de' && !empty($data['description_de']))
            ? $data['description_de']
            : $data['description'];
        $this->image = $data['image'];
        $this->tags = $data['tags'];
        $this->linkTitle = ($lang === 'de' && !empty($data['linkTitle_de']))
            ? $data['linkTitle_de']
            : ($data['linkTitle'] ?? null);
        $this->linkUrl = $data['linkUrl'] ?? null;
    }
}

function loadProjects(string $lang = 'en'): array
{
    $jsonData = file_get_contents(__DIR__ . '/../content/projects.json');
    $projectData = json_decode($jsonData, true);
    return array_map(fn($data) => new Project($data, $lang), $projectData);
}
