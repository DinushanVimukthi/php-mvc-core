<?php

namespace stdin\core;

use stdin\core\Exceptions\NotFoundException;

class Router
{

    protected array $route = [];
    public Request $request;
    public Response $response;
    public function __construct(Request $request,Response $response)
    {
        $this->request = $request;
        $this->response = $response;
    }

    public function get($path,$callback)
    {
        $this->route['get'][$path] = $callback;
    }
    public function post($path,$callback)
    {
        $this->route['post'][$path] = $callback;
    }

    public function resolve()
    {
        $path=$this->request->getPath();
        $method=$this->request->method();
        $callback=$this->route[$method][$path] ?? false;
        if(!$callback){
            $this->response->setStatusCode(404);
            throw new NotFoundException();
        }
        if (is_string($callback)) {
            return Application::$app->view->renderView($callback);
        }
        if(is_array($callback)){
            /** @var Controller $controller */
            $controller = new $callback[0]();
            Application::$app->controller=$controller;
            $controller->action =$callback[1];
            $callback[0]= $controller;
            foreach ($controller->getMiddlewares() as $middleware){
                $middleware->execute();
            }
        }
        return call_user_func($callback,$this->request,$this->response);



    }



//    protected function layoutContent()
//    {
//        $layout=Application::$app->layout;
//        if(Application::$app->controller)
//        {
//        $layout=Application::$app->controller->layout;
//        }
//        ob_start();
//        include_once Application::$ROOT_DIR ."/views/layout/$layout.php";
//        return ob_get_clean();
//    }
//
//    protected function renderOnlyView($view,$params)
//    {
//        foreach ($params as $key=>$value){
//            $$key=$value;
//        }
//
//        ob_start();
//        include_once Application::$ROOT_DIR ."/views/$view.php";
//        return ob_get_clean();
//    }

}