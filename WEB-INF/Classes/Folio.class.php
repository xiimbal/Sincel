<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
include_once("CatalogoFacturacion.class.php");

class Folio {

    private $id_folio;    
    private $folioInicial;
    private $folioFinal;
    private $serie;
    private $noAprobacion;
    private $anioAprobacion;
    private $ultimoFolio;
    private $RFCemisor;
    private $Canceladas;
    private $activo;
    private $UsuarioCreacion;
    private $FechaCreacion;
    private $UsuarioUltimaModificacion;
    private $FechaUltimaModificacion;
    private $Pantalla;

    public function getTabla() {
        $consulta = ("SELECT * FROM c_folio");
        $catalogo = new CatalogoFacturacion();
        $query = $catalogo->obtenerLista($consulta);
        return $query;
    }

    public function getRegistrobyID() {
        $consulta = "SELECT * FROM c_folio WHERE IdFolio=" . $this->id_folio;
        $catalogo = new CatalogoFacturacion();
        $query = $catalogo->obtenerLista($consulta);
        while ($rs = mysql_fetch_array($query)) {      
            $this->id_folio = $rs['IdFolio'];                  
            $this->folioInicial = $rs['FolioInicial'];
            $this->folioFinal = $rs['FolioFinal'];
            $this->serie = $rs['Serie'];
            $this->noAprobacion = $rs['NoAprobacion'];
            $this->anioAprobacion = $rs['AnioAprobacion'];
            $this->ultimoFolio = $rs['UltimoFolio'];
            $this->RFCemisor = $rs['RFCEmisor'];
            $this->activo = $rs['Activo'];
            $this->Canceladas = $rs['Canceladas'];
            return true;
        }
        return false;
    }
    
    public function getRegistrobyRFC() {
        $consulta = "SELECT * FROM c_folio WHERE RFCEmisor='" . $this->RFCemisor."'";
        $catalogo = new CatalogoFacturacion();
        $query = $catalogo->obtenerLista($consulta);
        while ($rs = mysql_fetch_array($query)) {    
            $this->id_folio = $rs['IdFolio'];                  
            $this->folioInicial = $rs['FolioInicial'];
            $this->folioFinal = $rs['FolioFinal'];
            $this->serie = $rs['Serie'];
            $this->noAprobacion = $rs['NoAprobacion'];
            $this->anioAprobacion = $rs['AnioAprobacion'];
            $this->ultimoFolio = $rs['UltimoFolio'];
            $this->RFCemisor = $rs['RFCEmisor'];
            $this->activo = $rs['Activo'];
            $this->Canceladas = $rs['Canceladas'];
            return true;
        }
        return false;
    }

    public function nuevoRegistro() {
        $nombres = "FolioInicial,FolioFinal,Serie,NoAprobacion,AnioAprobacion,UltimoFolio,RFCEmisor,Activo";
        $values = "'" . $this->folioInicial . "','" . $this->folioFinal . "','" . $this->serie . "','" . $this->noAprobacion . "',"
                . "'" . $this->anioAprobacion . "','" . $this->ultimoFolio . "','" . $this->RFCemisor . "','" . $this->activo . "'";
        $consulta = ("INSERT INTO c_folio(" . $nombres . ")  VALUES(" . $values . ")");                
        $catalogo = new CatalogoFacturacion();
        $query = $catalogo->obtenerLista($consulta);
        if ($query == 1) {
            return true;
        }
        return false;
    }

    public function editRegistro() {
        $consulta = ("UPDATE c_folio SET FolioInicial = '" . $this->folioInicial . "',FolioFinal='" . $this->folioFinal . "',serie = '" . $this->serie . "',"
                . "noAprobacion = '" . $this->noAprobacion . "',AnioAprobacion = '" . $this->anioAprobacion . "',UltimoFolio = '" . $this->ultimoFolio . "',"
                . "RFCEmisor = '" . $this->RFCemisor . "',Activo = '" . $this->activo . "' WHERE IdFolio='" . $this->id_folio . "';");
        $catalogo = new CatalogoFacturacion();
        $query = $catalogo->obtenerLista($consulta);
        if ($query == 1) {
            return true;
        }
        return false;
    }

    public function deleteRegistro() {
        $consulta = ("DELETE FROM c_folio WHERE IdFolio = '" . $this->id_folio . "';");
        $catalogo = new CatalogoFacturacion();
        $query = $catalogo->obtenerLista($consulta);
        if ($query == 1) {
            return true;
        }
        return false;
    }

    public function getId_folio() {
        return $this->id_folio;
    }

    public function getId_Empresa() {
        return $this->id_empresa;
    }

    public function getFolioInicial() {
        return $this->folioInicial;
    }

    public function getFolioFinal() {
        return $this->folioFinal;
    }

    public function getSerie() {
        return $this->serie;
    }

    public function getNoAprobacion() {
        return $this->noAprobacion;
    }

    public function getAnioAprobacion() {
        return $this->anioAprobacion;
    }

    public function getUltimoFolio() {
        return $this->ultimoFolio;
    }

    public function getRFCemisor() {
        return $this->RFCemisor;
    }

    public function getActivo() {
        return $this->activo;
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

    public function setId_folio($id_folio) {
        $this->id_folio = $id_folio;
    }

    public function setId_empresa($id_empresa) {
        $this->id_empresa = $id_empresa;
    }

    public function setFolioInicial($folioInicial) {
        $this->folioInicial = $folioInicial;
    }

    public function setFolioFinal($folioFinal) {
        $this->folioFinal = $folioFinal;
    }

    public function setSerie($serie) {
        $this->serie = $serie;
    }

    public function setNoAprobacion($noAprobacion) {
        $this->noAprobacion = $noAprobacion;
    }

    public function setAnioAprobacion($anioAprobacion) {
        $this->anioAprobacion = $anioAprobacion;
    }

    public function setUltimoFolio($ultimoFolio) {
        $this->ultimoFolio = $ultimoFolio;
    }

    public function setRFCemisor($RFCemisor) {
        $this->RFCemisor = $RFCemisor;
    }

    public function setActivo($activo) {
        $this->activo = $activo;
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

    function getCanceladas() {
        return $this->Canceladas;
    }

    function setCanceladas($Canceladas) {
        $this->Canceladas = $Canceladas;
    }
}
