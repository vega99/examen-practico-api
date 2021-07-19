     $this->form_validation->set_data($this->post());

     if($this->form_validation->run()==FALSE){

     }else{
        $response = array(
          "status"=>"error",
          "message"=>"Información enviada incorrectamente.",
          "validations"=>$this->form_validation->error_array(),
          "data"=>null
      );
     }
     $this->response($response,200);
