<?php

include_once ("ConexionFacturacion.class.php");
include_once ("Conexion.class.php");
include_once ("Catalogo.class.php");
include_once ("CatalogoFacturacion.class.php");

class ClientesFacturas {

    private $clienteFacturaAdeudo = array();
    private $nombreClienteAdeudo = array();
    private $RFCEmisorAdeudo = array();
    private $fechaFacturacion = array();
    private $clienteFactura = array();
    private $nombreCliente = array();
    private $RFCEmisor = array();
    private $clienteEquipo = array();
    private $claveCliente;
    private $idEstatusCobranza;
    private $claveZona;
    private $idDatosFacturacionEmpresa;
    private $idTipoCliente;
    private $claveGrupo;
    private $ejecutivoCuenta;
    private $nombreRazonSocial;
    private $rfc;
    private $activo;
    private $usuarioCreacion;
    private $usuarioModificacion;
    private $pantalla;
    private $idGiro;
    private $diferenciaFechas;
    private $ponerMoroso;
    private $clienteNoMarcados = array();

    public function ObtenerClienteFacturacionAdeudo() {
        $consulta = ("SELECT * FROM c_factura f  WHERE f.FacturaPagada=0 GROUP BY f.RFCReceptor ORDER BY f.RFCReceptor ASC");
        $catalogo = new CatalogoFacturacion(); $query = $catalogo->obtenerLista($consulta);
        $contador = 0;
        while ($rs = mysql_fetch_array($query)) {
            $this->clienteFacturaAdeudo[$contador] = $rs['RFCReceptor'];
//            $this->nombreClienteAdeudo[$contador] = $rs['NombreReceptor'];
//            $this->RFCEmisorAdeudo[$contador] = $rs['RFCEmisor'];
//            $this->fechaFacturacion[$contador] = $rs['FechaFacturacion'];
            $contador++;
        }
    }

    public function obtenerClientesFacturas() {
        $consulta = ("SELECT * FROM c_factura f GROUP BY f.RFCReceptor ORDER BY f.RFCReceptor ASC");
        $catalogo = new CatalogoFacturacion(); $query = $catalogo->obtenerLista($consulta);
        $contador = 0;
        while ($rs = mysql_fetch_array($query)) {
            $this->clienteFactura[$contador] = $rs['RFCReceptor'];
            $this->nombreCliente[$contador] = $rs['NombreReceptor'];
            $this->RFCEmisor[$contador] = $rs['RFCEmisor'];
            $this->fechaFacturacion[$contador] = $rs['FechaFacturacion'];
            $contador++;
        }
    }

    public function ObtenerClienteEquipos() {
        $consulta = ("SELECT * FROM c_cliente c ORDER BY c.RFC ASC");
        $catalogo = new Catalogo(); $query = $catalogo->obtenerLista($consulta);
        $contador = 0;
        while ($rs = mysql_fetch_array($query)) {
            $this->clienteEquipo[$contador] = $rs['RFC'];
            $contador++;
        }
    }

    public function ComprobarTipoCliente($rfcRecptor) {
        $consulta = ("SELECT f.RFCReceptor,MAX(f.FechaFacturacion),period_diff(date_format(now(), '%Y%m'), date_format(MAX(f.FechaFacturacion), '%Y%m')) as meses FROM c_factura f  WHERE f.RFCReceptor='" . $rfcRecptor . "' AND f.FacturaPagada=0 GROUP BY f.RFCReceptor");
        $catalogo = new CatalogoFacturacion(); $query = $catalogo->obtenerLista($consulta);
        while ($rs = mysql_fetch_array($query)) {
            $this->diferenciaFechas = $rs['meses'];
            return true;
        }
        return false;
        //SELECT f.RFCReceptor,MAX(f.FechaFacturacion),DATEDIFF(now(),MAX(f.FechaFacturacion))AS dif FROM c_factura f  WHERE f.FacturaPagada=0 GROUP BY f.RFCReceptor
    }

