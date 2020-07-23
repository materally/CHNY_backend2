<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: X-API-KEY, Origin, X-Requested-With, Content-Type, Accept, Access-Control-Request-Method");
header("Access-Control-Allow-Credentials: true");
header("Content-Type: application/json; charset=UTF-8");

class Clients extends KM_Controller {
    public function index($client_id = 0)
    {
        if($_SERVER['REQUEST_METHOD'] == 'GET' AND $_GET['API_SECRET'] == API_SECRET){
            // get user(s)
            if(empty($client_id) OR $client_id === 0){
                // get all users
                $client = ClientsModel::with('ClientMaintenances')->orderBy('cegnev', 'asc')->get();
            }else{
                $client = ClientsModel::with('ClientMaintenances')->find($client_id);
            }
            http_response_code(200);
            echo json_encode($client);
        }else{
            http_response_code(405);
            echo json_encode(['error' => 'Bad request']);
        }
    }

    public function delete()
    {
        if($_SERVER['REQUEST_METHOD'] == 'POST' AND $_POST['API_SECRET'] == API_SECRET){
            $client_id = $_POST['client_id'];
            ClientsModel::where('client_id', $client_id)->first()->delete();

            $m = MaintenanceModel::where('client_id', $client_id)->first();
            if($m){
                MaintenanceModel::where('client_id', $client_id)->delete();
            }
            
            http_response_code(200);
            echo json_encode(['success' => 'Sikeres törlés!']);
        }else{
            http_response_code(405);
            echo json_encode(['error' => 'Bad request']);
        }
    }

    public function create()
    {
        if($_SERVER['REQUEST_METHOD'] == 'POST' AND $_POST['API_SECRET'] == API_SECRET){
            // required
            $cegnev         = $_POST['cegnev'];
            $szekhely       = $_POST['szekhely'];
            $szamlazasi_cim = $_POST['szamlazasi_cim'];

            $kapcs_nev           = (isset($_POST['kapcs_nev'])) ? $_POST['kapcs_nev'] : '-';
            $kapcs_telefon       = (isset($_POST['kapcs_telefon'])) ? $_POST['kapcs_telefon'] : '-';
            $kapcs_email         = (isset($_POST['kapcs_email'])) ? $_POST['kapcs_email'] : '-';
            $kamra_latitude      = (isset($_POST['kamra_latitude'])) ? $_POST['kamra_latitude'] : 0;
            $kamra_longitude     = (isset($_POST['kamra_longitude'])) ? $_POST['kamra_longitude'] : 0;
            $kamra_cim           = (isset($_POST['kamra_cim'])) ? $_POST['kamra_cim'] : 0;
            $utolso_karbantartas = (isset($_POST['utolso_karbantartas']) AND !empty($_POST['utolso_karbantartas'])) ? $_POST['utolso_karbantartas'] : NULL;

            if(!empty($cegnev) AND !empty($szekhely) AND !empty($szamlazasi_cim)){
                $client = new ClientsModel;
                $client->cegnev = $cegnev;
                $client->szamlazasi_cim = $szamlazasi_cim;
                $client->szekhely = $szekhely;
                $client->kapcs_nev = $kapcs_nev;
                $client->kapcs_telefon = $kapcs_telefon;
                $client->kapcs_email = $kapcs_email;
                $client->kamra_latitude = $kamra_latitude;
                $client->kamra_longitude = $kamra_longitude;
                $client->kamra_cim = $kamra_cim;
                $client->utolso_karbantartas = $utolso_karbantartas;
                $client->save();
                if($client){
                    if($utolso_karbantartas !== NULL){
                        $kovetkezo_karbantartas = $this->calcKovKarbantartas($utolso_karbantartas);
                        $hutokamra = ClientsModel::where('client_id', $client->client_id)->first();
                        $hutokamra->kovetkezo_karbantartas = $kovetkezo_karbantartas;
                        $hutokamra->save();
                        $m1 = new MaintenanceModel;
                        $m1->list_id = -1;
                        $m1->client_id = $client->client_id;
                        $m1->datum = $utolso_karbantartas;
                        $m1->save();
                    }
                    http_response_code(200);
                    echo json_encode(['success' => 'Az ügyfél létrehozva!', 'client_id' => $client->client_id]);
                }else{
                    http_response_code(200);
                    echo json_encode(['error' => 'A feltöltés nem sikerült!']);
                }
            }else{
                http_response_code(200);
                echo json_encode(['error' => 'Minden mező kitöltése kötelező!']);
            }
        }else{
            http_response_code(405);
            echo json_encode(['error' => 'Bad request']);
        }
    }

    public function updateHutokamra($client_id)
    {
        if($_SERVER['REQUEST_METHOD'] == 'POST' AND $_POST['API_SECRET'] == API_SECRET){
            // required
            $kamra_latitude      = (isset($_POST['kamra_latitude'])) ? $_POST['kamra_latitude'] : NULL;
            $kamra_longitude     = (isset($_POST['kamra_longitude'])) ? $_POST['kamra_longitude'] : NULL;
            $kamra_cim           = (isset($_POST['kamra_cim'])) ? $_POST['kamra_cim'] : NULL;

            if(!empty($kamra_latitude) AND !empty($kamra_longitude) AND !empty($kamra_cim)){
                $kamra = ClientsModel::where('client_id', $client_id)->first();
                $kamra->kamra_latitude = $kamra_latitude;
                $kamra->kamra_longitude = $kamra_longitude;
                $kamra->kamra_cim = $kamra_cim;
                $kamra->save();
                if($kamra){
                    http_response_code(200);
                    echo json_encode(['success' => 'Sikeres módosítás!']);
                }else{
                    http_response_code(200);
                    echo json_encode(['error' => 'A módosítás nem sikerült!']);
                }
            }
        }else{
            http_response_code(405);
            echo json_encode(['error' => 'Bad request']);
        }
    }

