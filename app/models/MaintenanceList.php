
<?php

use Illuminate\Database\Eloquent\Model as Eloquent;

class MaintenanceListModel extends Eloquent {
    protected $table = 'maintenance_list';
    protected $primaryKey = 'list_id';
    public $timestamps = false;
    protected $fillable = ['datum', 'munkatars', 'megjegyzes', 'megjegyzes_munkatars', 'utvonal', 'pdf'];

    public function Maintenances()
    {
        return $this->hasMany('MaintenanceModel', 'list_id')->with('Client');
    }

}