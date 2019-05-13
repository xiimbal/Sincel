<?php

include_once ("Catalogo.class.php");

class EmpresaProductoSAT {
    private $IdEmpresaProductoSAT;
    private $IdDatosFacturacionEmpresa;
    private $IdClaveProdServ;
    private $IdUnidadMedida;
    private $UsuarioCreacion;
    private $FechaCreacion;
    private $UsuarioUltimaModificacion;
    private $FechaUltimaModificacion;
    private $Pantalla;
    private $tabla = "k_empresaproductosat";
    private $nombreId = "IdEmpresaProductoSAT";
    
    public function getRegistroById($id) {
        $consulta = ("SELECT * FROM $this->tabla WHERE $this->nombreId ='" . $id . "'");
        $catalogo = new Catalogo();
        $query = $catalogo->obtenerLista($consulta);
        if ($rs = mysql_fetch_array($query)) {
            $this->IdEmpresaProductoSAT = $rs['IdEmpresaProductoSAT'];
            $this->IdDatosFacturacionEmpresa = $rs['IdDatosFacturacionEmpresa'];
            $this->IdClaveProdServ = $rs['IdClaveProdServ'];
            $this->IdUnidadMedida = $rs['IdUnidadMedida'];
            $this->UsuarioCreacion = $rs['UsuarioCreacion'];
            $this->FechaCreacion = $rs['FechaCreacion'];
            $this->UsuarioUltimaModificacion = $rs['UsuarioUltimaModificacion'];
            $this->FechaUltimaModificacion = $rs['FechaUltimaModificacion'];
            $this->Pantalla = $rs['Pantalla'];
        }
        return $query;
    }
    
    public function newRegistro() {
        $consulta = ("INSERT INTO $this->tabla(IdEmpresaProductoSAT,IdDatosFacturacionEmpresa,IdClaveProdServ,IdUnidadMedida,
            UsuarioCreacion,FechaCreacion,UsuarioUltimaModificacion,FechaUltimaModificacion,Pantalla)
            VALUES(0,$this->IdDatosFacturacionEmpresa,$this->IdClaveProdServ,$this->IdUnidadMedida,'$this->UsuarioCreacion',
            NOW(),'$this->UsuarioUltimaModificacion',NOW(),'$this->Pantalla')");
        //echo $consulta;
        $catalogo = new Catalogo();
        $this->IdEmpresaProductoSAT = $catalogo->insertarRegistro($consulta);
        if ($this->IdEmpresaProductoSAT != NULL && $this->IdEmpresaProductoSAT != 0) {
            return true;
        }
        return false;
    }
    
    public function editRegistro() {
        $where = "$this->nombreId =" . $this->IdEmpresaProductoSAT;
        $consulta = ("UPDATE $this->tabla SET IdDatosFacturacionEmpresa = $this->IdDatosFacturacionEmpresa,IdClaveProdServ = $this->IdClaveProdServ,
            IdUnidadMedida = $this->IdUnidadMedida,
            UsuarioUltimaModificacion = '$this->UsuarioUltimaModificacion',FechaUltimaModificacion = NOW(),Pantalla = '$this->Pantalla' "
                . " WHERE $where;");
        $catalogo = new Catalogo();
        $query = $catalogo->insertarRegistro($consulta);
        if ($query == 1) {
            return true;
        }
        return false;
    }
    
    function deleteRegistro() {
        $catalogo = new Catalogo();
        $where = "$this->nombreId = $this->IdEmpresaProductoSAT";
        $consulta = ("DELETE FROM `$this->tabla` WHERE $where;");
        $query = $catalogo->insertarRegistro($consulta, $this->tabla, $where);
        if ($query == 1) {
            return true;
        }
        return false;
    }
    
    function getDetallesByEmpresa(){
        $consulta = "SELECT IdEmpresaProductoSAT FROM $this->tabla WHERE IdDatosFacturacionEmpresa = $this->IdDatosFacturacionEmpresa ";
        $catalogo = new Catalogo();
        if (isset($this->empresa)) {
            $catalogo->setEmpresa($this->empresa);
        }
        $query = $catalogo->obtenerLista($consulta);
        $array = array();
        while($rs = mysql_fetch_array($query)){
            array_push($array, $rs[0]);
        }
        return $array;
    }
    
    function getIdEmpresaProductoSAT() {
        return $this->IdEmpresaProductoSAT;
    }

    function getIdDatosFacturacionEmpresa() {
        return $this->IdDatosFacturacionEmpresa;
    }

    function getIdClaveProdServ() {
        return $this->IdClaveProdServ;
    }

    function getIdUnidadMedida() {
        return $this->IdUnidadMedida;
    }

    function getUsuarioCreacion() {
        return $this->UsuarioCreacion;
    }

    function getFechaCreacion() {
        return $this->FechaCreacion;
    }

    function getUsuarioUltimaModificacion() {
        return $this->UsuarioUltimaModificacion;
    }

    function getFechaUltimaModificacion() {
        return $this->FechaUltimaModificacion;
    }

    function getPantalla() {
        return $this->Pantalla;
    }

    function setIdEmpresaProductoSAT($IdEmpresaProductoSAT) {
        $this->IdEmpresaProductoSAT = $IdEmpresaProductoSAT;
    }

    function setIdDatosFacturacionEmpresa($IdDatosFacturacionEmpresa) {
        $this->IdDatosFacturacionEmpresa = $IdDatosFacturacionEmpresa;
    }

    function setIdClaveProdServ($IdClaveProdServ) {
        $this->IdClaveProdServ = $IdClaveProdServ;
    }

    function setIdUnidadMedida($IdUnidadMedida) {
        $this->IdUnidadMedida = $IdUnidadMedida;
    }

    function setUsuarioCreacion($UsuarioCreacion) {
        $this->UsuarioCreacion = $UsuarioCreacion;
    }

    function setFechaCreacion($FechaCreacion) {
        $this->FechaCreacion = $FechaCreacion;
    }

    function setUsuarioUltimaModificacion($UsuarioUltimaModificacion) {
        $this->UsuarioUltimaModificacion = $UsuarioUltimaModificacion;
    }

    function setFechaUltimaModificacion($FechaUltimaModificacion) {
        $this->FechaUltimaModificacion = $FechaUltimaModificacion;
    }

    function setPantalla($Pantalla) {
        $this->Pantalla = $Pantalla;
    }

}
