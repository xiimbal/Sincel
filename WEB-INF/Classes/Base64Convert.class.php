<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

class Base64Convert {

    private $file;
    private $string;
    private $base64;

    public function Convertbase64File() {
        $file_data = file_get_contents($this->file);
        $encode_config_file = base64_encode($file_data);
        $this->base64=$encode_config_file;
        return$encode_config_file;
    }
    
    public function Convertbase64String() {
        $this->base64=base64_encode($this->string);
        return$this->base64;
    }

    public function getString() {
        return $this->string;
    }

    public function setString($string) {
        $this->string = $string;
    }

        public function getBase64() {
        return $this->base64;
    }

    public function setBase64($base64) {
        $this->base64 = $base64;
    }

    public function getFile() {
        return $this->file;
    }

    public function setFile($file) {
        $this->file = $file;
    }

}

?>