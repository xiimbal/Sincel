<?php

include_once("Catalogo.class.php");

class TipoMoneda {
    
    private $IdTipoMoneda;
    private $TipoMoneda;
    private $Abreviatura;
    private $Activo;
    private $UsuarioCreacion;
    private $FechaCreacion;
    private $UsuarioUltimaModificacion;
    private $FechaUltimaModificacion;
    private $Pantalla;

    public function getRegistroById($id) {
        $consulta = ("SELECT * FROM c_tipomoneda WHERE IdTipoMoneda='" . $id . "'");
        
        $catalogo = new Catalogo();
        if (isset($this->empresa)) {
            $catalogo->setEmpresa($this->empresa);
        }
        $query = $catalogo->obtenerLista($consulta);
        if ($rs = mysql_fetch_array($query)) {
            $this->IdTipoMoneda = $rs['IdTipoMoneda'];
            $this->TipoMoneda = $rs['TipoMoneda'];
            $this->Abreviatura = $rs['Abreviatura'];
            $this->Activo = $rs['Activo'];
            $this->UsuarioCreacion = $rs['UsuarioCreacion'];
            $this->FechaCreacion = $rs['FechaCreacion'];
            $this->UsuarioUltimaModificacion = $rs['UsuarioUltimaModificacion'];
            $this->FechaUltimaModificacion = $rs['FechaUltimaModificacion'];
            $this->Pantalla = $rs['Pantalla'];
        }
        return $query;
    }
    
    public function getRegistroByAbreviatura($abreviatura){
        $consulta = ("SELECT * FROM c_tipomoneda WHERE Abreviatura='$abreviatura'");
        $catalogo = new Catalogo();
        if (isset($this->empresa)) {
            $catalogo->setEmpresa($this->empresa);
        }
        $query = $catalogo->obtenerLista($consulta);
        if ($rs = mysql_fetch_array($query)) {
            $this->IdTipoMoneda = $rs['IdTipoMoneda'];
            $this->TipoMoneda = $rs['TipoMoneda'];
            $this->Abreviatura = $rs['Abreviatura'];
            $this->Activo = $rs['Activo'];
            $this->UsuarioCreacion = $rs['UsuarioCreacion'];
            $this->FechaCreacion = $rs['FechaCreacion'];
            $this->UsuarioUltimaModificacion = $rs['UsuarioUltimaModificacion'];
            $this->FechaUltimaModificacion = $rs['FechaUltimaModificacion'];
            $this->Pantalla = $rs['Pantalla'];
            return true;
        }
        return false;
    }
    
    public function newRegistro() {
        $consulta = ("INSERT INTO c_tipomoneda(IdTipoMoneda,TipoMoneda,Abreviatura,Activo,UsuarioCreacion,
            FechaCreacion,UsuarioUltimaModificacion,FechaUltimaModificacion,Pantalla)
            VALUES(0,'$this->TipoMoneda','$this->Abreviatura'," . $this->Activo . ",'" . $this->UsuarioCreacion . "',
                NOW(),'" . $this->UsuarioUltimaModificacion . "',NOW(),'" . $this->Pantalla . "')");
        //echo $consulta;
        $catalogo = new Catalogo();
        $this->IdTipoGasto = $catalogo->insertarRegistro($consulta);
        if ($this->IdTipoGasto != NULL && $this->IdTipoGasto != 0) {
            return true;
        }
        return false;
    }
    
    public function editRegistro() {
        $tabla = "c_tipomoneda";
        $where = "IdTipoMoneda=" . $this->IdTipoMoneda;
        $consulta = ("UPDATE $tabla SET TipoMoneda = '$this->TipoMoneda', Abreviatura = '$this->Abreviatura',
            Activo = " . $this->Activo . ",UsuarioUltimaModificacion = '" . $this->UsuarioUltimaModificacion . "',FechaUltimaModificacion = NOW(),Pantalla = '" . $this->Pantalla . "' "
                . " WHERE $where;");
        $catalogo = new Catalogo();
        $query = $catalogo->ejecutaConsultaActualizacion($consulta, $tabla, $where);
        if ($query == 1) {
            return true;
        }
        return false;
    }
    
    function deleteRegistro() {
        $catalogo = new Catalogo();
        $tabla = "c_tipomoneda";
        $where = "IdTipoMoneda = $this->IdTipoMoneda";
        $consulta = ("DELETE FROM `$tabla` WHERE $where;");
        $query = $catalogo->ejecutaConsultaActualizacion($consulta, $tabla, $where);
        if ($query == 1) {
            return true;
        }
        return false;
    }
    
    function getIdTipoMoneda() {
        return $this->IdTipoMoneda;
    }

    function getTipoMoneda() {
        return $this->TipoMoneda;
    }

    function getAbreviatura() {
        return $this->Abreviatura;
    }

    function getActivo() {
        return $this->Activo;
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

    function setIdTipoMoneda($IdTipoMoneda) {
        $this->IdTipoMoneda = $IdTipoMoneda;
    }

    function setTipoMoneda($TipoMoneda) {
        $this->TipoMoneda = $TipoMoneda;
    }

    function setAbreviatura($Abreviatura) {
        $this->Abreviatura = $Abreviatura;
    }

    function setActivo($Activo) {
        $this->Activo = $Activo;
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
