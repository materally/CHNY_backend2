<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: X-API-KEY, Origin, X-Requested-With, Content-Type, Accept, Access-Control-Request-Method");
header("Access-Control-Allow-Credentials: true");
header("Content-Type: application/json; charset=UTF-8");

include("clients.php");

class Maintenance extends KM_Controller {
    public function index($list_id = 0)
    {
        if($_SERVER['REQUEST_METHOD'] == 'GET' AND $_GET['API_SECRET'] == API_SECRET){
            // get user(s)
            if(empty($list_id) OR $list_id === 0){
                // get all users
                $maintenance = MaintenanceListModel::with('Maintenances')->get();
            }else{
                $maintenance = MaintenanceListModel::where('list_id', $list_id)->with('Maintenances')->first();
            }
            http_response_code(200);
            echo json_encode($maintenance);
        }else{
            http_response_code(405);
            echo json_encode(['error' => 'Bad request']);
        }
    }

    /* public function update($maintenance_id)
    {
        if($_SERVER['REQUEST_METHOD'] == 'POST' AND $_POST['API_SECRET'] == API_SECRET){
            if($maintenance_id > 0 AND isset($maintenance_id)){
                $maintenance = MaintenanceModel::where('maintenance_id', $maintenance_id)->first();
                $maintenance->datum = $_POST['datum'];
                $maintenance->munkatars = $_POST['munkatars'];
                $maintenance->megjegyzes = $_POST['megjegyzes'];
                $maintenance->save();
                if($maintenance){
                    http_response_code(200);
                    echo json_encode(['success' => 'Sikeres módosítást!']);
                }else{
                    http_response_code(200);
                    echo json_encode(['error' => 'A módosítás nem sikerült!']);
                }
            }else{
                http_response_code(200);
                echo json_encode(['error' => 'Hiányzó paraméter!']);
            }
            
        }else{
            http_response_code(405);
            echo json_encode(['error' => 'Bad request']);
        }
    } */

    public function create($client_id)
    {
        if($_SERVER['REQUEST_METHOD'] == 'POST' AND $_POST['API_SECRET'] == API_SECRET){
            $datum      = $_POST['datum'];
            $munkatars  = $_POST['munkatars'];
            $megjegyzes = (isset($_POST['megjegyzes'])) ? $_POST['megjegyzes'] : NULL;
            
            if($client_id > 0 AND isset($client_id)){
                if(!empty($datum) AND !empty($munkatars)){
                    $maintenance = new MaintenanceModel;
                    $maintenance->client_id = $client_id;
                    $maintenance->datum = $datum;
                    $maintenance->munkatars = $munkatars;
                    $maintenance->megjegyzes = $megjegyzes;
                    $maintenance->save();
                    if($maintenance){
                        /* $kovetkezo_karbantartas = new DateTime($datum);
                        $kovetkezo_karbantartas->modify('+6 months');
                        $kovetkezo_karbantartas->format('Y-m-d'); */
                        $kovetkezo_karbantartas = Clients::calcKovKarbantartas($datum);
                        $hutokamra = ClientsModel::where('client_id', $client_id)->first();
                        $hutokamra->utolso_karbantartas = $datum;
                        $hutokamra->kovetkezo_karbantartas = $kovetkezo_karbantartas;
                        $hutokamra->save();
                        http_response_code(200);
                        echo json_encode(['success' => 'Létrehozva!']);
                    }else{
                        http_response_code(200);
                        echo json_encode(['error' => 'A feltöltés nem sikerült!']);
                    }
                }else{
                    http_response_code(200);
                    echo json_encode(['error' => 'Minden mező kitöltése kötelező!']);
                }
            }else{
                http_response_code(200);
                echo json_encode(['error' => 'Hiányzó paraméter!']);
            }
        }else{
            http_response_code(405);
            echo json_encode(['error' => 'Bad request']);
        }
    }

