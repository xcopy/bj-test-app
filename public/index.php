<?php

require_once dirname(__DIR__).'/vendor/autoload.php';
require_once dirname(__DIR__).'/db/generated-conf/config.php';

use Klein\Klein;
use Klein\Request;
use App\View;
use App\Models\TaskQuery;

$klein = new Klein();

$klein->respond('GET', '/', function (Request $request) {
    $page = $request->param('page') ?? 1;

    $pager = TaskQuery::create()
        ->paginate($page, $maxPerPage = 3);

    return View::render('index.twig', [
        'pager' => $pager
    ]);
});

$klein->dispatch();
