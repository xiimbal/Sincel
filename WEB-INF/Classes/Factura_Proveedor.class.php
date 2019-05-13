<?php

include_once("Catalogo.class.php");

/**
 * Description of Factura_Proveedor
 *
 * @author MAGG
 */
class Factura_Proveedor {

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
    private $empresa;
    private $facturaticket;
    
    public function getRegistroByRFCFolio(){
        $consulta = "SELECT IdFacturaProveedor FROM c_factura_proveedor WHERE Folio = $this->Folio AND IdEmisor = '$this->IdEmisor';";
        $catalogo = new Catalogo();
        if (isset($this->empresa)) {
            $catalogo->setEmpresa($this->empresa);
        }
        $result = $catalogo->obtenerLista($consulta);
        if(mysql_num_rows($result) > 0){
            return true;
        }
        return false;
    }
    
    public function newRegistro(){
        if(!isset($this->IdOrdenCompra) || empty($this->IdOrdenCompra)){
            $this->IdOrdenCompra = "NULL";
        }
        
        $consulta = ("INSERT INTO c_factura_proveedor(IdFacturaProveedor,IdOrdenCompra,Folio,Fecha,IdEmisor,IdReceptor,SubTotal,Iva,Total,PathFactura,PathXml,"
                . "UsuarioCreacion,FechaCreacion,UsuarioUltimaModificacion,FechaUltimaModificacion,Pantalla) "
                . "VALUES(0,$this->IdOrdenCompra,'$this->Folio','$this->Fecha','$this->IdEmisor',$this->IdReceptor,$this->SubTotal,$this->Iva,$this->Total,'$this->PathFactura','$this->PathXml',"
                . "'$this->UsuarioCreacion', NOW(), '$this->UsuarioUltimaModificacion', NOW(), '$this->Pantalla');");        
        //echo $consulta;
        $catalogo = new Catalogo();
        if (isset($this->empresa)) {
            $catalogo->setEmpresa($this->empresa);
        }
        $this->IdFacturaProveedor = $catalogo->insertarRegistro($consulta);
        
        if ($this->IdFacturaProveedor != NULL && $this->IdFacturaProveedor != 0) {
            if(isset($this->facturaticket) && $this->facturaticket == 1){
                $consulta2 = "UPDATE c_factura_proveedor SET Folio = $this->IdFacturaProveedor WHERE IdFacturaProveedor = $this->IdFacturaProveedor";
                $catalogo->obtenerLista($consulta2);
            }
            return true;
        }
        return false;
    }
    
    public function editPathFactura(){                
        $tabla = "c_factura_proveedor";
        $where = "IdFacturaProveedor = $this->IdFacturaProveedor";
        $consulta = ("UPDATE $tabla SET PathFactura = '$this->PathFactura',
                UsuarioUltimaModificacion = '$this->UsuarioUltimaModificacion', FechaUltimaModificacion = NOW(), Pantalla = '$this->Pantalla'
                WHERE $where;");
        $catalogo = new Catalogo();
        $query = $catalogo->obtenerLista($consulta, $tabla, $where);
        if ($query == 1) {
            return true;
        }
        return false;
    }    
    
    public function deleteRegistro(){
        $tabla = "c_factura_proveedor";
        $where = "IdFacturaProveedor = $this->IdFacturaProveedor";
        $consulta = ("DELETE FROM $tabla WHERE $where;");
        $catalogo = new Catalogo();
        $query = $catalogo->obtenerLista($consulta, $tabla, $where);
        if ($query == 1) {
            return true;
        }
        return false;
    }
    
    public function registrarRelacionTyF($idTicket){
        $consulta = "INSERT INTO k_ticket_facturacionProv (IdTicket, IdFacturaProveedor) VALUES ($idTicket, $this->IdFacturaProveedor)";
        $catalogo = new Catalogo();
        if (isset($this->empresa)) {
            $catalogo->setEmpresa($this->empresa);
        }
        $result = $catalogo->obtenerLista($consulta);
        if ($result == 1) {
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
        return $this->empresa;
    }

    function setEmpresa($empresa) {
        $this->empresa = $empresa;
    }

    function getFacturaticket() {
        return $this->facturaticket;
    }

    function setFacturaticket($facturaticket) {
        $this->facturaticket = $facturaticket;
    }

}
