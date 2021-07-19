<?php
use Restserver\Libraries\REST_Controller;
defined('BASEPATH') OR exit('No direct script access allowed');

require APPPATH . 'libraries/REST_Controller.php';
require APPPATH . 'libraries/Format.php';

class MY_RootController extends REST_Controller {
    function __construct(){
        parent:: __construct();
        $this->load->model('DAO');
    }
    function randomPassword() {
        $alphabet = "abcdefghijklmnopqrstuwxyzABCDEFGHIJKLMNOPQRSTUWXYZ0123456789";
        $pass = array();
        $alphaLength = strlen($alphabet) - 1;
        for ($i = 0; $i < 8; $i++) {
            $n = rand(0, $alphaLength);
            $pass[] = $alphabet[$n];
        }
        return implode($pass);
    }
    function check_valid_date($param_date){
        if($param_date){
            if (preg_match("/^[0-9]{4}-(0[1-9]|1[0-2])-(0[1-9]|[1-2][0-9]|3[0-1])$/", $param_date)) {
              if(checkdate(substr($param_date, 5, 2), substr($param_date, 8, 2), substr($param_date, 0, 4))){
                return true;
              }else{
                $this->form_validation->set_message('check_valid_date','El campo {field} es incorrecto, formato[yyyy-mm-dd].');
                  return false;
              }
            }else {
              $this->form_validation->set_message('check_valid_date','El campo {field} es incorrecto, formato[yyyy-mm-dd].');
                return false;
            }
        }else{
            $this->form_validation->set_message('check_valid_date','El campo {field} es incorrecto, formato[yyyy-mm-dd].');
            return false;
        }
    }
    //formato 24H
    function check_valid_time($param_time){
        if($param_time){
            if (preg_match("/^(?:2[0-4]|[01][1-9]|10):([0-5][0-9])$/",$param_time)) {
              if($this->validateDate($param_time,'H:i')){
                return true;
              }else{
                $this->form_validation->set_message('check_valid_time','El formato del campo {field} es incorrecto, formato[HH:mm 24hrs].'.$param_time);
                  return false;
              }
            }else {
              $this->form_validation->set_message('check_valid_time','El formato del campo {field} es incorrecto, formato[HH:mm 24hrs]...'.$param_time);
                return false;
            }
        }else{
            $this->form_validation->set_message('check_valid_time','El campo {field} es requerido, formato[HH:mm 24hrs].');
            return false;
        }
    }
    function get_text_day_date($day_num){
      $text_days = array("DOMINGO","LUNES","MARTES","MIERCOLES","JUEVES","VIERNES","SABADO");
      if($day_num >=0 && $day_num <= 6){
        return $text_days[$day_num];
      }else{
        return "";
      }
    }
    function genre_valid($value){
        $genres = array("Femenino","Masculino");
        if(in_array($value,$genres)){
            return true;
        }else{
            $this->form_validation->set_message('genre_valid','El campo {field} es requerido y sólo se permite (Femenino o Masculino) como valor.');
            return false;
        }
    }

    function validate_curp($valor) {
     	if(strlen($valor)==18){
        	$letras     = substr($valor, 0, 4);
        	$numeros    = substr($valor, 4, 6);
        	$sexo       = substr($valor, 10, 1);
        	$mxState    = substr($valor, 11, 2);
        	$letras2    = substr($valor, 13, 3);
        	$homoclave  = substr($valor, 16, 2);
         	if(ctype_alpha($letras) && ctype_alpha($letras2) && ctype_digit($numeros) && ctype_alnum($homoclave) && ctype_alpha($mxState) && $this->check_sex_curp($sexo)){
            	return true;
        	}
             $this->form_validation->set_message('validate_curp','El campo {field} no contiene un formato valido (18 Caracteres Ejem. (CURP920830HQTRRL09)).');
    	return false;
     	}else{
             $this->form_validation->set_message('validate_curp','El campo {field} no contiene un formato valido (18 Caracteres Ejem. (CURP920830HQTRRL09)).');
        	return false;
    	}
	}
	function check_sex_curp($sexo){
	    $sexoCurp = ['H','M'];
	    if(in_array(strtoupper($sexo),$sexoCurp)){
	       return true;
	    }
	    return false;
	}
}
