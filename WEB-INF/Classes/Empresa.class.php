<?php

include_once("CatalogoFacturacion.class.php");
include_once("Catalogo.class.php");
/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

class Empresa {

    private $id;
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
    private $FacturaCFDI;
    private $ImagenPHP;
    private $Activo;
    private $Directorio;
    private $ArchivoP12;
    private $ArchivoRFC;
    private $ArchivoLogo;
    private $UsuarioCreacion;
    private $UsuarioModificacion;
    private $FechaCreacion;
    private $FechaModificacion;
    private $Pantalla;
    private $id_Cfdi;
    private $id_pac;
    private $FacturaTickets;
    private $cfdi33;
    private $IdSerie;

    public function getRegistrobyID() {
        $consulta = ("SELECT * FROM c_datosfacturacionempresa WHERE IdDatosFacturacionEmpresa='" . $this->id . "'");
        $catalogo = new Catalogo();
        $query = $catalogo->obtenerLista($consulta);
        while ($rs = mysql_fetch_array($query)) {
            $this->id = $rs['IdDatosFacturacionEmpresa'];
            $this->RazonSocial = $rs['RazonSocial'];
            $this->Calle = $rs['Calle'];
            $this->Colonia = $rs['Colonia'];
            $this->Delegacion = $rs['Delegacion'];
            $this->Estado = $rs['Estado'];
            $this->Pais = $rs['Pais'];
            $this->CP = $rs['CP'];
            $this->RFC = $rs['RFC'];
            $this->Telefono = $rs['Telefono'];
            $this->NoExterior = $rs['NoExterior'];
            $this->NoInterior = $rs['NoInterior'];
            $this->RegimenFiscal = $rs['RegimenFiscal'];
            $this->FacturaCFDI = $rs['facturaCFDI'];
            $this->ImagenPHP = $rs['ImagenPHP'];
            $this->Directorio = $rs['directorio'];
            $this->ArchivoP12 = $rs['archivoP12'];
            $this->ArchivoRFC = $rs['archivoRFC'];
            $this->ArchivoLogo = $rs['archivoLogo'];
            $this->id_Cfdi = $rs['id_Cfdi'];
            $this->id_pac = $rs['id_pac'];
            $this->FacturaTickets = $rs['FacturaTickets'];
            $this->cfdi33 = $rs['cfdi33'];
            $this->IdSerie = $rs['IdSerie'];
            return true;
        }
        return false;
    }

    public function getRegistrobyRFC() {
        $consulta = ("SELECT * FROM c_datosfacturacionempresa WHERE RFC='" . $this->RFC . "'");        
        $catalogo = new Catalogo();
        $query = $catalogo->obtenerLista($consulta);
        while ($rs = mysql_fetch_array($query)) {
            $this->id = $rs['IdDatosFacturacionEmpresa'];
            $this->RazonSocial = $rs['RazonSocial'];
            $this->Calle = $rs['Calle'];
            $this->Colonia = $rs['Colonia'];
            $this->Delegacion = $rs['Delegacion'];
            $this->Estado = $rs['Estado'];
            $this->Pais = $rs['Pais'];
            $this->CP = $rs['CP'];
            $this->RFC = $rs['RFC'];
            $this->Telefono = $rs['Telefono'];
            $this->NoExterior = $rs['NoExterior'];
            $this->NoInterior = $rs['NoInterior'];
            $this->RegimenFiscal = $rs['RegimenFiscal'];
            $this->FacturaCFDI = $rs['facturaCFDI'];
            $this->ImagenPHP = $rs['ImagenPHP'];
            $this->Directorio = $rs['directorio'];
            $this->ArchivoP12 = $rs['archivoP12'];
            $this->ArchivoRFC = $rs['archivoRFC'];
            $this->ArchivoLogo = $rs['archivoLogo'];
            $this->id_Cfdi = $rs['id_Cfdi'];
            $this->id_pac = $rs['id_pac'];
            $this->FacturaTickets = $rs['FacturaTickets'];
            $this->cfdi33 = $rs['cfdi33'];
            $this->IdSerie = $rs['IdSerie'];
            return true;
        }
        return false;
    }

    public function getTablaEmpresas() {
        $consulta = ("SELECT RazonSocial,FechaCreacion,RFC,Pais,IF(Activo=1,'Activo','No Activo') AS Activo,IdDatosFacturacionEmpresa AS idEmpresa,id_Cfdi,id_pac FROM c_datosfacturacionempresa");
        $catalogo = new Catalogo();
        $query = $catalogo->obtenerLista($consulta);
        return $query;
    }

