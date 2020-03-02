<?php

use Illuminate\Database\Eloquent\Model as Eloquent;

class ClientsModel extends Eloquent {
    protected $table = 'clients';
    protected $primaryKey = 'client_id';
    public $timestamps = false;
    protected $fillable = ['cegnev', 'szekhely', 'szamlazasi_cim', 'kapcs_nev', 'kapcs_telefon', 'kapcs_email', 'kamra_latitude', 'kamra_longitude', 'kamra_cim', 'utolso_karbantartas', 'kovetkezo_karbantartas'];

    public function ClientMaintenances()
    {
        return $this->hasMany('MaintenanceModel', 'client_id')->with('ListInfo')->orderBy('datum', 'desc');
    }

}