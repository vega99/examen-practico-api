<?php
require APPPATH . 'core/MY_RootController.php';

defined('BASEPATH') OR exit('No direct script access allowed');

class Api extends MY_RootController {
    function __construct(){
        parent:: __construct();
        $this->load->model('DAO');
    }

   function login_post(){
     $this->form_validation->set_data($this->post());
     $this->form_validation->set_rules('pEmail','email','required');
     $this->form_validation->set_rules('pPassword','password','required');

     if($this->form_validation->run()){
         $response = $this->DAO->login($this->post('pEmail'),$this->post('pPassword'));
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

   function web_users_post(){
     $this->form_validation->set_data($this->post());
     $this->form_validation->set_rules('pName','Nombre','required|max_length[120]|min_length[3]');
     $this->form_validation->set_rules('pLastname','Apellidos','required|max_length[120]|min_length[3]');
     $this->form_validation->set_rules('pEmail','Correo','required|max_length[120]|min_length[10]|is_unique[users.email]');
     $this->form_validation->set_rules('pPassword','contraseña','required');

     if($this->form_validation->run()){
       $this->load->library('bcrypt');
       $dataperson = array(
         "name" => $this->post('pName'),
         "lastname"  => $this->post('pLastname'),
         "email" => $this->post('pEmail'),
         "password" => $this->bcrypt->hash_password($this->post('pPassword'))
       );

       $id_user = $this->DAO->saveOrUpdate('users', $dataperson, array(), TRUE);
       // $gen_password = $this->randomPassword();
       // $this->load->library('bcrypt');

       $response = array(
         "email_user" => $this->post('pEmail'),         
         "user" => $id_user['data']
       );
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
}
