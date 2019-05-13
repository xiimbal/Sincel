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
include_once("../WEB-INF/Classes/PermisosSubMenu.class.php");
include_once("../WEB-INF/Classes/Usuario.class.php");
include_once("../WEB-INF/Classes/Parametros.class.php");
include_once("../WEB-INF/Classes/TFSCliente.class.php");

$parametros = new Parametros();

$mostrarContadores = true;
if ($parametros->getRegistroById("13") && $parametros->getValor() == "0") {
    $mostrarContadores = false;
}

$catalogo = new Catalogo();
$obj = new Ticket();
$pedido = new Pedido();
$lecturaTicket = new LecturaTicket();
$permisos_grid = new PermisosSubMenu();
$same_page = "mesa/alta_ticketphp.php";
$permisos_grid->getPermisosSubmenu($_SESSION['idUsuario'], $same_page);

$permisos_grid2 = new PermisosSubMenu();
$nombre_objeto = $permisos_grid2->getNombreTicketSistema();
$nombre_puesto = $permisos_grid2->getNombreTecnicoSistema();

$usuario = new Usuario();
$usuario->setId($_SESSION['idUsuario']);

$clientes_permitidos = $usuario->obtenerNegociosDeUsuario();
$array_clientes = implode("','", $clientes_permitidos);
if (!empty($array_clientes)) {
    $array_clientes = "'$array_clientes'";
}

if (isset($_POST['regresar']) && $_POST['regresar']) {
    $pagina_listaRegresar = $_POST['regresar'];
} else {
    $pagina_listaRegresar = "mesa/lista_ticket.php";
}

//Verificamos que tenga permiso para modificar el estado.
$modificar_estado = "disabled";
if ($permisos_grid->tienePermisoEspecial($usuario->getId(), 31)) {
    $modificar_estado = "";
}

$UbicaiconNoDomicilio = "";
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
$fechaContadorAnterior = "";
$comentario_lectura = "";
$detalle = "";
$desactivarCheckPedido = "";
$drawList = "";
$noSerie = "";
$desactivarRadioPedido = "";
$desactivarRadio = "";
$tfs = "";
$readCliente = "readonly";
$botonCancelar = "";
$botonBuscar = "";
$tipoServicio = "";
$ubicaionNo = "";
$descativarClienteLocalidad = "";
$descativarTipoReporte = "";
$EstadoTicketDatos = "";
$claveLocalidadEstadoTicket = "";
$rango = "";
$NoGuia = "";

if (isset($_POST['detalle']) && ($_POST['detalle'] == "0" || $_POST['detalle'] == "1")) {
    $detalle = $_POST['detalle'];
} else if (isset($_GET['detalle']) && ($_GET['detalle'] == "0" || $_GET['detalle'] == "1")) {
    $detalle = $_GET['detalle'];
}

if ($detalle == "1") {
    $desactivarCheckPedido = "disabled";
    $descativarTipoReporte = "disabled";
    $descativarClienteLocalidad = "disabled";
    $botonBuscar = "disabled";
    $ubicaionNo = "readonly";
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
    $tipoReporte = $_POST["area"];
} else if (isset($_GET["idTicket"]) && $_GET["idTicket"] != "" && isset($_GET["area"]) && $_GET["area"] != "") {
    $idTicket = $_GET["idTicket"];
    $tipoReporte = $_GET["area"];
}

if ($idTicket != "" && $tipoReporte != "") {

    echo "<br/><br/>$nombre_objeto:&nbsp;&nbsp; <input type='text' value='$idTicket' readonly/>";
    if ($detalle == "1") {
        ?>
        <a href='#' onclick='detalleReporte("reportes/reporte_ticket.php", "<?php echo $idTicket; ?>", "<?php echo $tipoReporte; ?>", "0");
                        return false;' title='Reporte' >
               <?php
               if (isset($_GET['frame']) && $_GET['frame'] == "1") {
                   echo '<img src="../resources/images/icono_impresora.png" width="25" height="25"/>';
               } else {
                   echo '<img src="resources/images/icono_impresora.png" width="25" height="25"/>';
               }
               ?>            
        </a>
        <?php
    }
    $obj->getTicketByID($idTicket);
    $estadoTicket = $obj->getEstadoDeTicket();

    $claveCliente = $obj->getClaveCliente();
    $claveLocalidad = $obj->getClaveCentroCosto();
    $claveLocalidadEstadoTicket = $obj->getClaveCentroCosto();
    // echo $claveLocalidad;
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
    $NoGuia = $obj->getNoGuia();
    $nombreTabla = "tAlmacen1";
    $noSerie = $obj->getNoSerieEquipo();
    //$drawList = "disabled";
    $read = "readonly";
    // $desactivarRadio = "disabled";
    $EstadoTicketDatos = $obj->getEstadoDeTicket();
}
if ($idTicket != "") {//buscar pedido
    $pedido->getPedidoByIdTicket($idTicket);
    $arrayPedidoNoSerie = $pedido->getArrayNoSerie();
    $arrayPedidoNegro = $pedido->getArrayNegro();
    $arrayPedidoCian = $pedido->getArrayCian();
    $arrayPedidoMagenta = $pedido->getArrayMagenta();
    $arrayPedidoAmarrillo = $pedido->getArrayAmarillo();
}

$idUsuario = $_SESSION['idUsuario'];
$idPuesto = "";
$queryTipoCliente = $catalogo->obtenerLista("SELECT u.IdPuesto FROM c_usuario u WHERE u.IdUsuario='$idUsuario'");
while ($rs = mysql_fetch_array($queryTipoCliente)) {
    $idPuesto = $rs['IdPuesto'];
}
$onsultaCliente = "";
if (!empty($clientes_permitidos)) {
    $consultaCliente = "SELECT c.ClaveCliente,c.IdEstatusCobranza,c.NombreRazonSocial,c.Suspendido FROM c_cliente c WHERE c.ClaveCliente IN($array_clientes) AND c.Activo=1 ORDER BY c.NombreRazonSocial ASC";
} else if ($idPuesto == "21") {
    $botonCancelar = "1";
    $consultaCliente = "SELECT c.ClaveCliente,c.IdEstatusCobranza,c.NombreRazonSocial,c.Suspendido FROM k_tfscliente tfs,c_cliente c WHERE tfs.ClaveCliente=c.ClaveCliente AND tfs.IdUsuario='$idUsuario'";
} else if ($idPuesto == "11") {
    $consultaCliente = "SELECT * FROM c_cliente c WHERE c.EjecutivoCuenta='$idUsuario'";
} else {
    $consultaCliente = "SELECT c.ClaveCliente,c.IdEstatusCobranza,c.NombreRazonSocial,c.Suspendido FROM c_cliente c WHERE c.Activo=1 ORDER BY c.NombreRazonSocial ASC";
}

if (isset($_POST["noSerie"]) && $_POST["noSerie"] != "") {
    $noSerie = $_POST["noSerie"];
}

if (isset($_POST["area"]) && $_POST["area"] != "") {
    $tipoReporte = $_POST["area"];
} else if (isset($_GET["area"]) && $_GET["area"] != "") {
    $tipoReporte = $_GET["area"];
}

