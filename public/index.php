<?php

require_once dirname(__DIR__).'/vendor/autoload.php';
require_once dirname(__DIR__).'/db/generated-conf/config.php';

use Klein\Klein;
use Klein\Request;
use Klein\Response;
use Propel\Runtime\ActiveQuery\Criteria;
use App\View;
use App\Models\Task;
use App\Models\TaskQuery;
use App\Models\User;
use App\Models\UserQuery;

$klein = new Klein();

$klein->respond(['GET', 'POST'], '/', function (Request $request, Response $response) {
    session_start();

    list(
        'page' => $page,
        'column' => $column,
        'order' => $order
    ) = $request->paramsGet();

    $allowedOrders = [Criteria::DESC, Criteria::ASC];
    $sortableColumns = ['id', 'status', 'username', 'email'];

    $_column = in_array($column, $sortableColumns)
        ? $column
        : $sortableColumns[0];

    $_order = in_array($order, $allowedOrders)
        ? $order
        : $allowedOrders[0];

    $_page = $page ?? 1;

    $tasks = TaskQuery::create()
        ->orderBy($_column, $_order)
        ->paginate($_page, 3);

    $sortables = [];

    foreach ($sortableColumns as $col) {
        $query = [
            'column' => $col,
            'order' => $_order === Criteria::DESC ? Criteria::ASC : Criteria::DESC
        ];

        if ($page && $page > 1) {
            $query['page'] = $page;
        }

        array_push($sortables, [
            'name' => $col,
            'query' => http_build_query($query)
        ]);
    }

    $pagination = [];

    foreach ($tasks->getLinks() as $i) {
        $query = ['page' => $i];

        if ($column && $order) {
            $query['column'] = $column;
            $query['order'] = $order;
        }

        array_push($pagination, [
            'page' => $i,
            'query' => http_build_query($query)
        ]);
    }

    $form = [
        'task' => new Task,
        'errors' => []
    ];

    if ($request->method('POST')) {
        list(
            'username' => $username,
            'email' => $email,
            'content' => $content
        ) = $request->paramsPost();

        $task = new Task;
        $task->setUsername($username);
        $task->setEmail($email);
        $task->setContent($content);

        $form['task'] = $task;

        if (!$task->validate()) {
            foreach ($task->getValidationFailures() as $failure) {
                $form['errors'][$failure->getPropertyPath()] = $failure->getMessage();
            }
        } else {
            $task->save();
            $response->redirect('/');
        }
    }

    $user = UserQuery::create()->findOneById($_SESSION['user_id']);

    return View::render('index', compact(
        'order', 'column', 'page',
        'sortables', 'pagination',
        'tasks',
        'form',
        'user'
    ));
});

$klein->respond(['GET', 'POST'], '/signin', function (Request $request, Response $response) {
    session_start();

    $user = $_SESSION['user_id']
        ? UserQuery::create()->findOneById($_SESSION['user_id'])
        : new User;
    $errors = [];

    if ($request->method('POST')) {
        list(
            'username' => $username,
            'password' => $password
        ) = $request->paramsPost();

        $user = new User;
        $user->setUsername($username);
        $user->setPassword($password);

        if ($user->validate()) {
            $user = UserQuery::create()->findOneByUsername($username);

            if ($user && password_verify($password, $user->getPassword())) {
                session_start();
                $_SESSION['user_id'] = $user->getPrimaryKey();
                $response->redirect('/');
            }
        } else {
            foreach ($user->getValidationFailures() as $failure) {
                $errors[$failure->getPropertyPath()] = $failure->getMessage();
            }
        }
    }

    return View::render('signin', [
        'user' => $user,
        'errors' => $errors
    ]);
});

$klein->respond('GET', '/signout', function (Request $request, Response $response) {
    session_start();
    $_SESSION['user_id'] = null;
    session_destroy();

    $response->redirect('/');
});

$klein->dispatch();
