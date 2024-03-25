<?php

use DI\ContainerBuilder;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Slim\App;

require_once __DIR__ . '/../vendor/autoload.php';

$containerBuilder = new ContainerBuilder();
$containerBuilder->addDefinitions(__DIR__ . '/container.php');
$container = $containerBuilder->build();

$app = $container->get(App::class);


// Add global variable to twig
$app->add('App\Middlewares\BreadcrumbsMiddleware');


$app->get('/', 'App\Controllers\HomeController:index');
$app->get('/item/{id}', 'App\Controllers\HomeController:getAnnonce');


return $app;
