<?php
require APPPATH . 'core/MY_RootController.php';

defined('BASEPATH') OR exit('No direct script access allowed');

class Api extends MY_RootController {
    function __construct(){
        parent:: __construct();
        $this->load->model('DAO');
    }

    function questions_get(){
      if($this->get('pId')){
        $response = $this->DAO->selectEntity('questions', array('user_id' => $this->get('pId')));
      }else{
        $response = $this->DAO->selectEntity('questions');
      }
      $this->response($response,200);
    }

    function questions_post(){
      $this->form_validation->set_data($this->post());
      $this->form_validation->set_rules('pTitle', 'título', 'required|min_length[6]');
      $this->form_validation->set_rules('pDescription', 'descripción', 'required|min_length[6]');
      $this->form_validation->set_rules('pUser', 'usuario', 'required|callback_valid_user');

      if($this->form_validation->run()){
        $data = array(
          "title" => $this->post('pTitle'),
          "description" => $this->post('pDescription'),
          "user_id" => $this->post('pUser')
        );
        $response = $this->DAO->saveOrUpdate('questions', $data, $whereClause = null, $returnKey = FALSE );
      }else{
         $response = array(
           "status"=>"error",
           "message"=>"Información enviada incorrectamente.",
           "validations"=>$this->form_validation->error_array(),
           "data"=>null
       );
      }
      $this->response($response,200);
    }

    function questions_put(){

      if($this->get('pId')){

        $data = $this->put();
        $data += ["pId" => $this->get('pId')];

        $this->form_validation->set_data($data);
        $this->form_validation->set_rules('pTitle', 'título', 'required|min_length[6]');
        $this->form_validation->set_rules('pDescription', 'descripción', 'required|min_length[6]');
        $this->form_validation->set_rules('pUser', 'usuario', 'required|callback_valid_user');
        $this->form_validation->set_rules('pId', 'Clave', 'required|callback_valid_question');

        if($this->form_validation->run()){
          $data = array(
            "title" => $this->put('pTitle'),
            "description" => $this->put('pDescription'),
            "user_id" => $this->put('pUser')
          );
          $response = $this->DAO->saveOrUpdate('questions', $data, array('id' => $this->get('pId')));
        }else{
          $response = array(
            "status"=>"error",
            "message"=>"Información enviada incorrectamente.",
            "validations"=>$this->form_validation->error_array(),
            "data"=>null
          );
        }
        $this->response($response,200);

      }else{
        $response = array(
          "status"=>"error",
          "message"=>"Parámetro pid no enviado",
          "validations"=> array(),
          "data"=>null
        );
      }
      $this->response($response, 200);
    }

      function markfavorite_put(){
        $data = $this->put();
        $this->form_validation->set_data($data);
        $this->form_validation->set_rules('pQuestion', 'pregunta', 'required|callback_valid_question');
        $this->form_validation->set_rules('pAnswer', 'respuesta', 'required|callback_valid_answer');

        if($this->form_validation->run()){
          $data = array(
            "answer_id" => $this->put('pAnswer')
          );
          $response = $this->DAO->saveOrUpdate('questions', $data, array('id' => $this->put('pQuestion')));
        }else{
          $response = array(
            "status"=>"error",
            "message"=>"Información enviada incorrectamente.",
            "validations"=>$this->form_validation->error_array(),
            "data"=>null
          );
        }
        $this->response($response,200);
    }


    function valid_user($value){
      $user_exists = $this->DAO->selectEntity('users', array('id' => $value));
      if($user_exists['data']){
        return TRUE;
      }else{
        $this->form_validation->set_message('valid_user', 'El campo {field} no existe en el banco de datos');
        return FALSE;
      }
    }
    function valid_question($value){
      $question_exists = $this->DAO->selectEntity('questions', array('id' => $value));
      if($question_exists['data']){
        return TRUE;
      }else{
        $this->form_validation->set_message('valid_question', 'El campo {field} no existe en el banco de datos');
        return FALSE;
      }
    }

    function valid_answer($value){
      $answer_exists = $this->DAO->selectEntity('answers', array('id' => $value));
      if($answer_exists['data']){
        return TRUE;
      }else{
        $this->form_validation->set_message('valid_answer', 'El campo {field} no existe en el banco de datos');
        return FALSE;
      }
    }



}
