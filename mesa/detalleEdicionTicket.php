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
$tipoReporteMostrar = "";
$pagina_listaRegresar = "mesa/lista_ticket.php";
$estadoTicket = "3";
$read = "";
$noSerie = "";
$Modelo = "";
$estatusTicket = "";
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
$claveEspecial = "";
$areaAtencion = "";
$idticket = "";
$ticketCliente = "";
$ticketDistribucion = "";
$descripcion = "";
$observacion = "";

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
$arrayPedidos = array();
$tipo = "";
//$contadorNegroNuevo = "";
//$contadorColorNuevo = "";
//$nivelNegroNuevo = "";
//$nivelCiaNuevo = "";
//$nivelMagentaNuevo = "";
//$nivelAmarilloNuevo = "";
$fechaContador = "";
$contadorNegro = "";
$contadorColor = "";
$nivelNegro = "";
$nivelCia = "";
$nivelMagenta = "";
$nivelAmarillo = "";
$anio = "";
$mes = "";
$dia = "";
$hora = "";
$NumContador = 0;
$editar = "1";
$whereCliente = "";
$fechaHoraLectura = "";
$horarioReponsableInicio = "";
$horarioResponsableFin = "";
$horarioAtencioFin = "";
$horarioAtencionFin = "";
$tipoServicio = "";
$noParteEquipo = "";
$detalle = "";
$fechaHoraTicket = "";
$usuarioTicket = "";
$fechaContadorAnterior = "";
if (isset($_POST["editar"]) && $_POST['editar'] != "") {
    $editar = $_POST['editar'];
}
if (isset($_POST['detalle']) && $_POST['detalle'] != "") {
    $detalle = $_POST['detalle'];
}

