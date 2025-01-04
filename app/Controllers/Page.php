<?php

namespace App\Controllers;

class Page extends BaseController
{
    public function __construct() {
        // parent::__construct();

        $this->enableDefaultFunctions = false;
    }

    /**
    * Load master template
    */
    public function index() {
        return view('frontend/index.html');
    }   

    /**
    * Remaps if function is not existing
    */
    public function _remap($method, $params = array()) {
        if (method_exists($this, $method)) {
            return call_user_func_array(array($this, $method), $params);
        } else {
            return $this->index();
        }
    }

    /**
    * Load specific page
    */
    public function load($page) {
        if (!file_exists(APPPATH . 'views/pages/' . $page . '.php')) {
            show_404();
        }
        
        return view('pages/' . $page . '.php');
    }
}