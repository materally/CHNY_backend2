<?php

class Home extends KM_Controller {
    public function index()
    {
        $title = "Home";
        $this->view('_template/head', ['title' => $title]);
        $this->view('home/index');
        $this->view('_template/footer');
    }

    /* public function update()
    {
        $c = ClientsModel::all();
        foreach ($c as $u) {
            echo $u->client_id.'<br>';
            echo $u->utolso_karbantartas.'<br>';
            $m = new MaintenanceModel;
            $m->list_id = -1;
            $m->client_id = $u->client_id;
            $m->datum = $u->utolso_karbantartas;
            $m->save();
        }
    } */

}