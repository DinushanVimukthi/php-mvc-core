<?php
namespace app\core;

use app\core\db\Database;
use app\core\db\DbModel;

class Application
{
    public string $layout='main';
    public string $userClass;
    public static $ROOT_DIR;
    public Router $router;
    public Request $request;
    public Response $response;
    public static Application $app;
    public ?Controller $controller=null;
    public Database $db;
    public Session $session;
    public ?UserModel $user;
    public View $view;

    public static function isGuest()
    {
        return !self::$app->user;
    }

    /**
     * @return Controller
     */
    public function getController(): Controller
    {
        return $this->controller;
    }

    /**
     * @param Controller $controller
     */
    public function setController(Controller $controller): void
    {
        $this->controller = $controller;
    }

    public function __construct($path,array $config)
    {
        $this->userClass= $config['userClass'];
        self::$app = $this;
        $this->view=new View();
        self::$ROOT_DIR = $path;
        $this->request = new Request();
        $this->response = new Response();
        $this->session = new Session();
        $this->router = new Router($this->request, $this->response);
        $this->db=new Database($config['db']);
        $primaryValue=$this->session->get('user');

        if($primaryValue) {
            $primaryKey = $this->userClass::PrimaryKey();
            $this->user=$this->userClass::findOne([$primaryKey => $primaryValue]);
        }
        else{
            $this->user=null;
        }
    }
    public function run(){
        try {
        echo $this->router->resolve();
        }catch (\Exception $e){
            $this->response->setStatusCode($e->getCode());
            echo $this->view->renderView('_error',['exception'=>$e]);
        }
    }

    public function login(UserModel $user){
        $this->user=$user;
        $primaryKey=$user->PrimaryKey();
        $primaryValue=$user->{$primaryKey};


        $this->session->set('user',$primaryValue);
        return true;
    }

    public function logOut()
    {
        $this->user=null;
        $this->session->remove('user');

    }

}