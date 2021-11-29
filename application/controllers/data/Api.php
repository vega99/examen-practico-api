<?php
require APPPATH . 'core/MY_RootController.php';

defined('BASEPATH') OR exit('No direct script access allowed');

class Api extends MY_RootController {
    function __construct(){
        parent:: __construct();
        $this->load->model('DAO');
    }

    function data_get(){
        $answers = $this->DAO->sqlQuery('SELECT a.*,u.name as name, u.lastname as lastname, u.email as email FROM answers as a  join users as u on a.user_id = u.id  order by a.created_at desc');
        $questions = $this->DAO->sqlQuery('SELECT q.*, u.name as name, u.lastname as lastname, u.email as email, u.id as id_user FROM questions as q join users as u on q.user_id = u.id order by q.created_at desc');
        $response = array(
          "status" =>'success',
          "message" => 'Iformación cargada correctamente',
          "validations" => null,
          "data" => array(
            "questions" => $questions["data"],
            "answers" => $answers["data"]
          )
        );
        $this->response($response,200);
    }

}
