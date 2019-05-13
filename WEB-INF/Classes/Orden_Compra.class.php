<?php

include_once("Conexion.class.php");
include_once("Catalogo.class.php");

class Orden_Compra {

    private $idOrdenCompra;
    private $fechaOC;
    private $facturaEmisor;
    private $facturaRecptor;
    private $estatus;
    private $condicionPago;
    private $embarca;
    private $noCliente;
    private $noPedidoProv;
    private $notas;
    private $transportista;
    private $peso;
    private $metros;
    private $origen;
    private $metodoEntrega;
    private $observacion;
    private $tipoCambio;
    private $activo;
    private $pathFactura;
    private $usuarioCreacion;
    private $usuarioModificacion;
    private $pantalla;
    private $almacen;
    private $empresa;
    private $no_pedido;
    private $nom_condicionPago;
    private $Factura_Ticket;
    private $Descripcion_Ticket;
    private $Subtotal_Ticket;
    private $Total_Ticket;
    private $nombreProveedor;

    public function getRegistroById($id) {
        $consulta = ("SELECT *,fp.Nombre AS condicion, p.NombreComercial AS Proveedor
            FROM c_orden_compra oc 
            LEFT JOIN c_formapago fp ON oc.CondicionesPago=fp.IdFormaPago 
            LEFT JOIN c_proveedor AS p ON oc.FacturaEmisor = p.ClaveProveedor
            WHERE Id_orden_compra='$id'");
        $catalogo = new Catalogo();
        if (isset($this->empresa)) {
            $catalogo->setEmpresa($this->empresa);
        } $query = $catalogo->obtenerLista($consulta);
        while ($rs = mysql_fetch_array($query)) {
            $this->idOrdenCompra = $rs['Id_orden_compra'];
            $this->fechaOC = $rs['FechaOrdenCompra'];
            $this->facturaEmisor = $rs['FacturaEmisor'];
            $this->facturaRecptor = $rs['FacturaReceptor'];
            $this->condicionPago = $rs['CondicionesPago'];
            $this->nom_condicionPago = $rs['condicion'];
            $this->estatus = $rs['Estatus'];
            $this->embarca = $rs['Embarca'];
            $this->noCliente = $rs['NoCliente'];
            $this->noPedidoProv = $rs['NoPedidoProv'];
            $this->notas = $rs['Notas'];
            $this->transportista = $rs['Transportista'];
            $this->peso = $rs['Peso'];
            $this->metros = $rs['Metros'];
            $this->origen = $rs['Origen'];
            $this->metodoEntrega = $rs['MetodoEntrega'];
            $this->observacion = $rs['Observaciones'];
            $this->tipoCambio = $rs['TipoCambio'];
            $this->activo = $rs['Activo'];
            $this->almacen = $rs['IdAlmacen'];
            $this->pathFactura = $rs['PathFactura'];
            $this->no_pedido = $rs['NoPedido'];
            $this->Factura_Ticket = $rs['Factura_Ticket'];
            $this->Descripcion_Ticket = $rs['Descripcion_Ticket'];
            $this->Subtotal_Ticket = $rs['Subtotal_Ticket'];
            $this->Total_Ticket = $rs['Total_Ticket'];
            $this->nombreProveedor = $rs['Proveedor'];
            return true;
        }
        return false;
    }

    public function newRegistro() {
        $trans = "'$this->transportista'";
        $precioDolar = "'$this->tipoCambio'";
        if ($this->transportista == "0" || empty($this->transportista)) {
            $trans = "NULL";
        }
        if ($this->tipoCambio == "") {
            $precioDolar = "NULL";
        }
        if (!isset($this->almacen) || empty($this->almacen)) {
            $this->almacen = "NULL";
        }
        if (!isset($this->condicionPago) || empty($this->condicionPago)) {
            $this->condicionPago = "NULL";
        }
        if (isset($this->estatus) && $this->estatus == "NULL") {
            $this->estatus = "NULL";
        }else{
            $this->estatus = "'$this->estatus'";
        }
        $consulta = ("INSERT INTO c_orden_compra(Id_orden_compra,NoPedido,FechaOrdenCompra,FacturaEmisor,FacturaReceptor,Estatus,CondicionesPago,
                        Embarca,NoCliente,NoPedidoProv,Notas,Transportista,Peso,Metros,Origen,MetodoEntrega,Observaciones,TipoCambio,IdAlmacen,
                        Activo,UsuarioCreacion,FechaCreacion,UsuarioUltimaModificacion,FechaUltimaModificacion,Pantalla)
                        VALUES(0,'" . $this->no_pedido . "','" . $this->fechaOC . "','" . $this->facturaEmisor . "','" . $this->facturaRecptor . "'," . $this->estatus . "," . $this->condicionPago . ",
                                '" . $this->embarca . "','" . $this->noCliente . "','" . $this->noPedidoProv . "','" . $this->notas . "',$trans,'" . $this->peso . "','" . $this->metros . "','" . $this->origen . "','" . $this->metodoEntrega . "','" . $this->observacion . "',$precioDolar," . $this->almacen . ",                             
                                " . $this->activo . ",'" . $this->usuarioCreacion . "',now(),'" . $this->usuarioModificacion . "',now(),'" . $this->pantalla . "')");
        //echo $consulta;
        $catalogo = new Catalogo();
        if (isset($this->empresa)) {
            $catalogo->setEmpresa($this->empresa);
        } $this->idOrdenCompra = $catalogo->insertarRegistro($consulta);
        if ($this->idOrdenCompra != NULL && $this->idOrdenCompra != 0) {
            return true;
        }
        return false;
    }

    public function editTodoRegistro() {
        $trans = "'$this->transportista'";
        $precioDolar = "'$this->tipoCambio'";
        $ped = "'$this->no_pedido'";
        if ($this->transportista == "0") {
            $trans = "NULL";
        }
        if ($this->tipoCambio == "") {
            $precioDolar = "NULL";
        }
        if ($this->no_pedido == "") {
            $ped = "NULL";
        }
        if($this->Factura_Ticket != "1"){$this->Factura_Ticket = "0";}
        if(empty($this->Descripcion_Ticket)){$this->Descripcion_Ticket = "NULL";}else{$this->Descripcion_Ticket = "'$this->Descripcion_Ticket'";}
        if(empty($this->Subtotal_Ticket)){$this->Subtotal_Ticket = "NULL";}
        if(empty($this->Total_Ticket)){$this->Total_Ticket = "NULL";}
        $consulta = ("UPDATE c_orden_compra SET NoPedido=$ped, FechaOrdenCompra = '$this->fechaOC',FacturaEmisor = '$this->facturaEmisor',FacturaReceptor = '$this->facturaRecptor',Estatus = '$this->estatus',CondicionesPago = '$this->condicionPago',
            Embarca = '$this->embarca',NoCliente = '$this->noCliente',NoPedidoProv = '$this->noPedidoProv',Notas = '$this->notas',Transportista = $trans,Peso = '$this->peso',Metros = '$this->metros',
                Origen = '$this->origen',MetodoEntrega = '$this->metodoEntrega',Observaciones='" . $this->observacion . "',TipoCambio=$precioDolar,
            UsuarioUltimaModificacion = '$this->usuarioModificacion', FechaUltimaModificacion = now(), Pantalla = '$this->pantalla',IdAlmacen='$this->almacen',
            Factura_Ticket = $this->Factura_Ticket,Descripcion_Ticket = $this->Descripcion_Ticket,Subtotal_Ticket = $this->Subtotal_Ticket, Total_Ticket = $this->Total_Ticket
            WHERE Id_orden_compra = $this->idOrdenCompra;");
        $catalogo = new Catalogo();
        if (isset($this->empresa)) {
            $catalogo->setEmpresa($this->empresa);
        } $query = $catalogo->obtenerLista($consulta);
        if ($query == 1) {
            return true;
        }
        return false;
    }

    public function editRegistro() {
        $consulta = ("UPDATE c_orden_compra SET Estatus = '$this->estatus',
            UsuarioUltimaModificacion = '$this->usuarioModificacion', FechaUltimaModificacion = now(), Pantalla = '$this->pantalla'
            WHERE Id_orden_compra = $this->idOrdenCompra;");
        $catalogo = new Catalogo();
        if (isset($this->empresa)) {
            $catalogo->setEmpresa($this->empresa);
        } $query = $catalogo->obtenerLista($consulta);
        if ($query == 1) {
            return true;
        }
        return false;
    }

    public function editRegistroPathFactura() {
        if ($this->pathFactura == "") {
            $pdf = "NULL";
        } else {
            $pdf = "'$this->pathFactura'";
        }
        $consulta = ("UPDATE c_orden_compra SET PathFactura = $pdf,
            UsuarioUltimaModificacion = '$this->usuarioModificacion', FechaUltimaModificacion = now(), Pantalla = '$this->pantalla'
            WHERE Id_orden_compra = $this->idOrdenCompra;");
        $catalogo = new Catalogo();
        if (isset($this->empresa)) {
            $catalogo->setEmpresa($this->empresa);
        } $query = $catalogo->obtenerLista($consulta);
        if ($query == 1) {
            return true;
        }
        return false;
    }

    public function deleteRegistroDetalle() {
        $elimiarDetalle = "DELETE FROM k_orden_compra WHERE IdOrdenCompra = $this->idOrdenCompra;";
        $consulta = ($elimiarDetalle);
        $catalogo = new Catalogo();
        if (isset($this->empresa)) {
            $catalogo->setEmpresa($this->empresa);
        } $query = $catalogo->obtenerLista($consulta);
        if ($query == 1) {
            return true;
        }
        return false;
    }

    public function deleteRegistroOC() {
        $elimiarOC = "DELETE FROM c_orden_compra WHERE Id_orden_compra = $this->idOrdenCompra;";
        $consulta = ($elimiarOC);
        $catalogo = new Catalogo();
        if (isset($this->empresa)) {
            $catalogo->setEmpresa($this->empresa);
        } $query = $catalogo->obtenerLista($consulta);
        if ($query == 1) {
            return true;
        }
        return false;
    }

    public function getCompraBySerie($serie) {
        $consulta = "SELECT MAX(coc.Id_orden_compra) AS Orden_compra, coc.FechaCreacion,  koc.PrecioUnitario, deo.FolioFactura FROM `k_detalle_entrada_orden_compra` AS deo
                        LEFT JOIN k_orden_compra AS koc ON koc.IdDetalleOC = deo.idKOrdenTrabajo LEFT JOIN c_orden_compra AS coc ON coc.Id_orden_compra = koc.IdOrdenCompra WHERE deo.NoSerie = '$serie';";
        $catalogo = new Catalogo();
        if (isset($this->empresa)) {
            $catalogo->setEmpresa($this->empresa);
        }
        $query = $catalogo->obtenerLista($consulta);
        return $query;
    }

    public function get_registro_by_id_detalle($id) {
        $consulta = "SELECT koc.IdOrdenCompra FROM k_orden_compra koc INNER JOIN k_detalle_entrada_orden_compra kd ON koc.IdDetalleOC=kd.idKOrdenTrabajo WHERE kd.Id_detalle_entrada='$id';";
        $catalogo = new Catalogo();
        if (isset($this->empresa)) {
            $catalogo->setEmpresa($this->empresa);
        }
        $query = $catalogo->obtenerLista($consulta);
        $id_oc = "";
        while ($rs = mysql_fetch_array($query)) {
            $id_oc = $rs['IdOrdenCompra'];
        }
        return $id_oc;
    }
    
    public function newRegistroTicket(){
        if(empty($this->facturaRecptor)){
            $this->facturaRecptor = "NULL";
        }
        $consulta = "INSERT INTO c_orden_compra (NoPedido,FechaOrdenCompra, FacturaEmisor, FacturaReceptor, CondicionesPago, Estatus,
        Factura_Ticket, Descripcion_Ticket, Subtotal_Ticket, Total_Ticket, Activo, UsuarioCreacion, FechaCreacion, UsuarioUltimaModificacion, FechaUltimaModificacion, Pantalla) 
        VALUES('$this->no_pedido','$this->fechaOC', '$this->facturaEmisor', $this->facturaRecptor, $this->condicionPago, $this->estatus, $this->Factura_Ticket, '$this->Descripcion_Ticket',
        $this->Subtotal_Ticket, $this->Total_Ticket, $this->activo, '$this->usuarioCreacion', now(), '$this->usuarioModificacion', now(), '$this->pantalla')";
        $catalogo = new Catalogo();
        if (isset($this->empresa)) {
            $catalogo->setEmpresa($this->empresa);
        } $this->idOrdenCompra = $catalogo->insertarRegistro($consulta);
        if ($this->idOrdenCompra != NULL && $this->idOrdenCompra != 0) {
            return true;
        }
        return false;
    }
    
    public function registrarRelacionTyF($idTicket, $pago){
        if(empty($pago) || $pago < "0"){
            $pago = "0";
        }
        $consulta = "INSERT INTO k_tickets_oc (IdTicket, IdOrdenCompra, PagoChofer) VALUES ($idTicket, $this->idOrdenCompra, $pago)";
        $catalogo = new Catalogo();
        if (isset($this->empresa)) {
            $catalogo->setEmpresa($this->empresa);
        }
        $result = $catalogo->obtenerLista($consulta);
        if ($result == 1) {
            return true;
        }
        return false;
    }

    public function getIdOrdenCompra() {
        return $this->idOrdenCompra;
    }

    public function getFechaOC() {
        return $this->fechaOC;
    }

    public function getFacturaEmisor() {
        return $this->facturaEmisor;
    }

    public function getFacturaRecptor() {
        return $this->facturaRecptor;
    }

    public function getEstatus() {
        return $this->estatus;
    }

    public function getCondicionPago() {
        return $this->condicionPago;
    }

    public function getActivo() {
        return $this->activo;
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

    public function setIdOrdenCompra($idOrdenCompra) {
        $this->idOrdenCompra = $idOrdenCompra;
    }

    public function setFechaOC($fechaOC) {
        $this->fechaOC = $fechaOC;
    }

    public function setFacturaEmisor($facturaEmisor) {
        $this->facturaEmisor = $facturaEmisor;
    }

    public function setFacturaRecptor($facturaRecptor) {
        $this->facturaRecptor = $facturaRecptor;
    }

    public function setEstatus($estatus) {
        $this->estatus = $estatus;
    }

    public function setCondicionPago($condicionPago) {
        $this->condicionPago = $condicionPago;
    }

    public function setActivo($activo) {
        $this->activo = $activo;
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

    public function getNoCliente() {
        return $this->noCliente;
    }

    public function getNoPedidoProv() {
        return $this->noPedidoProv;
    }

    public function getNotas() {
        return $this->notas;
    }

    public function getTransportista() {
        return $this->transportista;
    }

    public function getPeso() {
        return $this->peso;
    }

    public function getMetros() {
        return $this->metros;
    }

    public function getOrigen() {
        return $this->origen;
    }

    public function getMetodoEntrega() {
        return $this->metodoEntrega;
    }

    public function setNoCliente($noCliente) {
        $this->noCliente = $noCliente;
    }

    public function setNoPedidoProv($noPedidoProv) {
        $this->noPedidoProv = $noPedidoProv;
    }

    public function setNotas($notas) {
        $this->notas = $notas;
    }

    public function setTransportista($transportista) {
        $this->transportista = $transportista;
    }

    public function setPeso($peso) {
        $this->peso = $peso;
    }

    public function setMetros($metros) {
        $this->metros = $metros;
    }

    public function setOrigen($origen) {
        $this->origen = $origen;
    }

    public function setMetodoEntrega($metodoEntrega) {
        $this->metodoEntrega = $metodoEntrega;
    }

    public function getEmbarca() {
        return $this->embarca;
    }

    public function setEmbarca($embarca) {
        $this->embarca = $embarca;
    }

    public function getObservacion() {
        return $this->observacion;
    }

    public function setObservacion($observacion) {
        $this->observacion = $observacion;
    }

    public function getTipoCambio() {
        return $this->tipoCambio;
    }

    public function setTipoCambio($tipoCambio) {
        $this->tipoCambio = $tipoCambio;
    }

    public function getAlmacen() {
        return $this->almacen;
    }

    public function setAlmacen($almacen) {
        $this->almacen = $almacen;
    }

    public function getPathFactura() {
        return $this->pathFactura;
    }

    public function setPathFactura($pathFactura) {
        $this->pathFactura = $pathFactura;
    }

    public function getEmpresa() {
        return $this->empresa;
    }

    public function setEmpresa($empresa) {
        $this->empresa = $empresa;
    }

    public function getNo_pedido() {
        return $this->no_pedido;
    }

    public function setNo_pedido($no_pedido) {
        $this->no_pedido = $no_pedido;
    }
    public function getNom_condicionPago() {
        return $this->nom_condicionPago;
    }

    public function setNom_condicionPago($nom_condicionPago) {
        $this->nom_condicionPago = $nom_condicionPago;
    }

    function getFactura_Ticket() {
        return $this->Factura_Ticket;
    }

    function getDescripcion_Ticket() {
        return $this->Descripcion_Ticket;
    }

    function getSubtotal_Ticket() {
        return $this->Subtotal_Ticket;
    }

    function getTotal_Ticket() {
        return $this->Total_Ticket;
    }

    function setFactura_Ticket($Factura_Ticket) {
        $this->Factura_Ticket = $Factura_Ticket;
    }

    function setDescripcion_Ticket($Descripcion_Ticket) {
        $this->Descripcion_Ticket = $Descripcion_Ticket;
    }

    function setSubtotal_Ticket($Subtotal_Ticket) {
        $this->Subtotal_Ticket = $Subtotal_Ticket;
    }

    function setTotal_Ticket($Total_Ticket) {
        $this->Total_Ticket = $Total_Ticket;
    }
    
    function getNombreProveedor() {
        return $this->nombreProveedor;
    }

    function setNombreProveedor($nombreProveedor) {
        $this->nombreProveedor = $nombreProveedor;
    }
}
