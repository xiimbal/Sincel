*<?php
session_start();
if (!isset($_SESSION['user']) || $_SESSION['user'] == "") {
    header("Location: ../../index.php");
}
include_once("../WEB-INF/Classes/Catalogo.class.php");
include_once("../WEB-INF/Classes/LecturaTicket.class.php");
include_once("../WEB-INF/Classes/ResurtidoToner.class.php");
include_once("../WEB-INF/Classes/PermisosSubMenu.class.php");
include_once("../WEB-INF/Classes/Parametros.class.php");

$permisos_grid2 = new PermisosSubMenu();
$nombre_objeto = $permisos_grid2->getNombreTicketSistema();
$nombre_puesto = $permisos_grid2->getNombreTecnicoSistema();

$parametros = new Parametros();
$mostrarContadores = true;
if ($parametros->getRegistroById("13") && $parametros->getValor() == "0") {
    $mostrarContadores = false;
}

$catalogo = new Catalogo();
$lecturaTicket = new LecturaTicket();
$idTicket = $_GET['idTicket'];
$tipoReporte = "";
$orden = "";
$claveCliente = "";
$claveLocalidad = "";
$nombreCliente = "";
$nombreLocalidad = "";
$contacto = "";
$telefono1 = "";
$extencion1 = "";
$telefono2 = "";
$extencion2 = "";
$celular = "";
$correo = "";
$fechaHoraTicket = "";
$serieFalla = "";
$ModeloFalla = "";
$domicilio = "";
$contadorNegro = "";
$contadorColor = "";
$nivelNegro = "";
$nivelCia = "";
$nivelMagenta = "";
$nivelAmarillo = "";
$descripcionReporte = "";
$observacionAdicional = "";
$domicilioCliente = "";
$ticketCliente = "";
$ticketDistribucion = "";
$EstadoTicketDatos = "";
$resurtido = "0";
$fechaValidado = "";
$series = array();
$compatibleNuevo = false;

$consulta = "SELECT 
    (SELECT CASE WHEN t.AreaAtencion = 2 THEN (SELECT group_concat(ClaveEspEquipo separator ', ') FROM `c_pedido` WHERE IdTicket = t.IdTicket) ELSE t.NoSerieEquipo END) AS NumSerie, 
    t.TipoReporte,t.ClaveCliente,t.ClaveCentroCosto,t.NombreCliente,t.NombreCentroCosto,t.NombreResp,t.EstadoDeTicket,t.Resurtido,
    t.Telefono1Resp,t.Telefono2Resp,t.Extension1Resp,t.Extension2Resp, t.CelularResp,t.CorreoEResp,t.FechaHora,t.NoSerieEquipo,
    mpt.FechaUltimaModificacion AS fechaValidado, mpt.Contestada AS aceptado,t.ModeloEquipo,t.DescripcionReporte,d.Calle,d.NoExterior,d.NoInterior,d.Colonia,d.Delegacion,d.Ciudad,d.CodigoPostal,t.ObservacionAdicional,t.NoTicketCliente,t.NoTicketDistribuidor,
    (SELECT CASE WHEN ISNULL(GROUP_CONCAT(DISTINCT a.nombre_almacen)) THEN GROUP_CONCAT(DISTINCT a2.nombre_almacen) ELSE GROUP_CONCAT(DISTINCT a.nombre_almacen) END) AS almacen
    FROM c_ticket t 
    LEFT JOIN c_domicilio d ON d.ClaveEspecialDomicilio=t.ClaveCentroCosto 
    INNER JOIN c_notaticket AS nt ON nt.IdTicket = t.IdTicket
    LEFT JOIN k_nota_refaccion AS nr ON nt.IdNotaTicket = nr.IdNotaTicket
    LEFT JOIN c_almacen AS a ON a.id_almacen = nr.IdAlmacen
    LEFT JOIN k_resurtidotoner AS rt ON rt.IdTicket = t.IdTicket 
    LEFT JOIN c_almacen AS a2 ON a2.id_almacen = rt.IdAlmacen
    LEFT JOIN c_mailpedidotoner AS mpt ON mpt.idTicket = t.IdTicket
    WHERE t.IdTicket='$idTicket' ORDER BY NumSerie DESC;";

$queryTicket = $catalogo->obtenerLista($consulta);
if ($rs = mysql_fetch_array($queryTicket)) {
    $series = explode(", ", $rs['NumSerie']);
    $tipoReporte = $rs['TipoReporte'];
    $claveCliente = $rs['ClaveCliente'];
    $claveLocalidad = $rs['ClaveCentroCosto'];
    $claveLocalidadEstadoTicket = $rs['ClaveCentroCosto'];
    $nombreCliente = $rs['NombreCliente'];
    $nombreLocalidad = $rs['NombreCentroCosto'];
    $contacto = $rs['NombreResp'];
    $telefono1 = $rs['Telefono1Resp'];
    $telefono2 = $rs['Telefono2Resp'];
    $extencion1 = $rs['Extension1Resp'];
    $extencion2 = $rs['Extension2Resp'];
    $celular = $rs['CelularResp'];
    $correo = $rs['CorreoEResp'];
    $fechaHoraTicket = $rs['FechaHora'];
    $serieFalla = $rs['NoSerieEquipo'];
    $ModeloFalla = $rs['ModeloEquipo'];
    $descripcionReporte = $rs['DescripcionReporte'];
    $observacionAdicional = $rs['ObservacionAdicional'];
    $ticketCliente = $rs['NoTicketCliente'];
    $ticketDistribucion = $rs['NoTicketDistribuidor'];
    $EstadoTicketDatos = $rs['EstadoDeTicket'];
    $domicilioCliente = $rs['Calle'] . "," . $rs['NoExterior'] . ",No. Int: " . $rs['NoInterior'] . "," . $rs['Colonia'] . "," . $rs['Delegacion'] . "," . $rs['Ciudad'] . "," . $rs['CodigoPostal'];
    $resurtido = $rs['Resurtido'];
    $almacen = $rs['almacen'];
    $fechaValidado = $rs['fechaValidado'];
    $aceptado = $rs['aceptado'];
}
sort($series);
if ($tipoReporte != "15") {
    $orden = "Orden de Resurtido de mini almacén $almacen";
    if ($serieFalla != "") {
        $lecturaTicket->setNoSerie($serieFalla);
        $lecturaTicket->getLecturaByTicket($idTicket);
        $fechaContadorAnterior = $lecturaTicket->getFechaA();
        $contadorNegro = $lecturaTicket->getContadorBNA();
        $contadorColor = $lecturaTicket->getContadorColorA();
        $nivelNegro = $lecturaTicket->getNivelNegroA();
        $nivelCia = $lecturaTicket->getNivelCiaA();
        $nivelMagenta = $lecturaTicket->getNivelMagentaA();
        $nivelAmarillo = $lecturaTicket->getNivelAmarilloA();
    }
}
list($fechaTicket, $horaTicket) = explode(" ", $fechaHoraTicket);
list($anio, $mes, $dia) = explode("-", $fechaTicket);
$fechaTicket = $dia . "-" . $mes . "-" . $anio;
$idDatosFacturacion = "";
$nombreLogo = "";
$queryFacturacion = $catalogo->obtenerLista("SELECT df.Telefono,df.ImagenPHP,df.IdDatosFacturacionEmpresa,df.Calle,df.NoExterior,df.Colonia,df.Delegacion,df.Estado,df.CP FROM c_datosfacturacionempresa df WHERE df.IdDatosFacturacionEmpresa=(SELECT c.IdDatosFacturacionEmpresa FROM c_cliente c WHERE c.ClaveCliente='$claveCliente')");
while ($rs = mysql_fetch_array($queryFacturacion)) {
    $idDatosFacturacion = $rs['IdDatosFacturacionEmpresa'];
    $nombreLogo = $rs['ImagenPHP'];
    $telefonos = $rs['Telefono'];
    $domicilio = $rs['Calle'] . "," . $rs['NoExterior'] . "," . $rs['Colonia'] . "," . $rs['Delegacion'] . "," . $rs['Estado'] . "," . $rs['CP'];
    if ($telefonos != "") {
        $domicilio = $domicilio . "<br/>Telefonos: " . $telefonos;
    }
}

