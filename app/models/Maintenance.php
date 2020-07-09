<?php

use Illuminate\Database\Eloquent\Model as Eloquent;

class MaintenanceModel extends Eloquent {
    protected $table = 'maintenance';
    protected $primaryKey = 'maintenance_id';
    public $timestamps = false;
    protected $fillable = ['list_id', 'client_id', 'datum', 'elvegezve_datum', 'elvegezve'];

    public function Client()
    {
        return $this->belongsTo('ClientsModel', 'client_id');
    }

    public function ListInfo()
    {
        return $this->belongsTo('MaintenanceListModel', 'list_id');
    }

}