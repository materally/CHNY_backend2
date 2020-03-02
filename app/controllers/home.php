<?php

class Home extends KM_Controller {
    public function index()
    {
        $title = "Home";
        $this->view('_template/head', ['title' => $title]);
        $this->view('home/index');
        $this->view('_template/footer');
    }
}