<?php

include_once("Conexion.class.php");
include_once ("Catalogo.class.php");
/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

class CFDI {

    private $id_Cfdi;
    private $nombre;
    private $csd;
    private $archivo_key;
    private $csd_password;
    private $UsuarioCreacion;
    private $FechaCreacion;
    private $UsuarioUltimaModificacion;
    private $FechaUltimaModificacion;
    private $Pantalla;
    private $pem;
    private $NoCertificado;
    private $NoSAT;

    public function getTabla() {
        $consulta = ("SELECT * FROM c_cfdi ORDER BY nombre");
        $catalogo = new Catalogo(); $query = $catalogo->obtenerLista($consulta);
        return $query;
    }

    public function nuevoRegistro() {
        $consulta = ("INSERT INTO c_cfdi(nombre,csd,archivo_key,csd_password,UsuarioCreacion,FechaCreacion,UsuarioUltimaModificacion,FechaUltimaModificacion,Pantalla,pem,NoCertificado,NoSAT)
            VALUES('" . $this->nombre . "','" . $this->csd . "','" . $this->archivo_key . "','" . $this->csd_password . "','" . $this->UsuarioCreacion . "',NOW(),'" . $this->UsuarioUltimaModificacion . "',NOW(),'" . $this->Pantalla . "','" . $this->pem . "','" . $this->NoCertificado . "','" . $this->NoSAT . "');");
        $catalogo = new Catalogo(); $query = $catalogo->obtenerLista($consulta);
        if ($query == 1) {
            return true;
        } else {
            return false;
        }
    }

    public function updateRegistro() {
        $consulta = ("UPDATE c_cfdi SET nombre='" . $this->nombre . "',csd_password='" . $this->csd_password . "',UsuarioUltimaModificacion='" . $this->UsuarioUltimaModificacion . "',FechaUltimaModificacion=NOW(),Pantalla='" . $this->Pantalla . "',NoCertificado='" . $this->NoCertificado . "',NoSAT='" . $this->NoSAT . "' WHERE id_Cfdi=" . $this->id_Cfdi);
        $catalogo = new Catalogo(); $query = $catalogo->obtenerLista($consulta);
        if ($query == 1) {
            return true;
        } else {
            return false;
        }
    }

    public function getRegistrobyID() {
        $consulta = ("SELECT * FROM c_cfdi AS cf WHERE cf.id_Cfdi=" . $this->id_Cfdi);
        $catalogo = new Catalogo(); $query = $catalogo->obtenerLista($consulta);
        while ($rs = mysql_fetch_array($query)) {
            $this->id_Cfdi = $rs['id_Cfdi'];
            $this->nombre = $rs['nombre'];
            $this->csd = $rs['csd'];
            $this->archivo_key = $rs['archivo_key'];
            $this->csd_password = $rs['csd_password'];
            $this->pem = $rs['pem'];
            $this->NoCertificado = $rs['NoCertificado'];
            $this->NoSAT = $rs['NoSAT'];
            return true;
        }
        return false;        
    }

    public function deletebyID() {
        $consulta = ("DELETE FROM c_cfdi WHERE id_Cfdi=" . $this->id_Cfdi);
        $catalogo = new Catalogo(); $query = $catalogo->obtenerLista($consulta);
        if ($query == 1) {
            return true;
        } else {
            return false;
        }
    }

    public function getNoSAT() {
        return $this->NoSAT;
    }

    public function setNoSAT($NoSAT) {
        $this->NoSAT = $NoSAT;
    }

    public function getNoCertificado() {
        return $this->NoCertificado;
    }

    public function setNoCertificado($NoCertificado) {
        $this->NoCertificado = $NoCertificado;
    }

    public function getPem() {
        return $this->pem;
    }

    public function setPem($pem) {
        $this->pem = $pem;
    }

    public function getId_Cfdi() {
        return $this->id_Cfdi;
    }

    public function getNombre() {
        return $this->nombre;
    }

    public function getCsd() {
        return $this->csd;
    }

    public function getArchivo_key() {
        return $this->archivo_key;
    }

    public function getCsd_password() {
        return $this->csd_password;
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

    public function setId_Cfdi($id_Cfdi) {
        $this->id_Cfdi = $id_Cfdi;
    }

    public function setNombre($nombre) {
        $this->nombre = $nombre;
    }

    public function setCsd($csd) {
        $this->csd = $csd;
    }

    public function setArchivo_key($archivo_key) {
        $this->archivo_key = $archivo_key;
    }

    public function setCsd_password($csd_password) {
        $this->csd_password = $csd_password;
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

}

?>