/* * ********************** Query para llenar la información de las tablas  ************************** */

$resurtidoToner = new ResurtidoToner();
$resurtidoToner->setIdTicket($idTicket);
$query = $resurtidoToner->getTabla();
$primeraFila1 = "";
$primeraFila2 = "";
$tabla = "";
$idAlmacen = "";
$fecha = "";
$cliente = "";
$localidad = "";
$val = false;
$rowspan = 1;
$contestada = 0;
$filas = "";
$arrayCantidadComponente = array();
$arrayComponenteModelo = array();
$arrayFechaComponente = array();

/* * ********************** **********************************************  ************************** */

if (($EstadoTicketDatos == "2" || $EstadoTicketDatos == "4") && $tipoReporte != "15") {
    $consultaLocalidad = "SELECT c.ClaveCliente,c.NombreRazonSocial,c.IdTipoCliente,c.IdEstatusCobranza, cc.Nombre AS localidad,
    td.Nombre AS tdomicilio,d.Calle,d.Colonia,d.Delegacion, 
    (CASE WHEN !ISNULL(cc.ClaveZona) THEN cc.ClaveZona ELSE c.ClaveZona END) AS zona, d.NoExterior,
    d.NoInterior,d.Ciudad,d.CodigoPostal,d.Estado,
    (SELECT z.fk_id_gzona FROM c_zona z WHERE z.ClaveZona=cc.ClaveZona OR z.ClaveZona=c.ClaveZona LIMIT 1) AS ubicacion, 
    (SELECT GROUP_CONCAT(u.Nombre,' ',u.ApellidoPaterno,' ',u.ApellidoMaterno) FROM k_tfscliente tfsc,c_usuario u WHERE cc.ClaveCliente=tfsc.ClaveCliente AND cc.ClaveCliente=tfsc.ClaveCliente AND u.IdUsuario=tfsc.IdUsuario GROUP BY tfsc.ClaveCliente) as tfs,
    (SELECT ct.Nombre FROM c_contacto ct WHERE ct.ClaveEspecialContacto=cc.ClaveCentroCosto ORDER BY ct.IdContacto DESC LIMIT 1) AS nombreContacto,
    (SELECT ct.Telefono FROM c_contacto ct WHERE ct.ClaveEspecialContacto=cc.ClaveCentroCosto ORDER BY ct.IdContacto DESC LIMIT 1) AS Telefono,
    (SELECT ct.Celular FROM c_contacto ct WHERE ct.ClaveEspecialContacto=cc.ClaveCentroCosto ORDER BY ct.IdContacto DESC LIMIT 1) AS Celular,
    (SELECT ct.CorreoElectronico FROM c_contacto ct WHERE ct.ClaveEspecialContacto=cc.ClaveCentroCosto ORDER BY ct.IdContacto DESC LIMIT 1) AS CorreoElectronico
    FROM c_centrocosto cc,c_domicilio d,c_cliente c,c_tipodomicilio td
    WHERE cc.ClaveCentroCosto=d.ClaveEspecialDomicilio AND cc.ClaveCliente=c.ClaveCliente AND td.IdTipoDomicilio=d.IdTipoDomicilio 
    AND cc.ClaveCentroCosto='$claveLocalidadEstadoTicket'";
} else {
    if ($tipoReporte != "15") {
        $consultaLocalidadEquipo = "SELECT ie.NoSerie,(SELECT CASE WHEN ISNULL(ie.IdKserviciogimgfa) 
                                            THEN (SELECT cc.ClaveCentroCosto FROM k_anexoclientecc an,c_centrocosto cc WHERE cc.ClaveCentroCosto=an.CveEspClienteCC AND an.IdAnexoClienteCC=ie.IdAnexoClienteCC)
                                            ELSE (SELECT cc.ClaveCentroCosto FROM c_centrocosto cc,k_serviciogimgfa sg WHERE sg.IdKserviciogimgfa=ie.IdKserviciogimgfa AND sg.ClaveCentroCosto=cc.ClaveCentroCosto)END )AS Localidad                                            
                                            FROM c_inventarioequipo ie,k_equipocaracteristicaformatoservicio fs WHERE ie.NoSerie='$serieFalla' AND fs.NoParte=ie.NoParteEquipo AND fs.IdTipoServicio<>2 ORDER BY fs.IdFormatoEquipo ASC LIMIT 1";
        $queryConsultaLocalidad = $catalogo->obtenerLista($consultaLocalidadEquipo);
        while ($rs = mysql_fetch_array($queryConsultaLocalidad)) {
            $claveLocalidad = $rs['Localidad'];
        }

        $consultaDomicilioLocalidad = "SELECT c.NombreRazonSocial,cc.Nombre,d.Calle,d.NoExterior,d.NoInterior,d.Colonia,d.Delegacion,d.Estado,
            d.CodigoPostal,d.Ciudad
            FROM c_domicilio d,c_cliente c,c_centrocosto cc 
            WHERE d.ClaveEspecialDomicilio='$claveLocalidad' AND d.ClaveEspecialDomicilio=cc.ClaveCentroCosto AND cc.ClaveCliente=c.ClaveCliente";
        $queryConsultaDomicilio = $catalogo->obtenerLista($consultaDomicilioLocalidad);
        if ($rs = mysql_fetch_array($queryConsultaDomicilio)) {
            $nombreCliente = $rs['NombreRazonSocial'];
            $nombreLocalidad = $rs['Nombre'];
            $domicilioCliente = $rs['Calle'] . "," . $rs['NoExterior'] . ", No. Int: " . $rs['NoInterior'] . " ," . $rs['Colonia'] . "," . $rs['Delegacion'] . "," . $rs['Ciudad'] . "," . $rs['CodigoPostal'];
        }
    } else if ($resurtido == "1") {
        $consultaDomicilioLocalidad = "SELECT krt.IdTicket, krt.IdAlmacen, a.nombre_almacen, da.Calle, da.NoExterior, 
            da.NoInterior, da.Colonia, da.Ciudad, da.Estado, da.Pais, da.CodigoPostal, da.Delegacion 
            FROM `k_resurtidotoner` AS krt
            LEFT JOIN c_almacen AS a ON a.id_almacen = krt.IdAlmacen
            LEFT JOIN c_domicilio_almacen AS da ON da.IdAlmacen = a.id_almacen 
            WHERE IdTicket = $idTicket
            GROUP BY IdTicket;";
        $queryConsultaDomicilio = $catalogo->obtenerLista($consultaDomicilioLocalidad);
        while ($rs = mysql_fetch_array($queryConsultaDomicilio)) {
            $nombreCliente = "Almacén: " . $rs['nombre_almacen'];
            $nombreLocalidad = "";
            $domicilioCliente = $rs['Calle'] . ", " . $rs['NoExterior'] . ", No. Int: " . $rs['NoInterior'] . " , " . $rs['Colonia'] . ", " . $rs['Delegacion'] . ", " . $rs['Ciudad'] . ", " . $rs['CodigoPostal'];
        }
    }
    $consultaLocalidad = "SELECT c.ClaveCliente,c.NombreRazonSocial,c.IdTipoCliente,c.IdEstatusCobranza, cc.Nombre AS localidad,
    td.Nombre AS tdomicilio,d.Calle,d.Colonia,d.Delegacion, 
    (CASE WHEN !ISNULL(cc.ClaveZona) THEN cc.ClaveZona ELSE c.ClaveZona END) AS zona, d.NoExterior,
    d.NoInterior,d.Ciudad,d.CodigoPostal,d.Estado,
    (SELECT z.fk_id_gzona FROM c_zona z WHERE z.ClaveZona=cc.ClaveZona OR z.ClaveZona=c.ClaveZona LIMIT 1) AS ubicacion, 
    (SELECT GROUP_CONCAT(u.Nombre,' ',u.ApellidoPaterno,' ',u.ApellidoMaterno) FROM k_tfscliente tfsc,c_usuario u WHERE cc.ClaveCliente=tfsc.ClaveCliente AND cc.ClaveCliente=tfsc.ClaveCliente AND u.IdUsuario=tfsc.IdUsuario GROUP BY tfsc.ClaveCliente) as tfs,
    (SELECT ct.Nombre FROM c_contacto ct WHERE ct.ClaveEspecialContacto=cc.ClaveCentroCosto ORDER BY ct.IdContacto DESC LIMIT 1) AS nombreContacto,
    (SELECT ct.Telefono FROM c_contacto ct WHERE ct.ClaveEspecialContacto=cc.ClaveCentroCosto ORDER BY ct.IdContacto DESC LIMIT 1) AS Telefono,
    (SELECT ct.Celular FROM c_contacto ct WHERE ct.ClaveEspecialContacto=cc.ClaveCentroCosto ORDER BY ct.IdContacto DESC LIMIT 1) AS Celular,
    (SELECT ct.CorreoElectronico FROM c_contacto ct WHERE ct.ClaveEspecialContacto=cc.ClaveCentroCosto ORDER BY ct.IdContacto DESC LIMIT 1) AS CorreoElectronico
    FROM c_centrocosto cc,c_domicilio d,c_cliente c,c_tipodomicilio td
    WHERE cc.ClaveCentroCosto=d.ClaveEspecialDomicilio AND cc.ClaveCliente=c.ClaveCliente AND td.IdTipoDomicilio=d.IdTipoDomicilio 
    AND cc.ClaveCentroCosto='$claveLocalidad'";
}
?>
<!DOCTYPE html>
<html lang="es">
    <head>     
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
        <title>Reporte <?php echo $nombre_objeto; ?></title>
        <link rel="icon" href="../resources/images/logos/ra4.png" type="image/x-icon"/>
        <style>
            .BorderTabla td,th {
                border: solid black;
                border-width:1px;
                border-spacing: 0px;
                border-collapse: collapse
            }
            img.imagen{width:150px; height:70px;}
            img.imagens{width:150px; height:70px;}
            @media print {
                * { margin: 0 !important; padding: 0 !important; }
                #controls, .footer, .footerarea{ display: none; }
                html, body {
                    /*changing width to 100% causes huge overflow and wrap*/
                    height:80%; 
                    background: #FFF; 
                    font-size: 9.5pt;
                }
                img.imagen{width:75px; height:30px;}
                img.imagens{width:75px; height:30px;}
                template { width: auto; left:0; top:0; }
                li { margin: 0 0 10px 20px !important;}
            }
        </style>
    </head>
    <body style="font-size:12px;font-family:Arial;height:50%;">
        <link rel="stylesheet" href="../resources/css/genesis/jquery-ui-1.10.3.custom.css" type="text/css" media="screen" />
        <script src="../resources/js/jquery/jquery-1.11.3.min.js"></script>
        <script src="../resources/js/jquery/jquery-ui.min.js"></script>
        <script type="text/javascript" language="javascript" src="../resources/js/paginas/reporte_ticket.js"></script>
        <a href=javascript:window.print(); style="margin-left: 85%;">Imprimir</a> 
        <br/><br/>
