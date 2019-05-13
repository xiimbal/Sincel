<?php

include_once("Conexion.class.php");
include_once("Catalogo.class.php");

class Entrada_Orden_trabajo {

    private $idOrden;
    private $cantidad;
    private $almacen;
    private $ubicacion;
    private $cancelado;
    private $folioFactura;
    private $usuarioCreacion;
    private $usuarioModificacion;
    private $pantalla;
    private $noSerie;
    private $empresa;

    public function newRegistro() {
        if ($this->noSerie == "") {
            $serie = "NULL";
        } else {
            $serie = "'$this->noSerie'";
        }
        
        $consulta = ("INSERT INTO k_detalle_entrada_orden_compra(Id_detalle_entrada,idKOrdenTrabajo,Fecha,CantidadEntrada,Almacen,NoSerie,ubicacion,Cancelado,FolioFactura,UsuarioCreacion,FechaCreacion,UsuarioUltimaModificacion,FechaUltimaModificacion,Pantalla)
            VALUES(0," . $this->idOrden . ",now(),'" . $this->cantidad . "'," . $this->almacen . ",$serie,'" . $this->ubicacion . "',$this->cancelado,'" . $this->folioFactura . "','" . $this->usuarioCreacion . "',now(),'" . $this->usuarioModificacion . "',now(),'" . $this->pantalla . "')");
        
        $catalogo = new Catalogo();
        if (isset($this->empresa)) {
            $catalogo->setEmpresa($this->empresa);
        } 
        $this->idOrden = $catalogo->insertarRegistro($consulta);
        
        if($this->idOrden != NULL && $this->idOrden != 0){     
            return true;
        }
        return false;
    }
    
    public function marcarComoRecibidoAlmacen($usuario, $pantalla){
        $consulta = "UPDATE k_detalle_entrada_orden_compra SET RecibidoAlmacen = 1, UsuarioUltimaModificacion = '$usuario', 
            FechaUltimaModificacion = NOW(), Pantalla = '$pantalla' WHERE Id_detalle_entrada = $this->idOrden;";
        
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

    public function getIdOrden() {
        return $this->idOrden;
    }

    public function getCantidad() {
        return $this->cantidad;
    }

    public function getAlmacen() {
        return $this->almacen;
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

    public function setIdOrden($idOrden) {
        $this->idOrden = $idOrden;
    }

    public function setCantidad($cantidad) {
        $this->cantidad = $cantidad;
    }

    public function setAlmacen($almacen) {
        $this->almacen = $almacen;
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

    public function getNoSerie() {
        return $this->noSerie;
    }

    public function setNoSerie($noSerie) {
        $this->noSerie = $noSerie;
    }

    public function getCancelado() {
        return $this->cancelado;
    }

    public function setCancelado($cancelado) {
        $this->cancelado = $cancelado;
    }

    public function getFolioFactura() {
        return $this->folioFactura;
    }

    public function setFolioFactura($folioFactura) {
        $this->folioFactura = $folioFactura;
    }

    public function getEmpresa() {
        return $this->empresa;
    }

    public function setEmpresa($empresa) {
        $this->empresa = $empresa;
    }

}
