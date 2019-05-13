<?php

include_once("Catalogo.class.php");

class FacturaExtra {

    private $Id_adicional;
    private $Id_factura;
    private $Numero_orden;
    private $Numero_proveedor;
    private $observaciones_dentro_xml;
    private $observaciones_fuera_xml;
    private $UsuarioCreacion;
    private $FechaCreacion;
    private $UsuarioUltimaModificacion;
    private $FechaUltimaModificacion;
    private $Pantalla;

    public function getRegistroById() {
        $consulta = ("SELECT * FROM k_factura_extras WHERE Id_adicional='" . $this->Id_adicional . "'");
        $catalogo = new Catalogo(); $query = $catalogo->obtenerLista($consulta);
        while ($rs = mysql_fetch_array($query)) {
            $this->Id_adicional = $rs['Id_adicional'];
            $this->Id_factura = $rs['Id_factura'];
            $this->Numero_orden = $rs['Numero_orden'];
            $this->Numero_proveedor = $rs['Numero_proveedor'];
            $this->observaciones_dentro_xml = $rs['observaciones_dentro_xml'];
            $this->observaciones_fuera_xml = $rs['observaciones_fuera_xml'];
            $this->UsuarioCreacion = $rs['UsuarioCreacion'];
            $this->FechaCreacion = $rs['FechaCreacion'];
            $this->UsuarioUltimaModificacion = $rs['UsuarioUltimaModificacion'];
            $this->FechaUltimaModificacion = $rs['FechaUltimaModificacion'];
            $this->Pantalla = $rs['Pantalla'];
            return true;
        }
        return false;
    }

    public function getRegistroByFactura() {
        $consulta = ("SELECT * FROM k_factura_extras WHERE Id_factura='" . $this->Id_factura . "'");
        $catalogo = new Catalogo(); $query = $catalogo->obtenerLista($consulta);
        while ($rs = mysql_fetch_array($query)) {
            $this->Id_adicional = $rs['Id_adicional'];
            $this->Id_factura = $rs['Id_factura'];
            $this->Numero_orden = $rs['Numero_orden'];
            $this->Numero_proveedor = $rs['Numero_proveedor'];
            $this->observaciones_dentro_xml = $rs['observaciones_dentro_xml'];
            $this->observaciones_fuera_xml = $rs['observaciones_fuera_xml'];
            $this->UsuarioCreacion = $rs['UsuarioCreacion'];
            $this->FechaCreacion = $rs['FechaCreacion'];
            $this->UsuarioUltimaModificacion = $rs['UsuarioUltimaModificacion'];
            $this->FechaUltimaModificacion = $rs['FechaUltimaModificacion'];
            $this->Pantalla = $rs['Pantalla'];
            return true;
        }
        return false;
    }
    
    public function getId_adicional() {
        return $this->Id_adicional;
    }

    public function getId_factura() {
        return $this->Id_factura;
    }

    public function getNumero_orden() {
        return $this->Numero_orden;
    }

    public function getNumero_proveedor() {
        return $this->Numero_proveedor;
    }

    public function getObservaciones_dentro_xml() {
        return $this->observaciones_dentro_xml;
    }

    public function getObservaciones_fuera_xml() {
        return $this->observaciones_fuera_xml;
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

    public function setId_adicional($Id_adicional) {
        $this->Id_adicional = $Id_adicional;
    }

    public function setId_factura($Id_factura) {
        $this->Id_factura = $Id_factura;
    }

    public function setNumero_orden($Numero_orden) {
        $this->Numero_orden = $Numero_orden;
    }

    public function setNumero_proveedor($Numero_proveedor) {
        $this->Numero_proveedor = $Numero_proveedor;
    }

    public function setObservaciones_dentro_xml($observaciones_dentro_xml) {
        $this->observaciones_dentro_xml = $observaciones_dentro_xml;
    }

    public function setObservaciones_fuera_xml($observaciones_fuera_xml) {
        $this->observaciones_fuera_xml = $observaciones_fuera_xml;
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