<?php
if ($tipoReporte != "15") {
    foreach ($series as $key => $value) {
        echo "<input type='hidden' id='serie_$key' name='serie_$key' value='$value'/>";
    }
}
echo "<input type='hidden' id='numero_series' name='numero_series' value='" . count($series) . "'/>";
echo "<input type='hidden' id='id_ticket' name='id_ticket' value='$idTicket'/>";
?>

        <table style="width: 100%">
            <tr>                
                <td style="width: 50%"><img src="../<?php echo $nombreLogo; ?>"/></td>
                <td style="width: 10%"></td>
                <td style="width: 45%;font-size:12px;font-family:Arial;">
<?php echo $domicilio; ?>
                </td>
            </tr>
            <tr>
                <td align='center' colspan="3" style="font-size:14px;font-family:Arial;"><b><?php echo $orden; ?></b><br/><br/></td>
            </tr>           
            <tr>
                <td style="width: 50%;font-size:12px;font-family:Arial;">
<?php echo $nombreCliente . " / " . $nombreLocalidad; ?><br/>
<?php echo $domicilioCliente; ?>
                </td>
                <td rowspan="2">
                    <table>
                        <tr><td></td><td></td><td><b>No. de <?php echo $nombre_objeto; ?>:</b><br/></td></tr>
                    </table>
                    <table class="BorderTabla" style="margin-left: 15px;">
                        <thead style="background-color: grey;">
                            <tr>
<?php
if ($ticketCliente != "")
    echo "<th align='center' style='font-size:12px;font-family:Arial;'>Cliente</th>";
?>
                                <?php
                                if ($ticketDistribucion != "")
                                    echo "<th align='center' style='font-size:12px;font-family:Arial;'>Distibución</th>";
                                ?>
                                <th align='center' style='font-size:12px;font-family:Arial;'><?php echo $_SESSION['nombreEmpresa']; ?></th>
                            </tr>
                        </thead>
                        <tbody style="background-color: #D3D6FF">
                            <tr>
<?php
if ($ticketCliente != "")
    echo "<td align='center' style='font-size:12px;font-family:Arial;'>$ticketCliente</td>";
?>
                                <?php
                                if ($ticketDistribucion != "")
                                    echo "<td align='center' style='font-size:12px;font-family:Arial;'>$ticketDistribucion</td>";
                                ?>
                                <td style="color: red;font-size:12px;font-family:Arial;">
                                <?php
                                echo $idTicket;
                                ?>
                                </td>                                
                            </tr>
                        </tbody>                        
                    </table>                     
                </td>        
                <td><br/><?php echo "<div style='margin-left: 45%;' id=\"div_ticket\" ></div>"; ?></td>
            </tr>
            <tr>
                <td  style="width: 45%;font-size:12px;font-family:Arial;" colspan="2"></td>
                <td align='center' style="width: 45%"></td>               
            </tr>
        </table>
        <table style="width: 90%;">
            <tr>
                <td style="width: 50%">
                    <table style="width: 100%">
                        <tr>
                            <td style=font-size:12px;font-family:Arial;><b>Contacto:</b>
                                <div style="display: inline; font-size:12px;font-family:Arial;"><?php echo $contacto; ?></div>
                            </td>                            
                        </tr>
                    </table>
                    <table style="width: 100%">
                        <tr>
<?php
if ($telefono1 != "")
    echo "<td style='width: 25%'><b>Teléfono 1:</b> $telefono1</td>";
