<?php

require_once dirname(__DIR__).'/vendor/autoload.php';
require_once dirname(__DIR__).'/db/generated-conf/config.php';

use Klein\Klein;
use App\View;
use App\Models\TaskQuery;

$klein = new Klein();

$klein->respond('GET', '/', function () {
    return View::render('index.twig', [
        'tasks' => TaskQuery::create()->find()
    ]);
});

$klein->dispatch();