    public function ClientesNoMarcados() {
        $consulta = ("SELECT * FROM c_cliente c WHERE c.NoVolverMoroso=0 AND c.IdEstatusCobranza=1");
        $catalogo = new Catalogo(); $query = $catalogo->obtenerLista($consulta);
        $contador = 0;
        while ($rs = mysql_fetch_array($query)) {
            $this->clienteNoMarcados[$contador] = $rs['RFC'];
            $contador++;
        }
    }

    public function editarTipoCliente($claveCliente) {
        $consulta = ("UPDATE c_cliente SET IdEstatusCobranza = '2',UsuarioUltimaModificacion = '" . $this->usuarioModificacion . "',FechaUltimaModificacion = now(),Pantalla = '" . $this->pantalla . "'
            WHERE RFC='" . $claveCliente . "';");
        $catalogo = new Catalogo(); $query = $catalogo->obtenerLista($consulta);
        if ($query == 1) {
            return true;
        }
        return false;
    }

    public function InsertarCliente() {
        $consulta = ("SELECT MAX(CAST(ClaveCliente AS UNSIGNED)) AS maximo FROM `c_cliente`;");
        $catalogo = new Catalogo(); $query2 = $catalogo->obtenerLista($consulta);
        if ($rs = mysql_fetch_array($query2)) {
            $maximo = (int) $rs['maximo'];
            if ($maximo == "" || $maximo == 0) {
                $maximo = 1000;
            }
            $maximo++;
            $query = $catalogo->obtenerLista("INSERT INTO c_cliente(ClaveCliente,IdEstatusCobranza,IdDatosFacturacionEmpresa,NombreRazonSocial,RFC,Activo,UsuarioCreacion,FechaCreacion,UsuarioUltimaModificacion,FechaUltimaModificacion,Pantalla)
            VALUES('$maximo'," . $this->idEstatusCobranza . "," . $this->idDatosFacturacionEmpresa . ",'" . $this->nombreRazonSocial . "','" . $this->rfc . "',
                " . $this->activo . ",'" . $this->usuarioCreacion . "',now(),'" . $this->usuarioModificacion . "',now(),'" . $this->pantalla . "')");            
            if ($query == 1) {
                return true;
            }
        }
        return false;
    }

    public function datosFacturacion($rfcEmisor) {
        $consulta = ("SELECT * FROM c_datosfacturacionempresa fe  WHERE fe.RFC='" . $rfcEmisor . "'");
        $catalogo = new Catalogo(); $query = $catalogo->obtenerLista($consulta);
        while ($rs = mysql_fetch_array($query)) {
            $this->idDatosFacturacionEmpresa = $rs['IdDatosFacturacionEmpresa'];
            return TRUE;
        }
        return FALSE;
    }

    public function NoVolverAPonerComoMoroso($claveCliente) {
        $consulta = ("UPDATE c_cliente SET NoVolverMoroso = '" . $this->ponerMoroso . "',UsuarioUltimaModificacion = '" . $this->usuarioModificacion . "',FechaUltimaModificacion = now(),Pantalla = '" . $this->pantalla . "'
            WHERE ClaveCliente='" . $claveCliente . "';");
        $catalogo = new Catalogo(); $query = $catalogo->obtenerLista($consulta);
        if ($query == 1) {
            return true;
        }
        return false;
    }

    public function getClienteFactura() {
        return $this->clienteFactura;
    }

    public function setClienteFactura($clienteFactura) {
        $this->clienteFactura = $clienteFactura;
    }

    public function getClienteEquipo() {
        return $this->clienteEquipo;
    }

    public function setClienteEquipo($clienteEquipo) {
        $this->clienteEquipo = $clienteEquipo;
    }

    public function getClienteFacturaAdeudo() {
        return $this->clienteFacturaAdeudo;
    }

    public function setClienteFacturaAdeudo($clienteFacturaAdeudo) {
        $this->clienteFacturaAdeudo = $clienteFacturaAdeudo;
    }

    public function getClaveCliente() {
        return $this->claveCliente;
    }

    public function setClaveCliente($claveCliente) {
        $this->claveCliente = $claveCliente;
    }

    public function getIdEstatusCobranza() {
        return $this->idEstatusCobranza;
    }

    public function setIdEstatusCobranza($idEstatusCobranza) {
        $this->idEstatusCobranza = $idEstatusCobranza;
    }

    public function getClaveZona() {
        return $this->claveZona;
    }

    public function setClaveZona($claveZona) {
        $this->claveZona = $claveZona;
    }

    public function getIdDatosFacturacionEmpresa() {
        return $this->idDatosFacturacionEmpresa;
    }

    public function setIdDatosFacturacionEmpresa($idDatosFacturacionEmpresa) {
        $this->idDatosFacturacionEmpresa = $idDatosFacturacionEmpresa;
    }

    public function getIdTipoCliente() {
        return $this->idTipoCliente;
    }

    public function setIdTipoCliente($idTipoCliente) {
        $this->idTipoCliente = $idTipoCliente;
    }

    public function getClaveGrupo() {
        return $this->claveGrupo;
    }

    public function setClaveGrupo($claveGrupo) {
        $this->claveGrupo = $claveGrupo;
    }

    public function getEjecutivoCuenta() {
        return $this->ejecutivoCuenta;
    }

    public function setEjecutivoCuenta($ejecutivoCuenta) {
        $this->ejecutivoCuenta = $ejecutivoCuenta;
    }

    public function getNombreRazonSocial() {
        return $this->nombreRazonSocial;
    }

    public function setNombreRazonSocial($nombreRazonSocial) {
        $this->nombreRazonSocial = $nombreRazonSocial;
    }

    public function getRfc() {
        return $this->rfc;
    }

    public function setRfc($rfc) {
        $this->rfc = $rfc;
    }

    public function getActivo() {
        return $this->activo;
    }

    public function setActivo($activo) {
        $this->activo = $activo;
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

    public function getIdGiro() {
        return $this->idGiro;
    }

    public function setIdGiro($idGiro) {
        $this->idGiro = $idGiro;
    }

    public function getNombreClienteAdeudo() {
        return $this->nombreClienteAdeudo;
    }

    public function setNombreClienteAdeudo($nombreClienteAdeudo) {
        $this->nombreClienteAdeudo = $nombreClienteAdeudo;
    }

    public function getRFCEmisorAdeudo() {
        return $this->RFCEmisorAdeudo;
    }

    public function setRFCEmisorAdeudo($RFCEmisorAdeudo) {
        $this->RFCEmisorAdeudo = $RFCEmisorAdeudo;
    }

    public function getFechaFacturacion() {
        return $this->fechaFacturacion;
    }

    public function setFechaFacturacion($fechaFacturacion) {
        $this->fechaFacturacion = $fechaFacturacion;
    }

    public function getNombreCliente() {
        return $this->nombreCliente;
    }

    public function setNombreCliente($nombreCliente) {
        $this->nombreCliente = $nombreCliente;
    }

    public function getRFCEmisor() {
        return $this->RFCEmisor;
    }

    public function setRFCEmisor($RFCEmisor) {
        $this->RFCEmisor = $RFCEmisor;
    }

    public function getDiferenciaFechas() {
        return $this->diferenciaFechas;
    }

    public function setDiferenciaFechas($diferenciaFechas) {
        $this->diferenciaFechas = $diferenciaFechas;
    }

    public function getPonerMoroso() {
        return $this->ponerMoroso;
    }

    public function setPonerMoroso($ponerMoroso) {
        $this->ponerMoroso = $ponerMoroso;
    }

    public function getClienteNoMarcados() {
        return $this->clienteNoMarcados;
    }

    public function setClienteNoMarcados($clienteNoMarcados) {
        $this->clienteNoMarcados = $clienteNoMarcados;
    }

}

?>
