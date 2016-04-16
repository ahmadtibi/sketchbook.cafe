<?php

class App
{
    protected $controller = 'home';
    protected $method = 'index';
    protected $params = [];

    public function __construct()
    {
        $url = $this->parseURL();

        if (file_exists('../app/controllers/' . $url[0] . '.php'))
        {
            $this->controller = $url[0];
            unset($url[0]); // remove from array
        }

        require_once '../app/controllers/' . $this->controller .'.php';

        // Create a new object of this controller
        $this->controller = new $this->controller;

        // Method
        if (isset($url[1]))
        {
            if (method_exists($this->controller, $url[1]))
            {
                $this->method = $url[1];
                unset($url[1]);
            }
        }

        $this->params = $url ? array_values($url) : [];

        // Don't allow view
        if ($this->method == 'view')
        {
            error('Invalid method.');
        }

        call_user_func_array([$this->controller, $this->method], $this->params);
    }

    public function parseUrl()
    {
        // Sanitize
        //$url = $_GET['url'];
        $url = isset($_GET['url']) ? $_GET['url'] : '';
        if (isset($url))
        {
            $url = trim(addslashes($url));

            // Length Check
            if (isset($url{255}))
            {
                return null;
            }

            // Specific Characters Only
            if (preg_match('/[^A-Za-z0-9_\/]/',$url))
            {
                return null;
            }

            // Return URL
            return $url = explode('/',filter_var(rtrim($url, '/'), FILTER_SANITIZE_URL));
        }

    }
}