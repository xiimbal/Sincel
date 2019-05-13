<?php
session_start();
if (!isset($_SESSION['user']) || $_SESSION['user'] == "") {
    header("Location: ../../index.php");
}

include_once("../WEB-INF/Classes/Catalogo.class.php");
include_once("../WEB-INF/Classes/LecturaTicket.class.php");
include_once("../WEB-INF/Classes/PermisosSubMenu.class.php");

$permisos_grid2 = new PermisosSubMenu();
$nombre_objeto = $permisos_grid2->getNombreTicketSistema();
$nombre_puesto = $permisos_grid2->getNombreTecnicoSistema();

$idTicket = "";
if(isset($_GET['idTicket'])){
    $idTicket = $_GET['idTicket'];
}
$catalogo = new Catalogo();
$lecturaTicket = new LecturaTicket();

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
$origen = "";
$destino = "";

$series = array();
$consulta = "SELECT 
    (SELECT CASE WHEN t.AreaAtencion = 2 THEN (SELECT group_concat(ClaveEspEquipo separator ', ') FROM `c_pedido` WHERE IdTicket = t.IdTicket) ELSE t.NoSerieEquipo END) AS NumSerie, 
    t.TipoReporte,t.ClaveCliente,t.ClaveCentroCosto,t.NombreCliente,t.NombreCentroCosto,CONCAT(u.Nombre, ' ', u.ApellidoPaterno, ' ', u.ApellidoMaterno) AS NombreResp,t.EstadoDeTicket,t.Resurtido,
    t.Telefono1Resp,t.Telefono2Resp,t.Extension1Resp,t.Extension2Resp, t.CelularResp,u.correo AS CorreoEResp,t.FechaHora,t.NoSerieEquipo,
    t.ModeloEquipo,t.DescripcionReporte,d.Calle,d.NoExterior,d.NoInterior,d.Colonia,d.Delegacion,d.Ciudad,d.CodigoPostal,t.ObservacionAdicional,t.NoTicketCliente,t.NoTicketDistribuidor,
    e.Origen,e.Destino FROM c_ticket t 
    LEFT JOIN c_domicilio d ON d.ClaveEspecialDomicilio=t.ClaveCentroCosto INNER JOIN k_tecnicoticket k ON k.IdTicket = t.IdTicket
    INNER JOIN c_usuario u ON u.IdUsuario = k.IdUsuario INNER JOIN c_especial e ON e.idTicket = t.IdTicket
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
    $resurtido = $rs['Resurtido'];
    $origen = $rs['Origen'];
    $destino = $rs['Destino'];
}
sort($series);
if ($tipoReporte != "15") {
    $orden = "Orden de Servicio";
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
?>
<!DOCTYPE html>
<html lang="es">
    <head>     
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
        <title>Reporte Fotográfico</title>
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
        <a href=javascript:window.print(); style="margin: 85%;">Imprimir</a>
        <img src="../<?php echo $nombreLogo; ?>"/>
        <br/><br/>
        <b><font size="6"><?php echo $nombre_objeto; ?> No. <?php echo $idTicket; ?></font></b>
        <br/><br/>
        <table style="width: 100%;" border="1">
            <tr>
                <td rowspan="2" style="width: 26%;">
                    Cliente: <?php echo $nombreCliente; ?>
                </td>
                <td rowspan="2" style="width: 27%;">
                    <?php echo $nombreLocalidad; ?>
                </td>
                <td rowspan="2" style="width: 26%;">
                    <?php
                        /*if ($tipoReporte == "15") {
                            echo " Orden de toner";
                        }else{
                            echo " ".$serieFalla." MODELO: ".$ModeloFalla;
                        }*/
                    ?>
                </td>
                <td style="width: 7%;">Año</td>
                <td style="width: 7%;">Mes</td>
                <td style="width: 7%;">Dia</td>
            </tr>
            <tr>
                <?php if(strlen($fechaTicket) == 10) { ?>
                    <td style="width: 7%;"><?php echo substr($fechaTicket, 6, 4); ?></td>
                    <td style="width: 7%;"><?php echo substr($fechaTicket, 4, 1); ?></td>
                    <td style="width: 7%;"><?php echo substr($fechaTicket, 0, 2); ?></td>
                <?php }else { ?> 
                    <td style="width: 7%;"><?php echo substr($fechaTicket, 7, 4); ?></td>
                    <td style="width: 7%;"><?php echo substr($fechaTicket, 4, 2); ?></td>
                    <td style="width: 7%;"><?php echo substr($fechaTicket, 0, 2); ?></td>
                <?php } ?> 
            </tr>
            <tr>
                <td rowspan="2" colspan="2">
                    Contacto: <?php echo $contacto; ?>
                </td>
                <td rowspan="2" colspan="4">
                    Direccion Cliente: <?php echo $domicilioCliente; ?>
                </td>
            </tr>
            <tr></tr>
            <tr>
                <td rowspan="2" colspan="2">
                    Correo: <?php echo $correo; ?>
                </td>
                <td rowspan="2" colspan="4">
                    Origen: <?php echo $origen?> <br>
                    <?php 
                    $queryOD = "SELECT CONCAT(Calle_des, ' No. Ext. ', NoExterior_des, ' No. Int. ', NoInterior_des, ', Col. ', Colonia_des, ', Ciudad ', Ciudad_des, ', Mpo. ', Delegacion_des, ', C.P. ', CodigoPostal_des,
                    ', Localidad ', Localidad_des) AS escala FROM k_especialescala WHERE IdTicket = '$idTicket'";
                    $resultOD = $catalogo->obtenerLista($queryOD);
                    $escalaOD = 1;
                    while($rs = mysql_fetch_array($resultOD)){
                        echo "Escala $escalaOD: " . $rs['escala'] . " <br>";
                        $escalaOD += 1;
                    }
                    ?>
                    Destino: <?php echo $destino?>
                </td>
            </tr>
            <tr></tr>
            <?php
                $imagenes = "SELECT * from c_notaticket WHERE Activo = 1 AND MostrarCliente = 1 AND IdTicket = ".$idTicket." ORDER BY FechaHora ASC";
                $resImagenes = $catalogo->obtenerLista($imagenes);
                $td = 1;
                while($rsIm = mysql_fetch_array($resImagenes))
                {
                    if($td == 1){
                        echo "<tr>";
                        echo "<td align = 'center' colspan='2'>";
                        echo $rsIm['DiagnosticoSol'];
                        if(isset($rsIm['PathImagen']) && $rsIm['PathImagen'] != ""){
                            if(file_exists("../".$rsIm['PathImagen'])){
                                echo "<br/><img src = '../".$rsIm['PathImagen']."' height='300' width='350'>";
                            }else{
                                echo "<br/><img src = '".$rsIm['PathImagen']."' height='300' width='350'>";
                            }
                        }
                        echo "</td>";
                    }else{
                        echo "<td align = 'center' colspan='4'>";
                        echo $rsIm['DiagnosticoSol'];
                        if(isset($rsIm['PathImagen']) && $rsIm['PathImagen'] != ""){
                            if(file_exists("../".$rsIm['PathImagen'])){
                                echo "<br/><img src = '../".$rsIm['PathImagen']."' height='300' width='350'>";
                            }else{
                                echo "<br/><img src = '".$rsIm['PathImagen']."' height='300' width='350'>";
                            }
                        }
                        echo "</td>";
                        echo "<tr>";
                        $td = 0;
                    }
                    $td++;
                }
            ?>
            <?php 
                echo "<tr>";
                echo "<td colspan = '7'>";
                if($tipoReporte == "15"){
                    echo "<table border='1' style = 'width: 40%;'>";
                    foreach($series as $serie){
                        echo "<tr>";
                        echo "<td align = 'center'> Serie: </td>";
                        echo "<td align = 'center'> ".$serie."</td>";
                        echo "</tr>";
                    }
                    echo "</table>";
                }
                echo "<b><font size='4'>DESCRIPCION</font></b><br/>";
                echo $descripcionReporte;
                if(isset($observacionAdicional) && $observacionAdicional != ""){
                    echo "<br/><b><font size='3'>OBSERVACION ADICIONAL</font></b><br/>";
                    echo $observacionAdicional;
                }                   
                echo "</td>";
                echo "</tr>";
            ?>
        </table>
    </body>
</html>