    public function delete()
    {
        if($_SERVER['REQUEST_METHOD'] == 'POST' AND $_POST['API_SECRET'] == API_SECRET){
            $maintenance_id = $_POST['maintenance_id'];
            if($maintenance_id > 0 AND isset($maintenance_id)){
                $maintenance = MaintenanceModel::where('maintenance_id', $maintenance_id)->first()->delete();
                if($maintenance){
                    http_response_code(200);
                    echo json_encode(['success' => 'Sikeres törlés!']);
                }else{
                    http_response_code(200);
                    echo json_encode(['error' => 'A törlés nem sikerült!']);
                }
            }else{
                http_response_code(200);
                echo json_encode(['error' => 'Hiányzó paraméter!']);
            }
        }else{
            http_response_code(405);
            echo json_encode(['error' => 'Bad request']);
        }
    }

    public function createlist()
    {
        if($_SERVER['REQUEST_METHOD'] == 'POST' AND $_POST['API_SECRET'] == API_SECRET){
            $datum          = date("Y-m-d");
            $munkatars      = $_POST['munkatars'];
            $megjegyzes     = (isset($_POST['megjegyzes'])) ? $_POST['megjegyzes'] : NULL;
            $megjegyzes_munkatars = (isset($_POST['megjegyzes_munkatars'])) ? $_POST['megjegyzes_munkatars'] : NULL;
            $utvonal        = $_POST['utvonal'];
            $pdf            = 'null';
            $list           = json_decode($_POST['list'], true);

            // create list
            $new_list = new MaintenanceListModel;
            $new_list->datum = $datum;
            $new_list->munkatars = $munkatars;
            $new_list->megjegyzes = $megjegyzes;
            $new_list->megjegyzes_munkatars = $megjegyzes_munkatars;
            $new_list->utvonal = $utvonal;
            $new_list->pdf = $pdf;
            $new_list->save();
            $list_id = $new_list->list_id;

            // create maintenance
            foreach ($list as $key => $value){
                /* $m = new MaintenanceModel;
                $m->list_id = $list_id;
                $m->client_id = $value['client_id'];
                $m->datum = $datum;
                $m->save(); */
                $m = MaintenanceModel::where('client_id', $value['client_id'])->first();
                $m->list_id = $list_id;
                $m->save();
                
                $kovetkezo_karbantartas = Clients::calcKovKarbantartas($datum);
                $hutokamra = ClientsModel::where('client_id', $value['client_id'])->first();
                $hutokamra->utolso_karbantartas = $datum;
                $hutokamra->kovetkezo_karbantartas = $kovetkezo_karbantartas;
                $hutokamra->save();
            }

            $createpdf = $this->createPDF($list_id);

            $topdf = MaintenanceListModel::where('list_id', $list_id)->first();
            $topdf->pdf = $createpdf['url'];
            $topdf->save();

            http_response_code(200);
            echo json_encode(['success' => 'Sikeres!', 'list_id' => $list_id]);
   
        }else{
            http_response_code(405);
            echo json_encode(['error' => 'Bad request']);
        }
    }

