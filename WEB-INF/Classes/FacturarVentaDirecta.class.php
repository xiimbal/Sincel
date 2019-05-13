<?php

include_once("Catalogo.class.php");
include_once("Conexion.class.php");
include_once("CatalogoFacturacion.class.php");
include_once ("ConexionFacturacion.class.php");

class FacturarVentaDirecta {

    private $idVentaDirecta;
    private $idFactura;
    private $rfcCliente;
    private $nombreCliente;
    private $rfcEmpresa;
    private $nombreEmpresa;
    private $totalFactura;
    private $folio;
    private $serie;
    private $periodoFacturacion;
    private $usuarioModificacion;
    private $pantalla;
    private $pathFactura;

    public function getDatosFacturacion() {
        $consulta = ("SELECT cl.RFC AS rfcCliente,dfe.RFC AS rfcEmpresa,cl.NombreRazonSocial AS nombreCliente,dfe.RazonSocial AS nombreEmpresa,SUM(vdd.Costo * vdd.Cantidad)AS total
                                FROM c_ventadirecta vd,c_cliente cl,c_datosfacturacionempresa dfe,k_ventadirectadet vdd WHERE vd.ClaveCliente=cl.ClaveCliente AND cl.IdDatosFacturacionEmpresa=dfe.IdDatosFacturacionEmpresa AND vd.IdVentaDirecta=vdd.IdVentaDirecta AND vd.IdVentaDirecta='" . $this->idVentaDirecta . "'");
        $catalogo = new Catalogo(); $query = $catalogo->obtenerLista($consulta);
        while ($rs = mysql_fetch_array($query)) {
            $this->rfcCliente = $rs['rfcCliente'];
            $this->nombreCliente = $rs['nombreCliente'];
            $this->rfcEmpresa = $rs['rfcEmpresa'];
            $this->nombreEmpresa = $rs['nombreEmpresa'];
            $this->totalFactura = $rs['total'];
            return true;
        }
        return false;
    }

    public function newFactura() {
        $consulta = ("INSERT INTO c_factura(IdFactura,RFCReceptor,NombreReceptor,RFCEmisor,NombreEmisor,FechaFacturacion,Folio,Serie,PeriodoFacturacion,Observaciones,EstadoFactura,FechaModificacion,TipoComprobante,FacturaEnviada,FacturaPagada,Total,IdFacturaRelacion,CentrosCosto,cfdiRespPac,PathPDF)
            VALUES('" . $this->idFactura . "','" . $this->rfcCliente . "','" . $this->nombreCliente . "','" . $this->rfcEmpresa . "','" . $this->nombreEmpresa . "',now(),'" . $this->folio . "','" . $this->serie . "','" . $this->periodoFacturacion . "','',1,now(),'ingreso',0,0,'" . $this->totalFactura . "',0,'','','".$this->pathFactura."')");
        $catalogo = new CatalogoFacturacion(); $query = $catalogo->obtenerLista($consulta);
        if ($query == 1) {
            return true;
        }
        return false;
    }

    public function getUltimoIdRegistrado() {
        $consulta = ("SELECT MAX(f.IdFactura) AS ultimoId FROM c_factura f");
        $catalogo = new CatalogoFacturacion(); $query = $catalogo->obtenerLista($consulta);
        While ($rs = mysql_fetch_array($query)) {
            $this->idFactura = $rs['ultimoId'];
            return true;
        }
        return false;
    }

    public function editIdFactura() {
        $consulta = ("UPDATE c_ventadirecta SET id_factura = '" . $this->folio . "',UsuarioUltimaModificacion = '" . $this->usuarioModificacion . "',FechaUltimaModificacion = now(),Pantalla = '" . $this->pantalla . "' WHERE IdVentaDirecta='" . $this->idVentaDirecta . "';");        
        $catalogo = new Catalogo(); $query = $catalogo->obtenerLista($consulta);
        if ($query == 1) {
            return true;
        }
        return false;
    }

    public function getIdVentaDirecta() {
        return $this->idVentaDirecta;
    }

    public function setIdVentaDirecta($idVentaDirecta) {
        $this->idVentaDirecta = $idVentaDirecta;
    }

    public function getRfcCliente() {
        return $this->rfcCliente;
    }

    public function setRfcCliente($rfcCliente) {
        $this->rfcCliente = $rfcCliente;
    }

    public function getNombreCliente() {
        return $this->nombreCliente;
    }

    public function setNombreCliente($nombreCliente) {
        $this->nombreCliente = $nombreCliente;
    }

    public function getRfcEmpresa() {
        return $this->rfcEmpresa;
    }

    public function setRfcEmpresa($rfcEmpresa) {
        $this->rfcEmpresa = $rfcEmpresa;
    }

    public function getNombreEmpresa() {
        return $this->nombreEmpresa;
    }

    public function setNombreEmpresa($nombreEmpresa) {
        $this->nombreEmpresa = $nombreEmpresa;
    }

    public function getTotalFactura() {
        return $this->totalFactura;
    }

    public function setTotalFactura($totalFactura) {
        $this->totalFactura = $totalFactura;
    }

    public function getIdFactura() {
        return $this->idFactura;
    }

    public function setIdFactura($idFactura) {
        $this->idFactura = $idFactura;
    }

    public function getFolio() {
        return $this->folio;
    }

    public function setFolio($folio) {
        $this->folio = $folio;
    }

    public function getSerie() {
        return $this->serie;
    }

    public function setSerie($serie) {
        $this->serie = $serie;
    }

    public function getPeriodoFacturacion() {
        return $this->periodoFacturacion;
    }

    public function setPeriodoFacturacion($periodoFacturacion) {
        $this->periodoFacturacion = $periodoFacturacion;
    }

    public function getPantalla() {
        return $this->pantalla;
    }

    public function setPantalla($pantalla) {
        $this->pantalla = $pantalla;
    }
    public function getUsuarioModificacion() {
        return $this->usuarioModificacion;
    }

    public function setUsuarioModificacion($usuarioModificacion) {
        $this->usuarioModificacion = $usuarioModificacion;
    }
    public function getPathFactura() {
        return $this->pathFactura;
    }

    public function setPathFactura($pathFactura) {
        $this->pathFactura = $pathFactura;
    }



}

?>
