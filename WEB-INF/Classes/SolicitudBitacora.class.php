<?php

include_once ("Catalogo.class.php");

/**
 * Description of SolicitudBitacora
 *
 * @author MAGG
 */
class SolicitudBitacora {

    private $IdAsignacion;
    private $id_bitacora;
    private $id_solicitud;
    private $NoParte;
    private $ClaveCentroCosto;
    private $Activo;
    private $UsuarioCreacion;
    private $FechaCreacion;
    private $UsuarioUltimaModificacion;
    private $FechaUltimaModificacion;
    private $Pantalla;
    private $tabla = "k_solicitudbitacora";
    private $campoId = "IdAsignacion";

    public function getRegistroById($id) {
        $consulta = ("SELECT * FROM $this->tabla WHERE $this->campoId = $id;");
        $catalogo = new Catalogo();
        $query = $catalogo->obtenerLista($consulta);
        while ($rs = mysql_fetch_array($query)) {
            $this->IdAsignacion = $rs['IdAsignacion'];
            $this->id_bitacora = $rs['id_bitacora'];
            $this->id_solicitud = $rs['id_solicitud'];
            $this->NoParte = $rs['NoParte'];
            $this->ClaveCentroCosto = $rs['ClaveCentroCosto'];
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
        $consulta = ("INSERT INTO $this->tabla(IdAsignacion,id_bitacora,id_solicitud,NoParte,ClaveCentroCosto,
            Activo,UsuarioCreacion,FechaCreacion,UsuarioUltimaModificacion,FechaUltimaModificacion,Pantalla) 
            VALUES(0,$this->id_bitacora,$this->id_solicitud,'$this->NoParte','$this->ClaveCentroCosto',
                $this->Activo,'$this->UsuarioCreacion',NOW(),'$this->UsuarioUltimaModificacion',NOW(),'$this->Pantalla');");        
        $catalogo = new Catalogo(); 
        $this->IdAsignacion = $catalogo->insertarRegistro($consulta);
        if ($this->IdAsignacion !=NULL && $this->IdAsignacion !=0) {                        
            return true;
        }        
        return false;
    }
    
    public function editRegistro() {        
        $consulta = ("UPDATE $this->tabla SET id_bitacora = $this->id_bitacora,id_solicitud = $this->id_solicitud,
                NoParte = '$this->NoParte',ClaveCentroCosto = '$this->ClaveCentroCosto',
                Activo = '$this->Activo',UsuarioUltimaModificacion = '$this->UsuarioUltimaModificacion',FechaUltimaModificacion = NOW(),Pantalla = '$this->Pantalla' 
                WHERE IdAsignacion = " . $this->IdAsignacion . ";");
        $catalogo = new Catalogo(); $query = $catalogo->obtenerLista($consulta);
        if ($query == 1) {
            return true;
        }
        return false;
    }
    
    public function deleteRegistro($idBitacora, $IdSolicitud){
        $consulta = "DELETE FROM `$this->tabla` WHERE id_bitacora = $idBitacora AND id_solicitud = $IdSolicitud;";        
        $catalogo = new Catalogo(); 
        $query = $catalogo->obtenerLista($consulta);
        if ($query == 1) {
            return true;
        }
        return false;
    }

    function getIdAsignacion() {
        return $this->IdAsignacion;
    }

    function getId_bitacora() {
        return $this->id_bitacora;
    }

    function getId_solicitud() {
        return $this->id_solicitud;
    }

    function getNoParte() {
        return $this->NoParte;
    }

    function getClaveCentroCosto() {
        return $this->ClaveCentroCosto;
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

    function setIdAsignacion($IdAsignacion) {
        $this->IdAsignacion = $IdAsignacion;
    }

    function setId_bitacora($id_bitacora) {
        $this->id_bitacora = $id_bitacora;
    }

    function setId_solicitud($id_solicitud) {
        $this->id_solicitud = $id_solicitud;
    }

    function setNoParte($NoParte) {
        $this->NoParte = $NoParte;
    }

    function setClaveCentroCosto($ClaveCentroCosto) {
        $this->ClaveCentroCosto = $ClaveCentroCosto;
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

}