if ($noSerie != "") {//datos del equipo
    $consulta = "SELECT ie.NoSerie,(SELECT CASE WHEN ISNULL(ie.IdKserviciogimgfa) 
                THEN (SELECT cc.ClaveCentroCosto FROM k_anexoclientecc an,c_centrocosto cc WHERE cc.ClaveCentroCosto=an.CveEspClienteCC AND an.IdAnexoClienteCC=ie.IdAnexoClienteCC)
                ELSE (SELECT cc.ClaveCentroCosto FROM c_centrocosto cc,k_serviciogimgfa sg WHERE sg.IdKserviciogimgfa=ie.IdKserviciogimgfa AND sg.ClaveCentroCosto=cc.ClaveCentroCosto)END )AS Localidad,
                (SELECT CASE WHEN ISNULL(ie.IdKserviciogimgfa) 
                THEN (SELECT cc.ClaveCliente FROM k_anexoclientecc an,c_centrocosto cc WHERE cc.ClaveCentroCosto=an.CveEspClienteCC AND an.IdAnexoClienteCC=ie.IdAnexoClienteCC)
                ELSE (SELECT cc.ClaveCliente FROM c_centrocosto cc,k_serviciogimgfa sg WHERE sg.IdKserviciogimgfa=ie.IdKserviciogimgfa AND sg.ClaveCentroCosto=cc.ClaveCentroCosto)END )AS Cliente,fs.IdTipoServicio AS tipoServicio
                FROM c_inventarioequipo ie,k_equipocaracteristicaformatoservicio fs WHERE ie.NoSerie='$noSerie' AND fs.NoParte=ie.NoParteEquipo AND fs.IdTipoServicio<>2 ORDER BY fs.IdFormatoEquipo ASC LIMIT 1";
    $queryEquipoSerie = $catalogo->obtenerLista($consulta);
    while ($rs = mysql_fetch_array($queryEquipoSerie)) {
        $claveLocalidad = $rs['Localidad'];
        $claveCliente = $rs['Cliente'];
        $tipoServicio = $rs['tipoServicio'];
    }
    $lecturaTicket->setNoSerie($noSerie);
    $lecturaTicket->getLecturaBYNoSerie();
    $fechaContadorAnterior = $lecturaTicket->getFechaA();
    $contadorNegroAnterior = $lecturaTicket->getContadorBNA();
    $contadorColorAnterior = $lecturaTicket->getContadorColorA();
    $queryUbucaion = $catalogo->obtenerLista("SELECT ie.Ubicacion FROM c_inventarioequipo ie WHERE ie.NoSerie='$noSerie'");
    while ($rs = mysql_fetch_array($queryUbucaion)) {
        $UbicaiconNoDomicilio = $rs['Ubicacion'];
    }
}
$listaNoSeries = "";
if (isset($_POST['listSerie']) && $_POST['listSerie'] != "") {
    $listaNoSeries = $_POST['listSerie'];
}
$permisoEspecialRendimiento = "";
if ($permisos_grid->tienePermisoEspecial($_SESSION['idUsuario'], 12)) {
    $permisoEspecialRendimiento = "1";
} else {
    $permisoEspecialRendimiento = "0";
}
?>
<!DOCTYPE html>
<html lang="es">
    <head>
        <?php
        if (isset($_GET['frame']) && $_GET['frame'] == "1") {
            $path_previo = "../";
            echo '<link rel="stylesheet" href="../resources/css/genesis/jquery-ui-1.10.3.custom.css" type="text/css" media="screen" />
                <script src="../resources/js/jquery/jquery-1.11.3.min.js"></script>
                <script src="../resources/js/jquery/jquery-ui.min.js"></script>  
                <script type="text/javascript" src="../resources/js/jquery/jquery.validate.js"></script>
                <script type="text/javascript" src="../resources/js/jquery/jquery-ui-timepicker-addon.js"></script>
                <script type="text/javascript" src="../resources/js/jquery/jquery.maskedinput.min.js"></script>
                <script type="text/javascript" src="../resources/js/funciones.js"></script>                   

                <style type="text/css">
                .centerFieldset {
                text-align:center;
                }

                .centerFieldset fieldset {
                display:inline;
                margin-left:auto;
                margin-right:auto;
                text-align:left;
                }
                </style>
                
                <!-- Tables -->
                <script type="text/javascript" language="javascript" src="../resources/media/js/jquery.dataTables.js"></script>
                <script type="text/javascript" language="javascript" src="../resources/media/js/TableTools.min.js"></script>
                <link href="../resources/css/table/demo_page.css" rel="stylesheet" type="text/css">
                <link href="../resources/css/table/demo_table_jui.css" rel="stylesheet" type="text/css">
                <link href="../resources/css/table/TableTools.css" rel="stylesheet" type="text/css">
                <link href="../resources/css/sicop.css" rel="stylesheet" type="text/css">  

                <!-- multiselect -->
                <script src="../resources/js/multiselect/jquery.multiselect.min.js"></script>
                <script src="../resources/js/multiselect/jquery.multiselect.filter.min.js"></script>
                <link href="../resources/css/multiselect/jquery.multiselect.css" rel="stylesheet" type="text/css">
                <link href="../resources/css/multiselect/jquery.multiselect.filter.css" rel="stylesheet" type="text/css">';
            echo '<script type="text/javascript" language="javascript" src="../resources/js/paginas/nuevoTicket.js"></script>';
        } else {
            $path_previo = "";
            echo '<script type="text/javascript" language="javascript" src="resources/js/paginas/nuevoTicket.js"></script>';
        }
        ?>

        <script>
            $(function () {

                var availableTags = [
<?php
$arrayNoSerie = array();
$c = 0;
$query1 = $catalogo->obtenerLista("SELECT ie.NoSerie,an.CveEspClienteCC FROM c_inventarioequipo ie,k_anexoclientecc an WHERE ie.IdAnexoClienteCC=an.IdAnexoClienteCC");
while ($rs = mysql_fetch_array($query1)) {
    $arrayNoSerie[$c] = $rs['NoSerie'];
    $c++;
}
for ($x = 0; $x < count($arrayNoSerie); $x++) {
    echo "'" . $arrayNoSerie[$x] . "',";
}
?>
                ];
                $("#txtNoSrieFallaBuscar").autocomplete({
                    source: availableTags,
                    minLength: 2
                });
            });</script>

    </head>
    <body>
        <div class="principal"> 
            <form id="frmAltaTicket" name="frmAltaTicket" action="/" method="POST">
                <div>
                    <table style="width: 100%">
                        <tr><td>Tipo de reporte:</td>
                            <td>
                                <select id="sltTipoReporte" name="sltTipoReporte" onchange="MostrarTipoReporte(this.value);" <?php echo $descativarTipoReporte; ?> >
                                    <option value="0">Seleccione tipo de reporte</option>
                                    <?php
                                    $consultaTipo = "SELECT * FROM c_estado e,k_flujoestado fe WHERE e.IdEstado=fe.IdEstado AND fe.IdFlujo=1";
                                    $tipoReporteConsulta = $catalogo->obtenerLista($consultaTipo);
                                    while ($rs = mysql_fetch_array($tipoReporteConsulta)) {
                                        $s = "";
                                        if ($tipoReporte == $rs['IdEstado']){
                                            $s = "selected";
                                        }
                                        echo "<option value='" . $rs['IdEstado'] . "' $s>" . $rs['Nombre'] . "</option>";
                                    }
                                    ?>
                                </select>
                            </td>
                            <td>Estado del <?php echo $nombre_objeto; ?>:</td>
                            <td>
                                <select id="sltEstadoTicket" name="sltEstadoTicket" <?php echo $drawList . " " . $modificar_estado; ?> >
                                    <option value="0">Seleccione el estado del <?php echo $nombre_objeto; ?></option>  
                                    <?php
                                    $consultaEstadoTicket = "SELECT * FROM c_estadoticket et WHERE et.Activo=1 ORDER BY et.Nombre ASC";
                                    $queryEstado = $catalogo->obtenerLista($consultaEstadoTicket);
                                    while ($rs = mysql_fetch_array($queryEstado)) {
                                        $s = "";
                                        if ($estadoTicket == $rs['IdEstadoTicket']) {
                                            $s = "selected";
                                        }
                                        echo "<option value='" . $rs['IdEstadoTicket'] . "' $s>" . $rs['Nombre'] . "</option>";
                                    }
                                    ?> 
                                </select>
                            </td>
                        </tr>
                    </table>
                    <?php if ($detalle == "1") { 
                        //Aquí pondremos las ligas para los manuales.
                        echo "<div class='centerFieldset'>";
                        echo "<fieldset style='width:100%'>";
                        echo "<legend>Manuales</legend>";
                        $queryPDFs = "SELECT DISTINCT
                            (CASE WHEN !ISNULL(e.PathEspecificacionesTecnicas) THEN e.PathEspecificacionesTecnicas ELSE e2.PathEspecificacionesTecnicas END) AS PathEspecificacionesTecnicas,
                            (CASE WHEN !ISNULL(e.PathGuiaOperacionAvanza) THEN e.PathGuiaOperacionAvanza ELSE e2.PathGuiaOperacionAvanza END) AS PathGuiaOperacionAvanza,
                            (CASE WHEN !ISNULL(e.PathListaPartes) THEN e.PathListaPartes ELSE e2.PathListaPartes END) AS PathListaPartes,
                            (CASE WHEN !ISNULL(e.PathOperacion) THEN e.PathOperacion ELSE e2.PathOperacion END) AS PathOperacion,
                            (CASE WHEN !ISNULL(e.PathManualServicio) THEN e.PathManualServicio ELSE e2.PathManualServicio END) AS PathManualServicio
                            FROM c_ticket t
                            LEFT JOIN c_notaticket AS nt ON t.IdTicket = nt.IdTicket 
                            LEFT JOIN c_pedido AS p ON t.IdTicket = p.IdTicket 
                            LEFT JOIN c_bitacora AS b ON p.ClaveEspEquipo = b.NoSerie 
                            LEFT JOIN c_equipo AS e ON e.NoParte = b.NoParte
                            LEFT JOIN c_bitacora AS b2 ON b2.NoSerie = t.NoSerieEquipo
                            LEFT JOIN c_equipo AS e2 ON e2.NoParte = b2.NoParte
                            WHERE t.IdTicket = $idTicket;";
                        $resultPDFs = $catalogo->obtenerLista($queryPDFs);
                        echo "<table width='100%'>";
                        echo "<tr>";
                        while($rs = mysql_fetch_array($resultPDFs)){
                            if(isset($rs['PathEspecificacionesTecnicas']) && $rs['PathEspecificacionesTecnicas'] != ""){
                                echo "<td><a href='WEB-INF/Controllers/documentos/equipos/" . $rs['PathEspecificacionesTecnicas'] . "' target='_blank'>Especificaciones Técnicas </a></td>";
                            }
                            if(isset($rs['PathGuiaOperacionAvanza']) && $rs['PathGuiaOperacionAvanza'] != ""){
                                echo "<td><a href='WEB-INF/Controllers/documentos/equipos/" . $rs['PathGuiaOperacionAvanza'] . "' target='_blank'>Guía operación </a></td>";
                            }
                            if(isset($rs['PathListaPartes']) && $rs['PathListaPartes'] != ""){
                                echo "<td><a href='WEB-INF/Controllers/documentos/equipos/" . $rs['PathListaPartes'] . "' target='_blank'>Lista de partes </a></td>";
                            }
                            if(isset($rs['PathOperacion']) && $rs['PathOperacion'] != ""){
                                echo "<td><a href='WEB-INF/Controllers/documentos/equipos/" . $rs['PathOperacion'] . "' target='_blank'>Guía de operación </a></td>";
                            }
                            if(isset($rs['PathManualServicio']) && $rs['PathManualServicio'] != ""){
                                echo "<td><a href='WEB-INF/Controllers/documentos/equipos/" . $rs['PathManualServicio'] . "' target='_blank'>Manual de servicio </a></td>";
                            }
                        }
                        echo "</tr>";
                        echo "</table>";
                        echo "</fieldset></div>";
                        ?>
                        <table id="tAlmacen2" class="tabla_datos" style="width: 100%">
                            <thead>
                                <tr>
                                    <th style="text-align: center; min-width:10%">Fecha y Hora</th>
                                    <th style="text-align: center; min-width: 25%">Diagnostico</th>
                                    <th style="text-align: center; min-width: 15%">Estatus de Atención</th>
                                    <th style="text-align: center; min-width: 20%">Tipo solución</th>
                                    <th style="text-align: center; min-width: 15%">Técnico</th>
                                    <th style="text-align: center; min-width: 15%">Detalle</th>
                                    <th style="text-align: center; min-width: 15%">Recurso</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                /* Inicializamos la clase */
                                $query = $catalogo->obtenerLista("SELECT nt.IdNotaTicket,nt.FechaHora,nt.DiagnosticoSol,nt.IdEstatusAtencion,e.Nombre AS estatus,nt.UsuarioUltimaModificacion, nt.PathImagen, nt.MostrarCliente 
                                                    FROM c_notaticket nt LEFT JOIN c_estado e ON nt.IdEstatusAtencion=e.IdEstado WHERE nt.IdTicket='$idTicket' AND nt.Activo=1 ORDER BY nt.FechaHora DESC");
                                while ($rs = mysql_fetch_array($query)) {
                                    if (!empty($clientes_permitidos) && $rs['MostrarCliente'] == "0") {
                                        continue;
                                    }
                                    if ($rs['IdEstatusAtencion'] != "65") {
                                        echo "<tr>";
                                        echo "<td align='center' scope='row' style='font-size:11px'>" . $rs['FechaHora'] . "</td>";
                                        echo "<td align='center' scope='row' style='font-size:11px'>" . $rs['DiagnosticoSol'] . "</td>";
                                        echo "<td align='center' scope='row' style='font-size:11px'>" . $rs['estatus'] . "</td>";
                                        echo "<td align='center' scope='row' style='font-size:11px'></td>";
                                        echo "<td align='center' scope='row' style='font-size:11px'>" . $rs['UsuarioUltimaModificacion'] . "</td>";
                                        echo "<td align='center' scope='row' style='font-size:11px'>";
                                        ?>
                                    <a href='#' onclick='mostrarDetalleNota("<?php echo $path_previo; ?>mesa/detalleNota.php?frame=1", "<?php echo $rs['IdNotaTicket'] ?>", "<?php echo $tipoReporte ?>");
                                                        return false;' title='Detalle' > 
                                        <img src='<?php echo $path_previo; ?>resources/images/Textpreview.png'/> 
                                    </a>
                                    <?php
                                    echo "</td><td align='center' scope='row' style='font-size:11px'>";
                                    if (isset($rs['PathImagen']) && $rs['PathImagen'] != "") {
                                        echo "<a href='" . $rs['PathImagen'] . "' target='_blank'>Ver evidencia </a>";
                                    }
                                    echo "</td></tr>";
                                }
                            }
                            ?>
                            </tbody>
                        </table>
                        <?php
                    }
                    ?>
                </div> 
                <br/><br/>
                <?php if ($tipoReporte != "" && $tipoReporte != "0") { ?>
                <div>                    
                        <div id="tabs">
                            <ul>
                                <li><a href="#tabs-1">Pedido</a></li>
                                <li><a href = "#tabs-2">Cliente</a></li>
                            </ul>
                            <?php if ($tipoReporte == "15") { ?>
                                <div id = "tabs-1" style = "background-color: #A4A4A4">
                                    <table style="width: 50%">
                                        <tr>
                                            <td>No serie</td>
                                            <td><input type="text" id='txtNoSrieTonerBuscar' name='txtNoSrieTonerBuscar' value="<?php echo $noSerie ?>"/><input type='button' id="botonBuscar" name="botonBuscar" value="Buscar" onclick="BuscarEquipoCliLocEqui('txtNoSrieTonerBuscar', 'slcCliente', 'slcLocalidad', 'selectNoSerie')" class="boton" <?php echo $botonBuscar; ?>/></td>
                                        </tr> 
                                        <tr>
                                            <td>Cliente</td>
                                            <td>
                                                <select id="slcCliente" name="slcCliente" style="width: 300px" onchange="incidenciaClienteSuspendido(this.value)" class="filtro" <?php echo $descativarClienteLocalidad; ?> >
                                                    <option value="0">Seleccione un cliente</option>
                                                    <?php
                                                    $todosClientes = true;
                                                    $tfsCliente = new TFSCliente();
                                                    $tfsCliente->setIdUsuario($_SESSION['idUsuario']);
                                                    $resultTFS = $tfsCliente->getClientesByTFS();
                                                    while($rsTFS = mysql_fetch_array($resultTFS)){
                                                        $todosClientes = false;
                                                        $s = "";
                                                        if ($claveCliente != "" && $claveCliente == $rsTFS['ClaveCliente']) {
                                                            $nombreCliente = $rsTFS['NombreRazonSocial'];
                                                            $s = "selected";
                                                        }
                                                        echo "<option value='" . $rsTFS['ClaveCliente'] . "' $s>" . $rsTFS['NombreRazonSocial'] . "</option>";
                                                    }
                                                    
                                                    if($todosClientes){
                                                        $queryCliente = $catalogo->obtenerLista($consultaCliente);
                                                        while ($rs = mysql_fetch_array($queryCliente)) {
                                                            $s = "";
                                                            if ($claveCliente != "" && $claveCliente == $rs['ClaveCliente']) {
                                                                $nombreCliente = $rs['NombreRazonSocial'];
                                                                $s = "selected";
                                                            }
                                                            echo "<option value='" . $rs['ClaveCliente'] . "' $s>" . $rs['NombreRazonSocial'] . "</option>";
                                                        }
                                                    }
                                                    ?>
                                                </select>
                                            </td>
                                            <td></td>
                                        </tr>
                                        <tr>
                                            <?php
                                            $permisoEspecial = "";
                                            if ($permisos_grid->tienePermisoEspecial($_SESSION['idUsuario'], 8)) {
                                                $permisoEspecial = "1";
                                            } else {
                                                $permisoEspecial = "0";
                                            }
                                            ?>
                                            <td>Localidad</td>                                            
                                            <td>          
                                                <input type="hidden" id="permisoTicketMiniAlmacen" name="permisoTicketMiniAlmacen" value="<?php echo $permisoEspecial; ?>"/>
                                                <select id="slcLocalidad" name="slcLocalidad" style="width: 300px" class="filtro" onchange="CambioLocalidadTicketToner(this.value, '<?php echo $permisoEspecial ?>');" <?php echo $descativarClienteLocalidad; ?> >
                                                    <?php
                                                    if ($claveCliente != "") {
                                                        $queryCliente = $catalogo->obtenerLista("SELECT cc.ClaveCentroCosto,cc.Nombre FROM c_centrocosto cc WHERE cc.ClaveCliente='$claveCliente' AND cc.Activo=1 ORDER BY cc.Nombre ASC;");
                                                        echo " <option value='0'>Seleccione una localidad</option>";
                                                        while ($rs = mysql_fetch_array($queryCliente)) {
                                                            $s = "";
                                                            if ($claveLocalidad != "" && $claveLocalidad == $rs['ClaveCentroCosto']) {
                                                                $nombreLocalidad = $rs['Nombre'];
                                                                $s = "selected";
                                                            }
                                                            echo "<option value=" . $rs['ClaveCentroCosto'] . " " . $s . ">" . $rs['Nombre'] . "</option>";
//                                                            
                                                        }
                                                    }
                                                    ?>
                                                </select>
                                            </td>
                                            <td></td>
                                        </tr>
                                        <?php if ($detalle == "") { ?>
                                            <tr>
                                                <td>No Serie</td>
                                                <td>
                                                    <select id="selectNoSerie" name="selectNoSerie[]" multiple="multiple" style="width: 300px" <?php echo $descativarClienteLocalidad; ?> >
                                                        <?php
                                                        if ($claveLocalidad != "") {
                                                            $arraySeries = array();
                                                            $noSerieArray = explode(" / ", $listaNoSeries);
                                                            for ($x = 0; $x < count($noSerieArray); $x++) {
                                                                $arraySeries[$x] = $noSerieArray[$x];
                                                            }
                                                            // $lista = "'" . implode("','", $arraySeries) . "'";
                                                            $query = $catalogo->obtenerLista("SELECT DISTINCT(cie.NoSerie) AS NoSerie,e.NoParte AS NoParteEquipo,e.Modelo,
                                                                                    (SELECT ke.IdTipoServicio FROM k_equipocaracteristicaformatoservicio AS ke WHERE ke.NoParte = cie.NoParteEquipo ORDER BY ke.IdTipoServicio ASC LIMIT 1) AS tipoFormato
                                                                                    FROM k_anexoclientecc AS kacc LEFT JOIN c_inventarioequipo AS cie ON cie.IdAnexoClienteCC = kacc.IdAnexoClienteCC LEFT JOIN c_centrocosto AS cc ON cc.ClaveCentroCosto = kacc.CveEspClienteCC LEFT JOIN k_serviciogimgfa AS ks ON ks.IdKServiciogimgfa = cie.IdKServiciogimgfa LEFT JOIN c_centrocosto AS cc2 ON cc2.ClaveCentroCosto = ks.ClaveCentroCosto
                                                                                    LEFT JOIN c_equipo AS e ON e.NoParte = cie.NoParteEquipo WHERE !ISNULL(cie.NoSerie) AND ((kacc.CveEspClienteCC = '$claveLocalidad' AND ISNULL(cie.IdKServiciogimgfa)) OR (!ISNULL(cie.IdKServiciogimgfa) && ks.ClaveCentroCosto = '$claveLocalidad')) ORDER BY NoSerie DESC");
                                                            while (($serie = mysql_fetch_array($query))) {
                                                                $s = "";
                                                                for ($x = 0; $x < count($arraySeries); $x++) {
                                                                    if ($arraySeries[$x] == $serie['NoSerie']){
                                                                        $s = "selected";
                                                                    }
                                                                }
                                                                echo "<option value='" . $serie['NoSerie'] . "' $s>" . $serie['NoSerie'] . " / " . $serie['Modelo'] . "</option>";
                                                            }
                                                        }
                                                        ?>
                                                    </select>
                                                    <div id='errorSelectNoSerie'></div>
                                                </td>
                                                <td><input type="button" value="Buscar" class="boton" onclick="BuscarEquiposNumeroSerieLocalidad();"/></td>
                                            </tr>
                                        <?php } ?>
                                    </table>
                                    <?php
                                    if ($listaNoSeries != "" || $claveLocalidad != "") {
                                        $arraySeries = array();
                                        $noSerieArray = explode(" / ", $listaNoSeries);
                                        for ($x = 0; $x < count($noSerieArray); $x++) {
                                            $arraySeries[$x] = $noSerieArray[$x];
                                        }
                                        $lista = "'" . implode("','", $arraySeries) . "'";
                                        //print_r ($noSerieArray);
                                        $query = $catalogo->obtenerLista("SELECT DISTINCT(cie.NoSerie) AS NoSerie,e.NoParte AS NoParteEquipo,e.Modelo,
                                                                                    (SELECT ke.IdTipoServicio FROM k_equipocaracteristicaformatoservicio AS ke WHERE ke.NoParte = cie.NoParteEquipo ORDER BY ke.IdTipoServicio ASC LIMIT 1) AS tipoFormato
                                                                                    FROM k_anexoclientecc AS kacc LEFT JOIN c_inventarioequipo AS cie ON cie.IdAnexoClienteCC = kacc.IdAnexoClienteCC LEFT JOIN c_centrocosto AS cc ON cc.ClaveCentroCosto = kacc.CveEspClienteCC LEFT JOIN k_serviciogimgfa AS ks ON ks.IdKServiciogimgfa = cie.IdKServiciogimgfa LEFT JOIN c_centrocosto AS cc2 ON cc2.ClaveCentroCosto = ks.ClaveCentroCosto
                                                                                    LEFT JOIN c_equipo AS e ON e.NoParte = cie.NoParteEquipo WHERE !ISNULL(cie.NoSerie) AND ((kacc.CveEspClienteCC = '$claveLocalidad' AND ISNULL(cie.IdKServiciogimgfa)) OR (!ISNULL(cie.IdKServiciogimgfa) && ks.ClaveCentroCosto = '$claveLocalidad')) AND cie.NoSerie IN ($lista)  ORDER BY NoSerie DESC");
                                        ?>
                                        <br/><br/>
                                        <table id="<?php echo $nombreTabla; ?>" class="tabla_datos" style="width: 100%; max-width: 100%;">
                                            <thead>
                                                <tr>
                                                    <th style="text-align: center; min-width:10%">Activar</th>
                                                    <th style="text-align: center; min-width:10%">No Serie<br/>Contador B/N</th>
                                                    <th style="text-align: center; min-width: 20%">Modelo equipo<br/>Contador Color</th>
                                                    <th style="text-align: center; min-width: 15%">Negro<br/>Negro %</th>
                                                    <th style="text-align: center; min-width: 15%">Cian<br/>Cian %</th>
                                                    <th style="text-align: center; min-width: 15%">Magenta<br/>Magenta %</th>
                                                    <th style="text-align: center; min-width: 15%">Amarillo<br/>Amarillo %</th>    
                                                    <th style="text-align: center; min-width: 10%">Comentario</th>    
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php
                                                if ($detalle == "1") {
                                                    $consultaEquiposTicket = "SELECT DISTINCT(cie.NoSerie) AS NoSerie,e.NoParte AS NoParteEquipo,e.Modelo,cie.ServicioSinToner,
                                                                                    (SELECT ke.IdTipoServicio FROM k_equipocaracteristicaformatoservicio AS ke WHERE ke.NoParte = cie.NoParteEquipo ORDER BY ke.IdTipoServicio ASC LIMIT 1) AS tipoFormato
                                                                                    FROM k_anexoclientecc AS kacc 
                                                                                    LEFT JOIN c_inventarioequipo AS cie ON cie.IdAnexoClienteCC = kacc.IdAnexoClienteCC 
                                                                                    LEFT JOIN c_bitacora AS b ON b.NoSerie = cie.NoSerie
                                                                                    LEFT JOIN c_centrocosto AS cc ON cc.ClaveCentroCosto = kacc.CveEspClienteCC LEFT JOIN k_serviciogimgfa AS ks ON ks.IdKServiciogimgfa = cie.IdKServiciogimgfa LEFT JOIN c_centrocosto AS cc2 ON cc2.ClaveCentroCosto = ks.ClaveCentroCosto                                                                                  
                                                                                    LEFT JOIN c_equipo AS e ON e.NoParte = cie.NoParteEquipo,c_pedido AS p,c_ticket t   
                                                                                    WHERE !ISNULL(cie.NoSerie) AND b.Activo = 1 AND ((kacc.CveEspClienteCC = '$claveLocalidad' AND ISNULL(cie.IdKServiciogimgfa)) OR (!ISNULL(cie.IdKServiciogimgfa) && ks.ClaveCentroCosto = '$claveLocalidad')) AND p.ClaveEspEquipo=cie.NoSerie AND t.IdTicket=p.IdTicket AND t.IdTicket='$idTicket'  ORDER BY NoSerie DESC";
                                                } else if ($detalle == "0") {
                                                    $consultaEquiposTicket = "SELECT DISTINCT(cie.NoSerie) AS NoSerie,e.NoParte AS NoParteEquipo,e.Modelo,cie.ServicioSinToner,
                                                                                    (SELECT ke.IdTipoServicio FROM k_equipocaracteristicaformatoservicio AS ke WHERE ke.NoParte = cie.NoParteEquipo ORDER BY ke.IdTipoServicio ASC LIMIT 1) AS tipoFormato
                                                                                    FROM k_anexoclientecc AS kacc 
                                                                                    LEFT JOIN c_inventarioequipo AS cie ON cie.IdAnexoClienteCC = kacc.IdAnexoClienteCC 
                                                                                    LEFT JOIN c_bitacora AS b ON b.NoSerie = cie.NoSerie
                                                                                    LEFT JOIN c_centrocosto AS cc ON cc.ClaveCentroCosto = kacc.CveEspClienteCC 
                                                                                    LEFT JOIN k_serviciogimgfa AS ks ON ks.IdKServiciogimgfa = cie.IdKServiciogimgfa 
                                                                                    LEFT JOIN c_centrocosto AS cc2 ON cc2.ClaveCentroCosto = ks.ClaveCentroCosto
                                                                                    LEFT JOIN c_equipo AS e ON e.NoParte = cie.NoParteEquipo 
                                                                                    WHERE !ISNULL(cie.NoSerie) AND b.Activo = 1 AND ((kacc.CveEspClienteCC = '$claveLocalidad' AND ISNULL(cie.IdKServiciogimgfa)) OR (!ISNULL(cie.IdKServiciogimgfa) && ks.ClaveCentroCosto = '$claveLocalidad')) ORDER BY NoSerie DESC";
                                                } else {
                                                    $consultaEquiposTicket = "SELECT DISTINCT(cie.NoSerie) AS NoSerie,e.NoParte AS NoParteEquipo,e.Modelo,cie.ServicioSinToner,
                                                                                    (SELECT ke.IdTipoServicio FROM k_equipocaracteristicaformatoservicio AS ke WHERE ke.NoParte = cie.NoParteEquipo ORDER BY ke.IdTipoServicio ASC LIMIT 1) AS tipoFormato
                                                                                    FROM k_anexoclientecc AS kacc 
                                                                                    LEFT JOIN c_inventarioequipo AS cie ON cie.IdAnexoClienteCC = kacc.IdAnexoClienteCC 
                                                                                    LEFT JOIN c_bitacora AS b ON b.NoSerie = cie.NoSerie
                                                                                    LEFT JOIN c_centrocosto AS cc ON cc.ClaveCentroCosto = kacc.CveEspClienteCC 
                                                                                    LEFT JOIN k_serviciogimgfa AS ks ON ks.IdKServiciogimgfa = cie.IdKServiciogimgfa 
                                                                                    LEFT JOIN c_centrocosto AS cc2 ON cc2.ClaveCentroCosto = ks.ClaveCentroCosto
                                                                                    LEFT JOIN c_equipo AS e ON e.NoParte = cie.NoParteEquipo 
                                                                                    WHERE !ISNULL(cie.NoSerie) AND b.Activo = 1 AND ((kacc.CveEspClienteCC = '$claveLocalidad' AND ISNULL(cie.IdKServiciogimgfa)) OR (!ISNULL(cie.IdKServiciogimgfa) && ks.ClaveCentroCosto = '$claveLocalidad')) AND cie.NoSerie IN ($lista) ORDER BY NoSerie DESC";
                                                }
                                                //echo $consultaEquiposTicket;
                                                $queryEquipos = $catalogo->obtenerLista($consultaEquiposTicket);
                                                $contador = 0;
                                                while ($rs = mysql_fetch_array($queryEquipos)) {
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
                                                        if ($idTicket == "") {
                                                            $consultaLecturas = "SELECT lt.ContadorBN,lt.ContadorCL,lt.NivelTonNegro,
                                                                lt.NivelTonCian,lt.NivelTonMagenta,lt.NivelTonAmarillo,lt.ContadorBNA,lt.ContadorCLA,lt.NivelTonNegroA,
                                                                lt.NivelTonCianA,lt.NivelTonMagentaA,lt.NivelTonAmarilloA ,lt.FechaA,lt.Comentario
                                                                FROM c_lecturasticket lt WHERE lt.ClvEsp_Equipo='" . $rs['NoSerie'] . "' ORDER BY lt.id_lecturaticket DESC LIMIT 1;";
                                                        } else {
                                                            $consultaLecturas = "SELECT lt.ContadorBN,lt.ContadorCL,lt.NivelTonNegro,
                                                                lt.NivelTonCian,lt.NivelTonMagenta,lt.NivelTonAmarillo,lt.ContadorBNA,lt.ContadorCLA,lt.NivelTonNegroA,
                                                                lt.NivelTonCianA,lt.NivelTonMagentaA,lt.NivelTonAmarilloA ,lt.FechaA,lt.Comentario
                                                                FROM c_lecturasticket lt WHERE lt.fk_idticket = $idTicket AND lt.ClvEsp_Equipo='" . $rs['NoSerie'] . "' ORDER BY lt.id_lecturaticket DESC LIMIT 1;";
                                                        }
ECHO $consultaLecturas;
                                                        $queryLecturas = $catalogo->obtenerLista($consultaLecturas);
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
                                                            $comentario_lectura = $rs3['Comentario'];
                                                        }
                                                        $consultaPedido = "SELECT c.NoParte, c.Modelo, c.Descripcion,dn.NoSerieEquipo,c.IdColor
                                                                            FROM c_notaticket nt,k_detalle_notarefaccion dn,c_componente c
                                                                            WHERE nt.IdNotaTicket=dn.IdNota AND dn.Componente=c.NoParte AND nt.IdTicket='$idTicket' AND dn.NoSerieEquipo='" . $rs['NoSerie'] . "'";

                                                        $queryPedido = $catalogo->obtenerLista($consultaPedido);
                                                        while ($rs2 = mysql_fetch_array($queryPedido)) {															
//
//                                                            $buscarA = " Y";
//                                                            $buscarM = " M";
//                                                            $buscarC = " C";
//                                                            $posA = strpos($rs2['Modelo'], $buscarA);
//                                                            $posM = strpos($rs2['Modelo'], $buscarM);
//                                                            $posC = strpos($rs2['Modelo'], $buscarC);
                                                            if ($rs2['IdColor'] == "4") {
                                                                $ckA = "checked";
                                                            }
                                                            if ($rs2['IdColor'] == "3") {
                                                                $ckM = "checked";
                                                            }
                                                            if ($rs2['IdColor'] == "2") {
                                                                $ckC = "checked";
                                                            }
                                                            if ($rs2['IdColor'] == "1") {
                                                                $ckN = "checked";
                                                            }
                                                        }
                                                    } else {
                                                        $mostrarInput = "display: none;";
                                                    }



                                                    echo "<tr>";
                                                    echo "<td align='center' scope='row' style='font-size:11px'>";
                                                    if ($rs['ServicioSinToner'] == "1") {
                                                        echo "Este equipo tiene un servicio sin tóner incluído<br/>";

                                                        if (empty($desactivarCheckPedido)) {
                                                            $desactiva = "disabled='disabled'";
                                                        } else {
                                                            $desactiva = "";
                                                        }
                                                    } else {
                                                        $desactiva = "";
                                                    }
                                                    echo "<input type='checkbox' name='activar_$contador' id='activar_$contador' onclick='bucarVentaDirectaToner($contador, \"" . $rs['NoSerie'] . "\", \"" . $rs['tipoFormato'] . "\"); marcarActivo(\"activar_$contador\");' $checkSerie $desactivarCheckPedido $desactiva/>";
                                                    echo "</td>";
                                                    echo "<td align='center' scope='row' style='font-size:11px'>" . $rs['NoSerie'] . ""
                                                    . "<input type='hidden' name='txtNoSerieE_$contador' id='txtNoSerieE_$contador' value='" . $rs['NoSerie'] . "'/>"
                                                    . "<input type='hidden' name='txtNoParteE_$contador' id='txtNoParteE_$contador' value='" . $rs['NoParteEquipo'] . "'/>"
                                                    . "<input type='hidden' name='txtModeloE_$contador' id='txtModeloE_$contador' value='" . $rs['Modelo'] . "'/>"
                                                    . "<br/>";
                                                    $display = "";
                                                    $texto_mostrar = "Anterior: ";
                                                    $texto_mostrar2 = "Actual: ";
                                                    if (!$mostrarContadores) {
                                                        $display = "display:none;";
                                                        $texto_mostrar = "";
                                                        $texto_mostrar2 = "";
                                                    }
                                                    echo "$texto_mostrar <input type='text' name='txtContadorNegroAnterior_$contador' id='txtContadorNegroAnterior_$contador' style='width: 80px; $display $mostrarInput' readonly value='$contadorNegroAnterior'/><input type='hidden' name='txtfechaAnterior_$contador' id='txtfechaAnterior_$contador' style='width: 80px;' value='$fechaContadorAnterior'/><br/>";
                                                    if ($contadorNegroAnterior != "") {
                                                        $anterior = "0";
                                                        $anterior = $contadorNegroAnterior;
                                                        $nuevo = $anterior + 100000;
                                                        $rango .= "jQuery.validator.addMethod('validarContadorNegroTonerMayor', function (value) {
                                                            if (parseInt($('#txtContadorNegro_$contador').val()) > parseInt($('#txtContadorNegroAnterior_$contador').val()) + 100000) {
                                                                $('#errorNegro$contador').text(' * El contador no debe superar mas de 100,000 al anterior');
                                                                return true;
                                                            } else {
                                                                $('#errorNegro$contador').text('');
                                                                return true;
                                                            }
                                                        }, '');";
                                                        $rango .= "$('#txtContadorNegro_$contador').rules('add',{validarContadorNegroTonerMayor: true});";
                                                    }

                                                    echo "$texto_mostrar2 <input type='text' name='txtContadorNegro_$contador' id='txtContadorNegro_$contador' style='width: 80px; $mostrarInput' value='$contadorNegro' onblur='verificarContadores($contador,0)'/><br/><label id='errorNegro$contador'></label><br/><div id='divErrorContNegro" . $contador . "'></div></td>";
                                                    echo "<td align='center' scope='row' style='font-size:11px'>" . $rs['Modelo'] . "<input type='hidden' name='txtModeloE_$contador' id='txtModeloE_$contador' value='" . $rs['Modelo'] . "'/><br/>"; //verificar componentes compatibles

                                                    $queryCompatibles = $catalogo->obtenerLista("SELECT c.NoParte,c.Modelo,c.Descripcion,c.IdColor FROM c_equipo e,c_componente c,k_equipocomponentecompatible ec 
                                                        WHERE e.NoParte=ec.NoParteEquipo AND c.NoParte=ec.NoParteComponente 
                                                        AND e.Modelo='" . $rs['Modelo'] . "' AND c.IdTipoComponente=2 AND c.Activo = 1 ");
                                                    $toner_negro_compatible = array();
                                                    $toner_cian_compatible = array();
                                                    $toner_magenta_compatible = array();
                                                    $toner_amarillo_compatible = array();
                                                    while ($rs1 = mysql_fetch_array($queryCompatibles)) {
                                                        $tonerNegro = "";
                                                        $tonerCian = "";
                                                        $tonerMagenta = "";
                                                        $tonerAmarillo = "";
                                                        if ($rs1['IdColor'] == "2") {
                                                            $tonerCian = $rs1['NoParte'];
                                                            //echo "<input type='text' name='txtTonerCian$contador' id='txtTonerCian$contador' value='Cian: " . $tonerCian . "'/>";
                                                            $toner_cian_compatible[$rs1['NoParte']] = $rs1['Modelo'] . " / " . $rs1['NoParte'];
                                                        }
                                                        if ($rs1['IdColor'] == "3") {
                                                            $tonerMagenta = $rs1['NoParte'];
                                                            //echo "<input type='text' name='txtTonerMagenta$contador' id='txtTonerMagenta$contador' value='Magenta: " . $tonerMagenta . "'/>";
                                                            $toner_magenta_compatible[$rs1['NoParte']] = $rs1['Modelo'] . " / " . $rs1['NoParte'];
                                                        }
                                                        if ($rs1['IdColor'] == "4") {
                                                            $tonerAmarillo = $rs1['NoParte'];
                                                            //echo "<input type='text' name='txtTonerAmarillo$contador' id='txtTonerAmarillo$contador' value='Amarillo: " . $tonerAmarillo . "'/>";
                                                            $toner_amarillo_compatible[$rs1['NoParte']] = $rs1['Modelo'] . " / " . $rs1['NoParte'];
                                                        }
                                                        if ($rs1['IdColor'] == "1") {
                                                            $tonerNegro = $rs1['NoParte'];
                                                            //echo "<input type='text' name='txtTonerNegro$contador' id='txtTonerNegro$contador' value='Negro: " . $tonerNegro . "'/>";
                                                            $toner_negro_compatible[$rs1['NoParte']] = $rs1['Modelo'] . " / " . $rs1['NoParte'];
                                                        }
                                                    }
                                                    if ($rs['tipoFormato'] != "1") {
                                                        echo "</td>";
                                                    } else {
                                                        $anterior = "0";
                                                        if ($contadorColorAnterior != "") {
                                                            $anterior = $contadorColorAnterior;
                                                            $nuevo = $anterior + 100000;
                                                            $rango .= "jQuery.validator.addMethod('validarContadorColorTonerMayor', function (value) {
                                                                if (parseInt($('#txtContadorColor_$contador').val()) > parseInt($('#txtContadorColorAnterior_$contador').val()) + 100000) {
                                                                    $('#errorColor$contador').text(' * El contador no debe superar mas de 100,000 al anterior');
                                                                    return true;
                                                                } else {
                                                                    $('#errorColor$contador').text('');
                                                                    return true;
                                                                }
                                                            }, '');";
                                                            $rango .= "$('#txtContadorColor_$contador').rules('add',{validarContadorColorTonerMayor: true});";
                                                        }

                                                        echo "<input type='text' name='txtContadorColorAnterior_$contador' id='txtContadorColorAnterior_$contador' style='width: 80px; $display $mostrarInput' readonly value='$contadorColorAnterior'/><br/>";
                                                        echo "<input type='text' name='txtContadorColor_$contador' id='txtContadorColor_$contador' style='width: 80px; $display $mostrarInput'value='$contadorColor' onblur='verificarContadores($contador,1)'/><br/><label id='errorColor$contador'></label><br/><div id='divErrorContColor" . $contador . "'></div></td>";
                                                    }
                                                    echo "<td align='center' scope='row' style='font-size:11px'>";

                                                        echo "<select id='txtTonerNegro$contador' name='txtTonerNegro$contador'>";
                                                        if (count($toner_negro_compatible) <= 0) {
                                                            echo "<option value=''>No hay toner compatible negro</option>";
                                                        } else if (count($toner_negro_compatible) > 1) {
                                                            $variableXX = "";
                                                            if ($idTicket != "") {
                                                                $consultaParaToner = "SELECT NoParteComponente FROM k_nota_refaccion WHERE NoSerieEquipo = '".$rs["NoSerie"]."' 
                                                                        AND IdNotaTicket IN (SELECT IdNotaTicket FROM c_notaticket WHERE IdTicket = $idTicket)";
                                                                $resultXX = $catalogo->obtenerLista($consultaParaToner);
                                                                if($rsXX = mysql_fetch_array($resultXX)){
                                                                    $variableXX = $rsXX['NoParteComponente'];
                                                                }
                                                            }
                                                            echo "<option value=''>Selecciona el toner compatible negro</option>";
                                                        }
                                                        foreach ($toner_negro_compatible as $key => $value) {
                                                            if(strcmp($key, $variableXX) == 0){
                                                                echo "<option value='$key' selected= 'selected'>$value</option>";
                                                            }else{
                                                                echo "<option value='$key'>$value</option>";
                                                            }   
                                                        }
                                                        echo "</select><br/><div id='error_toner_compatible_negro$contador' style='color: red;'></div><br/>";

                                                        echo "<input type='checkbox' name='ckbNegro_$contador' id='ckbNegro_$contador' $habilitar $ckN $desactivarCheckPedido onclick='validarRendimiento(this.name,0,$contador,0,0,0)'/>";
                                                        if ($permisos_grid->tienePermisoEspecial($_SESSION['idUsuario'], 35)) {
                                                            echo "<a href=\"#\" title=\"Ver existencias\" onclick=\"lanzarPopUpVerExistenciasToner('Ver existencias', 'ventas/verificarExistenciasTipoModelo.php', $contador, '1');
                                                                                    return false;\"><img src=\"resources/images/existencias.png\" width=\"28\" height=\"28\"/></a>";
                                                        }
                                                            /*?>
                                                        <a href="#" title="Ver existencias" onclick="lanzarPopUpVerExistenciasToner('Ver existencias', 'ventas/verificarExistenciasTipoModelo.php', '<?php echo $contador; ?>', '1');
                                                                                    return false;"><img src="resources/images/existencias.png" width="28" height="28"/></a>
                                                           <?
                                                       }*/
                                                       echo "<br/>";
                                                       echo "<input type='text' name='txtNivelNegroAnterior_$contador' id='txtNivelNegroAnterior_$contador' style='width: 50px; $mostrarInput' readonly value='$nivelNegroAnterior'/><br/>";
                                                       echo "<input type='text' name='txtNivelNegro_$contador' id='txtNivelNegro_$contador' style='width: 50px; $mostrarInput' value='$nivelNegro' /><br/>
                                                            <label id='lecturaCambioToner_$contador'></label>"
                                                            . "<br/>"
                                                            . "<label id='ultimaLecturaCorte_$contador'></label>";
                                                        echo "</td>";
                                                   
                                                   //echo "Tipo Formato: ".$rs['TipoFormato'];
                                                   if (isset($rs['tipoFormato']) && $rs['tipoFormato'] != "1") {
                                                       echo "<td align='center' scope='row' style='font-size:11px'></td>";
                                                       echo "<td align='center' scope='row' style='font-size:11px'></td>";
                                                       echo "<td align='center' scope='row' style='font-size:11px'></td>";                                                       
                                                   }else {
                                                       echo "<td align='center' scope='row' style='font-size:11px'>";
                                                       echo "<select id='txtTonerCian$contador' name='txtTonerCian$contador'>";
                                                       if (count($toner_cian_compatible) <= 0) {
                                                           echo "<option value=''>No hay toner compatible cian</option>";
                                                       } else if (count($toner_cian_compatible) > 1) {
                                                           echo "<option value=''>Selecciona el toner cian negro</option>";
                                                       }
                                                       foreach ($toner_cian_compatible as $key => $value) {
                                                           echo "<option value='$key'>$value</option>";
                                                       }
                                                       echo "</select><br/><div id='error_toner_compatible_cian$contador' style='color: red;'></div><br/>";
                                                       echo "<input type='checkbox' name='ckbCian_$contador' id='ckbCian_$contador' $habilitar $ckC $desactivarCheckPedido onclick='validarRendimiento(this.name,1,$contador,1,0,0)'/>";
                                                       if ($permisos_grid->tienePermisoEspecial($_SESSION['idUsuario'], 35)) {
                                                           ?>
                                                        <a href="#" title="Ver existencias" onclick="lanzarPopUpVerExistenciasToner('Ver existencias', 'ventas/verificarExistenciasTipoModelo.php', '<?php echo $contador; ?>', '2');
                                                                                        return false;"><img src="resources/images/existencias.png" width="28" height="28"/></a>
                                                           <?php
                                                       }
                                                       echo "<br/>";
                                                       echo "<input type='text' name='txtNivelCianAnterior_$contador' id='txtNivelCianAnterior_$contador' style='width: 50px; $mostrarInput ' readonly value='$nivelCianAnterior'/><br/>";
                                                       echo "<input type='text' name='txtNivelCian_$contador' id='txtNivelCian_$contador' style='width: 50px; $mostrarInput'value='$nivelCian' /><br/>"
                                                       . "<label id='lecturaCambioTonerCian_$contador'></label><br/><label id='ultimaLecturaCorteCian_$contador'></label></td>";

                                                       echo "<td align='center' scope='row' style='font-size:11px'>";
                                                       echo "<select id='txtTonerMagenta$contador' name='txtTonerMagenta$contador'>";
                                                       if (count($toner_magenta_compatible) <= 0) {
                                                           echo "<option value=''>No hay toner compatible magenta</option>";
                                                       } else if (count($toner_magenta_compatible) > 1) {
                                                           echo "<option value=''>Selecciona el toner magenta negro</option>";
                                                       }
                                                       foreach ($toner_magenta_compatible as $key => $value) {
                                                           echo "<option value='$key'>$value</option>";
                                                       }
                                                       echo "</select><br/><div id='error_toner_compatible_magenta$contador' style='color: red;'></div><br/>";
                                                       echo "<input type='checkbox' name='ckbMagenta_$contador' id='ckbMagenta_$contador' $habilitar $ckM $desactivarCheckPedido onclick='validarRendimiento(this.name,1,$contador,0,1,0)'/>";
                                                       if ($permisos_grid->tienePermisoEspecial($_SESSION['idUsuario'], 35)) {
                                                           ?>
                                                        <a href="#" title="Ver existencias" onclick="lanzarPopUpVerExistenciasToner('Ver existencias', 'ventas/verificarExistenciasTipoModelo.php', '<?php echo $contador; ?>', '3');
                                                                                        return false;"><img src="resources/images/existencias.png" width="28" height="28"/></a>
                                                           <?php
                                                       }
                                                       echo "<br/>";
                                                       echo "<input type='text' name='txtNivelMagentaAnterior_$contador' id='txtNivelMagentaAnterior_$contador' style='width: 50px; $mostrarInput' readonly value='$nivelMagentaAnterior' /><br/>";
                                                       echo "<input type='text' name='txtNivelMagenta_$contador' id='txtNivelMagenta_$contador'style='width: 50px; $mostrarInput' value='$nivelMagenta'/><br/>"
                                                       . "<label id='lecturaCambioTonerMagenta_$contador'></label><br/><label id='ultimaLecturaCorteMagenta_$contador'></label></td>";

                                                       echo "<td align='center' scope='row' style='font-size:11px'>";
                                                       echo "<select id='txtTonerAmarillo$contador' name='txtTonerAmarillo$contador'>";
                                                       if (count($toner_amarillo_compatible) <= 0) {
                                                           echo "<option value=''>No hay toner compatible amarillo</option>";
                                                       } else if (count($toner_amarillo_compatible) > 1) {
                                                           echo "<option value=''>Selecciona el toner compatible amarillo</option>";
                                                       }
                                                       foreach ($toner_amarillo_compatible as $key => $value) {
                                                           echo "<option value='$key'>$value</option>";
                                                       }
                                                       echo "</select><br/><div id='error_toner_compatible_amarillo$contador' style='color: red;'></div><br/>";
                                                       echo "<input type='checkbox' name='ckbAmarillo_$contador' id='ckbAmarillo_$contador' $habilitar $ckA $desactivarCheckPedido onclick='validarRendimiento(this.name,1,$contador,0,0,1)'/>";
                                                       if ($permisos_grid->tienePermisoEspecial($_SESSION['idUsuario'], 35)) {
                                                           ?>
                                                        <a href="#" title="Ver existencias" onclick="lanzarPopUpVerExistenciasToner('Ver existencias', 'ventas/verificarExistenciasTipoModelo.php', '<?php echo $contador; ?>', '4');
                                                                                        return false;"><img src="resources/images/existencias.png" width="28" height="28"/></a>
                                                           <?php
                                                       }
                                                       echo "<br/>";
                                                       echo "<input type='text' name='txtNivelAmarilloAnterior_$contador' id='txtNivelAmarilloAnterior_$contador' style='width: 50px; $mostrarInput' readonly value='$nivelAmarilloAnterior'/><br/>";
                                                       echo "<input type='text' name='txtNivelAmarillo_$contador' id='txtNivelAmarillo_$contador' style='width: 50px; $mostrarInput' value='$nivelAmarillo'/><br/>"
                                                       . "<label id='lecturaCambioTonerAmarillo_$contador'></label><br/><label id='ultimaLecturaCorteAmarillo_$contador'></label></td>";
                                                   }
                                                   if (!$mostrarContadores) {
                                                       $display = "style='display:none';";
                                                   }
                                                   echo "<td align='center' scope='row' style='font-size:11px'><textarea id='comentario_$contador' name='comentario_$contador' $display>$comentario_lectura</textarea></td>";
                                                   echo "</tr>";
                                                   $contador++;
                                               }
                                               ?>
                                            </tbody>
                                        </table>
                                    <?php } ?>
                                </div>
                            <?php } else {
                                ?>
                                <div id = "tabs-1" style = "background-color: #A4A4A4">
                                    <div id="busquedaSerie">
                                        <table style="width: 50%">
                                            <tr>
                                                <td>No serie</td>
                                                <td><input type="text" id='txtNoSrieFallaBuscar' name='txtNoSrieFallaBuscar' value="<?php echo $noSerie ?>"/><input type='button' id="botonBuscar" name="botonBuscar" value="Buscar" onclick="bucarVentaDirecta()" class="boton" <?php echo $botonBuscar; ?>/></td>
                                            </tr>                                        
                                            <tr>
                                                <td><input type="hidden" id='slcCliente' name='slcCliente' value="<?php echo $claveCliente ?>"/></td>
                                                <td><input type="hidden" id='slcLocalidad' name='slcLocalidad' value="<?php echo $claveLocalidad ?>"/></td>
                                            </tr>                                       
                                        </table>
                                    </div>
                                    <div id="busquedaCliente">
                                        <table style="width: 50%">
        <!--                                            <tr>
                                                <td colspan="4"><input type="checkbox" name="activarBuscarCliente" id="activarBuscarCliente" onclick="activarBuscarClienteCh()"/>Activar Busqueda por cliente</td> 
                                            </tr>-->
                                            <tr>
                                                <td>Cliente</td>
                                                <td>
                                                    <select id="slcCliente" name="slcCliente" style="width: 300px" onchange="incidenciaClienteSuspendidoFalla(this.value)"  class="filtro" <?php echo $descativarClienteLocalidad; ?> >
                                                        <option value="0">Seleccione un cliente</option>
                                                        <?php
                                                            $todosClientes = true;
                                                            $tfsCliente = new TFSCliente();
                                                            $tfsCliente->setIdUsuario($_SESSION['idUsuario']);
                                                            $resultTFS = $tfsCliente->getClientesByTFS();
                                                            while($rsTFS = mysql_fetch_array($resultTFS)){
                                                                $todosClientes = false;
                                                                $s = "";
                                                                if ($claveCliente != "" && $claveCliente == $rsTFS['ClaveCliente']) {
                                                                    $nombreCliente = $rsTFS['NombreRazonSocial'];
                                                                    $s = "selected";
                                                                }
                                                                echo "<option value='" . $rsTFS['ClaveCliente'] . "' $s>" . $rsTFS['NombreRazonSocial'] . "</option>";
                                                            }

                                                            if($todosClientes){
                                                                $queryCliente = $catalogo->obtenerLista($consultaCliente);
                                                                while ($rs = mysql_fetch_array($queryCliente)) {
                                                                    $s = "";
                                                                    if ($claveCliente != "" && $claveCliente == $rs['ClaveCliente']) {
                                                                        $nombreCliente = $rs['NombreRazonSocial'];
                                                                        $s = "selected";
                                                                    }
                                                                    echo "<option value='" . $rs['ClaveCliente'] . "' $s>" . $rs['NombreRazonSocial'] . "</option>";
                                                                }
                                                            }
                                                        ?>
                                                    </select>
                                                </td>
                                            </tr>
                                            <tr>
                                                <?php
                                                $permisoEspecial = "";
                                                if ($permisos_grid->tienePermisoEspecial($_SESSION['idUsuario'], 8)) {
                                                    $permisoEspecial = "1";
                                                } else {
                                                    $permisoEspecial = "0";
                                                }
                                                ?>
                                                <td>Localidad</td>                                            
                                                <td>                                               
                                                    <select id="slcLocalidad" name="slcLocalidad" style="width: 300px"  class="filtro" onchange="CambioLocalidadTicket(this.value);" <?php echo $descativarClienteLocalidad ?> >
                                                        <?php
                                                        if ($claveCliente != "") {
                                                            $queryCliente = $catalogo->obtenerLista("SELECT cc.ClaveCentroCosto,cc.Nombre FROM c_centrocosto cc WHERE cc.ClaveCliente='$claveCliente' AND cc.Activo=1 ORDER BY cc.Nombre ASC;");
                                                            echo " <option value='0'>Seleccione una localidad</option>";
                                                            while ($rs = mysql_fetch_array($queryCliente)) {
                                                                $s = "";
                                                                if ($claveLocalidad != "" && $claveLocalidad == $rs['ClaveCentroCosto']) {
                                                                    $nombreLocalidad = $rs['Nombre'];
                                                                    $s = "selected";
                                                                }
                                                                echo "<option value=" . $rs['ClaveCentroCosto'] . " " . $s . ">" . $rs['Nombre'] . "</option>";
//                                                            
                                                            }
                                                        }
                                                        ?>
                                                    </select>
                                                </td>
                                            </tr>
                                        </table>
                                    </div>
                                    <?php if ($claveLocalidad != "") { ?>
                                        <br/><br/>                                        
                                        <table id="<?php echo $nombreTabla; ?>" class="tabla_datos" style="width: 100%">
                                            <thead>
                                                <tr>
                                                    <th style="text-align: center; min-width:10%">No Serie</th>
                                                    <th style="text-align: center; min-width: 20%">Modelo equipo</th>
                                                    <th style="text-align: center; min-width: 15%">Contador B/N</th>
                                                    <th style="text-align: center; min-width: 15%">Contador color</th>
                                                    <th style="text-align: center; min-width: 15%">Comentario</th> 
                                                    <th style="text-align: center; min-width: 15%">Reportar</th>   
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php
                                                $onclickRadio = "";
                                                $whereSerie = "";
                                                if ($noSerie != "") {
                                                    $whereSerie = "AND cie.NoSerie='$noSerie'";
                                                }
                                                $query = $catalogo->obtenerLista("SELECT DISTINCT(cie.NoSerie) AS NoSerie,e.NoParte AS NoParteEquipo,e.Modelo,
                                                    (SELECT ke.IdTipoServicio FROM k_equipocaracteristicaformatoservicio AS ke WHERE ke.NoParte = cie.NoParteEquipo ORDER BY ke.IdTipoServicio ASC LIMIT 1) AS tipoFormato
                                                    FROM k_anexoclientecc AS kacc 
                                                    LEFT JOIN c_inventarioequipo AS cie ON cie.IdAnexoClienteCC = kacc.IdAnexoClienteCC 
                                                    LEFT JOIN c_bitacora AS b ON b.NoSerie = cie.NoSerie
                                                    LEFT JOIN c_centrocosto AS cc ON cc.ClaveCentroCosto = kacc.CveEspClienteCC 
                                                    LEFT JOIN k_serviciogimgfa AS ks ON ks.IdKServiciogimgfa = cie.IdKServiciogimgfa 
                                                    LEFT JOIN c_centrocosto AS cc2 ON cc2.ClaveCentroCosto = ks.ClaveCentroCosto
                                                    LEFT JOIN c_equipo AS e ON e.NoParte = cie.NoParteEquipo 
                                                    WHERE !ISNULL(cie.NoSerie) AND b.Activo = 1 AND ((kacc.CveEspClienteCC = '$claveLocalidad' AND ISNULL(cie.IdKServiciogimgfa)) OR (!ISNULL(cie.IdKServiciogimgfa) && ks.ClaveCentroCosto = '$claveLocalidad')) $whereSerie ORDER BY NoSerie DESC");
                                                $contador = 0;
                                                while ($rs = mysql_fetch_array($query)) {

                                                    $seleccionar = "";
                                                    if ($noSerie == $rs['NoSerie']) {
                                                        $seleccionar = "checked";
                                                        if ($idTicket != "") {
                                                            $queryLecturas = $catalogo->obtenerLista("SELECT lt.ContadorBN,lt.ContadorCL,lt.NivelTonNegro,
                                                                                                    lt.NivelTonCian,lt.NivelTonMagenta,lt.NivelTonAmarillo,lt.ContadorBNA,lt.ContadorCLA,lt.NivelTonNegroA,
                                                                                                    lt.NivelTonCianA,lt.NivelTonMagentaA,lt.NivelTonAmarilloA ,lt.FechaA,lt.Comentario
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
                                                                $comentario_lectura = $rs3['Comentario'];
                                                            }
                                                        }
                                                    }
                                                    echo "<tr>";
                                                    echo "<input type='hidden' name='tipoFormatoEquipo' id='tipoFormatoEquipo' value='" . $rs['tipoFormato'] . "'/>";
                                                    echo "<td align='center' scope='row' style='font-size:11px'>" . $rs['NoSerie'] . ""
                                                    . "<input type='hidden' name='txtNoSerieE_$contador' id='txtNoSerieE_$contador' style='width: 80px;' value='" . $rs['NoSerie'] . "'/></td>"
                                                    . "<input type='hidden' name='txtNoParteE_$contador' id='txtNoParteE_$contador' value='" . $rs['NoParteEquipo'] . "'/>"
                                                    . "<input type='hidden' name='txtModeloE_$contador' id='txtModeloE_$contador' value='" . $rs['Modelo'] . "'/>";
                                                    echo "<td align='center' scope='row' style='font-size:11px'>" . $rs['Modelo'] . ""
                                                    . "<input type='hidden' name='txtModeloE_$contador' id='txtModeloE_$contador' style='width: 80px;' value='" . $rs['Modelo'] . "'/>"
                                                    . "<input type='hidden' name='txtfechaAnterior_$contador' id='txtfechaAnterior_$contador' style='width: 80px;' readonly/>"
                                                    . "</td>";
                                                    $anterior = "0";
                                                    if ($contadorNegroAnterior != "") {
                                                        $anterior = $contadorNegroAnterior;
                                                        $nuevo = $anterior + 100000;
                                                        $rango .= "jQuery.validator.addMethod('validarContadorNegroTonerMayor', function (value) {
                                                            if (parseInt($('#txtContadorNegro_$contador').val()) > parseInt($('#txtContadorNegroAnterior_$contador').val()) + 100000) {
                                                                $.validator.messages.validarContadorNegroTonerMayor = '';
                                                                $('#errorNegro$contador').text(' * El contador no debe superar mas de 100,000 al anterior');
                                                                return true;
                                                            } else {
                                                                $('#errorNegro$contador').text('');
                                                                return true;
                                                            }
                                                        }, '');";
                                                        $rango .= "$('#txtContadorNegro_$contador').rules('add',{validarContadorNegroTonerMayor: true});";
                                                    }
                                                    $display = "";
                                                    $texto_mostrar = "Anterior: ";
                                                    $texto_mostrar2 = "Actual: ";
                                                    if (!$mostrarContadores) {
                                                        $display = "display:none;";
                                                        $texto_mostrar = "";
                                                        $texto_mostrar2 = "";
                                                    }

                                                    echo "<td align='center' scope='row' style='font-size:11px'>"
                                                    . "<br/>$texto_mostrar <input type='text' name='txtContadorNegroAnterior_$contador' id='txtContadorNegroAnterior_$contador' style='width: 80px; $display' value='$contadorNegroAnterior' readonly/>"
                                                    . "<br/>$texto_mostrar2 <input type='text' name='txtContadorNegro_$contador' id='txtContadorNegro_$contador' style='width: 80px; $display' value='$contadorNegro'/><br/><label id='errorNegro$contador'></label></td>";
                                                    if ($rs['tipoFormato'] != "1") {
                                                        echo "<td align='center' scope='row' style='font-size:11px'></td>";
                                                    } else {
                                                        $anterior = "0";
                                                        if ($contadorColorAnterior != "") {
                                                            $anterior = $contadorColorAnterior;
                                                            $nuevo = $anterior + 100000;
                                                            $rango .= "jQuery.validator.addMethod('validarContadorColorTonerMayor', function (value) {
                                                                if (parseInt($('#txtContadorColor_$contador').val()) > parseInt($('#txtContadorColorAnterior_$contador').val()) + 100000) {
                                                                    $('#errorColor$contador').text(' * El contador no debe superar mas de 100,000 al anterior');
                                                                    return true;
                                                                } else {
                                                                    $('#errorColor$contador').text('');
                                                                    return true;
                                                                }
                                                            }, '');";
                                                            $rango .= "$('#txtContadorColor_$contador').rules('add',{validarContadorColorTonerMayor: true});";
                                                        }

                                                        echo "<td align='center' scope='row' style='font-size:11px'>"
                                                        . "<br/><input type='text' name='txtContadorColorAnterior_$contador' id='txtContadorColorAnterior_$contador' style='width: 80px; $display' value='$contadorColorAnterior' readonly/>"
                                                        . "<br/><input type='text' name='txtContadorColor_$contador' id='txtContadorColor_$contador' style='width: 80px; $display' value='$contadorColor'/><br/><label id='errorColor$contador'></label></td>";
                                                    }
                                                    $display = "";
                                                    if (!$mostrarContadores) {
                                                        $display = "style='display:none';";
                                                    }
                                                    echo "<td align='center' scope='row' style='font-size:11px'><textarea id='comentario_$contador' name='comentario_$contador' $display>$comentario_lectura</textarea></td>";
                                                    if ($detalle == "1") {
                                                        echo "<td align='center' scope='row' style='font-size:11px'><input type='radio' name='rdEquipoFalla' id='rdEquipoFalla' value='" . $contador . " / " . $rs['NoSerie'] . " / " . $rs['Modelo'] . "'  $seleccionar  $desactivarRadioPedido/></td>";
                                                    } else {
                                                        echo "<td align='center' scope='row' style='font-size:11px'><input type='radio' name='rdEquipoFalla' onclick='incidenciaByTicketFallaCliente(\"" . $rs['NoSerie'] . "\")' id='rdEquipoFalla' value='" . $contador . " / " . $rs['NoSerie'] . " / " . $rs['Modelo'] . "'  $seleccionar  $desactivarRadioPedido/></td>";
                                                    }
                                                    echo "</tr>";
                                                    $contador++;
                                                }
                                                ?>
                                            </tbody>
                                        </table>
                                        <table>
                                            <tr><td>Ubicación</td><td><p style="color:red;">Esta ubicación no es el domicilio</p></td></tr>
                                            <tr><td colspan="2"><textarea id="ubicacionNoDomicilio" name="ubicacionNoDomicilio" style="width:600px; height: 50px" <?php echo $ubicaionNo ?> > <?php echo $UbicaiconNoDomicilio ?></textarea></td></tr>
                                        </table>
                                    <?php } ?>
                                </div>
                            <?php } ?>
                            <div id = "tabs-2" style = "background-color: #A4A4A4">
                                <?php
                                if ($claveLocalidad != "") {//datos del cliente 
                                    if ($EstadoTicketDatos == "2" && $tipoReporte != "15" || $EstadoTicketDatos == "4" && $tipoReporte != "15") {//
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
                                                                    AND cc.ClaveCentroCosto='$claveLocalidadEstadoTicket' ORDER BY d.IdDomicilio";
                                    } else {
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
                                                                    AND cc.ClaveCentroCosto='$claveLocalidad' ORDER BY d.IdDomicilio";
                                    }
                                    $queryDomicilio = $catalogo->obtenerLista($consultaLocalidad);
                                    if ($rs = mysql_fetch_array($queryDomicilio)) {
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
                                }
                                ?>
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
                                                <select id="tipoClienteToner" name="tipoClienteToner"  <?php echo $drawList; ?> disabled> >
                                                    <option value="0">Seleccione tipo de cliente</option>
                                                    <?php
                                                    $queryEstado = $catalogo->getListaAlta("c_tipocliente", "Nombre");
                                                    while ($rs = mysql_fetch_array($queryEstado)) {
                                                        $s = "";
                                                        if ($tipoCliente == $rs['IdTipoCliente']){
                                                            $s = "selected";
                                                        }
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
                                                        if ($estatusCobranza == $rs['IdEstatusCobranza']){
                                                            $s = "selected";
                                                        }
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
                                                            if ($idZona == $rs['ClaveZona']){
                                                                $s = "selected";
                                                            }
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
                                                        if ($estadoLocal == ""){
                                                            echo "<option value='0'>Selecciona un estado</option>";
                                                        }else{
                                                            echo "<option value='$estadoLocal'>$estadoLocal</option>";
                                                        }
                                                        $queryEstadoLocal = $catalogo->getListaAlta("c_ciudades", "Ciudad");
                                                        while ($rs = mysql_fetch_array($queryEstadoLocal)) {
                                                            $s = "";
                                                            if ($estadoLocal == $rs['Ciudad']){
                                                                $s = "selected";
                                                            }
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
                </div>
                <?php } ?>
                <div>                    
                    <fieldset>
                        <legend>Datos del <?php echo $nombre_objeto; ?></legend>   
                        <table>
                            <tr>
                                <td>Prioridad del <?php echo $nombre_objeto; ?>: </td>
                                <td>
                                    <?php
                                    $result2 = $catalogo->obtenerLista("SELECT pt.IdPrioridad, pt.Prioridad, tp.TipoPrioridad,  c.Hexadecimal
                                            FROM `c_prioridadticket` AS pt
                                            LEFT JOIN c_color AS c ON c.IdColor = pt.IdColor
                                            LEFT JOIN c_tipoprioridad AS tp ON tp.IdTipoPrioridad = pt.IdTipoPrioridad WHERE pt.Activo = 1;");
                                    echo "<select id='prioridad' name='prioridad'>";
                                    echo "<option value = 0 >Seleccione una prioridad</option>";
                                    while ($rs2 = mysql_fetch_array($result2)) {
                                        echo "<option value='" . $rs2['IdPrioridad'] . "' style='background: #" . $rs2['Hexadecimal'] . ";'>" . $rs2['Prioridad'] . " (" . $rs2['TipoPrioridad'] . ")</option>";
                                    }
                                    echo "</select>";
                                    ?>
                                </td>
                            </tr>
                        </table>
                        <fieldset>
                            <legend>Contacto responsable del <?php echo $nombre_objeto; ?></legend>
                            <table style='width: 100%'>
                                <tr>
                                <tr>
                                    <td><input type="radio" name="rdContacto" id="rdContacto" value="1" onclick="mostrarTipoContacto(1);"  <?php echo $desactivarRadio ?>/>Nuevo contacto </td>
                                    <td><input type="radio" name="rdContacto" id="rdContacto" value="0" onclick="mostrarTipoContacto(0);" checked="checked" <?php echo $desactivarRadio ?>/>Contacto existente </td>
                                </tr>
                                <tr>

                                    <td>Nombre</td>
                                    <td colspan="5">
                                        <div id="contactoExistente">
                                            <select id="txtNombre" name="txtNombre"  onchange="DatosContacto(this.value);" style='width: 655px' <?php echo $drawList; ?> >
                                                <option value="0">Seleccione contacto</option>
                                                <?php
                                                if ($claveLocalidad != "") {
                                                    $queryContactos = $catalogo->obtenerLista("SELECT ct.IdContacto,ct.Nombre,ct.Telefono,ct.Celular,ct.CorreoElectronico,(SELECT tc.Nombre FROM c_tipocontacto tc WHERE ct.IdTipoContacto=tc.IdTipoContacto) AS tipoContacto,ct.IdTipoContacto AS IdTipoContacto,ct.ClaveEspecialContacto AS ClaveEspecialContacto
                                                                                        FROM c_contacto ct WHERE ct.ClaveEspecialContacto='$claveLocalidad' OR ct.ClaveEspecialContacto='$claveCliente';");
                                                    while ($rs = mysql_fetch_array($queryContactos)) {
                                                        $s = "";
                                                        if ($nombreResp == $rs['Nombre']){
                                                            $s = "selected";
                                                        }
                                                        echo "<option value='" . $rs["Nombre"] . " // " . $rs["Telefono"] . " // " . $rs["Celular"] . " // " . $rs["CorreoElectronico"] . " // " . $rs['IdContacto'] . " // " . $rs['IdTipoContacto'] . " // " . $rs['ClaveEspecialContacto'] . "' $s>" . $rs['Nombre'] . "   (" . $rs['tipoContacto'] . ")" . "</option>";
                                                    }
                                                }
                                                ?>
                                            </select>
                                        </div>
                                        <div id="contactoNuevo">
                                            <input type="text" id="txtNombre1" name="txtNombre1" value="<?php echo $nombreResp; ?>" style='width: 680px; display: none;' <?php echo $read; ?> readonly="readonly"/>
                                        </div>

            <!--//                                    <input type="text" id="txtNombre" name="txtNombre" value="<?php echo $nombreResp; ?>" style='width: 680px'>-->
                                    </td>
                                </tr>
                                <tr>
                                    <td><label for="txtTelefono1">Telefono 1:</label></td>
                                    <td colspan="3"><input type="text" id="txtTelefono1" name="txtTelefono1" value="<?php echo $telefono1Resp; ?>" style='width: 250px'  <?php echo $read; ?> /></td>
                                    <td><label for="txtExtencion1">Extención 1:</label></td>
                                    <td><input type="text" id="txtExtencion1" name="txtExtencion1" value="<?php echo $Extencio2Resp; ?>" style='width: 250px'  <?php echo $read; ?> readonly/></td>
                                </tr>
                                <tr>
                                    <td><label for="txtTelefono2">Telefono 2 <br/>(Este dato solo se guarda para el ticket actual):</label></td>
                                    <td colspan="3"><input type="text" id="txtTelefono2" name="txtTelefono2" value="<?php echo $telefono2Atencion; ?>" style='width: 250px'  <?php echo $read; ?> readonly/></td>
                                    <td><label for="txtExtencion2">Extención 2 <br/>(Este dato solo se guarda para el ticket actual):</label></td>
                                    <td><input type="text" id="txtExtencion2" name="txtExtencion2" value="<?php echo $Extencio2Resp; ?>" style='width: 250px' <?php echo $read; ?> readonly/></td>
                                </tr>
                                <tr>
                                    <td><label for="txtCelular">Celular</label></td>
                                    <td colspan="3"><input type="text" id="txtCelular" name="txtCelular" value="<?php echo $celularResp; ?>" style='width: 250px' <?php echo $read; ?> /></td> 
                                </tr>
                                <tr>
                                    <td><label for="correoElectronico">Correo electrónico</label></td>
                                    <td colspan="3"><input type="text" id="correoElectronico" name="correoElectronico" value="<?php echo $correoResp; ?>" style='width: 250px' <?php echo $read; ?> /><div id="errorCorreoResp" <?php echo $read; ?>/></div></td> 
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
                                        <select id='lstHR' name='lstHR' <?php echo $drawList; ?> >
                                            <?php
                                            for ($x = 1; $x <= 12; $x++) {
                                                $s = "";
                                                if ($x == "9" || $hinicioResp == $x){
                                                    $s = "selected";
                                                }
                                                echo "<option value='" . $x . "' $s>" . $x . "</option> ";
                                            }
                                            ?>
                                        </select>
                                        <select id='lstMR' name='lstMR' <?php echo $drawList; ?> >
                                            <option value="00">00</option>
                                            <option value="15">15</option>
                                            <option value="30">30</option>
                                            <option value="45">45</option>
                                        </select>
                                        <select id='lstTA' name='lstTA' <?php echo $drawList; ?> >
                                            <option value="am">am</option>
                                            <option value="pm">pm</option>
                                        </select>
                                    </td>
                                    <td><label for="lstFinR"><b>Fin:</b>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Hora</label></td>
                                    <td>

                                        <select id='lstFinHR' name='lstFinHR' <?php echo $drawList; ?> >
                                            <?php
                                            $hfinResp = "";
                                            $mfinResp = "";
                                            $tfinResp = "";
                                            list($hfinResp) = explode(",", $horarioResponsableFin);
                                            for ($x = 1; $x <= 12; $x++) {
                                                $s = "";
                                                if ($x == "6" || $hfinResp == $x){
                                                    $s = "selected";
                                                }
                                                echo "<option value='" . $x . "' $s>" . $x . "</option> ";
                                            }
                                            ?>
                                        </select>
                                        <select id='lstFinMR' name='lstFinMR' <?php echo $drawList; ?> >
                                            <option value="00" selected>00</option>
                                            <option value="15">15</option>
                                            <option value="30">30</option>
                                            <option value="45">45</option>
                                        </select>
                                        <select id='lstFinTR' name='lstFinTR' <?php echo $drawList; ?> >
                                            <option value="am">am</option>
                                            <option value="pm" selected>pm</option>
                                        </select>
                                    </td>
                                </tr>
                            </table>
                        </fieldset>    
                        <?php if ($idTicket == "") { ?>
                            <a href='#' onclick='CopiarDatosContacto();
                                        return false;' title='Copiar datos de contacto' >Copiar datos de contacto responsable de <?php echo $nombre_objeto; ?> o centro de atención</a>
                           <?php } ?>
                        <fieldset>
                            <legend>Contacto de atención del <?php echo $nombre_objeto; ?></legend>   
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
                                        <select id='lstHA' name='lstHA' <?php echo $drawList; ?> >
                                            <?php
                                            for ($x = 1; $x <= 12; $x++) {
                                                $s = "";
                                                if ($x == "9"){
                                                    $s = "selected";
                                                }
                                                echo "<option value='" . $x . "' $s>" . $x . "</option> ";
                                            }
                                            ?>
                                        </select>
                                        <select id='lstMA' name='lstMA' <?php echo $drawList; ?> >
                                            <option value="00" selected>00</option>
                                            <option value="15">15</option>
                                            <option value="30">30</option>
                                            <option value="45">45</option>
                                        </select>
                                        <select id='lstTA' name='lstTA' <?php echo $drawList; ?> >
                                            <option value="am" selected>am</option>
                                            <option value="pm">pm</option>
                                        </select>
                                    </td>
                                    <td><label for="lstFinA"><b>Fin:</b>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Hora</label></td>
                                    <td>
                                        <select id='lstFinHA' name='lstFinHA' <?php echo $drawList; ?> >
                                            <?php
                                            for ($x = 1; $x <= 12; $x++) {
                                                $s = "";
                                                if ($x == "6"){
                                                    $s = "selected";
                                                }
                                                echo "<option value='" . $x . "' $s>" . $x . "</option> ";
                                            }
                                            ?>
                                        </select>
                                        <select id='lstFinMA' name='lstFinMA' <?php echo $drawList; ?> >
                                            <option value="00" selected>00</option>
                                            <option value="15">15</option>
                                            <option value="30">30</option>
                                            <option value="45">45</option>
                                        </select>
                                        <select id='lstFinTA' name='lstFinTA' <?php echo $drawList; ?> >
                                            <option value="am">am</option>
                                            <option value="pm" selected>pm</option>
                                        </select>
                                    </td>
                                </tr>
                            </table>
                        </fieldset>
                        <table style="width:  100%">
                            <tr>
                                <td>No. <?php echo $nombre_objeto; ?> cliente:</td><td><input type="text" id="txtNoTicketClienteGral" name="txtNoTicketClienteGral" value='<?php echo $ticketCliente ?>' <?php echo $read; ?>/></td>
                                <td>No. <?php echo $nombre_objeto; ?> distribuidor:</td><td><input type="text" id="txtNoTicketDistribucionGral" name="txtNoTicketDistribucionGral" value='<?php echo $ticketDistribucion; ?>' <?php echo $read; ?>/></td>
                                <td>No. Guía</td><td><input type="text" id="noGuia" name="noGuia" value='<?php echo $NoGuia ?>'></td>
                            </tr>   
                            <tr>
                                <td>Descripción del reporte:</td>
                                <td style="width: 85%" colspan="5">
                                    <textarea style="width: 100%; height: 150px;" id='descripcion' name='descripcion' <?php echo $read; ?> ><?php echo $descripcion; ?></textarea>
                                </td>
                            </tr>
                            <tr>
                                <td>Observaciones adicionales:</td>
                                <td style="width: 85%" colspan="5">
                                    <textarea style="width: 100%;height: 150px;" id='observacion' name='observacion' <?php echo $read; ?> ><?php echo $observacion; ?></textarea>
                                </td>
                            </tr>   
                            <tr>
                                <td>Área de atención<span class="obligatorio"> *</span>:</td>
                                <td>
                                    <select id="areaAtencionGral" name="areaAtencionGral" style="width: 300px" <?php echo $drawList; ?> >

                                        <?php
                                        if ($tipoReporte == "15") {
                                            $queryArea = "SELECT  e.IdEstado,e.Nombre  FROM c_estado e,c_flujo f,k_flujoestado fe WHERE e.IdEstado=fe.IdEstado AND f.IdFlujo=fe.IdFlujo AND f.IdFlujo=3";
                                        } else if ($tipoReporte != "15" && $tipoReporte != "") {
                                            echo "<option value='0'>Seleccione el area de atención</option>";
                                            $queryArea = "SELECT  e.IdEstado,e.Nombre  FROM c_estado e,c_flujo f,k_flujoestado fe WHERE e.IdEstado=fe.IdEstado AND f.IdFlujo=fe.IdFlujo AND f.IdFlujo=2";
                                        } else {
                                            echo "<option value='0'>Seleccione el area de atención</option>";
                                        }
                                        $query = $catalogo->obtenerLista($queryArea);
                                        while ($rs = mysql_fetch_array($query)) {
                                            $s = "";
                                            if ($areaAtencion != "" && $areaAtencion == $rs['IdEstado']){
                                                $s = "selected";
                                            }
                                            echo "<option value='" . $rs['IdEstado'] . "' $s>" . $rs['Nombre'] . "</option>";
                                        }
                                        ?>
                                    </select>
                                </td>
                                <td></td><td></td><td></td><td></td>
                            </tr>
                        </table>
                    </fieldset>
                </div>
                <?php if ($detalle != "1" && $permisos_grid->getModificar()) { ?>              
                    <input type = "submit" id = "botonGuardar" name = "botonGuardar" class = "boton" value = "Guardar"/>
                    <?php if ($botonCancelar != "1") { ?>  
                    <input type = "button" class = "boton" value = "Cancelar" onclick = "cambiarContenidos('<?php echo $pagina_listaRegresar; ?>');
                                        return false;"/>
                               <?php
                           }
                       }
                       ?>
                <input type = "hidden" name = "idTicket" id = "idTicket" value = "<?php echo $idTicket; ?>" />
                <input type = "hidden" name = "txtPermisoRendimiento" id = "txtPermisoRendimiento" value = "<?php echo $permisoEspecialRendimiento; ?>" />
                <input type = "hidden" name = "nombreCC" id = "idTicket" value = "<?php echo $nombreLocalidad; ?>" />
                <input type = "hidden" name = "nombreCliente" id = "idTicket" value = "<?php echo $nombreCliente; ?>" />
                <input type = "hidden" name = "filaSeleccionada" id = "filaSeleccionada" value = ""/>
                <input type = "hidden" name = "tipoUsuario" id = "tipoUsuario" value = "<?php echo $idPuesto; ?>"/>

            </form>
        </div>
        <div id = "dialog" ></div>
        <?php if ($detalle == "0") { ?>      
            <img class="imagenMouse" src="<?php echo $path_previo; ?>resources/images/add.png" title="Nueva nota" onclick='AgregarNotaTicket("nota/AgregarNota.php?idTicket1=<?php echo $idTicket; ?>", "<?php echo $tipoReporte ?>");' style="float: right; cursor: pointer;" />  
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
                    $query = $catalogo->obtenerLista("SELECT nt.IdNotaTicket,nt.FechaHora,nt.DiagnosticoSol,nt.IdEstatusAtencion,e.Nombre AS estatus,nt.UsuarioUltimaModificacion
                                                    FROM c_notaticket nt LEFT JOIN c_estado e ON nt.IdEstatusAtencion=e.IdEstado WHERE nt.IdTicket='$idTicket' AND nt.Activo=1 ORDER BY nt.FechaHora DESC");
                    while ($rs = mysql_fetch_array($query)) {
                        if ($rs['IdEstatusAtencion'] != "65") {
                            echo "<tr>";
                            echo "<td align='center' scope='row' style='font-size:11px'>" . $rs['FechaHora'] . "</td>";
                            echo "<td align='center' scope='row' style='font-size:11px'>" . $rs['DiagnosticoSol'] . "</td>";
                            echo "<td align='center' scope='row' style='font-size:11px'>" . $rs['estatus'] . "</td>";
                            echo "<td align='center' scope='row' style='font-size:11px'></td>";
                            echo "<td align='center' scope='row' style='font-size:11px'>" . $rs['UsuarioUltimaModificacion'] . "</td>";
                            echo "<td align='center' scope='row' style='font-size:11px'>";
                            ?>
                        <a href='#' onclick='mostrarDetalleNota("<?php echo $path_previo; ?>mesa/detalleNota.php?frame=1", "<?php echo $rs['IdNotaTicket'] ?>", "<?php echo $tipoReporte ?>");
                                            return false;' title='Detalle' > <img src='<?php echo $path_previo; ?>resources/images/Textpreview.png'/> </a>
                           <?php
                           echo "</td></tr>";
                       }
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
    <div id="SeleccionPedido" title="Mensaje">
    </div>
    <input type="hidden" id="tablaTamanio" name="tablaTamanio" value="<?php echo $contador; ?>"/>
    <?php echo "<script>$rango</script>"; ?>
</body>

</html>