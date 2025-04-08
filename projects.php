<?php
class Project
{
  public $title;
  public $description;
  public $image;
  public $tags;
  public $linkTitle;
  public $linkUrl;
}

$project1 = new Project();
$project1->title = 'Freiburg Challenge / 2020';
$project1->description = "A location-based social game for Freiburg I'm currently working on.";
$project1->image = 'img/freiburg-challenge.jpg';
$project1->tags = ['Typescript', 'React JS', 'Firebase', 'Docker', 'PWA'];


$project2 = new Project();
$project2->title = 'What The Flag / 2019';
$project2->description =
  'A Quizduell-like game with questions about countries, capitals and flags where users can challenge each other. Developed for my room mates as a Progressive Web App with the MERN Stack.';
$project2->image = 'img/what-the-flag.jpg';
$project2->tags = ['JavaScript', 'React JS', 'MongoDB', 'Express JS', 'PWA'];
$project2->linkTitle = 'github repo';
$project2->linkUrl = 'https://github.com/nicograef/what-the-flag';



$project3 = new Project();
$project3->title = 'Sudoku Android App / 2016';
$project3->description =
  "A native android app to learn and play sudoku puzzles. I started this project in early 2016 'cause I wanted to know how native app developing works. After publishing the first version I made a total revamp including a new design and a Sudoku of the Week.";
$project3->image = 'img/sudoku-app.jpg';
$project3->tags = ['Java', 'Android', 'Firebase'];
$project3->linkTitle = 'github repo';
$project3->linkUrl = 'https://github.com/nicograef/sudoku-trainer';



$project4 = new Project();
$project4->title = 'Lokalrunde / 2020';
$project4->description =
  'For the german-wide online hackathon #WirVsVirus my team designed and prototyped an app to support local bars and cafes during the covid-19 crisis.';
$project4->image = 'img/lokalrunde.jpg';
$project4->tags = ['Hackathon', 'Angular JS', 'Firebase'];
$project4->linkTitle = 'github repo';
$project4->linkUrl = 'https://github.com/VirtualCoffee/lokalrunde';


$project5 = new Project();
$project5->title = 'Smart Coffee / 2015';
$project5->description =
  'As part of a student project a fellow stundent and I hacked a coffee machine to accept orders via wifi. We also built a robot arm to serve cups and improved some sensors.';
$project5->image = 'img/smart-coffee.jpg';
$project5->tags = ['Arduino', 'C++', '3D-Printing', 'Embedded'];
$project5->linkTitle = 'watch video';
$project5->linkUrl = 'https://youtu.be/dsMQO0oeDec';


$project6 = new Project();
$project6->title = 'Tutorial Runner Game / 2015';
$project6->description =
  'Back in 2015 I dreamed of being a game developer. So I decided to try out the Unreal Engine. After completing a tutorial I created this runner game. I also composed the music, created the game sounds and some textures.';
$project6->image = 'img/tutorial-runner.jpg';
$project6->tags = ['Unreal Engine', 'C++', 'Game Dev'];
$project6->linkTitle = 'watch video';
$project6->linkUrl = 'https://youtu.be/k_JBZWA0-zM';


$project7 = new Project();
$project7->title = 'Board Games App / 2018';
$project7->description =
  "A functional mockup for a board games community app based on Framework7 and Cordova/Phonegap I designed for a friend's student project.";
$project7->image = 'img/board-games-community-app.jpg';
$project7->tags = ['Cordova', 'Framework7', 'Mockup', 'Prototype'];
$project7->linkTitle = 'Watch demo';
$project7->linkUrl = 'https://youtu.be/43I4IdAd3HA';


$project8 = new Project();
$project8->title = 'Sudoku Solver / 2016';
$project8->description =
  'Java gui application to solve sudokus I created out of curiosity back in 2016. I was wondering if I could come up with a algorithm to solve sudokus - on my on.';
$project8->image = 'img/sudoku-solver.jpg';
$project8->tags = ['Java', 'Desktop', 'Algorithms'];
$project8->linkTitle = 'github repo';
$project8->linkUrl = 'https://github.com/nicograef/sudoku-solver';


