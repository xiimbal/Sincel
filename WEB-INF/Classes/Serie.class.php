<?php

include_once("Conexion.class.php");
include_once("Catalogo.class.php");
include_once("CatalogoFacturacion.class.php");

class Serie {
   
    private $IdSerie;
    private $Prefijo;
    private $FolioInicio;
    private $FolioPreFactura;
    private $Activo;
    private $UsuarioCreacion;
    private $FechaCreacion;
    private $UsuarioUltimaModificacion;
    private $FechaUltimaModificacion;
    private $Pantalla;
    
    public function buscarTodo(){
        $catalogo = new Catalogo();
	$query = $catalogo->obtenerLista("SELECT * FROM c_serie");   
        return $query;
    }
    
    public function buscarTodoPago(){
        $catalogo = new Catalogo();
	$query = $catalogo->obtenerLista("SELECT * FROM c_seriepago");   
        return $query;
    }
    
    public function getRegistroById($id) {
        $catalogo = new Catalogo();
	$query = $catalogo->obtenerLista("SELECT * FROM c_serie WHERE IdSerie='" . $id . "'");        
        if ($rs = mysql_fetch_array($query)) {
            $this->IdSerie = $rs['IdSerie'];
            $this->Prefijo = $rs['Prefijo'];
            $this->FolioInicio = $rs['FolioInicio'];
            $this->FolioPreFactura = $rs['FolioPreFactura'];
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

    
    public function getRegistroPagoById($id) {
        $catalogo = new Catalogo();
	$query = $catalogo->obtenerLista("SELECT * FROM c_seriepago WHERE IdSerie='" . $id . "'");        
        if ($rs = mysql_fetch_array($query)) {
            $this->IdSerie = $rs['IdSerie'];
            $this->Prefijo = $rs['Prefijo'];
            $this->FolioInicio = $rs['FolioInicio'];
            $this->FolioPreFactura = $rs['FolioPreFactura'];
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
        $consulta = "INSERT INTO c_serie(Prefijo,FolioInicio,FolioPreFactura,Activo,UsuarioCreacion,FechaCreacion,UsuarioUltimaModificacion,FechaUltimaModificacion,Pantalla)
        VALUES('$this->Prefijo',$this->FolioInicio,$this->FolioPreFactura,$this->Activo,'$this->UsuarioCreacion',NOW(),'$this->UsuarioUltimaModificacion',NOW(),'$this->Pantalla')";
        
        $catalogo = new Catalogo();
	$this->IdSerie = $catalogo->insertarRegistro($consulta);             
        if ($this->IdSerie != NULL && $this->IdSerie != 0) {
            return true;
        }
        return false;
    }

    public function newRegistroPago() { 
        $consulta = "INSERT INTO c_seriepago(Prefijo,FolioInicio,FolioPreFactura,Activo,UsuarioCreacion,FechaCreacion,UsuarioUltimaModificacion,FechaUltimaModificacion,Pantalla)
        VALUES('$this->Prefijo',$this->FolioInicio,$this->FolioPreFactura,$this->Activo,'$this->UsuarioCreacion',NOW(),'$this->UsuarioUltimaModificacion',NOW(),'$this->Pantalla')";
        $catalogo = new Catalogo();
	$this->IdSerie = $catalogo->insertarRegistro($consulta);             
        if ($this->IdSerie != NULL && $this->IdSerie != 0) {
            return true;
        }
        return false;
    }

    public function editRegistro() {
        $catalogo = new Catalogo();
        $query = $catalogo->obtenerLista("UPDATE c_serie SET Prefijo = '$this->Prefijo', FolioInicio = $this->FolioInicio,Activo = '$this->Activo',
                            UsuarioUltimaModificacion = '" . $this->UsuarioUltimaModificacion . "',FechaUltimaModificacion = now(),
                            Pantalla = '" . $this->Pantalla . "' WHERE IdSerie = $this->IdSerie");
        if ($query == 1) {
            return true;
        }
        return false;
    }

    public function editRegistroPago() {
        $catalogo = new Catalogo();
        $query = $catalogo->obtenerLista("UPDATE c_seriepago SET Prefijo = '$this->Prefijo', FolioInicio = $this->FolioInicio,Activo = '$this->Activo',
                            UsuarioUltimaModificacion = '" . $this->UsuarioUltimaModificacion . "',FechaUltimaModificacion = now(),
                            Pantalla = '" . $this->Pantalla . "' WHERE IdSerie = $this->IdSerie");
        if ($query == 1) {
            return true;
        }
        return false;
    }
    

    public function deleteRegistro($id) {
        $catalogo = new Catalogo();
        $query = $catalogo->obtenerLista("DELETE FROM c_serie WHERE IdSerie = '$id'; ");        
        if ($query == 1) {
            return true;
        }
        return false;
    }
    
    public function deleteRegistroPago($idp) {
        $catalogo = new Catalogo();
        $query = $catalogo->obtenerLista("DELETE FROM c_seriepago WHERE IdSerie = '$idp';");        
        if ($query == 1) {
            return true;
        }
        return false;
    }
    
    public function existeFolio(){
        $catalogo = new CatalogoFacturacion();
        $consulta = "SELECT * FROM c_factura WHERE Folio = $this->Prefijo".$this->FolioInicio;
        $query = $catalogo->obtenerLista($consulta);
        if(mysql_num_rows($query) > 0){
            return true;
        }
        return false;
    }
    
    function getIdSerie() {
        return $this->IdSerie;
    }

    function getPrefijo() {
        return $this->Prefijo;
    }

    function getFolioInicio() {
        return $this->FolioInicio;
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

    function setIdSerie($IdSerie) {
        $this->IdSerie = $IdSerie;
    }

    function setPrefijo($Prefijo) {
        $this->Prefijo = $Prefijo;
    }

    function setFolioInicio($FolioInicio) {
        $this->FolioInicio = $FolioInicio;
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
    
    function getFolioPreFactura() {
        return $this->FolioPreFactura;
    }

    function setFolioPreFactura($FolioPreFactura) {
        $this->FolioPreFactura = $FolioPreFactura;
    }

}
