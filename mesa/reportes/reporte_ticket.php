<?php
session_start();
if (!isset($_SESSION['user']) || $_SESSION['user'] == "") {
    header("Location: ../../index.php");
}
include_once("../../WEB-INF/Classes/Catalogo.class.php");
include_once("../../WEB-INF/Classes/LecturaTicket.class.php");
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
$consulta = "SELECT t.TipoReporte,t.ClaveCliente,t.ClaveCentroCosto,t.NombreCliente,t.NombreCentroCosto,t.NombreResp,
                t.Telefono1Resp,t.Telefono2Resp,t.Extension1Resp,t.Extension2Resp, t.CelularResp,t.CorreoEResp,t.FechaHora,t.NoSerieEquipo,
                t.ModeloEquipo,t.DescripcionReporte,d.Calle,d.NoExterior,d.Colonia,d.Delegacion,d.Ciudad,d.CodigoPostal,t.ObservacionAdicional,t.NoTicketCliente,t.NoTicketDistribuidor
            FROM c_ticket t LEFT JOIN c_domicilio d ON d.ClaveEspecialDomicilio=t.ClaveCentroCosto 
            WHERE t.IdTicket='$idTicket'";
$queryTicket = $catalogo->obtenerLista($consulta);
while ($rs = mysql_fetch_array($queryTicket)) {
    $tipoReporte = $rs['TipoReporte'];
    $claveCliente = $rs['ClaveCliente'];
    $claveLocalidad = $rs['ClaveCentroCosto'];
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
    $domicilioCliente = $rs['Calle'] . "," . $rs['NoExterior'] . "," . $rs['Colonia'] . "," . $rs['Delegacion'] . "," . $rs['Ciudad'] . "," . $rs['CodigoPostal'];
}
if ($tipoReporte == "1") {
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
$queryFacturacion = $catalogo->obtenerLista("SELECT df.IdDatosFacturacionEmpresa,df.Calle,df.NoExterior,df.Colonia,df.Delegacion,df.Estado,df.CP FROM c_datosfacturacionempresa df WHERE df.IdDatosFacturacionEmpresa=(SELECT c.IdDatosFacturacionEmpresa FROM c_cliente c WHERE c.ClaveCliente='V09_CL02')");
while ($rs = mysql_fetch_array($queryFacturacion)) {
    $idDatosFacturacion = $rs['IdDatosFacturacionEmpresa'];
    $domicilio = $rs['Calle'] . "," . $rs['NoExterior'] . "," . $rs['Colonia'] . "," . $rs['Delegacion'] . "," . $rs['Estado'] . "," . $rs['CP'];
}
if ($idDatosFacturacion == "1") {
    $nombreLogo = "Logo_Kyocera_SCG.jpg";
} else if ($idDatosFacturacion == "2") {
    $nombreLogo = "Logo_Kyocera_ODF.jpg";
} else if ($idDatosFacturacion == "3") {
    $nombreLogo = "Logo_Kyocera_GSC.jpg";
}
?>
<!DOCTYPE html>
<html lang="es">
    <head>     
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/> 
        <style>
            .BorderTabla td,th {
                border: solid black;
                border-width:1px;
                border-spacing: 0px;
                border-collapse: collapse
            }
        </style>
    </head>
    <body>
        <a href=javascript:window.print(); style="margin: 85%;">Imprimir</a> 
        <table style="width: 100%">
            <tr>                
                <td align='center' style="width: 45%"><img src="../../resources/images/logos/<?php echo $nombreLogo; ?>"/></td>
                <td align='center' style="width: 10%"></td>
                <td align='center' style="width: 45%">
                    <p><?php echo $domicilio ?></p>
                </td>
            </tr>
            <tr>
                <td align='center' colspan="3"><b><?php echo $orden; ?></b></td>
            </tr>
            <tr>
                <td align='center' style="width: 45%"></td>
                <td align='center' style="width: 10%"></td>
                <td align='center' style="width: 45%"><b>No. de Ticktet:</b></td>
            </tr>
            <tr>
                <td style="width: 45%" align='center' colspan="2"><?php echo $nombreCliente . " / " . $nombreLocalidad; ?></td>
                <td rowspan="2" style="width: 45%"  align='center'>
                    <table class="BorderTabla">
                        <thead style="background-color: grey;">
                            <tr>
                                <?php
                                if ($ticketCliente != "")
                                    echo "<th>Cliente</th>";
                                ?>
                                <?php
                                if ($ticketDistribucion != "")
                                    echo "<th>Distibución</th>";
                                ?>
                                <th>Génesis</th>
                            </tr>
                        </thead>
                        <tbody style="background-color: #D3D6FF"><tr>
                                <?php
                                if ($ticketCliente != "")
                                    echo "<td>$ticketCliente</td>";
                                ?>
                                <?php
                                if ($ticketDistribucion != "")
                                    echo "<td>$ticketDistribucion</td>";
                                ?>
                                <td style="color: red"><?php echo $idTicket; ?></td>
                            </tr></tbody>
                    </table>   
                </td>
            </tr>
            <tr>
                <td  style="width: 45%" align='center' colspan="2"><?php echo $domicilioCliente; ?></td>
                <td align='center' style="width: 45%"></td>               
            </tr>
        </table>
        <table style="width: 100%">
            <tr>
                <td style="width: 50%">
                    <table style="width: 100%">
                        <tr>
                            <td><b>Contacto:</b></td><td colspan="3"><?php echo $contacto; ?></td>
                        </tr>
                    </table>
                    <table style="width: 100%">
                        <tr>
                            <?php
                            if ($telefono1 != "")
                                echo "<td align='center' style='width: 30%'><b>Teléfono 1:</b> $telefono1</td>";
                            else
                                echo "<td align='center' style='width: 30%'></td>";
                            if ($extencion1 != "")
                                echo "<td align='center' style='width: 20%'> <b>Ext 1: </b>$extencion1</td>";
                            else
                                echo "<td align='center' style='width: 20%'></td>";
                            if ($telefono2 != "")
                                echo "<td align='center' style='width: 30%'><b>Teléfono 2: </b>$telefono2;</td>";
                            else
                                echo "<td align='center' style='width: 20%'></td>";
                            if ($extencion2 != "")
                                echo "<td align='center' style='width: 30%'><b>Ext 2</b>:$extencion2</td>";
                            else
                                echo "<td align='center' style='width: 20%'></td>";
                            ?>
                        </tr>
                    </table>
                    <table style="width: 100%">
                        <tr>
                            <?php
                            if ($celular != "")
                                echo "<td style='width: 25%' align='left'><b>Celular: </b></td><td style='width: 25%' align='left'>$celular</td>";
                            else
                                echo "<td align='center' style='width: 25%'></td>";
                            if ($correo != "")
                                echo "<td style='width: 25%' align='right'><b>Correo: </b></td><td style='width: 25%' align='left'>$correo</td></tr>";
                            else
                                echo "<td align='center' style='width: 25%'></td>";
                            ?>


                    </table>
                </td>
                <td style="width: 50%">
                    <?php
                    if ($tipoReporte == "1") {
                        echo "<table style='width: 100%'><tr>";
                        echo "<td><b>Modelo: </b></td><td>$ModeloFalla</td>";
                        echo "<td><b>Serie:</b></td><td><b></b>$serieFalla</td>";
                        echo "</tr><tr>";
                        echo "<td colspan='4'><b>Fecha de levantamiento: </b> $fechaTicket $horaTicket</td>";
                        echo "</tr><tr>";
                        echo "<td><b>Contador B/N:</b></td><td>$contadorNegro</td>";
                        if ($contadorColor != "")
                            echo "<td><b>Contador color: </b></td><td>$contadorColor</td>";
                        else
                            echo "<td><b></b></td><td></td>";
                        echo "</tr><tr>";
                        if ($nivelNegro != "")
                            echo "<td><b>Nivel negro:</b>$nivelNegro </td>";
                        else
                            echo "<td><b></b></td>";
                        if ($nivelCia != "")
                            echo "<td><b>Nivel cian:</b> $nivelCia</td>";
                        else
                            echo "<td><b></b></td>";
                        if ($nivelMagenta != "")
                            echo "<td><b>Nivel magenta:</b> $nivelMagenta</td>";
                        else
                            echo "<td><b></b></td>";
                        if ($nivelAmarillo != "")
                            echo "<td><b>Nivel amarillo:</b> $nivelAmarillo</td>";
                        else
                            echo "<td><b></b></td>";
                        echo "</tr></table>";
                    } else if ($tipoReporte == "15") {
                        echo "<table style='width: 100%'><tr>";
                        echo "<td align='right'><b>Fecha levantamiento de ticket: </b></td><td align='left'>$fechaTicket $horaTicket</td>";
                        echo "</table>";
                    }
                    ?>
                </td>
            </tr>
        </table>
        <?php if ($tipoReporte == "15") { ?>
            <fieldset>
                <legend>Datos de los Equipos</legend> 
                <table style="width: 100%;" class="BorderTabla">                    
                    <thead style="background-color: grey;">      
                        <tr>
                            <td align="center" colspan="2">Equipo</td>
                            <td align="center" colspan="3">Contadores</td>
                            <td align="center" colspan="4">Niveles</td>
                            <td align="center" colspan="4">Pedido</td>
                        </tr>
                        <tr>
                            <th align="center" style="width: 10%;">No Serie</th>
                            <th align="center" style="width: 10%;">Modelo</th>
                            <th align="center" style="width: 15%;">Fecha/Hora</th>
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
                        $queryPedido = $catalogo->obtenerLista("SELECT p.ClaveEspEquipo,p.Modelo FROM c_pedido p WHERE p.IdTicket='$idTicket' GROUP BY p.ClaveEspEquipo");
                        while ($rs = mysql_fetch_array($queryPedido)) {
                            $lecturaTicket->setNoSerie($rs['ClaveEspEquipo']);
                            $lecturaTicket->getLecturaBYNoSerie();
                            $fechaContadorAnterior = $lecturaTicket->getFechaA();
                            $contadorNegro = $lecturaTicket->getContadorBNA();
                            $contadorColor = $lecturaTicket->getContadorColorA();
                            $nivelNegro = $lecturaTicket->getNivelNegroA();
                            $nivelCia = $lecturaTicket->getNivelCiaA();
                            $nivelMagenta = $lecturaTicket->getNivelMagentaA();
                            $nivelAmarillo = $lecturaTicket->getNivelAmarillo();
                            if ($fechaContadorAnterior == "") {
                                $fecha = "";
                                $hora = "";
                            } else {
                                list($fecha, $hora) = explode(" ", $fechaContadorAnterior);
                                list($anio1, $mes1, $dia1) = explode("-", $fecha);
                            }
                            $consultaPedido = "SELECT c.NoParte, c.Modelo, c.Descripcion,dn.NoSerieEquipo,dn.Cantidad
                                                                            FROM c_notaticket nt,k_detalle_notarefaccion dn,c_componente c
                                                                            WHERE nt.IdNotaTicket=dn.IdNota AND dn.Componente=c.NoParte AND nt.IdTicket='$idTicket' AND dn.NoSerieEquipo='" . $rs['ClaveEspEquipo'] . "'";

                            $queryTonerSolicitado = $catalogo->obtenerLista($consultaPedido);
                            $tamanoConslta1 = mysql_num_rows($queryTonerSolicitado); // obtenemos el número de filas 
                            $tamanoConslta = $tamanoConslta1 + 1;
                            echo "<tr><td align='center' rowspan='$tamanoConslta'>" . $rs['ClaveEspEquipo'] . "</td>"
                            . "<td align='center' rowspan='$tamanoConslta'>" . $rs['Modelo'] . "</td>"
                            . "<td align='center' rowspan='$tamanoConslta'>" . $dia1 . "-" . $mes1 . "-" . $anio1 . " " . $hora . "</td>"
                            . "<td align='center' rowspan='$tamanoConslta'>" . $contadorNegro . "</td>"
                            . "<td align='center' rowspan='$tamanoConslta'>" . $contadorColor . "</td>"
                            . "<td align='center' rowspan='$tamanoConslta'>" . $nivelNegro . "</td>"
                            . "<td align='center' rowspan='$tamanoConslta'>" . $nivelCia . "</td>"
                            . "<td align='center' rowspan='$tamanoConslta'>" . $nivelAmarillo . "</td>"
                            . "<td align='center' rowspan='$tamanoConslta'>" . $nivelMagenta . "</td></tr>";
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
                <legend>Recepción Toner</legend> 
                <?php
                $consultaTonerEnviado = "SELECT c.Modelo,c.NoParte,c.Descripcion,nr.Cantidad FROM c_notaticket nt,k_nota_refaccion nr,c_componente c 
                                                WHERE nt.IdTicket='$idTicket' AND nt.IdEstatusAtencion=66 AND nt.IdNotaTicket=nr.IdNotaTicket AND nr.NoParteComponente=c.NoParte";
                $queryTonerEnviado = $catalogo->obtenerLista($consultaTonerEnviado);
                echo "<table style='width: 50%' class='BorderTabla'>";
                echo "<thead><thead style='background-color: grey;'><th align='center'>Cantidad</th><th align='center'>Modelo</th><th align='center'>No parte</th></thead><tbody style='background-color: #D3D6FF;'>";
                while ($rs = mysql_fetch_array($queryTonerEnviado)) {
                    echo "<tr><td align='center'>" . $rs['Cantidad'] . "</td><td align='center'>" . $rs['Modelo'] . "</td><td align='center'>" . $rs['NoParte'] . "</td></tr>";
                }
                echo "</tbody><table>";
                ?>
            </fieldset> 
            <fieldset>
                <legend>Observaciones Adicionales</legend> 
                <?php echo $observacionAdicional; ?>
            </fieldset> 
            <fieldset>
                <legend>Evaluación 1 mala   3 regular   5 excelente</legend> 
                <table style="width: 100%;height: 40px" class="BorderTabla">
                    <thead style="background-color: grey;"><tr><td align="center" style="width: 40%;">Técnico</td><td align="center" style="width: 20%;">Puntualidad</td><td align="center" style="width: 20%;">Actitud</td><td align="center" style="width: 20%;">Conocimientos</td></tr></thead>
                    <tbody style="background-color: #D3D6FF;"><tr><td align="center"></td><td align="center">1 2 3 4 5</td><td align="center">1 2 3 4 5</td><td align="center">1 2 3 4 5</td></tr></tbody>
                </table>
            </fieldset>
            <fieldset>
                <legend>Comentarios y Sugerencias</legend> 
                <table style="width: 100%;height: 100px">
                    <tr><td></td></tr>
                </table>
            </fieldset>    

        <?php } else if ($tipoReporte == "1") { ?>
            <fieldset>
                <legend>Problema reportado</legend> 
                <?php echo $descripcionReporte ?>
            </fieldset>
            <fieldset>
                <legend>Acciones realizadas / Acciones pendientes de realizar</legend> 
                <table style="width: 100%;height: 40px;" class="BorderTabla">
                    <thead style="background-color: grey;"><tr><th align="center" style="width: 20%;">Fecha</th><th align="center" style="width: 40%;">Diagnóstico</th><th align="center" style="width: 20%;">Técnico</th><th align="center" style="width: 20%;">Status</th></tr></thead>
                    <tbody style="background-color: #D3D6FF;">
                        <?php
                        $consultaNotas = "SELECT nt.FechaHora,nt.DiagnosticoSol,nt.UsuarioUltimaModificacion,e.Nombre FROM c_notaticket nt,c_estado e WHERE nt.IdEstatusAtencion=e.IdEstado AND nt.IdTicket='$idTicket'";
                        $queryNotas = $catalogo->obtenerLista($consultaNotas);
                        while ($rs = mysql_fetch_array($queryNotas)) {
                            list($fecha, $hora) = explode(" ", $rs['FechaHora']);
                            list($anio1, $mes1, $dia1) = explode("-", $fecha);
                            echo "<tr><td align='center'>" . $dia1 . "-" . $mes1 . "-" . $anio1 . " " . $hora . "</td><td align='center'>" . $rs['DiagnosticoSol'] . "</td><td align='center'>" . $rs['UsuarioUltimaModificacion'] . "</td><td align='center'>" . $rs['Nombre'] . "</td></tr>";
                        }
                        ?>
                    </tbody>
                </table>
            </fieldset>
            <fieldset>
                <legend></legend> 
                <fieldset>
                    <legend>Observaciones Adicionales</legend> 
                    <?php echo $observacionAdicional; ?>
                </fieldset>
                <fieldset>
                    <legend>Cierre</legend> 
                    <table style="width: 100%; height: 40px" class="BorderTabla">
                        <thead style="background-color: grey;"><tr><th align="center" style="width: 10%;">Fecha</th><th align="center"  style="width: 10%;">Hora</th><th align="center"  style="width: 20%;">Contador B/N</th><th align="center"  style="width: 20%;">Contador color</th><th align="center"  style="width: 10%;">Nivel tóner negro</th><th align="center"  style="width: 10%;">Nivel tóner cian</th><th align="center"  style="width: 10%;">Nivel tóner amarillo</th><th align="center"  style="width: 10%;">Nivel tóner magenta</th></tr></thead>
                        <tbody style="background-color: #D3D6FF;">
                            <?php
                            echo "<tr>"
                            . "<td align='center'>&nbsp;</td>"
                            . "<td align='center'></td>"
                            . "<td align='center'></td>"
                            . "<td align='center'></td>"
                            . "<td align='center'></td>"
                            . "<td align='center'></td>"
                            . "<td align='center'></td>"
                            . "<td align='center'></td></tr>";
                            ?>
                        </tbody>
                    </table>
                </fieldset>
                <fieldset>
                    <legend>Evaluación 1 mala   3 regular   5 excelente</legend> 
                    <table style="width: 100%;height: 40px" class="BorderTabla">
                        <thead style="background-color: grey;"><tr><td align="center" style="width: 40%;">Técnico</td><td align="center" style="width: 20%;">Puntualidad</td><td align="center" style="width: 20%;">Actitud</td><td align="center" style="width: 20%;">Conocimientos</td></tr></thead>
                        <tbody style="background-color: #D3D6FF;"><tr><td align="center"></td><td align="center">1 2 3 4 5</td><td align="center">1 2 3 4 5</td><td align="center">1 2 3 4 5</td></tr></tbody>
                    </table>
                </fieldset>
                <fieldset>
                    <legend>Comentarios y Sugerencias</legend> 
    <!--                    <table style="width: 100%;height: 100px">
                        <tr><td></td></tr>
                    </table>-->
                </fieldset>               
            </fieldset>
        <?php } ?>
        <fieldset>
            <legend>Firmas</legend> 
            <table style="width:  100%" >
                <tr><td style="width: 50%" align="center">Firma de conformidad</td><td style="width: 50%" align="center">Ingeniero Génesis</td></tr>
                <tr><td align="center" style="width: 50%"><br/><br/><HR width=300px > </td><td align="center" style="width: 50%"><br/><br/><HR width=300px ></td></tr>
                <tr><td style="width: 50%" align="center"><?php echo $contacto; ?></td><td style="width: 50%" align="center"></td></tr>
            </table>
        </fieldset>
    </body>
</html>