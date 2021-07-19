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

   function login_mobile_post(){
     $this->form_validation->set_data($this->post());
     $this->form_validation->set_rules('pEmail','email','required');
     $this->form_validation->set_rules('pPassword','password','required');

     if($this->form_validation->run()){
         $response = $this->DAO->login($this->post('pEmail'),$this->post('pPassword'), "mobile");
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
   function users_get(){
     $sql = "SELECT id_person, concat(name_person,'',lastname_person) as fullname_person, email_person, gender_person, identifier_person,
              phone_person, role_person, IF((SELECT COUNT(*) FROM tb_users WHERE user_person = id_person) > 0, 'Con acceso','Sin acceso')
               as 'access_person', IF(person_career IS NULL,'',(SELECT name_career FROM tb_careers WHERE person_career = id_career)) as career_person
              FROM  tb_persons";
     $response = $this->DAO->sqlQuery($sql);

     $this->response($response,200);
   }

   function web_users_post(){
     $this->form_validation->set_data($this->post());
     $this->form_validation->set_rules('pName','Nombre','required|max_length[120]|min_length[3]');
     $this->form_validation->set_rules('pLastname','Apellidos','required|max_length[120]|min_length[3]');
     $this->form_validation->set_rules('pGender','Género','required|in_list[F,M]');
     $this->form_validation->set_rules('pEmail','Correo','required|max_length[120]|min_length[10]|is_unique[tb_persons.email_person]');
     $this->form_validation->set_rules('pIdentifier','Identificación[RFC/Num. Empleado]','required|max_length[18]|min_length[3]|is_unique[tb_persons.identifier_person]');
     $this->form_validation->set_rules('pPhone','Teléfono','required|max_length[18]');

     if($this->form_validation->run()){
       $this->DAO->init_transaction();

       $dataperson = array(
         "name_person" => $this->post('pName'),
         "lastname_person"  => $this->post('pLastname'),
         "gender_person" => $this->post('pGender'),
         "email_person" => $this->post('pEmail'),
         "identifier_person" => $this->post('pIdentifier'),
         "phone_person" => $this->post('pPhone'),
         "role_person" => 'Almacenista'
       );

       $id_person = $this->DAO->saveOrUpdate('tb_persons', $dataperson, array(), TRUE);
       $gen_password = $this->randomPassword();
       $this->load->library('bcrypt');

       $data_user = array(
         "email_user" => $this->post('pEmail'),
         "password_user" => $this->bcrypt->hash_password($gen_password),
         "user_person" => $id_person['data']
       );

       $respuesta = $this->DAO->saveOrUpdate('tb_users', $data_user);

       if($this->DAO->check_transaction()['status'] == "success"){
          $response = array(
            "status" => "success",
            "message" => "Usuario creado correctamente",
            "validations" => array(),
            "data" => array(
              "key_password" => $gen_password,
              "user_email" => $this->post('pEmail')
            )
          );
       }else
         $response = array(
           "status"=>"error",
           "message"=>"Error al crear el usuario, intente nuevamente, si el error persiste contácte al administrador",
           "validations"=>array(),
           "data"=>array()
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
   function mobile_users_post(){
     $this->form_validation->set_data($this->post());
     $this->form_validation->set_rules('pName','Nombre','required|max_length[120]|min_length[3]');
     $this->form_validation->set_rules('pLastname','Apellidos','required|max_length[120]|min_length[3]');
     $this->form_validation->set_rules('pGender','Género','required|in_list[F,M]');
     $this->form_validation->set_rules('pEmail','Correo','required|max_length[120]|min_length[10]|is_unique[tb_persons.email_person]');
     $this->form_validation->set_rules('pIdentifier','Identificación[RFC/Num. Empleado]','required|max_length[18]|min_length[3]|is_unique[tb_persons.identifier_person]');
     $this->form_validation->set_rules('pPhone','Teléfono','required|max_length[18]');
     $this->form_validation->set_rules('pRole','Rol','required|in_list[Estudiante,Personal académico/administrativo]');


     if($this->form_validation->run()){
       if($this->post('pRole') == "Estudiante"){
         $this->form_validation->set_rules('pCareer','Carrera','callback_career_exists');
          if(!$this->form_validation->run()){
              $response = array(
                "status"=>"error",
                "message"=>"Información enviada incorrectamente.",
                "validations"=>$this->form_validation->error_array(),
                "data"=>null
            );
            $this->response($response,200);
          }
       }

       $this->DAO->init_transaction();

       $dataperson = array(
         "name_person" => $this->post('pName'),
         "lastname_person"  => $this->post('pLastname'),
         "gender_person" => $this->post('pGender'),
         "email_person" => $this->post('pEmail'),
         "identifier_person" => $this->post('pIdentifier'),
         "phone_person" => $this->post('pPhone'),
         "role_person" => $this->post('pRole'),
         "person_career" => $this->post('pRole') == "Estudiante" ? $this->post('pCareer') : null
       );

       $id_person = $this->DAO->saveOrUpdate('tb_persons', $dataperson, array(), TRUE);
       $gen_password = $this->randomPassword();
       $this->load->library('bcrypt');

       $data_user = array(
         "email_user" => $this->post('pEmail'),
         "password_user" => $this->bcrypt->hash_password($gen_password),
         "user_person" => $id_person['data']
       );

       $respuesta = $this->DAO->saveOrUpdate('tb_users', $data_user);

       if($this->DAO->check_transaction()['status'] == "success"){
          $response = array(
            "status" => "success",
            "message" => "Usuario creado correctamente",
            "validations" => array(),
            "data" => array(
              "email" => $this->post('pEmail'),
              "key_password" => $gen_password
            )
          );
        }else{
          $response = array(
            "status"=>"error",
            "message"=>"Error al crear el usuario, intente nuevamente, si el error persiste contácte al administrador",
            "validations"=>array(),
            "data"=>array()
        );
        }
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

   function career_exists($value){
     $career_exists = $this->DAO->selectEntity('tb_careers', array('id_career' => $value, 'status_career'=> 'Activo'));
     if($career_exists['data']){
       return TRUE;
     }else{
       $this->form_validation->set_message('career_exists', 'El campo   {field} no existe en el banco de datos');
       return FALSE;
     }
   }
}
