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
                $client = ClientsModel::with('ClientMaintenances')->orderBy('utolso_karbantartas', 'asc')->get();
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
                        $kovetkezo_karbantartas = new DateTime($utolso_karbantartas);
                        $kovetkezo_karbantartas->modify('+6 months');
                        $kovetkezo_karbantartas->format('Y-m-d');
                        $hutokamra = ClientsModel::where('client_id', $client->client_id)->first();
                        $hutokamra->kovetkezo_karbantartas = $kovetkezo_karbantartas;
                        $hutokamra->save();
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
        $clients = ClientsModel::all();
        foreach ($clients as $value) {
            if($value['kamra_latitude']){
                $kovetkezo_karbantartas = new \DateTime($value['kovetkezo_karbantartas']);
                $now = new \DateTime();
                if($kovetkezo_karbantartas->diff($now)->days > 30) {
                    $fill = '#006009';
                }else{
                    $fill = '#d00';
                }
                $return[] = [
                    'data' => $value,
                    'fill' => $fill
                ];
            }
        }
        http_response_code(200);
        echo json_encode($return);
    }

}