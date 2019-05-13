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
include_once("../WEB-INF/Classes/Definiciones.class.php");

$catalogo = new Catalogo();
$obj = new Ticket();
$pedido = new Pedido();
$lecturaTicket = new LecturaTicket();
$permisos_grid = new PermisosSubMenu();
$definiciones = new Definiciones();
$same_page = "mesa/alta_ticket2.php";
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
$mostrarDatos = 0;
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
$latitud = "";
$longitud = "";
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
    $mostrarDatos = 1;
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
    $consultaCliente = "SELECT c.ClaveCliente,c.IdEstatusCobranza,c.NombreRazonSocial,c.Suspendido FROM c_cliente c WHERE c.Activo=1 /*AND Modalidad = 5*/ ORDER BY c.NombreRazonSocial ASC";
}
//echo $consultaCliente;

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
            echo '
        <script type="text/javascript" language="javascript" src="resources/js/paginas/ReporteLecturas.js"></script>
        
        <!-- Bootstrap core CSS -->
        <link href="resources/css/Bootstrap 4/bootstrap.min.css" rel="stylesheet">
        
        <!-- FontAwesome para iconos -->
        <link href="resources/css/Bootstrap 4/fontawesome/all.min.css" rel="stylesheet">';


            echo '<script type="text/javascript" language="javascript" src="../resources/js/paginas/nuevoTicket.js"></script>';
        } else {
            $path_previo = "";
            echo '<script type="text/javascript" language="javascript" src="resources/js/paginas/nuevoTicket2.js"></script>';            
        }
        ?>
        <script>
            $(function () {
                $("#tabs").tabs();
            });
        </script>
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
        <script>
            $(document).ready(function () {
                $('.boton').button();
            });
        </script>
    </head>
 
    <body>
        <div class="principal"> 
            <form id="frmAltaTicket" name="frmAltaTicket" action="/" method="POST">
                <div class="container-fluid">
                   <div class="form-row">
                      <div  class="form-group col-md-3">
                        <label>Tipo de reporte:</label>
                            <select class="form-control" id="sltTipoReporte" name="sltTipoReporte" onchange="MosdivarTipoReporte(this.value);" <?php echo $descativarTipoReporte; ?>>                                    
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
                    </div>
                    <div  class="form-group col-md-3">
                        <label>Estado del <?php echo $nombre_objeto; ?>:</label>
                            <select class="form-control" id="sltEstadoTicket" name="sltEstadoTicket" <?php echo $drawList . " " . $modificar_estado; ?>>
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
                    </div>
                
                    <?php if ($detalle == "1") { ?>
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
                                                        return false;' title='Detalle' > 
                                        <img src='<?php echo $path_previo; ?>resources/images/Textpreview.png'/> 
                                    </a>
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
                </div> 
                <br/><br/>
                <div>
                    <?php if ($tipoReporte != "" && $tipoReporte != "0") { ?>
                        <div id="tabs">
                            <ul>
                                <li><a href="#tabs-1"><?php if($definiciones->getRegistroById(1)){ echo $definiciones->getNombre(); }else{ echo "Pedido"; }?></a></li>
                                <li><a href = "#tabs-2"><?php if($definiciones->getRegistroById(2)){ echo $definiciones->getNombre(); }else{ echo "Cliente"; }?></a></li>
                            </ul>
                            <?php if ($tipoReporte == "15") { ?>
                                <div id = "tabs-1" style = "background-color: #A4A4A4"><div class="form-row">
                                        <div class="form-group col-md-4">
                                            <label><?php if($definiciones->getRegistroById(5)){ echo $definiciones->getNombre(); }else{ echo "No. Serie"; }?></label>
                                            <input class="form-control" type="text" id='txtNoSrieTonerBuscar' name='txtNoSrieTonerBuscar' value="<?php echo $noSerie ?>"/>
                                            <input type='button' id="botonBuscar" name="botonBuscar" value="Buscar" onclick="BuscarEquipoCliLocEqui('txtNoSrieTonerBuscar', 'slcCliente', 'slcLocalidad', 'selectNoSerie')" class="boton" <?php echo $botonBuscar; ?>/>
                                        </div> 
                                        <div class="form-group col-md-4"">
                                            <label><?php if($definiciones->getRegistroById(1)){ echo $definiciones->getNombre(); }else{ echo "Cliente"; }?></label>
                                            <select id="slcCliente" name="slcCliente" onchange="incidenciaClienteSuspendido(this.value)" class="filtro" <?php echo $descativarClienteLocalidad; ?>>
                                                <option value="0">Seleccione un <?php if($definiciones->getRegistroById(1)){ echo strtolower($definiciones->getNombre()); }else{ echo "cliente"; }?></option>
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
                                        </div> 
                                        <div class="form-group col-md-4"">
                                            <?php
                                            $permisoEspecial = "";
                                            if ($permisos_grid->tienePermisoEspecial($_SESSION['idUsuario'], 8)) {
                                                $permisoEspecial = "1";
                                            } else {
                                                $permisoEspecial = "0";
                                            }
                                            ?>
                                            <label>Localidad</label>
                                            <input type="hidden" id="permisoTicketMiniAlmacen" name="permisoTicketMiniAlmacen" value="<?php echo $permisoEspecial; ?>"/>
                                            <select id="slcLocalidad" name="slcLocalidad" style="form-control" class="filtro" onchange="CambioLocalidadTicketToner(this.value, '<?php echo $permisoEspecial ?>');" <?php echo $descativarClienteLocalidad; ?>>
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
//                                                            }
                                                    }
                                                }
                                                ?>
                                            </select>
                                        </div> 
                                        <?php if ($detalle == "") { ?>
                                        <div class="form-group col-md-4"">
                                            <label><?php if($definiciones->getRegistroById(5)){ echo $definiciones->getNombre(); }else{ echo "No. Serie"; }?></label>
                                            <select id="selectNoSerie" name="selectNoSerie[]" multiple="multiple" style="width: 300px" <?php echo $descativarClienteLocalidad; ?>>
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
                                                            if ($arraySeries[$x] == $serie['NoSerie'])
                                                                $s = "selected";
                                                        }
                                                        echo "<option value='" . $serie['NoSerie'] . "' $s>" . $serie['NoSerie'] . " / " . $serie['Modelo'] . "</option>";
                                                    }
                                                }
                                                ?>
                                            </select>
                                            <div id='errorSelectNoSerie'></div>
                                            <input type="button" value="Buscar" class="boton" onclick="BuscarEquiposNumeroSerieLocalidad();"/>
                                        </div>
                                        <?php } ?>
                                    </div>
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
                                    <?php } ?>
                                </div>
                            <?php } else {
                                ?>
                                <div id = "tabs-1" style = "background-color: #A4A4A4">
                                                                        
                                    <div id="busquedaCliente" class="form-row">
                                        <div class="form-group col-md-4">
        <!--                                            <tr>
                                                <td colspan="4"><input type="checkbox" name="activarBuscarCliente" id="activarBuscarCliente" onclick="activarBuscarClienteCh()"/>Activar Busqueda por cliente</td> 
                                            </tr>-->
                                            <label><?php if($definiciones->getRegistroById(1)){ echo $definiciones->getNombre(); }else{ echo "Cliente"; }?></label>
                                            <select id="slcCliente" name="slcCliente"  onchange="incidenciaClienteSuspendidoFalla(this.value)"  class="form-control" <?php echo $descativarClienteLocalidad ?>>
                                                <option value="0">Seleccione un <?php if($definiciones->getRegistroById(1)){ echo strtolower($definiciones->getNombre()); }else{ echo "cliente"; }?></option>
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
                                        </div>
                                        <div class="form-group col-md-4">
                                            <?php
                                            $permisoEspecial = "";
                                            if ($permisos_grid->tienePermisoEspecial($_SESSION['idUsuario'], 8)) {
                                                $permisoEspecial = "1";
                                            } else {
                                                $permisoEspecial = "0";
                                            }
                                            ?>
                                            <label>Localidad</label>
                                            <select id="slcLocalidad" name="slcLocalidad"   class="form-control" onchange="CambioLocalidadTicket(this.value);" <?php echo $descativarClienteLocalidad ?>>
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
//                                                            }
                                                    }
                                                }
                                                ?>
                                            </select>
                                        </div>
                                    </div>
                                    <div id="busquedaSerie">
                                        <table style="width: 50%">
                                            <tr>
                                                <td><?php /*if($definiciones->getRegistroById(5)){ echo $definiciones->getNombre(); }else{ echo "No. Serie"; }*/?></td>
                                                <td>
                                                    <input type="text" id='txtNoSrieFallaBuscar' name='txtNoSrieFallaBuscar' value="<?php echo $noSerie ?>" style="display: none;"/>
                                                    <!--<input type='button' id="botonBuscar" name="botonBuscar" value="Buscar" onclick="bucarVentaDirecta();" class="boton" <?php //echo $botonBuscar; ?>/>-->
                                                </td>
                                            </tr>                                        
                                            <tr>
                                                <td><input type="hidden" id='slcCliente' name='slcCliente' value="<?php echo $claveCliente ?>"/></td>
                                                <td><input type="hidden" id='slcLocalidad' name='slcLocalidad' value="<?php echo $claveLocalidad ?>"/></td>
                                            </tr>                                       
                                        </table>
                                    </div>
                                    <?php if ($claveLocalidad != "") { ?>
                                    	<div class="form-row">
                                    		<div class="form-group col-md-4">
                                    			<label>Ubicación<p class="bg-danger">Especificar donde se pondrá el módulo de promoción</p></label>
                                    			<textarea id="ubicacionNoDomicilio" name="ubicacionNoDomicilio" class="form-control"> <?php echo $ubicaionNo; ?><?php echo $UbicaiconNoDomicilio; ?></textarea>
                                    		</div>
                                    	</div>
                                    <?php } ?>
                                </div>
                            <?php } ?>
                            <div id = "tabs-2" style = "background-color: #A4A4A4">
                                <?php
                                if ($claveLocalidad != "") {//datos del cliente 
                                    if ($EstadoTicketDatos == "2" && $tipoReporte != "15" || $EstadoTicketDatos == "4" && $tipoReporte != "15") {//
                                        $consultaLocalidad = "SELECT c.ClaveCliente,c.NombreRazonSocial,c.IdTipoCliente,c.IdEstatusCobranza, cc.Nombre AS localidad,
                                                                    td.Nombre AS tdomicilio,d.Calle,d.Colonia,d.Delegacion,d.Latitud,d.Longitud, 
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
                                                                    td.Nombre AS tdomicilio,d.Calle,d.Colonia,d.Delegacion,d.Latitud,d.Longitud,
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
                                        $latitud = $rs['Latitud'];
                                        $longitud = $rs['Longitud'];
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
                                    <legend><?php if($definiciones->getRegistroById(1)){ echo $definiciones->getNombre(); }else{ echo "Cliente"; }?>:</legend>

                                    <div class="form-row">
                                        <div class="form-group col-md-4">
                                            <?php if($definiciones->getRegistroById(1)){ echo $definiciones->getNombre(); }else{ echo "Cliente"; }?>:<span class="obligatorio"> *</span>
                                            <input type="text" class="form-control" id="txtClaveClienteToner" name="txtClaveClienteToner" value="<?php echo $claveCliente; ?>" <?php echo $readCliente; ?> />
                                            <!--<div class="form-group col-12 col-md-4"></div><div class="form-group col-12 col-md-4"><input type="checkbox" id="ckActualizarinfoToner" name="ckActualizarinfoToner" />Actualizar Información</div>-->
                                        </div>
                                        <div class="form-group col-md-4">
                                            Nombre Empresa:
                                            <input type="text" class="form-control" id="nombreEmpresaToner" name="nombreEmpresaToner" value="<?php echo $nombreCliente; ?>"  <?php echo $readCliente; ?>/>
                                        </div>
                                        <div class="form-group col-md-4">
                                            Tipo de <?php if($definiciones->getRegistroById(1)){ echo strtolower($definiciones->getNombre()); }else{ echo "cliente"; }?>
                                            <select class='form-control' id="tipoClienteToner" name="tipoClienteToner"  <?php echo $drawList; ?> disabled>
                                                <option value="0">Seleccione tipo de <?php if($definiciones->getRegistroById(1)){ echo strtolower($definiciones->getNombre()); }else{ echo "cliente"; }?></option>
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
                                        </div>
                                        <div class="form-group col-md-4">
                                            Estatus de cobranza:
                                            <!--<input type="text" class="form-control" id="txtEstatusToner" name="txtEstatusToner" value="<?php echo $estatusCobranza; ?>"  <?php //echo $read; ?> />-->
                                            <select class='form-control' id="tipoestatusCobranza" name="tipoestatusCobranza"  <?php echo $drawList; ?> disabled>
                                                <?php
                                                $queryCobranza = $catalogo->getListaAlta("c_estatuscobranza", "Nombre");
                                                while ($rs = mysql_fetch_array($queryCobranza)) {
                                                    $s = "";
                                                    if ($estatusCobranza == $rs['IdEstatusCobranza'])
                                                        $s = "selected";
                                                    echo "<option value='" .$rs['IdEstatusCobranza'] . "' $s>" . $rs['Nombre'] . "</option>";
                                                }
                                                ?>
                                            </select>
                                        </div>
                                        <div class="form-group col-md-4">
                                            Localidad:
                                            <input type="text" class="form-control" id="localidadToner" name="localidadToner" value="<?php echo $nombreLocalidad; ?>" <?php echo $readCliente; ?> />
                                        </div>
                                    </div>
                                    <fieldset>

                                        <legend>Domicilio<span class="obligatorio"> *</span></legend> 
                                        <div class="form-row">
                                            <div class="form-group  col-md-3">
                                                <label>Zona:</label>
    <!--                                                            <input type="text"class='form-control' id="txtZonaToner" name="txtZonaToner" value=""  />
                                                           <input type="text"class='form-control' id="txtZonaFalla" name="txtZonaFalla" value="<?php //echo $estatusCobranza; ?>"  <?php //echo $read; ?> />-->
                                                <select class='form-control' id="txtZonaToner" name="txtZonaToner"  disabled>
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
                                            </div>
                                            <div class="form-group col-md-3">
                                                <label>Ubicación:</label>
                                                <select class='form-control' id="sltUbicacionToner" name="sltUbicacionToner"  <?php echo $drawList; ?> >
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
                                                    <!--<input type="hidden" id="sltUbicacionToner" name="sltUbicacionToner" value="<?php //echo $unicacionTxt; ?>"  <?php //echo $unicacionTxt; ?> />-->
                                            </div>
                                            <div class="form-group col-md-3">
                                                <label>Calle:<span class="obligatorio"> *</span></label>
                                                <input type="text"class='form-control' id="txtCalleToner" name="txtCalleToner" value="<?php echo $calle; ?>"  <?php echo $readCliente; ?> />
                                            </div>
                                            <div class="form-group col-md-3">
                                                <label>No.exterior:<span class="obligatorio"> *</span></label>
                                                <input type="text"class='form-control' id="txtNoExteriorToner" name="txtNoExteriorToner" value="<?php echo $nExterior; ?>"  <?php echo $readCliente; ?> />
                                            </div>
                                            <div class="form-group col-md-3">
                                                <label>No. interior:</label>
                                                <input type="text"class='form-control' id="txtNoInteriorToner" name="txtNoInteriorToner" value="<?php echo $nInterior; ?>"  <?php echo $readCliente; ?> />
                                            </div>
                                            <div class="form-group col-md-3">
                                                <label>Colonia:<span class="obligatorio"> *</span></label>
                                                <input type="text"class='form-control' id="txtColoniaToner" name="txtColoniaToner" value="<?php echo $colonia; ?>"  <?php echo $readCliente; ?> />
                                            </div>
                                            <div class="form-group col-md-3">
                                                <label>Ciudad:<span class="obligatorio"> *</span></label>
                                                <input type="text"class='form-control' id="txtCiudadToner" name="txtCiudadToner" value="<?php echo $ciudad; ?>"  <?php echo $readCliente; ?> />
                                            </div>
                                            <div class="form-group col-md-3">
                                                <label>Estado:<span class="obligatorio"> *</span></label>
                                                <select class='form-control' id="sltEstadoToner" name="sltEstadoToner"  <?php echo $drawList ?> disabled>
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
                                            </div>
                                            <div class="form-group col-md-3">
                                                <label>Delegación:<span class="obligatorio"> *</span></label>
                                                <input type="text"class='form-control' id="txdivelegacionToner" name="txdivelegacionToner" value="<?php echo $delegacion; ?>" <?php echo $readCliente; ?>  />
                                            </div>
                                            <div class="form-group col-md-3">
                                                <label>C.P:<span class="obligatorio"> *</span></label>
                                                <input type="text"class='form-control' id="txtCpToner" name="txtCpToner" value="<?php echo $cp; ?>"   <?php echo $readCliente; ?>/>
                                            </div>
                                            <div class="form-group col-md-3">
                                                <label>Latitud</label>
                                                <input type="text"class='form-control' id="latitud" name="latitud" value=""  <?php echo $readCliente; ?>/>
                                            </div>
                                            <div class="form-group col-md-3">
                                                <label>Longitud</label>
                                                <input type="text"class='form-control' id="longitud" name="longitud" value=""  <?php echo $readCliente; ?>/>
                                            </div>
                                        </div>
                                    </fieldset>                                    
                                </fieldset>
                            </div>                            
                        </div>
                    <?php } ?>
                </div>
                <div>
                    <fieldset>
                        <legend>Datos del <?php echo $nombre_objeto; ?></legend>
                        <div class="form-row">
                            <div  class="form-group col-md-3">
                                <label>Prioridad del <?php echo $nombre_objeto; ?>:</label>
                                <?php
                                $result2 = $catalogo->obtenerLista("SELECT pt.IdPrioridad, pt.Prioridad, tp.TipoPrioridad,  c.Hexadecimal
                                    FROM `c_prioridadticket` AS pt
                                    LEFT JOIN c_color AS c ON c.IdColor = pt.IdColor
                                    LEFT JOIN c_tipoprioridad AS tp ON tp.IdTipoPrioridad = pt.IdTipoPrioridad WHERE pt.Activo = 1;");

                                echo "<select class='form-control' id='prioridad' name='prioridad'>";
                                echo "<option value = 0 >Seleccione una prioridad</option>";
                                while($rs2 = mysql_fetch_array($result2)){
                                    echo "<option value='".$rs2['IdPrioridad']."' style='background: #".$rs2['Hexadecimal'].";'>".$rs2['Prioridad']." (".$rs2['TipoPrioridad'].")</option>";
                                }
                                echo "</select>";
                                ?>
                            </div>
                        </div>
 <hr size="10" width="100%" />
                        <fieldset>
                    <legend>Contacto que solicitó el <?php echo $nombre_objeto; ?></legend>
                          <div class="form-row">
                            <div class="form-group col-md-3">
                                <input type="radio" name="rdContacto" id="rdContacto" value="1" onclick="mostrarTipoContacto(1)"  <?php echo $desactivarRadio ?>/>
                                <label>Nuevo contacto</label><br>
                                <input type="radio" name="rdContacto" id="rdContacto" value="0" onclick="mostrarTipoContacto(0)" checked="checked" <?php echo $desactivarRadio ?>/>
                                <label>Contacto existente</label>
                            </div>
                            <div class="form-group col-md-3">
                                <label>Nombre</label>
                                <div id="contactoExistente">
                                    <select class='form-control' id="txtNombre" name="txtNombre"  onchange="DatosContacto(this.value);" <?php  echo $drawList; ?>>
                                        <option value="0">Seleccione contacto</option>
                                        <?php
                                        if ($claveLocalidad != "") {
                                            $queryContactos = $catalogo->obtenerLista("SELECT ct.IdContacto,ct.Nombre,ct.Telefono,ct.Celular,ct.CorreoElectronico,(SELECT tc.Nombre FROM c_tipocontacto tc WHERE ct.IdTipoContacto=tc.IdTipoContacto) AS tipoContacto,ct.IdTipoContacto AS IdTipoContacto,ct.ClaveEspecialContacto AS ClaveEspecialContacto
                                                FROM c_contacto ct WHERE ct.ClaveEspecialContacto='$claveLocalidad' OR ct.ClaveEspecialContacto='$claveCliente'");

                                            while ($rs = mysql_fetch_array($queryContactos)) {
                                                $s = "";
                                                if ($nombreResp == $rs['Nombre'])
                                                    $s = "selected";
                                                echo "<option value='" . $rs["Nombre"] . " // " . $rs["Telefono"] . " // " . $rs["Celular"] . " // " . $rs["CorreoElectronico"] . " // " . $rs['IdContacto'] . " // " . $rs['IdTipoContacto'] . " // " . $rs['ClaveEspecialContacto'] . "' $s>" . $rs['Nombre'] . "   (" . $rs['tipoContacto'] . ")" . "</option>";
                                            }
                                        }
                                        ?>
                                    </select>
                                </div>
                                <div id="contactoNuevo">
                                    <input class='form-control'type="text" id="txtNombre1" name="txtNombre1" value="<?php echo $nombreResp; ?>"  <?php echo $read; ?>/>
                                </div>

            <!--//                                    <input class='form-control'type="text" id="txtNombre" name="txtNombre" value="<?php //echo $nombreResp; ?>" style='width: 680px'>-->
                            </div>

                            <div class="form-group col-md-3">
                                <br>
                                <br>
                                <label for="txtTelefono1">Telefono 1:</label>
                                <input class='form-control'type="text" id="txtTelefono1" name="txtTelefono1" value="<?php echo $telefono1Resp; ?>"   <?php echo $read; ?> />
                            </div>
                            <div class="form-group col-md-3">
                                <br>
                                <br>
                                <label for="txtExtencion1">Extención 1:</label>
                                <input class='form-control'type="text" id="txtExtencion1" name="txtExtencion1" value="<?php echo $Extencio2Resp; ?>"   <?php echo $read; ?> readonly/>
                            </div>
                            <div class="form-group col-md-3">
                                <label for="txtTelefono2">Telefono 2<br/>(Este dato solo se guarda para el ticket actual):</label>
                                <input class='form-control'type="text" id="txtTelefono2" name="txtTelefono2" value="<?php echo $telefono2Atencion; ?>"   <?php echo $read; ?> readonly/>
                            </div>
                            <div class="form-group col-md-3">
                                <label for="txtExtencion2">Extención 2<br/>(Este dato solo se guarda para el ticket actual):</label>
                                <input class='form-control'type="text" id="txtExtencion2" name="txtExtencion2" value="<?php echo $Extencio2Resp; ?>"  <?php echo $read; ?> readonly/>
                            </div>
                            <div class="form-group col-md-3">
                                <label for="txtCelular">Celular</label>
                                <input class='form-control'type="text" id="txtCelular" name="txtCelular" value="<?php echo $celularResp; ?>"  <?php echo $read; ?> />
                            </div>

                            <div class="form-group col-md-3">
                                <label for="correoElectronico">Correo electrónico</label>
                                <input class='form-control'type="text" id="correoElectronico" name="correoElectronico" value="<?php echo $correoResp; ?>"  <?php echo $read; ?> />
                                <div id="errorCorreoResp"> <?php echo $read; ?> </div>
                            </div>

                            <div class="form-group col-md-3">
                                <label for="lstHA">Horario de atención:</label>
                                <?php
                                $hinicioResp = "";
                                $minicioResp = "";
                                $tinicioResp = "";
                                list($hinicioResp) = explode(",", $horarioReponsableInicio);
                                ?>
                                <br>
                                <label>Hora de inicio</label>
                                <select class='form-control' id='lstHR' name='lstHR' <?php echo $drawList; ?>>
                                    <?php
                                    for ($x = 1; $x <= 12; $x++) {
                                        $s = "";
                                        if ($x == "9" || $hinicioResp == $x){
                                            $s = "selected";
                                            echo "<option value='" . $x . "' $s>" . $x . "</option> ";
                                        }
                                    }
                                    ?>
                                </select>
                                <select class='form-control' id='lstMR' name='lstMR' <?php echo $drawList; ?>>
                                    <option value="00">00</option>
                                    <option value="15">15</option>
                                    <option value="30">30</option>
                                    <option value="45">45</option>
                                </select>
                                <select class='form-control' id='lstTA' name='lstTA' <?php echo $drawList; ?>>
                                    <option value="am">am</option>
                                    <option value="pm">pm</option>
                                </select>
                            </div>

                            <div class="form-group col-md-3">

                                <label for="lstFinR">Hora de fin:</label>
                                <select class='form-control' id='lstFinHR' name='lstFinHR' <?php echo $drawList; ?>>
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
                                <select class='form-control' id='lstFinMR' name='lstFinMR' <?php echo $drawList; ?>>
                                    <option value="00" selected>00</option>
                                    <option value="15">15</option>
                                    <option value="30">30</option>
                                    <option value="45">45</option>
                                </select>
                                <select class='form-control' id='lstFinTR' name='lstFinTR' <?php echo $drawList; ?>>
                                    <option value="am">am</option>
                                    <option value="pm" selected>pm</option>
                                </select>
                            </div>
                        </div>
                    </fieldset>    
                    <?php if ($idTicket == "") { ?>
                        <a href='#' onclick='CopiarDatosContacto();
                                    return false;' title='Copiar datos de contacto' >Copiar datos de contacto responsable de <?php echo $nombre_objeto; ?> o centro de atención</a>
                       <?php } ?>

<hr size="10" width="100%" />                       
                    <fieldset>
                        <legend>Contacto de atención del <?php echo $nombre_objeto; ?> (Contacto en sitio)</legend>

                        <div class="form-row">
                            <div class="form-group col-md-3">
                                <label for="txtNombreAtencion">Nombre</label>
                                <input type="text" class="form-control" id="txtNombreAtencion" name="txtNombreAtencion" value="<?php echo $telefono1Atencion; ?>" <?php echo $read; ?>/>
                            </div>
                            <div class="form-group col-md-3">
                                <label for="txtTelefono1Atencion">Telefono 1:</label>
                                <input type="text" class="form-control" id="txtTelefono1Atencion" name="txtTelefono1Atencion" value="<?php echo $telefono1Atencion; ?>" <?php echo $read; ?>/>
                            </div>
                            <div class="form-group col-md-3">
                                <label for="txtExtencion1Atencion">Extención 1:</label>
                                <input type="text" class="form-control" id="txtExtencion1Atencion" name="txtExtencion1Atencion" value="<?php echo $Extencio1Atencion; ?>"  <?php echo $read; ?>/>
                            </div>
                            <div class="form-group col-md-3">
                                <label for="txtTelefono2Atencion">Telefono 2:</label>
                                <input type="text" class="form-control" id="txtTelefono2Atencion" name="txtTelefono2Atencion" value="<?php echo $telefono2Atencion; ?>"  <?php echo $read; ?>/>
                            </div>
                            <div class="form-group col-md-3">
                                <label for="txtExtencion2Atencion">Extención 2:</label>
                                <input type="text" class="form-control" id="txtExtencion2Atencion" name="txtExtencion2Atencion" value="<?php echo $Extencio2Atencion; ?>"  <?php echo $read; ?>/>
                            </div>
                            <div class="form-group col-md-3">
                                <label for="txtCelularAtencion">Celular</label>
                                <input type="text" class="form-control" id="txtCelularAtencion" name="txtCelularAtencion" value="<?php echo $celularAtencion; ?>"  <?php echo $read; ?>/>
                            </div>
                            <div class="form-group col-md-3">
                                <label for="txtCorreoElecdivonico">Correo electrónico</label>
                                <input type="text" class="form-control" id="txtCorreoElecdivonico" name="txtCorreoElecdivonico" value="<?php echo $correoAtencion; ?>"  <?php echo $read; ?>/>
                                <div id='errorCorreoAtencion'></div>
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="form-group col-md-3">
                                <label class="m-0" for="lstHA">Horario de atención:</label>
                                <br>
                                <label>Hora de inicio:</label>
                                <select class='form-control' id='lstHA' name='lstHA' <?php echo $drawList; ?>>
                                    <?php
                                    for ($x = 1; $x <= 12; $x++) {
                                        $s = "";
                                        if ($x == "9")
                                            $s = "selected";
                                        echo "<option value='" . $x . "' $s>" . $x . "</option> ";
                                    }
                                    ?>
                                </select>
                                <select class='form-control' id='lstMA' name='lstMA' <?php echo $drawList; ?>>
                                    <option value="00" selected>00</option>
                                    <option value="15">15</option>
                                    <option value="30">30</option>
                                    <option value="45">45</option>
                                </select>
                                <select class='form-control' id='lstTA' name='lstTA' <?php echo $drawList; ?>>
                                    <option value="am" selected>am</option>
                                    <option value="pm">pm</option>
                                </select>
                            </div>
                            <div class="form-group col-md-3">
                                <label for="lstFinA">Hora de fin:</label>
                                <select class='form-control' id='lstFinHA' name='lstFinHA' <?php echo $drawList; ?>>
                                    <?php
                                    for ($x = 1; $x <= 12; $x++) {
                                        $s = "";
                                        if ($x == "6")
                                            $s = "selected";
                                        echo "<option value='" . $x . "' $s>" . $x . "</option> ";
                                    }
                                    ?>
                                </select>
                                <select class='form-control' id='lstFinMA' name='lstFinMA' <?php echo $drawList; ?>>
                                    <option value="00" selected>00</option>
                                    <option value="15">15</option>
                                    <option value="30">30</option>
                                    <option value="45">45</option>
                                </select>
                                <select class='form-control' id='lstFinTA' name='lstFinTA' <?php echo $drawList; ?>>
                                    <option value="am">am</option>
                                    <option value="pm" selected>pm</option>
                                </select>
                            </div>
                        </div>
                    </fieldset>

                    <div class="form-row">
                        <div class="form-group  col-md-4">
                            <label>No. <?php echo $nombre_objeto; ?> <?php if($definiciones->getRegistroById(1)){ echo strtolower($definiciones->getNombre()); }else{ echo "cliente"; }?>:</label>
                            <input type="text" class="form-control" id="txtNoTicketClienteGral" name="txtNoTicketClienteGral" value='<?php echo $ticketCliente ?>' <?php echo $read; ?>/>
                            <label>No. <?php echo $nombre_objeto; ?> distribuidor:</label>
                            <input type="text" class="form-control" id="txtNoTickedivistribucionGral" name="txtNoTickedivistribucionGral" value='<?php echo $tickedivistribucion; ?>' <?php echo $read; ?>/>
                        </div>   
                        <div class="form-group col-md-4">
                            <label>Descripción:</label>
                            <textarea class="form-control" style="width: 100%; height: 150px;" id='descripcion' name='descripcion' <?php echo $read; ?>><?php echo $descripcion; ?></textarea>
                        </div>
                        <div class="form-group col-md-4">
                            <label>Observaciones adicionales:</label>
                            <textarea class="form-control" style="width: 100%;height: 150px;" id='observacion' name='observacion' <?php echo $read; ?>><?php echo $observacion; ?></textarea>
                        </div>   
                        <div class="form-group col-md-3">
                            <label>Área de atención<span class="obligatorio"> *</span>:</label>
                            <select class='form-control' id="areaAtencionGral" name="areaAtencionGral"  <?php echo $drawList; ?>>
                                <?php
                                if ($tipoReporte == "15") {
                                    $queryArea = "SELECT  e.IdEstado,e.Nombre  FROM c_estado e,c_flujo f,k_flujoestado fe WHERE e.IdEstado=fe.IdEstado AND f.IdFlujo=fe.IdFlujo AND f.IdFlujo=3";
                                } else if ($tipoReporte != "15" && $tipoReporte != "") {
                                    $queryArea = "SELECT  e.IdEstado,e.Nombre  FROM c_estado e,c_flujo f,k_flujoestado fe WHERE e.IdEstado=fe.IdEstado AND f.IdFlujo=fe.IdFlujo AND f.IdFlujo=2";
                                }

                                $query = $catalogo->obtenerLista($queryArea);
                                if(mysql_num_rows($query) > 1){
                                    echo "<option value='0'>Seleccione el area de atención</option>";
                                }
                                while ($rs = mysql_fetch_array($query)) {
                                    $s = "";
                                    if ($areaAtencion != "" && $areaAtencion == $rs['IdEstado'])
                                        $s = "selected";
                                    echo "<option value='" . $rs['IdEstado'] . "' $s>" . $rs['Nombre'] . "</option>";
                                }
                                ?>
                            </select>
                        </div>
                        <div class="form-group col-md-3">
                            <label>Prioridad del ticket: </label>
                            <?php
                            $result2 = $catalogo->obtenerLista("SELECT pt.IdPrioridad, pt.Prioridad, tp.TipoPrioridad,  c.Hexadecimal
                                FROM `c_prioridadticket` AS pt
                                LEFT JOIN c_color AS c ON c.IdColor = pt.IdColor
                                LEFT JOIN c_tipoprioridad AS tp ON tp.IdTipoPrioridad = pt.IdTipoPrioridad WHERE pt.Activo = 1;");
                            echo "<select class='form-control' id='prioridad' name='prioridad'>";
                            echo "<option value = 0 >Seleccione una prioridad</option>";
                            while($rs2 = mysql_fetch_array($result2)){
                                echo "<option value='".$rs2['IdPrioridad']."' style='background: #".$rs2['Hexadecimal'].";'>".$rs2['Prioridad']." (".$rs2['TipoPrioridad'].")</option>";
                            }
                            echo "</select>";
                            ?>
                        </div>
                    </div>
                </fieldset>
                <?php if ($detalle != "1" && $permisos_grid->getModificar()) { ?>
                    <input class="button btn btn-lg btn-block btn-outline-success mt-3 mb-3" type = "submit" id = "botonGuardar" name = "botonGuardar" class = "boton" value = "Guardar"/>
                    <?php if ($botonCancelar != "1") { ?>
                        <input class="button btn btn-lg btn-block btn-outline-danger mt-3 mb-3" type = "submit" class = "boton" value = "Cancelar" onclick = "cambiarContenidos('<?php echo $pagina_listaRegresar; ?>'); return false;"/>
                        <?php
                    }
                }
                ?>
            </div>
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
<input type="hidden" id="mostrarDatos" name="mostrarDatos" value="<?php echo $mostrarDatos; ?>"/>
</div>
</body>
</div>
<?php echo "<script>$rango</script>" ?>
</html>

