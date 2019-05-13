<?php

include_once ("Conexion.class.php");
include_once ("Catalogo.class.php");

class Detalle_entrada_Almacen_OC {

    private $id_det_oc;
    private $id_det_entrada;
    private $cantidad;
    private $ubicacion;
    private $usuarioCreacion;
    private $usuarioModificacion;
    private $pantalla;
    private $empresa;
    private $id_ticket;

    public function newRegistro() {
        $consulta = "INSERT INTO k_det_entr_oc_almacen(Id_det_ent_alm_oc, Id_detalle_entrada,Cantidad,Ubicacion, UsuarioCreacion, FechaCreacion, UsuarioUltimaModificacion,FechaUltimaModificacion, Pantalla) 
                        VALUES(0,'$this->id_det_entrada','$this->cantidad','$this->ubicacion','$this->usuarioCreacion', NOW(), '$this->usuarioModificacion', NOW(), '$this->pantalla');";
        $catalogo = new Catalogo();
        if (isset($this->empresa)) {
            $catalogo->setEmpresa($this->empresa);
        }
        $query = $catalogo->obtenerLista($consulta);
        if ($query == 1) {
            return true;
        }
        return false;
    }

    public function verificar_backorder($id_oc, $no_parte) {
        $consulta = "SELECT t.IdTicket FROM k_importacion_orden_compra ioc INNER JOIN c_notaticket nt ON ioc.IdNotaTicket=nt.IdNotaTicket
                    INNER JOIN c_ticket t ON nt.IdTicket=t.IdTicket WHERE ioc.IdOrdenCompra='$id_oc' AND ioc.noParte='$no_parte'";
        $catalogo = new Catalogo();
        if (isset($this->empresa)) {
            $catalogo->setEmpresa($this->empresa);
        }
        $query = $catalogo->obtenerLista($consulta);
        while ($rs = mysql_fetch_array($query)) {
            $this->id_ticket = $rs['IdTicket'];
            return true;
        }
        return FALSE;
    }

    public function verificar_no_serie($noSerie) {
        $consulta = "SELECT * FROM k_detalle_entrada_orden_compra kd WHERE kd.NoSerie='$noSerie'";
        $catalogo = new Catalogo();
        if (isset($this->empresa)) {
            $catalogo->setEmpresa($this->empresa);
        }
        $query = $catalogo->obtenerLista($consulta);
        if (mysql_num_rows($query) > 0) {
            return true;
        }
        return false;
    }

    public function getId_det_oc() {
        return $this->id_det_oc;
    }

    public function getId_det_entrada() {
        return $this->id_det_entrada;
    }

    public function getCantidad() {
        return $this->cantidad;
    }

    public function getUbicacion() {
        return $this->ubicacion;
    }

    public function getUsuarioCreacion() {
        return $this->usuarioCreacion;
    }

    public function getUsuarioModificacion() {
        return $this->usuarioModificacion;
    }

    public function getPantalla() {
        return $this->pantalla;
    }

    public function setId_det_oc($id_det_oc) {
        $this->id_det_oc = $id_det_oc;
    }

    public function setId_det_entrada($id_det_entrada) {
        $this->id_det_entrada = $id_det_entrada;
    }

    public function setCantidad($cantidad) {
        $this->cantidad = $cantidad;
    }

    public function setUbicacion($ubicacion) {
        $this->ubicacion = $ubicacion;
    }

    public function setUsuarioCreacion($usuarioCreacion) {
        $this->usuarioCreacion = $usuarioCreacion;
    }

    public function setUsuarioModificacion($usuarioModificacion) {
        $this->usuarioModificacion = $usuarioModificacion;
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

    public function getId_ticket() {
        return $this->id_ticket;
    }

    public function setId_ticket($id_ticket) {
        $this->id_ticket = $id_ticket;
    }

}
