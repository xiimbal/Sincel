<?php

session_start();

if (!isset($_SESSION['user']) || $_SESSION['user'] == "") {
    header("Location: ../index.php");
}

ini_set("memory_limit", "1024M");
set_time_limit(0);
header('Content-Type: text/html; charset=utf-8');

include_once("../WEB-INF/Classes/Catalogo.class.php");
include_once("../WEB-INF/Classes/CatalogoFacturacion.class.php");
include_once("../WEB-INF/Classes/Parametros.class.php");
include_once("../WEB-INF/Classes/ReporteLectura.class.php");
include_once("../WEB-INF/Classes/ServicioGeneral.class.php");
include_once("../WEB-INF/Classes/Equipo.class.php");
include_once("../WEB-INF/Classes/PHP_XLSXWriter-master/xlsxwriter.class.php");
ini_set('display_errors', 0);
ini_set('log_errors', 1);
error_reporting(E_ALL & ~E_NOTICE);


if (isset($_POST['reporte1'])) {
    $filename = "ReporteProductividadCliente.xlsx";
} else if (isset($_POST['reporte2'])) {
    $filename = "ReporteProductividadModelo.xlsx";
} else {
    $filename = "ReporteProductividad.xlsx";
}

header('Content-disposition: attachment; filename="' . XLSXWriter::sanitize_filename($filename) . '"');
header("Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet");
header('Content-Transfer-Encoding: binary');
header('Cache-Control: must-revalidate');
header('Pragma: public');
$writer = new XLSXWriter();
$writer->setAuthor('Techra');

$catalogo = new Catalogo();
$catalogo_facturacion = new CatalogoFacturacion();
$parametros = new Parametros();

$fecha_inicio = "";
$fecha_final = "";
$clave_cliente = "";
$where_facturas = "";
$where_gastos = "";
$where_toner = "";
$where_tecnicos = "";
$where_envios = "";
$where_contratos = "";
$where_equipos = "";
/* Parametros para calculos */
$tipo_cambio = 15;
$costo_tecnico = 500;
$costo_paqueteria = 150;
$costo_propio = 100;

if (isset($_POST['tipo_cambio']) && !empty($_POST['tipo_cambio'])) {
    $tipo_cambio = (float) $_POST['tipo_cambio'];
}

if (isset($_POST['costo_tecnico']) && !empty($_POST['costo_tecnico'])) {
    $costo_tecnico = (float) $_POST['costo_tecnico'];
}

if (isset($_POST['costo_paqueteria']) && !empty($_POST['costo_paqueteria'])) {
    $costo_paqueteria = (float) $_POST['costo_paqueteria'];
}

if (isset($_POST['costo_propio']) && !empty($_POST['costo_propio'])) {
    $costo_propio = (float) $_POST['costo_propio'];
}

if (isset($_POST['fecha1']) && !empty($_POST['fecha1'])) {
    $where_facturas .= " AND f.FechaFacturacion >= '" . $_POST['fecha1'] . " 00:00:00'";
    $where_gastos .= " AND nt.FechaHora >= '" . $_POST['fecha1'] . " 00:00:00' ";
    $where_tecnicos .= " AND t.FechaHora >= '" . $_POST['fecha1'] . " 00:00:00' ";
    $where_envios .= " AND kem.FechaCreacion >= '" . $_POST['fecha1'] . " 00:00:00' ";
    $where_toner .= " AND ke.FechaCreacion >= '" . $_POST['fecha1'] . " 00:00:00' ";
    $where_equipos .= " AND f.FechaFacturacion >= '" . $_POST['fecha1'] . " 00:00:00' ";
}

if (isset($_POST['fecha2']) && !empty($_POST['fecha2'])) {
    $where_facturas .= " AND f.FechaFacturacion <= '" . $_POST['fecha2'] . " 23:59:59'";
    $where_gastos .= " AND nt.FechaHora <= '" . $_POST['fecha2'] . " 23:59:59' ";
    $where_tecnicos .= " AND t.FechaHora <= '" . $_POST['fecha2'] . " 23:59:59' ";
    $where_envios .= " AND kem.FechaCreacion <= '" . $_POST['fecha2'] . " 23:59:59' ";
    $where_toner .= " AND ke.FechaCreacion <= '" . $_POST['fecha2'] . " 23:59:59' ";
    $where_equipos .= " AND f.FechaFacturacion <= '" . $_POST['fecha2'] . " 23:59:59' ";
}

if (isset($_POST['rfc']) && !empty($_POST['rfc'])) {
    $where_facturas .= " AND f.RFCReceptor = '" . $_POST['rfc'] . "'";
    $where_gastos .= " AND c.RFC = '" . $_POST['rfc'] . "' ";
    $where_tecnicos .= " AND c.RFC = '" . $_POST['rfc'] . "' ";
    $where_envios .= " AND c.RFC = '" . $_POST['rfc'] . "' ";
    $where_contratos .= " AND c.RFC = '" . $_POST['rfc'] . "' ";
    $where_toner .= " AND c.RFC = '" . $_POST['rfc'] . "' ";
    $where_equipos .= " AND c.RFC = '" . $_POST['rfc'] . "' ";
}

$clientes_filtro = $_POST['cliente'];
if (is_array($clientes_filtro) && !empty($clientes_filtro)) {
    $where = " AND c.ClaveCliente IN(";
    foreach ($clientes_filtro as $value) {
        $where .= "'$value',";
    }
    if (!empty($where)) {
        $where = substr($where, 0, strlen($where) - 1);
    }
    $where .= ") ";
    $where_facturas .= $where;
    $where_gastos .= $where;
    $where_tecnicos .= $where;
    $where_envios .= $where;
    $where_contratos .= $where;
    $where_toner .= $where;
    $where_equipos .= $where;
}

$costos_refacciones = array();
$costos_toner = array();
$costos_tecnico = array();
$costos_envios = array();
$numero_equipos = array();

