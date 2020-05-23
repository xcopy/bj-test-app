<?php

session_start();

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

$user = $_SESSION['user_id']
    ? UserQuery::create()->findOneById($_SESSION['user_id'])
    : new User;

$klein = new Klein();

$klein->respond(['GET', 'POST'], '/', function (Request $request, Response $response) use ($user) {
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

    $task = new Task;
    $errors = [];

    if ($request->method('POST')) {
        list(
            'username' => $username,
            'email' => $email,
            'content' => $content
        ) = $request->paramsPost();

        $task->setUsername($username);
        $task->setEmail($email);
        $task->setContent($content);

        if (!$task->validate()) {
            foreach ($task->getValidationFailures() as $failure) {
                $errors[$failure->getPropertyPath()] = $failure->getMessage();
            }
        } else {
            $task->save();
            $response->redirect('/');
        }
    }

    return View::render('index', compact(
        'request',
        'order', 'column', 'page',
        'sortables', 'pagination',
        'tasks',
        'form',
        'user',
        'task',
        'errors'
    ));
});

$klein->respond(['GET', 'POST'], '/edit/[i:id]', function (Request $request, Response $response) use($user) {
    $id = $request->param('id');
    $task = TaskQuery::create()->findOneById($id);
    $errors = [];

    if ($user->getPrimaryKey() && $request->method('POST')) {
        list(
            'qs' => $qs,
            'username' => $username,
            'email' => $email,
            'content' => $content,
            'status' => $status
        ) = $request->paramsPost();

        $task->setUsername($username);
        $task->setEmail($email);
        $task->setContent($content);
        $task->setStatus($status === 'on');
        $task->setEdited($content === $task->getContent());

        if (!$task->validate()) {
            foreach ($task->getValidationFailures() as $failure) {
                $errors[$failure->getPropertyPath()] = $failure->getMessage();
            }
        } else {
            $task->save();
            $response->redirect('/?'.$qs);
        }
    }

    return View::render('edit', compact(
        'request',
        'user',
        'task',
        'errors'
    ));
});

$klein->respond(['GET', 'POST'], '/signin', function (Request $request, Response $response) use($user) {
    $errors = [];

    if ($request->method('POST')) {
        list(
            'username' => $username,
            'password' => $password
        ) = $request->paramsPost();

        $user = $user ?? new User;

        $user->setUsername($username);
        $user->setPassword($password);

        if ($user->validate()) {
            $tempUser = UserQuery::create()->findOneByUsername($username);

            if (password_verify($password, $tempUser->getPassword())) {
                $_SESSION['user_id'] = $tempUser->getPrimaryKey();
                $response->redirect('/');
            } else {
                $errors['password'] = 'Wrong password.';
            }
        } else {
            foreach ($user->getValidationFailures() as $failure) {
                $errors[$failure->getPropertyPath()] = $failure->getMessage();
            }
        }
    }

    return View::render('signin', compact(
        'user',
        'errors'
));
});

$klein->respond('GET', '/signout', function (Request $request, Response $response) {
    $_SESSION['user_id'] = null;
    session_destroy();

    $response->redirect('/');
});

$klein->dispatch();
