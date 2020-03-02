<?php

class KM_Controller extends KM_Helpers {

    public function view($view, $data = [])
    {
        require_once '../app/views/'.$view.'.php';
    }

    public function load_email_template($template)
    {
        return file_get_contents('../app/views/_email/'.$template.'.html', true);
    }

}