<?php
session_start();
if (!isset($_SESSION['user']) || $_SESSION['user'] == "") {
    header("Location: ../index.php");
}
date_default_timezone_get();
$fechaHoraActual = ""; // date("d-m-Y H:i:s");
include_once("../WEB-INF/Classes/Catalogo.class.php");
include_once("../WEB-INF/Classes/Ticket.class.php");
include_once("../WEB-INF/Classes/Pedido.class.php");
include_once("../WEB-INF/Classes/LecturaTicket.class.php");
$catalogo = new Catalogo();
$obj = new Ticket();
$pedido = new Pedido();
$lecturaTicket = new LecturaTicket();
if (isset($_POST['regresar']) && $_POST['regresar']) {
    $pagina_listaRegresar = $_POST['regresar'];
} else {
    $pagina_listaRegresar = "mesa/lista_ticket.php";
}
//$pagina_listaRegresar = "mesa/lista_ticket.php";
$tipoReporte = "";
$estadoTicket = "3";
//div responsable
$correoAtencion = "";
$celularAtencion = "";
$Extencio2Atencion = "";
$telefono2Atencion = "";
$Extencio1Atencion = "";
$telefono1Atencion = "";
$nombreAtencion = "";
$correoResp = "";
$celularResp = "";
$Extencio2Resp = "";
$telefono2Resp = "";
$Extencio1Resp = "";
$telefono1Resp = "";
$nombreResp = "";
$horarioReponsableInicio = "";
$horarioResponsableFin = "";
$horarioAtencioFin = "";
$horarioAtencionFin = "";
//ticket datos
$ticketCliente = "";
$ticketDistribucion = "";
$descripcion = "";
$observacion = "";
//client 
$claveCliente = "";
$nombreCliente = "";
$claveLocalidad = "";
$nombreLocalidad = "";
$tipoCliente = "";
$nombreTipoCliente = "";
$estatusCobranza = "";
$tipoDomicilio = "";
$calle = "";
$colonia = "";
$delegacion = "";
$idZona = "";
$nExterior = "";
$nInterior = "";
$ciudad = "";
$cp = "";
$estadoLocal = "";
$nombreContacto = "";
$telefono = "";
$celular = "";
$correoE = "";
$Ubicaion = "";
$idticket = "";
$read = "";
$arrayPedidoNoSerie = array();
$arrayPedidoNegro = array();
$arrayPedidoCian = array();
$arrayPedidoMagenta = array();
$arrayPedidoAmarrillo = array();
$habilitar = "disabled";
$mostrarInput = "display: none;";
$idTicket = "";
$nombreTabla = "tAlmacen";

