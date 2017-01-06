<?php 
require_once(__DIR__.'/../init.php');
$settings = json_decode(file_get_contents('../settings.json'), true);

$serializeSkillFilter = new Twig_SimpleFilter('technologySerialize', function ($string) {
    return strtolower(str_replace(['.', ' ', '/'], '', $string));
});
$app['twig']->addFilter($serializeSkillFilter);

$app->get('/', function() use($app, $settings) {
    $projects = $settings['projects'];

    foreach($projects as $pkey => $project) {
        foreach($project as $key => $value) {
            if(in_array($key, ['title', 'description'], true)) {
                $project[$key] = $value[$app['translator']->getLocale()];
            }
        }
        $projects[$pkey] = $project;
    }

    return $app['twig']->render('main.html.twig', [
        'projects' => $projects,
        'skills' => $settings['skills']
    ]);
}); 

$app->run();