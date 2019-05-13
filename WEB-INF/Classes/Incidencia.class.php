<?php

include_once("Conexion.class.php");
include_once("Catalogo.class.php");

/**
 * Description of Incidencia
 *
 * @author MAGG
 */
class Incidencia {

    private $Id_Incidencia;
    private $NoSerie;
    private $Fecha;
    private $FechaFin;
    private $Descripcion;
    private $Status;
    private $ClaveCentroCosto;
    private $Id_Ticket;
    private $Activo;
    private $UsuarioCreacion;
    private $FechaCreacion;
    private $UsuarioUltimaModificacion;
    private $FechaUltimaModificacion;
    private $Pantalla;
    private $idTipoIncidencia;
    private $empresa;

    public function newRegistro() {        
        if(!isset($this->NoSerie) || empty($this->NoSerie)){
            $this->NoSerie = "NULL";
        }else{
            $this->NoSerie = "'$this->NoSerie'";
        }
        
        if(!isset($this->ClaveCentroCosto) || empty($this->ClaveCentroCosto)){
            $this->ClaveCentroCosto = "NULL";
        }else{
            $this->ClaveCentroCosto = "'$this->ClaveCentroCosto'";
        }
        
        $consulta = "INSERT INTO c_incidencias(Id_Incidencia,NoSerie,Fecha, FechaFin,Descripcion,Status,ClaveCentroCosto,Id_Ticket,
        Activo,UsuarioCreacion,FechaCreacion,UsuarioUltimaModificacion,FechaUltimaModificacion,Pantalla,IdTipoIncidencia) VALUES(0,$this->NoSerie,
            '$this->Fecha','$this->FechaFin','$this->Descripcion',$this->Status, $this->ClaveCentroCosto,$this->Id_Ticket, $this->Activo,
            '$this->UsuarioCreacion',NOW(),'$this->UsuarioUltimaModificacion',NOW(),'$this->Pantalla',$this->idTipoIncidencia);";
        //echo $consulta;
        $catalogo = new Catalogo();
        if(isset($this->empresa)){
            $catalogo->setEmpresa($this->empresa);
        }
        $this->Id_Incidencia = $catalogo->insertarRegistro($consulta);         
        if ($this->Id_Incidencia!=NULL && $this->Id_Incidencia!=0) {                        
            return true;
        }        
        return false;
    }

    public function getId_Incidencia() {
        return $this->Id_Incidencia;
    }

    public function setId_Incidencia($Id_Incidencia) {
        $this->Id_Incidencia = $Id_Incidencia;
    }

    public function getNoSerie() {
        return $this->NoSerie;
    }

    public function setNoSerie($NoSerie) {
        $this->NoSerie = $NoSerie;
    }

    public function getFecha() {
        return $this->Fecha;
    }

    public function setFecha($Fecha) {
        $this->Fecha = $Fecha;
    }

    public function getFechaFin() {
        return $this->FechaFin;
    }

    public function setFechaFin($FechaFin) {
        $this->FechaFin = $FechaFin;
    }

    public function getDescripcion() {
        return $this->Descripcion;
    }

    public function setDescripcion($Descripcion) {
        $this->Descripcion = $Descripcion;
    }

    public function getStatus() {
        return $this->Status;
    }

    public function setStatus($Status) {
        $this->Status = $Status;
    }

    public function getClaveCentroCosto() {
        return $this->ClaveCentroCosto;
    }

    public function setClaveCentroCosto($ClaveCentroCosto) {
        $this->ClaveCentroCosto = $ClaveCentroCosto;
    }

    public function getId_Ticket() {
        return $this->Id_Ticket;
    }

    public function setId_Ticket($Id_Ticket) {
        $this->Id_Ticket = $Id_Ticket;
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
    public function getIdTipoIncidencia() {
        return $this->idTipoIncidencia;
    }

    public function setIdTipoIncidencia($idTipoIncidencia) {
        $this->idTipoIncidencia = $idTipoIncidencia;
    }

    function getEmpresa() {
        return $this->empresa;
    }

    function setEmpresa($empresa) {
        $this->empresa = $empresa;
    }

}

?>