else
    echo "<td style='width: 25%;font-size:12px;font-family:Arial;'></td>";
if ($extencion1 != "")
    echo "<td style='width: 25%;font-size:12px;font-family:Arial;'> <b>Ext 1: </b>$extencion1</td>";
else
    echo "<td style='width: 25%;font-size:12px;font-family:Arial;'></td>";
if ($telefono2 != "")
    echo "<td style='width: 25%;font-size:12px;font-family:Arial;'><b>Teléfono 2: </b>$telefono2;</td>";
else
    echo "<td style='width: 25%;font-size:12px;font-family:Arial;'></td>";
if ($extencion2 != "")
    echo "<td style='width: 25%;font-size:12px;font-family:Arial;'><b>Ext 2</b>:$extencion2</td>";
else
    echo "<td style='width: 25%;font-size:12px;font-family:Arial;'></td>";
?>
                        </tr>
                    </table>
                    <table style="width: 100%">
                        <tr>
<?php
if ($celular != "")
    echo "<td style='width: 25%;font-size:12px;font-family:Arial;'><b>Celular: </b>$celular</td>";
/* else
  echo "<td style='width: 25%;font-size:12px;font-family:Arial;'></td>"; */
if ($correo != "")
    echo "<td style='width: 25%;font-size:12px;font-family:Arial;'><b>Correo: </b>$correo</td></tr>";
/* else
  echo "<td style='width: 25%;font-size:12px;font-family:Arial;'></td>"; */
?>
                    </table>
                </td>
                <td style="width: 50%">
<?php
echo "<table style='width: 100%'><tr>";
echo "<td style='font-size:12px;font-family:Arial;'><b></b>";
//echo $series[0];
echo "</td>";
echo "</tr><tr>";
echo "<td colspan='2' style='font-size:12px;font-family:Arial;'><b>Fecha en que se detono el resurtido: </b> $fechaTicket $horaTicket</td>";
if ((int) $aceptado == 1) {
    echo "<td colspan='2' style='font-size:12px;font-family:Arial;'><b>Fecha en que se respondio la solicitud: </b> $fechaValidado</td>";
} else {
    echo "<td colspan='2' style='font-size:12px;font-family:Arial;'><b>Aún no se responde la solicitud</b></td>";
}
echo "</tr><tr>";

echo "</tr><tr>";
if ($nivelNegro != "")
    echo "<td style='font-size:12px;font-family:Arial;'><b>Nivel negro:</b>$nivelNegro </td>";
else
    echo "<td><b></b></td>";
if ($nivelCia != "")
    echo "<td style='font-size:12px;font-family:Arial;'><b>Nivel cian:</b> $nivelCia</td>";
else
    echo "<td><b></b></td>";
if ($nivelMagenta != "")
    echo "<td style='font-size:12px;font-family:Arial;'><b>Nivel magenta:</b> $nivelMagenta</td>";
else
    echo "<td><b></b></td>";
if ($nivelAmarillo != "")
    echo "<td style='font-size:12px;font-family:Arial;'><b>Nivel amarillo:</b> $nivelAmarillo</td>";
else
    echo "<td><b></b></td>";
echo "</tr></table>";
?>
                </td>
            </tr>
        </table>
