<?php

namespace App;

use Twig\Loader\FilesystemLoader;
use Twig\Environment;

class View
{
    public static function render($name, $args = [])
    {
        static $twig = null;

        if ($twig === null) {
            $loader = new FilesystemLoader(dirname(__DIR__).'/App/Views');
            $twig = new Environment($loader);
        }

        return $twig->render($name, $args);
    }
}
