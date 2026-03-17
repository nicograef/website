<?php
class Project
{
    public $title;
    public $description;
    public $image;
    public $tags;
    public $linkTitle;
    public $linkUrl;

    public function __construct($data, string $lang = 'en')
    {
        $rawTitle = ($lang === 'de' && !empty($data['title_de']))
            ? $data['title_de']
            : $data['title'];
        $this->title = htmlspecialchars($rawTitle);
        $rawDescription = ($lang === 'de' && !empty($data['description_de']))
            ? $data['description_de']
            : $data['description'];
        $this->description = htmlspecialchars($rawDescription);
        $this->image = $data['image'];
        $this->tags = $data['tags'];
        $rawLinkTitle = ($lang === 'de' && !empty($data['linkTitle_de']))
            ? $data['linkTitle_de']
            : ($data['linkTitle'] ?? null);
        $this->linkTitle = $rawLinkTitle !== null ? htmlspecialchars($rawLinkTitle) : null;
        $this->linkUrl = $data['linkUrl'] ?? null;
    }
}

function loadProjects(string $lang = 'en'): array
{
    $jsonData = file_get_contents(__DIR__ . '/../content/projects.json');
    $projectData = json_decode($jsonData, true);
    return array_map(fn($data) => new Project($data, $lang), $projectData);
}
