<?php

include_once("Orden_Compra.class.php");
include_once("Catalogo.class.php");

class Detalle_Orden_Compra {

    private $idDetalle;
    private $idOrdenCompra;    
    private $noParteComponente;
    private $noParteEquipo;
    private $cantidad;
    private $precioUnitario;
    private $dolar;
    private $costoTotal;
    private $usuarioCreacion;
    private $usuarioModificacion;
    private $pantalla;
    private $totalCantidad;
    private $totalEntrada;
    private $totalRecibidaAlmacen;
    private $tipo;
    private $empresa;
    private $kg;
    private $Empaque;

    public function newRegistroCompnente() {    
        if(!isset($this->precioUnitario) || empty($this->precioUnitario)){
            $this->precioUnitario = "NULL";
        }
        if(!isset($this->cantidad) || empty($this->cantidad)){
            $this->cantidad = "NULL";
        }
        if(!isset($this->costoTotal) || empty($this->costoTotal)){
            $this->costoTotal = "NULL";
        }
        if(!isset($this->dolar) || empty($this->dolar)){
            $this->dolar = "NULL";
        }
       /* if(!isset($this->kg) || empty($this->kg)){
            $this->kg = "NULL";
        }
        if(!isset($this->Empaque) || empty($this->Empaque)){
            $this->Empaque = "NULL";
        }else{
            $this->Empaque = "'".$this->Empaque."'";
        }*/
        $consulta = "INSERT INTO k_orden_compra(IdDetalleOC,IdOrdenCompra,NoParteComponente,Cantidad,PrecioUnitario,Dolar,PrecioTotal,UsuarioCreacion,FechaCreacion,UsuarioUltimaModificacion,FechaUltimaModificacion,Pantalla)
    VALUES(0," . $this->idOrdenCompra . ",'" . $this->noParteComponente . "','" . $this->cantidad . "','" . $this->precioUnitario . "'," . $this->dolar . ",'" . $this->costoTotal . "','" . $this->usuarioCreacion . "',now(),'" . $this->usuarioModificacion . "',now(),'" . $this->pantalla . "')";
        

        $catalogo = new Catalogo();
        if (isset($this->empresa)) {
            $catalogo->setEmpresa($this->empresa);
        }
        $this->idDetalle = $catalogo->insertarRegistro($consulta);
        if ($this->idDetalle != NULL && $this->idDetalle != 0) {
            $orden = new Orden_Compra();
            $orden->setEmpresa($this->empresa);
            if($orden->getRegistroById($this->idOrdenCompra)){
                if($orden->getEstatus() == "70"){
                    $orden->setEstatus("72");
                    $orden->setUsuarioModificacion($_SESSION['user']);
                    $orden->setPantalla("nuevoRegistroComponente");
                    $orden->editRegistro();
                }
            }
            return true;
        }
        return false;
    }

