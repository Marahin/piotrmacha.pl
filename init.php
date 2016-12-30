<?php
require_once __DIR__.'/vendor/autoload.php'; 

use Symfony\Component\Translation\Loader\YamlFileLoader;
use Symfony\Component\HttpFoundation\Request;

$languages = ['en', 'pl'];

$app = new Silex\Application();
$app['debug'] = $_SERVER['REMOTE_ADDR'] == '127.0.0.1' ? true : false;
$app->register(new Silex\Provider\TwigServiceProvider(), array(
    'twig.path' => __DIR__.'/views',
));
$app->register(new Silex\Provider\LocaleServiceProvider());
$app->register(new Silex\Provider\TranslationServiceProvider(), array(
    'locale_fallbacks' => array($languages[0]),
));
$app->extend('translator', function($translator, $app) use ($languages) {
    $translator->addLoader('yaml', new YamlFileLoader());
    foreach($languages as $language) {
        $translator->addResource('yaml', __DIR__.'/locales/'.$language.'.yml', $language);
    }
    return $translator;
});

$app->get('/{locale}', function($locale) use ($app) {
    setcookie('language', $locale, time() + 31536000);
    $_COOKIE['language'] = $locale;
    return $app->redirect('/');
})->assert('locale', implode('|', $languages));

$app->before(function(Request $request) use ($app, $languages) {
    if(!isset($_COOKIE['language']) || !in_array($_COOKIE['language'], $languages)) { 
        $language = substr($_SERVER['HTTP_ACCEPT_LANGUAGE'], 0, 2);
        setcookie('language', $language, time() + 31536000);
        $_COOKIE['language'] = $language;
    } else {
        $language = $_COOKIE['language'];
    }
    if(!in_array($language, $languages)) {
        $language = $languages[0];
    }
    $app['translator']->setLocale($language);
});