<?php
$estatus_mostrado = array();
?>
        <fieldset>
            <legend><b>Solicitado</b></legend>
        <?php
        if ($permisos_grid2->tienePermisoEspecial($_SESSION['idUsuario'], 35) && $EstadoTicketDatos != 2 && $EstadoTicketDatos != 4) {
            echo "<table style='width: 100%;height: 40px;' class='BorderTabla'>";
        } else {
            echo "<table style='width: 80%;height: 40px;' class='BorderTabla'>";
        }
        ?>
            <colgroup span="2"></colgroup>
            <colgroup span="3"></colgroup>
            <colgroup span="4"></colgroup>
            <colgroup span="4"></colgroup>
            <?php if ($permisos_grid2->tienePermisoEspecial($_SESSION['idUsuario'], 35) && $EstadoTicketDatos != 2 && $EstadoTicketDatos != 4) { ?>
                <colgroup span="2"></colgroup>
            <?php } ?>
            <tr style="background-color: grey;">
                <th align="center" colspan="2" scope="colgroup"></th>
                <th align="center" colspan="3" scope="colgroup">Surtido anterior</th>
                <th align="center" colspan="4" scope="colgroup">Ticket <?php echo $idTicket; ?></th>
                <th align="center" colspan="4" scope="colgroup">Mini almacén</th>
                <?php if ($permisos_grid2->tienePermisoEspecial($_SESSION['idUsuario'], 35) && $EstadoTicketDatos != 2 && $EstadoTicketDatos != 4) { ?>
                    <th align="center" colspan="2" scope="colgroup">Almacén</th>
                <?php } ?>
            </tr>
            <tr style="background-color: grey;">
                <th align="center" scope="col">Detona</th>
                <th align="center" scope="col">Modelo</th>
                <th align="center" scope="col">Ticket</th>
                <th align="center" scope="col">Fecha</th>
                <th align="center" scope="col">Cantidad</th>
                <th align="center" scope="col">Solicitado</th>
                <th align="center" scope="col">Surtido</th>
                <th align="center" scope="col">Por surtir</th>
                <th align="center" scope="col">Autorizados</th>
                <th align="center" scope="col">Minimo</th>
                <th align="center" scope="col">Máximo</th>
                <th align="center" scope="col">Existencia actual</th>
                <th align="center" scope="col">Equipos que lo usan</th>
                <?php if ($permisos_grid2->tienePermisoEspecial($_SESSION['idUsuario'], 35) && $EstadoTicketDatos != 2 && $EstadoTicketDatos != 4) { ?>
                    <th align="center" scope="col">Existencia</th>
                    <th align="center" scope="col">Precio USD</th>
                <?php } ?>
            </tr>
            <tbody style="background-color: #D3D6FF;">
                <?php
                while ($rs = mysql_fetch_array($query)) {
                    echo "<tr>";
                    if ((int) $rs['mail'] == 1) {
                        if (!isset($rs['minimo'])) {
                            $consultaCompatibles = "SELECT act.NoParte, act.CantidadMinima AS minimo, act.CantidadMaxima AS maxima, 
                                        act.cantidad_existente AS existencias, act.NoEquiposBeneficiados AS equipos
                                        FROM k_equipocomponentecompatible AS kecc 
                                        LEFT JOIN k_equipocomponentecompatible AS kecc2 ON kecc2.NoParteEquipo = kecc.NoParteEquipo
                                        INNER JOIN c_componente AS c ON c.NoParte = kecc2.NoParteComponente AND c.IdTipoComponente = 2 
                                        AND c.IdColor = " . $rs['IdColor'] . " AND c.Activo = 1
                                        INNER JOIN k_almacencomponenteticket AS act ON act.NoParte = kecc2.NoParteComponente AND act.IdTicket = $idTicket
                                        WHERE kecc.NoParteComponente = '" . $rs['NoComponenteToner'] . "'
                                        GROUP BY c.NoParte;";

                            $resultCompatible = $catalogo->obtenerLista($consultaCompatibles);
                            if ($rsCompatible = mysql_fetch_array($resultCompatible)) {
                                $compatible = true;
                                $tonerCompatible = $rsCompatible['NoParte'];
                                $minimoCompatible = $rsCompatible['minimo'];
                                $maximoCompatible = $rsCompatible['maxima'];
                                $existenciasCompatible = $rsCompatible['existencias'];
                                $equiposBeneficiadosCompatible = $rsCompatible['equipos'];
                            }
                        }
                    }
                    //$comaptible = false;
                    echo "<td align='center'></td>";
                    //if($compatible){
                    if (false) {
                        echo "<td align='center'>" . $rs['ModeloT'] . " ($tonerCompatible)*</td>";
                    } else {
                        echo "<td align='center'>" . $rs['ModeloT'] . " (" . $rs['NoComponenteToner'] . ")</td>";
                    }
                    $idTicketAnteriorComponente = $resurtidoToner->ticketAnteriorResurtidoPorComponenteYAlmacen($rs['NoComponenteToner'], $idTicket, $rs['IdAlmacen']);
                    echo "<td align='center'>" . $idTicketAnteriorComponente . "</td>";
                    if (isset($idTicketAnteriorComponente) && $idTicketAnteriorComponente != "") {
                        $queryFechaCantidadTicket = "SELECT t.FechaHora, rt.Cantidadresurtido FROM c_ticket t
                                        LEFT JOIN k_resurtidotoner AS rt ON rt.IdTicket = t.IdTicket 
                                        WHERE t.IdTicket = $idTicketAnteriorComponente";
                        $resultTicketAnterior = $catalogo->obtenerLista($queryFechaCantidadTicket);
                        if ($rsTicketAnterior = mysql_fetch_array($resultTicketAnterior)) {
                            echo "<td align='center'>" . $rsTicketAnterior['FechaHora'] . "</td>";
                            echo "<td align='center'>" . $rsTicketAnterior['Cantidadresurtido'] . "</td>";
                        } else {
                            echo "<td align='center'></td>";
                            echo "<td align='center'></td>";
                        }
                    } else {
                        echo "<td align='center'></td>";
                        echo "<td align='center'></td>";
                    }
                    echo "<td align='center'>" . $rs['CantidadSolicitada'] . "</td>";
                    if ((int) $rs['mail'] == 1) {
                        if (isset($rs['Cantidad']) && $rs['Cantidad'] != "") {
                            echo"<td class='borde centrado'>" . $rs['Cantidad'] . "</td>";
                            $diferencia = (int) $rs['CantidadSolicitada'] - (int) $rs['Cantidad'];
                            echo"<td class='borde centrado'>" . $diferencia . "</td>";
                            echo "<td align='center'></td>";
                        } else {
                            echo "<td class='borde centrado'>0</td>";
                            echo "<td class='borde centrado'>" . $rs['CantidadSolicitada'] . "</td>";
                            echo "<td class='borde centrado'></td>";
                        }
                    } else {
                        echo "<td class='borde centrado'>0</td>";
                        echo "<td class='borde centrado'>" . $rs['CantidadSolicitada'] . "</td>";
                        echo "<td class='borde centrado'>Sin autorizar</td>";
                    }
                    if ((int) $rs['mail'] == 1) {
                        if (isset($rs['minimo'])) {
                            echo "<td class='borde centrado'>" . $rs['minimo'] . "</td>";
                        } else {
                            if (isset($minimoCompatible) && $minimoCompatible != "") {
                                echo "<td class='borde centrado'>$minimoCompatible</td>";
                            } else {
                                echo "<td class='borde centrado'>N/A</td>";
                            }
                        }
                        if (isset($rs['maximo'])) {
                            echo "<td class='borde centrado'>" . $rs['maximo'] . "</td>";
                        } else {
                            if (isset($maximoCompatible) && $maximoCompatible != "") {
                                echo "<td class='borde centrado'>$maximoCompatible</td>";
                            } else {
                                echo "<td class='borde centrado'>N/A</td>";
                            }
                        }
                    } else {
                        if (isset($rs['minimoA'])) {
                            echo "<td class='borde centrado'>" . $rs['minimoA'] . "</td>";
                        } else {
                            echo "<td class='borde centrado'>N/A</td>";
                        }
                        if (isset($rs['maximoA'])) {
                            echo "<td class='borde centrado'>" . $rs['maximoA'] . "</td>";
                        } else {
                            echo "<td class='borde centrado'>N/A</td>";
                        }
                    }
                    if (isset($rs['existencia'])) {
                        echo "<td class='borde centrado'>" . $rs['existencia'] . "</td>";
                    } else {
                        if (isset($existenciasCompatible) && $existenciasCompatible != "") {
                            echo "<td class='borde centrado'>$existenciasCompatible</td>";
                        } else {
                            echo "<td class='borde centrado'>N/A</td>";
                        }
                    }
                    if (isset($rs['equiposUso'])) {
                        echo "<td class='borde centrado'>" . $rs['equiposUso'] . "</td>";
                    } else {
                        if (isset($equiposBeneficiadosCompatible) && $equiposBeneficiadosCompatible != "") {
                            echo "<td class='borde centrado'>$equiposBeneficiadosCompatible</td>";
                        } else {
                            echo "<td class='borde centrado'>N/A</td>";
                        }
                    }
                    if ($permisos_grid2->tienePermisoEspecial($_SESSION['idUsuario'], 35) && $EstadoTicketDatos != 2 && $EstadoTicketDatos != 4) {
                        echo "<td class='borde centrado'>" . $rs['existenciaG'] . "</td>";
                        echo "<td class='borde centrado'>" . $rs['precio'] . "</td>";
                    }
                    echo "<tr>";
                    if (isset($rs['Cantidad']) && $rs['Cantidad']) {
                        $arrayCantidadComponente['' . $rs['NoComponenteToner']] = $rs['Cantidad'];
                        $arrayComponenteModelo['' . $rs['NoComponenteToner']] = $rs['ModeloT'];
                        $arrayFechaComponente['' . $rs['NoComponenteToner']] = $rs['fechaComponente'];
                    }
                    $arrayNoTicketComponente['' . $rs['NoComponenteToner']] = $idTicketAnteriorComponente;
                    $arrayComponenteModelo['' . $rs['NoComponenteToner']] = $rs['ModeloT'];
                    $arrayCantidadSolicitadaComponente['' . $rs['NoComponenteToner']] = (int) $rs['CantidadSolicitada'];
                    $fecha = $rs['Fecha'];
                    $almacen = $rs['almacen'];
                    $idAlmacen = $rs['IdAlmacen'];
                    $cliente = $rs['cliente'];
                    $localidad = $rs['localidad'];
                    $claveLocalidad = $rs['ClaveCentroCosto'];
                    $val = true;
                    $claveCliente = $rs['ClaveCliente'];
                }
                $querySurtidosCompatibles = "SELECT c.Modelo, nr.Cantidad, '0' AS Solicitado, ag.cantidad_existencia, c.PrecioDolares, 
                            nr.NoParteComponente AS NoComponenteToner, rt.IdAlmacen, nt2.FechaCreacion AS fechaComponente
                            FROM k_nota_refaccion nr
                            LEFT JOIN c_notaticket nt2 ON nt2.IdNotaTicket = nr.IdNotaTicket
                            INNER JOIN c_componente c ON c.NoParte = nr.NoParteComponente
                            LEFT JOIN k_almacencomponente AS ag ON ag.NoParte = nr.NoParteComponente AND ag.id_almacen = 6
                            LEFT JOIN k_resurtidotoner AS rt ON rt.IdTicket = nt2.IdTicket
                            WHERE nr.IdNotaTicket IN (SELECT nt.IdNotaTicket FROM c_notaticket nt WHERE nt.IdTicket = $idTicket) 
                            AND nt2.IdEstatusAtencion = 66 AND nr.NoParteComponente NOT IN(SELECT rt.NoComponenteToner FROM k_resurtidotoner rt WHERE rt.IdTicket = $idTicket)
                            GROUP BY NoParteComponente";
                $queryasdasd = $catalogo->obtenerLista($querySurtidosCompatibles);
                while ($rs = mysql_fetch_array($queryasdasd)) {
                    $compatibleNuevo = true;
                    echo "<tr>";
                    echo "<td align='center'></td>";
                    echo "<td align='center'>" . $rs['Modelo'] . "</td>";
                    $idTicketAnteriorComponente = $resurtidoToner->ticketAnteriorResurtidoPorComponenteYAlmacen($rs['nr.NoParteComponente'], $idTicket, $rs['IdAlmacen']);
                    echo "<td align='center'>" . $idTicketAnteriorComponente . "</td>";
                    if (isset($idTicketAnteriorComponente) && $idTicketAnteriorComponente != "") {
                        $queryFechaCantidadTicket = "SELECT t.FechaHora, rt.Cantidadresurtido FROM c_ticket t
                                        LEFT JOIN k_resurtidotoner AS rt ON rt.IdTicket = t.IdTicket 
                                        WHERE t.IdTicket = $idTicketAnteriorComponente";
                        $resultTicketAnterior = $catalogo->obtenerLista($queryFechaCantidadTicket);
                        if ($rsTicketAnterior = mysql_fetch_array($resultTicketAnterior)) {
                            echo "<td align='center'>" . $rsTicketAnterior['FechaHora'] . "</td>";
                            echo "<td align='center'>" . $rsTicketAnterior['Cantidadresurtido'] . "</td>";
                        } else {
                            echo "<td align='center'></td>";
                            echo "<td align='center'></td>";
                        }
                    } else {
                        echo "<td align='center'></td>";
                        echo "<td align='center'></td>";
                    }
                    echo "<td align='center'>0</td>";
                    echo "<td class='borde centrado'>" . $rs['Cantidad'] . "</td>";
                    echo "<td class='borde centrado'>0</td>";
                    echo "<td class='borde centrado'></td>";
                    echo "<td class='borde centrado'>0</td>";
                    echo "<td class='borde centrado'>0</td>";
                    echo "<td class='borde centrado'>" . $rs['Cantidad'] . "</td>";
                    echo "<td class='borde centrado'>1</td>";
                    if ($permisos_grid2->tienePermisoEspecial($_SESSION['idUsuario'], 35) && $EstadoTicketDatos != 2 && $EstadoTicketDatos != 4) {
                        echo "<td class='borde centrado'>" . $rs['cantidad_existencia'] . "</td>";
                        echo "<td class='borde centrado'>" . $rs['PrecioDolares'] . "</td>";
                    }
                    echo "<tr>";

                    if (isset($rs['Cantidad']) && $rs['Cantidad']) {
                        $arrayCantidadComponente['' . $rs['NoComponenteToner']] = $rs['Cantidad'];
                        $arrayComponenteModelo['' . $rs['NoComponenteToner']] = $rs['Modelo'];
                        $arrayFechaComponente['' . $rs['NoComponenteToner']] = $rs['fechaComponente'];
                    }
                }
                ?>
            </tbody>
        </table>
        <?php
        if ($compatible) {
            echo "<b>* La información de mínimos, máximos, existencia, equipos beneficiados es del modelo entre parentésis</b>";
        }
        if ($compatibleNuevo) {
            echo "<br/><b>* En este almacén de cliente se registro un nuevo componente que es compatible con otro modelo ya existente en el almacén, por eso sus mínimos y maximos actuales están en 0</b>";
        }
        ?>
    </fieldset>
    <br/>
    <?php
    $ticketAnterior = 0;
    $fechaAnterior = "";
    /* $ticketAnteriorConsulta = "SELECT t.IdTicket AS ticketAnterior, t.FechaHora FROM c_ticket t
      WHERE t.IdTicket = (SELECT MAX(t2.IdTicket) FROM c_ticket t2
      WHERE t2.IdTicket < $idTicket AND t2.Resurtido = 1 AND t2.ClaveCliente = '$claveCliente')"; */
    $ticketAnteriorConsulta = "SELECT t.IdTicket AS ticketAnterior, t.FechaHora FROM c_ticket t
                WHERE t.IdTicket = (
                SELECT MAX(t2.IdTicket) 
                FROM c_ticket t2 
                LEFT JOIN k_resurtidotoner AS kr ON kr.IdTicket = t2.IdTicket
                WHERE t2.IdTicket < $idTicket AND t2.Resurtido = 1 AND kr.IdAlmacen = $idAlmacen);";
    $resultTicketAnterior = $catalogo->obtenerLista($ticketAnteriorConsulta);
    if ($rsTicketAnterior = mysql_fetch_array($resultTicketAnterior)) {
        $ticketAnterior = $rsTicketAnterior['ticketAnterior'];
        $fechaAnterior = $rsTicketAnterior['FechaHora'];
    }
    /* if($ticketAnterior != 0){
      echo "Para consultar el ticket de resurtido anterior de este almacén haga clic <a href='reporte_toner_ticket.php?idTicket=$ticketAnterior'  target='_blank'>"
      . " <img src='../resources/images/icono_impresora.png' width='20' height='20'></a>";
      } */
    //Vamos a mostrar los cambios de máximos y mínimos si es que hubo.
    $queryCambiosMinimosMaximos = "SELECT cma.*,c.Modelo FROM k_cambiosminialmacen cma 
                    LEFT JOIN c_componente AS c ON c.NoParte = cma.NoParte 
                    WHERE cma.IdAlmacen = $idAlmacen AND cma.Fecha < '$fecha' AND cma.Fecha > '$fechaAnterior' AND 
                    cma.NoParte IN (SELECT c2.NoParte FROM c_componente c2 INNER JOIN k_resurtidotoner AS rt2 ON rt2.NoComponenteToner = c2.NoParte WHERE rt2.IdTicket = $idTicket)";
    $resultCambios = $catalogo->obtenerLista($queryCambiosMinimosMaximos);
    if (mysql_num_rows($resultCambios) > 0) {
        echo "<fieldset>
                        <legend><b>Cambios en máximos y mínimos</b></legend>";
        echo "<table style='width: 60%;height: 40px;' class='BorderTabla'>";
        echo "<thead style='background-color: grey;'>";
        echo "<tr>";
        echo "<th>Modelo</th>";
        echo "<th>Fecha</th>";
        echo "<th>Min Anterior</th>";
        echo "<th>Max Anterior</th>";
        echo "<th>Min</th>";
        echo "<th>Max</th>";
        echo "</tr>";
        echo "</thead>";
        echo "<tbody style='background-color: #D3D6FF;'>";
        while ($rsCambios = mysql_fetch_array($resultCambios)) {
            echo "<tr>";
            echo "<td>" . $rsCambios['Modelo'] . "</td>";
            echo "<td>" . $rsCambios['Fecha'] . "</td>";
            echo "<td>" . $rsCambios['MinimoAnterior'] . "</td>";
            echo "<td>" . $rsCambios['MaximoAnterior'] . "</td>";
            echo "<td>" . $rsCambios['MinimoNuevo'] . "</td>";
            echo "<td>" . $rsCambios['MaximoNuevo'] . "</td>";
            echo "</tr>";
        }
        echo "</tbody>";
        echo "</table>";
    }
    ?>
    <fieldset>
        <legend><b>Surtido de este ticket</b></legend> 

        <?php
        $numeroSurtido = "";
        $consulta = "SELECT 
                    SUM(nr.Cantidad) AS Cantidad, c.Modelo, c.NoParte, DATE_FORMAT(nt.FechaHora, '%Y-%m-%d %H:%i') AS `FechaHoraFormat`,
                    (CASE WHEN !ISNULL(nt.NumeroSurtido) THEN nt.NumeroSurtido ELSE 0 END) AS NumeroSurtido
                    FROM `c_notaticket` AS nt
                    LEFT JOIN k_nota_refaccion AS nr ON nr.IdNotaTicket = nt.IdNotaTicket
                    LEFT JOIN c_estado AS e ON e.IdEstado = nt.IdEstatusAtencion
                    LEFT JOIN c_componente AS c ON c.NoParte = nr.NoParteComponente
                    WHERE nt.IdTicket = $idTicket AND nt.IdEstatusAtencion = 66 AND !ISNULL(nr.IdNotaTicket)
                    GROUP BY c.NoParte, FechaHoraFormat
                    ORDER BY NumeroSurtido, FechaHoraFormat, Modelo;";
        $result = $catalogo->obtenerLista($consulta);
        if (mysql_num_rows($result) > 0) {
            $contador = 1;
            while ($rs = mysql_fetch_array($result)) {
                if ($numeroSurtido != $rs['NumeroSurtido']) {//Se abre una tabla nueva
                    if ($numeroSurtido != "") {//Se cierra la ultima tabla abierta
                        echo '</tbody>
                                </table><br/><br/>';
                    }
                    $numeroSurtido = $rs['NumeroSurtido'];
                    echo 'Surtido '.$contador.': <br/><table style="width: 40%;height: 40px;" class="BorderTabla">
                            <thead style="background-color: grey;">
                                <tr>
                                    <th align="center" >Cantidad</th>
                                    <th align="center" >Modelo</th>
                                    <th align="center" >No. de Parte</th>
                                    <th align="center" >Fecha</th>
                                </tr>
                            </thead>
                            <tbody style="background-color: #D3D6FF;">';
                    $contador++;
                }
                echo "<tr>";
                echo "<td>" . $rs['Cantidad'] . "</td>";
                echo "<td>" . $rs['Modelo'] . "</td>";
                echo "<td>" . $rs['NoParte'] . "</td>";
                echo "<td>" . $rs['FechaHoraFormat'] . "</td>";
                echo "</tr>";
            }
            echo '</tbody>
                    </table>';
        }
        ?>

    </fieldset>
    <fieldset>
        <legend><b>Firmas</b></legend> 
        <table style="width:  100%" >
            <tr></tr>
            <tr><td align="center" style="width: 33%"><br/><br/><br/><HR width=250px > </td><td align="center" style="width: 33%"><br/><br/><br/><HR width=250px ></td><td align="center" style="width: 33%"><br/><br/><br/><HR width=250px ></td></tr>
            <tr><td style="width: 33%" align="center">Autoriza Surtido</td><td style="width: 33%" align="center">Entrega almacén</td><td style="width: 33%" align="center">Recibe <?php echo $almacen; ?></td></tr>
        </table>
    </fieldset>
    <div style="page-break-after: always;"></div>
    <table style="width: 100%">
        <tr>                
            <td style="width: 50%"><img src="../<?php echo $nombreLogo; ?>"/></td>
            <td style="width: 10%"></td>
            <td style="width: 45%;font-size:12px;font-family:Arial;">
                <?php echo $domicilio; ?>
            </td>
        </tr>
        <tr>
            <td align='center' colspan="3" style="font-size:14px;font-family:Arial;"><b><?php echo $orden; ?></b><br/><br/></td>
        </tr>           
        <tr>
            <td style="width: 50%;font-size:12px;font-family:Arial;">
                <?php echo $nombreCliente . " / " . $nombreLocalidad; ?><br/>
                <?php echo $domicilioCliente; ?>
            </td>
            <td rowspan="2">
                <table>
                    <tr><td></td><td></td><td><b>No. de <?php echo $nombre_objeto; ?>:</b><br/></td></tr>
                </table>
                <table class="BorderTabla" style="margin-left: 15px;">
                    <thead style="background-color: grey;">
                        <tr>
                            <?php
                            if ($ticketCliente != "")
                                echo "<th align='center' style='font-size:12px;font-family:Arial;'>Cliente</th>";
                            ?>
                            <?php
                            if ($ticketDistribucion != "")
                                echo "<th align='center' style='font-size:12px;font-family:Arial;'>Distibución</th>";
                            ?>
                            <th align='center' style='font-size:12px;font-family:Arial;'><?php echo $_SESSION['nombreEmpresa']; ?></th>
                        </tr>
                    </thead>
                    <tbody style="background-color: #D3D6FF">
                        <tr>
                            <?php
                            if ($ticketCliente != "")
                                echo "<td align='center' style='font-size:12px;font-family:Arial;'>$ticketCliente</td>";
                            ?>
                            <?php
                            if ($ticketDistribucion != "")
                                echo "<td align='center' style='font-size:12px;font-family:Arial;'>$ticketDistribucion</td>";
                            ?>
                            <td style="color: red;font-size:12px;font-family:Arial;">
                                <?php
                                echo $idTicket;
                                ?>
                            </td>                                
                        </tr>
                    </tbody>                        
                </table>                     
            </td>        
            <td><br/><?php echo "<div style='margin-left: 45%;' id=\"div_ticket\" ></div>"; ?></td>
        </tr>
        <tr>
            <td  style="width: 45%;font-size:12px;font-family:Arial;" colspan="2"></td>
            <td align='center' style="width: 45%"></td>               
        </tr>
    </table>
    <fieldset>
        <legend><b>Cambios de Toner que detonaron el tiquet de resurtido: <?php echo $idTicket; ?></b></legend> 
        <table style="width: 40%;height: 40px;" class="BorderTabla">
            <thead style="background-color: grey;">
                <tr>
                    <th>Ticket</th> 
                    <th>Fecha</th>
                    <th>Equipo</th>
                    <th>Serie</th>               
                    <th>NoParte</th>
                    <th>Modelo</th>
                    <th>Contador Actual</th>
                    <th>Contador Anterior</th>
                    <th>Impresiones</th>
                    <th>Rendimiento</th>
                    <th>Localidad</th>
                </tr>
            </thead>
            <tbody style="background-color: #D3D6FF;">
                <?php
                $consultaTickets = "SELECT lt.ClvEsp_Equipo AS NoSerie, nr.NoParteComponente AS NoParte, t.FechaHora AS Fecha,
                        c.Modelo AS Modelo, c.Descripcion AS Descripcion, nr.Cantidad AS Cantidad, t.IdTicket AS NoTicket,
                        a.nombre_almacen AS Almacen, t.NombreCliente AS Cliente, t.NombreCentroCosto AS Localidad,
                        lt.ContadorBN AS ContadorBN, lt.ContadorCL AS ContadorCL, lt.ModeloEquipo AS Equipo,
                        (lt.ContadorBN - lt2.ContadorBN) AS Impresiones, c.Rendimiento AS Rendimiento,
                        lt2.ContadorBN AS ContadorBNAnterior, lt2.ContadorCL AS ContadorCLAnterior, lt2.Fecha AS FechaAnterior
                        FROM c_ticket t 
                        INNER JOIN c_notaticket AS nt ON nt.IdTicket = t.IdTicket
                        LEFT JOIN c_lecturasticket AS lt ON fk_idticket = t.IdTicket
                        LEFT JOIN k_nota_refaccion AS nr ON nt.IdNotaTicket = nr.IdNotaTicket
                        LEFT JOIN c_almacen AS a ON a.id_almacen = nr.IdAlmacen
                        LEFT JOIN c_componente AS c ON c.NoParte = nr.NoParteComponente
                        LEFT JOIN c_ticket AS ta ON ta.IdTicket = (SELECT MAX(t2.IdTicket) FROM c_ticket t2 
                            WHERE t2.IdTicket < $idTicket AND t2.Resurtido = 1 AND t2.ClaveCliente = '$claveCliente' AND t2.EstadoDeTicket = 2)
                        LEFT JOIN c_mailpedidotoner AS mpt ON mpt.IdTicket = ta.IdTicket
                        LEFT JOIN c_lecturasticket AS lt2 ON lt2.id_lecturaticket = 
                            (SELECT MAX(lta.id_lecturaticket) FROM c_lecturasticket lta 
                            LEFT JOIN c_ticket AS ta ON lta.fk_idticket = ta.IdTicket
                            INNER JOIN c_notaticket nt3 ON nt3.IdTicket=ta.IdTicket 
                            INNER JOIN k_nota_refaccion nr3 ON nt3.IdNotaTicket=nr3.IdNotaTicket 
                            INNER JOIN c_componente c2 ON c2.NoParte=nr3.NoParteComponente
                            WHERE lta.ClvEsp_Equipo = lt.ClvEsp_Equipo AND ta.Resurtido = 0 AND lta.id_lecturaticket <  lt.id_lecturaticket AND c2.IdColor=c.IdColor)
                        WHERE t.TipoReporte = 15 AND t.Resurtido = 0 AND t.FechaHora < '$fecha' AND a.id_almacen = " . $idAlmacen
                        . " AND c.NoParte IN (SELECT c2.NoParte FROM c_componente c2 INNER JOIN k_resurtidotoner AS rt2 ON rt2.NoComponenteToner = c2.NoParte WHERE rt2.IdTicket = $idTicket)
                        AND t.FechaHora > mpt.FechaUltimaModificacion 
                        GROUP BY t.IdTicket ORDER BY nr.NoParteComponente,t.IdTicket";
                $resultTickets = $catalogo->obtenerLista($consultaTickets);

                while ($rsTickets = mysql_fetch_array($resultTickets)) {
                    //Calculamos el porcentaje del rendimiento
                    $rendimientoTotal = 0;
                    if (isset($rsTickets['Rendimiento']) && $rsTickets['Rendimiento'] != "") {
                        $rendimientoTotal = (int) $rsTickets['Rendimiento'];
                    }
                    $impresiones = $rsTickets['Impresiones'];
                    $porcentajeRendimiento = 0;
                    if ($rendimientoTotal != 0) {
                        $porcentajeRendimiento = ($impresiones * 100) / $rendimientoTotal;
                    }

                    echo "<tr>";
                    echo "<td>" . $rsTickets['NoTicket'] . "</td>";
                    echo "<td>" . $rsTickets['Fecha'] . "</td>";
                    echo "<td>" . $rsTickets['Equipo'] . "</td>";
                    echo "<td>" . $rsTickets['NoSerie'] . "</td>";
                    echo "<td>" . $rsTickets['NoParte'] . "</td>";
                    echo "<td>" . $rsTickets['Modelo'] . "</td>";
                    echo "<td>" . $rsTickets['ContadorBN'] . "</td>";
                    echo "<td>" . $rsTickets['FechaAnterior'] . "<br/>" . $rsTickets['ContadorBNAnterior'] . "</td>";
                    echo "<td>" . $rsTickets['Impresiones'] . "</td>";
                    if ($porcentajeRendimiento == 0) {
                        if (!isset($rsTickets['ContadorBNAnterior']) || $rsTickets['ContadorBNAnterior'] == "") {
                            echo "<td class='borde centrado'>Sin rendimiento por lectura anterior</td>";
                        } else {
                            echo "<td class='borde centrado'>Sin rendimiento</td>";
                        }
                    } else {
                        if ($porcentajeRendimiento < 0) {
                            echo "<td class='borde centrado'> 0 % de <br/>" . $rsTickets['Rendimiento'] . "</td>";
                        } else {
                            echo "<td class='borde centrado'> " . number_format($porcentajeRendimiento) . "% de <br/>" . $rsTickets['Rendimiento'] . "</td>";
                        }
                    }
                    echo "<td class='borde centrado'>" . $rsTickets['Localidad'] . "</td>";
                    echo "</tr>";
                    $arrayCantidadSolicitadaComponente['' . $rsTickets['NoParte']] --;
                }
                ?>
            </tbody>
        </table>
    </fieldset>