    public function updateClient($client_id)
    {
        if($_SERVER['REQUEST_METHOD'] == 'POST' AND $_POST['API_SECRET'] == API_SECRET){
            // required
            $cegnev         = (isset($_POST['cegnev'])) ? $_POST['cegnev'] : NULL;
            $szekhely       = (isset($_POST['szekhely'])) ? $_POST['szekhely'] : NULL;
            $szamlazasi_cim = (isset($_POST['szamlazasi_cim'])) ? $_POST['szamlazasi_cim'] : NULL;
            $kapcs_nev      = (isset($_POST['kapcs_nev'])) ? $_POST['kapcs_nev'] : NULL;
            $kapcs_email    = (isset($_POST['kapcs_email'])) ? $_POST['kapcs_email'] : NULL;
            $kapcs_telefon  = (isset($_POST['kapcs_telefon'])) ? $_POST['kapcs_telefon'] : NULL;

            if(!empty($cegnev) AND !empty($szekhely) AND !empty($szamlazasi_cim)){
                $client = ClientsModel::where('client_id', $client_id)->first();
                $client->cegnev = $cegnev;
                $client->szekhely = $szekhely;
                $client->szamlazasi_cim = $szamlazasi_cim;
                $client->kapcs_nev = $kapcs_nev;
                $client->kapcs_email = $kapcs_email;
                $client->kapcs_telefon = $kapcs_telefon;
                $client->save();
                if($client){
                    http_response_code(200);
                    echo json_encode(['success' => 'Sikeres módosítás!']);
                }else{
                    http_response_code(200);
                    echo json_encode(['error' => 'A módosítás nem sikerült!']);
                }
            }
        }else{
            http_response_code(405);
            echo json_encode(['error' => 'Bad request']);
        }
    }

    public function getMarkersForMap()
    {
        $return = array();
        $clients = ClientsModel::with('ClientMaintenances')->get();
        foreach ($clients as $value) {
            if($value['kamra_latitude']){
                
                $count = MaintenanceModel::where('client_id', $value['client_id'])->count();
                if($count === 1){
                    $uk = MaintenanceModel::where('client_id', $value['client_id'])->orderBy('maintenance_id', 'desc')->first();
                }else{
                    $uk = MaintenanceModel::where('client_id', $value['client_id'])->orderBy('maintenance_id', 'desc')->skip(1)->take(1)->first();
                }

                $kov = $this->calcKovKarbantartas($uk['datum']);

                $json = json_decode($value, true);
                $datum = $json['client_maintenances'][0]['datum'];
                $elvegezve = $json['client_maintenances'][0]['elvegezve'];
               
                $now = date('Y-m-d');

                if($now <= $kov) {
                    $fill = '#ad9900'; // zöld
                }else{
                    // ha túlléptük, de még nincs elvégezve
                    if($elvegezve === 0){
                        $fill = '#E80D8A';
                    }else{
                        // ha túlléptük, és már el is van végezve
                        $fill = '#ad9900'; // zöld
                    }
                }
                $return[] = [
                    'data' => $value,
                    'kovetkezo_karbantartas' => $kov,
                    'utolso_karbantartas' => $uk,
                    'fill' => $fill,
                    'original_fill' => $fill,
                ]; 
            }
        }
        http_response_code(200);
        echo json_encode($return);
    }

    public static function calcKovKarbantartas($datum)
    {
        $utolso_karbantartas = new DateTime($datum);
        $uk_year    = $utolso_karbantartas->format('Y');
        $uk_month   = $utolso_karbantartas->format('m');
        $uk_day     = $utolso_karbantartas->format('d');

        $for_Oktober   = array('01', '02', '03', '04', '05', '06', '07');
        $for_Aprilis   = array('08', '09', '10', '11', '12');

        // idei év októberre
        if(in_array($uk_month, $for_Oktober) && $uk_year === date("Y")){
            $kovetkezo_karbantartas = date("Y").'-10-01';
        }

        // következő év, október után
        if(in_array($uk_month, $for_Aprilis) && $uk_year === date("Y")){
            $newy = date("Y")+1;
            $kovetkezo_karbantartas = $newy.'-04-01';
        }

        // előző évek októberre
        if(in_array($uk_month, $for_Oktober) && $uk_year < date("Y")){
            $kovetkezo_karbantartas = $uk_year.'-10-01';
        }

        // következő év, október után
        if(in_array($uk_month, $for_Aprilis) && $uk_year < date("Y")){
            $newy = $uk_year+1;
            $kovetkezo_karbantartas = $newy.'-04-01';
        }

        return $kovetkezo_karbantartas;
    }

}