<?php
session_start();
if (!isset($_SESSION['user']) || $_SESSION['user'] == "") {
    header("Location: ../../index.php");
}
include_once("../WEB-INF/Classes/Catalogo.class.php");
include_once("../WEB-INF/Classes/EquipoCaracteristicasFormatoServicio.class.php");
$noSolicitud = $_GET['noSolicitud'];
//$noSolicitud = 2;

$catalogo = new Catalogo();
$equipo = new EquipoCaracteristicasFormatoServicio();

$consulta = "SELECT cc.ClaveCentroCosto, cc.Nombre AS localidad, c.fecha_solicitud AS fecha,cts.Nombre AS tipoSolicitud,
cli.NombreRazonSocial, cli.RFC, co.Nombre AS contacto, co.Telefono, bi.NoSerie,  d.*, cc.Nombre As centro,
(CASE WHEN ks.tipo = 0 THEN (SELECT MAX(e.Modelo) FROM `c_equipo` AS e WHERE e.NoParte = ks.Modelo) ELSE (SELECT MAX(com.Modelo) FROM `c_componente` AS com WHERE com.NoParte = ks.Modelo) END) AS Modelo,
f.RazonSocial AS razon,CONCAT(f.RazonSocial,' ',f.Calle,' No. Ext. ',f.NoExterior,' No. Int. ',f.NoInterior,' ,COL. ',f.Colonia,' ',f.Delegacion,', ',f.Pais,' ',f.Estado,' C.P. ',f.CP,' TELS.',f.Telefono) AS facturacion,
CONCAT(u.Nombre,' ',u.ApellidoPaterno,' ',u.ApellidoMaterno) AS solicito
FROM c_solicitud AS c
INNER JOIN k_solicitud AS ks ON c.id_solicitud = $noSolicitud AND ks.id_solicitud = c.id_solicitud
INNER JOIN c_tiposolicitud AS cts ON cts.IdTipoMovimiento = c.id_tiposolicitud
LEFT JOIN c_centrocosto AS cc ON ks.ClaveCentroCosto = cc.ClaveCentroCosto
INNER JOIN c_cliente AS cli ON cli.ClaveCliente = c.ClaveCliente
LEFT JOIN c_bitacora AS bi ON bi.id_solicitud = c.id_solicitud AND bi.NoParte = ks.Modelo
LEFT JOIN c_domicilio AS d ON d.ClaveEspecialDomicilio = ks.ClaveCentroCosto
LEFT JOIN c_contacto AS co ON co.ClaveEspecialContacto = cc.ClaveCentroCosto
LEFT JOIN c_datosfacturacionempresa AS f ON f.IdDatosFacturacionEmpresa = cli.IdDatosFacturacionEmpresa
LEFT JOIN c_usuario AS u ON c.id_crea = u.IdUsuario
GROUP BY cc.ClaveCentroCosto ORDER BY cc.ClaveCentroCosto;"; /* Obtenemos las localidades de la solicitud */
//echo $consulta;
$query_solicitud = $catalogo->obtenerLista($consulta);
$solicito = "";
?>
<!DOCTYPE HTML>
<html lang="es">
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
        <style>
            body{font-family: Arial; font-size: 15px;}
            .titulo{font-weight: bold; font-size: 18px;}
            table{
                border-collapse:collapse;
            }            
            .borde{border: 1px solid #000;}
            .mediano{width: 30%;}
            .gigantes{width: 600px;}
            /*.pagebreak { page-break-after: always; page-break-before: always; }*/
            .espacio{min-height: 100px;}
            /*.obscuro{background-color: #404040; color: white; text-align: center;  font-style: italic; -webkit-print-color-adjust:exact;}
            .gris{background-color: #C0C0C0; font-weight: bold;  -webkit-print-color-adjust:exact;}
            .color{background-color: #3333CC; color: white; -webkit-print-color-adjust:exact; }
            .bn{background-color: #000; color: white; }*/
            .obscuro{color: black; text-align: center;  font-style: italic;}
            .gris{font-weight: bold; }
            .color{color: black;}
            .bn{color: black; }

            .pie{font-size: 10px; color: #800000;}
            .centrado {text-align: center;}
            .completeSize{width: 97%;}
        </style>
        <title>Reporte</title>
        <link rel="shortcut icon" href="../resources/images/logos/ra4.png" type="image/x-icon"/>
    </head>
    <body>
        <a href=javascript:window.print(); style="margin: 85%;">Imprimir</a>
        <?php
        while ($resultSet = mysql_fetch_array($query_solicitud)) {//while que recorre todas las localidades de la solicitud
            $solicito = $resultSet['solicito'];
            ?>
            <div class="principal">            
                <img src="../resources/images/kyocera_reporte.png" style="float:right; margin: 0% 20% 5% 0%; height: 45px;"/>            
                <div class="titulo">FORMATO DE SOLICITUD DE EQUIPOS</div>                        
                <div style="margin-left: 83%; font-weight: bold; font-size: 20px;">No. Solicitud: <?php echo $noSolicitud; ?></div>
                <table class="completeSize">
                    <tr>
                        <td class="borde mediano obscuro centrado">SE FACTURA POR</td>
                        <td class="borde centrado" style="width: 75%;"><?php echo $resultSet['razon']; ?></td>
                    </tr>
                </table>
                <br/>
                <table class="completeSize">
                    <tr>
                        <td class="borde mediano centrado obscuro">TIPO DE MOVIMIENTO</td>
                        <td class="borde mediano centrado"><?php echo $resultSet['tipoSolicitud']; ?></td>
                        <td style="min-width: 50px;"></td>
                        <td class="borde mediano gris centrado">FECHA</td>
                        <td class="borde mediano centrado"><?php echo $resultSet['fecha']; ?></td>
                    </tr>
                </table>            
            </div>
            <br/>
            <table class="completeSize">
                <tr>
                    <td colspan="4" class="borde obscuro">DATOS GENERALES</td>
                </tr>
                <tr>
                    <td class="borde gris">NOMBRE ó RAZON SOCIAL</td>
                    <td colspan="3" class="borde"><?php echo $resultSet['NombreRazonSocial']; ?></td>
                </tr>
                <tr>
                    <td class="borde gris">CONTACTO COMERCIAL</td>
                    <td class="borde"><?php echo $resultSet['contacto']; ?></td>
                    <td class="borde gris" style="text-align: right;">RFC</td>
                    <td class="borde"><?php echo $resultSet['RFC']; ?></td>
                </tr>
                <?php
                    if(isset($resultSet['centro'])){/*Si existe la localidad*/
                ?>
                <tr>
                    <td class="borde obscuro" colspan="4"><?php echo $resultSet['centro']; ?></td>
                </tr>
                <tr>
                    <td class="borde gris">CALLE Y NÚMERO</td>
                    <td class="borde" colspan="3"><?php
                        echo $resultSet['Calle'];
                        if ($resultSet['NoExterior'] != null) {
                            echo " No. Ext: " . $resultSet['NoExterior'];
                        }
                        if ($resultSet['NoInterior'] != null) {
                            echo " No. Int: " . $resultSet['NoInterior'];
                        }
                        ?></td>
                </tr>
                <tr>
                    <td class="borde gris">COLONIA</td>
                    <td class="borde" colspan="3"><?php echo $resultSet['Colonia']; ?></td>
                </tr>
                <tr>
                    <td class="borde gris">DELEGACION ó MUNICIPIO</td>
                    <td class="borde" colspan="3"><?php echo $resultSet['Delegacion']; ?></td>
                </tr>
                <tr>
                    <td class="borde gris">CIUDAD / ESTADO</td>
                    <td class="borde" colspan="3"><?php echo $resultSet['Ciudad']; ?></td>
                </tr>
                <tr>
                    <td class="borde gris">TELEFONO Y EXTENSION</td>
                    <td class="borde" style="min-width: 20%;"><?php echo $resultSet['Telefono']; ?></td>
                    <td class="borde gris" style="text-align: right;">C. POSTAL</td>
                    <td class="borde"><?php echo $resultSet['CodigoPostal']; ?></td>
                </tr>
                <?php
                    }else{
                ?>
                <tr>
                    <td class="borde obscuro" colspan="4">Sin localidad</td>
                </tr>
                <?php
                    }
                ?>
                <!--<tr>
                    <td class="borde obscuro" colspan="4">CONDICIONES DE LA OPERACIÓN EN ARRENDAMIENTO</td>
                </tr>
                <tr>-->
                    <?php
                    /*$consulta = "SELECT NoSerie FROM `c_bitacora` WHERE id_solicitud = $noSolicitud AND ClaveCentroCosto = '" . $resultSet['ClaveCentroCosto'] . "';";
                    $result = $catalogo->obtenerLista($consulta);
                    $NoSerie = "";
                    if ($rResult = mysql_fetch_array($result)) {
                        $NoSerie = $rResult['NoSerie'];
                    }

                    $consulta = "SELECT inve.NoSerie, 
                    fa.RentaMensual AS faRenta, fa.MLIncluidosBN AS faincluidosBN, fa.MLIncluidosColor AS faincluidosColor, fa.CostoMLExcedentesBN AS faExcedentesBN, 
                    fa.CostoMLExcedentesColor AS faExcedentesColor, fa.CostoMLProcesadosBN AS faProcesadasBN, fa.CostoMLProcesadosColor AS faProcesadosColor,
                    gfa.RentaMensual AS gfaRenta, gfa.MLIncluidosBN AS gfaincluidosBN, gfa.MLIncluidosColor AS gfaincluidosColor, gfa.CostoMLExcedentesBN AS gfaExcedentesBN, 
                    gfa.CostoMLExcedentesColor AS gfaExcedentesColor, gfa.CostoMLProcesadosBN AS gfaProcesadasBN, gfa.CostoMLProcesadosColor AS gfaProcesadosColor,
                    im.RentaMensual AS imRenta, im.PaginasIncluidasBN AS imincluidosBN, im.PaginasIncluidasColor AS imincluidosColor, im.CostoPaginasExcedentesBN AS imExcedentesBN, 
                    im.CostoPaginasExcedentesColor AS imExcedentesColor, im.CostoPaginaProcesadaBN AS imProcesadasBN, im.CostoPaginaProcesadaColor AS imProcesadosColor,
                    gim.RentaMensual AS gimRenta, gim.PaginasIncluidasBN AS gimincluidosBN, gim.PaginasIncluidasColor AS gimincluidosColor, gim.CostoPaginasExcedentesBN AS gimExcedentesBN, 
                    gim.CostoPaginasExcedentesColor AS gimExcedentesColor, gim.CostoPaginaProcesadaBN AS gimProcesadasBN, gim.CostoPaginaProcesadaColor AS gimProcesadosColor
                    FROM `c_inventarioequipo` AS inve
                    LEFT JOIN k_serviciofa AS fa ON fa.IdAnexoClienteCC = inve.IdAnexoClienteCC AND fa.IdServicioFA = inve.ClaveEspKServicioFAIM
                    LEFT JOIN k_serviciogfa AS gfa ON gfa.IdAnexoClienteCC = inve.IdAnexoClienteCC AND gfa.IdServicioGFA = inve.ClaveEspKServicioFAIM
                    LEFT JOIN k_servicioim AS im ON im.IdAnexoClienteCC = inve.IdAnexoClienteCC AND im.IdServicioIM = inve.ClaveEspKServicioFAIM
                    LEFT JOIN k_serviciogim AS gim ON gim.IdAnexoClienteCC = inve.IdAnexoClienteCC AND gim.IdServicioGIM = inve.ClaveEspKServicioFAIM
                    WHERE inve.NoSerie = '$NoSerie';";
                    //echo $consulta;
                    $result = $catalogo->obtenerLista($consulta);
                    $rentaMensual = 0;
                    $incluidasBN = 0;
                    $incluidasColor = 0;
                    $excedenteBN = 0;
                    $excedenteColor = 0;
                    $procesadasBN = 0;
                    $procesadasColor = 0;
                    while ($rResult = mysql_fetch_array($result)) {
                        $prefijos = array();
                        $prefijos[0] = "fa";
                        $prefijos[1] = "gfa";
                        $prefijos[2] = "im";
                        $prefijos[3] = "gim";
                        foreach ($prefijos as $value) {/* Buscamos la renta mensual */
                        /*    if ($rResult[$value . "Renta"] != null) {
                                $rentaMensual = $rResult[$value . "Renta"];
                                break;
                            }
                        }
                        foreach ($prefijos as $value) {/* Buscamos incluidos B/N */
                            /*if ($rResult[$value . "incluidosBN"] != null) {
                                $incluidasBN = $rResult[$value . "incluidosBN"];
                                break;
                            }
                        }
                        foreach ($prefijos as $value) {/* Buscamos incluidos Color */
                            /*if ($rResult[$value . "incluidosColor"] != null) {
                                $incluidasColor = $rResult[$value . "incluidosColor"];
                                break;
                            }
                        }
                        foreach ($prefijos as $value) {/* Buscamos excedentes b/n */
                            /*if ($rResult[$value . "ExcedentesBN"] != null) {
                                $excedenteBN = $rResult[$value . "ExcedentesBN"];
                                break;
                            }
                        }
                        foreach ($prefijos as $value) {/* Buscamos excedentes color */
                            /*if ($rResult[$value . "ExcedentesColor"] != null) {
                                $excedenteColor = $rResult[$value . "ExcedentesColor"];
                                break;
                            }
                        }
                        foreach ($prefijos as $value) {/* Buscamos procesadas b/n */
                            /*if ($rResult[$value . "ProcesadasBN"] != null) {
                                $procesadasBN = $rResult[$value . "ProcesadasBN"];
                                break;
                            }
                        }
                        foreach ($prefijos as $value) {/* Buscamos procesadas color */
                            /*if ($rResult[$value . "ProcesadosColor"] != null) {
                                $procesadasColor = $rResult[$value . "ProcesadosColor"];
                                break;
                            }
                        }
                    }
                    /* Obtenemos los datos del contrato */
                    /*$consulta = "SELECT inve.NoSerie,con.NoContrato, con.FechaInicio, con.FechaTermino, period_diff(date_format(con.FechaTermino, '%Y%m'), date_format(con.FechaInicio, '%Y%m')) as meses_forzosos
                    FROM `c_inventarioequipo` AS inve
                    INNER JOIN k_anexoclientecc AS ka ON inve.IdAnexoClienteCC = ka.IdAnexoClienteCC
                    INNER JOIN c_anexotecnico AS ca ON ca.ClaveAnexoTecnico = ka.ClaveAnexoTecnico
                    INNER JOIN c_contrato AS con ON con.NoContrato = ca.NoContrato
                    WHERE inve.NoSerie = '$NoSerie';";
                    $result = $catalogo->obtenerLista($consulta);
                    $fechaInicial = "";
                    $fechaFinal = "";
                    $mesesForsozos = "";
                    while ($rResult = mysql_fetch_array($result)) {
                        $aux = explode(" ", $rResult['FechaInicio']);
                        $fechaFinal = $aux[0];
                        $aux = explode(" ", $rResult['FechaTermino']);
                        $fechaInicial = $aux[0];
                        $aux = explode(" ", $rResult['meses_forzosos']);
                        $mesesForsozos = $aux[0];
                    }*/
                    ?>
                    <!--<td class="borde gris">MESES FORZOSOS</td>
                    <td class="borde"><?php //echo $mesesForsozos; ?></td>
                    <td class="borde gris">IMPORTE RENTA BASE MENSUAL</td>
                    <td class="borde">$ <?php //echo number_format($rentaMensual, 2); ?></td>
                </tr>
                <tr>
                    <td class="borde gris">FECHA DE INICIO DEL CONTRATO</td>
                    <td class="borde"><?php //echo $fechaInicial; ?></td>
                    <td class="borde bn">PAGINAS INCLUIDAS B/N</td>
                    <td class="borde"><?php //echo $incluidasBN; ?></td>
                </tr>
                <tr>
                    <td class="borde gris">FECHA DE TERMINO DEL CONTRATO</td>
                    <td class="borde"><?php //echo $fechaFinal; ?></td>
                    <td class="borde color">PAGINAS INCLUIDAS COLOR</td>
                    <td class="borde"><?php //echo $incluidasColor; ?></td>
                </tr>
                <tr>
                    <td class="borde gris">DIAS DE CREDITO</td>
                    <td class="borde"></td>
                    <td class="borde bn">PRECIO PAGINAS EXCEDENTES EN B/N</td>
                    <td class="borde">$  <?php //echo number_format($excedenteBN, 2); ?></td>
                </tr>
                <tr>
                    <td class="borde gris">FORMA DE PAGO</td>
                    <td class="borde"></td>
                    <td class="borde color">PRECIO PAGINAS EXCEDENTES EN COLOR</td>
                    <td class="borde">$  <?php //echo number_format($excedenteColor, 2); ?></td>
                </tr>
                <tr>
                    <td class="borde gris">DIAS DE REVISION DE FACTURAS</td>
                    <td class="borde"></td>
                    <td class="borde bn">PRECIO PAGINAS PROCESADAS EN B/N       (CLICK)</td>
                    <td class="borde">$  <?php //echo number_format($procesadasBN, 2); ?></td>
                </tr>
                <tr>
                    <td class="borde gris">HORARIO DE REVISION DE FACTURAS</td>
                    <td class="borde"></td>
                    <td class="borde color">PRECIO PAGINAS PROCESADAS EN COLOR (CLICK)</td>
                    <td class="borde">$  <?php //echo number_format($procesadasColor, 2); ?></td>
                </tr>-->
                <tr>
                    <td class="borde obscuro" colspan="4">DESCRIPCION DE EQUIPOS - ACCESORIOS - CONSUMIBLES</td>
                </tr>
            </table>
            <table class="completeSize">
                <tr>
                    <td class='borde' style="width: 15%;">CANTIDAD</td>
                    <td class='borde' style="width: 20%;">MODELO</td>
                    <td class='borde' style="width: 20%;">No SERIE</td>
                    <td class='borde' style="width: 20%;">CONTADORES</td>
                    <td class='borde' style="width: 25%;">COMENTARIO</td>                    
                </tr>
                <?php
                $consulta = "SELECT SUM(cantidad_autorizada) AS autorizada FROM k_solicitud WHERE id_solicitud = $noSolicitud AND tipo = 0;";
                $query = $catalogo->obtenerLista($consulta);
                $cantidad_autorizada = 0;
                while ($rs = mysql_fetch_array($query)) {
                    $cantidad_autorizada = intval($rs['autorizada']);
                }

                $consulta = "SELECT count(NoSerie) AS asignadas FROM c_bitacora WHERE id_solicitud = $noSolicitud;";
                $query = $catalogo->obtenerLista($consulta);
                $cantidad_asignada = 0;
                while ($rs = mysql_fetch_array($query)) {
                    $cantidad_asignada = intval($rs['asignadas']);
                }
                
                if ($cantidad_asignada > 0 && $cantidad_asignada == $cantidad_autorizada) {
                    if(isset($resultSet['ClaveCentroCosto'])){
                        $consulta = "SELECT DISTINCT(bi.NoSerie) AS NoSerie, 
                            (CASE WHEN !ISNULL(l2.IdLectura) THEN 'Usado' WHEN !ISNULL(lt.id_lecturaticket) THEN 'Usado' ELSE 'Nuevo' END) AS EstadoEquipoByLectura,
                            ( CASE WHEN c.estatus = 0 THEN ks.cantidad ELSE ks.cantidad_autorizada END) AS autorizada, ks.tipo, ks.Modelo AS NoParte, c.comentario, cc.Nombre AS localidad, c.fecha_solicitud AS fecha,
                            ti.Nombre AS estadoEquipo,l.ContadorBNPaginas, l.ContadorBNML, l.ContadorColorPaginas, l.ContadorColorML, l.NivelTonNegro, l.NivelTonMagenta, l.NivelTonCian, l.NivelTonAmarillo,
                            c.estatus,cli.NombreRazonSocial, cli.RFC, co.Nombre AS contacto, d.*, cc.Nombre As centro, ks.cantidad_autorizada,
                            (CASE WHEN ks.tipo = 0 THEN (SELECT MAX(e.Modelo) FROM `c_equipo` AS e WHERE e.NoParte = ks.Modelo) ELSE (SELECT MAX(com.Modelo) FROM `c_componente` AS com WHERE com.NoParte = ks.Modelo) END) AS Modelo,
                            f.RazonSocial AS razon,CONCAT(f.RazonSocial,' ',f.Calle,' No. Ext. ',f.NoExterior,' No. Int. ',f.NoInterior,' ,COL. ',f.Colonia,' ',f.Delegacion,', ',f.Pais,' ',f.Estado,' C.P. ',f.CP,' TELS.',f.Telefono) AS facturacion
                            FROM c_solicitud AS c
                            INNER JOIN k_solicitud AS ks ON c.id_solicitud = $noSolicitud AND ks.id_solicitud = c.id_solicitud
                            INNER JOIN c_centrocosto AS cc ON ks.ClaveCentroCosto = cc.ClaveCentroCosto AND ks.ClaveCentroCosto = '" . $resultSet['ClaveCentroCosto'] . "'
                            INNER JOIN c_cliente AS cli ON cli.ClaveCliente = cc.ClaveCliente
                            LEFT JOIN c_bitacora AS bi ON bi.id_solicitud = c.id_solicitud AND bi.NoParte = ks.Modelo AND ks.ClaveCentroCosto = bi.ClaveCentroCosto
                            LEFT JOIN c_domicilio AS d ON d.IdDomicilio = (SELECT MAX(IdDomicilio) FROM c_domicilio WHERE ClaveEspecialDomicilio = ks.ClaveCentroCosto)
                            LEFT JOIN c_contacto AS co ON co.IdContacto = (SELECT MAX(IdContacto) FROM c_contacto WHERE ClaveEspecialContacto = cc.ClaveCentroCosto)
                            LEFT JOIN c_datosfacturacionempresa AS f ON f.IdDatosFacturacionEmpresa = cli.IdDatosFacturacionEmpresa 
                            LEFT JOIN k_enviosmensajeria AS kem ON kem.IdSolicitud = c.id_solicitud AND kem.NoSerie = bi.NoSerie
                            LEFT JOIN c_lectura AS l ON l.IdLectura = (SELECT MAX(IdLectura) FROM c_lectura WHERE IdSolicitud = c.id_solicitud AND NoSerie = bi.NoSerie)
                            LEFT JOIN c_tipoinventario AS ti ON ti.idTipo = ks.TipoInventario
                            LEFT JOIN c_lectura AS l2 ON l2.IdLectura = (SELECT MAX(IdLectura) FROM c_lectura WHERE NoSerie = bi.NoSerie AND (ISNULL(IdSolicitud) OR IdSolicitud <> c.id_solicitud))
                            LEFT JOIN c_lecturasticket AS lt ON lt.id_lecturaticket = (SELECT MAX(id_lecturaticket) FROM c_lecturasticket WHERE ClvEsp_Equipo = bi.NoSerie)                            
                            ORDER BY tipo,NoParte;";                        
                    }else{
                        $consulta = "SELECT DISTINCT(bi.NoSerie) AS NoSerie, 
                            (CASE WHEN !ISNULL(l2.IdLectura) THEN 'Usado' WHEN !ISNULL(lt.id_lecturaticket) THEN 'Usado' ELSE 'Nuevo' END) AS EstadoEquipoByLectura,
                            ( CASE WHEN c.estatus = 0 THEN ks.cantidad ELSE ks.cantidad_autorizada END) AS autorizada, ks.tipo, ks.Modelo AS NoParte, c.comentario, c.fecha_solicitud AS fecha,
                            ti.Nombre AS estadoEquipo,l.ContadorBNPaginas, l.ContadorBNML, l.ContadorColorPaginas, l.ContadorColorML, l.NivelTonNegro, l.NivelTonMagenta, l.NivelTonCian, l.NivelTonAmarillo,
                            c.estatus,cli.NombreRazonSocial, cli.RFC, ks.cantidad_autorizada,
                            (CASE WHEN ks.tipo = 0 THEN (SELECT MAX(e.Modelo) FROM `c_equipo` AS e WHERE e.NoParte = ks.Modelo) ELSE (SELECT MAX(com.Modelo) FROM `c_componente` AS com WHERE com.NoParte = ks.Modelo) END) AS Modelo,
                            f.RazonSocial AS razon,CONCAT(f.RazonSocial,' ',f.Calle,' No. Ext. ',f.NoExterior,' No. Int. ',f.NoInterior,' ,COL. ',f.Colonia,' ',f.Delegacion,', ',f.Pais,' ',f.Estado,' C.P. ',f.CP,' TELS.',f.Telefono) AS facturacion
                            FROM c_solicitud AS c
                            INNER JOIN k_solicitud AS ks ON c.id_solicitud = $noSolicitud AND ks.id_solicitud = c.id_solicitud
                            INNER JOIN c_cliente AS cli ON cli.ClaveCliente = c.ClaveCliente
                            LEFT JOIN c_bitacora AS bi ON bi.id_solicitud = c.id_solicitud AND bi.NoParte = ks.Modelo
                            LEFT JOIN c_datosfacturacionempresa AS f ON f.IdDatosFacturacionEmpresa = cli.IdDatosFacturacionEmpresa
                            LEFT JOIN k_enviosmensajeria AS kem ON kem.IdSolicitud = c.id_solicitud AND kem.NoSerie = bi.NoSerie
                            LEFT JOIN c_lectura AS l ON l.IdLectura = (SELECT MAX(IdLectura) FROM c_lectura WHERE IdSolicitud = c.id_solicitud AND NoSerie = bi.NoSerie)
                            LEFT JOIN c_tipoinventario AS ti ON ti.idTipo = ks.TipoInventario
                            LEFT JOIN c_lectura AS l2 ON l2.IdLectura = (SELECT MAX(IdLectura) FROM c_lectura WHERE NoSerie = bi.NoSerie AND (ISNULL(IdSolicitud) OR IdSolicitud <> c.id_solicitud))
                            LEFT JOIN c_lecturasticket AS lt ON lt.id_lecturaticket = (SELECT MAX(id_lecturaticket) FROM c_lecturasticket WHERE ClvEsp_Equipo = bi.NoSerie)
                            WHERE ISNULL(ks.ClaveCentroCosto)                            
                            ORDER BY tipo,NoParte;";
                    }
                } else {
                    if(isset($resultSet['ClaveCentroCosto'])){
                        $consulta = "SELECT bi.NoSerie, 
                            (CASE WHEN !ISNULL(l2.IdLectura) THEN 'Usado' WHEN !ISNULL(lt.id_lecturaticket) THEN 'Usado' ELSE 'Nuevo' END) AS EstadoEquipoByLectura,
                            ( CASE WHEN c.estatus = 0 THEN ks.cantidad ELSE ks.cantidad_autorizada END) AS autorizada, ks.tipo, ks.Modelo AS NoParte, c.comentario, cc.Nombre AS localidad, c.fecha_solicitud AS fecha,
                            ti.Nombre AS estadoEquipo,l.ContadorBNPaginas, l.ContadorBNML, l.ContadorColorPaginas, l.ContadorColorML, l.NivelTonNegro, l.NivelTonMagenta, l.NivelTonCian, l.NivelTonAmarillo,
                            c.estatus,cli.NombreRazonSocial, cli.RFC, co.Nombre AS contacto, d.*, cc.Nombre As centro, ks.cantidad_autorizada,
                            (CASE WHEN ks.tipo = 0 THEN (SELECT MAX(e.Modelo) FROM `c_equipo` AS e WHERE e.NoParte = ks.Modelo) ELSE (SELECT MAX(com.Modelo) FROM `c_componente` AS com WHERE com.NoParte = ks.Modelo) END) AS Modelo,
                            f.RazonSocial AS razon,CONCAT(f.RazonSocial,' ',f.Calle,' No. Ext. ',f.NoExterior,' No. Int. ',f.NoInterior,' ,COL. ',f.Colonia,' ',f.Delegacion,', ',f.Pais,' ',f.Estado,' C.P. ',f.CP,' TELS.',f.Telefono) AS facturacion
                            FROM c_solicitud AS c
                            INNER JOIN k_solicitud AS ks ON c.id_solicitud = $noSolicitud AND ks.id_solicitud = c.id_solicitud
                            INNER JOIN c_centrocosto AS cc ON ks.ClaveCentroCosto = cc.ClaveCentroCosto AND ks.ClaveCentroCosto = '" . $resultSet['ClaveCentroCosto'] . "'
                            INNER JOIN c_cliente AS cli ON cli.ClaveCliente = cc.ClaveCliente
                            LEFT JOIN c_bitacora AS bi ON bi.id_solicitud = c.id_solicitud AND bi.NoParte = ks.Modelo AND ks.ClaveCentroCosto = bi.ClaveCentroCosto
                            LEFT JOIN c_domicilio AS d ON d.IdDomicilio = (SELECT MAX(IdDomicilio) FROM c_domicilio WHERE ClaveEspecialDomicilio = ks.ClaveCentroCosto)
                            LEFT JOIN c_contacto AS co ON co.IdContacto = (SELECT MAX(IdContacto) FROM c_contacto WHERE ClaveEspecialContacto = cc.ClaveCentroCosto)
                            LEFT JOIN c_datosfacturacionempresa AS f ON f.IdDatosFacturacionEmpresa = cli.IdDatosFacturacionEmpresa                         
                            LEFT JOIN k_enviosmensajeria AS kem ON kem.IdSolicitud = c.id_solicitud AND kem.NoSerie = bi.NoSerie
                            LEFT JOIN c_lectura AS l ON l.IdLectura = (SELECT MAX(IdLectura) FROM c_lectura WHERE IdSolicitud = c.id_solicitud AND NoSerie = bi.NoSerie)
                            LEFT JOIN c_tipoinventario AS ti ON ti.idTipo = ks.TipoInventario
                            LEFT JOIN c_lectura AS l2 ON l2.IdLectura = (SELECT MAX(IdLectura) FROM c_lectura WHERE NoSerie = bi.NoSerie AND (ISNULL(IdSolicitud) OR IdSolicitud <> c.id_solicitud))
                            LEFT JOIN c_lecturasticket AS lt ON lt.id_lecturaticket = (SELECT MAX(id_lecturaticket) FROM c_lecturasticket WHERE ClvEsp_Equipo = bi.NoSerie)                           
                            ORDER BY tipo,NoParte;";
                    }else{
                        $consulta = "SELECT bi.NoSerie, 
                            (CASE WHEN !ISNULL(l2.IdLectura) THEN 'Usado' WHEN !ISNULL(lt.id_lecturaticket) THEN 'Usado' ELSE 'Nuevo' END) AS EstadoEquipoByLectura,
                            ( CASE WHEN c.estatus = 0 THEN ks.cantidad ELSE ks.cantidad_autorizada END) AS autorizada, ks.tipo, ks.Modelo AS NoParte, c.comentario, c.fecha_solicitud AS fecha,
                            ti.Nombre AS estadoEquipo,c.estatus,cli.NombreRazonSocial, cli.RFC, ks.cantidad_autorizada,
                            l.ContadorBNPaginas, l.ContadorBNML, l.ContadorColorPaginas, l.ContadorColorML, l.NivelTonNegro, l.NivelTonMagenta, l.NivelTonCian, l.NivelTonAmarillo,
                            (CASE WHEN ks.tipo = 0 THEN (SELECT MAX(e.Modelo) FROM `c_equipo` AS e WHERE e.NoParte = ks.Modelo) ELSE (SELECT MAX(com.Modelo) FROM `c_componente` AS com WHERE com.NoParte = ks.Modelo) END) AS Modelo,
                            f.RazonSocial AS razon,CONCAT(f.RazonSocial,' ',f.Calle,' No. Ext. ',f.NoExterior,' No. Int. ',f.NoInterior,' ,COL. ',f.Colonia,' ',f.Delegacion,', ',f.Pais,' ',f.Estado,' C.P. ',f.CP,' TELS.',f.Telefono) AS facturacion
                            FROM c_solicitud AS c
                            INNER JOIN k_solicitud AS ks ON c.id_solicitud = $noSolicitud AND ks.id_solicitud = c.id_solicitud
                            INNER JOIN c_cliente AS cli ON cli.ClaveCliente = c.ClaveCliente
                            LEFT JOIN c_bitacora AS bi ON bi.id_solicitud = c.id_solicitud AND bi.NoParte = ks.Modelo
                            LEFT JOIN c_datosfacturacionempresa AS f ON f.IdDatosFacturacionEmpresa = cli.IdDatosFacturacionEmpresa 
                            LEFT JOIN k_enviosmensajeria AS kem ON kem.IdSolicitud = c.id_solicitud AND kem.NoSerie = bi.NoSerie
                            LEFT JOIN c_lectura AS l ON l.IdLectura = (SELECT MAX(IdLectura) FROM c_lectura WHERE IdSolicitud = c.id_solicitud AND NoSerie = bi.NoSerie)
                            LEFT JOIN c_tipoinventario AS ti ON ti.idTipo = ks.TipoInventario
                            LEFT JOIN c_lectura AS l2 ON l2.IdLectura = (SELECT MAX(IdLectura) FROM c_lectura WHERE NoSerie = bi.NoSerie AND (ISNULL(IdSolicitud) OR IdSolicitud <> c.id_solicitud))
                            LEFT JOIN c_lecturasticket AS lt ON lt.id_lecturaticket = (SELECT MAX(id_lecturaticket) FROM c_lecturasticket WHERE ClvEsp_Equipo = bi.NoSerie)
                            WHERE ISNULL(ks.ClaveCentroCosto)                             
                            ORDER BY tipo,NoParte;";
                    }
                }
                //echo $consulta;
                $modelo_anterior = ""; //Para identifcar un cambio, y asi poder poner los modelos que no tienen numero de serie, en el caso de solicitudes a medias                    
                $num_equipos = 1;
                $cantidad_autorizada = 0;
                $tipo_anterior = "";
                $comentario = "";
                $estatus = "0";
                $serie = false;

                $query = $catalogo->obtenerLista($consulta);
                while ($rs = mysql_fetch_array($query)) {
                    $estatus = $rs['estatus'];
                    $comentario = $rs['comentario'];
                    
                    /* Esta condicion es para solicitudes a medias */
                    if ($serie && $estatus == "1" && $tipo_anterior == "0" && $modelo_anterior != "" && $modelo_anterior != $rs['Modelo']) {
                        for (; $num_equipos <= $cantidad_autorizada;) {
                            echo "<tr>";
                            echo "<td class='borde'>1</td><td class='borde'>" . $modelo_anterior . "</td><td class='borde'></td><td class='borde'>" . $comentario . "</td>
                                    <td class='borde'></td>";
                            echo "</tr>";
                            $num_equipos++;
                        }
                        $num_equipos = 1;
                        $serie = false;
                    } else if ($estatus == "1" && $rs['tipo'] == "0") {
                        $num_equipos++;
                        if ($rs['NoSerie'] != null && $rs['NoSerie'] != "") {
                            $serie = true;
                        }
                    }
                    $contadores = "";
                    $estadoEquipo = "";
                    
                    if ($rs['NoSerie'] == null || $rs['NoSerie'] == "") {
                        $cantidad = $rs['autorizada'];
                    } else {
                        $cantidad = 1;
                        $fa = false;                        
                         /*Guardamos la lectura registrada del equipo*/                        
                        $equipo = new EquipoCaracteristicasFormatoServicio();
                        $result2 = $equipo->getCaracteristicasByParte($rs['Modelo']);
                        while($rs2 = mysql_fetch_array($result2)){                            
                            if($rs2['IdFormatoEquipo'] == "3"){
                                $fa = true;
                            }
                        }
                        
                        if(!$fa){
                            if(isset($rs['ContadorBNPaginas'])){
                                $contadores.="Contador BN: ".$rs['ContadorBNPaginas'];
                            }
                            if(isset($rs['ContadorColorPaginas'])){
                                $contadores.="<br/>Contador color: ".$rs['ContadorColorPaginas'];
                            }
                        }else{
                            if(isset($rs['ContadorBNML'])){
                                $contadores.="Contador BN: ".$rs['ContadorBNML'];
                            }
                            if(isset($rs['ContadorColorML'])){
                                $contadores.="<br/>Contador color: ".$rs['ContadorColorML'];
                            }
                        }
                        if(isset($rs['NivelTonNegro'])){
                            $contadores.="<br/>Toner negro: ".$rs['NivelTonNegro']."%";
                        }
                        if(isset($rs['NivelTonMagenta'])){
                            $contadores.="<br/>Toner magenta: ".$rs['NivelTonMagenta']."%";
                        }
                        if(isset($rs['NivelTonCian'])){
                            $contadores.="<br/>Toner cian: ".$rs['NivelTonCian']."%";
                        }
                        if(isset($rs['NivelTonAmarillo'])){
                            $contadores.="<br/>Toner amarillo: ".$rs['NivelTonAmarillo']."%";
                        }
                        if(isset($rs['EstadoEquipoByLectura'])){
                            $estadoEquipo = "(".$rs['EstadoEquipoByLectura'].")";
                        }
                    }                    
                    
                    echo "<tr>";
                    echo "<td class='borde'>$cantidad</td><td class='borde'>" . $rs['Modelo'] . "</td><td class='borde'>" . $rs['NoSerie'] . " $estadoEquipo</td>
                            <td class='borde'>$contadores</td><td class='borde'>" . $comentario . "</td>";
                    echo "</tr>";

                    $modelo_anterior = $rs['Modelo'];
                    $cantidad_autorizada = intval($rs['cantidad_autorizada']);
                    $tipo_anterior = $rs['tipo'];
                }//Cierre

                /* Esta condicion es para solicitudes a medias */
                if ($serie && $estatus == "1" && $tipo_anterior == "0" && $modelo_anterior != "") {
                    for (; $num_equipos < $cantidad_autorizada;) {
                        echo "<tr>";
                        echo "<td class='borde'>1</td><td class='borde'>" . $modelo_anterior . "</td><td class='borde'></td><td class='borde'>$comentario</td>
                                <td class='borde'></td>";
                        echo "</tr>";
                        $num_equipos++;
                    }
                    $num_equipos = 0;
                }
                ?>
                <tr>
                    <td class="borde obscuro" colspan="5">OBSERVACIONES</td>
                </tr>
                <tr>
                    <td class="borde" colspan="5"><div class="espacio"></div></td>
                </tr>
            </table>
            <table class="completeSize">
                <tr>
                    <td class="borde centrado" style="width: 33%;">CLIENTE</td>
                    <td class="borde centrado" style="width: 33%;">EJECUTIVO DE CUENTAS</td>
                    <td class="borde centrado" style="width: 33%;">AUTORIZACIÓN</td>
                </tr>
                <tr>
                    <td class="borde"><div class="espacio"></div></td>
                    <td class="borde centrado"><div class="espacio"></div><?php echo $solicito; ?></td>
                    <td class="borde centrado"><div class="espacio centrado"></div>LIC. CLAUDIA MORENO</td>
                </tr>
                <tr>
                    <td class="borde centrado">FIRMA</td>
                    <td class="borde centrado">FIRMA</td>
                    <td class="borde centrado">FIRMA</td>
                </tr>
            </table>  
            <div class="pie">
    <?php echo $resultSet['facturacion']; ?>
            </div>    
            <br/><br/><br/>
            <div style="page-break-after: always;"></div>
    <?php
}//Cierra while que recorre las localidades
?>
    </body>
</html>
