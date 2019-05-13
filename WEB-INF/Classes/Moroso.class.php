<?php

include_once ("Conexion.class.php");
include_once("Catalogo.class.php");

class Moroso {

    private $cliente;
    private $localidad;

    public function MorosoCliente() {
        $consulta = ("UPDATE c_cliente SET IdEstatusCobranza=2 WHERE ClaveCliente='" . $this->cliente . "'");
        $catalogo = new Catalogo(); $query = $catalogo->obtenerLista($consulta);
        if ($query == 1) {
            return true;
        }
        return false;
    }

    public function MorosoLocalidad() {
        $consulta = ("UPDATE c_centrocosto SET Moroso=0 WHERE ClaveCliente='" . $this->cliente . "'");
        $catalogo = new Catalogo(); 
        foreach ($this->localidad as $value) {
            $query = $catalogo->obtenerLista("UPDATE c_centrocosto SET Moroso=1 WHERE ClaveCentroCosto='" . $value . "'");
        }
        $query = $catalogo->obtenerLista($consulta);
        if ($query >= 1) {
            return true;
        }
        return false;
    }

    public function MorosoCC() {
        $consulta = ("UPDATE c_cen_costo SET Moroso=0 WHERE ClaveCliente='" . $this->cliente . "'");
        $catalogo = new Catalogo();
        foreach ($this->localidad as $value) {
            $query = $catalogo->obtenerLista("UPDATE c_cen_costo SET Moroso=1 WHERE id_cc='" . $value . "'");
        }
        $query = $catalogo->obtenerLista($consulta);
        if ($query >= 1) {
            return true;
        }
        return false;
    }

    public function getCliente() {
        return $this->cliente;
    }

    public function getLocalidad() {
        return $this->localidad;
    }

    public function setCliente($cliente) {
        $this->cliente = $cliente;
    }

    public function setLocalidad($localidad) {
        $this->localidad = $localidad;
    }

}
