<?php

include_once("Catalogo.class.php");

class ReporteHistorico {

    private $NumReporte;
    private $Retirado;
    private $UsuarioCreacion;
    private $FechaCreacion;
    private $UsuarioUltimaModificacion;
    private $FechaUltimaModificacion;
    private $Pantalla;

    public function getRegistroById($id) {
        $consulta = "SELECT * FROM `reportes_historicos` WHERE NumReporte = $id;";
        $catalogo = new Catalogo();
        $result = $catalogo->obtenerLista($consulta);
        while($rs = mysql_fetch_array($result)){
            $this->NumReporte = $rs['NumReporte'];
            $this->Retirado = $rs['Retirado'];
            $this->UsuarioCreacion = $rs['UsuarioCreacion'];
            $this->FechaCreacion = $rs['FechaCreacion'];
            $this->UsuarioUltimaModificacion = $rs['UsuarioUltimaModificacion'];
            $this->FechaUltimaModificacion = $rs['FechaUltimaModificacion'];
            $this->Pantalla = $rs['Pantalla'];
            return true;
        }
        return false;
    }

    public function actualizarRetirado() {
        $consulta = ("UPDATE reportes_historicos SET Retirado = '" . $this->Retirado . "',UsuarioUltimaModificacion='" . $this->UsuarioUltimaModificacion . "',FechaUltimaModificacion=NOW(),Pantalla = '" . $this->Pantalla . "' WHERE NumReporte='" . $this->NumReporte . "';");
        $catalogo = new Catalogo();
        $query = $catalogo->obtenerLista($consulta);
        if ($query == 1) {
            return true;
        }
        return false;
    }
    
    public function actualizarFacturar($valor){
        $consulta = "UPDATE `movimientos_equipo` SET FacturarMovimiento = $valor, UsuarioUltimaModificacion = '$this->UsuarioUltimaModificacion', FechaUltimaModificacion = NOW(), Pantalla = '$this->Pantalla' WHERE id_movimientos IN(SELECT id_movimientos FROM reportes_movimientos WHERE id_reportes = $this->NumReporte);";
        
        $catalogo = new Catalogo();
        $query = $catalogo->obtenerLista($consulta);
        if ($query != 0) {
            return true;
        }
        return false;
    }

    public function getNumReporte() {
        return $this->NumReporte;
    }

    public function getRetirado() {
        return $this->Retirado;
    }

    public function getUsuarioCreacion() {
        return $this->UsuarioCreacion;
    }

    public function getFechaCreacion() {
        return $this->FechaCreacion;
    }

    public function getUsuarioUltimaModificacion() {
        return $this->UsuarioUltimaModificacion;
    }

    public function getFechaUltimaModificacion() {
        return $this->FechaUltimaModificacion;
    }

    public function getPantalla() {
        return $this->Pantalla;
    }

    public function setNumReporte($NumReporte) {
        $this->NumReporte = $NumReporte;
    }

    public function setRetirado($Retirado) {
        $this->Retirado = $Retirado;
    }

    public function setUsuarioCreacion($UsuarioCreacion) {
        $this->UsuarioCreacion = $UsuarioCreacion;
    }

    public function setFechaCreacion($FechaCreacion) {
        $this->FechaCreacion = $FechaCreacion;
    }

    public function setUsuarioUltimaModificacion($UsuarioUltimaModificacion) {
        $this->UsuarioUltimaModificacion = $UsuarioUltimaModificacion;
    }

    public function setFechaUltimaModificacion($FechaUltimaModificacion) {
        $this->FechaUltimaModificacion = $FechaUltimaModificacion;
    }

    public function setPantalla($Pantalla) {
        $this->Pantalla = $Pantalla;
    }

}