$project9 = new Project();
$project9->title = 'ML Classification / 2016';
$project9->description =
  'After completing an online course about Machine Learning I played around with the MNIST-dataset, implemented my version of the Genetic Algorithm and coded a Feed Forward Neural Network.';
$project9->image = 'img/classification.jpg';
$project9->tags = ['Python', 'Java', 'Machine Learning', 'Classification'];
$project9->linkTitle = 'github repo';
$project9->linkUrl = 'https://github.com/nicograef/ai';


$project10 = new Project();
$project10->title = 'Wirkraft / 2018';
$project10->description =
  'In 2018 I developed a native android app, a hybrid app and several features for the web backend of a community platform.';
$project10->image = 'img/wirkraft.jpg';
$project10->tags = ['Vue JS', 'Cordova', 'Couch DB', 'Android'];
$project10->linkTitle = 'Visit website';
$project10->linkUrl = 'http://wirkraft.com';


$project11 = new Project();
$project11->title = 'MSH Sportpferde / 2017';
$project11->description =
  "As a webdesign freelancer I designed and (hand-)coded the website for an equestrian farm. Later they asked me to help build an online presence. That's kind of how I got into online marketing.";
$project11->image = 'img/msh-sportpferde.jpg';
$project11->tags = ['Web Design', 'Online Marketing', 'Instagram', 'Google'];
$project11->linkTitle = 'Visit website';
$project11->linkUrl = 'https://msh-sportpferde.de';


$project12 = new Project();
$project12->title = 'Meisterwunder Pop-up Store / 2015';
$project12->description =
  'As my first startup-like project a friend and I opened a pop-up store before christmas 2015. We offered custom portrait art and showed some art hacking experiments at our opening.';
$project12->image = 'img/makelangelo.jpg';
$project12->tags = ['Startup', 'Art'];
$project12->linkTitle = 'watch video';
$project12->linkUrl = 'https://youtu.be/p4j1_mpg-3o';

$project13 = new Project();
$project13->title = 'Wiwili / 2019';
$project13->description =
  "In Freiburg there's a bridge called Wiwili and it's got a cyclists sensor built into the ground. I downloaded the data from FRITZ (the open data platform of Freiburg) and used it to play around with D3.JS.";
$project13->image = 'img/wiwili.png';
$project13->tags = ['Data Visualisation', 'D3.JS', 'Open Data'];
$project13->linkTitle = 'Play with it';
$project13->linkUrl = 'https://nicograef.com/wiwili';

$project14 = new Project();
$project14->title = 'Project 0742 / 2016';
$project14->description =
  'A simple website maker I made to learn some node.js stuff. Sadly, I lost the final version of my code and this repo only represents the alpha version.';
$project14->image = 'img/project0742.jpg';
$project14->tags = ['Node JS', 'Web Design'];
$project14->linkTitle = 'watch video';
$project14->linkUrl = 'https://youtu.be/Y6JF0A9bYoE';

$project15 = new Project();
$project15->title = 'Kuunery / 2019';
$project15->description =
  'As an experiment in online marketing I invented an art magazine startup, set up an online shop with shopify and used instagram for marketing.';
$project15->image = 'img/kuunery.jpg';
$project15->tags = ['Online Marketing', 'Instagram', 'Shopify', 'Art'];

$project16 = new Project();
$project16->title = 'Country Quiz NPM Module / 2019';
$project16->description =
  'I created this module as part of the What The Flag project and published it to NPM just out of curiosity. It lets you create questions and quizzes about countries, flags and capitals. Later, I used this as a training project for testing and Travis CI.';
$project16->image = 'img/country-quiz.jpg';
$project16->tags = ['NPM', 'Travis CI', 'Documentation', 'Test Coverage'];
$project16->linkTitle = 'npm package';
$project16->linkUrl = 'https://www.npmjs.com/package/country-quiz';

$projects = array($project1, $project2, $project3, $project4, $project5, $project6, $project7, $project8, $project9, $project10, $project11, $project12, $project13, $project14, $project15, $project16);

// convert all title and description strings of all projects to html entities
foreach ($projects as $project) {
  $project->title = htmlspecialchars($project->title);
  $project->description = htmlspecialchars($project->description);
}