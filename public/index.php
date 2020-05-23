<?php

require_once dirname(__DIR__).'/vendor/autoload.php';

use Klein\Klein;
use App\View;

$klein = new Klein();

$klein->respond('GET', '/', function () {
    return View::render('index.twig');
});

$klein->dispatch();
