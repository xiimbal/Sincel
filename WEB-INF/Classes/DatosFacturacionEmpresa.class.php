<?php

include_once("ConexionFacturacion.class.php");
include_once("Catalogo.class.php");
include_once ("CatalogoFacturacion.class.php");

/**
 * Description of DatosFacturacionEmpresa
 *
 * @author MAGG
 */
class DatosFacturacionEmpresa {

    private $IdDatosFacturacionEmpresa;
    private $RazonSocial;
    private $Calle;
    private $Colonia;
    private $Delegacion;
    private $Estado;
    private $Pais;
    private $CP;
    private $RFC;
    private $Telefono;
    private $Orden;
    private $NoExterior;
    private $NoInterior;
    private $RegimenFiscal;
    private $facturaCFDI;
    private $ImagenPHP;
    private $Activo;
    private $UsuarioCreacion;
    private $FechaCreacion;
    private $UsuarioUltimaModificacion;
    private $FechaUltimaModificacion;
    private $Pantalla;
    private $id_pac;
    private $empresa;
    private $FacturaTickets;
    private $cfdi33;
    private $IdSerie;

    public function getRegistroById($id) {
        $consulta = ("SELECT * FROM `c_datosfacturacionempresa` WHERE IdDatosFacturacionEmpresa = '" . $id . "';");
        $catalogo = new Catalogo();
        if (isset($this->empresa)) {
            $catalogo->setEmpresa($this->empresa);
        }
        $query = $catalogo->obtenerLista($consulta);
        while ($rs = mysql_fetch_array($query)) {
            $this->IdDatosFacturacionEmpresa = $rs['IdDatosFacturacionEmpresa'];
            $this->RazonSocial = $rs['RazonSocial'];
            $this->Calle = $rs['Calle'];
            $this->Colonia = $rs['Colonia'];
            $this->Delegacion = $rs['Delegacion'];
            $this->Estado = $rs['Estado'];
            $this->Pais = $rs['Pais'];
            $this->CP = $rs['CP'];
            $this->RFC = $rs['RFC'];
            $this->Telefono = $rs['Telefono'];
            $this->Orden = $rs['Orden'];
            $this->NoExterior = $rs['NoExterior'];
            $this->NoInterior = $rs['NoInterior'];
            $this->RegimenFiscal = $rs['RegimenFiscal'];
            $this->facturaCFDI = $rs['facturaCFDI'];
            $this->ImagenPHP = $rs['ImagenPHP'];
            $this->Activo = $rs['Activo'];
            $this->UsuarioCreacion = $rs['UsuarioCreacion'];
            $this->FechaCreacion = $rs['FechaCreacion'];
            $this->UsuarioUltimaModificacion = $rs['UsuarioUltimaModificacion'];
            $this->FechaUltimaModificacion = $rs['FechaUltimaModificacion'];
            $this->Pantalla = $rs['Pantalla'];
            $this->id_pac = $rs['id_pac'];
            $this->FacturaTickets = $rs['FacturaTickets'];
            $this->cfdi33 = $rs['cfdi33'];
            $this->IdSerie = $rs['IdSerie'];
            return true;
        }
        return false;
    }

    public function getRegistroByRFC($rfc) {
        $consulta = ("SELECT * FROM `c_datosfacturacionempresa` WHERE RFC = '" . $rfc . "';");
        $catalogo = new Catalogo();
        if (isset($this->empresa)) {
            $catalogo->setEmpresa($this->empresa);
        }
        $query = $catalogo->obtenerLista($consulta);
        while ($rs = mysql_fetch_array($query)) {
            $this->IdDatosFacturacionEmpresa = $rs['IdDatosFacturacionEmpresa'];
            $this->RazonSocial = $rs['RazonSocial'];
            $this->Calle = $rs['Calle'];
            $this->Colonia = $rs['Colonia'];
            $this->Delegacion = $rs['Delegacion'];
            $this->Estado = $rs['Estado'];
            $this->Pais = $rs['Pais'];
            $this->CP = $rs['CP'];
            $this->RFC = $rs['RFC'];
            $this->Telefono = $rs['Telefono'];
            $this->Orden = $rs['Orden'];
            $this->NoExterior = $rs['NoExterior'];
            $this->NoInterior = $rs['NoInterior'];
            $this->RegimenFiscal = $rs['RegimenFiscal'];
            $this->facturaCFDI = $rs['facturaCFDI'];
            $this->ImagenPHP = $rs['ImagenPHP'];
            $this->Activo = $rs['Activo'];
            $this->UsuarioCreacion = $rs['UsuarioCreacion'];
            $this->FechaCreacion = $rs['FechaCreacion'];
            $this->UsuarioUltimaModificacion = $rs['UsuarioUltimaModificacion'];
            $this->FechaUltimaModificacion = $rs['FechaUltimaModificacion'];
            $this->Pantalla = $rs['Pantalla'];
            $this->id_pac = $rs['id_pac'];
            $this->FacturaTickets = $rs['FacturaTickets'];
            $this->cfdi33 = $rs['cfdi33'];
            $this->IdSerie = $rs['IdSerie'];
            return true;
        }
        return false;
    }