    public function createPDF($list_id)
    {
        $list = MaintenanceListModel::where('list_id', $list_id)->with('Maintenances')->first();
        $megjegyzes_munkatars = nl2br($list['megjegyzes_munkatars']);

        $pdf = new MYPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
        $pdf->SetCreator(PDF_CREATOR);
        $pdf->SetAuthor('Charlie Hűtő');
        $pdf->SetTitle('Útmutató');
        $pdf->SetSubject('Útmutató');
        $pdf->SetKeywords('charliehuti, utmutato');
        $pdf->setPrintHeader(true);
        $pdf->setHeaderFont(Array(PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN));
        $pdf->setFooterFont(Array(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA));
        $pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);
        $pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
        $pdf->SetHeaderMargin(0);
        $pdf->SetFooterMargin(0);
        $pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);
        if (@file_exists(dirname(__FILE__).'/lang/eng.php')) {
            require_once(dirname(__FILE__).'/lang/eng.php');
            $pdf->setLanguageArray($l);
        }
        $pdf->setFontSubsetting(true);
        $pdf->SetFont('freesans', '', 13, '', false);

        $pdf->AddPage();

        $tagvs_p = array('p' => array(0 => array('h' => 0, 'n' => 0), 1 => array('h' => 0, 'n' => 0)));
        $pdf->setHtmlVSpace($tagvs_p);

        $html1  = '<h2 style="text-align:center;"><u>Karbantartási útmutató</u></h2>';
        $html1 .= '<h6><span style="color:#1176DE">Kiállítás dátuma:</span> '.$list['datum'].'</h6>';
        $html1 .= '<hr>';
        $pdf->writeHTMLCell(0, 0, '', '', $html1, 0, 1, 0, true, '', true); 
        /* -------------------------------------------------------------------- */
        $html2 = '<h6>'.$list['munkatars'].' részére!</h6>';
        $html2 .= '<p style="font-size:13px;">'.$megjegyzes_munkatars.'</p><br>';
        $pdf->writeHTMLCell(0, 0, '', '', $html2, 0, 1, 0, true, '', true);
        /* -------------------------------------------------------------------- */
        $html3 = '<h6>Részletes:</h6>';

        $html3 .= '<table style="font-size:12px; border-spacing: 0 5px;" cellspacing="0" cellpadding="5"><thead class="padding:10px;">';
        $html3 .= '<tr style="background-color: #eaeaea; font-weight:bold; padding:10px;">';
            $html3 .= '<th style="text-align:center; border: 1px solid #d1d1d1">Cégnév</th>';
            $html3 .= '<th style="text-align:center; border: 1px solid #d1d1d1">Kapcsolattartó</th>';
            $html3 .= '<th style="text-align:center; border: 1px solid #d1d1d1">Kamra címe</th>';
            $html3 .= '<th style="text-align:center; border: 1px solid #d1d1d1">GPS koordináták</th>';
        $html3 .= '</tr></thead><tbody style="">';

        $i = 1;
        foreach($list['maintenances'] as $l){
            $bg = ($i % 2 == 0 ) ? 'background-color:#efefef;' : '';
            $html3 .= '<tr>';
                $html3 .= '<td style="text-align:left; border: 1px solid #d1d1d1; padding:3px; '.$bg.'">'.$l['client']['cegnev'].'</td>';
                $html3 .= '<td style="text-align:center; border: 1px solid #d1d1d1; padding:3px; '.$bg.'">'.$l['client']['kapcs_nev'].' <br><b>('.$l['client']['kapcs_telefon'].')</b></td>';
                $html3 .= '<td style="text-align:left; border: 1px solid #d1d1d1; padding:3px; '.$bg.'">'.$l['client']['kamra_cim'].'</td>';
                $html3 .= '<td style="text-align:left; border: 1px solid #d1d1d1; padding:3px; '.$bg.'">'.$l['client']['kamra_latitude'].', '.$l['client']['kamra_longitude'].'</td>';
            $html3 .= '</tr>';
            $i++;
        }
        
        $html3 .= '</tbody></table>';
        $pdf->writeHTMLCell(0, 0, '', '', $html3, 0, 1, 0, true, '', true);

        $filename = 'charliehuto_'.$list['list_id'].'_'.$list['datum'].'_'.rand(0,999999999);
        
        ob_end_clean();
        $pdf->Output(getcwd().'/pdf/'.$filename.'.pdf', 'F');
        //$pdf->Output('asd.pdf', 'I');
        // F a create

        $return = [
            'url' => SITE_URL_PUBLIC.'pdf/'.$filename.'.pdf',
            'file' => $filename.'.pdf'
        ];
        return $return;
    }

    public function checked($maintenance_id)
    {
        if($_SERVER['REQUEST_METHOD'] == 'POST' AND $_POST['API_SECRET'] == API_SECRET){
            if($maintenance_id > 0 AND isset($maintenance_id)){
                $maintenance = MaintenanceModel::where('maintenance_id', $maintenance_id)->first();
                $maintenance->elvegezve_datum = date("Y-m-d");
                $maintenance->elvegezve = 1;
                $maintenance->save();
                $n = new MaintenanceModel;
                $n->list_id = -1;
                $n->client_id = $maintenance->client_id;
                $n->datum = Clients::calcKovKarbantartas(date("Y-m-d"));
                $n->save();
                $c = ClientsModel::where('client_id', $maintenance->client_id)->first();
                $c->kovetkezo_karbantartas = Clients::calcKovKarbantartas(date("Y-m-d"));
                $c->utolso_karbantartas = date("Y-m-d");
                $c->save();
                if($maintenance){
                    http_response_code(200);
                    echo json_encode(['success' => 'Sikeres módosítás!']);
                }else{
                    http_response_code(200);
                    echo json_encode(['error' => 'A módosítás nem sikerült!']);
                }
            }else{
                http_response_code(200);
                echo json_encode(['error' => 'Hiányzó paraméter!']);
            }
            
        }else{
            http_response_code(405);
            echo json_encode(['error' => 'Bad request']);
        }
    }

    public function deletefromlist($maintenance_id)
    {
        if($_SERVER['REQUEST_METHOD'] == 'POST' AND $_POST['API_SECRET'] == API_SECRET){
            if($maintenance_id > 0 AND isset($maintenance_id)){
                $old = $_POST['list_id'];
                $maintenance = MaintenanceModel::where('maintenance_id', $maintenance_id)->first();
                $maintenance->list_id = -1;
                $maintenance->save();
                // pdf edit
                $createpdf = $this->createPDF($old);
                $topdf = MaintenanceListModel::where('list_id', $old)->first();
                $topdf->pdf = $createpdf['url'];
                $topdf->save();

                if($maintenance){
                    http_response_code(200);
                    echo json_encode(['success' => 'Sikeres módosítás!']);
                }else{
                    http_response_code(200);
                    echo json_encode(['error' => 'A módosítás nem sikerült!']);
                }
            }else{
                http_response_code(200);
                echo json_encode(['error' => 'Hiányzó paraméter!']);
            }
            
        }else{
            http_response_code(405);
            echo json_encode(['error' => 'Bad request']);
        }
    }

    public function updateMunkatars($list_id)
    {
        if($_SERVER['REQUEST_METHOD'] == 'POST' AND $_POST['API_SECRET'] == API_SECRET){
            if($list_id > 0 AND isset($list_id)){
                $l = MaintenanceListModel::where('list_id', $list_id)->first();
                $l->munkatars = $_POST['munkatars'];
                $l->save();
                // pdf edit
                $createpdf = $this->createPDF($list_id);
                $topdf = MaintenanceListModel::where('list_id', $list_id)->first();
                $topdf->pdf = $createpdf['url'];
                $topdf->save();

                if($l){
                    http_response_code(200);
                    echo json_encode(['success' => 'Sikeres módosítást!']);
                }else{
                    http_response_code(200);
                    echo json_encode(['error' => 'A módosítás nem sikerült!']);
                }
            }else{
                http_response_code(200);
                echo json_encode(['error' => 'Hiányzó paraméter!']);
            }
            
        }else{
            http_response_code(405);
            echo json_encode(['error' => 'Bad request']);
        }
    }

    public function updateBelso($list_id)
    {
        if($_SERVER['REQUEST_METHOD'] == 'POST' AND $_POST['API_SECRET'] == API_SECRET){
            if($list_id > 0 AND isset($list_id)){
                $l = MaintenanceListModel::where('list_id', $list_id)->first();
                $l->megjegyzes = $_POST['megjegyzes'];
                $l->save();
                if($l){
                    http_response_code(200);
                    echo json_encode(['success' => 'Sikeres módosítást!']);
                }else{
                    http_response_code(200);
                    echo json_encode(['error' => 'A módosítás nem sikerült!']);
                }
            }else{
                http_response_code(200);
                echo json_encode(['error' => 'Hiányzó paraméter!']);
            }
            
        }else{
            http_response_code(405);
            echo json_encode(['error' => 'Bad request']);
        }
    }

    public function updateMegjegyzesMunkatars($list_id)
    {
        if($_SERVER['REQUEST_METHOD'] == 'POST' AND $_POST['API_SECRET'] == API_SECRET){
            if($list_id > 0 AND isset($list_id)){
                $l = MaintenanceListModel::where('list_id', $list_id)->first();
                $l->megjegyzes_munkatars = $_POST['megjegyzes_munkatars'];
                $l->save();

                // pdf edit
                $createpdf = $this->createPDF($list_id);
                $topdf = MaintenanceListModel::where('list_id', $list_id)->first();
                $topdf->pdf = $createpdf['url'];
                $topdf->save();
                
                if($l){
                    http_response_code(200);
                    echo json_encode(['success' => 'Sikeres módosítást!']);
                }else{
                    http_response_code(200);
                    echo json_encode(['error' => 'A módosítás nem sikerült!']);
                }
            }else{
                http_response_code(200);
                echo json_encode(['error' => 'Hiányzó paraméter!']);
            }
            
        }else{
            http_response_code(405);
            echo json_encode(['error' => 'Bad request']);
        }
    }

    public function addToList($list_id)
    {
        if($_SERVER['REQUEST_METHOD'] == 'POST' AND $_POST['API_SECRET'] == API_SECRET){
            if($list_id > 0 AND isset($list_id)){
                $m = MaintenanceModel::where('client_id', $_POST['client_id'])->first();
                $m->list_id = $list_id;
                $m->save();
                // pdf edit
                $createpdf = $this->createPDF($m->list_id);
                $topdf = MaintenanceListModel::where('list_id', $m->list_id)->first();
                $topdf->pdf = $createpdf['url'];
                $topdf->save();

                if($m){
                    http_response_code(200);
                    echo json_encode(['success' => 'Sikeres módosítást!']);
                }else{
                    http_response_code(200);
                    echo json_encode(['error' => 'A módosítás nem sikerült!']);
                }
            }else{
                http_response_code(200);
                echo json_encode(['error' => 'Hiányzó paraméter!']);
            }
            
        }else{
            http_response_code(405);
            echo json_encode(['error' => 'Bad request']);
        }
    }

    public function updateMap($list_id)
    {
        if($_SERVER['REQUEST_METHOD'] == 'POST' AND $_POST['API_SECRET'] == API_SECRET){
            if($list_id > 0 AND isset($list_id)){
                $l = MaintenanceListModel::where('list_id', $list_id)->first();
                $l->utvonal = $_POST['utvonal'];
                $l->save();
                if($l){
                    http_response_code(200);
                    echo json_encode(['success' => 'Sikeres módosítást!']);
                }else{
                    http_response_code(200);
                    echo json_encode(['error' => 'A módosítás nem sikerült!']);
                }
            }else{
                http_response_code(200);
                echo json_encode(['error' => 'Hiányzó paraméter!']);
            }
            
        }else{
            http_response_code(405);
            echo json_encode(['error' => 'Bad request']);
        }
    }

}

class MYPDF extends TCPDF {
    public function Header() {
        $image_file = SITE_URL_PUBLIC.'assets/img/pdf_logo.png';
        $this->Image($image_file, 10, 10, 50, '', 'PNG', '', 'T', false, 300, '', false, false, 0, false, false, false);
        $this->SetFont('helvetica', 'B', 20);
    }
}