<?php

include_once ("ConexionFacturacion.class.php");
include_once ("Conexion.class.php");
include_once ("Catalogo.class.php");
include_once ("CatalogoFacturacion.class.php");
include_once ("Parametros.class.php");

class ClientesExportacion {

    private $clientesEquipos = array();
    private $clientesMorosos = array();
    private $clientesFacturacion = array();
    private $nombreFacturacion = array();
    private $RFCEmisor = array();
    private $nombreFacturacionMoroso = array();
    private $RFCEmisorMoroso = array();
    private $serieMoroso = array();
    private $idEstatusCobranza;
    private $idDatosFacturacionEmpresa;
    private $nombreRazonSocial;
    private $rfc;
    private $activo;
    private $usuarioCreacion;
    private $usuarioModificacion;
    private $pantalla;
    private $dDatosFacturacion;
    private $rfcDatosFacturacion;
    private $clienteNoMarcados;
    private $ponerMoroso;
    private $folio = array();
    private $fechaFacturada;
    private $nombreCliente;
    private $nombreEjecutivo;
    private $xml = array();
    private $empresa;

    /**
     * Obtiene todos los clientes de la base de facturacion.
     */
    public function ObtenerClientesfacturacion() {
        $consulta = ("
            SELECT TRIM(RFCReceptor) AS RFCReceptor, TRIM(RFCEmisor) AS RFCEmisor, NombreReceptor, Folio, FechaFacturacion, FacturaXML 
            FROM c_factura f WHERE f.FechaFacturacion >= '2013-03-01 00:00:00' 
            AND !ISNULL(FacturaXML) AND FacturaXML <> '' AND RFCReceptor <> '' 
            GROUP BY f.RFCReceptor ORDER BY f.RFCReceptor ASC;");
        $catalogo = new CatalogoFacturacion();
        if (isset($this->empresa)) {
            $catalogo->setEmpresa($this->empresa);
        }
        $query = $catalogo->obtenerLista($consulta);
        $contador = 0;
        while ($rs = mysql_fetch_array($query)) {            
            $this->clientesFacturacion[$contador] = $rs['RFCReceptor'];
            $this->nombreFacturacion[$contador] = $rs['NombreReceptor'];
            $this->RFCEmisor[$contador] = $rs['RFCEmisor'];
            $this->folio[$contador] = $rs['Folio'];
            $this->fechaFacturada[$contador] = $rs['FechaFacturacion'];
            $this->xml[$contador] = $rs['FacturaXML'];
            $contador++;
        }
    }

    /**
     * Todos los RFC's de los clientes de la base de operacion.
     */
    public function ObtenerClienteEquipos() {
        $consulta = ("SELECT TRIM(RFC) AS RFC FROM c_cliente c ORDER BY c.RFC ASC");
        $catalogo = new Catalogo();
        if (isset($this->empresa)) {
            $catalogo->setEmpresa($this->empresa);
        }
        $query = $catalogo->obtenerLista($consulta);
        $contador = 0;
        while ($rs = mysql_fetch_array($query)) {
            $this->clientesEquipos[$contador] = $rs['RFC'];
            $contador++;
        }
    }

    /**
     * Obtiene los clientes que tienen facturas no pagadas desde hace "n" meses
     */
    public function ObtenerClientesMorosos() {
        $parametro = new Parametros();        
        if(isset($this->empresa)){
            $parametro->setEmpresa($this->empresa);
        }
        $parametro->getRegistroById("12");
        
        if (($parametro->getValor()) != null) {
            $meses = $parametro->getValor();
        } else {
            $meses = 3;
        }
        $consulta = "SELECT TRIM(RFCReceptor) AS RFCReceptor,NombreReceptor, MIN(FechaFacturacion) AS FechaFacturacion, Folio AS folio 
            FROM c_factura WHERE Serie<>'PREF' AND TipoComprobante = 'ingreso' AND EstadoFactura<>0 AND (ISNULL(EstatusFactura) OR EstatusFactura<>3) AND FacturaPagada = 0 
            AND FechaFacturacion <= DATE_SUB(NOW(),INTERVAL $meses MONTH) GROUP BY RFCReceptor ORDER BY FechaFacturacion DESC;";
        $catalogo = new CatalogoFacturacion();
        if (isset($this->empresa)) {
            $catalogo->setEmpresa($this->empresa);
        }$query = $catalogo->obtenerLista($consulta);
        $contador = 0;
        while ($rs = mysql_fetch_array($query)) {
            $this->clientesMorosos[$contador] = $rs['RFCReceptor'];
            $this->serieMoroso[$contador] = $rs['folio'];
            //echo "<br/>PRocesando calse ".$rs['RFCReceptor']." con ".$rs['folio'];
            $contador++;
        }
    }

    /**
     * Obtiene los clientes marcados como morosos pero que ya no tienen facturas no pagadas de mas de n meses.
     * @return type query con los clientes.
     */
    public function ObtenerClientesMorososSinAdeudo() {
        $parametro = new Parametros();
        if(isset($this->empresa)){
            $parametro->setEmpresa($this->empresa);
        }
        $parametro->getRegistroById("12");
        if (($parametro->getValor()) != null) {
            $meses = $parametro->getValor();
        } else {
            $meses = 3;
        }
        $consulta = "SELECT c.ClaveCliente, c.NombreRazonSocial, c.RFC, f.IdFactura, f.Folio, CONCAT(u.Nombre,' ',u.ApellidoPaterno,' ',u.ApellidoMaterno) AS EjecutivoCuenta, u.IdUsuario, u.correo
        FROM `c_cliente` AS c
        LEFT JOIN c_factura AS f ON f.IdFactura = (SELECT MAX(IdFactura) FROM c_factura WHERE TRIM(c.RFC) = TRIM(RFCReceptor) AND (TipoComprobante = 'ingreso' AND EstadoFactura = 1 AND PendienteCancelar = 0 
        AND (ISNULL(Serie) OR Serie='') AND (ISNULL(EstatusFactura) OR EstatusFactura<>3) AND FacturaPagada = 0 
        AND FechaFacturacion <= DATE_SUB(NOW(),INTERVAL $meses MONTH)))
        INNER JOIN c_usuario AS u ON u.IdUsuario = c.EjecutivoCuenta
        WHERE c.IdEstatusCobranza = 2 AND ISNULL(f.IdFactura)
        ORDER BY u.IdUsuario, c.NombreRazonSocial;";
        $catalogo = new CatalogoFacturacion();
        if (isset($this->empresa)) {
            $catalogo->setEmpresa($this->empresa);
        }$query = $catalogo->obtenerLista($consulta);
        return $query;
    }

    public function newCliente() {
        $consulta = ("SELECT MAX(CAST(ClaveCliente AS UNSIGNED)) AS maximo FROM `c_cliente`;");
        $catalogo = new Catalogo();
        if (isset($this->empresa)) {
            $catalogo->setEmpresa($this->empresa);
        } $query2 = $catalogo->obtenerLista($consulta);
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
        $catalogo = new Catalogo();
        if (isset($this->empresa)) {
            $catalogo->setEmpresa($this->empresa);
        } $query = $catalogo->obtenerLista($consulta);
        while ($rs = mysql_fetch_array($query)) {
            $this->idDatosFacturacionEmpresa = $rs['IdDatosFacturacionEmpresa'];
            return TRUE;
        }
        return FALSE;
    }

    /**
     * Edita el estatus de cobranza del cliente especificado.
     * @param type $RFC RFC del cliete.
     * @return boolean true en caso de haber modificado el estado, false en caso contrario.
     */
    public function editCliente($RFC) {
        $consulta = ("UPDATE c_cliente SET IdEstatusCobranza = '" . $this->idEstatusCobranza . "',UsuarioUltimaModificacion = '" . $this->usuarioModificacion . "',FechaUltimaModificacion = now(),Pantalla = '" . $this->pantalla . "'
            WHERE RFC='" . $RFC . "' AND NoVolverMoroso = 0;");
        $catalogo = new Catalogo();
        if (isset($this->empresa)) {
            $catalogo->setEmpresa($this->empresa);
        } $query = $catalogo->obtenerLista($consulta);
        if ($query == 1) {
            return true;
        }
        return false;
    }

    /**
     * Editar el estatus de cobranza del cliente especificado.
     * @param type $ClaveCliente clave del cliente
     * @return boolean true en caso de haber modificado el estado, false en caso contrario.
     */
    public function editarEstatusCobranzaCliente($ClaveCliente) {
        $consulta = ("UPDATE c_cliente SET IdEstatusCobranza = '" . $this->idEstatusCobranza . "',UsuarioUltimaModificacion = '" . $this->usuarioModificacion . "',FechaUltimaModificacion = now(),Pantalla = '" . $this->pantalla . "'
            WHERE ClaveCliente='$ClaveCliente';");
        $catalogo = new Catalogo();
        if (isset($this->empresa)) {
            $catalogo->setEmpresa($this->empresa);
        } $query = $catalogo->obtenerLista($consulta);
        if ($query == 1) {
            return true;
        }
        return false;
    }

    public function ClientesNoMarcados() {
        $consulta = ("SELECT * FROM c_cliente c WHERE c.NoVolverMoroso=0 AND c.IdEstatusCobranza=1");
        $catalogo = new Catalogo();
        if (isset($this->empresa)) {
            $catalogo->setEmpresa($this->empresa);
        } $query = $catalogo->obtenerLista($consulta);
        $contador = 0;
        while ($rs = mysql_fetch_array($query)) {
            $this->clienteNoMarcados[$contador] = $rs['RFC'];
            $contador++;
        }
    }

    public function NoVolverAPonerComoMoroso($claveCliente) {
        $consulta = ("UPDATE c_cliente SET NoVolverMoroso = '" . $this->ponerMoroso . "',UsuarioUltimaModificacion = '" . $this->usuarioModificacion . "',FechaUltimaModificacion = now(),Pantalla = '" . $this->pantalla . "'
            WHERE ClaveCliente='" . $claveCliente . "';");
        $catalogo = new Catalogo();
        if (isset($this->empresa)) {
            $catalogo->setEmpresa($this->empresa);
        } $query = $catalogo->obtenerLista($consulta);
        if ($query == 1) {
            return true;
        }
        return false;
    }

    public function GetNombreClienteCambiado($idCliente) {
        $consulta = ("SELECT * FROM c_cliente c  WHERE c.ClaveCliente='" . $idCliente . "'");
        $catalogo = new Catalogo();
        if (isset($this->empresa)) {
            $catalogo->setEmpresa($this->empresa);
        } $query = $catalogo->obtenerLista($consulta);
        while ($rs = mysql_fetch_array($query)) {
            $this->nombreCliente = $rs['NombreRazonSocial'];
            return TRUE;
        }
        return FALSE;
    }

    public function EjecutivoCliente($rfc) {
        $consulta = ("SELECT c.ClaveCliente,c.NombreRazonSocial,u.IdUsuario,CONCAT(u.Nombre,' ',u.ApellidoPaterno,' ',u.ApellidoMaterno) AS ejecutivo,u.correo,c.EjecutivoCuenta FROM c_cliente c,c_usuario u WHERE c.EjecutivoCuenta=u.IdUsuario  AND c.EjecutivoCuenta<>'null' AND c.RFC='" . $rfc . "'");
        $catalogo = new Catalogo();
        if (isset($this->empresa)) {
            $catalogo->setEmpresa($this->empresa);
        } $query = $catalogo->obtenerLista($consulta);
        if ($rs = mysql_fetch_array($query)) {
            $this->nombreEjecutivo = $rs['ejecutivo'];
            return true;
        } else {
            $this->nombreEjecutivo = "Sin ejecutivo";
            return false;
        }
    }

    public function getClientesEquipos() {
        return $this->clientesEquipos;
    }

    public function setClientesEquipos($clientesEquipos) {
        $this->clientesEquipos = $clientesEquipos;
    }

    public function getClientesMorosos() {
        return $this->clientesMorosos;
    }

    public function setClientesMorosos($clientesMorosos) {
        $this->clientesMorosos = $clientesMorosos;
    }

    public function getClientesFacturacion() {
        return $this->clientesFacturacion;
    }

    public function setClientesFacturacion($clientesFacturacion) {
        $this->clientesFacturacion = $clientesFacturacion;
    }

    public function getNombreFacturacion() {
        return $this->nombreFacturacion;
    }

    public function setNombreFacturacion($nombreFacturacion) {
        $this->nombreFacturacion = $nombreFacturacion;
    }

    public function getRFCEmisor() {
        return $this->RFCEmisor;
    }

    public function setRFCEmisor($RFCEmisor) {
        $this->RFCEmisor = $RFCEmisor;
    }

    public function getNombreFacturacionMoroso() {
        return $this->nombreFacturacionMoroso;
    }

    public function setNombreFacturacionMoroso($nombreFacturacionMoroso) {
        $this->nombreFacturacionMoroso = $nombreFacturacionMoroso;
    }

    public function getRFCEmisorMoroso() {
        return $this->RFCEmisorMoroso;
    }

    public function setRFCEmisorMoroso($RFCEmisorMoroso) {
        $this->RFCEmisorMoroso = $RFCEmisorMoroso;
    }

    public function getIdEstatusCobranza() {
        return $this->idEstatusCobranza;
    }

    public function setIdEstatusCobranza($idEstatusCobranza) {
        $this->idEstatusCobranza = $idEstatusCobranza;
    }

    public function getIdDatosFacturacionEmpresa() {
        return $this->idDatosFacturacionEmpresa;
    }

    public function setIdDatosFacturacionEmpresa($idDatosFacturacionEmpresa) {
        $this->idDatosFacturacionEmpresa = $idDatosFacturacionEmpresa;
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

    public function getDDatosFacturacion() {
        return $this->dDatosFacturacion;
    }

    public function setDDatosFacturacion($dDatosFacturacion) {
        $this->dDatosFacturacion = $dDatosFacturacion;
    }

    public function getRfcDatosFacturacion() {
        return $this->rfcDatosFacturacion;
    }

    public function setRfcDatosFacturacion($rfcDatosFacturacion) {
        $this->rfcDatosFacturacion = $rfcDatosFacturacion;
    }

    public function getClienteNoMarcados() {
        return $this->clienteNoMarcados;
    }

    public function setClienteNoMarcados($clienteNoMarcados) {
        $this->clienteNoMarcados = $clienteNoMarcados;
    }

    public function getPonerMoroso() {
        return $this->ponerMoroso;
    }

    public function setPonerMoroso($ponerMoroso) {
        $this->ponerMoroso = $ponerMoroso;
    }

    public function getFolio() {
        return $this->folio;
    }

    public function setFolio($folio) {
        $this->folio = $folio;
    }

    public function getFechaFacturada() {
        return $this->fechaFacturada;
    }

    public function setFechaFacturada($fechaFacturada) {
        $this->fechaFacturada = $fechaFacturada;
    }

    public function getNombreCliente() {
        return $this->nombreCliente;
    }

    public function setNombreCliente($nombreCliente) {
        $this->nombreCliente = $nombreCliente;
    }

    public function getNombreEjecutivo() {
        return $this->nombreEjecutivo;
    }

    public function setNombreEjecutivo($nombreEjecutivo) {
        $this->nombreEjecutivo = $nombreEjecutivo;
    }

    public function getXml() {
        return $this->xml;
    }

    public function setXml($xml) {
        $this->xml = $xml;
    }

    public function getSerieMoroso() {
        return $this->serieMoroso;
    }

    public function setSerieMoroso($serieMoroso) {
        $this->serieMoroso = $serieMoroso;
    }

    public function getEmpresa() {
        return $this->empresa;
    }

    public function setEmpresa($empresa) {
        $this->empresa = $empresa;
    }

}
?>


