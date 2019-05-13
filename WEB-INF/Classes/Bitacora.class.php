<?php

include_once("Conexion.class.php");
include_once ("Catalogo.class.php");

class Bitacora {

    private $idBitacota;
    private $noParte;
    private $noSerie;
    private $idAlmacen;
    private $usuarioCreacion;
    private $usuarioModificacion;
    private $pantalla;
    private $empresa;

    public function verficarExistencia() {
        $consulta = ("SELECT * FROM c_bitacora WHERE NoSerie='" . $this->noSerie . "'");
        $catalogo = new Catalogo();
        if (isset($this->empresa)) {
            $catalogo->setEmpresa($this->empresa);
        } $query = $catalogo->obtenerLista($consulta);
        if (mysql_num_rows($query) > 0) {
            return TRUE;
        }
        return FALSE;
    }

    public function newRegistro() {
        $consulta = ("INSERT INTO c_bitacora(id_bitacora,NoParte,NoSerie,IdTipoInventario,IdAlmacen,VentaDirecta,Activo,UsuarioCreacion,FechaCreacion,UsuarioUltimaModificacion,FechaUltimaModificacion,Pantalla)
            VALUES(0,'" . $this->noParte . "','" . $this->noSerie . "',1,'" . $this->idAlmacen . "',0,1,'" . $this->usuarioCreacion . "',now(),'" . $this->usuarioModificacion . "',now(),'" . $this->pantalla . "')");
        $catalogo = new Catalogo();
        if (isset($this->empresa)) {
            $catalogo->setEmpresa($this->empresa);
        } $query = $catalogo->obtenerLista($consulta);
        if ($query == 1) {
            return true;
        }
        return false;
    }       

    public function getIdBitacota() {
        return $this->idBitacota;
    }

    public function getNoParte() {
        return $this->noParte;
    }

    public function getNoSerie() {
        return $this->noSerie;
    }

    public function getIdAlmacen() {
        return $this->idAlmacen;
    }

    public function getUsuarioCreacion() {
        return $this->usuarioCreacion;
    }

    public function getUsuarioModificacion() {
        return $this->usuarioModificacion;
    }

    public function setIdBitacota($idBitacota) {
        $this->idBitacota = $idBitacota;
    }

    public function setNoParte($noParte) {
        $this->noParte = $noParte;
    }

    public function setNoSerie($noSerie) {
        $this->noSerie = $noSerie;
    }

    public function setIdAlmacen($idAlmacen) {
        $this->idAlmacen = $idAlmacen;
    }

    public function setUsuarioCreacion($usuarioCreacion) {
        $this->usuarioCreacion = $usuarioCreacion;
    }

    public function setUsuarioModificacion($usuarioModificacion) {
        $this->usuarioModificacion = $usuarioModificacion;
    }

    public function getPantalla() {
        return $this->pantalla;
    }

    public function setPantalla($pantalla) {
        $this->pantalla = $pantalla;
    }

    public function getEmpresa() {
        return $this->empresa;
    }

    public function setEmpresa($empresa) {
        $this->empresa = $empresa;
    }
}
