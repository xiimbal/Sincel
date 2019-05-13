<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
include_once("Conexion.class.php");
include_once ("Catalogo.class.php");

class CentroCostoReal {

    private $id_cc;
    private $nombre;
    private $ClaveCliente;
    private $UsuarioCreacion;
    private $FechaCreacion;
    private $UsuarioUltimaModificacion;
    private $FechaUltimaModificacion;
    private $Pantalla;
    private $localidades;
    private $Moroso;

    public function getRegistrobyID() {
        $consulta = ("SELECT * FROM c_cen_costo WHERE id_cc = '" . $this->id_cc . "';");
        $catalogo = new Catalogo(); $query = $catalogo->obtenerLista($consulta);
        while ($rs = mysql_fetch_array($query)) {
            $this->nombre = $rs['nombre'];
            $this->ClaveCliente = $rs['ClaveCliente'];
            $this->Moroso = $rs['Moroso'];
            return true;
        }
        return false;
    }

    public function nuevoRegistro() {
        $consulta = ("INSERT INTO c_cen_costo(nombre,ClaveCliente,UsuarioCreacion,FechaCreacion,UsuarioUltimaModificacion,FechaUltimaModificacion,Pantalla,Moroso)
            VALUES('" . $this->nombre . "','" . $this->ClaveCliente . "','" . $this->UsuarioCreacion . "',NOW(),'" . $this->UsuarioUltimaModificacion . "',NOW(),'" . $this->Pantalla . "','" . $this->Moroso . "')");
        $catalogo = new Catalogo(); $id = $catalogo->insertarRegistro($consulta);        
        if ($id != NULL && $id != 0) {
            if ($this->localidades != NULL) {
                foreach ($this->localidades as $value) {
                    $catalogo->obtenerLista("UPDATE c_centrocosto SET id_cr='" . $id . "' WHERE ClaveCentroCosto='" . $value . "'");
                }
            }            
            return true;
        }        
        return false;
    }

    public function updateRegistro() {
        $consulta = ("UPDATE c_cen_costo SET nombre='" . $this->nombre . "',ClaveCliente='" . $this->ClaveCliente . "',UsuarioUltimaModificacion='" . $this->UsuarioUltimaModificacion . "',FechaUltimaModificacion=NOW(),Pantalla='" . $this->Pantalla . "',Moroso='" . $this->Moroso . "'
            WHERE id_cc=" . $this->id_cc);
        $catalogo = new Catalogo(); $query = $catalogo->obtenerLista($consulta);
        if ($query == 1) {
            $catalogo->obtenerLista("UPDATE c_centrocosto SET id_cr=null WHERE id_cr=" . $this->id_cc);
            if ($this->localidades != NULL) {
                foreach ($this->localidades as $value) {
                    $catalogo->obtenerLista("UPDATE c_centrocosto SET id_cr='" . $this->id_cc . "' WHERE ClaveCentroCosto='" . $value . "'");
                }
            }            
            return true;
        }        
        return false;
    }

    public function deleteRegistro() {
        $catalogo = new Catalogo();
        $catalogo->obtenerLista("UPDATE c_centrocosto SET id_cr=null WHERE id_cr='" . $this->id_cc . "'");
        $query = $catalogo->obtenerLista("DELETE FROM c_cen_costo WHERE id_cc=" . $this->id_cc);        
        if ($query == 1) {
            return true;
        }
        return false;
    }

    public function getLocalidades() {
        return $this->localidades;
    }

    public function setLocalidades($localidades) {
        $this->localidades = $localidades;
    }

    public function getId_cc() {
        return $this->id_cc;
    }

    public function getNombre() {
        return $this->nombre;
    }

    public function getClaveCliente() {
        return $this->ClaveCliente;
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

    public function setId_cc($id_cc) {
        $this->id_cc = $id_cc;
    }

    public function setNombre($nombre) {
        $this->nombre = $nombre;
    }

    public function setClaveCliente($ClaveCliente) {
        $this->ClaveCliente = $ClaveCliente;
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

    public function getMoroso() {
        return $this->Moroso;
    }

    public function setMoroso($Moroso) {
        $this->Moroso = $Moroso;
    }

}