if (isset($_POST['reporte1'])) {
    $costos_facturas = array();
    $clientes_procesados = array();
    $clientes_nombre = array();
    $contratos_cliente = array();

    /************** Contrato de clientes   ************* */
    $consulta = "SELECT c.ClaveCliente, c.NombreRazonSocial, c.RFC, ctt.FechaInicio, ctt.FechaTermino 
        FROM c_cliente AS c
        LEFT JOIN c_contrato AS ctt ON ctt.ClaveCliente = c.ClaveCliente
        WHERE c.Activo = 1 AND c.Modalidad <> 2 $where_contratos
        GROUP BY c.ClaveCliente
        ORDER BY c.NombreRazonSocial;";
    $result = $catalogo->obtenerLista($consulta);
    while ($rs = mysql_fetch_array($result)) {
        if (!isset($contratos_cliente[$rs['RFC']])) {
            $contratos_cliente[$rs['RFC']]['Inicio'] = $rs['FechaInicio'];
            $contratos_cliente[$rs['RFC']]['Fin'] = $rs['FechaTermino'];
        }
    }

    /************** # equipos de clientes   ************* */
    $consulta = "SELECT
        (CONCAT(u.Nombre,' ',u.ApellidoPaterno,' ',u.ApellidoMaterno)) AS EjecutivoCuenta, c.RFC,
        COUNT(cinv.NoSerie) AS SUMA
        FROM `c_inventarioequipo` AS cinv
        LEFT JOIN k_serviciogimgfa AS ks ON ks.IdKserviciogimgfa = cinv.IdKserviciogimgfa
        RIGHT JOIN k_anexoclientecc AS ka ON ka.IdAnexoClienteCC = cinv.IdAnexoClienteCC
        RIGHT JOIN c_centrocosto AS cc ON cc.ClaveCentroCosto = ka.CveEspClienteCC
        LEFT JOIN c_cliente AS c ON c.ClaveCliente = cc.ClaveCliente
        LEFT JOIN c_usuario AS u ON u.IdUsuario=c.EjecutivoCuenta
        LEFT JOIN c_bitacora AS b ON b.NoSerie = cinv.NoSerie
        WHERE !ISNULL(cinv.NoSerie) AND b.VentaDirecta = 0 AND c.Modalidad <> 2 $where_contratos
        GROUP BY c.ClaveCliente
        ORDER BY SUMA DESC;";
    $result = $catalogo->obtenerLista($consulta);
    while ($rs = mysql_fetch_array($result)) {
        if (!isset($numero_equipos[$rs['RFC']])) {
            $numero_equipos[$rs['RFC']] = $rs['SUMA'];
        }
    }

    /**************    Monto de facturas   ************* */
    $consulta = "SELECT COUNT(IdFactura) AS numeroFacturas, SUM(Total) AS Total, RFCReceptor, 
        (CASE WHEN !ISNULL(c.ClaveCliente) THEN c.NombreRazonSocial ELSE f.NombreReceptor END) AS NombreCliente, c.ClaveCliente
        FROM c_factura AS f
        LEFT JOIN c_cliente AS c ON c.ClaveCliente = (SELECT MIN(ClaveCliente) FROM c_cliente WHERE RFC = f.RFCReceptor AND Activo = 1) 
        WHERE EstadoFactura = 1 AND Serie = '' AND TipoComprobante = 'ingreso'
        AND PendienteCancelar = 0
        AND (ISNULL(EstatusFactura) OR EstatusFactura <> 3)
        AND c.Modalidad <> 2 AND f.TipoArrendamiento = 1
        $where_facturas
        GROUP BY RFCReceptor
        ORDER BY Total DESC;"; //Suma de facturas

    $result = $catalogo_facturacion->obtenerLista($consulta);
    while ($rs = mysql_fetch_array($result)) {
        $clientes_nombre[$rs['RFCReceptor']] = $rs['NombreCliente'];
        if (!isset($costos_facturas[$rs['RFCReceptor']])) {
            $costos_facturas[$rs['RFCReceptor']] = (float) $rs['Total'];
        } else {
            $costos_facturas[$rs['RFCReceptor']] += (float) $rs['Total'];
        }
    }

    /**************     Costo de toner   ************* */
    $consulta = "SELECT c.ClaveCliente, c.NombreRazonSocial, c.RFC, ke.IdEnvio, ke.NoParte, ke.Cantidad,ke.ClaveCentroCosto,
        p.IdTicket,ke.IdSolicitudEquipo,ke.FechaCreacion,cmp.PrecioDolares,
        (cmp.PrecioDolares * ke.Cantidad * $tipo_cambio) AS PrecioTotalLista,
        koc.PrecioUnitario AS PrecioCompra,
        koc.Dolar,coc.TipoCambio, (CASE WHEN koc.Dolar = 0 THEN koc.PrecioUnitario * ke.Cantidad ELSE koc.PrecioUnitario * coc.TipoCambio * ke.Cantidad END) AS PrecioTotalCompra
        FROM k_enviotoner AS ke
        INNER JOIN c_centrocosto AS cc ON cc.ClaveCentroCosto = ke.ClaveCentroCosto
        INNER JOIN c_cliente AS c ON c.ClaveCliente = cc.ClaveCliente
        LEFT JOIN k_orden_compra AS koc ON koc.IdDetalleOC = (SELECT MAX(IdDetalleOC) FROM k_orden_compra WHERE NoParteComponente = ke.NoParte)
        LEFT JOIN c_orden_compra AS coc ON coc.Id_orden_compra = koc.IdOrdenCompra
        INNER JOIN c_componente AS cmp ON cmp.NoParte = ke.NoParte
        LEFT JOIN c_pedido AS p ON p.IdPedido = ke.IdSolicitud
        LEFT JOIN c_bitacora AS b ON b.NoSerie = p.ClaveEspEquipo
        LEFT JOIN k_enviotoner AS ke3 ON ke3.IdEnvio = (
        SELECT MIN(IdEnvio) FROM k_enviotoner AS ke2 
        WHERE
        (ke2.IdSolicitud = ke.IdSolicitud OR ke2.IdSolicitudEquipo = ke.IdSolicitudEquipo) AND ke.NoParte = ke2.NoParte 
        AND ke2.Cantidad = ke.Cantidad AND ke2.ClaveCentroCosto = ke.ClaveCentroCosto
        AND ( (ke2.IdMensajeria = ke.IdMensajeria AND ke2.NoGuia = ke.NoGuia) OR (ke2.IdConductor = ke.IdConductor AND ke2.IdVehiculo = ke.IdVehiculo) )
        AND DATE(ke.FechaCreacion) = DATE(ke2.FechaCreacion) AND ke2.UsuarioCreacion = ke.UsuarioCreacion)
        WHERE c.Modalidad <> 2 AND b.VentaDirecta = 0 AND cmp.IdTipoComponente = 2 AND ke.IdEnvio = ke3.IdEnvio $where_toner
        ORDER BY c.NombreRazonSocial,ke.Cantidad DESC;"; //Suma de toner
    $result = $catalogo->obtenerLista($consulta);
    while ($rs = mysql_fetch_array($result)) {
        if (!isset($costos_toner[$rs['RFC']])) {
            if (isset($rs['PrecioTotalCompra']) && !empty($rs['PrecioTotalCompra'])) {
                $costos_toner[$rs['RFC']] = ((float) $rs['PrecioTotalCompra'] );
            } else {
                $costos_toner[$rs['RFC']] = ((float) $rs['PrecioTotalLista'] );
            }
        } else {
            if (isset($rs['PrecioTotalCompra']) && !empty($rs['PrecioTotalCompra'])) {
                $costos_toner[$rs['RFC']] += ((float) $rs['PrecioTotalCompra'] );
            } else {
                $costos_toner[$rs['RFC']] += ((float) $rs['PrecioTotalLista'] );
            }
        }
    }
    /**************     Costo de refacciones   ************* */
    $consulta = "SELECT c.ClaveCliente, c.NombreRazonSocial, c.RFC,  nr.cantidad, 
        ((cmp.PrecioDolares) * $tipo_cambio * nr.Cantidad) AS PrecioTotalLista,
        (CASE WHEN koc.Dolar = 1 THEN (koc.PrecioUnitario * coc.TipoCambio * nr.Cantidad) ELSE (koc.PrecioUnitario * nr.Cantidad) END)  AS PrecioTotalCompra,
        koc.Dolar
        FROM c_ticket AS t
        INNER JOIN c_notaticket AS nt ON nt.IdTicket = t.IdTicket
        INNER JOIN k_nota_refaccion AS nr ON nr.IdNotaTicket = nt.IdNotaTicket
        INNER JOIN c_cliente AS c ON c.ClaveCliente = t.ClaveCliente
        LEFT JOIN k_orden_compra AS koc ON koc.IdDetalleOC = (SELECT MAX(IdDetalleOC) FROM k_orden_compra WHERE NoParteComponente = nr.NoParteComponente)
        LEFT JOIN c_orden_compra AS coc ON coc.Id_orden_compra = koc.IdOrdenCompra
        INNER JOIN c_componente AS cmp ON cmp.NoParte = nr.NoParteComponente
        LEFT JOIN c_bitacora AS b ON b.NoSerie = t.NoSerieEquipo
        WHERE nt.IdEstatusAtencion = 17 AND b.VentaDirecta = 0 AND c.Modalidad <> 2 $where_gastos
        UNION
        SELECT c.ClaveCliente, c.NombreRazonSocial, c.RFC,ke.Cantidad,
        (cmp.PrecioDolares * ke.Cantidad * $tipo_cambio) AS PrecioTotalLista,
        (CASE WHEN koc.Dolar = 0 THEN koc.PrecioUnitario * ke.Cantidad ELSE koc.PrecioUnitario * coc.TipoCambio * ke.Cantidad END) AS PrecioTotalCompra,
        koc.Dolar
        FROM k_enviotoner AS ke
        INNER JOIN c_centrocosto AS cc ON cc.ClaveCentroCosto = ke.ClaveCentroCosto
        INNER JOIN c_cliente AS c ON c.ClaveCliente = cc.ClaveCliente
        LEFT JOIN k_orden_compra AS koc ON koc.IdDetalleOC = (SELECT MAX(IdDetalleOC) FROM k_orden_compra WHERE NoParteComponente = ke.NoParte)
        LEFT JOIN c_orden_compra AS coc ON coc.Id_orden_compra = koc.IdOrdenCompra
        INNER JOIN c_componente AS cmp ON cmp.NoParte = ke.NoParte
        LEFT JOIN c_pedido AS p ON p.IdPedido = ke.IdSolicitud
        LEFT JOIN c_bitacora AS b ON b.NoSerie = p.ClaveEspEquipo
        LEFT JOIN k_enviotoner AS ke3 ON ke3.IdEnvio = (
        SELECT MIN(IdEnvio) FROM k_enviotoner AS ke2 
        WHERE 
        (ke2.IdSolicitud = ke.IdSolicitud OR ke2.IdSolicitudEquipo = ke.IdSolicitudEquipo) AND ke.NoParte = ke2.NoParte 
        AND ke2.Cantidad = ke.Cantidad AND ke2.ClaveCentroCosto = ke.ClaveCentroCosto
        AND ( (ke2.IdMensajeria = ke.IdMensajeria AND ke2.NoGuia = ke.NoGuia) OR (ke2.IdConductor = ke.IdConductor AND ke2.IdVehiculo = ke.IdVehiculo) )
        AND DATE(ke.FechaCreacion) = DATE(ke2.FechaCreacion) AND ke2.UsuarioCreacion = ke.UsuarioCreacion)
        WHERE cmp.IdTipoComponente = 1 AND b.VentaDirecta = 0 AND c.Modalidad <> 2 AND ke.IdEnvio = ke3.IdEnvio $where_toner
        ORDER BY NombreRazonSocial,Cantidad;"; //Suma de refacciones
    $result = $catalogo->obtenerLista($consulta);
    while ($rs = mysql_fetch_array($result)) {
        if (!isset($costos_refacciones[$rs['RFC']])) {
            if (isset($rs['PrecioTotalCompra']) && !empty($rs['PrecioTotalCompra'])) {
                $costos_refacciones[$rs['RFC']] = ((float) $rs['PrecioTotalCompra'] );
            } else {
                $costos_refacciones[$rs['RFC']] = ((float) $rs['PrecioTotalLista'] );
            }
        } else {
            if (isset($rs['PrecioTotalCompra']) && !empty($rs['PrecioTotalCompra'])) {
                $costos_refacciones[$rs['RFC']] += ((float) $rs['PrecioTotalCompra'] );
            } else {
                $costos_refacciones[$rs['RFC']] += ((float) $rs['PrecioTotalLista'] );
            }
        }
    }
    /**************     Costo de tecnicos   ************* */
    $consulta = "SELECT COUNT(t.IdTicket) AS numero_tecnicos, c.ClaveCliente, c.RFC, c.NombreRazonSocial
        FROM `c_ticket` AS t
        LEFT JOIN k_tecnicoticket AS kt ON kt.IdTicket = t.IdTicket
        LEFT JOIN c_cliente AS c ON c.ClaveCliente = t.ClaveCliente
        LEFT JOIN c_bitacora AS b ON b.NoSerie = t.NoSerieEquipo
        WHERE t.Activo = 1 AND b.VentaDirecta = 0 AND c.Modalidad <> 2 $where_tecnicos
        GROUP BY t.ClaveCliente
        ORDER BY numero_tecnicos DESC;";
    $result = $catalogo->obtenerLista($consulta);
    while ($rs = mysql_fetch_array($result)) {
        if (!isset($costos_tecnico[$rs['RFC']])) {
            $costos_tecnico[$rs['RFC']] = ((int) $rs['numero_tecnicos'] * (float) $costo_tecnico);
        } else {
            $costos_tecnico[$rs['RFC']] += ((int) $rs['numero_tecnicos'] * (float) $costo_tecnico);
        }
    }
    /**************     Costo de envios   ************* */
    $consulta = "SELECT COUNT(IdEnvio) AS numero_envios, c.ClaveCliente, c.NombreRazonSocial, c.RFC,
        (CASE WHEN !ISNULL(IdMensajeria) THEN 1 ELSE 2 END) AS TipoEnvio
        FROM `k_enviosmensajeria` AS kem
        LEFT JOIN c_centrocosto AS cc ON cc.ClaveCentroCosto = kem.ClaveCentroCosto
        LEFT JOIN c_cliente AS c ON c.ClaveCliente = cc.ClaveCliente
        LEFT JOIN c_bitacora AS b ON b.NoSerie = kem.NoSerie
        WHERE !ISNULL(c.ClaveCliente) AND b.VentaDirecta = 0 AND c.Modalidad <> 2 $where_envios
        GROUP BY c.ClaveCliente, TipoEnvio
        UNION
        SELECT COUNT(IdEnvio) AS numero_envios, c.ClaveCliente, c.NombreRazonSocial, c.RFC,
        (CASE WHEN !ISNULL(IdMensajeria) THEN 1 ELSE 2 END) AS TipoEnvio
        FROM k_enviotoner AS kem
        LEFT JOIN c_centrocosto AS cc ON cc.ClaveCentroCosto = kem.ClaveCentroCosto
        LEFT JOIN c_cliente AS c ON c.ClaveCliente = cc.ClaveCliente
        LEFT JOIN c_pedido AS p ON p.IdPedido = kem.IdSolicitud
        LEFT JOIN c_bitacora AS b ON b.NoSerie = p.ClaveEspEquipo
        LEFT JOIN c_solicitud AS cs ON cs.id_solicitud = kem.IdSolicitudEquipo
        WHERE !ISNULL(c.ClaveCliente) AND c.Modalidad <> 2
        AND (b.VentaDirecta = 0 OR cs.id_tiposolicitud <> 6)
        $where_envios
        GROUP BY c.ClaveCliente, TipoEnvio
        ORDER BY numero_envios DESC;";
    $result = $catalogo->obtenerLista($consulta);
    while ($rs = mysql_fetch_array($result)) {
        if (!isset($costos_envios[$rs['RFC']])) {
            if ($rs['TipoEnvio'] == "1") {
                $costos_envios[$rs['RFC']] = ((int) $rs['numero_envios'] * (float) $costo_paqueteria);
            } else {
                $costos_envios[$rs['RFC']] = ((int) $rs['numero_envios'] * (float) $costo_propio);
            }
        } else {
            if ($rs['TipoEnvio'] == "1") {
                $costos_envios[$rs['RFC']] += ((int) $rs['numero_envios'] * (float) $costo_paqueteria);
            } else {
                $costos_envios[$rs['RFC']] += ((int) $rs['numero_envios'] * (float) $costo_propio);
            }
        }
    }

    /* Ponemos las cabeceras */
    $cabeceras = array("RFC" => "string", "Cliente" => "string", "Número equipos" => "number", "Inicio contrato" => "date", "Fin contrato" => "date", "Monto Factura" => "money", "Costo refacciones" => "money",
        "Costo tóner" => "money", "Costo técnico" => "money", "Costo envíos" => "money", "Total" => "money");

    $hoja = "Reporte";
    $writer->writeSheetHeader($hoja, $cabeceras);

    foreach ($costos_facturas as $key => $value) {
        $total = (float) $value - ((float) $costos_refacciones[$key] + (float) $costos_toner[$key] + (float) $costos_tecnico[$key] + (float) $costos_envios[$key]);
        $array_valores = array();
        array_push($array_valores, $key);
        array_push($array_valores, $clientes_nombre[$key]);
        array_push($array_valores, $numero_equipos[$key]);
        array_push($array_valores, $contratos_cliente[$key]['Inicio']);
        array_push($array_valores, $contratos_cliente[$key]['Fin']);
        array_push($array_valores, $value);
        array_push($array_valores, $costos_refacciones[$key]);
        array_push($array_valores, $costos_toner[$key]);
        array_push($array_valores, $costos_tecnico[$key]);
        array_push($array_valores, $costos_envios[$key]);
        array_push($array_valores, $total);
        $writer->writeSheetRow($hoja, $array_valores);
        array_push($clientes_procesados, $key);
    }

    $result = $catalogo->getListaAlta("c_cliente", "RFC");
    while ($rs = mysql_fetch_array($result)) {
        if (!in_array($rs['RFC'], $clientes_procesados)/* &&
          (isset($costos_refacciones[$rs['RFC']]) || isset($costos_toner[$rs['RFC']]) || isset($costos_tecnico[$rs['RFC']]) || isset($costos_envios[$rs['RFC']])) */) {
            $key = $rs['RFC'];
            $value = $costos_facturas[$key];
            $total = (float) $value - ((float) $costos_refacciones[$key] + (float) $costos_toner[$key] + (float) $costos_tecnico[$key] + (float) $costos_envios[$key]);
            $array_valores = array();
            array_push($array_valores, $key);
            array_push($array_valores, $rs['NombreRazonSocial']);
            array_push($array_valores, $numero_equipos[$key]);
            array_push($array_valores, $contratos_cliente[$key]['Inicio']);
            array_push($array_valores, $contratos_cliente[$key]['Fin']);
            array_push($array_valores, $value);
            array_push($array_valores, $costos_refacciones[$key]);
            array_push($array_valores, $costos_toner[$key]);
            array_push($array_valores, $costos_tecnico[$key]);
            array_push($array_valores, $costos_envios[$key]);
            array_push($array_valores, $total);
            $writer->writeSheetRow($hoja, $array_valores);
        }
    }
} else if (isset($_POST['reporte2'])) {
    $reporte_lectura = new ReporteLectura();
    $modelos_procesados = array();
    $modelos_nombre = array();
    $facturado_modelo = array();
    /*     * *********** facturado por modelo ************ */
    $renta_global = 0;
    $paginas_global_bn = 0;
    $paginas_global_color = 0;
    $incluidosBN = 0;
    $incluidosColor = 0;
    $costoExcedentesBN = 0;
    $costoExcedentesColor = 0;
    $costoProcesadosBN = 0;
    $costoProcesadosColor = 0;
    $equipos_servicio = array();
    $idKServicio = "";
    $idServicio = "";
    $isGlobal = false;
    $consulta = "SELECT b.NoSerie, e.NoParte, f.IdFactura, 
        (CASE WHEN !ISNULL(kim.IdKServicioIM) THEN 1 WHEN !ISNULL(kgim.IdKServicioGIM) THEN 0 WHEN !ISNULL(kfa.IdKServicioFA) THEN 1 WHEN !ISNULL(kgfa.IdKServicioGFA) THEN 0 ELSE 0 END) AS IsParticular,

        (CASE WHEN !ISNULL(kgim.IdKServicioGIM) OR !ISNULL(kgfa.IdKServicioGFA) THEN
        (SELECT COUNT(fd2.IdFacturaDetalle) FROM c_facturadetalle AS fd2 
        INNER JOIN c_factura AS f2 ON f2.IdFactura = fd2.IdFactura
        INNER JOIN c_folio_prefactura AS fp2 ON fp2.Folio = f2.Folio AND fp2.IdEmisor = f2.RFCEmisor
        WHERE fd2.IdKServicio = fd.IdKServicio AND fd2.IdServicio = fd.IdServicio 
        AND DATE(f2.FechaFacturacion) = DATE(f.FechaFacturacion) AND f.RFCReceptor = f2.RFCReceptor
        AND !ISNULL(fp2.FolioTimbrado) AND (f2.TipoArrendamiento = 1 OR ISNULL(f2.TipoArrendamiento)))
        ELSE 1 END) AS NumeroEquipos,

        f.RFCReceptor,f.RFCEmisor, DATE(f.FechaFacturacion) AS FechaFacturacion, DATE(l.Fecha) AS FechaLectura, fp.FolioTimbrado, 
        (CASE WHEN !ISNULL(kecs.Id) THEN 1 ELSE 0 END) AS isColor,
        (CASE WHEN !ISNULL(kecs2.Id) THEN 1 ELSE 0 END) AS isFA,
        fd.IdKServicio,fd.IdServicio, (SELECT COUNT(IdBitacora) FROM c_facturadetalle WHERE IdFactura = f.IdFactura) AS NumEquiposFactura,
        fd.RentaMensual, fd.IncluidosBN, fd.IncluidosColor,
        fd.CostoExcedentesBN, fd.CostoExcedentesColor, fd.CostoProcesadosBN, fd.CostoProcesadosColor,
        (CASE WHEN !ISNULL(kecs2.Id) THEN l.ContadorBNML ELSE l.ContadorBNPaginas END) AS ContadorBN,
        (CASE WHEN ISNULL(kecs.Id) THEN 0 WHEN !ISNULL(kecs2.Id) THEN l.ContadorColorML ELSE l.ContadorColorPaginas END) AS ContadorColor
        FROM `c_facturadetalle` AS fd
        LEFT JOIN c_factura AS f ON f.IdFactura = fd.IdFactura
        LEFT JOIN c_folio_prefactura AS fp ON fp.Folio = f.Folio AND fp.IdEmisor = f.RFCEmisor
        LEFT JOIN c_cliente AS c ON c.ClaveCliente = f.RFCReceptor
        LEFT JOIN c_bitacora AS b ON b.id_bitacora = fd.IdBitacora
        LEFT JOIN c_lectura AS l ON l.IdLectura = (SELECT MAX(IdLectura) FROM c_lectura WHERE b.NoSerie = NoSerie AND LecturaCorte = 1 AND YEAR(Fecha) = YEAR(f.FechaFacturacion) AND MONTH(Fecha) = MONTH(f.FechaFacturacion))
        LEFT JOIN k_servicioim AS kim ON kim.IdKServicioIM = fd.IdKServicio AND kim.IdServicioIM = fd.IdServicio
        LEFT JOIN k_serviciogim AS kgim ON kgim.IdKServicioGIM = fd.IdKServicio AND kgim.IdServicioGIM = fd.IdServicio
        LEFT JOIN k_serviciofa AS kfa ON kfa.IdKServicioFA = fd.IdKServicio AND kfa.IdServicioFA = fd.IdKServicio
        LEFT JOIN k_serviciogfa AS kgfa ON kgfa.IdKServicioGFA = fd.IdKServicio AND kgfa.IdServicioGFA = fd.IdServicio
        LEFT JOIN c_equipo AS e ON e.NoParte = b.NoParte
        LEFT JOIN k_equipocaracteristicaformatoservicio AS kecs ON kecs.Id = (SELECT MAX(ID) FROM k_equipocaracteristicaformatoservicio WHERE NoParte = e.NoParte AND IdTipoServicio = 1)
        LEFT JOIN k_equipocaracteristicaformatoservicio AS kecs2 ON kecs2.Id = (SELECT MAX(ID) FROM k_equipocaracteristicaformatoservicio WHERE NoParte = e.NoParte AND IdCaracteristicaEquipo = 2)
        WHERE !ISNULL(fp.FolioTimbrado) AND f.TipoArrendamiento = 1 AND c.Modalidad <> 2 AND b.VentaDirecta = 0 $where_equipos
        ORDER BY fd.IdServicio, fd.IdKServicio, FechaFacturacion;";
    $result = $catalogo->obtenerLista($consulta);

    while ($rs = mysql_fetch_array($result)) {
        if ($isGlobal && $idKServicio != $rs['IdKServicio'] && $idServicio != $rs['IdServicio']) {
            //Dependiendo del servicio, es lo que se va a cobrar
            $servicio_general = new ServicioGeneral();
            if ($servicio_general->getCobranzasByTipoServicio($idServicio)) {
                $cobrarRenta = $servicio_general->getCobrarRenta();
                $cobrarExcedenteBN = $servicio_general->getCobrarExcedenteBN();
                $cobrarExcedenteColor = $servicio_general->getCobrarExcedenteColor();
                $cobrarProcesadasBN = $servicio_general->getCobrarProcesadasBN();
                $cobrarProcesadasColor = $servicio_general->getCobrarProcesadasColor();
            } else {
                $cobrarRenta = false;
                $cobrarExcedenteBN = false;
                $cobrarExcedenteColor = false;
                $cobrarProcesadasBN = false;
                $cobrarProcesadasColor = false;
            }
            $costo = $reporte_lectura->calcularCostoServicio($renta_global, $paginas_global_bn, $paginas_global_color, $incluidosBN, $incluidosColor, $costoExcedentesBN, $costoExcedentesColor, $costoProcesadosBN, $costoProcesadosColor, $cobrarRenta, $cobrarExcedenteBN, $cobrarExcedenteColor, $cobrarProcesadasBN, $cobrarProcesadasColor);
            foreach ($equipos_servicio as $key => $value) {
                if (isset($facturado_modelo[$value])) {
                    $facturado_modelo[$value] += ($costo / count($equipos_servicio));
                } else {
                    $facturado_modelo[$value] = ($costo / count($equipos_servicio));
                    $equipo = new Equipo();
                    if ($equipo->getRegistroById($value)) {
                        $modelos_nombre[$value] = $equipo->getModelo();
                    }
                }
            }

            $renta_global = 0;
            $paginas_global_bn = 0;
            $paginas_global_color = 0;
            $incluidosBN = 0;
            $incluidosColor = 0;
            $costoExcedentesBN = 0;
            $costoExcedentesColor = 0;
            $costoProcesadosBN = 0;
            $costoProcesadosColor = 0;
            $equipos_servicio = array();
        }

        if ($rs['IsParticular'] == "1") {//Si es un servicio particular lo cobrado se suma por cada equipo
            //Dependiendo del servicio, es lo que se va a cobrar
            $servicio_general = new ServicioGeneral();
            if ($servicio_general->getCobranzasByTipoServicio($rs['IdServicio'])) {
                $cobrarRenta = $servicio_general->getCobrarRenta();
                $cobrarExcedenteBN = $servicio_general->getCobrarExcedenteBN();
                $cobrarExcedenteColor = $servicio_general->getCobrarExcedenteColor();
                $cobrarProcesadasBN = $servicio_general->getCobrarProcesadasBN();
                $cobrarProcesadasColor = $servicio_general->getCobrarProcesadasColor();
            } else {
                $cobrarRenta = false;
                $cobrarExcedenteBN = false;
                $cobrarExcedenteColor = false;
                $cobrarProcesadasBN = false;
                $cobrarProcesadasColor = false;
            }
            $costo = $reporte_lectura->calcularCostoServicio((float) $rs['RentaMensual'], (int) $rs['ContadorBN'], (int) $rs['ContadorColor'], (int) $rs['IncluidosBN'], (int) $rs['IncluidosColor'], (float) $rs['CostoExcedentesBN'], (float) $rs['CostoExcedentesColor'], (float) $rs['CostoProcesadosBN'], (float) $rs['CostoProcesadosColor'], $cobrarRenta, $cobrarExcedenteBN, $cobrarExcedenteColor, $cobrarProcesadasBN, $cobrarProcesadasColor);
            if (isset($facturado_modelo[$rs['NoParte']])) {
                $facturado_modelo[$rs['NoParte']] += $costo;
            } else {
                $facturado_modelo[$rs['NoParte']] = $costo;
                $equipo = new Equipo();
                if ($equipo->getRegistroById($rs['NoParte'])) {
                    $modelos_nombre[$rs['NoParte']] = $equipo->getModelo();
                }
            }
        } else {//Si es un servicio global, vamos acumulando las cantidades              
            $paginas_global_bn += (int) $rs['ContadorBN'];
            $paginas_global_color = (int) $rs['ContadorColor'];
            $renta_global = $rs['RentaMensual'];
            $incluidosBN = (int) $rs['IncluidosBN'];
            $incluidosColor = (int) $rs['IncluidosColor'];
            $costoExcedentesBN = (float) $rs['CostoExcedentesBN'];
            $costoExcedentesColor = (float) $rs['CostoExcedentesColor'];
            $costoProcesadosBN = (float) $rs['CostoProcesadosBN'];
            $costoProcesadosColor = (float) $rs['CostoProcesadosColor'];
            $equipos_servicio[$rs['NoSerie']] = $rs['NoParte'];
        }

        $idKServicio = $rs['IdKServicio'];
        $idServicio = $rs['IdServicio'];
        if ($rs['IsParticular'] == "0") {
            $isGlobal = true;
        } else {
            $isGlobal = false;
        }
    }

    if ($isGlobal && $idKServicio != $rs['IdKServicio'] && $idServicio != $rs['IdServicio']) {
        //Dependiendo del servicio, es lo que se va a cobrar
        $servicio_general = new ServicioGeneral();
        if ($servicio_general->getCobranzasByTipoServicio($idServicio)) {
            $cobrarRenta = $servicio_general->getCobrarRenta();
            $cobrarExcedenteBN = $servicio_general->getCobrarExcedenteBN();
            $cobrarExcedenteColor = $servicio_general->getCobrarExcedenteColor();
            $cobrarProcesadasBN = $servicio_general->getCobrarProcesadasBN();
            $cobrarProcesadasColor = $servicio_general->getCobrarProcesadasColor();
        } else {
            $cobrarRenta = false;
            $cobrarExcedenteBN = false;
            $cobrarExcedenteColor = false;
            $cobrarProcesadasBN = false;
            $cobrarProcesadasColor = false;
        }
        $costo = $reporte_lectura->calcularCostoServicio($renta_global, $paginas_global_bn, $paginas_global_color, $incluidosBN, $incluidosColor, $costoExcedentesBN, $costoExcedentesColor, $costoProcesadosBN, $costoProcesadosColor, $cobrarRenta, $cobrarExcedenteBN, $cobrarExcedenteColor, $cobrarProcesadasBN, $cobrarProcesadasColor);
        foreach ($equipos_servicio as $key => $value) {
            if (isset($facturado_modelo[$value])) {
                $facturado_modelo[$value] += ($costo / count($equipos_servicio));
            } else {
                $facturado_modelo[$value] = ($costo / count($equipos_servicio));
                $equipo = new Equipo();
                if ($equipo->getRegistroById($value)) {
                    $modelos_nombre[$value] = $equipo->getModelo();
                }
            }
        }
    }

    /*     * ************     Costo de toner   ************* */
    $consulta = "SELECT c.ClaveCliente, c.NombreRazonSocial, c.RFC, ke.IdEnvio, ke.NoParte, ke.Cantidad,ke.ClaveCentroCosto,
        p.IdTicket,ke.IdSolicitudEquipo,
        (CASE WHEN !ISNULL(e.NoParte) THEN e.NoParte WHEN !ISNULL(e2.NoParte) THEN e2.NoParte WHEN !ISNULL(e3.NoParte) THEN e3.NoParte ELSE NULL END) AS NoParteEquipo,
        (CASE WHEN !ISNULL(e.NoParte) THEN e.Modelo WHEN !ISNULL(e2.NoParte) THEN e2.Modelo WHEN !ISNULL(e3.NoParte) THEN e3.Modelo ELSE NULL END) AS ModeloEquipo,
        ke.FechaCreacion,cmp.PrecioDolares,
        (cmp.PrecioDolares * ke.Cantidad * $tipo_cambio) AS PrecioTotalLista,
        koc.PrecioUnitario AS PrecioCompra,
        koc.Dolar,coc.TipoCambio, (CASE WHEN koc.Dolar = 0 THEN koc.PrecioUnitario * ke.Cantidad ELSE koc.PrecioUnitario * coc.TipoCambio * ke.Cantidad END) AS PrecioTotalCompra
        FROM k_enviotoner AS ke
        INNER JOIN c_centrocosto AS cc ON cc.ClaveCentroCosto = ke.ClaveCentroCosto
        INNER JOIN c_cliente AS c ON c.ClaveCliente = cc.ClaveCliente
        LEFT JOIN k_orden_compra AS koc ON koc.IdDetalleOC = (SELECT MAX(IdDetalleOC) FROM k_orden_compra WHERE NoParteComponente = ke.NoParte)
        LEFT JOIN c_orden_compra AS coc ON coc.Id_orden_compra = koc.IdOrdenCompra
        INNER JOIN c_componente AS cmp ON cmp.NoParte = ke.NoParte
        LEFT JOIN c_pedido AS p ON p.IdPedido = ke.IdSolicitud
        LEFT JOIN c_bitacora AS b ON b.NoSerie = p.ClaveEspEquipo
        LEFT JOIN c_equipo AS e ON e.NoParte = b.NoParte
        LEFT JOIN k_solicitud AS ks ON ks.id_solicitud = ke.IdSolicitudEquipo AND ks.id_partida = (SELECT MIN(id_partida) FROM k_solicitud WHERE id_solicitud = ke.IdSolicitudEquipo AND tipo = 1 AND Modelo = ke.NoParte AND ke.ClaveCentroCosto = ClaveCentroCosto)
        LEFT JOIN c_bitacora AS b2 ON b2.NoSerie = ks.NoSerie
        LEFT JOIN c_equipo AS e2 ON e2.NoParte = b2.NoParte
        LEFT JOIN c_equipo AS e3 ON e3.NoParte = (
        SELECT kecc.NoParteEquipo 
        FROM k_solicitud AS ks 
        LEFT JOIN k_equipocomponentecompatible AS kecc ON kecc.NoParteEquipo = ks.Modelo
        WHERE ks.id_solicitud = ke.IdSolicitudEquipo AND ks.tipo = 0 AND kecc.NoParteComponente = ke.NoParte
        LIMIT 0,1
        )
        LEFT JOIN k_enviotoner AS ke3 ON ke3.IdEnvio = (
        SELECT MIN(IdEnvio) FROM k_enviotoner AS ke2 
        WHERE 
        (ke2.IdSolicitud = ke.IdSolicitud OR ke2.IdSolicitudEquipo = ke.IdSolicitudEquipo) AND ke.NoParte = ke2.NoParte 
        AND ke2.Cantidad = ke.Cantidad AND ke2.ClaveCentroCosto = ke.ClaveCentroCosto
        AND ( (ke2.IdMensajeria = ke.IdMensajeria AND ke2.NoGuia = ke.NoGuia) OR (ke2.IdConductor = ke.IdConductor AND ke2.IdVehiculo = ke.IdVehiculo) )
        AND DATE(ke.FechaCreacion) = DATE(ke2.FechaCreacion) AND ke2.UsuarioCreacion = ke.UsuarioCreacion)
        WHERE cmp.IdTipoComponente = 2 AND b.VentaDirecta = 0 AND c.Modalidad <> 2 AND ke.IdEnvio = ke3.IdEnvio $where_toner
        ORDER BY c.NombreRazonSocial,ke.Cantidad DESC;"; //Suma de toner
    $result = $catalogo->obtenerLista($consulta);

    while ($rs = mysql_fetch_array($result)) {
        if (!isset($rs['NoParteEquipo']) || empty($rs['NoParteEquipo'])) {
            continue;
        }
        if (!isset($costos_toner[$rs['NoParteEquipo']])) {
            if (isset($rs['PrecioTotalCompra']) && !empty($rs['PrecioTotalCompra'])) {
                $costos_toner[$rs['NoParteEquipo']] = ((float) $rs['PrecioTotalCompra'] );
            } else {
                $costos_toner[$rs['NoParteEquipo']] = ((float) $rs['PrecioTotalLista'] );
            }
        } else {
            if (isset($rs['PrecioTotalCompra']) && !empty($rs['PrecioTotalCompra'])) {
                $costos_toner[$rs['NoParteEquipo']] += ((float) $rs['PrecioTotalCompra'] );
            } else {
                $costos_toner[$rs['NoParteEquipo']] += ((float) $rs['PrecioTotalLista'] );
            }
        }
    }
    /*     * ************     Costo de refacciones   ************* */
    $consulta = "SELECT c.ClaveCliente, c.NombreRazonSocial, c.RFC,  nr.cantidad, 
        b.NoSerie, e.NoParte AS NoParteEquipo, e.Modelo AS ModeloEquipo,
        ((cmp.PrecioDolares) * $tipo_cambio * nr.Cantidad) AS PrecioTotalLista,
        (CASE WHEN koc.Dolar = 1 THEN (koc.PrecioUnitario * coc.TipoCambio * nr.Cantidad) ELSE (koc.PrecioUnitario * nr.Cantidad) END)  AS PrecioTotalCompra,
        koc.Dolar
        FROM c_ticket AS t
        INNER JOIN c_notaticket AS nt ON nt.IdTicket = t.IdTicket
        INNER JOIN k_nota_refaccion AS nr ON nr.IdNotaTicket = nt.IdNotaTicket
        INNER JOIN c_cliente AS c ON c.ClaveCliente = t.ClaveCliente
        LEFT JOIN k_orden_compra AS koc ON koc.IdDetalleOC = (SELECT MAX(IdDetalleOC) FROM k_orden_compra WHERE NoParteComponente = nr.NoParteComponente)
        LEFT JOIN c_orden_compra AS coc ON coc.Id_orden_compra = koc.IdOrdenCompra
        INNER JOIN c_componente AS cmp ON cmp.NoParte = nr.NoParteComponente
        LEFT JOIN c_bitacora AS b ON b.NoSerie = t.NoSerieEquipo
        LEFT JOIN c_equipo AS e ON b.NoParte = e.NoParte
        WHERE nt.IdEstatusAtencion = 17 AND b.VentaDirecta = 0 AND c.Modalidad <> 2 $where_gastos
        UNION
        SELECT c.ClaveCliente, c.NombreRazonSocial, c.RFC, ke.Cantidad,
        b.NoSerie,
        (CASE WHEN !ISNULL(e.NoParte) THEN e.NoParte WHEN !ISNULL(e2.NoParte) THEN e2.NoParte WHEN !ISNULL(e3.NoParte) THEN e3.NoParte ELSE NULL END) AS NoParteEquipo,
        (CASE WHEN !ISNULL(e.NoParte) THEN e.Modelo WHEN !ISNULL(e2.NoParte) THEN e2.Modelo WHEN !ISNULL(e3.NoParte) THEN e3.Modelo ELSE NULL END) AS ModeloEquipo,
        (cmp.PrecioDolares * ke.Cantidad * $tipo_cambio) AS PrecioTotalLista,
        (CASE WHEN koc.Dolar = 0 THEN koc.PrecioUnitario * ke.Cantidad ELSE koc.PrecioUnitario * coc.TipoCambio * ke.Cantidad END) AS PrecioTotalCompra,
        koc.Dolar
        FROM k_enviotoner AS ke
        INNER JOIN c_centrocosto AS cc ON cc.ClaveCentroCosto = ke.ClaveCentroCosto
        INNER JOIN c_cliente AS c ON c.ClaveCliente = cc.ClaveCliente
        LEFT JOIN k_orden_compra AS koc ON koc.IdDetalleOC = (SELECT MAX(IdDetalleOC) FROM k_orden_compra WHERE NoParteComponente = ke.NoParte)
        LEFT JOIN c_orden_compra AS coc ON coc.Id_orden_compra = koc.IdOrdenCompra
        INNER JOIN c_componente AS cmp ON cmp.NoParte = ke.NoParte
        LEFT JOIN c_pedido AS p ON p.IdPedido = ke.IdSolicitud
        LEFT JOIN c_bitacora AS b ON b.NoSerie = p.ClaveEspEquipo
        LEFT JOIN c_equipo AS e ON e.NoParte = b.NoParte
        LEFT JOIN k_solicitud AS ks ON ks.id_solicitud = ke.IdSolicitudEquipo AND ks.id_partida = (SELECT MIN(id_partida) FROM k_solicitud WHERE id_solicitud = ke.IdSolicitudEquipo AND tipo = 1 AND Modelo = ke.NoParte AND ke.ClaveCentroCosto = ClaveCentroCosto)
        LEFT JOIN c_bitacora AS b2 ON b2.NoSerie = ks.NoSerie
        LEFT JOIN c_equipo AS e2 ON e2.NoParte = b2.NoParte
        LEFT JOIN c_equipo AS e3 ON e3.NoParte = (
        SELECT kecc.NoParteEquipo 
        FROM k_solicitud AS ks 
        LEFT JOIN k_equipocomponentecompatible AS kecc ON kecc.NoParteEquipo = ks.Modelo
        WHERE ks.id_solicitud = ke.IdSolicitudEquipo AND ks.tipo = 0 AND kecc.NoParteComponente = ke.NoParte
        LIMIT 0,1
        )
        LEFT JOIN k_enviotoner AS ke3 ON ke3.IdEnvio = (
        SELECT MIN(IdEnvio) FROM k_enviotoner AS ke2 
        WHERE 
        (ke2.IdSolicitud = ke.IdSolicitud OR ke2.IdSolicitudEquipo = ke.IdSolicitudEquipo) AND ke.NoParte = ke2.NoParte 
        AND ke2.Cantidad = ke.Cantidad AND ke2.ClaveCentroCosto = ke.ClaveCentroCosto
        AND ( (ke2.IdMensajeria = ke.IdMensajeria AND ke2.NoGuia = ke.NoGuia) OR (ke2.IdConductor = ke.IdConductor AND ke2.IdVehiculo = ke.IdVehiculo) )
        AND DATE(ke.FechaCreacion) = DATE(ke2.FechaCreacion) AND ke2.UsuarioCreacion = ke.UsuarioCreacion)
        WHERE cmp.IdTipoComponente = 1 AND b.VentaDirecta = 0 AND c.Modalidad <> 2 AND ke.IdEnvio = ke3.IdEnvio $where_toner
        ORDER BY NoParteEquipo,Cantidad DESC;"; //Suma de refacciones
    $result = $catalogo->obtenerLista($consulta);

    while ($rs = mysql_fetch_array($result)) {
        if (!isset($rs['NoParteEquipo']) || empty($rs['NoParteEquipo'])) {
            continue;
        }
        if (!isset($costos_refacciones[$rs['NoParteEquipo']])) {
            if (isset($rs['PrecioTotalCompra']) && !empty($rs['PrecioTotalCompra'])) {
                $costos_refacciones[$rs['NoParteEquipo']] = ((float) $rs['PrecioTotalCompra'] );
            } else {
                $costos_refacciones[$rs['NoParteEquipo']] = ((float) $rs['PrecioTotalLista'] );
            }
        } else {
            if (isset($rs['PrecioTotalCompra']) && !empty($rs['PrecioTotalCompra'])) {
                $costos_refacciones[$rs['NoParteEquipo']] += ((float) $rs['PrecioTotalCompra'] );
            } else {
                $costos_refacciones[$rs['NoParteEquipo']] += ((float) $rs['PrecioTotalLista'] );
            }
        }
    }
    /*     * ************     Costo de tecnicos   ************* */
    $consulta = "SELECT COUNT(t.IdTicket) AS numero_tecnicos, e.NoParte,e.Modelo
        FROM `c_ticket` AS t
        LEFT JOIN k_tecnicoticket AS kt ON kt.IdTicket = t.IdTicket
        LEFT JOIN c_cliente AS c ON c.ClaveCliente = t.ClaveCliente
        LEFT JOIN c_bitacora AS b ON b.NoSerie = t.NoSerieEquipo
        LEFT JOIN c_equipo AS e ON b.NoParte = e.NoParte
        WHERE t.Activo = 1 AND c.Modalidad <> 2 AND b.VentaDirecta = 0 AND t.TipoReporte <> 15 $where_tecnicos
        GROUP BY e.NoParte
        ORDER BY numero_tecnicos DESC;";
    $result = $catalogo->obtenerLista($consulta);

    while ($rs = mysql_fetch_array($result)) {
        if (!isset($costos_tecnico[$rs['NoParte']])) {
            $costos_tecnico[$rs['NoParte']] = ((int) $rs['numero_tecnicos'] * (float) $costo_tecnico);
        } else {
            $costos_tecnico[$rs['NoParte']] += ((int) $rs['numero_tecnicos'] * (float) $costo_tecnico);
        }
    }
    /*     * ************     Costo de envios   ************* */
    $consulta = "SELECT IdEnvio, 
        e.NoParte, e.Modelo, 
        (CASE WHEN !ISNULL(IdMensajeria) THEN 1 ELSE 2 END) AS TipoEnvio,
        kem.IdSolicitud,
        kem.NoGuia, kem.IdMensajeria, kem.IdVehiculo, kem.IdConductor,
        1 AS Equipo
        FROM `k_enviosmensajeria` AS kem
        LEFT JOIN c_centrocosto AS cc ON cc.ClaveCentroCosto = kem.ClaveCentroCosto
        LEFT JOIN c_cliente AS c ON c.ClaveCliente = cc.ClaveCliente
        LEFT JOIN c_bitacora AS b ON b.NoSerie = kem.NoSerie
        LEFT JOIN c_equipo AS e ON e.NoParte = b.NoParte
        WHERE c.Modalidad <> 2 AND b.VentaDirecta = 0 AND !ISNULL(e.NoParte) 
        AND ( (!ISNULL(kem.IdMensajeria) AND !ISNULL(kem.NoGuia)) OR (!ISNULL(kem.IdVehiculo) AND !ISNULL(kem.IdConductor)) ) 
        $where_envios
        UNION ALL
        SELECT kem.IdEnvio, 
        (CASE WHEN !ISNULL(e.NoParte) THEN e.NoParte WHEN !ISNULL(e2.NoParte) THEN e2.NoParte WHEN !ISNULL(e3.NoParte) THEN e3.NoParte ELSE NULL END) AS NoParteEquipo,
        (CASE WHEN !ISNULL(e.NoParte) THEN e.Modelo WHEN !ISNULL(e2.NoParte) THEN e2.Modelo WHEN !ISNULL(e3.NoParte) THEN e3.Modelo ELSE NULL END) AS ModeloEquipo,        
        (CASE WHEN !ISNULL(kem.IdMensajeria) THEN 1 ELSE 2 END) AS TipoEnvio,
        (CASE WHEN !ISNULL(kem.IdSolicitud) THEN kem.IdSolicitud ELSE kem.IdSolicitudEquipo END) AS IdSolicitud,
        kem.NoGuia, kem.IdMensajeria, kem.IdVehiculo, kem.IdConductor,
        0 AS Equipo
        FROM k_enviotoner AS kem
        INNER JOIN c_centrocosto AS cc ON cc.ClaveCentroCosto = kem.ClaveCentroCosto
        INNER JOIN c_cliente AS c ON c.ClaveCliente = cc.ClaveCliente
        LEFT JOIN c_pedido AS p ON p.IdPedido = kem.IdSolicitud
        LEFT JOIN c_bitacora AS b ON b.NoSerie = p.ClaveEspEquipo
        LEFT JOIN c_equipo AS e ON e.NoParte = b.NoParte
        LEFT JOIN k_solicitud AS ks ON ks.id_solicitud = kem.IdSolicitudEquipo AND ks.id_partida = (SELECT MIN(id_partida) FROM k_solicitud WHERE id_solicitud = kem.IdSolicitudEquipo AND tipo = 1 AND Modelo = kem.NoParte AND kem.ClaveCentroCosto = ClaveCentroCosto)
        LEFT JOIN c_bitacora AS b2 ON b2.NoSerie = ks.NoSerie
        LEFT JOIN c_equipo AS e2 ON e2.NoParte = b2.NoParte
        LEFT JOIN c_equipo AS e3 ON e3.NoParte = (
        SELECT kecc.NoParteEquipo 
        FROM k_solicitud AS ks 
        LEFT JOIN k_equipocomponentecompatible AS kecc ON kecc.NoParteEquipo = ks.Modelo
        WHERE ks.id_solicitud = kem.IdSolicitudEquipo AND ks.tipo = 0 AND kecc.NoParteComponente = kem.NoParte
        LIMIT 0,1
        )

        WHERE c.Modalidad <> 2 AND b.VentaDirecta = 0 AND (!ISNULL(e.NoParte) OR !ISNULL(e2.NoParte) OR !ISNULL(e3.NoParte)) 
        AND ( (!ISNULL(kem.IdMensajeria) AND !ISNULL(kem.NoGuia)) OR (!ISNULL(kem.IdVehiculo) AND !ISNULL(kem.IdConductor)) ) 
        $where_envios
        ORDER BY Equipo,IdSolicitud,NoGuia,IdVehiculo,IdConductor;";
    $result = $catalogo->obtenerLista($consulta);
    $idSolicitud = "";
    $no_guia = "";
    $id_mensajeria = "";
    $vehiculo = 0;
    $conductor = 0;
    $tipo_envio = 0;
    $equipos_en_envio = array();
    $numero_equipos = 0;
    while ($rs = mysql_fetch_array($result)) {
        if (!empty($idSolicitud) && ($idSolicitud != $rs['IdSolicitud']) 
                && ($no_guia != $rs['NoGuia'] && $id_mensajeria != $rs['IdMensajeria']) 
                && ($vehiculo != $rs['IdVehiculo'] && $conductor != $rs['IdConductor'])) {//Si hay un cambio de envio
            $numero_equipos = count($equipos_en_envio);
            //Aqui se deben de guarda los precios
            foreach ($equipos_en_envio as $value) {
                //echo "<br/>Agregando $value a $numero_equipos";
                if ($numero_equipos == 0) {//No puede haber cero equipo en un envío, sin embargo se valida que mínimo haya 1
                    $numero_equipos = 1;
                }
                if (!isset($costos_envios[$value])) {
                    if ($tipo_envio == "1") {
                        $costos_envios[$value] = (/*((float) $costo_paqueteria) / */( (int) $numero_equipos));
                    } else {
                        $costos_envios[$value] = (/*((float) $costo_propio) / */( (int) $numero_equipos));
                    }
                } else {
                    if ($tipo_envio == "1") {
                        $costos_envios[$value] += (/*((float) $costo_paqueteria) /*/ ( (int) $numero_equipos));
                    } else {
                        $costos_envios[$value] += (/*((float) $costo_propio) /*/ ( (int) $numero_equipos));
                    }
                }
            }
            
            if($numero_equipos > 0){
                $numero_equipos = 0;
            }
            $equipos_en_envio = array();
        }//Fin si es cambio de envio
        $idSolicitud = $rs['IdSolicitud'];
        $tipo_envio = $rs['TipoEnvio'];
        $no_guia = $rs['NoGuia'];
        $id_mensajeria = $rs['IdMensajeria'];
        $vehiculo = $rs['IdVehiculo'];
        $conductor = $rs['IdConductor'];
        array_push($equipos_en_envio, $rs['NoParte']);        
    }
    
    if (!empty($idSolicitud)) {
        //Aqui se deben de guarda los precios
        foreach ($equipos_en_envio as $value) {
            $numero_equipos = count($equipos_en_envio);
            if ($numero_equipos == 0) {//No puede haber cero equipo en un envío, sin embargo se valida que mínimo haya 1
                $numero_equipos = 1;
            }
            if (!isset($costos_envios[$value])) {
                if ($tipo_envio == "1") {
                    $costos_envios[$value] = (((float) $costo_paqueteria) / ( (int) $numero_equipos));
                } else {
                    $costos_envios[$value] = (((float) $costo_propio) / ( (int) $numero_equipos));
                }
            } else {
                if ($tipo_envio == "1") {
                    $costos_envios[$value] += (((float) $costo_paqueteria) / ( (int) $numero_equipos));
                } else {
                    $costos_envios[$value] += (((float) $costo_propio) / ( (int) $numero_equipos));
                }
            }
        }
    }

    /* Ponemos las cabeceras */
    $cabeceras = array("NoParte" => "string", "Modelo" => "string", "Facturado" => "money", "Costo refacciones" => "money",
        "Costo tóner" => "money", "Costo técnico" => "money", "Costo envíos" => "money", "Total" => "money");

    $hoja = "Reporte";
    $writer->writeSheetHeader($hoja, $cabeceras);
    arsort($facturado_modelo);
    foreach ($facturado_modelo as $key => $value) {
        $total = (float) $value - $costos_toner[$key] - $costos_refacciones[$key] - $costos_tecnico[$key] - $costos_envios[$key];
        $array_valores = array();
        array_push($array_valores, $key);
        array_push($array_valores, $modelos_nombre[$key]);
        array_push($array_valores, $value);
        array_push($array_valores, $costos_refacciones[$key]);
        array_push($array_valores, $costos_toner[$key]);
        array_push($array_valores, $costos_tecnico[$key]);
        array_push($array_valores, $costos_envios[$key]);
        array_push($array_valores, $total);
        $writer->writeSheetRow($hoja, $array_valores);
        array_push($modelos_procesados, $key);
    }

    $result = $catalogo->getListaAlta("c_equipo", "NoParte");
    while ($rs = mysql_fetch_array($result)) {
        if (!in_array($rs['NoParte'], $modelos_procesados) /* &&
          (isset($costos_refacciones[$rs['NoParte']]) || isset($costos_toner[$rs['NoParte']]) || isset($costos_tecnico[$rs['NoParte']]) || isset($costos_envios[$rs['NoParte']])) */) {
            $key = $rs['NoParte'];
            $value = $facturado_modelo[$key];
            $total = (float) $value - ((float) $costos_refacciones[$key] + (float) $costos_toner[$key] + (float) $costos_tecnico[$key] + (float) $costos_envios[$key]);
            $array_valores = array();
            array_push($array_valores, $key);
            array_push($array_valores, $rs['Modelo']);
            array_push($array_valores, $value);
            array_push($array_valores, $costos_refacciones[$key]);
            array_push($array_valores, $costos_toner[$key]);
            array_push($array_valores, $costos_tecnico[$key]);
            array_push($array_valores, $costos_envios[$key]);
            array_push($array_valores, $total);
            $writer->writeSheetRow($hoja, $array_valores);
        }
    }
    /* $array_valores = array();
      array_push($array_valores, "Número de parte del MODELO");
      array_push($array_valores, "Modelo del equipo");
      array_push($array_valores, "Aquí se consideran el monto de las pre-facturas generadas desde reporte de lecturas y que posteriormente fueron timbradas");
      array_push($array_valores, "Se obtienen todas las refacciones enviadas a los modelos de equipo (por solicitud o por el módulo de refacciones) y se suma el costo de todas ellas. El costo de cada refacción se obtiene de la última compra de dicha refacción");
      array_push($array_valores, "Se obtienen todos los tóner enviados a los modelos de equipo (pos solicitud o por el módulo de tóner) y se suma el costo de todos ellos. El costo de cada tóner se obtiene de la misma manera que se hace con las refacciones");
      array_push($array_valores, "Se multiplica el número de técnicos asignados en tickets de los modelos por el valor asignado en el campo 'Costo de ténico'  del  los filtros para el reporte");
      array_push($array_valores, "Se multiplica el número de envíos a los modelos por el valor asignado en el campo 'Costo envío paquetería' o 'Costo envío propio' (según haya sido el tipo de envío) del  los filtros para el reporte");
      array_push($array_valores, "Se saca la diferencia del monto de factura menos la suma de los costos de refacciones, de tóner, envíos y técnicos");
      $writer->writeSheetRow($hoja, $array_valores); */
    $array_valores = array();
    array_push($array_valores, "Cualquier monto de equipo que no se haya facturado desde lecturas, no se toma en cuenta para las estadísticas");
    array_push($array_valores, "");
    array_push($array_valores, "");
    array_push($array_valores, "");
    array_push($array_valores, "");
    array_push($array_valores, "");
    array_push($array_valores, "");
    array_push($array_valores, "");
    $writer->writeSheetRow($hoja, $array_valores);
    /*$array_valores = array();
    array_push($array_valores, "Número de parte del MODELO");
    array_push($array_valores, "Modelo del equipo");
    array_push($array_valores, "Aquí se consideran el monto de las pre-facturas generadas desde reporte de lecturas y que posteriormente fueron timbradas");
    array_push($array_valores, "Se obtienen todas las refacciones enviadas a los modelos de equipo (por solicitud o por el módulo de refacciones) y se suma el costo de todas ellas. El costo de cada refacción se obtiene de la última compra de dicha refacción");
    array_push($array_valores, "Se obtienen todos los tóner enviados a los modelos de equipo (pos solicitud o por el módulo de tóner) y se suma el costo de todos ellos. El costo de cada tóner se obtiene de la misma manera que se hace con las refacciones");
    array_push($array_valores, "Se multiplica el número de técnicos asignados en tickets de los modelos por el valor asignado en el campo 'Costo de ténico'  del  los filtros para el reporte");
    array_push($array_valores, "Se multiplica el número de envíos a los modelos por el valor asignado en el campo 'Costo envío paquetería' o 'Costo envío propio' (según haya sido el tipo de envío) del  los filtros para el reporte");
    array_push($array_valores, "Se saca la diferencia del monto de factura menos la suma de los costos de refacciones, de tóner, envíos y técnicos");
    $writer->writeSheetRow($hoja, $array_valores);*/
}

$writer->writeToStdOut();
/* $writer->writeToFile('example.xlsx');
  echo $writer->writeToString(); */
exit(0);
?>