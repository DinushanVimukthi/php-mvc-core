<?php

namespace stdin\core\middlewares;

use stdin\core\Application;
use stdin\core\Exceptions\ForbiddenException;

class AuthMiddleware extends BaseMiddleware
{
    public array $actions=[];
    public function __construct(array $actions=[])
    {
        $this->actions=$actions;
    }

    public function execute()
    {
        // TODO: Implement execute() method.
        if(Application::isGuest()) {
            if (empty($this->actions) ||
                in_array(Application::$app->controller->action, $this->actions)) {
                throw new ForbiddenException();
            }
        }
    }

}