    public function getEmpresasFacturacion() {
        $consulta = "SELECT * FROM `c_datosfacturacionempresa` WHERE Activo = 1 ORDER BY RazonSocial;";
        $catalogo = new Catalogo();
        if (isset($this->empresa)) {
            $catalogo->setEmpresa($this->empresa);
        }
        $result = $catalogo->obtenerLista($consulta);
        return $result;
    }

    public function getIdDatosFacturacionEmpresa() {
        return $this->IdDatosFacturacionEmpresa;
    }

    public function setIdDatosFacturacionEmpresa($IdDatosFacturacionEmpresa) {
        $this->IdDatosFacturacionEmpresa = $IdDatosFacturacionEmpresa;
    }

    public function getRazonSocial() {
        return $this->RazonSocial;
    }

    public function setRazonSocial($RazonSocial) {
        $this->RazonSocial = $RazonSocial;
    }

    public function getCalle() {
        return $this->Calle;
    }

    public function setCalle($Calle) {
        $this->Calle = $Calle;
    }

    public function getColonia() {
        return $this->Colonia;
    }

    public function setColonia($Colonia) {
        $this->Colonia = $Colonia;
    }

    public function getDelegacion() {
        return $this->Delegacion;
    }

    public function setDelegacion($Delegacion) {
        $this->Delegacion = $Delegacion;
    }

    public function getEstado() {
        return $this->Estado;
    }

    public function setEstado($Estado) {
        $this->Estado = $Estado;
    }

    public function getPais() {
        return $this->Pais;
    }

    public function setPais($Pais) {
        $this->Pais = $Pais;
    }

    public function getCP() {
        return $this->CP;
    }

    public function setCP($CP) {
        $this->CP = $CP;
    }

    public function getRFC() {
        return $this->RFC;
    }

    public function setRFC($RFC) {
        $this->RFC = $RFC;
    }

    public function getTelefono() {
        return $this->Telefono;
    }

    public function setTelefono($Telefono) {
        $this->Telefono = $Telefono;
    }

    public function getOrden() {
        return $this->Orden;
    }

    public function setOrden($Orden) {
        $this->Orden = $Orden;
    }

    public function getNoExterior() {
        return $this->NoExterior;
    }

    public function setNoExterior($NoExterior) {
        $this->NoExterior = $NoExterior;
    }

    public function getNoInterior() {
        return $this->NoInterior;
    }

    public function setNoInterior($NoInterior) {
        $this->NoInterior = $NoInterior;
    }

    public function getRegimenFiscal() {
        return $this->RegimenFiscal;
    }

    public function setRegimenFiscal($RegimenFiscal) {
        $this->RegimenFiscal = $RegimenFiscal;
    }

    public function getFacturaCFDI() {
        return $this->facturaCFDI;
    }

    public function setFacturaCFDI($facturaCFDI) {
        $this->facturaCFDI = $facturaCFDI;
    }

    public function getImagenPHP() {
        return $this->ImagenPHP;
    }

    public function setImagenPHP($ImagenPHP) {
        $this->ImagenPHP = $ImagenPHP;
    }

    public function getActivo() {
        return $this->Activo;
    }

    public function setActivo($Activo) {
        $this->Activo = $Activo;
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

    function getId_pac() {
        return $this->id_pac;
    }

    function setId_pac($id_pac) {
        $this->id_pac = $id_pac;
    }

    function getFacturaTickets() {
        return $this->FacturaTickets;
    }

    function getCfdi33() {
        return $this->cfdi33;
    }

    function setFacturaTickets($FacturaTickets) {
        $this->FacturaTickets = $FacturaTickets;
    }

    function setCfdi33($cfdi33) {
        $this->cfdi33 = $cfdi33;
    }

    
    function getIdSerie() {
        return $this->IdSerie;
    }

    function setIdSerie($IdSerie) {
        $this->IdSerie = $IdSerie;
    }
}

?>
