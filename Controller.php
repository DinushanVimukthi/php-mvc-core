<?php

namespace app\core;
use app\core\middlewares\BaseMiddleware;

/**
 * Class Controller
 * @var \app\core\middlewares\BaseMiddleware[]
 */

class Controller
{   public string $action='';
    public string $layout = 'main';
    protected array $middlewares = [];
    public function setLayout($layout)
    {
        $this->layout = $layout;
    }

    public static function render($view, $params = [])
    {
        return Application::$app->view->renderView($view, $params);
    }

    public function registerMiddleware(BaseMiddleware $middleware)
    {
        $this->middlewares[]=$middleware;
    }

    /**
     * @return array
     */
    public function getMiddlewares(): array
    {
        return $this->middlewares;
    }



}