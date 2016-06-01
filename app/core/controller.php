<?php

class Controller
{
    protected $model;
    protected $view;
    protected $params;

    public function __construct()
    {
        session_start();
        if (isset($_POST['lang']))
            $_SESSION['lang'] = $_POST['lang'];
        elseif (!isset($_SESSION['lang']))
            $_SESSION['lang'] = 'ru';

        require 'app/lang/'. $_SESSION['lang'].'.php';

        $this->view = new View();
    }

    public function action_index() {
    }

    public function addParams($parameters)
    {
        if(!empty($parameters)){
            $this->params = $parameters;
        }
    }

    public function __get($param)
    {
        if (isset($this->params[$param])) {
            return $this->params[$param];
        }
        return false;
    }

} 