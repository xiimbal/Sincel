<?php

include_once("Catalogo.class.php");

/**
 * Description of Log
 *
 * @author MAGG
 */
class Log {

    private $IdQuery;
    private $Consulta;
    private $Fecha;
    private $Seccion;
    private $IdUsuario;
    private $Tipo;
    
    public function newRegistro() {
        $consulta = "INSERT INTO c_log(IdQuery, Consulta, Fecha, IdUsuario, Seccion, Tipo) 
            VALUES(0,'".  str_replace("'", "´", $this->Consulta)."',NOW(),$this->IdUsuario,'$this->Seccion','$this->Tipo');";
        $catalogo = new Catalogo();
        if (isset($this->empresa)) {
            $catalogo->setEmpresa($this->empresa);
        }
        $this->IdQuery = $catalogo->insertarRegistro($consulta);
        if ($this->IdQuery != NULL && $this->IdQuery != 0) {
            return true;
        }
        return false;
    }
    
    public function getIdQuery() {
        return $this->IdQuery;
    }

    public function setIdQuery($IdQuery) {
        $this->IdQuery = $IdQuery;
    }

    public function getConsulta() {
        return $this->Consulta;
    }

    public function setConsulta($Consulta) {
        $this->Consulta = $Consulta;
    }

    public function getFecha() {
        return $this->Fecha;
    }

    public function setFecha($Fecha) {
        $this->Fecha = $Fecha;
    }

    public function getSeccion() {
        return $this->Seccion;
    }

    public function setSeccion($Seccion) {
        $this->Seccion = $Seccion;
    }

    public function getIdUsuario() {
        return $this->IdUsuario;
    }

    public function setIdUsuario($IdUsuario) {
        $this->IdUsuario = $IdUsuario;
    }

    public function getTipo() {
        return $this->Tipo;
    }

    public function setTipo($Tipo) {
        $this->Tipo = $Tipo;
    }

}

?>
