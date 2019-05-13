<?php

include_once("Conexion.class.php");
include_once("Catalogo.class.php");

class FacturaProveedor {
    private $IdFacturaProveedor;
    private $IdOrdenCompra;
    private $Folio;
    private $Fecha;
    private $IdEmisor;
    private $IdReceptor;
    private $SubTotal;
    private $Iva;
    private $Total;
    private $PathFactura;
    private $PathXml;
    private $UsuarioCreacion;
    private $FechaCreacion;
    private $UsuarioUltimaModificacion;
    private $FechaUltimaModificacion;
    private $Pantalla;
    private $Empresa;
    
    public function getRegistroById($id) {
        $consulta = "SELECT * FROM c_factura_proveedor WHERE IdFacturaProveedor = '$id';";
        $catalogo = new Catalogo();
        if(!empty($this->Empresa)){
            $catalogo->setEmpresa($this->Empresa);
        }
        $query = $catalogo->obtenerLista($consulta);
        
        while ($rs = mysql_fetch_array($query)) {
            $this->IdFacturaProveedor = $rs['IdFacturaProveedor'];
            $this->IdOrdenCompra = $rs['IdOrdenCompra'];
            $this->Folio = $rs['Folio'];
            $this->Fecha = $rs['Fecha'];
            $this->IdEmisor = $rs['IdEmisor'];
            $this->IdReceptor = $rs['IdReceptor'];
            $this->SubTotal = $rs['SubTotal'];
            $this->Iva = $rs['Iva'];
            $this->Total = $rs['Total'];
            $this->PathFactura = $rs['PathFactura'];
            $this->PathXml = $rs['PathXml'];
            $this->UsuarioCreacion = $rs['UsuarioCreacion'];
            $this->FechaCreacion = $rs['FechaCreacion'];
            $this->UsuarioUltimaModificacion = $rs['UsuarioUltimaModificacion'];
            $this->FechaUltimaModificacion = $rs['FechaUltimaModificacion'];
            $this->Pantalla = $rs['Pantalla'];
            return true;
        }
        return false;
    }
    
    function getIdFacturaProveedor() {
        return $this->IdFacturaProveedor;
    }

    function getIdOrdenCompra() {
        return $this->IdOrdenCompra;
    }

    function getFolio() {
        return $this->Folio;
    }

    function getFecha() {
        return $this->Fecha;
    }

    function getIdEmisor() {
        return $this->IdEmisor;
    }

    function getIdReceptor() {
        return $this->IdReceptor;
    }

    function getSubTotal() {
        return $this->SubTotal;
    }

    function getIva() {
        return $this->Iva;
    }

    function getTotal() {
        return $this->Total;
    }

    function getPathFactura() {
        return $this->PathFactura;
    }

    function getPathXml() {
        return $this->PathXml;
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

    function setIdFacturaProveedor($IdFacturaProveedor) {
        $this->IdFacturaProveedor = $IdFacturaProveedor;
    }

    function setIdOrdenCompra($IdOrdenCompra) {
        $this->IdOrdenCompra = $IdOrdenCompra;
    }

    function setFolio($Folio) {
        $this->Folio = $Folio;
    }

    function setFecha($Fecha) {
        $this->Fecha = $Fecha;
    }

    function setIdEmisor($IdEmisor) {
        $this->IdEmisor = $IdEmisor;
    }

    function setIdReceptor($IdReceptor) {
        $this->IdReceptor = $IdReceptor;
    }

    function setSubTotal($SubTotal) {
        $this->SubTotal = $SubTotal;
    }

    function setIva($Iva) {
        $this->Iva = $Iva;
    }

    function setTotal($Total) {
        $this->Total = $Total;
    }

    function setPathFactura($PathFactura) {
        $this->PathFactura = $PathFactura;
    }

    function setPathXml($PathXml) {
        $this->PathXml = $PathXml;
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

    function getEmpresa() {
        return $this->Empresa;
    }

    function setEmpresa($Empresa) {
        $this->Empresa = $Empresa;
    }

}
