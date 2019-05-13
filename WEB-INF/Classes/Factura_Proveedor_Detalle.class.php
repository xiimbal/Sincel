<?php

include_once("Catalogo.class.php");

/**
 * Description of Factura_Proveedor_Detalle
 *
 * @author MAGG
 */
class Factura_Proveedor_Detalle {

    private $IdKFacturaProv;
    private $IdFacturaProveedor;
    private $Descripcion;
    private $Cantidad;
    private $Unidad;
    private $ValorUnitario;
    private $Importe;
    private $UsuarioCreacion;
    private $FechaCreacion;
    private $UsuarioUltimaModificacion;
    private $FechaUltimaModificacion;
    private $Pantalla;
    
    public function newRegistro(){
        if(!isset($this->IdOrdenCompra) || empty($this->IdOrdenCompra)){
            $this->IdOrdenCompra = "NULL";
        }                         
        
        $consulta = ("INSERT INTO k_factura_proveedor(IdKFacturaProv,IdFacturaProveedor,Descripcion,Cantidad,Unidad,ValorUnitario,Importe,"
                . "UsuarioCreacion,FechaCreacion,UsuarioUltimaModificacion,FechaUltimaModificacion,Pantalla) "
                . "VALUES(0,$this->IdFacturaProveedor,'$this->Descripcion',$this->Cantidad,$this->Unidad,$this->ValorUnitario,$this->Importe,"
                . "'$this->UsuarioCreacion', NOW(), '$this->UsuarioUltimaModificacion', NOW(), '$this->Pantalla');");
        //echo "<br/>$consulta";
        $catalogo = new Catalogo();
        $this->IdKFacturaProv = $catalogo->insertarRegistro($consulta);
        
        if ($this->IdKFacturaProv != NULL && $this->IdKFacturaProv != 0) {
            return true;
        }
        return false;
    }
    
    public function deleteRegistro(){
        $tabla = "k_factura_proveedor";
        $where = "IdFacturaProveedor = $this->IdFacturaProveedor";
        $consulta = ("DELETE FROM $tabla WHERE $where;");
        $catalogo = new Catalogo();
        $query = $catalogo->obtenerLista($consulta);
        if ($query == 1) {
            return true;
        }
        return false;
    }

    function getIdKFacturaProv() {
        return $this->IdKFacturaProv;
    }

    function getIdFacturaProveedor() {
        return $this->IdFacturaProveedor;
    }

    function getDescripcion() {
        return $this->Descripcion;
    }

    function getCantidad() {
        return $this->Cantidad;
    }

    function getUnidad() {
        return $this->Unidad;
    }

    function getValorUnitario() {
        return $this->ValorUnitario;
    }

    function getImporte() {
        return $this->Importe;
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

    function setIdKFacturaProv($IdKFacturaProv) {
        $this->IdKFacturaProv = $IdKFacturaProv;
    }

    function setIdFacturaProveedor($IdFacturaProveedor) {
        $this->IdFacturaProveedor = $IdFacturaProveedor;
    }

    function setDescripcion($Descripcion) {
        $this->Descripcion = $Descripcion;
    }

    function setCantidad($Cantidad) {
        $this->Cantidad = $Cantidad;
    }

    function setUnidad($Unidad) {
        $this->Unidad = $Unidad;
    }

    function setValorUnitario($ValorUnitario) {
        $this->ValorUnitario = $ValorUnitario;
    }

    function setImporte($Importe) {
        $this->Importe = $Importe;
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
