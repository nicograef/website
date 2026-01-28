<?php
class Project
{
    public $title;
    public $description;
    public $image;
    public $tags;
    public $linkTitle;
    public $linkUrl;

    public function __construct($data)
    {
        $this->title = htmlspecialchars($data['title']);
        $this->description = htmlspecialchars($data['description']);
        $this->image = $data['image'];
        $this->tags = $data['tags'];
        $this->linkTitle = $data['linkTitle'] ?? null;
        $this->linkUrl = $data['linkUrl'] ?? null;
    }
}

$jsonData = file_get_contents(__DIR__ . '/../content/projects.json');
$projectData = json_decode($jsonData, true);
$projects = array_map(fn($data) => new Project($data), $projectData);
