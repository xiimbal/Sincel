<?php

include_once ("Conexion.class.php");
include_once("Catalogo.class.php");

class TonerSolicitado {

    private $idTicket;
    private $idAlmacen;
    private $nombraAlmacen;
    private $usuarioCreacion;
    private $usuarioModificacion;
    private $pantalla;

    public function getAlmacen($idTicket) {
        $consulta = ("SELECT * FROM c_almacen a WHERE a.id_almacen=(SELECT ma.IdAlmacen FROM k_minialmacenlocalidad ma 
                            WHERE ma.ClaveCentroCosto=(SELECT t.ClaveCentroCosto FROM c_ticket t WHERE t.IdTicket='" . $idTicket . "')) ORDER BY a.nombre_almacen ASC");
        $catalogo = new Catalogo(); $query = $catalogo->obtenerLista($consulta);
        while ($rs = mysql_fetch_array($query)) {
            $this->idAlmacen = $rs['id_almacen'];
            $this->nombraAlmacen = $rs['nombre_almacen'];
            return true;
        }
        return false;
    }


    public function getIdTicket() {
        return $this->idTicket;
    }

    public function setIdTicket($idTicket) {
        $this->idTicket = $idTicket;
    }

    public function getIdAlmacen() {
        return $this->idAlmacen;
    }

    public function setIdAlmacen($idAlmacen) {
        $this->idAlmacen = $idAlmacen;
    }

    public function getNombraAlmacen() {
        return $this->nombraAlmacen;
    }

    public function setNombraAlmacen($nombraAlmacen) {
        $this->nombraAlmacen = $nombraAlmacen;
    }
    
    public function getUsuarioCreacion() {
        return $this->usuarioCreacion;
    }

    public function setUsuarioCreacion($usuarioCreacion) {
        $this->usuarioCreacion = $usuarioCreacion;
    }

    public function getUsuarioModificacion() {
        return $this->usuarioModificacion;
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
}

?>
