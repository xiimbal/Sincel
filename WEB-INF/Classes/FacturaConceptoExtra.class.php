<?php

include_once("Catalogo.class.php");

/**
 * Description of FacturaConceptoExtra
 *
 * @author MAGG
 */
class FacturaConceptoExtra {

    private $Id_adicional;
    private $Id_factura;
    private $Numero_order;
    private $Numero_proveedor;
    private $observaciones_dentro_xml;
    private $observaciones_fuera_xml;
    private $UsuarioCreacion;
    private $FechaCreacion;
    private $UsuarioUltimaModificacion;
    private $FechaUltimaModificacion;
    private $Pantalla;
    private $empresa;

    public function newRegistro() {
        $consulta = "INSERT INTO k_factura_extras(Id_adicional, Id_factura, Numero_orden, Numero_proveedor, observaciones_dentro_xml, observaciones_fuera_xml, 
            UsuarioCreacion, FechaCreacion, UsuarioUltimaModificacion, FechaUltimaModificacion, Pantalla) VALUES(0,$this->Id_factura, '$this->Numero_order',
                '$this->Numero_proveedor','$this->observaciones_dentro_xml','$this->observaciones_fuera_xml','$this->UsuarioCreacion',NOW(),'$this->UsuarioUltimaModificacion',NOW(),'$this->Pantalla');";
        $catalogo = new Catalogo();
        if(isset($this->empresa)){
            $catalogo->setEmpresa($this->empresa);
        }
        $this->Id_adicional = $catalogo->insertarRegistro($consulta);
        if ($this->Id_adicional != NULL && $this->Id_adicional != 0) {
            return true;
        }
        return false;
    }

    public function getId_adicional() {
        return $this->Id_adicional;
    }

    public function setId_adicional($Id_adicional) {
        $this->Id_adicional = $Id_adicional;
    }

    public function getId_factura() {
        return $this->Id_factura;
    }

    public function setId_factura($Id_factura) {
        $this->Id_factura = $Id_factura;
    }

    public function getNumero_order() {
        return $this->Numero_order;
    }

    public function setNumero_order($Numero_order) {
        $this->Numero_order = $Numero_order;
    }

    public function getNumero_proveedor() {
        return $this->Numero_proveedor;
    }

    public function setNumero_proveedor($Numero_proveedor) {
        $this->Numero_proveedor = $Numero_proveedor;
    }

    public function getObservaciones_dentro_xml() {
        return $this->observaciones_dentro_xml;
    }

    public function setObservaciones_dentro_xml($observaciones_dentro_xml) {
        $this->observaciones_dentro_xml = $observaciones_dentro_xml;
    }

    public function getObservaciones_fuera_xml() {
        return $this->observaciones_fuera_xml;
    }

    public function setObservaciones_fuera_xml($observaciones_fuera_xml) {
        $this->observaciones_fuera_xml = $observaciones_fuera_xml;
    }

    public function getUsuarioCreacion() {
        return $this->UsuarioCreacion;
    }

    public function setUsuarioCreacion($UsuarioCreacion) {
        $this->UsuarioCreacion = $UsuarioCreacion;
    }

    public function getFechaCreacion() {
        return $this->FechaCreacion;
    }

    public function setFechaCreacion($FechaCreacion) {
        $this->FechaCreacion = $FechaCreacion;
    }

    public function getUsuarioUltimaModificacion() {
        return $this->UsuarioUltimaModificacion;
    }

    public function setUsuarioUltimaModificacion($UsuarioUltimaModificacion) {
        $this->UsuarioUltimaModificacion = $UsuarioUltimaModificacion;
    }

    public function getFechaUltimaModificacion() {
        return $this->FechaUltimaModificacion;
    }

    public function setFechaUltimaModificacion($FechaUltimaModificacion) {
        $this->FechaUltimaModificacion = $FechaUltimaModificacion;
    }

    public function getPantalla() {
        return $this->Pantalla;
    }

    public function setPantalla($Pantalla) {
        $this->Pantalla = $Pantalla;
    }

    public function getEmpresa() {
        return $this->empresa;
    }

    public function setEmpresa($empresa) {
        $this->empresa = $empresa;
    }

}

?>