    public function deletebyID() {
        $consulta = ("DELETE FROM c_datosfacturacionempresa WHERE IdDatosFacturacionEmpresa = '" . $this->id . "';");
        $catalogo = new Catalogo();
        $query = $catalogo->obtenerLista($consulta);
        if ($query == 1) {
            $catalogo = new CatalogoFacturacion();
            $query = $catalogo->obtenerLista($consulta);
            if ($query == 1) {
                return true;
            }
            return false;
        }
        return false;
    }

    public function nuevoRegistro() {
        if($this->FacturaTickets != "1"){
            $this->FacturaTickets = "0";
        }
        $consulta = ("INSERT INTO c_datosfacturacionempresa(RazonSocial,Calle,Colonia,Delegacion,Estado,Pais,CP,RFC,NoExterior,NoInterior,Activo,UsuarioCreacion,FechaCreacion,UsuarioUltimaModificacion,FechaUltimaModificacion,Pantalla,id_Cfdi,id_pac,RegimenFiscal,cfdi33,IdSerie)
	VALUES('" . $this->RazonSocial . "','" . $this->Calle . "','" . $this->Colonia . "','" . $this->Delegacion . "','" . $this->Estado . "','México','" . $this->CP . "','" . $this->RFC . "','" . $this->NoExterior . "','" . $this->NoInterior . "'," . $this->Activo . ",'" . $this->UsuarioCreacion . "',NOW(),'" . $this->UsuarioModificacion . "',NOW(),'" . $this->Pantalla . "','" . $this->id_Cfdi . "','" . $this->id_pac . "'," . $this->RegimenFiscal . ",$this->cfdi33,$this->IdSerie);");
        $catalogo = new Catalogo();
        $this->id = $catalogo->insertarRegistro($consulta);
        if ($this->id != NULL && $this->id != 0) {
            $catalogo = new CatalogoFacturacion();
            $consulta = "INSERT INTO c_datosfacturacionempresa(IdDatosFacturacionEmpresa,RazonSocial, Calle, Colonia, Delegacion, Estado, Pais, CP, RFC, Telefono, Orden, NoExterior, "
                    . "NoInterior, RegimenFiscal, facturaCFDI, Activo, UsuarioCreacion, FechaCreacion, UsuarioUltimaModificacion, FechaUltimaModificacion, "
                    . "Pantalla, FacturaTickets,cfdi33,IdSerie) VALUES($this->id, '$this->RazonSocial', '$this->Calle','$this->Colonia','$this->Delegacion','" . $this->Estado . "','México',"
                    . "'" . $this->CP . "','$this->RFC','',0,'$this->NoExterior','$this->NoInterior',$this->RegimenFiscal,1,1,'$this->UsuarioCreacion',NOW(),"
                    . "'$this->UsuarioModificacion',NOW(),'$this->Pantalla', $this->FacturaTickets, $this->cfdi33, $this->IdSerie);";            
            $idFacturacion = $catalogo->insertarRegistro($consulta);
            if ($idFacturacion != NULL && $idFacturacion != 0) {
                return true;
            }else{
                return false;
            }
        }
        return false;
    }

    public function editRegistro() {        
        if($this->FacturaTickets != "1"){
            $this->FacturaTickets = "0";
        }
        $consulta = "UPDATE c_datosfacturacionempresa SET RazonSocial='" . $this->RazonSocial . "',Calle='" . $this->Calle . "',Colonia='" . $this->Colonia . "',
            Delegacion='" . $this->Delegacion . "',Estado='" . $this->Estado . "',Pais='México',CP='" . $this->CP . "',RFC='" . $this->RFC . "',NoExterior='" . $this->NoExterior . "',NoInterior='" . $this->NoInterior . "',Activo=" . $this->Activo . ",UsuarioUltimaModificacion='" . $this->UsuarioModificacion . "',FechaUltimaModificacion=NOW(),
            Pantalla='" . $this->Pantalla . "',id_Cfdi='" . $this->id_Cfdi . "',id_pac='" . $this->id_pac . "',RegimenFiscal=" . $this->RegimenFiscal . ", FacturaTickets = $this->FacturaTickets, cfdi33 = $this->cfdi33, IdSerie = $this->IdSerie
            WHERE IdDatosFacturacionEmpresa=" . $this->id;   
        //echo $consulta;
        $catalogo = new Catalogo();
        $query = $catalogo->obtenerLista($consulta);
        if ($query == 1) {
            $catalogo = new CatalogoFacturacion();
            $consulta = "UPDATE c_datosfacturacionempresa SET RazonSocial='" . $this->RazonSocial . "',Calle='" . $this->Calle . "',Colonia='" . $this->Colonia . "',
            Delegacion='" . $this->Delegacion . "',Estado='" . $this->Estado . "',Pais='México',CP='" . $this->CP . "',RFC='" . $this->RFC . "',NoExterior='" . $this->NoExterior . "',NoInterior='" . $this->NoInterior . "',Activo=" . $this->Activo . ",UsuarioUltimaModificacion='" . $this->UsuarioModificacion . "',FechaUltimaModificacion=NOW(),
            Pantalla='" . $this->Pantalla . "',RegimenFiscal=" . $this->RegimenFiscal . ", FacturaTickets = $this->FacturaTickets, cfdi33 = $this->cfdi33, IdSerie = $this->IdSerie WHERE IdDatosFacturacionEmpresa=" . $this->id;
            $query = $catalogo->obtenerLista($consulta);
            if ($query == 1) {
                return true;
            }
            return false;
        }
        return false;
    }

    public function actualizarLogo() {
        $consulta = ("UPDATE c_datosfacturacionempresa SET archivoLogo='" . $this->ArchivoLogo . "' WHERE IdDatosFacturacionEmpresa=" . $this->id);
        $catalogo = new Catalogo();
        $query = $catalogo->obtenerLista($consulta);
        if ($query == 1) {
            $catalogo = new CatalogoFacturacion();
            $consulta = "UPDATE c_datosfacturacionempresa SET ImagenPHP='" . $this->ArchivoLogo . "' WHERE IdDatosFacturacionEmpresa=" . $this->id;
            $query = $catalogo->obtenerLista($consulta);
            if ($query == 1) {
                return true;
            }
        }
        return false;
    }
    
    public function getIdEmpresaFacturarTickets(){
        $query1 = "SELECT IdDatosFacturacionEmpresa FROM c_datosfacturacionempresa WHERE FacturaTickets = 1;";
        $id = "";
        $catalogo = new Catalogo();
        $result1 = $catalogo->obtenerLista($query1);
        if(mysql_num_rows($result1) >= 1){//Si hay una empresa registrada para facturar
            while($rs = mysql_fetch_array($result1)){
                $id = $rs['IdDatosFacturacionEmpresa'];
            }
        }
        return $id;
    }
    
    public function hayEmpresasCFDI33(){
        $consulta = "SELECT * FROM c_datosfacturacionempresa WHERE cfdi33 = 1";
        $catalogo = new Catalogo();
        $result = $catalogo->obtenerLista($consulta);
        if(mysql_num_rows($result) > 0){
            return true;
        }
        return false;
    }

    public function getId_pac() {
        return $this->id_pac;
    }

    public function setId_pac($id_pac) {
        $this->id_pac = $id_pac;
    }

    public function getId_Cfdi() {
        return $this->id_Cfdi;
    }

    public function setId_Cfdi($id_Cfdi) {
        $this->id_Cfdi = $id_Cfdi;
    }

    public function getId() {
        return $this->id;
    }

    public function setId($id) {
        $this->id = $id;
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
        return $this->FacturaCFDI;
    }

    public function setFacturaCFDI($FacturaCFDI) {
        $this->FacturaCFDI = $FacturaCFDI;
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

    public function getDirectorio() {
        return $this->Directorio;
    }

    public function setDirectorio($Directorio) {
        $this->Directorio = $Directorio;
    }

    public function getArchivoP12() {
        return $this->ArchivoP12;
    }

    public function setArchivoP12($ArchivoP12) {
        $this->ArchivoP12 = $ArchivoP12;
    }

    public function getArchivoRFC() {
        return $this->ArchivoRFC;
    }

    public function setArchivoRFC($ArchivoRFC) {
        $this->ArchivoRFC = $ArchivoRFC;
    }

    public function getArchivoLogo() {
        return $this->ArchivoLogo;
    }

    public function setArchivoLogo($ArchivoLogo) {
        $this->ArchivoLogo = $ArchivoLogo;
    }

    public function getUsuarioCreacion() {
        return $this->UsuarioCreacion;
    }

    public function setUsuarioCreacion($UsuarioCreacion) {
        $this->UsuarioCreacion = $UsuarioCreacion;
    }

    public function getUsuarioModificacion() {
        return $this->UsuarioModificacion;
    }

    public function setUsuarioModificacion($UsuarioModificacion) {
        $this->UsuarioModificacion = $UsuarioModificacion;
    }

    public function getFechaCreacion() {
        return $this->FechaCreacion;
    }

    public function setFechaCreacion($FechaCreacion) {
        $this->FechaCreacion = $FechaCreacion;
    }

    public function getFechaModificacion() {
        return $this->FechaModificacion;
    }

    public function setFechaModificacion($FechaModificacion) {
        $this->FechaModificacion = $FechaModificacion;
    }

    public function getPantalla() {
        return $this->Pantalla;
    }

    public function setPantalla($Pantalla) {
        $this->Pantalla = $Pantalla;
    }

    function getFacturaTickets() {
        return $this->FacturaTickets;
    }

    function setFacturaTickets($FacturaTickets) {
        $this->FacturaTickets = $FacturaTickets;
    }

    function getCfdi33() {
        return $this->cfdi33;
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
