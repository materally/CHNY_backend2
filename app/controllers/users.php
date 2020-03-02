<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: X-API-KEY, Origin, X-Requested-With, Content-Type, Accept, Access-Control-Request-Method");
header("Access-Control-Allow-Credentials: true");
header("Content-Type: application/json; charset=UTF-8");

class Users extends KM_Controller {
    public function index($user_id = 0)
    {
        if($_SERVER['REQUEST_METHOD'] == 'GET' AND $_GET['API_SECRET'] == API_SECRET){
            // get user(s)
            if(empty($user_id) OR $user_id === 0){
                // get all users
                $user = UserModel::all();
            }else{
                $user = UserModel::find($user_id);
            }
            http_response_code(200);
            echo json_encode($user);
        }else{
            http_response_code(405);
            echo json_encode(['error' => 'Bad request']);
        }
    }

    public function login($email, $password)
    {
        if($_SERVER['REQUEST_METHOD'] == 'POST' AND $_POST['API_SECRET'] == API_SECRET){
            // get user
            $pw = sha1($password);
            $user = UserModel::where('email', $email)->where('password', $pw)->first();
            if($user === NULL){
                http_response_code(200);
                echo json_encode(['error' => 'A felhasználó nem létezik vagy rossz e-mail cím / jelszó páros!']);
                return;
            }
            $return = [
                'user_id'   => $user['user_id'],
                'email'     => $user['email'],
                'token'     => $user['token'],
                'scope'     => $user['scope'],
                'name'      => $user['name'],
            ];
            http_response_code(200);
            echo json_encode($return);
        }else{
            http_response_code(405);
            echo json_encode(['error' => 'Bad request']);
        } 
    }
}