    public function newRegistroEquipo() {
        $consulta = ("INSERT INTO k_orden_compra(IdDetalleOC,IdOrdenCompra,NoParteEquipo,Cantidad,PrecioUnitario,Dolar,PrecioTotal,UsuarioCreacion,FechaCreacion,UsuarioUltimaModificacion,FechaUltimaModificacion,Pantalla)
                              VALUES(0," . $this->idOrdenCompra . ",'" . $this->noParteEquipo . "','" . $this->cantidad . "','" . $this->precioUnitario . "'," . $this->dolar . ",'" . $this->costoTotal . "','" . $this->usuarioCreacion . "',now(),'" . $this->usuarioModificacion . "',now(),'" . $this->pantalla . "')");
        $catalogo = new Catalogo();
        if (isset($this->empresa)) {
            $catalogo->setEmpresa($this->empresa);
        }
        $this->idDetalle = $catalogo->insertarRegistro($consulta);
        if ($this->idDetalle != NULL && $this->idDetalle != 0) {
            $orden = new Orden_Compra();
            if($orden->getRegistroById($this->idOrdenCompra)){
                if($orden->getEstatus() == "70"){
                    $orden->setEstatus("72");
                    $orden->setUsuarioModificacion($_SESSION['user']);
                    $orden->setPantalla("nuevoRegistroComponente");
                    $orden->editRegistro();
                }
            }
            return true;
        }
        return false;
    }

    public function editRegistroCantidad() {
        $consulta = ("UPDATE k_orden_compra SET CantidadEntregada = CantidadEntregada+$this->cantidad,UsuarioUltimaModificacion = '" . $this->usuarioModificacion . "',FechaUltimaModificacion = now(),Pantalla = '" . $this->pantalla . "'
                WHERE IdDetalleOC='" . $this->idDetalle . "';");
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

    public function editRegistroComp() {
        $consulta = ("UPDATE k_orden_compra SET NoParteComponente = '$this->noParteComponente',Cantidad=$this->cantidad,PrecioUnitario='$this->precioUnitario',Dolar=$this->dolar,PrecioTotal='$this->costoTotal'
                ,UsuarioUltimaModificacion = '" . $this->usuarioModificacion . "',FechaUltimaModificacion = now(),Pantalla = '" . $this->pantalla . "'
                WHERE IdDetalleOC='" . $this->idDetalle . "';");
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

    public function editRegistroEquipo() {
        $consulta = ("UPDATE k_orden_compra SET NoParteEquipo = '$this->noParteEquipo',Cantidad=$this->cantidad,PrecioUnitario='$this->precioUnitario',Dolar=$this->dolar,PrecioTotal='$this->costoTotal'
                ,UsuarioUltimaModificacion = '" . $this->usuarioModificacion . "',FechaUltimaModificacion = now(),Pantalla = '" . $this->pantalla . "'
                WHERE IdDetalleOC='" . $this->idDetalle . "';");
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

    function deleteRegistro($ids) {
        $consulta = ("DELETE FROM k_orden_compra WHERE IdDetalleOC IN ($ids)");
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

    public function getNoParteBYID() {
        $consulta = ("SELECT NoParteComponente,NoParteEquipo FROM k_orden_compra WHERE IdDetalleOC=$this->idDetalle");
        $catalogo = new Catalogo();
        if (isset($this->empresa)) {
            $catalogo->setEmpresa($this->empresa);
        }
        
        $query = $catalogo->obtenerLista($consulta);
        while ($rs = mysql_fetch_array($query)) {
            $this->noParteComponente = $rs['NoParteComponente'];
            $this->noParteEquipo = $rs['NoParteEquipo'];
            return true;
        }
        return false;
    }    
    
    
    /**
     * 
     * @param type $tipo 0:componente, 1: Equipo
     * @return boolean
     */
    public function getCantidadesPorParte($tipo) {
        if($tipo == "0"){
            $where = " AND NoParteComponente = '$this->noParteComponente' ";
        }else if($tipo == "1"){
            $where = " AND NoParteEquipo = '$this->noParteEquipo' ";
        }else{
            $where = "";
        }
        $consulta = ("SELECT oc.IdOrdenCompra,
            SUM(oc.Cantidad) AS solicitadas,
            SUM(oc.CantidadEntregada) AS entregadas            
            FROM k_orden_compra oc 
            WHERE oc.IdOrdenCompra=$this->idOrdenCompra $where GROUP BY oc.IdOrdenCompra;");
        $catalogo = new Catalogo();
        if (isset($this->empresa)) {
            $catalogo->setEmpresa($this->empresa);
        }
        $query = $catalogo->obtenerLista($consulta);
        while ($rs = mysql_fetch_array($query)) {
            $this->totalCantidad = $rs['solicitadas'];
            $this->totalEntrada = $rs['entregadas'];
        }
        if (mysql_num_rows($query) > 0) {
            if ($this->totalCantidad == $this->totalEntrada) {
                return TRUE;
            } else {
                return FALSE;
            }
        }
        return FALSE;
    }
    
    public function getCantidades() {
        $consulta = ("SELECT oc.IdOrdenCompra,
            SUM(oc.Cantidad) AS solicitadas,
            SUM(oc.CantidadEntregada) AS entregadas,
            (SELECT SUM(koa.Cantidad) FROM k_orden_compra AS oc2 
                LEFT JOIN k_detalle_entrada_orden_compra AS de ON de.idKOrdenTrabajo = oc2.IdDetalleOC 
                LEFT JOIN k_det_entr_oc_almacen AS koa ON koa.Id_detalle_entrada = de.Id_detalle_entrada
                WHERE oc2.IdOrdenCompra = oc.IdOrdenCompra) AS Recibidos
            FROM k_orden_compra oc 
            WHERE oc.IdOrdenCompra=$this->idOrdenCompra GROUP BY oc.IdOrdenCompra;");
        $catalogo = new Catalogo();
        if (isset($this->empresa)) {
            $catalogo->setEmpresa($this->empresa);
        }
        
        $query = $catalogo->obtenerLista($consulta);
        while ($rs = mysql_fetch_array($query)) {
            $this->totalCantidad = $rs['solicitadas'];
            $this->totalEntrada = $rs['entregadas'];
            $this->totalRecibidaAlmacen = $rs['Recibidos'];
            if (mysql_num_rows($query) > 0) {                
                if ($this->totalCantidad <= $this->totalEntrada) {
                    return TRUE;
                } else {
                    return FALSE;
                }
            }
        }        
        return FALSE;
    }

    public function verificarExistencia() {
        $consulta = ("SELECT * FROM k_orden_compra oc WHERE oc.IdOrdenCompra=$this->idOrdenCompra AND NoParteComponente='" . $this->noParteComponente . "' GROUP BY oc.IdOrdenCompra;");

        $catalogo = new Catalogo();
        if (isset($this->empresa)) {
            $catalogo->setEmpresa($this->empresa);
        }
        $query = $catalogo->obtenerLista($consulta);
        if (mysql_num_rows($query) > 0) {
            return TRUE;
        }
        return FALSE;
    }

    public function editarCantidadComponente() {
        $consulta = ("UPDATE k_orden_compra oc SET Cantidad='" . $this->cantidad . "' WHERE IdOrdenCompra=$this->idOrdenCompra AND NoParteComponente='" . $this->noParteComponente . "';");
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

    public function verificarExistenciaEquipo() {
        $consulta = ("SELECT * FROM k_orden_compra oc WHERE oc.IdOrdenCompra=$this->idOrdenCompra AND NoParteEquipo='" . $this->noParteEquipo . "' GROUP BY oc.IdOrdenCompra;");
        $catalogo = new Catalogo();
        if (isset($this->empresa)) {
            $catalogo->setEmpresa($this->empresa);
        }
        $query = $catalogo->obtenerLista($consulta);
        if (mysql_num_rows($query) > 0) {
            return TRUE;
        }
        return FALSE;
    }

    public function editarCantidadEquipo() {
        $consulta = ("UPDATE k_orden_compra oc SET Cantidad='" . $this->cantidad . "' WHERE IdOrdenCompra=$this->idOrdenCompra AND NoParteEquipo='" . $this->noParteEquipo . "';");
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
        

    public function actualizarProforma($idCompra, $proforma, $usuario, $pantalla) {
        $consulta = "UPDATE `c_orden_compra` SET Proforma = '$proforma', UsuarioUltimaModificacion = '$usuario', 
            FechaUltimaModificacion = NOW(), Pantalla = '$pantalla' WHERE Id_orden_compra = $idCompra;";

            echo "".$consulta;
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

    public function actualizarNumeroOC($idCompra, $numeroOC, $usuario, $pantalla) {
        $consulta = "UPDATE `c_orden_compra` SET NumOC = '$numeroOC', UsuarioUltimaModificacion = '$usuario', 
            FechaUltimaModificacion = NOW(), Pantalla = '$pantalla' WHERE Id_orden_compra = $idCompra;";
            echo $consulta;
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

    public function getIdDetalle() {
        return $this->idDetalle;
    }

    public function getIdOrdenCompra() {
        return $this->idOrdenCompra;
    }

    public function getNoParteComponente() {
        return $this->noParteComponente;
    }

    public function getNoParteEquipo() {
        return $this->noParteEquipo;
    }

    public function getCantidad() {
        return $this->cantidad;
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

    public function setIdDetalle($idDetalle) {
        $this->idDetalle = $idDetalle;
    }

    public function setIdOrdenCompra($idOrdenCompra) {
        $this->idOrdenCompra = $idOrdenCompra;
    }

    public function setNoParteComponente($noParteComponente) {
        $this->noParteComponente = $noParteComponente;
    }

    public function setNoParteEquipo($noParteEquipo) {
        $this->noParteEquipo = $noParteEquipo;
    }

    public function setCantidad($cantidad) {
        $this->cantidad = $cantidad;
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

    public function getPrecioUnitario() {
        return $this->precioUnitario;
    }

    public function getDolar() {
        return $this->dolar;
    }

    public function getCostoTotal() {
        return $this->costoTotal;
    }

    public function setPrecioUnitario($precioUnitario) {
        $this->precioUnitario = $precioUnitario;
    }

    public function setDolar($dolar) {
        $this->dolar = $dolar;
    }

    public function setCostoTotal($costoTotal) {
        $this->costoTotal = $costoTotal;
    }

    public function getTotalCantidad() {
        return $this->totalCantidad;
    }

    public function getTotalEntrada() {
        return $this->totalEntrada;
    }

    public function setTotalCantidad($totalCantidad) {
        $this->totalCantidad = $totalCantidad;
    }

    public function setTotalEntrada($totalEntrada) {
        $this->totalEntrada = $totalEntrada;
    }
    
    public function getTotalRecibidaAlmacen() {
        return $this->totalRecibidaAlmacen;
    }

    public function setTotalRecibidaAlmacen($totalRecibidaAlmacen) {
        $this->totalRecibidaAlmacen = $totalRecibidaAlmacen;
    }
    
    public function getTipo() {
        return $this->tipo;
    }

    public function setTipo($tipo) {
        $this->tipo = $tipo;
    }

    public function getEmpresa() {
        return $this->empresa;
    }

    public function setEmpresa($empresa) {
        $this->empresa = $empresa;
    }
    public function setEmpaque($empaque){
        $this->Empaque = $empaque;
    }
    public function getEmpaque(){
        return $this->Empaque;
    }
    public function setKg($kg){
        $this->kg = $kg;
    }
    public function getKg() {
        return $this->kg;
    }
}    