<?php
$primeraVez = true;
echo "<fieldset>";
echo "<legend><b>Inconsistencias entre la cantidad solicitada y los cambios de tóner</b></legend>";
foreach ($arrayCantidadSolicitadaComponente as $key => $value) {
    if ($value != 0) {
        echo "Para el modelo: " . $arrayComponenteModelo[$key] . " el ticket anterior de resurtido es: ";
        if ($arrayNoTicketComponente[$key] == "") {
            echo "No hay ticket anterior de resurtido<br/>";
        } else {
            echo " <a href='" . $url . "reporte_toner_ticket.php?idTicket=" . $arrayNoTicketComponente[$key] . "'  target='_blank'>" . $arrayNoTicketComponente[$key] . "</a><br/>";
        }
    }
}
$consultaMovimientosComponentes = "SELECT mc.CantidadMovimiento, c.Modelo, mc.Fecha,
                    (CASE WHEN !ISNULL(mc.IdAlmacenAnterior) THEN 'Salida' ELSE 'Entrada' END) AS Tipo, mc.UsuarioCreacion
                    FROM movimiento_componente mc 
                    LEFT JOIN c_componente AS c ON c.NoParte = mc.NoParteComponente
                    WHERE (mc.IdAlmacenAnterior = $idAlmacen OR mc.IdAlmacenNuevo = $idAlmacen) 
                    AND mc.Fecha < '$fecha' AND mc.Fecha > '$fechaAnterior' AND mc.IdTicket IS NULL 
                    AND mc.NoParteComponente IN (SELECT c2.NoParte FROM c_componente c2 INNER JOIN k_resurtidotoner AS rt2 ON rt2.NoComponenteToner = c2.NoParte WHERE rt2.IdTicket = $idTicket) ";
