<?php
session_start();
if (!isset($_SESSION['user']) || $_SESSION['user'] == "") {
    header("Location: ../../index.php");
}
include_once("../WEB-INF/Classes/Catalogo.class.php");
include_once("../WEB-INF/Classes/LecturaTicket.class.php");
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
$series = array();
$consulta = "SELECT 
    (SELECT CASE WHEN t.AreaAtencion = 2 THEN (SELECT group_concat(ClaveEspEquipo separator ', ') FROM `c_pedido` WHERE IdTicket = t.IdTicket) ELSE t.NoSerieEquipo END) AS NumSerie, 
    t.TipoReporte,t.ClaveCliente,t.ClaveCentroCosto,t.NombreCliente,t.NombreCentroCosto,t.NombreResp,t.EstadoDeTicket,
    t.Telefono1Resp,t.Telefono2Resp,t.Extension1Resp,t.Extension2Resp, t.CelularResp,t.CorreoEResp,t.FechaHora,t.NoSerieEquipo,
    t.ModeloEquipo,t.DescripcionReporte,d.Calle,d.NoExterior,d.NoInterior,d.Colonia,d.Delegacion,d.Ciudad,d.CodigoPostal,t.ObservacionAdicional,t.NoTicketCliente,t.NoTicketDistribuidor
    FROM c_ticket t LEFT JOIN c_domicilio d ON d.ClaveEspecialDomicilio=t.ClaveCentroCosto 
    WHERE t.IdTicket='$idTicket' ORDER BY NumSerie DESC, IdDomicilio;";

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
    $domicilioCliente = $rs['Calle'] . "," . $rs['NoExterior'] . ",No. Int: ".$rs['NoInterior']."," . $rs['Colonia'] . "," . $rs['Delegacion'] . "," . $rs['Ciudad'] . "," . $rs['CodigoPostal'];
}
sort($series);
if ($tipoReporte != "15") {
    $orden = "Orden de Servicio";
    if ($serieFalla != "") {
        $lecturaTicket->setNoSerie($serieFalla);
        $lecturaTicket->getLecturaBYNoSerie();
        $fechaContadorAnterior = $lecturaTicket->getFechaA();
        $contadorNegro = $lecturaTicket->getContadorBNA();
        $contadorColor = $lecturaTicket->getContadorColorA();
        $nivelNegro = $lecturaTicket->getNivelNegroA();
        $nivelCia = $lecturaTicket->getNivelCiaA();
        $nivelMagenta = $lecturaTicket->getNivelMagentaA();
        $nivelAmarillo = $lecturaTicket->getNivelAmarillo();
    }
} else if ($tipoReporte == "15") {
    $orden = "Orden de Tóner";
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

if ($EstadoTicketDatos == "2" && $tipoReporte != "15" || $EstadoTicketDatos == "4" && $tipoReporte != "15") {
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
        $consultaDomicilioLocalidad = "SELECT c.NombreRazonSocial,cc.Nombre,d.Calle,d.NoExterior,d.NoInterior,d.Colonia,d.Delegacion,d.Estado,d.CodigoPostal,d.Ciudad
        FROM c_domicilio d,c_cliente c,c_centrocosto cc WHERE d.ClaveEspecialDomicilio='$claveLocalidad' AND d.ClaveEspecialDomicilio=cc.ClaveCentroCosto AND cc.ClaveCliente=c.ClaveCliente";
        $queryConsultaDomicilio = $catalogo->obtenerLista($consultaDomicilioLocalidad);
        if ($rs = mysql_fetch_array($queryConsultaDomicilio)) {
            $nombreCliente = $rs['NombreRazonSocial'];
            $nombreLocalidad = $rs['Nombre'];
            $domicilioCliente = $rs['Calle'] . "," . $rs['NoExterior'] . ", No. Int: ".$rs['NoInterior']." ," . $rs['Colonia'] . "," . $rs['Delegacion'] . "," . $rs['Ciudad'] . "," . $rs['CodigoPostal'];
        }

        //$domicilioCliente="domicilio prueba";
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
        <title>Reporte ticket</title>
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
        <script src="http://code.jquery.com/jquery-1.9.1.js"></script>
        <script src="http://code.jquery.com/ui/1.10.3/jquery-ui.js"></script>        
        <script type="text/javascript" language="javascript" src="../resources/js/paginas/reporte_ticket.js"></script>
        <a href=javascript:window.print(); style="margin-left: 85%;">Imprimir</a> 
        <br/><br/>
        <?php
        foreach ($series as $key => $value) {
            echo "<input type='hidden' id='serie_$key' name='serie_$key' value='$value'/>";
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
                        <tr><td></td><td></td><td><b>No. de Ticket:</b><br/></td></tr>
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
                                <th align='center' style='font-size:12px;font-family:Arial;'>Génesis</th>
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
                            /*else
                                echo "<td style='width: 25%;font-size:12px;font-family:Arial;'></td>";*/
                            if ($correo != "")
                                echo "<td style='width: 25%;font-size:12px;font-family:Arial;'><b>Correo: </b>$correo</td></tr>";
                            /*else
                                echo "<td style='width: 25%;font-size:12px;font-family:Arial;'></td>";*/
                            ?>
                    </table>
                </td>
                <td style="width: 50%">
                    <?php
                    if ($tipoReporte != "15") {
                        echo "<table style='width: 100%'><tr>";
                        echo "<td style='font-size:12px;font-family:Arial;'><b>Modelo: </b>$ModeloFalla</td>";
                        echo "<td style='font-size:12px;font-family:Arial;'><div style='margin-left: 95%;'><b>Serie:</b></div></td>
                            <td style='font-size:12px;font-family:Arial;'><b></b>";
                        echo "<div id='cbNoSerie_0' style='max-width:100%; margin-left: 32%;'></div>";
                        //echo $series[0];
                        echo "</td>";
                        echo "</tr><tr>";
                        echo "<td colspan='4' style='font-size:12px;font-family:Arial;'><b>Fecha de levantamiento: </b> $fechaTicket $horaTicket</td>";
                        echo "</tr><tr>";
                        echo "<td style='font-size:12px;font-family:Arial;'><b>Contador B/N:</b>$contadorNegro</td>";
                        if ($contadorColor != ""){
                            echo "<td style='font-size:12px;font-family:Arial;'><b>Contador color: </b>$contadorColor</td>";
                        }
                        
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
                    } else if ($tipoReporte == "15") {
                        echo "<table style='width: 100%'><tr>";
                        echo "<td align='right' style='font-size:12px;font-family:Arial;'><b>Fecha levantamiento de ticket: </b></td><td align='left'>$fechaTicket $horaTicket</td>";
                        echo "</table>";
                    }
                    ?>
                </td>
            </tr>
        </table>
        <?php if ($tipoReporte == "15") { ?>
            <fieldset>
                <legend><b>Datos de los Equipos</b></legend> 
                <table style="width: 100%;" class="BorderTabla">                    
                    <thead style="background-color: grey;">      
                        <tr>
                            <td align="center" colspan="2">Equipo</td>
                            <td align="center" colspan="3">Contadores</td>
                            <td align="center" colspan="4">Niveles</td>
                            <td align="center" colspan="4">Pedido</td>
                        </tr>
                        <tr>
                            <th align="center" style="width: 13%;">No Serie</th>
                            <th align="center" style="width: 10%;">Modelo</th>
                            <th align="center" style="width: 12%;">Fecha/Hora</th>
                            <th align="center" style="width: 5%;">B/N</th>
                            <th align="center" style="width: 5%;">Color</th>
                            <th align="center" style="width: 5%;">Negro</th>
                            <th align="center" style="width: 5%;">Cian</th>
                            <th align="center" style="width: 5%;">Amarillo</th>
                            <th align="center" style="width: 5%;">Magenta</th>

                            <th align="center"  style="width: 5%;">Cantidad Solicitada</th>
                            <th align="center"  style="width: 25%;">Modelo / No parte</th>
                        </tr>
                    </thead>
                    <tbody style="background-color: #D3D6FF;">
                        <?php
                        $queryPedido = $catalogo->obtenerLista("SELECT p.ClaveEspEquipo,p.Modelo FROM c_pedido p 
                            WHERE p.IdTicket='$idTicket' GROUP BY p.ClaveEspEquipo");
                        $contador = 0;
                        while ($rs = mysql_fetch_array($queryPedido)) {
                            $lecturaTicket->setNoSerie($rs['ClaveEspEquipo']);
                            $lecturaTicket->getLecturaBYNoSerieAndTicket($idTicket);
                            $fechaContadorAnterior = $lecturaTicket->getFechaA();
                            $contadorNegro = $lecturaTicket->getContadorBNA();
                            $contadorColor = $lecturaTicket->getContadorColorA();
                            $nivelNegro = $lecturaTicket->getNivelNegroA();
                            $nivelCia = $lecturaTicket->getNivelCiaA();
                            $nivelMagenta = $lecturaTicket->getNivelMagentaA();
                            $nivelAmarillo = $lecturaTicket->getNivelAmarilloA();
                            if ($fechaContadorAnterior == "") {
                                $fecha = "";
                                $hora = "";
                            } else {
                                list($fecha, $hora) = explode(" ", $fechaContadorAnterior);
                                list($anio1, $mes1, $dia1) = explode("-", $fecha);
                            }
                            $consultaPedido = "SELECT c.NoParte, c.Modelo, c.Descripcion,dn.NoSerieEquipo,dn.Cantidad
                                FROM c_notaticket nt,k_detalle_notarefaccion dn,c_componente c
                                WHERE nt.IdNotaTicket=dn.IdNota AND dn.Componente=c.NoParte AND nt.IdTicket='$idTicket' 
                                AND dn.NoSerieEquipo='" . $rs['ClaveEspEquipo'] . "' ORDER BY NoSerieEquipo DESC;";

                            $queryTonerSolicitado = $catalogo->obtenerLista($consultaPedido);
                            $tamanoConslta1 = mysql_num_rows($queryTonerSolicitado); // obtenemos el número de filas 
                            $tamanoConslta = $tamanoConslta1 + 1;
                            echo "<tr>";
                            echo "<td align='center' rowspan='$tamanoConslta' style='border: 0px white solid;' >
                                &nbsp;&nbsp;&nbsp;&nbsp;<div id='cbNoSerie_$contador' style='max-width:100%;' class='imagenimpr'></div>&nbsp;&nbsp;&nbsp;&nbsp;
                              </td>";
                            /*echo "<td align='center' rowspan='$tamanoConslta' style='border: 0px white solid;' >
                                &nbsp;&nbsp;&nbsp;&nbsp;".$rs['ClaveEspEquipo']."&nbsp;&nbsp;&nbsp;&nbsp;
                              </td>";*/
                            echo "<td align='center' rowspan='$tamanoConslta'>" . $rs['Modelo'] . "</td>"
                            . "<td align='center' rowspan='$tamanoConslta'>" . $dia1 . "-" . $mes1 . "-" . $anio1 . " " . $hora . "</td>"
                            . "<td align='center' rowspan='$tamanoConslta'>" . $contadorNegro . "</td>"
                            . "<td align='center' rowspan='$tamanoConslta'>" . $contadorColor . "</td>"
                            . "<td align='center' rowspan='$tamanoConslta'>" . $nivelNegro . "</td>"
                            . "<td align='center' rowspan='$tamanoConslta'>" . $nivelCia . "</td>"
                            . "<td align='center' rowspan='$tamanoConslta'>" . $nivelAmarillo . "</td>"
                            . "<td align='center' rowspan='$tamanoConslta'>" . $nivelMagenta . "</td></tr>";
                            $contador++;
                            while ($toner = mysql_fetch_array($queryTonerSolicitado)) {
                                echo "<tr>"
                                . "<td align='center'>" . $toner['Cantidad'] . "</td><td align='center'>" . $toner['Modelo'] . " / " . $toner['NoParte'] . "</td>"
                                . "</tr>";
                            }
                            echo "</tr>";
                        }
                        ?>
                    </tbody>
                </table>
            </fieldset>
            <fieldset>
                <legend><b>Recepción Toner</b></legend> 
                <?php
                $consultaTonerEnviado = "SELECT c.Modelo,c.NoParte,c.Descripcion,SUM(nr.Cantidad) AS Cantidad FROM c_notaticket nt,k_nota_refaccion nr,c_componente c 
                    WHERE nt.IdTicket='$idTicket' AND nt.IdEstatusAtencion=66 AND nt.IdNotaTicket=nr.IdNotaTicket AND nr.NoParteComponente=c.NoParte GROUP BY c.NoParte";
                $queryTonerEnviado = $catalogo->obtenerLista($consultaTonerEnviado);
                $numero = mysql_num_rows($queryTonerEnviado);
                if ($numero > 0) {
                    echo "<table style='width: 50%' class='BorderTabla'>";
                    echo "<thead><thead style='background-color: grey;'><th align='center'>Cantidad</th><th align='center'>Modelo</th><th align='center'>No parte</th></thead><tbody style='background-color: #D3D6FF;'>";
                    while ($rs = mysql_fetch_array($queryTonerEnviado)) {
                        echo "<tr><td align='center'>" . $rs['Cantidad'] . "</td><td align='center'>" . $rs['Modelo'] . "</td><td align='center'>" . $rs['NoParte'] . "</td></tr>";
                    }
                    echo "</tbody><table>";
                }
                ?>
            </fieldset> 
            <fieldset style="width: 98%;height: 50px">
                <legend><b>Observaciones Adicionales</b></legend>
                <?php echo $observacionAdicional ?>
            </fieldset> 
            <fieldset>
                <legend><b>Evaluación 1 mala   3 regular   5 excelente</b></legend> 
                <table style="width: 100%;height: 40px" class="BorderTabla">
                    <thead style="background-color: grey;"><tr><td align="center" style="width: 40%;">Técnico</td><td align="center" style="width: 20%;">Puntualidad</td><td align="center" style="width: 20%;">Actitud</td><td align="center" style="width: 20%;">Conocimientos</td></tr></thead>
                    <tbody style="background-color: #D3D6FF;"><tr><td align="center"></td><td align="center">1 2 3 4 5</td><td align="center">1 2 3 4 5</td><td align="center">1 2 3 4 5</td></tr></tbody>
                </table>
            </fieldset>
            <fieldset>
                <legend><b>Comentarios y Sugerencias</b></legend> 
                <table style="width: 100%;height: 50px">
                    <tr><td></td></tr>
                </table>
            </fieldset>    

        <?php } else if ($tipoReporte != "15") { ?>
            <fieldset>
                <legend><b>Problema reportado</b></legend> 
                <?php echo $descripcionReporte ?>
            </fieldset>
            <fieldset>
                <legend><b>Acciones realizadas / Acciones pendientes de realizar</b></legend> 
                <table style="width: 100%;height: 40px;" class="BorderTabla">
                    <thead style="background-color: grey;">
                        <tr></tr>
                        <tr>
                            <th align="center" style="width: 10%;">Fecha</th>
                            <th align="center" style="width: 40%;">Diagnóstico</th>
                            <th align="center" style="width: 10%;">Técnico</th>
                            <th align="center" style="width: 10%;">Status</th>

                        </tr>
                    </thead>
                    <tbody style="background-color: #D3D6FF;">
                        <?php
                        $consultaNotas = "SELECT nt.IdNotaTicket,nt.FechaHora,nt.DiagnosticoSol,nt.UsuarioUltimaModificacion,e.Nombre,nt.IdEstatusAtencion 
                            FROM c_notaticket nt,c_estado e 
                            WHERE nt.IdEstatusAtencion=e.IdEstado AND nt.IdTicket='$idTicket' AND (nt.IdEstatusAtencion = 24 OR nt.MostrarCliente=1) 
                            ORDER BY nt.FechaHora DESC;";
                        $queryNotas = $catalogo->obtenerLista($consultaNotas);                        
                        while ($rs = mysql_fetch_array($queryNotas)) {
                            list($fecha, $hora) = explode(" ", $rs['FechaHora']);
                            list($anio1, $mes1, $dia1) = explode("-", $fecha);
                            echo "<tr>";                                                       
                            $consultaRefacionesSolicitadas = "SELECT c.Modelo,c.NoParte,nr.Cantidad,c.Descripcion,
                                (CASE WHEN !ISNULL(nr2.CantidadNota) THEN nr2.CantidadNota ELSE nr.Cantidad END) AS CantidadNota
                                FROM k_nota_refaccion AS nr
                                INNER JOIN c_componente AS c ON c.NoParte=nr.NoParteComponente
                                LEFT JOIN c_notaticket AS nt ON nt.IdNotaTicket = nr.IdNotaTicket
                                LEFT JOIN c_notaticket AS nt2 ON nt2.IdNotaTicket = (SELECT MIN(IdNotaTicket) FROM c_notaticket WHERE IdTicket = nt.IdTicket AND IdEstatusAtencion = 9)
                                LEFT JOIN k_nota_refaccion AS nr2 ON nr2.IdNotaTicket = nt2.IdNotaTicket AND nr2.NoParteComponente = nr.NoParteComponente
                                WHERE nr.IdNotaTicket = '" . $rs['IdNotaTicket'] . "' GROUP BY NoParte, nr.IdNotaTicket;";
                            //echo $consultaRefacionesSolicitadas;
                            $queryConsultaRefaccion = $catalogo->obtenerLista($consultaRefacionesSolicitadas);
                            $tamanoC = mysql_num_rows($queryConsultaRefaccion);
                            $tamanoC1 = $tamanoC + 1;
                            echo "<td align='center'  >" . $dia1 . "-" . $mes1 . "-" . $anio1 . " " . $hora . "</td>"
                            . "<td align='center'>" . $rs['DiagnosticoSol'] . "";

                            if ($rs['IdEstatusAtencion'] == "9" || $rs['IdEstatusAtencion'] == "24") {
                                echo "<table>";
                                echo "<tr>";
                                echo "<th align='center' style='width: 5%;'>cantidad</th>";
                                echo "<th align='center' style='width: 30%;'>Refacción</th>";
                                echo "</tr>";
                                while ($refaccion = mysql_fetch_array($queryConsultaRefaccion)) {
                                    echo "<tr>"
                                    . "<td align='center'>" . $refaccion['CantidadNota'] . "</td>"
                                    . "<td align='center'>" . $refaccion['Modelo'] . " / " . $refaccion['NoParte'] . " / " . $refaccion['Descripcion'] . "</td>"
                                    . "</tr>";
                                }
                                echo "</table>";
                            }
                            echo "</td>";
                            echo "<td align='center'>" . $rs['UsuarioUltimaModificacion'] . "</td>"
                            . "<td align='center'>" . $rs['Nombre'] . "</td>"
                            . "</tr>";
                        }
                        ?>
                    </tbody>
                </table>
            </fieldset>
            <fieldset>
                <legend></legend> 
                <fieldset>
                    <legend><b>Observaciones Adicionales</b></legend> 
                    <table style="width: 98%;height: 50px">
                        <tr><td><?php echo $observacionAdicional; ?></td></tr>
                    </table>
                </fieldset>
                <fieldset>
                    <legend><b>Cierre</b></legend> 
                    <table style="width: 100%; height: 40px" class="BorderTabla">
                        <thead style="background-color: grey;"><tr><th align="center" style="width: 10%;">Fecha</th><th align="center"  style="width: 10%;">Hora</th><th align="center"  style="width: 20%;">Contador B/N</th><th align="center"  style="width: 20%;">Contador color</th><th align="center"  style="width: 10%;">Nivel tóner negro</th><th align="center"  style="width: 10%;">Nivel tóner cian</th><th align="center"  style="width: 10%;">Nivel tóner amarillo</th><th align="center"  style="width: 10%;">Nivel tóner magenta</th></tr></thead>
                        <tbody style="background-color: #D3D6FF;">
                            <?php
                            $consultaNotas2 = "SELECT nt.IdEstatusAtencion FROM c_notaticket nt,c_estado e WHERE nt.IdEstatusAtencion=e.IdEstado AND nt.IdTicket='$idTicket' AND nt.MostrarCliente=1 ORDER BY nt.FechaHora DESC";
                            $queryestatus = $catalogo->obtenerLista($consultaNotas2);
                            $IdEstatusAtencion = "";
                            if ($rs = mysql_fetch_array($queryestatus)) {
                                $IdEstatusAtencion = $rs['IdEstatusAtencion'];
                            }
                            if (($EstadoTicketDatos != 2 && $EstadoTicketDatos != 4) || ($IdEstatusAtencion != 16 && $IdEstatusAtencion != 59)) {
                                $fechaLecturaTicket = "<br/>";
                                $hora = "<br/>";
                                $contadorNegro = "<br/>";
                                $contadorColor = "<br/>";
                                $nivelNegro = "<br/>";
                                $nivelCia = "<br/>";
                                $nivelAmarillo = "<br/>";
                                $nivelMagenta = "<br/>";
                            } else {
                                if ($fechaContadorAnterior == "") {
                                    $fecha = "";
                                    $hora = "";
                                    $fechaLecturaTicket = "";
                                } else {
                                    list($fecha, $hora) = explode(" ", $fechaContadorAnterior);
                                    list($anio1, $mes1, $dia1) = explode("-", $fecha);
                                    $fechaLecturaTicket = $dia1 . "-" . $mes1 . "-" . $anio1;
                                }
                            }
                            echo "<tr>"
                            . "<td align='center'>" . $fechaLecturaTicket . "</td>"
                            . "<td align='center'>" . $hora . "</td>"
                            . "<td align='center'>" . $contadorNegro . "</td>"
                            . "<td align='center'>" . $contadorColor . "</td>"
                            . "<td align='center'>" . $nivelNegro . "</td>"
                            . "<td align='center'>" . $nivelCia . "</td>"
                            . "<td align='center'>" . $nivelAmarillo . "</td>"
                            . "<td align='center'>" . $nivelMagenta . "</td></tr>";
                            ?>
                        </tbody>
                    </table>
                </fieldset>
                <fieldset>
                    <legend><b>Evaluación 1 mala   3 regular   5 excelente</b></legend> 
                    <table style="width: 100%;height: 40px" class="BorderTabla">
                        <thead style="background-color: grey;"><tr><td align="center" style="width: 40%;">Técnico</td><td align="center" style="width: 20%;">Puntualidad</td><td align="center" style="width: 20%;">Actitud</td><td align="center" style="width: 20%;">Conocimientos</td></tr></thead>
                        <tbody style="background-color: #D3D6FF;"><tr><td align="center"></td><td align="center">1 2 3 4 5</td><td align="center">1 2 3 4 5</td><td align="center">1 2 3 4 5</td></tr></tbody>
                    </table>
                </fieldset>
                <fieldset>
                    <legend><b>Comentarios y Sugerencias</b></legend> 
                    <table style="width: 100%;height: 50px">
                        <tr><td></td></tr>
                    </table>
                </fieldset>               
            </fieldset>
        <?php } ?>
        <fieldset>
            <legend><b>Firmas</b></legend> 
            <table style="width:  100%" >
                <tr><td style="width: 50%" align="center">Firma de conformidad</td><td style="width: 50%" align="center">Ingeniero Génesis</td></tr>
                <tr><td align="center" style="width: 50%"><br/><br/><HR width=300px > </td><td align="center" style="width: 50%"><br/><br/><HR width=300px ></td></tr>
                <tr><td style="width: 50%" align="center"><?php echo $contacto; ?></td><td style="width: 50%" align="center"></td></tr>
            </table>
        </fieldset>
    </body>
</html>