if (isset($_POST['mostrarDatos']) && $_POST['mostrarDatos'] != "") {
    $tipoReporteMostrar = $_POST['mostrarDatos'];
}
if (isset($_POST['noSerie']) && $_POST['noSerie'] != "") {
    $noSerie = $_POST['noSerie'];
}
if (isset($_POST['idTicket']) && $_POST['idTicket'] != "") {
    $idticket = $_POST['idTicket'];
    if ($_POST['area'] == "2") {
        $tipoReporteMostrar = "15";
        $pedido->setIdTicket($idticket);
        $arrayPedidoexistente = $pedido->getPedidoToner();
        list($idPedido, $modeloPedido, $ubicacionPedido, $noSeriePedido, $tonerNegroPedido, $tonerCiaPedido, $tonerMagentaPedido, $tonerAmarilloPedido, $lecturaTicketPedido, $estadoPedido, $colorPedido) = explode("/****/", $arrayPedidoexistente[0]);
        $noSerie = $noSeriePedido;
    } else {
        $tipoReporteMostrar = "1";
    }

    if ($obj->getTicketByID($idticket)) {//busaca datos para de ticket
        if ($noSerie == "")
            $noSerie = $obj->getNoSerieEquipo();
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
        $claveLocalidad = $obj->getClaveCentroCosto();
        $horarioReponsableInicio = $obj->getHorarioAtenInicResp();
        $horarioResponsableFin = $obj->getHorarioAtenFinResp();
        $horarioAtencioFin = $obj->getHorarioAtenInicAtenc();
        $horarioAtencionFin = $obj->getHorarioAtenFinAtenc();
        $Ubicaion = $obj->getUbicacion();
        $fechaHoraTicket = $obj->getFechaHora();
        $usuarioTicket = $obj->getUsuario();
    }
}
if ($noSerie != "") {//datos del equipo
    $fechaHoraActual = date("d-m-Y H:i:s");
    $queryEquipo = $catalogo->obtenerLista("SELECT ie.NoSerie,e.NoParte,e.Modelo,ax.CveEspClienteCC,
        (SELECT fs.IdTipoServicio FROM k_equipocaracteristicaformatoservicio fs WHERE fs.NoParte=e.NoParte AND fs.IdTipoServicio<>2 ORDER BY fs.IdTipoServicio DESC LIMIT 1) AS tipoServicio
        FROM c_equipo e,c_inventarioequipo ie,k_anexoclientecc ax 
        WHERE e.NoParte=ie.NoParteEquipo AND ie.NoSerie='$noSerie' AND ax.IdAnexoClienteCC=ie.IdAnexoClienteCC");
    while ($rs = mysql_fetch_array($queryEquipo)) {
        $Modelo = $rs['Modelo'];
        $tipoServicio = $rs['tipoServicio'];
        $claveLocalidad = $rs['CveEspClienteCC'];
        $noParteEquipo = $rs['NoParte'];
        $tipo = $rs['tipoServicio'];
    }
    if ($tipo == "1")
        $colorPedido = "No";
    else
        $colorPedido = "Si";
    $lecturaTicket->setNoSerie($noSerie);
    $lecturaTicket->getLecturaBYNoSerie();
    $fechaContadorAnterior = $lecturaTicket->getFechaA();
    $contadorNegro = $lecturaTicket->getContadorBNA();
    $contadorColor = $lecturaTicket->getContadorColorA();
    $nivelNegro = $lecturaTicket->getNivelNegroA();
    $nivelCia = $lecturaTicket->getNivelCiaA();
    $nivelMagenta = $lecturaTicket->getNivelMagentaA();
    $nivelAmarillo = $lecturaTicket->getNivelAmarillo();
}
if (isset($_POST['centroCosto']) && $_POST['centroCosto'] != "" || $claveLocalidad != "") {//datos del domicilio del cliente
    if (isset($_POST['centroCosto']) && $_POST['centroCosto'] != "") {
        $claveLocalidad = $_POST['centroCosto'];
    } else {
        $claveLocalidad = $claveLocalidad;
    }
    $queryDomicilio = $catalogo->obtenerLista("SELECT c.ClaveCliente,c.NombreRazonSocial,c.IdTipoCliente,c.IdEstatusCobranza,cc.Nombre AS localidad,
                                                td.Nombre AS tdomicilio,d.Calle,d.Colonia,d.Delegacion,(CASE WHEN !ISNULL(cc.ClaveZona) THEN cc.ClaveZona ELSE c.ClaveZona END) AS zona,
                                                d.NoExterior,d.NoInterior,d.Ciudad,d.CodigoPostal,d.Estado,ct.Nombre AS nombreContacto,ct.Telefono,ct.Celular,ct.CorreoElectronico,
                                                (SELECT z.fk_id_gzona FROM c_zona z WHERE z.ClaveZona=cc.ClaveZona OR  z.ClaveZona=c.ClaveZona  LIMIT 1) AS ubicacion
                                                FROM c_centrocosto cc,c_domicilio d,c_cliente c,c_tipodomicilio td,c_contacto ct
                                                WHERE cc.ClaveCentroCosto=d.ClaveEspecialDomicilio AND cc.ClaveCliente=c.ClaveCliente AND td.IdTipoDomicilio=d.IdTipoDomicilio 
                                                AND ct.ClaveEspecialContacto=cc.ClaveCentroCosto
                                                AND cc.ClaveCentroCosto='$claveLocalidad'");
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
    }
}
if (isset($_POST['contador'])) {
    $NumContador = $_POST['contador'];
}
?>
<!DOCTYPE html>
<html lang="es">
    <head>
        <script type="text/javascript" language="javascript" src="resources/media/js/jquery.dataTables.js"></script>
        <script type="text/javascript" language="javascript" src="resources/media/js/TableTools.min.js"></script>
        <link href="resources/css/table/demo_page.css" rel="stylesheet" type="text/css">
        <link href="resources/css/table/demo_table_jui.css" rel="stylesheet" type="text/css">
        <link href="resources/css/table/TableTools.css" rel="stylesheet" type="text/css">
        <link href="resources/css/sicop.css" rel="stylesheet" type="text/css">  
        <script type="text/javascript" language="javascript" src="resources/js/paginas/editarTicket.js"></script>
        <script>
            $(document).ready(function() {
                $('.boton').button().css('margin-top', '20px');
            });
        </script>
        <script>
            $(function() {
                $("#tabs").tabs();
            });
        </script>
        <script>
            $("#txtNoSerieEquipoToner").keyup(function(event) {
                if (event.keyCode == 13) {
                    $("#BuscarDatosTicketToner").click();
                }
            });
            $("#txtNoSerieEquipoFalla").keyup(function(event) {
                if (event.keyCode == 13) {
                    $("#BuscarDatosTicketFalla").click();
                }
            });</script>
        <script>
            $(function() {

                var availableTags = [
<?php
if ($claveLocalidad == "") {
    $query1 = $catalogo->obtenerLista("SELECT ie.NoSerie,an.CveEspClienteCC FROM c_inventarioequipo ie,k_anexoclientecc an WHERE ie.IdAnexoClienteCC=an.IdAnexoClienteCC");
} else {
    $query1 = $catalogo->obtenerLista("SELECT ie.NoSerie,an.CveEspClienteCC FROM c_inventarioequipo ie,k_anexoclientecc an WHERE ie.IdAnexoClienteCC=an.IdAnexoClienteCC AND an.CveEspClienteCC='$claveLocalidad'");
}
$arrayNoSerie = array();
$c = 0;
while ($rs = mysql_fetch_array($query1)) {
    $arrayNoSerie[$c] = $rs['NoSerie'];
    $c++;
}
for ($x = 0; $x < count($arrayNoSerie); $x++) {
    echo "'" . $arrayNoSerie[$x] . "',";
}
?>
                ];
                $(".NoSerie").autocomplete({
                    source: availableTags,
                    minLength: 2
                });
            });</script>
        <script>
            $(function() {

                var availableTags = [
<?php
//componentes
$queryToner = "";
if ($noParteEquipo != "") {
    $queryTonerCompatibles = $catalogo->obtenerLista("SELECT c.NoParte,c.Modelo,c.Descripcion FROM c_componente c LEFT JOIN k_equipocomponentecompatible ec ON ec.NoParteComponente=c.NoParte
                                           WHERE ec.NoParteEquipo =(SELECT ie.NoParteEquipo FROM c_inventarioequipo ie WHERE  ie.NoSerie='$noSerie') AND c.IdTipoComponente=2;");
    $queryTonerSCompatibilidad = $catalogo->obtenerLista("SELECT c.NoParte,c.Modelo,c.Descripcion FROM c_componente c WHERE c.NoParte NOT IN (SELECT ec.NoParteComponente FROM k_equipocomponentecompatible ec) AND c.IdTipoComponente=2");

    $arrayToner = array();
    $c1 = 0;
    while ($rs = mysql_fetch_array($queryTonerCompatibles)) {
        $arrayToner[$c1] = $rs['Modelo'] . " / " . $rs['NoParte'] . " / " . $rs['Descripcion'] . " / (Compatible)";
        $c1++;
    }
    while ($rs = mysql_fetch_array($queryTonerSCompatibilidad)) {
        $arrayToner[$c1] = $rs['Modelo'] . " / " . $rs['NoParte'] . " / " . $rs['Descripcion'];
        $c1++;
    }
    for ($x = 0; $x < count($arrayToner); $x++) {
        echo "'" . $arrayToner[$x] . "',";
    }
}
?>
                ];
                $(".NoParteToner").autocomplete({
                    source: availableTags,
                    minLength: 2
                });
            });</script>
        <script type="text/javascript" language="javascript" src="resources/js/paginas/lista.js"></script>
    </head>
    <body>
        <div class='principal'>
            <?php
            if ($idticket != "") {
                echo "<table style='width: 100%'>";
                echo "<tr>"
                . "<td>Ticket: </td>"
                . "<td><input type='text' value='$idticket'></td>";
                echo "<td>Fecha y Hora: </td>"
                . "<td><input type='text' value='$fechaHoraTicket'></td>"
                . "</tr>";
                echo "<tr>"
                . "<td>Usuario: </td>"
                . "<td><input type='text' value='$usuarioTicket'></td>";
                echo "</tr></table><br/><br/>";
            }
            ?>
            <form id="frmAltaTicket" name="frmAltaTicket" action="/" method="POST">
                <table style="width: 100%">
                    <tr><td>Tipo de reporte:</td>
                        <td>
                            <select id="sltTipoReporte" name="sltTipoReporte" onchange="MostrarTipoReporte(this.value);">
                                <option value="0">Seleccione tipo de reporte</option>
                                <?php
                                $consultaTipo = "SELECT * FROM c_estado e,k_flujoestado fe WHERE e.IdEstado=fe.IdEstado AND fe.IdFlujo=1";
                                $tipoReporte = $catalogo->obtenerLista($consultaTipo);
                                while ($rs = mysql_fetch_array($tipoReporte)) {
                                    $s = "";
                                    if ($tipoReporteMostrar == $rs['IdEstado'])
                                        $s = "selected";
                                    echo "<option value='" . $rs['IdEstado'] . "' $s>" . $rs['Nombre'] . "</option>";
                                }
                                ?>
                            </select>
                        </td>
                        <td>Estado del ticket:</td>
                        <td>
                            <select id="sltEstadoTicket" name="sltEstadoTicket">
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
                <div id="datosTicket">

                    <?php
                    if ($tipoReporteMostrar != "" && $tipoReporteMostrar != "0") {
                        ?>
                        <fieldset>
                            <legend>Ticket</legend> 
                            <div id="tabs">
                                <?php
                                if ($tipoReporteMostrar == "15") {
                                    ?>
                                    <ul>
                                        <li><a href="#tabs-1">Equipo</a></li>
                                        <li><a href="#tabs-2">Cliente</a></li>
                                        <li><a href="#tabs-3">Pedido</a></li>  
                                    </ul>  
                                    <div id="tabs-1" style="background-color: #A4A4A4">
                                        <table style="width: 100%">
                                            <tr>
                                                <td>No. serie equipo/Número de inventario Génesis:<span class="obligatorio"> *</span></td>
                                                <td><input type="text" id="txtNoSerieEquipoToner" name="txtNoSerieEquipoToner" value="<?php echo $noSerie; ?>"  <?php echo $read; ?> style='width: 250px' class="NoSerie"/>  
                                                    <!--                                                    <a href='#' onclick='MostraDatosEquipoTicket();
                                                                            return false;' title='Copiar datos de contacto' >Buscar equipo</a></td>-->
                                                    <?php
                                                    if ($editar != "0") {
                                                        if ($detalle != "1") {
                                                            ?>
                                                            <input type="button" name="BuscarDatosTicketToner" id="BuscarDatosTicketToner" value="Buscar" class="boton" onclick='MostraDatosEquipoTicket();'/>
                                                            <?php
                                                        }
                                                    }
                                                    ?>
                                                </td>
                                                <td>
        <!--                                                    <input type="checkbox" id="ckActualizarinfoToner" name="ckActualizarinfoToner" />Actualizar Información-->
                                                </td>
                                            </tr>
                                            <tr>
                                                <td>Modelo:<span class="obligatorio"> *</span></td>
                                                <td><input type="text" id="txtModeloToner" name="txtModeloToner" value="<?php echo $Modelo; ?>"  <?php echo $read; ?> style='width: 250px'></td>
                                                <td></td>
                                            </tr>
                                        </table>
                                        <fieldset>
                                            <legend>Contadores y niveles de toner</legend> 
                                            <table style="width: 100%">
                                                <tr>
                                                    <td style="width: 50%">
                                                        <fieldset>
                                                            <legend>Captura contador y niveles actuales</legend> 
                                                            <table style="width: 100%">
                                                                <tr>
                                                                    <td>Fecha:</td><td><input type="text" id="txtFechaContadorTonerNuevo" name="txtFechaContadorTonerNuevo" value="<?php echo $fechaHoraActual; ?>" <?php echo $read; ?>/></td>
                                                                </tr>
                                                                <?php if ($tipo == "1" || $tipo == "") { ?>
                                                                    <tr>
                                                                        <td>Contador blanco y negro (páginas):</td><td><input type="text" id="txtContadorBNTonerNuevo" name="txtContadorBNTonerNuevo" /><div id="idErrorContadorBN"></div></td>
                                                                    </tr>

                                                                    <tr>
                                                                        <td>Contador color(páginas):</td><td><input type="text" id="txtContadorColorTonerNuevo" name="txtContadorColorTonerNuevo"/><div id="idErrorContadorColor"></div></td>
                                                                    </tr>
                                                                    <tr>
                                                                        <td>Nivel de toner negro(%):</td><td><input type="text" id="txtNivelNegroTonerNuevo" name="txtNivelNegroTonerNuevo"/><div id="idErrorNivelBN"></div></td>
                                                                    </tr>
                                                                    <tr>
                                                                        <td>Nivel de toner cian(%):</td><td><input type="text" id="txtNivelCainTonerNuevo" name="txtNivelCainTonerNuevo"/><div id="idErrorNivelCian"></div></td>
                                                                    </tr>
                                                                    <tr>
                                                                        <td>Nivel de toner magenta(%):</td><td><input type="text" id="txtNivelMagentaTonerNuevo" name="txtNivelMagentaTonerNuevo"/><div id="idErrorNivelMAgenta"></div></td>
                                                                    </tr>
                                                                    <tr>
                                                                        <td>Nivel de toner amarillo(%):</td><td><input type="text" id="txtNivelAmarilloTonerNuevo" name="txtNivelAmarilloTonerNuevo"/><div id="idErrorNivelAmarillo"></div></td>
                                                                    </tr>
                                                                <?php } else {
                                                                    ?>
                                                                    <tr>
                                                                        <td>Contador blanco y negro (páginas):</td><td><input type="text" id="txtContadorBNTonerNuevo" name="txtContadorBNTonerNuevo"/><div id="idErrorContadorBN"></div></td>
                                                                    </tr>
                                                                    <tr>
                                                                        <td>Nivel de toner negro(%):</td><td><input type="text" id="txtNivelNegroTonerNuevo" name="txtNivelNegroTonerNuevo"/><div id="idErrorNivelBN"></div></td>
                                                                    </tr>
                                                                    <?php
                                                                }
                                                                ?>
                                                            </table>
                                                        </fieldset>
                                                    </td>
                                                    <td style="width: 50%">
                                                        <fieldset>
                                                            <legend>Contador anterior y niveles</legend> 
                                                            <table style="width: 100%">
                                                                <tr>
                                                                    <td>Fecha:</td><td><input type="text" id="txtFechaContadorTonerAnterior" name="txtFechaContadorTonerAnterior" <?php echo $read; ?> value="<?php echo $fechaContadorAnterior; ?>"/></td>
                                                                </tr>
                                                                <?php if ($tipo == "1" || $tipo == "") { ?>
                                                                    <tr>
                                                                        <td>Contador blanco y negro (páginas):</td><td><input type="text" id="txtContadorBNTonerAnterior" name="txtContadorBNTonerAnterior" <?php echo $read; ?> value="<?php echo $contadorNegro; ?>"/></td>
                                                                    </tr>                                                                
                                                                    <tr>
                                                                        <td>Contador color(páginas):</td><td><input type="text" id="txtContadorColorTonerAnterior" name="txtContadorColorTonerAnterior" <?php echo $read; ?> value="<?php echo $contadorColor; ?>"/></td>
                                                                    </tr>
                                                                    <tr>
                                                                        <td>Nivel de toner negro(%):</td><td><input type="text" id="txtNivelNegroTonerAnterior" name="txtNivelNegroTonerAnterior" <?php echo $read; ?> value="<?php echo $nivelNegro; ?>"/></td>
                                                                    </tr>
                                                                    <tr>
                                                                        <td>Nivel de toner cian(%):</td><td><input type="text" id="txtNivelCainTonerAnterior" name="txtNivelCainTonerAnterior" <?php echo $read; ?> value="<?php echo $nivelCia; ?>"/></td>
                                                                    </tr>
                                                                    <tr>
                                                                        <td>Nivel de toner magenta(%):</td><td><input type="text" id="txtNivelMagentaTonerAnterior" name="txtNivelMagentaTonerAnterior"<?php echo $read; ?> value="<?php echo $nivelMagenta; ?>"/></td>
                                                                    </tr>
                                                                    <tr>
                                                                        <td>Nivel de toner amarillo(%):</td><td><input type="text" id="txtNivelAmarilloTonerAnterior" name="txtNivelAmarilloTonerAnterior" <?php echo $read; ?> value="<?php echo $nivelAmarillo; ?>"/></td>
                                                                    </tr>
                                                                <?php } else {
                                                                    ?>
                                                                    <tr>
                                                                        <td>Contador blanco y negro (páginas):</td><td><input type="text" id="txtContadorBNTonerAnterior" name="txtContadorBNTonerAnterior" <?php echo $read; ?> value="<?php echo $contadorNegro; ?>"/></td>
                                                                    </tr>
                                                                    <tr>
                                                                        <td>Nivel de toner negro(%):</td><td><input type="text" id="txtNivelNegroTonerAnterior" name="txtNivelNegroTonerAnterior" <?php echo $read; ?> value="<?php echo $nivelNegro; ?>"/></td>
                                                                    </tr>
                                                                <?php }
                                                                ?>
                                                            </table>
                                                        </fieldset>
                                                    </td>
                                                </tr>
                                            </table>
                                        </fieldset>
                                        <fieldset>
                                            <legend>Piezas de toner solicitadas:</legend> 
                                            <table style="width: 100%">
                                                <tr>
                                                    <td>
                                                        Toner Negro: 
                                                    </td>
                                                    <td><input type="text" id="noParteComponente" name="noParteComponente" class="NoParteToner" style="width: 300px"/>
                                                        <div id="errorNegro"></div></td>
                                                    <td>Cantidad: </td>
                                                    <td>
                                                        <input type="text" id="txtTonerNegroSolicitada" name="txtTonerNegroSolicitada" value="" /><div id="errorNegroCantidad"></div>
                                                    </td>
                                                </tr>
                                                <?php if ($tipo == "1" || $tipo == "") { ?>
                                                    <tr>
                                                        <td>Toner cian</td><td><input type="text" id="noParteComponenteCia" name="noParteComponenteCia" class="NoParteToner" style="width: 300px" /> <div id="errorCyan"></div></td>
                                                        <td>Cantidad: </td><td><input type="text" id="txtTonerCiaSolicitada" name="txtTonerCiaSolicitada" value=""/><div id="errorCyanCantidad"></div></td>
                                                    </tr>
                                                    <tr>
                                                        <td>Toners magenta:</td><td><input type="text" id="noParteComponenteMagenta" name="noParteComponenteMagenta" class="NoParteToner"  style="width: 300px"/> <div id="errorMagenta"></div></td>
                                                        <td>Cantidad: </td><td><input type="text" id="txtTonerMagentaSolicitada" name="txtTonerMagentaSolicitada" value=""/><div id="errorMagentaCantidad"></div></td>
                                                    </tr>
                                                    <tr>
                                                        <td>Toners amarillo:</td><td><input type="text" id="noParteComponenteAmarillo" name="noParteComponenteAmarillo" class="NoParteToner" style="width: 300px"/> <div id="errorAmarillo"></div></td>
                                                        <td>Cantidad: </td><td><input type="text" id="txtTonerAmarilloSolicitada" name="txtTonerAmarilloSolicitada" value="" /><div id="errorAmarilloCantidadAgregarPedido"></div></td>
                                                    </tr>

                                                <?php } ?>
                                                <input type="hidden" id="idFila" name="idFila" value=""/>
                                            </table>
                                        </fieldset>
                                        <?php
                                        if ($noSerie != "" && $editar != "0") {
                                            if ($detalle != "1") {
                                                ?>
                                                <table style="width: 100%">
                                                    <tr>
                                                        <td><input id="idGuardarPedido"  name="idGuardarPedido" type="button" class="boton" value="Agregar" onclick="validarDatosPedido('<?php echo $noSerie; ?>', '<?php echo $Modelo; ?>', '<?php echo "ubicacion"; ?>', '<?php echo $tipo ?>');"/></td>
                                                        <td><input id="idCancelarPedido"  name="idCancelarPedido" type="button" class="boton" value="Cancelar" onclick=""/></td>
                                                    </tr>
                                                </table>
                                                <?php
                                            }
                                        }
                                        ?>
                                    </div>                          
                                    <div id="tabs-2" style="background-color: #A4A4A4">
                                        <fieldset >
                                            <legend>Cliente:</legend> 
                                            <table style="width: 100%;">
                                                <tr>
                                                    <td>Cliente:<span class="obligatorio"> *</span></td>
                                                    <td><input type="text" id="txtClaveClienteToner" name="txtClaveClienteToner" value="<?php echo $claveCliente; ?>" style='width: 250px'<?php echo $read; ?> /></td>
                                                    <!--<td></td><td><input type="checkbox" id="ckActualizarinfoToner" name="ckActualizarinfoToner" />Actualizar Información</td>-->
                                                </tr>
                                                <tr>
                                                    <td>Nombre Empresa:</td>
                                                    <td><input type="text" id="nombreEmpresaToner" name="nombreEmpresaToner" value="<?php echo $nombreCliente; ?>" style='width: 250px' <?php echo $read; ?>/></td>
                                                    <td></td><td></td>
                                                </tr>
                                                <tr>
                                                    <td>Tipo de cliente</td>
                                                    <td>
                                                        <select id="tipoClienteToner" name="tipoClienteToner">
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
                                                        <select id="tipoestatusCobranza" name="tipoestatusCobranza">                                                           
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
                                                    <td><input type="text" id="localidadToner" name="localidadToner" value="<?php echo $nombreLocalidad; ?>" style='width: 250px'<?php echo $read; ?> /></td>
                                                    <td>Nombre TFS:</td>
                                                    <td><input type="text" id="txtTfsToner" name="txtTfsToner" value="sitfs" style='width: 250px' <?php echo $read; ?> /></td>
                                                </tr>
                                            </table>
                                            <fieldset>
                                                <legend>Domicilio<span class="obligatorio"> *</span></legend> 
                                                <table style="width: 100%">
                                                    <tr>
                                                        <td>Tipo de domicilio:<span class="obligatorio"> *</span></td><td><input type="text" id="txtDomicilioToner" name="txtDomicilioToner" value="<?php echo $tipoDomicilio; ?>" style='width: 180px' <?php echo $read; ?>/></td>
                                                        <td>Zona:</td>
                                                        <td>
        <!--                                                            <input type="text" id="txtZonaToner" name="txtZonaToner" value="" style='width: 180px' />
                                                                   <input type="text" id="txtZonaFalla" name="txtZonaFalla" value="<?php echo $estatusCobranza; ?>" style='width: 180px' <?php echo $read; ?> />-->
                                                            <select id="txtZonaToner" name="txtZonaToner" style="width:180px">
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
                                                            <select id="sltUbicacionToner" name="sltUbicacionToner" style="width:180px">
                                                                <?php
                                                                $queryUbicacion = $catalogo->getListaAlta("c_ubicacionticket", "Nombre");
                                                                while ($rs = mysql_fetch_array($queryUbicacion)) {
                                                                    $s = "";
                                                                    if ($Ubicaion == $rs['IdUbicacion'])
                                                                        $s = "selected";
                                                                    echo "<option value='" . $rs['IdUbicacion'] . "' $s>" . $rs['Nombre'] . "</option>";
                                                                }
                                                                ?> 
                                                            </select>
                                                        </td>
                                                    </tr>
                                                    <tr>
                                                        <td>Calle:<span class="obligatorio"> *</span></td><td><input type="text" id="txtCalleToner" name="txtCalleToner" value="<?php echo $calle; ?>" style='width: 180px' <?php echo $read; ?> /></td>
                                                        <td>No.exterior:<span class="obligatorio"> *</span></td><td><input type="text" id="txtNoExteriorToner" name="txtNoExteriorToner" value="<?php echo $nExterior; ?>" style='width: 180px' <?php echo $read; ?> /></td>
                                                        <td>No. interior:</td><td><input type="text" id="txtNoInteriorToner" name="txtNoInteriorToner" value="<?php echo $nInterior; ?>" style='width: 180px' <?php echo $read; ?> /></td>
                                                    </tr>
                                                    <tr>
                                                        <td>Colonia:<span class="obligatorio"> *</span></td><td><input type="text" id="txtColoniaToner" name="txtColoniaToner" value="<?php echo $colonia; ?>" style='width: 180px' <?php echo $read; ?> /></td>
                                                        <td>Ciudad:<span class="obligatorio"> *</span></td><td><input type="text" id="txtCiudadToner" name="txtCiudadToner" value="<?php echo $ciudad; ?>" style='width: 180px' <?php echo $read; ?> /></td>
                                                        <td>Estado:<span class="obligatorio"> *</span></td>
                                                        <td>
                                                            <select id="sltEstadoToner" name="sltEstadoToner" style="width:180px">

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
                                                        <td>Delegación:<span class="obligatorio"> *</span></td><td><input type="text" id="txtDelegacionToner" name="txtDelegacionToner" value="<?php echo $delegacion; ?>" style='width: 180px'<?php echo $read; ?>  /></td>
                                                        <td>C.P:<span class="obligatorio"> *</span></td><td><input type="text" id="txtCpToner" name="txtCpToner" value="<?php echo $cp; ?>" style='width: 180px'  <?php echo $read; ?>/></td>
                                                        <td></td><td></td>
                                                    </tr>
                                                </table>
                                            </fieldset>
                                            Contacto de la localidad
                                            <table style="width: 100%">                                       
                                                <tr>
                                                    <td>Nombre:<span class="obligatorio"> *</span></td>
                                                    <td><input type="text" id="txtNombreCFalla" name="txtNombreCToner" value="<?php echo $nombreContacto; ?>" style='width: 250px' <?php echo $read; ?>/></td>
                                                </tr>
                                                <tr>
                                                    <td>Teléfono:<span class="obligatorio"> *</span></td>
                                                    <td><input type="text" id="txtTelefonoCFalla" name="txtTelefonoCToner" value="<?php echo $telefono; ?>" style='width: 250px' <?php echo $read; ?> /></td>
                                                </tr>
                                                <tr>
                                                    <td>Celular:</td>
                                                    <td><input type="text" id="txtCelularCFalla" name="txtCelularCToner" value="<?php echo $celular; ?>" style='width: 250px' <?php echo $read; ?> /></td>
                                                </tr>
                                                <tr>
                                                    <td>Correo electrónico:</td>
                                                    <td><input type="text" id="txtCorreoCFalla" name="txtCorreoCToner" value="<?php echo $correoE; ?>" style='width: 250px' <?php echo $read; ?> /></td>
                                                </tr>
                                            </table>
                                        </fieldset>
                                    </div>
                                    <div id="tabs-3" style="background-color: #A4A4A4" class="from">   
                                        <div id="divTabla">
                                            <table style="width: 100%;" id="tablaPedido">
                                                <thead style="background-color: grey;">
                                                <th align='center'>No. de serie</th>
                                                <th align='center'>Modelo</th>
                                                <th align='center'>Ubicación</th>
                                                <th align='center'>Color</th>
                                                <th align='center'>Toners negro</th>
                                                <th align='center'>Toners cian</th>
                                                <th align='center'>Toners magenta</th>
                                                <th align='center'>Toners amarillo </th>
                                                <th align='center'>Estado</th>
                                                <th align='center'>Detalle</th>
                                                <?php if ($idticket == "") { ?>
                                                    <th align='center'>Modificar</th>
                                                    <th align='center'>Eliminar</th>
                                                <?php } ?>
                                                </thead>  
                                                <tbody>
                                                    <?php
                                                    if (!empty($arrayPedidoexistente)) {
                                                        $cont = 0;
                                                        while ($cont < count($arrayPedidoexistente)) {
                                                            list($idPedido, $modeloPedido, $ubicacionPedido, $seriePedido, $tonerNegroPedido, $tonerCiaPedido, $tonerMagentaPedido, $tonerAmarilloPedido, $lecturaTicketPedido, $estadoPedido, $colorPedido) = explode("/****/", $arrayPedidoexistente[$cont]);
                                                            echo "<tr><td align='center'  style='background-color: palegoldenrod '><div id=''>" . $seriePedido . "</div><input type='hidden' id='idPedido" . $cont . "' name='idPedido" . $cont . "' value='" . $idPedido . "'/>" .
                                                            "<input type='hidden' id='serie" . $cont . "' name='serie" . $cont . "' value='" . $seriePedido . "'/></td>";
                                                            echo "<td align='center' style='background-color: palegoldenrod '><div id=''>" . $modeloPedido . "</div><input type='hidden' id='modelo" . $cont . "' name='modelo" . $cont . "' value='" . $modeloPedido . "'/></td>";
                                                            echo "<td align='center' style='background-color: palegoldenrod '><div id=''>" . $ubicacionPedido . "</div><input type='hidden' id='ubicacion" . $cont . "' name='ubicacion" . $cont . "' value='" . $ubicacionPedido . "'/></td>";
                                                            echo "<td align='center' style='background-color: palegoldenrod '><div id=''>" . $colorPedido . "</div><input type='hidden' id='color" . $cont . "' name='color" . $cont . "' value='" . $colorPedido . "'/></td>";
                                                            echo "<td align='center' style='background-color: palegoldenrod '><div id='idNegro" . $cont . "'>" . $tonerNegroPedido . "</div><input type='hidden' id='negro" . $cont . "' name='negro" . $cont . "' value='" . $tonerNegroPedido . "'/></td>";
                                                            echo "<td align='center' style='background-color: palegoldenrod '> <div id='idCia" . $cont . "'>" . $tonerCiaPedido . "</div><input type='hidden' id='cia" . $cont . "' name='cia" . $cont . "' value='" . $tonerCiaPedido . "'/></td>";
                                                            echo "<td align='center' style='background-color: palegoldenrod '><div id='idMagenta" . $cont . "'>" . $tonerMagentaPedido . "</div><input type='hidden' id='magenta" . $cont . "' name='magenta" . $cont . "' value='" . $tonerMagentaPedido . "'/></td>";
                                                            echo "<td align='center' style='background-color: palegoldenrod '><div id='idAmarillo" . $cont . "'>" . $tonerAmarilloPedido . "</div><input type='hidden' id='amarillo" . $cont . "' name='amarillo" . $cont . "' value='" . $tonerAmarilloPedido . "'/></td>";
                                                            echo "<td align='center' style='background-color: palegoldenrod '><div id='idEstado" . $cont . "'>" . $estadoPedido . "</div><input type='hidden' id='estado" . $cont . "' name='estado" . $cont . "' value='" . $estadoPedido . "'/></td>";
                                                            echo "<td align='center' style='background-color: palegoldenrod '><img class='imagenMouse' src='resources/images/Textpreview.png' title='modificar pedido' onclick='changeToTab(\"" . $seriePedido . "\",\"" . $modeloPedido . "\"," . $cont . "," . $tonerNegroPedido . "," . $tonerCiaPedido . "," . $tonerMagentaPedido . "," . $tonerAmarilloPedido . ",1);' style='float: right; cursor: pointer;' /></td>";
                                                            if ($idticket == "") {
                                                                echo "<td align='center' style='background-color: palegoldenrod '><img class='imagenMouse' src='resources/images/Modify.png' title='modificar pedido' onclick='changeToTab(\"" . $seriePedido . "\",\"" . $modeloPedido . "\"," . $cont . "," . $tonerNegroPedido . "," . $tonerCiaPedido . "," . $tonerMagentaPedido . "," . $tonerAmarilloPedido . ",2);' style='float: right; cursor: pointer;' /></td>";
                                                                echo "<td align='center' style='background-color: palegoldenrod '><img class='imagenMouse' src='resources/images/Erase.png' title='Eliminar pedido' onclick='deletePedido(" . $cont . ");' style='float: right; cursor: pointer;' /></td>";
                                                            }
                                                            echo "</tr>";
                                                            $cont++;
                                                        }
                                                        $NumContador = count($arrayPedidoexistente);
                                                    }
                                                    ?>

                                                </tbody>

                                            </table>
                                        </div>
                                        <input type="hidden" id="contador" name="contador" value="<?php echo $NumContador ?>"/>
                                    </div>
                                <?php } else if ($tipoReporteMostrar == "1") { ?>
                                    <ul>
                                        <li><a href="#tabs-1">Equipo</a></li>
                                        <li><a href = "#tabs-2">Cliente</a></li>
                                    </ul>
                                    <div id = "tabs-1" style = "background-color: #A4A4A4">
                                        <table style = "width: 100%">
                                            <tr>
                                                <td>No. serie equipo/Número de inventario Génesis:<span class = "obligatorio"> *</span></td>
                                                <td><input type = "text" id = "txtNoSerieEquipoFalla" name = "txtNoSerieEquipoFalla" value = "<?php echo $noSerie; ?>" <?php echo $read;
                                    ?> style='width: 250px' class="NoSerie"/>
                                                    <!--                                                    <a href='#' onclick='MostraDatosEquipoTicket();
                                                                            return false;' title='Copiar datos de contacto' >Buscar equipo</a></td>-->
                                                    <?php
                                                    if ($editar != "0" && $idticket == "") {

                                                        if ($detalle != "1") {
                                                            ?>
                                                            <input type="button" name="BuscarDatosTicketFalla" id="BuscarDatosTicketFalla" value="Buscar" class="boton" onclick='MostraDatosEquipoTicket();'/>
                                                            <?php
                                                        }
                                                    }
                                                    ?>
                                                </td>
                                                <td>
                                                    <!--<input type="checkbox" id="ckActualizarinfoFalla" name="ckActualizarinfoFalla" />Actualizar Información-->
                                                </td>
                                            </tr>
                                            <tr>
                                                <td>Modelo:<span class="obligatorio"> *</span></td>
                                                <td><input type="text" id="txtModeloFalla" name="txtModeloFalla" value="<?php echo $Modelo; ?>"  <?php echo $read; ?> style='width: 250px'></td>
                                                <td></td>
                                            </tr>
                                        </table>
                                    </div>
                                    <div id="tabs-2" style="background-color: #A4A4A4">
                                        <fieldset >
                                            <legend>Cliente:</legend> 
                                            <table style="width: 100%;">
                                                <tr>
                                                    <td>Cliente:<span class="obligatorio"> *</span></td>
                                                    <td><input type="text" id="txtClaveClienteFalla" name="txtClaveClienteFalla" value="<?php echo $claveCliente; ?>" style='width: 250px'<?php echo $read; ?>/></td>
        <!--                                                    <td></td><td><input type="checkbox" id="ckActualizarinfoFalla" name="ckActualizarinfoFalla" />Actualizar Información</td>-->
                                                </tr>
                                                <tr>
                                                    <td>Nombre Empresa:</td>
                                                    <td><input type="text" id="nombreEmpresaFalla" name="nombreEmpresaFalla" value="<?php echo $nombreCliente; ?>" style='width: 250px' <?php echo $read; ?>/></td>
                                                    <td></td><td></td>
                                                </tr>
                                                <tr>
                                                    <td>Tipo de cliente</td>
                                                    <td>
                                                        <select id="tipoClienteFalla" name="tipoClienteFalla">  $tipoCliente = $rs['IdTipoCliente'];
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
                                                        <select id="tipoestatusCobranza" name="tipoestatusCobranza">                                                           
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
                                                    <td><input type="text" id="localidadFalla" name="localidadFalla" value="<?php echo $nombreLocalidad; ?>" style='width: 250px' <?php echo $read; ?>/></td>
                                                    <td>Nombre TFS:</td>
                                                    <td><input type="text" id="txtTfsFalla" name="txtTfsFalla" value="<?php echo "sin tfs"; ?>" style='width: 250px' <?php echo $read; ?> /></td>
                                                </tr>
                                            </table>
                                            <fieldset>
                                                <legend>Domicilio<span class="obligatorio"> *</span></legend> 
                                                <table style="width: 100%">
                                                    <tr>
                                                        <td>Tipo de domicilio:<span class="obligatorio"> *</span></td><td><input type="text" id="txtDomicilioFalla" name="txtDomicilioFalla" value="<?php echo $tipoDomicilio; ?>" style='width: 180px' <?php echo $read; ?>/></td>
                                                        <td>Zona:</td>
                                                        <td>
        <!--                                                            <input type="text" id="txtZonaFalla" name="txtZonaFalla" value="<?php echo $estatusCobranza; ?>" style='width: 180px' <?php echo $read; ?> />-->
                                                            <select id="txtZonaFalla" name="txtZonaFalla" style="width:180px">
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
                                                            <select id="sltUbicacionFalla" name="sltUbicacionFalla" style="width:180px">
                                                                <?php
                                                                $queryUbicacion = $catalogo->getListaAlta("c_ubicacionticket", "Nombre");
                                                                while ($rs = mysql_fetch_array($queryUbicacion)) {
                                                                    $s = "";
                                                                    if ($Ubicaion == $rs['IdUbicacion'])
                                                                        $s = "selected";
                                                                    echo "<option value='" . $rs['IdUbicacion'] . "' $s>" . $rs['Nombre'] . "</option>";
                                                                }
                                                                ?> 
                                                            </select>
                                                        </td>
                                                    </tr>

                                                    <tr>
                                                        <td>Calle:<span class="obligatorio"> *</span></td><td><input type="text" id="txtCalleFalla" name="txtCalleFalla" value="<?php echo $calle; ?>" style='width: 180px' <?php echo $read; ?>/></td>
                                                        <td>No.exterior:<span class="obligatorio"> *</span></td><td><input type="text" id="txtNoExteriorFalla" name="txtNoExteriorFalla" value="<?php echo $nExterior; ?>" style='width: 180px' <?php echo $read; ?>/></td>
                                                        <td>No. interior:</td><td><input type="text" id="txtNoInteriorFalla" name="txtNoInteriorFalla" value="<?php echo $nInterior; ?>" style='width: 180px' /></td>
                                                    </tr>
                                                    <tr>
                                                        <td>Colonia:<span class="obligatorio"> *</span></td><td><input type="text" id="txtColoniaFalla" name="txtColoniaFalla" value="<?php echo $colonia; ?>" style='width: 180px' <?php echo $read; ?>/></td>
                                                        <td>Ciudad:<span class="obligatorio"> *</span></td><td><input type="text" id="txtCiudadFalla" name="txtCiudadFalla" value="<?php echo $ciudad; ?>" style='width: 180px' <?php echo $read; ?>/></td>
                                                        <td>Estado:<span class="obligatorio"> *</span></td>
                                                        <td>
                                                            <select id="sltEstadoFalla" name="sltEstadoFalla" style="width:180px">
                                                                <option value="0">Selecciona un estado</option>
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
                                                        <td>Delegación:<span class="obligatorio"> *</span></td><td><input type="text" id="txtDelegacionFalla" name="txtDelegacionFalla" value="<?php echo $delegacion; ?>" style='width: 180px' <?php echo $read; ?>/></td>
                                                        <td>C.P:<span class="obligatorio"> *</span></td><td><input type="text" id="txtCpFalla" name="txtCpFalla" value="<?php echo $cp; ?>" style='width: 180px' <?php echo $read; ?>/></td>
                                                        <td></td><td></td>
                                                    </tr>
                                                </table>
                                            </fieldset>
                                            Contacto de la localidad
                                            <table style="width: 100%">                                       
                                                <tr>
                                                    <td>Nombre:<span class="obligatorio"> *</span></td>
                                                    <td><input type="text" id="txtNombreCFalla" name="txtNombreCFalla" value="<?php echo $nombreContacto; ?>" style='width: 250px' <?php echo $read; ?> /></td>
                                                </tr>
                                                <tr>
                                                    <td>Teléfono:<span class="obligatorio"> *</span></td>
                                                    <td><input type="text" id="txtTelefonoCFalla" name="txtTelefonoCFalla" value="<?php echo $telefono; ?>" style='width: 250px' <?php echo $read; ?> /></td>
                                                </tr>
                                                <tr>
                                                    <td>Celular:</td>
                                                    <td><input type="text" id="txtCelularCFalla" name="txtCelularCFalla" value="<?php echo $celular; ?>" style='width: 250px' <?php echo $read; ?> /></td>
                                                </tr>
                                                <tr>
                                                    <td>Correo electrónico:</td>
                                                    <td><input type="text" id="txtCorreoCFalla" name="txtCorreoCFalla" value="<?php echo $correoE; ?>" style='width: 250px' <?php echo $read; ?> /></td>
                                                </tr>
                                            </table>
                                        </fieldset>
                                    </div>

                                <?php } ?>     
                        </fieldset>
                    </div>
                    <?php
                }
                if ($noSerie != "" && $detalle == "" && $tipoReporteMostrar == "1") {
                    echo "<a href='#' onclick='mostrarContadoresNiveles(\"" . $noSerie . "\");
                            return false;' title='Mostar contadores' >Agregar contador y niveles</a>";
                }
                ?>

                <fieldset>
                    <legend>Datos del ticket</legend>   
                    <fieldset>
                        <tr
                    <legend>Contacto responsable del ticket</legend>
                    <table style='width: 100%'>
                        <tr>
                        <tr>
                            <td><input type="radio" name="rdContacto" id="rdContacto" value="1" onclick="mostrarTipoContacto(1)" >Nuevo contacto </td>
                            <td><input type="radio" name="rdContacto" id="rdContacto" value="0" onclick="mostrarTipoContacto(0)" checked="checked" >Contacto existente </td>
                        </tr>
                        <tr>

                            <td>Nombre<span class="obligatorio"> *</span></td>
                            <td colspan="5">
                                <div id="contactoExistente">
                                    <select id="txtNombre" name="txtNombre"  onchange="DatosContacto(this.value);" style='width: 655px'>
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
                                    <input type="text" id="txtNombre1" name="txtNombre1" value="<?php echo $nombreResp; ?>" style='width: 680px'/>
                                </div>

<!--//                                    <input type="text" id="txtNombre" name="txtNombre" value="<?php echo $nombreResp; ?>" style='width: 680px'>-->
                            </td>
                        </tr>
                        <tr>
                            <td><label for="txtTelefono1">Telefono 1:</label></td>
                            <td colspan="3"><input type="text" id="txtTelefono1" name="txtTelefono1" value="<?php echo $telefono1Resp; ?>" style='width: 250px'></td>
                            <td><label for="txtExtencion1">Extención 1:</label></td>
                            <td><input type="text" id="txtExtencion1" name="txtExtencion1" value="<?php echo $Extencio2Resp; ?>" style='width: 250px'></td>
                        </tr>
                        <tr>
                            <td><label for="txtTelefono2">Telefono 2:</label></td>
                            <td colspan="3"><input type="text" id="txtTelefono2" name="txtTelefono2" value="<?php echo $telefono2Atencion; ?>" style='width: 250px'></td>
                            <td><label for="txtExtencion2">Extención 2:</label></td>
                            <td><input type="text" id="txtExtencion2" name="txtExtencion2" value="<?php echo $Extencio2Resp; ?>" style='width: 250px'></td>
                        </tr>
                        <tr>
                            <td><label for="txtCelular">Celular</label></td>
                            <td colspan="3"><input type="text" id="txtCelular" name="txtCelular" value="<?php echo $celularResp; ?>" style='width: 250px'></td> 
                        </tr>
                        <tr>
                            <td><label for="correoElectronico">Correo electrónico</label><span class="obligatorio"> *</span></td>
                            <td colspan="3"><input type="text" id="correoElectronico" name="correoElectronico" value="<?php echo $correoResp; ?>" style='width: 250px' /><div id="errorCorreoResp"></div></td> 
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
                                <select id='lstHR' name='lstHR'>
                                    <?php
                                    for ($x = 1; $x <= 12; $x++) {
                                        $s = "";
                                        if ($x == "9" || $hinicioResp == $x)
                                            $s = "selected";
                                        echo "<option value='" . $x . "' $s>" . $x . "</option> ";
                                    }
                                    ?>
                                </select>
                                <select id='lstMR' name='lstMR'>
                                    <option value="00">00</option>
                                    <option value="15">15</option>
                                    <option value="30">30</option>
                                    <option value="45">45</option>
                                </select>
                                <select id='lstTA' name='lstTA'>
                                    <option value="am">am</option>
                                    <option value="pm">pm</option>
                                </select>
                            </td>
                            <td><label for="lstFinR"><b>Fin:</b>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Hora</label></td>
                            <td>

                                <select id='lstFinHR' name='lstFinHR'>
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
                                <select id='lstFinMR' name='lstFinMR'>
                                    <option value="00" selected>00</option>
                                    <option value="15">15</option>
                                    <option value="30">30</option>
                                    <option value="45">45</option>
                                </select>
                                <select id='lstFinTR' name='lstFinTR'>
                                    <option value="am">am</option>
                                    <option value="pm" selected>pm</option>
                                </select>
                            </td>
                        </tr>
                    </table>
                </fieldset>
                <?php if ($detalle != "1") { ?>
                    <a href='#' onclick='CopiarDatosContacto();
                                return false;' title='Copiar datos de contacto' >Copiar datos de contacto responsable de ticket o centro de atención</a>
                   <?php } ?>
                <fieldset>
                    <legend>Contacto de atención del ticket</legend>   
                    <table style='width: 100%'>
                        <tr>
                            <td><label for="txtNombreAtencion">Nombre</label></td>
                            <td colspan="5"><input type="text" id="txtNombreAtencion" name="txtNombreAtencion" value="<?php echo $nombreAtencion; ?>" style='width: 650px'></td>
                        </tr>
                        <tr>
                            <td><label for="txtTelefono1Atencion">Telefono 1:</label></td>
                            <td colspan="3"><input type="text" id="txtTelefono1Atencion" name="txtTelefono1Atencion" value="<?php echo $telefono1Atencion; ?>" style='width: 250px'></td>

                            <td><label for="txtExtencion1Atencion">Extención 2:</label></td>
                            <td><input type="text" id="txtExtencion1Atencion" name="txtExtencion1Atencion" value="<?php echo $Extencio1Atencion; ?>" style='width: 250px'></td>
                        </tr>
                        <tr>
                            <td><label for="txtTelefono2Atencion">Telefono 2:</label></td>
                            <td colspan="3"><input type="text" id="txtTelefono2Atencion" name="txtTelefono2Atencion" value="<?php echo $telefono2Atencion; ?>" style='width: 250px'></td>

                            <td><label for="txtExtencion2Atencion">Extención 1:</label></td>
                            <td><input type="text" id="txtExtencion2Atencion" name="txtExtencion2Atencion" value="<?php echo $Extencio2Atencion; ?>" style='width: 250px'></td>
                        </tr>
                        <tr>
                            <td><label for="txtCelularAtencion">Celular</label></td>
                            <td colspan="3"><input type="text" id="txtCelularAtencion" name="txtCelularAtencion" value="<?php echo $celularAtencion; ?>" style='width: 250px'></td> 
                        </tr>
                        <tr>
                            <td><label for="txtCorreoElectronico">Correo electrónico</label></td>
                            <td colspan="3"><input type="text" id="txtCorreoElectronico" name="txtCorreoElectronico" value="<?php echo $correoAtencion; ?>" style='width: 250px' /><div id='errorCorreoAtencion'></div></td> 
                        </tr>
                        <tr>
                            <td><label for="lstHA">Horario de atención:</label></td>
                            <td colspan="3">
                                <b>Inicio:</b>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Hora:
                                <select id='lstHA' name='lstHA'>
                                    <?php
                                    for ($x = 1; $x <= 12; $x++) {
                                        $s = "";
                                        if ($x == "9")
                                            $s = "selected";
                                        echo "<option value='" . $x . "' $s>" . $x . "</option> ";
                                    }
                                    ?>
                                </select>
                                <select id='lstMA' name='lstMA'>
                                    <option value="00" selected>00</option>
                                    <option value="15">15</option>
                                    <option value="30">30</option>
                                    <option value="45">45</option>
                                </select>
                                <select id='lstTA' name='lstTA'>
                                    <option value="am" selected>am</option>
                                    <option value="pm">pm</option>
                                </select>
                            </td>
                            <td><label for="lstFinA"><b>Fin:</b>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Hora</label></td>
                            <td>
                                <select id='lstFinHA' name='lstFinHA'>
                                    <?php
                                    for ($x = 1; $x <= 12; $x++) {
                                        $s = "";
                                        if ($x == "6")
                                            $s = "selected";
                                        echo "<option value='" . $x . "' $s>" . $x . "</option> ";
                                    }
                                    ?>
                                </select>
                                <select id='lstFinMA' name='lstFinMA'>
                                    <option value="00" selected>00</option>
                                    <option value="15">15</option>
                                    <option value="30">30</option>
                                    <option value="45">45</option>
                                </select>
                                <select id='lstFinTA' name='lstFinTA'>
                                    <option value="am">am</option>
                                    <option value="pm" selected>pm</option>
                                </select>
                            </td>
                        </tr>
                    </table>
                </fieldset>
                <table style="width:  100%">
                    <tr>
                        <td>No. ticket cliente:</td><td><input type="text" id="txtNoTicketClienteGral" name="txtNoTicketClienteGral" value='<?php echo $ticketCliente ?>'/></td>
                        <td>No. ticket distribuidor:</td><td><input type="text" id="txtNoTicketDistribucionGral" name="txtNoTicketDistribucionGral" value='<?php echo $ticketDistribucion; ?>'/></td>
                    </tr>   
                    <tr>
                        <td>Descripción del reporte:</td>
                        <td style="width: 85%" colspan="3"><textarea style="width: 100%; height: 150px;" id='descripcion' name='descripcion' ><?php echo $descripcion; ?></textarea></td>
                    </tr>
                    <tr>
                        <td>Observaciones adicionales:</td>
                        <td style="width: 85%" colspan="3"><textarea style="width: 100%;height: 150px;" id='observacion' name='observacion'><?php echo $observacion; ?></textarea></td>
                    </tr>   
                    <tr>
                        <td>Área de atención<span class="obligatorio"> *</span>:</td>
                        <td>
                            <select id="areaAtencionGral" name="areaAtencionGral" style="width: 300px">

                                <?php
                                if ($tipoReporteMostrar == "15") {
                                    $queryArea = "SELECT  e.IdEstado,e.Nombre  FROM c_estado e,c_flujo f,k_flujoestado fe WHERE e.IdEstado=fe.IdEstado AND f.IdFlujo=fe.IdFlujo AND f.IdFlujo=3";
                                } else if ($tipoReporteMostrar == "1") {
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
            <?php
            if ($detalle != "1") {
                ?>
                <input type="submit" id="botonGuardar" name="botonGuardar"  class="boton" value="Guardar"/>
                <input type="submit" class="boton" value="Cancelar" onclick="cambiarContenidos('<?php echo $pagina_listaRegresar; ?>');
                            return false;"/>
                   <?php } ?>
            <input type="hidden" name="idTicket" id="idTicket" value="<?php echo $idticket; ?>" />
            <input type="hidden" name="nombreCC" id="nombreCC" value="<?php echo $nombreLocalidad; ?>" />
            <input type="hidden" name="claveCC" id="claveCC" value="<?php echo $claveLocalidad; ?>" />
            <!--lecturas-->
            <input type="hidden" name="contadorBNFallaNuevo" id="contadorBNFallaNuevo" />
            <input type="hidden" name="contadorColorFallaNuevo" id="contadorColorFallaNuevo" />
            <input type="hidden" name="nivelNegroFallaNuevo" id="nivelNegroFallaNuevo"  />
            <input type="hidden" name="nivelCianFallaNuevo" id="nivelCianFallaNuevo" />
            <input type="hidden" name="nivelMagentaFallaNuevo" id="nivelMagentaFallaNuevo"/>
            <input type="hidden" name="nivelAmarilloFallaNuevo" id="nivelAmarilloFallaNuevo" />

            <input type="hidden" name="fechaContadorFallaAnterior" id="fechaContadorFallaAnterior" value="<?php echo $fechaContadorAnterior; ?>" />
            <input type="hidden" name="contadorBNAnterior" id="contadorBNAnterior" value="<?php echo $contadorNegro; ?>" />
            <input type="hidden" name="contadorColorFallaAnterior" id="contadorColorFallaAnterior" value="<?php echo $contadorColor; ?>" />
            <input type="hidden" name="nivelNegroFallaAnterior" id="nivelNegroFallaAnterior" value="<?php echo $nivelNegro; ?>" />
            <input type="hidden" name="nivelCianFallaAnterior" id="nivelCianFallaAnterior" value="<?php echo $nivelCia; ?>" />
            <input type="hidden" name="nivelMagentaFallaAnterior" id="nivelMagentaFallaAnterior" value="<?php echo $nivelMagenta; ?>" />
            <input type="hidden" name="nivelAmarilloFallaAnterior" id="nivelAmarilloFallaAnterior" value="<?php echo $nivelAmarillo; ?>" />


        </form> 
    </div>
    <div id="dialog" >

    </div>
    <?php
    if ($detalle == "1") {
        ?>
        <table id="tAlmacen1" class="tabla_datos" style="width: 100%">
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
                                                    FROM c_notaticket nt LEFT JOIN c_estado e ON nt.IdEstatusAtencion=e.IdEstado WHERE nt.IdTicket='$idticket' ORDER BY nt.FechaHora DESC");
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
<div id="dialogContadorFalla" style="width: 100%;" >
    <fieldset>
        <legend>Contadores y niveles de toner</legend> 
        <table style="width: 100%">
            <tr>
                <td style="width: 50%">
                    <fieldset>
                        <legend>Captura contador y niveles actuales</legend> 
                        <table style="width: 100%">
                            <tr>
                                <td>Fecha:</td><td><input type="text" id="txtFechaContadorFallaNuevo" name="txtFechaContadorFallaNuevo" value="<?php echo $fechaHoraActual; ?>" <?php echo $read; ?>/></td>
                            </tr>
                            <?php if ($tipo == "1") { ?>
                                <tr>
                                    <td>Contador blanco y negro (páginas):</td><td><input type="text" id="txtContadorBNFallaNuevo" name="txtContadorBNFallaNuevo" /></td>
                                </tr>

                                <tr>
                                    <td>Contador color(páginas):</td><td><input type="text" id="txtContadorColorFallaNuevo" name="txtContadorColorFallaNuevo"/></td>
                                </tr>
                                <tr>
                                    <td>Nivel de toner negro(%):</td><td><input type="text" id="txtNivelNegroFallaNuevo" name="txtNivelNegroFallaNuevo"/></td>
                                </tr>
                                <tr>
                                    <td>Nivel de toner cian(%):</td><td><input type="text" id="txtNivelCainFallaNuevo" name="txtNivelCainFallaNuevo"/></td>
                                </tr>
                                <tr>
                                    <td>Nivel de toner magenta(%):</td><td><input type="text" id="txtNivelMagentaFallaNuevo" name="txtNivelMagentaFallaNuevo"/></td>
                                </tr>
                                <tr>
                                    <td>Nivel de toner amarillo(%):</td><td><input type="text" id="txtNivelAmarilloFallaNuevo" name="txtNivelAmarilloFallaNuevo"/></td>
                                </tr>
                            <?php } else {
                                ?>
                                <tr>
                                    <td>Contador blanco y negro (páginas):</td><td><input type="text" id="txtContadorBNFallaNuevo" name="txtContadorBNFallaNuevo"/></td>
                                </tr>
                                <tr>
                                    <td>Nivel de toner negro(%):</td><td><input type="text" id="txtNivelNegroFallaNuevo" name="txtNivelNegroFallaNuevo"/></td>
                                </tr>
                                <?php
                            }
                            ?>
                        </table>
                    </fieldset>
                </td>
                <td style="width: 50%">
                    <fieldset>
                        <legend>Contador anterior y niveles</legend> 
                        <table style="width: 100%">
                            <tr>
                                <td>Fecha:</td><td><input type="text" id="txtFechaContadorFallaAnterior" name="txtFechaContadorFallaAnterior" <?php echo $read; ?> value="<?php echo $fechaContadorAnterior; ?>"/></td>
                            </tr>
                            <?php if ($tipo == "1") { ?>
                                <tr>
                                    <td>Contador blanco y negro (páginas):</td><td><input type="text" id="txtContadorBNFallaAnterior" name="txtContadorBNFallaAnterior" <?php echo $read; ?> value="<?php echo $contadorNegro; ?>"/></td>
                                </tr>                                                                
                                <tr>
                                    <td>Contador color(páginas):</td><td><input type="text" id="txtContadorColorFallaAnterior" name="txtContadorColorFallaAnterior" <?php echo $read; ?> value="<?php echo $contadorColor; ?>"/></td>
                                </tr>
                                <tr>
                                    <td>Nivel de toner negro(%):</td><td><input type="text" id="txtNivelNegroFallaAnterior" name="txtNivelNegroFallaAnterior" <?php echo $read; ?> value="<?php echo $nivelNegro; ?>"/></td>
                                </tr>
                                <tr>
                                    <td>Nivel de toner cian(%):</td><td><input type="text" id="txtNivelCainFallaAnterior" name="txtNivelCainFallaAnterior" <?php echo $read; ?> value="<?php echo $nivelCia; ?>"/></td>
                                </tr>
                                <tr>
                                    <td>Nivel de toner magenta(%):</td><td><input type="text" id="txtNivelMagentaFallaAnterior" name="txtNivelMagentaFallaAnterior"<?php echo $read; ?> value="<?php echo $nivelMagenta; ?>"/></td>
                                </tr>
                                <tr>
                                    <td>Nivel de toner amarillo(%):</td><td><input type="text" id="txtNivelAmarilloFallaAnterior" name="txtNivelAmarilloFallaAnterior" <?php echo $read; ?> value="<?php echo $nivelAmarillo; ?>"/></td>
                                </tr>
                            <?php } else {
                                ?>
                                <tr>
                                    <td>Contador blanco y negro (páginas):</td><td><input type="text" id="txtContadorBNFallaAnterior" name="txtContadorBNFallaAnterior" <?php echo $read; ?> value="<?php echo $contadorNegro; ?>"/></td>
                                </tr>
                                <tr>
                                    <td>Nivel de toner negro(%):</td><td><input type="text" id="txtNivelNegroFallaAnterior" name="txtNivelNegroFallaAnterior" <?php echo $read; ?> value="<?php echo $nivelNegro; ?>"/></td>
                                </tr>
                            <?php }
                            ?>
                        </table>
                    </fieldset>
                </td>
            </tr>
        </table>
    </fieldset>
</div>
</body>
</html>
