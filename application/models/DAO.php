<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class DAO extends CI_Model {
    function __construct(){
        parent:: __construct();
    }
    function selectEntity($entity,$params =  null,$isUnique = FALSE){
    	if($params){
    		$this->db->where($params);
    	}
    	$query = $this->db->get($entity);
    	if($this->db->error()['message']!=''){
    		$response = array(
    			"status"=>"error",
    			"message"=>$this->db->error()['message'],
                "validations" =>array(),
    			"data"=>null
    		);
    	}else{
            $response = array(
            "status"=>"success",
    			   "message"=>"Información cargada correctamente",
            "validations" =>array(),
    			"data"=> $isUnique ?  $query->row() : $query->result()
    		);
    	}
    	return $response;
    }
    function sqlQuery($sql, $params = array(), $isUnique = FALSE){
        $query =  $this->db->query($sql,$params ? $params : null);

        if($this->db->error()['message']!=''){
    		$response = array(
    			"status"=>"error",
    			"message"=>$this->db->error()['message'],
    			"data"=>null
    		);
    	}else{
    		$response = array(
    			"status"=>"success",
    			"message"=>"Información cargada correctamente",
    			"data"=> $isUnique ?  $query->row() : $query->result()
    		);
    	}
    	return $response;
    }

    function deleteEntity($entity,$whereClause =  array()){
        $this->db->where($whereClause);
        $this->db->delete($entity);
        if($this->db->error()['message']!=''){
    		$response = array(
    			"status"=>"error",
    			"message"=>$this->db->error()['message'],
    			"data"=>null
    		);
    	}else{
            $response = array(
    			"status"=>"success",
    			"message"=> "Información borrada correctamente",
    			"data"=>null
    		);
        }
        return $response;
    }

    function saveOrUpdate($entity,$data,$whereClause = null, $returnKey = FALSE){
    	if($whereClause){
    		$this->db->where($whereClause);
            $this->db->update($entity,$data);
    	}else{
        $this->db->insert($entity,$data);
      }
    	if($this->db->error()['message']!=''){
    		$response = array(
    			"status"=>"error",
    			"message"=>$this->db->error()['message'],
    			"data"=>null
    		);
    	}else{
        if($whereClause){
            $msg = "Información actualizada correctamente!";
        }else{
            $msg = "Información registrada correctamente!";
        }
    		$response = array(
    			"status" => "success",
    			"message" => $msg,
          "data" => null
    		);
        if($returnKey){
          $response['data'] = $this->db->insert_id();
        }
    	}
    	return $response;
    }

    function login($email,$password){
        $this->db->where('email', $email);
        $user_exists = $this->db->get('users')->row();
        if($user_exists){
            $this->load->library('bcrypt');
            if($this->bcrypt->check_password($password, $user_exists->password)){
                  $response = array(
                      "status" => "success",
                      "message" => "Auntenticación Correctamente",
                      "validations" => array(),
                      "data" => array(
                          "user_key" => $user_exists->id,
                          "user_email" => $user_exists->email,
                          "user_fullname"=> $user_exists->name.' '.$user_exists->lastname,
                          "token" => $this->bcrypt->hash_password($user_exists->password)
                      )
                  );
            }else{
                $response = array(
                    "status" => "error",
                    "message" => "La clave ingresada no es correcta",
                    "validations" => array(),
                    "data" => null
                );
            }
        }else{
            $response = array(
                "status" => "error",
                "message" => "El correo ingresado no existe",
                "validations" => array(),
                "data" => null
            );
        }
        return $response;
    }

    function init_transaction(){
        $this->db->trans_begin();
    }

    function check_transaction(){
        if($this->db->trans_status()){
            $this->db->trans_commit();
            $response = array(
                "status" => "success",
                "message" => "Proceso completado correctamente",
                "data" => null
            );
        }else{
            $this->db->trans_rollback();
            $response = array(
                "status" => "error",
                "message" => "Error al completar el proceso",
                "data" => null
            );
        }
        return $response;
    }



}