$contadorNegro = "";
$contadorNegroAnterior = "";
$contadorColor = "";
$contadorColorAnterior = "";
$nivelNegro = "";
$nivelNegroAnterior = "";
$nivelCian = "";
$nivelCianAnterior = "";
$nivelMagenta = "";
$nivelMagentaAnterior = "";
$nivelAmarillo = "";
$nivelAmarilloAnterior = "";
$fechaContador = "";
$fechaContadorAnterior = "";
$detalle = "";
$desactivarCheckPedido = "";
$drawList = "";
$noSerie = "";
$desactivarRadioPedido = "";
$desactivarRadio = "";
$tfs = "";
$readCliente = "";
$readCliente = "readonly";
$botonCancelar = "";
if (isset($_POST['detalle']) && $_POST['detalle'] == "1" || isset($_POST['detalle']) && $_POST['detalle'] == "0") {
    $detalle = $_POST['detalle'];
    $desactivarCheckPedido = "disabled";
    //$desactivarRadioPedido = "disabled";
}
if (isset($_POST['mostrarDatos']) && $_POST['mostrarDatos'] != "0") {
    $tipoReporte = $_POST['mostrarDatos'];
}
if (isset($_POST['claveCliente']) && $_POST['claveCliente'] != "") {
    $claveCliente = $_POST['claveCliente'];
}
if (isset($_POST['claveLocalidad']) && $_POST['claveLocalidad'] != "") {
    $claveLocalidad = $_POST['claveLocalidad'];
}
if (isset($_POST["idTicket"]) && $_POST["idTicket"] != "" && isset($_POST["area"]) && $_POST["area"] != "") {
    $idTicket = $_POST["idTicket"];
    if ($_POST["area"] == "2")
        $tipoReporte = "15";
    else
        $tipoReporte = "1";
    echo "<br/><br/>Ticket:&nbsp;&nbsp; <input type='text' value='$idTicket' readonly/>";
    $obj->getTicketByID($idTicket);
    $claveCliente = $obj->getClaveCliente();
    $claveLocalidad = $obj->getClaveCentroCosto();
    $areaAtencion = $obj->getAreaAtencion();
    $ticketCliente = $obj->getNoTicketCliente();
    $ticketDistribucion = $obj->getNoTicketDistribuidor();
    $descripcion = $obj->getDescripcionReporte();
    $observacion = $obj->getObservacionAdicional();
    $nombreResp = $obj->getNombreResp();
    $telefono1Resp = $obj->getTelefono1Resp();
    $Extencio1Resp = $obj->getExtension1Resp();
    $telefono2Resp = $obj->getTelefono2Resp();
    $Extencio2Resp = $obj->getExtension2Resp();
    $celularResp = $obj->getCelularResp();
    $correoResp = $obj->getCorreoEResp();
    $nombreAtencion = $obj->getNombreAtenc();
    $telefono1Atencion = $obj->getTelefono1Atenc();
    $Extencio1Atencion = $obj->getExtension1Atenc();
    $telefono2Atencion = $obj->getTelefono2Atenc();
    $Extencio2Atencion = $obj->getExtension2Atenc();
    $celularAtencion = $obj->getCelularAtenc();
    $correoAtencion = $obj->getCorreoEAtenc();
    $horarioReponsableInicio = $obj->getHorarioAtenInicResp();
    $horarioResponsableFin = $obj->getHorarioAtenFinResp();
    $horarioAtencioFin = $obj->getHorarioAtenInicAtenc();
    $horarioAtencionFin = $obj->getHorarioAtenFinAtenc();
    $Ubicaion = $obj->getUbicacion();
    $fechaHoraTicket = $obj->getFechaHora();
    $usuarioTicket = $obj->getUsuario();
    $nombreTabla = "tAlmacen1";
    $noSerie = $obj->getNoSerieEquipo();
    //$drawList = "disabled";
    $read = "readonly";
    // $desactivarRadio = "disabled";
}
if ($idTicket != "") {//buscar pedido
    $pedido->getPedidoByIdTicket($idTicket);
    $arrayPedidoNoSerie = $pedido->getArrayNoSerie();
    $arrayPedidoNegro = $pedido->getArrayNegro();
    $arrayPedidoCian = $pedido->getArrayCian();
    $arrayPedidoMagenta = $pedido->getArrayMagenta();
    $arrayPedidoAmarrillo = $pedido->getArrayAmarillo();
}
if ($claveLocalidad != "") {//datos del cliente 
    $queryDomicilio = $catalogo->obtenerLista("SELECT c.ClaveCliente,c.NombreRazonSocial,c.IdTipoCliente,c.IdEstatusCobranza,
	cc.Nombre AS localidad, td.Nombre AS tdomicilio,d.Calle,d.Colonia,d.Delegacion,
	(CASE WHEN !ISNULL(cc.ClaveZona) THEN cc.ClaveZona ELSE c.ClaveZona END) AS zona, 
	d.NoExterior,d.NoInterior,d.Ciudad,d.CodigoPostal,d.Estado,ct.Nombre AS nombreContacto,
	ct.Telefono,ct.Celular,ct.CorreoElectronico, 
	(SELECT z.fk_id_gzona FROM c_zona z WHERE z.ClaveZona=cc.ClaveZona OR z.ClaveZona=c.ClaveZona LIMIT 1) AS ubicacion,
	(SELECT GROUP_CONCAT(u.Nombre,' ',u.ApellidoPaterno,' ',u.ApellidoMaterno) FROM k_tfscliente tfsc,c_usuario u WHERE cc.ClaveCliente=tfsc.ClaveCliente AND cc.ClaveCliente=tfsc.ClaveCliente AND u.IdUsuario=tfsc.IdUsuario GROUP BY tfsc.ClaveCliente) as tfs
	FROM c_centrocosto cc,c_domicilio d,c_cliente c,c_tipodomicilio td,c_contacto ct WHERE cc.ClaveCentroCosto=d.ClaveEspecialDomicilio 
	AND cc.ClaveCliente=c.ClaveCliente AND td.IdTipoDomicilio=d.IdTipoDomicilio 
	AND ct.ClaveEspecialContacto=cc.ClaveCentroCosto AND cc.ClaveCentroCosto='$claveLocalidad'");
    while ($rs = mysql_fetch_array($queryDomicilio)) {
        $claveCliente = $rs['ClaveCliente'];
        $nombreCliente = $rs['NombreRazonSocial'];
        $tipoCliente = $rs['IdTipoCliente'];
        $nombreLocalidad = $rs['localidad'];
        $estatusCobranza = $rs['IdEstatusCobranza'];
        $tipoDomicilio = $rs['tdomicilio'];
        $calle = $rs['Calle'];
        $colonia = $rs['Colonia'];
        $delegacion = $rs['Delegacion'];
        $idZona = $rs['zona'];
        $nExterior = $rs['NoExterior'];
        $nInterior = $rs['NoInterior'];
        $ciudad = $rs['Ciudad'];
        $cp = $rs['CodigoPostal'];
        $estadoLocal = $rs['Estado'];
        $nombreContacto = $rs['nombreContacto'];
        $telefono = $rs['Telefono'];
        $celular = $rs['Celular'];
        $correoE = $rs['CorreoElectronico'];
        $Ubicaion = $rs['ubicacion'];
        $tfs = $rs['tfs'];
        $readCliente = "readonly";
    }
	echo "MAGG: ".$claveLocalidad;
}
$idUsuario = $_SESSION['idUsuario'];
$idPuesto = "";
$queryTipoCliente = $catalogo->obtenerLista("SELECT u.IdPuesto FROM c_usuario u WHERE u.IdUsuario='$idUsuario'");
while ($rs = mysql_fetch_array($queryTipoCliente)) {
    $idPuesto = $rs['IdPuesto'];
}
$onsultaCliente = "";
if ($idPuesto == "21") {
    $botonCancelar = "1";
    $consultaCliente = "SELECT c.ClaveCliente,c.IdEstatusCobranza,c.NombreRazonSocial,c.Suspendido FROM k_tfscliente tfs,c_cliente c WHERE tfs.ClaveCliente=c.ClaveCliente AND tfs.IdUsuario='$idUsuario'";
} else if ($idPuesto == "11") {
    $consultaCliente = "SELECT * FROM c_cliente c WHERE c.EjecutivoCuenta='$idUsuario'";
} else {
    $consultaCliente = "SELECT c.ClaveCliente,c.IdEstatusCobranza,c.NombreRazonSocial,c.Suspendido FROM c_cliente c WHERE c.Activo=1 GROUP BY c.NombreRazonSocial ORDER BY c.NombreRazonSocial ASC";
}
?>
<!DOCTYPE html>
<html lang="es">
    <head>
        <script type="text/javascript" language="javascript" src="resources/js/paginas/nuevoTicket.js"></script>
        <script type="text/javascript" language="javascript" src="resources/js/paginas/lista.js"></script>
        <script>
            $(function() {
                $("#tabs").tabs();
            });
        </script>
    </head>
    <body>
        <div class="principal"> 
            <form id="frmAltaTicket" name="frmAltaTicket" action="/" method="POST">
                <div>
                    <table style="width: 100%">
                        <tr><td>Tipo de reporte:</td>
                            <td>
                                <select id="sltTipoReporte" name="sltTipoReporte" onchange="MostrarTipoReporte(this.value);" <?php echo $drawList; ?>>
                                    <option value="0">Seleccione tipo de reporte</option>
                                    <?php
                                    $consultaTipo = "SELECT * FROM c_estado e,k_flujoestado fe WHERE e.IdEstado=fe.IdEstado AND fe.IdFlujo=1";
                                    $tipoReporteConsulta = $catalogo->obtenerLista($consultaTipo);
                                    while ($rs = mysql_fetch_array($tipoReporteConsulta)) {
                                        $s = "";
                                        if ($tipoReporte == $rs['IdEstado'])
                                            $s = "selected";
                                        echo "<option value='" . $rs['IdEstado'] . "' $s>" . $rs['Nombre'] . "</option>";
                                    }
                                    ?>
                                </select>
                            </td>
                            <td>Estado del ticket:</td>
                            <td>
                                <select id="sltEstadoTicket" name="sltEstadoTicket" <?php echo $drawList; ?> disabled>
                                    <option value="0">Seleccione el estado del ticket</option>  
                                    <?php
                                    $consultaEstadoTicket = "SELECT * FROM c_estadoticket et WHERE et.Activo=1 ORDER BY et.Nombre ASC";
                                    $queryEstado = $catalogo->obtenerLista($consultaEstadoTicket);
                                    while ($rs = mysql_fetch_array($queryEstado)) {
                                        $s = "";
                                        if ($estadoTicket == $rs['IdEstadoTicket'])
                                            $s = "selected";
                                        echo "<option value='" . $rs['IdEstadoTicket'] . "' $s>" . $rs['Nombre'] . "</option>";
                                    }
                                    ?> 
                                </select>
                            </td>
                        </tr>
                    </table>
                </div> 
                <br/><br/>
                <div>
                    <?php if ($tipoReporte != "" && $tipoReporte != "0") { ?>
                        <div id="tabs">
                            <ul>
                                <li><a href="#tabs-1">Equipo</a></li>
                                <li><a href = "#tabs-2">Cliente</a></li>
                            </ul>
                            <?php if ($tipoReporte == "15") { ?>
                                <div id = "tabs-1" style = "background-color: #A4A4A4">
                                    <table style="width: 50%">
                                        <tr>
                                            <td>Cliente</td>
                                            <td>
                                                <select id="slcCliente" name="slcCliente" style="width: 300px" onchange="incidenciaClienteSuspendido(this.value)" class="filtro" <?php echo $drawList; ?>>
                                                    <option value="0">Seleccione un cliente</option>
                                                    <?php
                                                    $queryCliente = $catalogo->obtenerLista($consultaCliente);
                                                    while ($rs = mysql_fetch_array($queryCliente)) {
                                                        $s = "";
                                                        if ($claveCliente != "" && $claveCliente == $rs['ClaveCliente']) {
                                                            $nombreCliente = $rs['NombreRazonSocial'];
                                                            $s = "selected";
                                                        }
                                                        echo "<option value='" . $rs['ClaveCliente'] . "' $s>" . $rs['NombreRazonSocial'] . "</option>";
                                                    }
                                                    ?>
                                                </select>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td>Localidad</td>
                                            <td>
                                                <select id="slcLocalidad" name="slcLocalidad" style="width: 300px" class="filtro" onchange="MostrarEquipoLocalidad(this.value);" <?php echo $drawList; ?>>
                                                    <?php
                                                    if ($claveCliente != "") {
                                                        $queryCliente = $catalogo->obtenerLista("SELECT cc.ClaveCentroCosto,cc.Nombre FROM c_centrocosto cc WHERE cc.ClaveCliente='$claveCliente' AND cc.Activo=1 ORDER BY cc.Nombre ASC;");
                                                        if (mysql_num_rows($queryCliente) == 1) {
                                                            while ($rs = mysql_fetch_array($queryCliente)) {
                                                                $nombreLocalidad = $rs['Nombre'];
                                                                $claveLocalidad = $rs['ClaveCentroCosto'];
                                                                $s = "selected";
                                                                echo "<option value=" . $rs['ClaveCentroCosto'] . " " . $s . ">" . $rs['Nombre'] . "</option>";
                                                            }
                                                        } else {
                                                            echo " <option value='0'>Seleccione una localidad</option>";
                                                            while ($rs = mysql_fetch_array($queryCliente)) {
                                                                $s = "";
                                                                if ($claveLocalidad != "" && $claveLocalidad == $rs['ClaveCentroCosto']) {
                                                                    $nombreLocalidad = $rs['Nombre'];
                                                                    $s = "selected";
                                                                }
                                                                echo "<option value=" . $rs['ClaveCentroCosto'] . " " . $s . ">" . $rs['Nombre'] . "</option>";
                                                            }
                                                        }
                                                    }
                                                    ?>
                                                </select>
                                            </td>
                                        </tr>
                                    </table>
                                    <?php if ($claveLocalidad != "") { ?>
                                        <br/><br/>
                                        <!--                                        <a href='#' onclick='ReportarEquipoNoExistente();
                                                                                        return false;' title='Detalle'  style="float: right; cursor: pointer;"> <img src='resources/images/Textpreview.png'/> </a>-->
                                        <table id="<?php echo $nombreTabla; ?>" class="tabla_datos" style="width: 100%">
                                            <thead>
                                                <tr>
                                                    <th style="text-align: center; min-width:10%">Activar</th>
                                                    <th style="text-align: center; min-width:10%">No Serie<br/>Contador B/N</th>
                                                    <th style="text-align: center; min-width: 25%">Modelo equipo<br/>Contador Color</th>
                                                    <th style="text-align: center; min-width: 15%">Negro<br/>Negro %</th>
                                                    <th style="text-align: center; min-width: 20%">Cian<br/>Cian %</th>
                                                    <th style="text-align: center; min-width: 15%">Magenta<br/>Magenta %</th>
                                                    <th style="text-align: center; min-width: 15%">Amarillo<br/>Amrillo %</th>                                            
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php
                                                $query = $catalogo->obtenerLista("SELECT DISTINCT(cie.NoSerie) AS NoSerie,e.NoParte AS NoParteEquipo,e.Modelo,
                                                                                    (SELECT ke.IdTipoServicio FROM k_equipocaracteristicaformatoservicio AS ke WHERE ke.NoParte = cie.NoParteEquipo ORDER BY ke.IdTipoServicio ASC LIMIT 1) AS tipoFormato
                                                                                    FROM k_anexoclientecc AS kacc LEFT JOIN c_inventarioequipo AS cie ON cie.IdAnexoClienteCC = kacc.IdAnexoClienteCC LEFT JOIN c_centrocosto AS cc ON cc.ClaveCentroCosto = kacc.CveEspClienteCC LEFT JOIN k_serviciogimgfa AS ks ON ks.IdKServiciogimgfa = cie.IdKServiciogimgfa LEFT JOIN c_centrocosto AS cc2 ON cc2.ClaveCentroCosto = ks.ClaveCentroCosto
                                                                                    LEFT JOIN c_equipo AS e ON e.NoParte = cie.NoParteEquipo WHERE !ISNULL(cie.NoSerie) AND ((kacc.CveEspClienteCC = '$claveLocalidad' AND ISNULL(cie.IdKServiciogimgfa)) OR (!ISNULL(cie.IdKServiciogimgfa) && ks.ClaveCentroCosto = '$claveLocalidad')) ORDER BY NoSerie DESC");
                                                $contador = 0;
                                                while ($rs = mysql_fetch_array($query)) {
                                                    $checkSerie = "";
                                                    $habilitar = "disabled";
                                                    $ckN = "";
                                                    $ckC = "";
                                                    $ckM = "";
                                                    $ckA = "";
                                                    if (in_array($rs["NoSerie"], $arrayPedidoNoSerie)) {
                                                        $checkSerie = "checked";
                                                        $mostrarInput = "";
                                                        $habilitar = "";
                                                        $queryLecturas = $catalogo->obtenerLista("SELECT lt.ContadorBN,lt.ContadorCL,lt.NivelTonNegro,
                                                                                                    lt.NivelTonCian,lt.NivelTonMagenta,lt.NivelTonAmarillo,lt.ContadorBNA,lt.ContadorCLA,lt.NivelTonNegroA,
                                                                                                    lt.NivelTonCianA,lt.NivelTonMagentaA,lt.NivelTonAmarilloA ,lt.FechaA
                                                                                                    FROM c_lecturasticket lt WHERE lt.ClvEsp_Equipo='" . $rs["NoSerie"] . "'  AND lt.fk_idticket='$idTicket'");
                                                        while ($rs3 = mysql_fetch_array($queryLecturas)) {
                                                            $contadorNegroAnterior = $rs3['ContadorBNA'];
                                                            $contadorColorAnterior = $rs3['ContadorCLA'];
                                                            $nivelNegroAnterior = $rs3['NivelTonNegroA'];
                                                            $nivelCianAnterior = $rs3['NivelTonCianA'];
                                                            $nivelMagentaAnterior = $rs3['NivelTonMagentaA'];
                                                            $nivelAmarilloAnterior = $rs3['NivelTonAmarilloA'];
                                                            $fechaContadorAnterior = $rs3['FechaA'];
                                                            $contadorNegro = $rs3['ContadorBN'];
                                                            $contadorColor = $rs3['ContadorCL'];
                                                            $nivelNegro = $rs3['NivelTonNegro'];
                                                            $nivelCian = $rs3['NivelTonCian'];
                                                            $nivelMagenta = $rs3['NivelTonMagenta'];
                                                            $nivelAmarillo = $rs3['NivelTonAmarillo'];
                                                        }
                                                        $consultaPedido = "SELECT c.NoParte, c.Modelo, c.Descripcion,dn.NoSerieEquipo
                                                                            FROM c_notaticket nt,k_detalle_notarefaccion dn,c_componente c
                                                                            WHERE nt.IdNotaTicket=dn.IdNota AND dn.Componente=c.NoParte AND nt.IdTicket='$idTicket' AND dn.NoSerieEquipo='" . $rs['NoSerie'] . "'";
                                                        //                                                                "SELECT c.NoParte, c.Modelo, c.Descripcion
//                                                                            FROM c_equipo e, k_equipocomponentecompatible cp, c_componente c, c_ticket t, c_notaticket nt, k_nota_refaccion nr
//                                                                            WHERE e.Modelo = '" . $rs["Modelo"] . "' AND cp.NoParteEquipo = e.NoParte AND c.NoParte = cp.NoParteComponente
//                                                                            AND nr.NoParteComponente = c.NoParte AND nr.IdNotaTicket = nt.IdNotaTicket AND t.IdTicket = nt.IdTicket AND nt.IdEstatusAtencion = 67
//                                                                            AND t.IdTicket = '$idTicket' AND nr.NoSerieEquipo='" . $rs['NoSerie'] . "'";
                                                        $queryPedido = $catalogo->obtenerLista($consultaPedido);
                                                        while ($rs2 = mysql_fetch_array($queryPedido)) {

                                                            $buscarA = " Y";
                                                            $buscarM = " M";
                                                            $buscarC = " C";
                                                            $posA = strpos($rs2['Modelo'], $buscarA);
                                                            $posM = strpos($rs2['Modelo'], $buscarM);
                                                            $posC = strpos($rs2['Modelo'], $buscarC);
                                                            if ($posA !== FALSE) {
                                                                $ckA = "checked";
                                                            }
                                                            if ($posM !== FALSE) {
                                                                $ckM = "checked";
                                                            }
                                                            if ($posC !== FALSE) {
                                                                $ckC = "checked";
                                                            }
                                                            if ($posA === FALSE && $posM === FALSE && $posC === FALSE) {
                                                                $ckN = "checked";
                                                            }
                                                        }
                                                    } else {
                                                        $mostrarInput = "display: none;";
                                                    }
                                                    echo "<tr>";
                                                    echo "<td align='center' scope='row' style='font-size:11px'><input type='checkbox' name='activar_$contador' id='activar_$contador' onclick='incidenciaByTicket($contador, \"" . $rs['NoSerie'] . "\", \"" . $rs['tipoFormato'] . "\")' $checkSerie $desactivarCheckPedido/></td>";
                                                    echo "<td align='center' scope='row' style='font-size:11px'>" . $rs['NoSerie'] . "<input type='hidden' name='txtNoSerieE_$contador' id='txtNoSerieE_$contador' value='" . $rs['NoSerie'] . "'/><br/>";
                                                    echo "Anterior: <input type='text' name='txtContadorNegroAnterior_$contador' id='txtContadorNegroAnterior_$contador' style='width: 80px; $mostrarInput' readonly value='$contadorNegroAnterior'/><input type='hidden' name='txtfechaAnterior_$contador' id='txtfechaAnterior_$contador' style='width: 80px;' value='$fechaContadorAnterior'/><br/>";
                                                    echo "Actual: <input type='text' name='txtContadorNegro_$contador' id='txtContadorNegro_$contador' style='width: 80px; $mostrarInput' value='$contadorNegro'/></td>";
                                                    echo "<td align='center' scope='row' style='font-size:11px'>" . $rs['Modelo'] . "<input type='hidden' name='txtModeloE_$contador' id='txtModeloE_$contador' value='" . $rs['Modelo'] . "'/><br/>"; //verificar componentes compatibles

                                                    $queryCompatibles = $catalogo->obtenerLista("SELECT c.NoParte,c.Modelo,c.Descripcion FROM c_equipo e,c_componente c,k_equipocomponentecompatible ec WHERE e.NoParte=ec.NoParteEquipo AND c.NoParte=ec.NoParteComponente AND e.Modelo='" . $rs['Modelo'] . "'");
                                                    while ($rs1 = mysql_fetch_array($queryCompatibles)) {
                                                        $tonerNegro = "";
                                                        $tonerCian = "";
                                                        $tonerMagenta = "";
                                                        $tonerAmarillo = "";
                                                        $buscar3 = " Y";
                                                        $buscar2 = " M";
                                                        $buscar1 = " C";
                                                        $pos1 = strpos($rs1['Modelo'], $buscar1);
                                                        $pos2 = strpos($rs1['Modelo'], $buscar2);
                                                        $pos3 = strpos($rs1['Modelo'], $buscar3);
                                                        if ($pos1 !== FALSE) {
                                                            $tonerCian = $rs1['NoParte'];
                                                            echo "<input type='hidden' name='txtTonerCian$contador' id='txtTonerCian$contador' value='" . $tonerCian . "'/>";
                                                        }
                                                        if ($pos2 !== FALSE) {
                                                            $tonerMagenta = $rs1['NoParte'];
                                                            echo "<input type='hidden' name='txtTonerMagenta$contador' id='txtTonerMagenta$contador' value='" . $tonerMagenta . "'/>";
                                                        }
                                                        if ($pos3 !== FALSE) {
                                                            $tonerAmarillo = $rs1['NoParte'];
                                                            echo "<input type='hidden' name='txtTonerAmarillo$contador' id='txtTonerAmarillo$contador' value='" . $tonerAmarillo . "'/>";
                                                        }
                                                        if ($pos1 === FALSE && $pos2 === FALSE && $pos3 === FALSE) {
                                                            $tonerNegro = $rs1['NoParte'];
                                                            echo "<input type='hidden' name='txtTonerNegro$contador' id='txtTonerNegro$contador' value='" . $tonerNegro . "'/>";
                                                        }
                                                    }
                                                    if ($rs['tipoFormato'] != "1") {
                                                        echo "</td>";
                                                    } else {
                                                        echo "<input type='text' name='txtContadorColorAnterior_$contador' id='txtContadorColorAnterior_$contador' style='width: 80px; $mostrarInput' readonly value='$contadorColorAnterior'/><br/>";
                                                        echo "<input type='text' name='txtContadorColor_$contador' id='txtContadorColor_$contador' style='width: 80px; $mostrarInput'value='$contadorColor' /></td>";
                                                    }
                                                    echo "<td align='center' scope='row' style='font-size:11px'><input type='checkbox' name='ckbNegro_$contador' id='ckbNegro_$contador' $habilitar $ckN $desactivarCheckPedido/><br/>";
                                                    echo "<input type='text' name='txtNivelNegroAnterior_$contador' id='txtNivelNegroAnterior_$contador' style='width: 50px; $mostrarInput' readonly value='$nivelNegroAnterior'/><br/>";
                                                    echo "<input type='text' name='txtNivelNegro_$contador' id='txtNivelNegro_$contador' style='width: 50px; $mostrarInput' value='$nivelNegro' /></td>";
                                                    if ($rs['tipoFormato'] != "1") {
                                                        echo "<td align='center' scope='row' style='font-size:11px'></td>";
                                                        echo "<td align='center' scope='row' style='font-size:11px'></td>";
                                                        echo "<td align='center' scope='row' style='font-size:11px'></td>";
                                                    } else {
                                                        echo "<td align='center' scope='row' style='font-size:11px'><input type='checkbox' name='ckbCian_$contador' id='ckbCian_$contador' $habilitar $ckC $desactivarCheckPedido/><br/>";
                                                        echo "<input type='text' name='txtNivelCianAnterior_$contador' id='txtNivelCianAnterior_$contador' style='width: 50px; $mostrarInput ' readonly value='$nivelCianAnterior'/><br/>";
                                                        echo "<input type='text' name='txtNivelCian_$contador' id='txtNivelCian_$contador' style='width: 50px; $mostrarInput'value='$nivelCian' /></td>";
                                                        echo "<td align='center' scope='row' style='font-size:11px'><input type='checkbox' name='ckbMagenta_$contador' id='ckbMagenta_$contador' $habilitar $ckM $desactivarCheckPedido/><br/>";
                                                        echo "<input type='text' name='txtNivelMagentaAnterior_$contador' id='txtNivelMagentaAnterior_$contador' style='width: 50px; $mostrarInput' readonly value='$nivelMagentaAnterior' /><br/>";
                                                        echo "<input type='text' name='txtNivelMagenta_$contador' id='txtNivelMagenta_$contador'style='width: 50px; $mostrarInput' value='$nivelMagenta'/></td>";
                                                        echo "<td align='center' scope='row' style='font-size:11px'><input type='checkbox' name='ckbAmarillo_$contador' id='ckbAmarillo_$contador' $habilitar $ckA $desactivarCheckPedido/><br/>";
                                                        echo "<input type='text' name='txtNivelAmarilloAnterior_$contador' id='txtNivelAmarilloAnterior_$contador' style='width: 50px; $mostrarInput' readonly value='$nivelAmarilloAnterior'/><br/>";
                                                        echo "<input type='text' name='txtNivelAmarillo_$contador' id='txtNivelAmarillo_$contador' style='width: 50px; $mostrarInput' value='$nivelAmarillo'/></td>";
                                                    }
                                                    echo "</tr>";
                                                    $contador++;
                                                }
                                                ?>
                                            </tbody>
                                        </table>
                                    <?php } ?>
                                </div>
                            <?php } else if ($tipoReporte == "1") { ?>
                                <div id = "tabs-1" style = "background-color: #A4A4A4">
                                    <table style="width: 50%">
                                        <tr>
                                            <td>Cliente</td>
                                            <td>
                                                <select id="slcCliente" name="slcCliente" style="width: 300px" onchange="incidenciaClienteSuspendido(this.value)" class="filtro" <?php echo $drawList; ?>>
                                                    <option value="0">Seleccione un cliente</option>
                                                    <?php
                                                    $queryCliente = $catalogo->obtenerLista($consultaCliente);
                                                    while ($rs = mysql_fetch_array($queryCliente)) {
                                                        $s = "";
                                                        if ($claveCliente != "" && $claveCliente == $rs['ClaveCliente']) {
                                                            $nombreCliente = $rs['NombreRazonSocial'];
                                                            $s = "selected";
                                                        }
                                                        echo "<option value='" . $rs['ClaveCliente'] . "' $s>" . $rs['NombreRazonSocial'] . "</option>";
                                                    }
                                                    ?>
                                                </select></td>
                                        </tr>
                                        <tr>
                                            <td>Localidad</td>
                                            <td>
                                                <select id="slcLocalidad" name="slcLocalidad" style="width: 300px" class="filtro" onchange="MostrarEquipoLocalidad(this.value)" <?php echo $drawList; ?>>
                                                    <?php
                                                    if ($claveCliente != "") {
                                                        $queryCliente = $catalogo->obtenerLista("SELECT cc.ClaveCentroCosto,cc.Nombre FROM c_centrocosto cc WHERE cc.ClaveCliente='$claveCliente' AND cc.Activo=1 ORDER BY cc.Nombre;");
                                                        if (mysql_num_rows($queryCliente) == 1) {
                                                            while ($rs = mysql_fetch_array($queryCliente)) {
                                                                $s = "selected";
                                                                $claveLocalidad = $rs['ClaveCentroCosto'];
                                                                echo "<option value=" . $rs['ClaveCentroCosto'] . " " . $s . ">" . $rs['Nombre'] . "</option>";
                                                            }
                                                        } else {
                                                            echo " <option value='0'>Seleccione una localidad</option>";
                                                            while ($rs = mysql_fetch_array($queryCliente)) {
                                                                $s = "";
                                                                if ($claveLocalidad != "" && $claveLocalidad == $rs['ClaveCentroCosto'])
                                                                    $s = "selected";

                                                                echo "<option value=" . $rs['ClaveCentroCosto'] . " " . $s . ">" . $rs['Nombre'] . "</option>";
                                                            }
                                                        }
                                                    }
                                                    ?>
                                                </select>
                                            </td>
                                        </tr>
                                    </table>
                                    <?php if ($claveLocalidad != "") { ?>
                                        <br/><br/>
                                        <!--                                        <a href='#' onclick='ReportarEquipoNoExistente();
                                                                                        return false;' title='Detalle'  style="float: right; cursor: pointer;"> <img src='resources/images/Textpreview.png'/> </a>-->
                                        <table id="<?php echo $nombreTabla; ?>" class="tabla_datos" style="width: 100%">
                                            <thead>
                                                <tr>
                                                    <th style="text-align: center; min-width:10%">No Serie</th>
                                                    <th style="text-align: center; min-width: 25%">Modelo equipo</th>
                                                    <th style="text-align: center; min-width: 15%">Contador B/N</th>
                                                    <th style="text-align: center; min-width: 20%">Contador color</th>   
                                                    <th style="text-align: center; min-width: 20%">Reportar</th>   
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php
                                                $query = $catalogo->obtenerLista("SELECT DISTINCT(cie.NoSerie) AS NoSerie,e.NoParte AS NoParteEquipo,e.Modelo,
                                                                                    (SELECT ke.IdTipoServicio FROM k_equipocaracteristicaformatoservicio AS ke WHERE ke.NoParte = cie.NoParteEquipo ORDER BY ke.IdTipoServicio ASC LIMIT 1) AS tipoFormato
                                                                                    FROM k_anexoclientecc AS kacc LEFT JOIN c_inventarioequipo AS cie ON cie.IdAnexoClienteCC = kacc.IdAnexoClienteCC LEFT JOIN c_centrocosto AS cc ON cc.ClaveCentroCosto = kacc.CveEspClienteCC LEFT JOIN k_serviciogimgfa AS ks ON ks.IdKServiciogimgfa = cie.IdKServiciogimgfa LEFT JOIN c_centrocosto AS cc2 ON cc2.ClaveCentroCosto = ks.ClaveCentroCosto
                                                                                    LEFT JOIN c_equipo AS e ON e.NoParte = cie.NoParteEquipo WHERE !ISNULL(cie.NoSerie) AND ((kacc.CveEspClienteCC = '$claveLocalidad' AND ISNULL(cie.IdKServiciogimgfa)) OR (!ISNULL(cie.IdKServiciogimgfa) && ks.ClaveCentroCosto = '$claveLocalidad')) ORDER BY NoSerie DESC");
                                                $contador = 0;
                                                while ($rs = mysql_fetch_array($query)) {
                                                    $seleccionar = "";
                                                    if ($noSerie == $rs['NoSerie']) {
                                                        $seleccionar = "checked";
                                                        $queryLecturas = $catalogo->obtenerLista("SELECT lt.ContadorBN,lt.ContadorCL,lt.NivelTonNegro,
                                                                                                    lt.NivelTonCian,lt.NivelTonMagenta,lt.NivelTonAmarillo,lt.ContadorBNA,lt.ContadorCLA,lt.NivelTonNegroA,
                                                                                                    lt.NivelTonCianA,lt.NivelTonMagentaA,lt.NivelTonAmarilloA ,lt.FechaA
                                                                                                    FROM c_lecturasticket lt WHERE lt.ClvEsp_Equipo='" . $rs["NoSerie"] . "'  AND lt.fk_idticket='$idTicket'");
                                                        while ($rs3 = mysql_fetch_array($queryLecturas)) {
                                                            $contadorNegroAnterior = $rs3['ContadorBNA'];
                                                            $contadorColorAnterior = $rs3['ContadorCLA'];
                                                            $nivelNegroAnterior = $rs3['NivelTonNegroA'];
                                                            $nivelCianAnterior = $rs3['NivelTonCianA'];
                                                            $nivelMagentaAnterior = $rs3['NivelTonMagentaA'];
                                                            $nivelAmarilloAnterior = $rs3['NivelTonAmarilloA'];
                                                            $fechaContadorAnterior = $rs3['FechaA'];
                                                            $contadorNegro = $rs3['ContadorBN'];
                                                            $contadorColor = $rs3['ContadorCL'];
                                                            $nivelNegro = $rs3['NivelTonNegro'];
                                                            $nivelCian = $rs3['NivelTonCian'];
                                                            $nivelMagenta = $rs3['NivelTonMagenta'];
                                                            $nivelAmarillo = $rs3['NivelTonAmarillo'];
                                                        }
                                                    } else {
                                                        $contadorNegroAnterior = "";
                                                        $contadorColorAnterior = "";
                                                        $nivelNegroAnterior = "";
                                                        $nivelCianAnterior = "";
                                                        $nivelMagentaAnterior = "";
                                                        $nivelAmarilloAnterior = "";
                                                        $fechaContadorAnterior = "";
                                                        $contadorNegro = "";
                                                        $contadorColor = "";
                                                        $nivelNegro = "";
                                                        $nivelCian = "";
                                                        $nivelMagenta = "";
                                                        $nivelAmarillo = "";
                                                    }
                                                    echo "<tr>";
                                                    echo "<td align='center' scope='row' style='font-size:11px'>" . $rs['NoSerie'] . ""
                                                    . "<input type='hidden' name='txtNoSerieE_$contador' id='txtNoSerieE_$contador' style='width: 80px;' value='" . $rs['NoSerie'] . "'/></td>";
                                                    echo "<td align='center' scope='row' style='font-size:11px'>" . $rs['Modelo'] . ""
                                                    . "<input type='hidden' name='txtModeloE_$contador' id='txtModeloE_$contador' style='width: 80px;' value='" . $rs['Modelo'] . "'/>"
                                                    . "<input type='hidden' name='txtfechaAnterior_$contador' id='txtfechaAnterior_$contador' style='width: 80px;' readonly/>"
                                                    . "</td>";
                                                    echo "<td align='center' scope='row' style='font-size:11px'>"
                                                    . "<br/>Anterior<input type='text' name='txtContadorNegroAnterior_$contador' id='txtContadorNegroAnterior_$contador' style='width: 80px;' value='$contadorNegroAnterior' readonly/>"
                                                    . "<br/>actual<input type='text' name='txtContadorNegro_$contador' id='txtContadorNegro_$contador' style='width: 80px;' value='$contadorNegro'/></td>";
                                                    if ($rs['tipoFormato'] != "1") {
                                                        echo "<td align='center' scope='row' style='font-size:11px'></td>";
                                                    } else {
                                                        echo "<td align='center' scope='row' style='font-size:11px'>"
                                                        . "<br/><input type='text' name='txtContadorColorAnterior_$contador' id='txtContadorColorAnterior_$contador' style='width: 80px;' value='$contadorColorAnterior' readonly/>"
                                                        . "<br/><input type='text' name='txtContadorColor_$contador' id='txtContadorColor_$contador' style='width: 80px; ' value='$contadorColor'/></td>";
                                                    }
                                                    echo "<td align='center' scope='row' style='font-size:11px'><input type='radio' name='rdEquipoFalla' id='rdEquipoFalla' value='" . $contador . " / " . $rs['NoSerie'] . " / " . $rs['Modelo'] . "' onclick='incidenciaByTicketFalla($contador, \"" . $rs['NoSerie'] . "\", \"" . $rs['tipoFormato'] . "\")' $seleccionar  $desactivarRadioPedido/></td>";
                                                    echo "</tr>";
                                                    $contador++;
                                                }
                                                ?>
                                            </tbody>
                                        </table>
                                    <?php } ?>
                                </div>
                            <?php } ?>
                            <div id = "tabs-2" style = "background-color: #A4A4A4">
                                <fieldset >
                                    <legend>Cliente:</legend> 
                                    <table style="width: 100%;">
                                        <tr>
                                            <td>Cliente:<span class="obligatorio"> *</span></td>
                                            <td><input type="text" id="txtClaveClienteToner" name="txtClaveClienteToner" value="<?php echo $claveCliente; ?>" style='width: 250px'<?php echo $readCliente; ?> /></td>
                                            <!--<td></td><td><input type="checkbox" id="ckActualizarinfoToner" name="ckActualizarinfoToner" />Actualizar Información</td>-->
                                        </tr>
                                        <tr>
                                            <td>Nombre Empresa:</td>
                                            <td><input type="text" id="nombreEmpresaToner" name="nombreEmpresaToner" value="<?php echo $nombreCliente; ?>" style='width: 250px' <?php echo $readCliente; ?>/></td>
                                            <td></td><td></td>
                                        </tr>
                                        <tr>
                                            <td>Tipo de cliente</td>
                                            <td>
                                                <select id="tipoClienteToner" name="tipoClienteToner"  <?php echo $drawList; ?> disabled>
                                                    <option value="0">Seleccione tipo de cliente</option>
                                                    <?php
                                                    $queryEstado = $catalogo->getListaAlta("c_tipocliente", "Nombre");
                                                    while ($rs = mysql_fetch_array($queryEstado)) {
                                                        $s = "";
                                                        if ($tipoCliente == $rs['IdTipoCliente'])
                                                            $s = "selected";
                                                        echo "<option value='" . $rs['IdTipoCliente'] . "' $s>" . $rs['Nombre'] . "</option>";
                                                    }
                                                    ?> 
                                                </select>
                                            </td>
                                            <td></td><td></td>
                                        </tr>
                                        <tr>
                                            <td>Estatus de cobranza:</td>
                                            <td>
                                                <!--<input type="text" id="txtEstatusToner" name="txtEstatusToner" value="<?php echo $estatusCobranza; ?>" style='width: 250px' <?php echo $read; ?> />-->
                                                <select id="tipoestatusCobranza" name="tipoestatusCobranza"  <?php echo $drawList; ?> disabled>                                                           
                                                    <?php
                                                    $queryCobranza = $catalogo->getListaAlta("c_estatuscobranza", "Nombre");
                                                    while ($rs = mysql_fetch_array($queryCobranza)) {
                                                        $s = "";
                                                        if ($estatusCobranza == $rs['IdEstatusCobranza'])
                                                            $s = "selected";
                                                        echo "<option value='" . $rs['IdEstatusCobranza'] . "' $s>" . $rs['Nombre'] . "</option>";
                                                    }
                                                    ?> 
                                                </select>
                                            </td>
                                            <td></td><td></td>
                                        </tr>
                                        <tr>
                                            <td>Localidad:</td>
                                            <td><input type="text" id="localidadToner" name="localidadToner" value="<?php echo $nombreLocalidad; ?>" style='width: 250px'<?php echo $readCliente; ?> /></td>
                                            <td>Nombre TFS:</td>
                                            <td><input type="text" id="txtTfsToner" name="txtTfsToner" value="<?php echo $tfs; ?>" style='width: 250px' <?php echo $readCliente; ?> /></td>
                                        </tr>
                                    </table>
                                    <fieldset>
                                        <legend>Domicilio<span class="obligatorio"> *</span></legend> 
                                        <table style="width: 100%">
                                            <tr>
                                                <td>Tipo de domicilio:<span class="obligatorio"> *</span></td><td><input type="text" id="txtDomicilioToner" name="txtDomicilioToner" value="<?php echo $tipoDomicilio; ?>" style='width: 180px' <?php echo $readCliente; ?> /></td>
                                                <td>Zona:</td>
                                                <td>
    <!--                                                            <input type="text" id="txtZonaToner" name="txtZonaToner" value="" style='width: 180px' />
                                                           <input type="text" id="txtZonaFalla" name="txtZonaFalla" value="<?php echo $estatusCobranza; ?>" style='width: 180px' <?php echo $read; ?> />-->
                                                    <select id="txtZonaToner" name="txtZonaToner" style="width:180px" disabled>
                                                        <option value="0">Seleccione zona</option>
                                                        <?php
                                                        $queryZona = $catalogo->getListaAlta("c_zona", "NombreZona");
                                                        while ($rs = mysql_fetch_array($queryZona)) {
                                                            $s = "";
                                                            if ($idZona == $rs['ClaveZona'])
                                                                $s = "selected";
                                                            echo "<option value='" . $rs['ClaveZona'] . "' $s>" . $rs['NombreZona'] . "</option>";
                                                        }
                                                        ?> 
                                                    </select>
                                                </td>
                                                <td>Ubicación:</td>
                                                <td>
                                                    <select id="sltUbicacionToner" name="sltUbicacionToner" style="width:180px" <?php echo $drawList; ?> >
                                                        <?php
                                                        $queryUbicacion = $catalogo->getListaAlta("c_ubicacionticket", "Nombre");
                                                        while ($rs = mysql_fetch_array($queryUbicacion)) {
                                                            $s = "";
                                                            if ($Ubicaion == $rs['IdUbicacion']) {
                                                                $s = "selected";
                                                                $unicacionTxt = $rs['IdUbicacion'];
                                                            } else {
                                                                $unicacionTxt = "1";
                                                            }
                                                            echo "<option value='" . $rs['IdUbicacion'] . "' $s>" . $rs['Nombre'] . "</option>";
                                                        }
                                                        ?> 
                                                    </select>
                                                    <!--<input type="hidden" id="sltUbicacionToner" name="sltUbicacionToner" value="<?php echo $unicacionTxt; ?>" style='width: 180px' <?php echo $unicacionTxt; ?> />-->
                                                </td>
                                            </tr>
                                            <tr>
                                                <td>Calle:<span class="obligatorio"> *</span></td><td><input type="text" id="txtCalleToner" name="txtCalleToner" value="<?php echo $calle; ?>" style='width: 180px' <?php echo $readCliente; ?> /></td>
                                                <td>No.exterior:<span class="obligatorio"> *</span></td><td><input type="text" id="txtNoExteriorToner" name="txtNoExteriorToner" value="<?php echo $nExterior; ?>" style='width: 180px' <?php echo $readCliente; ?> /></td>
                                                <td>No. interior:</td><td><input type="text" id="txtNoInteriorToner" name="txtNoInteriorToner" value="<?php echo $nInterior; ?>" style='width: 180px' <?php echo $readCliente; ?> /></td>
                                            </tr>
                                            <tr>
                                                <td>Colonia:<span class="obligatorio"> *</span></td><td><input type="text" id="txtColoniaToner" name="txtColoniaToner" value="<?php echo $colonia; ?>" style='width: 180px' <?php echo $readCliente; ?> /></td>
                                                <td>Ciudad:<span class="obligatorio"> *</span></td><td><input type="text" id="txtCiudadToner" name="txtCiudadToner" value="<?php echo $ciudad; ?>" style='width: 180px' <?php echo $readCliente; ?> /></td>
                                                <td>Estado:<span class="obligatorio"> *</span></td>
                                                <td>
                                                    <select id="sltEstadoToner" name="sltEstadoToner" style="width:180px" <?php echo $drawList ?> disabled>

                                                        <?php
                                                        if ($estadoLocal == "")
                                                            echo "<option value='0'>Selecciona un estado</option>";
                                                        else
                                                            echo "<option value='$estadoLocal'>$estadoLocal</option>";
                                                        $queryEstadoLocal = $catalogo->getListaAlta("c_ciudades", "Ciudad");
                                                        while ($rs = mysql_fetch_array($queryEstadoLocal)) {
                                                            $s = "";
                                                            if ($estadoLocal == $rs['Ciudad'])
                                                                $s = "selected";
                                                            echo "<option value='" . $rs['Ciudad'] . "' $s>" . $rs['Ciudad'] . "</option>";
                                                        }
                                                        ?> 
                                                    </select>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td>Delegación:<span class="obligatorio"> *</span></td><td><input type="text" id="txtDelegacionToner" name="txtDelegacionToner" value="<?php echo $delegacion; ?>" style='width: 180px'<?php echo $readCliente; ?>  /></td>
                                                <td>C.P:<span class="obligatorio"> *</span></td><td><input type="text" id="txtCpToner" name="txtCpToner" value="<?php echo $cp; ?>" style='width: 180px'  <?php echo $readCliente; ?>/></td>
                                                <td></td><td></td>
                                            </tr>
                                        </table>
                                    </fieldset>
                                    Contacto de la localidad
                                    <table style="width: 100%">                                       
                                        <tr>
                                            <td>Nombre:<span class="obligatorio"> *</span></td>
                                            <td><input type="text" id="txtNombreCFalla" name="txtNombreCToner" value="<?php echo $nombreContacto; ?>" style='width: 250px' <?php echo $readCliente; ?>/></td>
                                        </tr>
                                        <tr>
                                            <td>Teléfono:<span class="obligatorio"> *</span></td>
                                            <td><input type="text" id="txtTelefonoCFalla" name="txtTelefonoCToner" value="<?php echo $telefono; ?>" style='width: 250px' <?php echo $readCliente; ?> /></td>
                                        </tr>
                                        <tr>
                                            <td>Celular:</td>
                                            <td><input type="text" id="txtCelularCFalla" name="txtCelularCToner" value="<?php echo $celular; ?>" style='width: 250px' <?php echo $readCliente; ?> /></td>
                                        </tr>
                                        <tr>
                                            <td>Correo electrónico:</td>
                                            <td><input type="text" id="txtCorreoCFalla" name="txtCorreoCToner" value="<?php echo $correoE; ?>" style='width: 250px' <?php echo $readCliente; ?> /></td>
                                        </tr>
                                    </table>
                                </fieldset>
                            </div>
                        </div>
                    <?php } ?>
                </div>
                <div>
                    <fieldset>
                        <legend>Datos del ticket</legend>   
                        <fieldset>
                            <tr
                        <legend>Contacto responsable del ticket</legend>
                        <table style='width: 100%'>
                            <tr>
                            <tr>
                                <td><input type="radio" name="rdContacto" id="rdContacto" value="1" onclick="mostrarTipoContacto(1)"  <?php echo $desactivarRadio ?>/>Nuevo contacto </td>
                                <td><input type="radio" name="rdContacto" id="rdContacto" value="0" onclick="mostrarTipoContacto(0)" checked="checked" <?php echo $desactivarRadio ?>/>Contacto existente </td>
                            </tr>
                            <tr>

                                <td>Nombre<span class="obligatorio"> *</span></td>
                                <td colspan="5">
                                    <div id="contactoExistente">
                                        <select id="txtNombre" name="txtNombre"  onchange="DatosContacto(this.value);" style='width: 655px' <?php echo $drawList; ?>>
                                            <option value="0">Seleccione contacto</option>
                                            <?php
                                            if ($claveLocalidad != "") {
                                                $queryContactos = $catalogo->obtenerLista("SELECT ct.IdContacto,ct.Nombre,ct.Telefono,ct.Celular,ct.CorreoElectronico,(SELECT tc.Nombre FROM c_tipocontacto tc WHERE ct.IdTipoContacto=tc.IdTipoContacto) AS tipoContacto
                                                                                        FROM c_contacto ct WHERE ct.ClaveEspecialContacto='$claveLocalidad' OR ct.ClaveEspecialContacto='$claveCliente'");
                                                while ($rs = mysql_fetch_array($queryContactos)) {
                                                    $s = "";
                                                    if ($nombreResp == $rs['Nombre'])
                                                        $s = "selected";
                                                    echo "<option value='" . $rs["Nombre"] . " / " . $rs["Telefono"] . " / " . $rs["Celular"] . " / " . $rs["CorreoElectronico"] . "' $s>" . $rs['Nombre'] . "   (" . $rs['tipoContacto'] . ")" . "</option>";
                                                }
                                            }
                                            ?>
                                        </select>
                                    </div>
                                    <div id="contactoNuevo">
                                        <input type="text" id="txtNombre1" name="txtNombre1" value="<?php echo $nombreResp; ?>" style='width: 680px; display: none;' <?php echo $read; ?>/>
                                    </div>

            <!--//                                    <input type="text" id="txtNombre" name="txtNombre" value="<?php echo $nombreResp; ?>" style='width: 680px'>-->
                                </td>
                            </tr>
                            <tr>
                                <td><label for="txtTelefono1">Telefono 1:</label></td>
                                <td colspan="3"><input type="text" id="txtTelefono1" name="txtTelefono1" value="<?php echo $telefono1Resp; ?>" style='width: 250px'  <?php echo $read; ?> readonly/></td>
                                <td><label for="txtExtencion1">Extención 1:</label></td>
                                <td><input type="text" id="txtExtencion1" name="txtExtencion1" value="<?php echo $Extencio2Resp; ?>" style='width: 250px'  <?php echo $read; ?> readonly/></td>
                            </tr>
                            <tr>
                                <td><label for="txtTelefono2">Telefono 2:</label></td>
                                <td colspan="3"><input type="text" id="txtTelefono2" name="txtTelefono2" value="<?php echo $telefono2Atencion; ?>" style='width: 250px'  <?php echo $read; ?> readonly/></td>
                                <td><label for="txtExtencion2">Extención 2:</label></td>
                                <td><input type="text" id="txtExtencion2" name="txtExtencion2" value="<?php echo $Extencio2Resp; ?>" style='width: 250px' <?php echo $read; ?> readonly/></td>
                            </tr>
                            <tr>
                                <td><label for="txtCelular">Celular</label></td>
                                <td colspan="3"><input type="text" id="txtCelular" name="txtCelular" value="<?php echo $celularResp; ?>" style='width: 250px' <?php echo $read; ?> readonly/></td> 
                            </tr>
                            <tr>
                                <td><label for="correoElectronico">Correo electrónico</label><span class="obligatorio"> *</span></td>
                                <td colspan="3"><input type="text" id="correoElectronico" name="correoElectronico" value="<?php echo $correoResp; ?>" style='width: 250px' <?php echo $read; ?> readonly/><div id="errorCorreoResp" <?php echo $read; ?>/></div></td> 
                            </tr>
                            <tr>
                                <td><label for="lstHA">Horario de atención:</label></td>
                                <td colspan="3">
                                    <?php
                                    $hinicioResp = "";
                                    $minicioResp = "";
                                    $tinicioResp = "";
                                    list($hinicioResp) = explode(",", $horarioReponsableInicio);
                                    ?>
                                    <b>Inicio:</b>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Hora:
                                    <select id='lstHR' name='lstHR' <?php echo $drawList; ?>>
                                        <?php
                                        for ($x = 1; $x <= 12; $x++) {
                                            $s = "";
                                            if ($x == "9" || $hinicioResp == $x)
                                                $s = "selected";
                                            echo "<option value='" . $x . "' $s>" . $x . "</option> ";
                                        }
                                        ?>
                                    </select>
                                    <select id='lstMR' name='lstMR' <?php echo $drawList; ?>>
                                        <option value="00">00</option>
                                        <option value="15">15</option>
                                        <option value="30">30</option>
                                        <option value="45">45</option>
                                    </select>
                                    <select id='lstTA' name='lstTA' <?php echo $drawList; ?>>
                                        <option value="am">am</option>
                                        <option value="pm">pm</option>
                                    </select>
                                </td>
                                <td><label for="lstFinR"><b>Fin:</b>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Hora</label></td>
                                <td>

                                    <select id='lstFinHR' name='lstFinHR' <?php echo $drawList; ?>>
                                        <?php
                                        $hfinResp = "";
                                        $mfinResp = "";
                                        $tfinResp = "";
                                        list($hfinResp) = explode(",", $horarioResponsableFin);
                                        for ($x = 1; $x <= 12; $x++) {
                                            $s = "";
                                            if ($x == "6" || $hfinResp == $x)
                                                $s = "selected";
                                            echo "<option value='" . $x . "' $s>" . $x . "</option> ";
                                        }
                                        ?>
                                    </select>
                                    <select id='lstFinMR' name='lstFinMR' <?php echo $drawList; ?>>
                                        <option value="00" selected>00</option>
                                        <option value="15">15</option>
                                        <option value="30">30</option>
                                        <option value="45">45</option>
                                    </select>
                                    <select id='lstFinTR' name='lstFinTR' <?php echo $drawList; ?>>
                                        <option value="am">am</option>
                                        <option value="pm" selected>pm</option>
                                    </select>
                                </td>
                            </tr>
                        </table>
                    </fieldset>    
                    <?php if ($idTicket == "") { ?>
                        <a href='#' onclick='CopiarDatosContacto();
                                return false;' title='Copiar datos de contacto' >Copiar datos de contacto responsable de ticket o centro de atención</a>
                       <?php } ?>
                    <fieldset>
                        <legend>Contacto de atención del ticket</legend>   
                        <table style='width: 100%'>
                            <tr>
                                <td><label for="txtNombreAtencion">Nombre</label></td>
                                <td colspan="5"><input type="text" id="txtNombreAtencion" name="txtNombreAtencion" value="<?php echo $nombreAtencion; ?>" style='width: 650px' <?php echo $read; ?>/></td>
                            </tr>
                            <tr>
                                <td><label for="txtTelefono1Atencion">Telefono 1:</label></td>
                                <td colspan="3"><input type="text" id="txtTelefono1Atencion" name="txtTelefono1Atencion" value="<?php echo $telefono1Atencion; ?>" style='width: 250px'<?php echo $read; ?>/></td>

                                <td><label for="txtExtencion1Atencion">Extención 2:</label></td>
                                <td><input type="text" id="txtExtencion1Atencion" name="txtExtencion1Atencion" value="<?php echo $Extencio1Atencion; ?>" style='width: 250px' <?php echo $read; ?>/></td>
                            </tr>
                            <tr>
                                <td><label for="txtTelefono2Atencion">Telefono 2:</label></td>
                                <td colspan="3"><input type="text" id="txtTelefono2Atencion" name="txtTelefono2Atencion" value="<?php echo $telefono2Atencion; ?>" style='width: 250px' <?php echo $read; ?>/></td>

                                <td><label for="txtExtencion2Atencion">Extención 1:</label></td>
                                <td><input type="text" id="txtExtencion2Atencion" name="txtExtencion2Atencion" value="<?php echo $Extencio2Atencion; ?>" style='width: 250px' <?php echo $read; ?>/></td>
                            </tr>
                            <tr>
                                <td><label for="txtCelularAtencion">Celular</label></td>
                                <td colspan="3"><input type="text" id="txtCelularAtencion" name="txtCelularAtencion" value="<?php echo $celularAtencion; ?>" style='width: 250px' <?php echo $read; ?>/></td> 
                            </tr>
                            <tr>
                                <td><label for="txtCorreoElectronico">Correo electrónico</label></td>
                                <td colspan="3"><input type="text" id="txtCorreoElectronico" name="txtCorreoElectronico" value="<?php echo $correoAtencion; ?>" style='width: 250px' <?php echo $read; ?>/><div id='errorCorreoAtencion'></div></td> 
                            </tr>
                            <tr>
                                <td><label for="lstHA">Horario de atención:</label></td>
                                <td colspan="3">
                                    <b>Inicio:</b>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Hora:
                                    <select id='lstHA' name='lstHA' <?php echo $drawList; ?>>
                                        <?php
                                        for ($x = 1; $x <= 12; $x++) {
                                            $s = "";
                                            if ($x == "9")
                                                $s = "selected";
                                            echo "<option value='" . $x . "' $s>" . $x . "</option> ";
                                        }
                                        ?>
                                    </select>
                                    <select id='lstMA' name='lstMA' <?php echo $drawList; ?>>
                                        <option value="00" selected>00</option>
                                        <option value="15">15</option>
                                        <option value="30">30</option>
                                        <option value="45">45</option>
                                    </select>
                                    <select id='lstTA' name='lstTA' <?php echo $drawList; ?>>
                                        <option value="am" selected>am</option>
                                        <option value="pm">pm</option>
                                    </select>
                                </td>
                                <td><label for="lstFinA"><b>Fin:</b>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Hora</label></td>
                                <td>
                                    <select id='lstFinHA' name='lstFinHA' <?php echo $drawList; ?>>
                                        <?php
                                        for ($x = 1; $x <= 12; $x++) {
                                            $s = "";
                                            if ($x == "6")
                                                $s = "selected";
                                            echo "<option value='" . $x . "' $s>" . $x . "</option> ";
                                        }
                                        ?>
                                    </select>
                                    <select id='lstFinMA' name='lstFinMA' <?php echo $drawList; ?>>
                                        <option value="00" selected>00</option>
                                        <option value="15">15</option>
                                        <option value="30">30</option>
                                        <option value="45">45</option>
                                    </select>
                                    <select id='lstFinTA' name='lstFinTA' <?php echo $drawList; ?>>
                                        <option value="am">am</option>
                                        <option value="pm" selected>pm</option>
                                    </select>
                                </td>
                            </tr>
                        </table>
                    </fieldset>
                    <table style="width:  100%">
                        <tr>
                            <td>No. ticket cliente:</td><td><input type="text" id="txtNoTicketClienteGral" name="txtNoTicketClienteGral" value='<?php echo $ticketCliente ?>' <?php echo $read; ?>/></td>
                            <td>No. ticket distribuidor:</td><td><input type="text" id="txtNoTicketDistribucionGral" name="txtNoTicketDistribucionGral" value='<?php echo $ticketDistribucion; ?>' <?php echo $read; ?>/></td>
                        </tr>   
                        <tr>
                            <td>Descripción del reporte:</td>
                            <td style="width: 85%" colspan="3"><textarea style="width: 100%; height: 150px;" id='descripcion' name='descripcion' <?php echo $read; ?>><?php echo $descripcion; ?></textarea></td>
                        </tr>
                        <tr>
                            <td>Observaciones adicionales:</td>
                            <td style="width: 85%" colspan="3"><textarea style="width: 100%;height: 150px;" id='observacion' name='observacion' <?php echo $read; ?>><?php echo $observacion; ?></textarea></td>
                        </tr>   
                        <tr>
                            <td>Área de atención<span class="obligatorio"> *</span>:</td>
                            <td>
                                <select id="areaAtencionGral" name="areaAtencionGral" style="width: 300px" <?php echo $drawList; ?>>

                                    <?php
                                    if ($tipoReporte == "15") {
                                        $queryArea = "SELECT  e.IdEstado,e.Nombre  FROM c_estado e,c_flujo f,k_flujoestado fe WHERE e.IdEstado=fe.IdEstado AND f.IdFlujo=fe.IdFlujo AND f.IdFlujo=3";
                                    } else if ($tipoReporte == "1") {
                                        echo "<option value='0'>Seleccione el area de atención</option>";
                                        $queryArea = "SELECT  e.IdEstado,e.Nombre  FROM c_estado e,c_flujo f,k_flujoestado fe WHERE e.IdEstado=fe.IdEstado AND f.IdFlujo=fe.IdFlujo AND f.IdFlujo=2";
                                    } else {
                                        echo "<option value='0'>Seleccione el area de atención</option>";
                                    }
                                    $query = $catalogo->obtenerLista($queryArea);
                                    while ($rs = mysql_fetch_array($query)) {
                                        $s = "";
                                        if ($areaAtencion != "" && $areaAtencion == $rs['IdEstado'])
                                            $s = "selected";
                                        echo "<option value='" . $rs['IdEstado'] . "' $s>" . $rs['Nombre'] . "</option>";
                                    }
                                    ?>
                                </select>
                            </td>
                            <td></td><td></td>
                        </tr>   
                    </table>
                </fieldset>
            </div>
            <?php if ($detalle != "1") { ?>              
                <input type = "submit" id = "botonGuardar" name = "botonGuardar" class = "boton" value = "Guardar"/>
                <?php if ($botonCancelar != "1") { ?>  
                    <input type = "submit" class = "boton" value = "Cancelar" onclick = "cambiarContenidos('<?php echo $pagina_listaRegresar; ?>');
                            return false;"/>
                       <?php }
                   } ?>
            <input type = "hidden" name = "idTicket" id = "idTicket" value = "<?php echo $idTicket; ?>" />
            <input type = "hidden" name = "nombreCC" id = "idTicket" value = "<?php echo $nombreLocalidad; ?>" />
            <input type = "hidden" name = "nombreCliente" id = "idTicket" value = "<?php echo $nombreCliente; ?>" />
            <input type = "hidden" name = "filaSeleccionada" id = "filaSeleccionada" value = ""/>
            <input type = "hidden" name = "tipoUsuario" id = "tipoUsuario" value = "<?php echo $idPuesto; ?>"/>

        </form>
    </div>
    <div id = "dialog" ></div>
    <?php if ($detalle == "1" || $detalle == "0") { ?>
        <?php if ($detalle != "1") { ?>
            <img class="imagenMouse" src="resources/images/add.png" title="Nueva nota" onclick='AgregarNotaTicket("nota/AgregarNota.php?idTicket1=<?php echo $idTicket; ?>", "<?php echo $tipoReporte ?>");' style="float: right; cursor: pointer;" />  
    <?php } ?>
        <table id="tAlmacen2" class="tabla_datos" style="width: 100%">
            <thead>
                <tr>
                    <th style="text-align: center; min-width:10%">Fecha y Hora</th>
                    <th style="text-align: center; min-width: 25%">Diagnostico</th>
                    <th style="text-align: center; min-width: 15%">Estatus de Atención</th>
                    <th style="text-align: center; min-width: 20%">Tipo solución</th>
                    <th style="text-align: center; min-width: 15%">Técnico</th>
                    <th style="text-align: center; min-width: 15%">Detalle</th>
                </tr>
            </thead>
            <tbody>
                <?php
                /* Inicializamos la clase */
                $query = $catalogo->obtenerLista("SELECT nt.IdNotaTicket,nt.FechaHora,nt.DiagnosticoSol,e.Nombre AS estatus,nt.UsuarioUltimaModificacion
                                                    FROM c_notaticket nt LEFT JOIN c_estado e ON nt.IdEstatusAtencion=e.IdEstado WHERE nt.IdTicket='$idTicket' ORDER BY nt.FechaHora DESC");
                while ($rs = mysql_fetch_array($query)) {
                    echo "<tr>";
                    echo "<td align='center' scope='row' style='font-size:11px'>" . $rs['FechaHora'] . "</td>";
                    echo "<td align='center' scope='row' style='font-size:11px'>" . $rs['DiagnosticoSol'] . "</td>";
                    echo "<td align='center' scope='row' style='font-size:11px'>" . $rs['estatus'] . "</td>";
                    echo "<td align='center' scope='row' style='font-size:11px'></td>";
                    echo "<td align='center' scope='row' style='font-size:11px'>" . $rs['UsuarioUltimaModificacion'] . "</td>";
                    echo "<td align='center' scope='row' style='font-size:11px'>";
                    ?>
                <a href='#' onclick='mostrarDetalleNota("mesa/detalleNota.php", "<?php echo $rs['IdNotaTicket'] ?>");
                        return false;' title='Detalle' > <img src='resources/images/Textpreview.png'/> </a>
                   <?php
                   echo "</td></tr>";
               }
               ?>
        </tbody>
    </table>
    <?php
}
?>
<div id="detalleNota">
</div>
<div id="correoExistencia">
</div>
</body>
</html>