$resultMovimientosComponente = $catalogo->obtenerLista($consultaMovimientosComponentes);
if (mysql_num_rows($resultMovimientosComponente)) {
    echo "<h5>Hubo cambios manuales en este almacen</h5>";
    echo "<table>";
    echo "<tr>";
    echo "<th>Modelo</th>";
    echo "<th>Fecha</th>";
    echo "<th>Tipo</th>";
    echo "<th>CantidadMovimiento</th>";
    echo "<th>Usuario de Modificación</th>";
    echo "</tr>";
    while ($rsMovimientosComponente = mysql_fetch_array($resultMovimientosComponente)) {
        echo "<tr>";
        echo "<td>" . $rsMovimientosComponente['Modelo'] . "</td>";
        echo "<td>" . $rsMovimientosComponente['Fecha'] . "</td>";
        echo "<td>" . $rsMovimientosComponente['Tipo'] . "</td>";
        echo "<td class='centrado'>" . $rsMovimientosComponente['CantidadMovimiento'] . "</td>";
        echo "<td>" . $rsMovimientosComponente['UsuarioCreacion'] . "</td>";
        echo "</tr>";
    }
    echo "</table>";
}
echo "</fieldset>";
?>
    <fieldset>
        <legend><b>Comentarios y Sugerencias</b></legend> 
        <table style="width: 100%;height: 150px">
            <tr><td></td></tr>
        </table>
    </fieldset>  
</body>
</html>

