<?php
require APPPATH . 'core/MY_RootController.php';

defined('BASEPATH') OR exit('No direct script access allowed');

class Api extends MY_RootController {
    function __construct(){
        parent:: __construct();
        $this->load->model('DAO');
    }

    function careers_get(){
      if($this->get('pId')){
        $response = $this->DAO->selectEntity('tb_careers', array('id_career' =>$this->get('pId'), 'status_career' => 'Activo' ), TRUE);
      }else{
        $response = $this->DAO->selectEntity('tb_careers', array('status_career' => 'Activo'));
      }
      $this->response($response,200);
    }

    function careers_post(){
      $this->form_validation->set_data($this->post());
      $this->form_validation->set_rules('pName', 'Nombre', 'required|max_length[40]|min_length[3]');
      if($this->form_validation->run()){
        $data = array(
          "name_career" => $this->post('pName')
        );
        $response = $this->DAO->saveOrUpdate('tb_careers', $data, $whereClause = null, $returnKey = FALSE );
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

    function careers_put(){

      if($this->get('pId')){

        $data = $this->put();
        $data += ["pId" => $this->get('pId')];

        $this->form_validation->set_data($data);

        $this->form_validation->set_rules('pName', 'Nombre', 'required|max_length[40]|min_length[3]');
        $this->form_validation->set_rules('pId', 'Clave', 'required|callback_valid_career');

        if($this->form_validation->run()){
          $data = array(
            "name_career" => $this->put('pName')
          );
          $response = $this->DAO->saveOrUpdate('tb_careers', $data, array('id_career' => $this->get('pId')));
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

    function careers_delete(){
      if($this->get('pId')){
        if($this->valid_career($this->get('pId'))){
          $data = array(
            "status_career" => "Inactivo"
          );
          $response = $this->DAO->saveOrUpdate('tb_careers', $data, array('id_career' => $this->get('pId')));

        }else{
          $response = array(
            "status"=>"error",
            "message"=>"El campo clave no existe en el banco de datos",
            "validations"=> array(),
            "data"=>null
          );
        }
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


    function materials_get(){
      $response = $this->DAO->selectEntity('tb_materials');
      return $this->response($response,200);
    }

    function materials_post(){
      $this->form_validation->set_rules('pName','Nombre','required|max_length[60]|min_length[4]');
      $this->form_validation->set_rules('pDesc','Descripción','required');
      $this->form_validation->set_rules('pUse','Nombre','required');
      $this->form_validation->set_rules('pPenal','En caso de extravio','required');
      $this->form_validation->set_rules('pType','Tipo de material requerido','required|in_list[Multiple,Consumible, Herramienta]');
      $this->form_validation->set_rules('pStock','Existencia de materiales','required|is_natural');
      $this->form_validation->set_rules('pImage','Imagen','callback_image_required');

      if($this->form_validation->run()){

        $config['upload_path']= "./files/materials/";
        $config['allowed_types'] = "jpg|png|gif|jpeg";
        $config['encrypt_name'] = TRUE;
        $config['file_ext_tolower'] = TRUE;

        $this->load->library('upload',$config);
        if($this->upload->do_upload('pImage')){
          $data = array(
            "name_material" => $this->post('pName'),
            "desc_material"  => $this->post('pDesc'),
            "use_material"  => $this->post('pUse'),
            "penal_material" => $this->post('pPenal'),
            "img_material" => "./files/materials/".$this->upload->data()['file_name'],
            "type_material"=> $this->post('pType'),
            "stock_material"=> $this->post('pStock')
          );

          $response = $this->DAO->saveOrUpdate('tb_materials',$data);
        }else{
          $response = array(
              "status"=>"error",
              "message"=> $this->upload->display_errors(),
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
    //validar que la carrera si exista en la base de datos
    function valid_career($value){
      $career_exists = $this->DAO->selectEntity('tb_careers', array('id_career' => $value));
      if($career_exists['data']){
        return TRUE;
      }else{
        $this->form_validation->set_message('valid_career', 'El campo   {field} no existe en el banco de datos');
        return FALSE;
      }
    }


    function image_required($value){
      if(isset($_FILES['pImage']) && $_FILES['pImage'] && !empty($_FILES['pImage']['name'])){
        return TRUE;
      }else{
        $this->form_validation->set_message('Image_required', 'El campo {field} es requerido');
        return FALSE;
      }
    }



}
