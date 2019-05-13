<?php
session_start();
include_once("../WEB-INF/Classes/AgregarNota.class.php");
include_once("../WEB-INF/Classes/Catalogo.class.php");
include_once("../WEB-INF/Classes/AlmacenConmponente.class.php");
include_once("../WEB-INF/Classes/LecturaTicket.class.php");
include_once("../WEB-INF/Classes/Lectura.class.php");
include_once("../WEB-INF/Classes/Ticket.class.php");
include_once("../WEB-INF/Classes/PermisosSubMenu.class.php");
include_once("../WEB-INF/Classes/EquipoCaracteristicasFormatoServicio.class.php");
include_once("../WEB-INF/Classes/ParametroGlobal.class.php");
include_once("../WEB-INF/Classes/Parametros.class.php");
include_once("../WEB-INF/Classes/NotaTicket.class.php");
include_once("../WEB-INF/Classes/PermisosSubMenu.class.php");

$parametros = new Parametros();
$parametroGlobal = new ParametroGlobal();

$mostrarContadores = "1";
if ($parametros->getRegistroById("13") && $parametros->getValor() == "0") {
    $mostrarContadores = "0";
}

$puede_agregar_nota = false;

//Para saber si tiene permiso de agregar nota
$permisos_grid = new PermisosSubMenu();
if( 
        ($permisos_grid->getPermisosSubmenu($_SESSION['idUsuario'], "mesa/lista_ticket.php") && $permisos_grid->getModificar()) ||
        ($permisos_grid->getPermisosSubmenu($_SESSION['idUsuario'], "mesa/lista_ticket_new.php") && $permisos_grid->getModificar()) ||
        ($permisos_grid->getPermisosSubmenu($_SESSION['idUsuario'], "almacen/lista_refaccionesSolicitadas.php") && $permisos_grid->getModificar()) ||
        ($permisos_grid->getPermisosSubmenu($_SESSION['idUsuario'], "almacen/lista_refaccionesSolicitadas_new.php") && $permisos_grid->getModificar()) ||
        ($permisos_grid->getPermisosSubmenu($_SESSION['idUsuario'], "almacen/toner_solicitado.php") && $permisos_grid->getModificar())
        
    )
{
    $puede_agregar_nota = true;
}

$lecturaTicket = new LecturaTicket();
$permiso = new PermisosSubMenu();
$lectura_obj = new Lectura();

$permisos_grid2 = new PermisosSubMenu();
$nombre_objeto = $permisos_grid2->getNombreTicketSistema();
$nombre_puesto = $permisos_grid2->getNombreTecnicoSistema();

date_default_timezone_get();
$anio = date('Y');
$mes = date('m');
$dia = date('d');
$fecha = $anio . "-" . $mes . "-" . $dia;
$pagina_listaRegresar = "mesa/lista_ticket.php";
$activo = "checked='checked'";
$read = "";
$idnota = "";
$idTicket = "";
$diagnostico = "";
$idestatus = "";
$hora = "";
$id = "";
$idRefaccion = array();
$cantidad = array();
$cantidad1 = "";
$contador = 0;
$validar = "style='visibility:hidden'";
$boton = "Guardar";
$cantidadSuministro1 = "";
$modelo = "";
$parte = "";
$descripcion = "";
$show = "checked='checked'";
$cotizacion = "checked='checked'";
$accion = "";
$interna = "";
$externa = "";
$NoSerieTicket = "";
$refacciones_solicitadas = array();
$precioArray = array();
$precioArray[0]= "Precio";
$rendimientoArray = array ();
$rendimientoArray[0]= "Rendomiento";
$viatico="";
$monto=0;
$km="";
$tiempoER="";
$tiempoEM="";
$noBoleto="";
$EdoComponente = "CN";
$incidenciaArchivo = true;
if (isset($_GET['idTicket1'])) {
    $interna = $_GET['idTicket1'];
    $idTicket = $interna;
    $pagina_lista = "mesa/lista_ticket.php";
} else if(isset ($_GET['id'])){
    $interna = $_GET['id'];
    $idTicket = $interna;
    $pagina_lista = "mesa/lista_ticket.php";
} else if (isset($_GET['idTicket'])) {
    $externa = $_GET['idTicket'];
    $idTicket = $externa;
    $pagina_lista = $_SESSION['liga'] . "/Operacion/MesaServicio/ConsultaDetalleTicketToner.aspx?Operacion=&IdTicket=" . $externa . "&Vista=Detalle&uguid=" . $_SESSION['user'];
}

if (isset($_GET['pagina_anterior'])) {
    $pagina_lista = $_GET['pagina_anterior'];
    $pagina_listaRegresar = $pagina_lista;
}else if(isset ($_GET['param1']) && !empty ($_GET['param1'])){
    $pagina_lista = $_GET['param1'];
    $pagina_listaRegresar = $pagina_lista;
}

$usuarioSolicitud = "";
$areaTickte = "";
if (isset($_POST['area']) && $_POST['area'] != "") {
    $areaTickte = $_POST['area'];
}

$lista = array();
$lis = array();
$descripcionR = array();
?>
<!DOCTYPE html>
<html lang="es">
    <head>
        <?php
        if (!empty($idTicket)) {
            $consultaComponentes = "SELECT c.NoParte,REPLACE(REPLACE(c.Descripcion, '\r', ''), '\n', ' ') AS Descripcion,c.Modelo
                FROM c_ticket AS t
                LEFT JOIN c_bitacora AS b ON b.NoSerie = t.NoSerieEquipo
                LEFT JOIN k_equipocomponentecompatible AS kecc ON kecc.NoParteEquipo = b.NoParte
                LEFT JOIN c_componente AS c ON c.NoParte = kecc.NoParteComponente
                WHERE t.IdTicket = $idTicket AND c.IdTipoCOmponente = 1;";
        } else {
            $consultaComponentes = "SELECT c.NoParte,REPLACE(REPLACE(c.Descripcion, '\r', ''), '\n', ' ') AS Descripcion,c.Modelo 
                FROM c_componente c WHERE c.IdTipoComponente='1' AND c.Activo = 1";
        }

        if ($consultaComponentes != "") {
            ?>
            <script>
                var arreglo = new Array();
                $(function () {
                    var otra = "otra";
                    var availableTags = [
    <?php
    $obj = new AlmacenComponente();
    $obj->serchNoSerie($consultaComponentes);
    $lista = $obj->getArreglo_php();
    $lis = $obj->getArreglo_php2();
    $descripcionR = $obj->getArreglo_php3();
    for ($x = 0; $x < count($lista); $x++) {
        echo "'" . $lista[$x] . " / " . $lis[$x] . " / " . $descripcionR[$x] . "',";
    }
    ?>
                    ];
                    arreglo = availableTags;
                    $(".refaccion").autocomplete({
                        source: availableTags,
                        minLength: 2
                    });
                });
                function ArregloCondatos() {
                    return arreglo;
                }
                //
                $(".filtroComponentes").multiselect({
                    multiple: false,
                    noneSelectedText: "No ha seleccionado",
                    selectedList: 1
                }).multiselectfilter({
                    label: 'Filtro',
                    placeholder: 'Escribe el filtro'
                });
            </script>
        <?php } ?>
        <?php if ($externa != "") { ?>
            <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
            <meta http-equiv="expires" content="-1">
            <link rel="shortcut icon" href="resources/images/logos/ra4.png" type="image/x-icon"/>
            <title>Genesis</title>
            <meta http-equiv="expires" content="-1">
            <!-- JS -->
            <link rel="stylesheet" href="../resources/css/genesis/jquery-ui-1.10.3.custom.css" type="text/css" media="screen" />
            <script src="../resources/js/jquery/jquery-1.11.3.min.js"></script>
            <script src="../resources/js/jquery/jquery-ui.min.js"></script>        
            <script type="text/javascript" src="../resources/js/jquery/jquery.validate.js"></script>
            <script type="text/javascript" src="../resources/js/jquery/jquery-ui-timepicker-addon.js"></script>
            <script type="text/javascript" src="../resources/js/funciones.js"></script>                   

            <!-- Tables -->
            <script type="text/javascript" language="javascript" src="../resources/media/js/jquery.dataTables.js"></script>
            <script type="text/javascript" language="javascript" src="../resources/media/js/TableTools.min.js"></script>
            <link href="../resources/css/table/demo_page.css" rel="stylesheet" type="text/css">
            <link href="../resources/css/table/demo_table_jui.css" rel="stylesheet" type="text/css">
            <link href="../resources/css/table/TableTools.css" rel="stylesheet" type="text/css">
            <link href="../resources/css/sicop.css" rel="stylesheet" type="text/css">  

            <link id="linkCSS" href="./css/Site.css" rel="stylesheet" type="text/css" media="all">
            <link href="./css/Site.css" rel="stylesheet" type="text/css">
            <link href="../resources/css/menu-12.css" rel="stylesheet" type="text/css" media="all">
            <style>
                .contenido{
                    width: 800px;
                    margin-left:auto;
                    margin-right:auto;
                }

                .style1{
                    width: 30%;
                }
            </style>
            <script type="text/javascript" language="javascript" src="../resources/js/paginas/agregarNota.js"></script> 
            <script type="text/javascript" language="javascript" src="../resources/js/paginas/validadRefaccion.js"></script> 
            <script type="text/javascript" language="javascript" src="../resources/js/jquery/jquery.mask.min.js"></script> 
            <script type="text/javascript" language="javascript" src="../resources/js/jquery/jquery.mask.js"></script> 

            <!-- multiselect -->
            <script src="../resources/js/multiselect/jquery.multiselect.min.js"></script>
            <script src="../resources/js/multiselect/jquery.multiselect.filter.min.js"></script>
            <link href="../resources/css/multiselect/jquery.multiselect.css" rel="stylesheet" type="text/css">
            <link href="../resources/css/multiselect/jquery.multiselect.filter.css" rel="stylesheet" type="text/css">
        <?php } else {
            ?>
            <script type="text/javascript" language="javascript" src="resources/js/paginas/agregarNota.js"></script> 
            <script type="text/javascript" language="javascript" src="resources/js/paginas/validadRefaccion.js"></script> 
            <script type="text/javascript" language="javascript" src="resources/js/jquery/jquery.mask.min.js"></script> 
            <script type="text/javascript" language="javascript" src="resources/js/jquery/jquery.mask.js"></script> 
        <?php } ?>
        <script>
                $(document).ready(function () {
                    $('.boton').button().css('margin-top', '20px');
                });
        </script>
        <script>
            $(function () {
                $('#fecha').datepicker({dateFormat: 'yy-mm-dd'});
                $('#hora').mask("99:99:99");

            });

            $("#hora").val(getHoraF(new Date()));

            function getHoraF(d)
            {

                var hora = d.getHours();
                var min = d.getMinutes();
                var seg = d.getSeconds();
                var str_segundo = new String(seg);
                if (str_segundo.length == 1)
                    seg = "0" + seg;

                var str_minuto = new String(min);
                if (str_minuto.length == 1)
                    min = "0" + min;

                var str_hora = new String(hora);
                if (str_hora.length == 1)
                    hora = "0" + hora;
                return hora + ":" + min + ":" + seg;
            }
            function getFechaF(date) {
                var day = ('0' + date.getDate()).slice(-2).toString();
                var month = date.getMonth() + 1;
                var year = date.getFullYear();
                return year + '-' + month + '-' + day;
            }
        </script>
    </head>
    <body>
        <div class="principal">
            <?php
            if (isset($_POST['idNota'])) {
                $id = $_POST['idNota'];
                $obj = new AgregarNota();
                $obj->getRegistroById($_POST['idNota']);
                $read = "readonly='readonly'";
                $idnota = $obj->getIdNotaTicket();
                $idTicket = $obj->getIdTicket();
                $diagnostico = $obj->getDiagnosticoSolucion();
                $idestatus = $obj->getIdestatusAtencion();
                
                
                if ($obj->getActivo() == "0") {
                    $activo = "";
                }               

                $refacciones_visibles = array();

                
                $refaccion = $obj->getRefaccionesById($id);
                if ($obj->getCotizacion() == "No") {
                    $cotizacion = "";
                }               
                
                $EdoComponente = $obj->getEstadoComponente();


                while ($rs = mysql_fetch_array($refaccion)) {
                    $idRefaccion[$contador] = $rs['NoParteComponente'];
                    $cantidad[$contador] = $rs['Cantidad'];
                    if ($obj->estaTodaEntregadaNotaRefaccion($id, $rs['NoParteComponente']) && isset($_POST['editaRefaccion'])) {
                        $refacciones_visibles[$contador] = false;
                    } else {
                        $refacciones_visibles[$contador] = true;
                    }
                    $contador++;
                }

                $validar = "";
                $cantidad1 = $cantidad[0];
                $pagina_lista = "hardware/lista_validarRefaccion.php";
                $boton = "Guardar y Validar";
                $pagina_listaRegresar = "hardware/lista_validarRefaccion.php";
                if ($obj->getActivo() == "0") {
                    $show = "";
                }
                $accion = "validar";
                $usuarioSolicitud = $_POST['usuario'];
            } else {
                $refacciones_visibles[0] = true;
            }

            $ticket_obj = new Ticket();
            $fechaHora = date('Y') . "-" . date('m') . "-" . date('d');
            if ($ticket_obj->getTicketByID($idTicket)) {
                $NoSerieTicket = $ticket_obj->getNoSerieEquipo();
                $fechaHora = $ticket_obj->getFechaHora();
            }
            ?>
            <?php
            $booleanFecha = FALSE;
            $fecha_limite = strtotime("2014-03-31");
            $fecha_ticket = strtotime($fechaHora);
            if ($fecha_ticket >= $fecha_limite) {
                $booleanFecha = TRUE;
            } else {
                $booleanFecha = FALSE;
                //$booleanFecha = TRUE;//False en caso de querer abrir con las ligas a .NET, true para abrir con PHP
            }
            echo "Historial $nombre_objeto: ";
            if ($booleanFecha) {
                $tipo_viejo = 0;
            } else {
                $tipo_viejo = 1;
            }
            ?>
            <a href='#' onclick='detalleTicket("mesa/alta_ticketphp.php", "<?php echo$idTicket; ?>", "<?php echo $ticket_obj->getTipoReporte(); ?>", "1", "", "<?php echo $tipo_viejo; ?>");
                    return false;' title='Detalle <?php echo $nombre_objeto; ?>' ><img src="resources/images/Textpreview.png"/></a>
            <fieldset>
                <legend>Nota de diagnóstico y atención</legend>
                <?php
                    $tipoReporteTicket = (int)$ticket_obj->getTipoReporte();
                    if($parametroGlobal->getRegistroById(27) && $parametroGlobal->getActivo() == "1"){
                        if($tipoReporteTicket == 26 || $tipoReporteTicket == 62){
							$notaTicket = new NotaTicket();
                            $notaTicket -> setIdTicket($ticket_obj -> getIdTicket());
                             if(!$notaTicket->buscarNotaConArchivo()){
                                $incidenciaArchivo = false;
								$nombre_tipo = "instalación";
								if($tipoReporteTicket == 26){
									$nombre_tipo = "mantenimiento";
								}
                                echo "<h3>Este ticket de $nombre_tipo no tiene un archivo adjunto agregado en ninguna nota, no se podrá cerrar el ticket</h3>";
                            }                            
                        }
                    }                                     
                ?>
                <input type="hidden" id="mostrar_contadores" name="mostrar_contadores" value="<?php echo $mostrarContadores; ?>"/>
                <form id="formAgregarNota" name="formAgregarNota" action="/" method="POST" enctype="multipart/form-data">
                    <h2><?php echo $nombre_objeto; ?>: <?php echo $idTicket; ?></h2>
                    <?php
                    //Se muestra informacion de Ticket si se trata de un viaje especial 
                    $catalogoes = new Catalogo();
                    $consulta = "SELECT (CASE WHEN ktt.FechaHoraInicio != 0 THEN  ktt.FechaHoraInicio ELSE ct.FechaHora END) AS FechaHora,
                             CONCAT(cu.Loggin,' ',cu.Nombre,' ',cu.ApellidoPaterno,' ',cu.ApellidoMaterno) AS Operador,ce.*, ccor.Ciudad AS Es_or, ccdes.Ciudad AS Es_des FROM c_especial ce  
                             LEFT JOIN c_ciudades AS ccor ON ccor.IdCiudad = ce.Estado_or LEFT JOIN c_ciudades AS ccdes ON ccdes.IdCiudad = ce.Estado_des 
                             LEFT JOIN k_tecnicoticket AS ktt ON ktt.IdTicket = ce.idTicket LEFT JOIN c_usuario AS cu ON cu.IdUsuario=ktt.IdUsuario
                             LEFT JOIN c_ticket AS ct ON ct.IdTicket=ce.idTicket 
                             WHERE ce.idTicket = " . $idTicket . ";";
                    $result = $catalogoes->obtenerLista($consulta);
                    if (mysql_num_rows($result) > 0) {
                        $rses = mysql_fetch_array($result);
                        echo "<b>Origen:</b> (" . $rses['Origen'] . ") " . $rses['Calle_or'] . " " . $rses['NoExterior_or'] . ", " . $rses['Colonia_or'] . ", " . $rses['CodigoPostal_or'] . "," . $rses['Ciudad_or'] . ", " . $rses['Delegacion_or'] . " " . $rses['Es_or'] . "<br/>";
                        echo "<b>Destino:</b> (" . $rses['Destino'] . ") " . $rses['Calle_des'] . " " . $rses['NoExterior_des'] . ", " . $rses['Colonia_des'] . ", " . $rses['CodigoPostal_des'] . "," . $rses['Ciudad_des'] . ", " . $rses['Delegacion_des'] . " " . $rses['Es_des'] . "<br/>";
                        echo "<b>Operador:</b> " . $rses['Operador'] . "<br/>";
                        echo "<b>Fecha y Hora:</b> " . $rses['FechaHora'] . "<br/><br/>";
                    }
                    ?>

                    <table>
                        <tr>
                            <td>Fecha: </td>
                            <td><input type='text' id='fecha' name='fecha' value="<?php echo $fecha ?>"/></td>   
                            <td>Hora: </td>
                            <td><input type='text' id='hora' name='hora' value="<?php echo $hora; ?>"/></td>   
                        </tr>
                        <tr>
                            <td>Diagnóstico o solución al reporte:</td>
                            <td colspan='3'><textarea id='diagnostico' name='diagnostico' cols='50'><?php echo $diagnostico; ?></textarea></td>
                        </tr>
                        <tr>
                            <td>Estatus de atención:</td>
                            <td>
                                <select id="estatus" name="estatus" onchange="mostrarRefacciones();">
                                    <?php
                                    $catalogo = new Catalogo();
                                    $query = $catalogo->obtenerLista("SELECT e.IdEstado,e.Nombre AS nombreEstado,f.IdFlujo,f.Nombre,fe.IdKFlujo
                                                                            FROM c_estado e,c_flujo f,k_flujoestado fe
                                                                            WHERE e.IdEstado=fe.IdEstado
                                                                            AND fe.IdFlujo=f.IdFlujo AND e.Activo=1 AND e.IdEstado <> 60 
                                                                            AND fe.IdFlujo=6 ORDER BY nombreEstado ASC");
                                    echo "<option value='0' >Selecciona una opción</option>";
                                    while ($rs = mysql_fetch_array($query)) {
                                        $s = "";
                                        if ($idestatus != "" && $idestatus == $rs['IdEstado']) {
                                            $s = "selected";
                                        }
                                        //Sino tiene el permiso para pendiente para proximo servicio, no se pone el estado
                                        if ($rs['IdEstado'] == 81 && !$permiso->tienePermisoEspecial($_SESSION['idUsuario'], 24)) {
                                            continue;
                                        }
                                        if((int)$rs['IdEstado'] == 16 && $incidenciaArchivo == false){
                                            continue;
                                        }
                                        echo "<option value=" . $rs['IdEstado'] . " " . $s . ">" . $rs['nombreEstado'] . "</option>";
                                    }
                                    ?>
                                </select>
                            </td>
                            <td><input type="checkbox" name="activo" id="activo" <?php echo $activo; ?>/>Activo</td>
                            <td><input type="checkbox" name="show" id="show" <?php echo $show; ?>/>Mostrar a cliente</td>
                        </tr>
                        <tr>
                            <td>Subir archivo para incidencias:</td>
                            <td><input type='file' name='file' id='file'></td>
                        </tr>
                    </table> 
                    
                    <div id="viatico">
                        <table>
                            <tr>
                                <td>Tipo de viático:</td>
                                <td>
                                    <select id='tipo_viatico' name='tipo_viatico' style='max-width: 300px'>
                                        <option value='0'>Seleccione una opción</option>
                                        <?php
                                        $catalogo1 = new Catalogo();
                                        $query1 = $catalogo1->obtenerLista("SELECT * FROM c_tipoviatico WHERE activo=1 ORDER BY nombre;");
                                        while ($rs = mysql_fetch_array($query1)) {
                                            $s = "";
                                            if ($viatico != "" && $viatico == $rs['idTipoViatico']) {
                                                $s = "selected";
                                            }
                                            echo "<option value='" . $rs['idTipoViatico'] . "' $s>" . $rs['nombre'] . "</option>";
                                        }
                                        ?>
                                    </select>
                                </td>
                                <td> Monto:</td>
                                <td><input type='text' style='max-width: 100px' id='monto' name='monto' value="<?php echo $monto; ?>"/></td>
                                <td><input type="checkbox" id="cobrar" name="cobrar" value="1"/> Cobrar al cliente</td>
                                <td><input type="checkbox" id="pagar" name="pagar" value="1"/> Pagar a <?php echo $nombre_puesto; ?></td>
                            </tr>
                        </table>                        
                    </div>
                    <div id="kmdiv">
                        <table>
                            <tr>
                                <td> Kilómetro:</td>
                                <td><input type='text' style='max-width: 100px' id='km' name='km' value="<?php echo $km; ?>"/></td>
                            </tr>
                        </table>                        
                    </div>
                    <div id="tiempoE">
                        <table>
                            <tr>
                                <td> Tiempo Real m:</td>
                                <td><input type='text' style='max-width: 100px' id='tiempo_esperaR' name='tiempo_esperaR' value="<?php echo $tiempoER; ?>"/></td>
                                <td> Tiempo Restado m:</td>
                                <td><input type='text' style='max-width: 100px' id='tiempo_esperaM' name='tiempo_esperaM' value="<?php echo $tiempoEM; ?>"/></td>
                            </tr>
                        </table>                        
                    </div>
                    <div id="noBoleto">
                        <table>
                            <tr>
                                <td> No Boleto:</td>
                                <td><input type='text' style='max-width: 100px' id='no_boleto' name='no_boleto' value="<?php echo $noBoleto; ?>"/></td>
                            </tr>
                        </table>                        
                    </div>
                    <div id="reasignacion">
                        <table>
                            <tr>
                                <td>Area:&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                                    &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                                    &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                                    &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</td>
                                <td>
                                    <select id='reasignar' name='reasignar' style='max-width: 300px'>
                                        <option value='0'>Seleccione una opción</option>
                                        <?php
                                        $catalogo1 = new Catalogo();
                                        $query1 = $catalogo1->obtenerLista("SELECT DISTINCT(e.IdEstado) AS IdEstado, e.Nombre FROM c_estado AS e
                                        INNER JOIN k_flujoestado AS kfe ON kfe.IdEstado = e.IdEstado AND (kfe.IdFlujo = 2 OR e.IdEstado = 2) ORDER BY Nombre;");
                                        while ($rs = mysql_fetch_array($query1)) {
                                            $s = "";
                                            if ($idRefaccion[0] != "" && $idRefaccion[0] == $rs['IdEstado']) {
                                                $s = "selected";
                                            }
                                            echo "<option value='" . $rs['IdEstado'] . "' $s>" . $rs['Nombre'] . "</option>";
                                        }
                                        ?>
                                    </select>
                                </td>
                            </tr>
                        </table>                        
                    </div>
                    <div id="asignaProveedor">
                        <table>
                            <tr>
                                <td>Proveedor:
                                    &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                                    &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                                    &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</td>
                                <td>
                                    <select id='proveedor' name='proveedor' style='max-width: 300px'>
                                        <option value='0'>Seleccione una opción</option>
                                        <?php
                                        $catalogo1 = new Catalogo();
                                        $query1 = $catalogo1->obtenerLista("SELECT p.ClaveProveedor,p.NombreComercial
                                                                            FROM k_proveedorzona pz,c_sucursal s,c_proveedor p
                                                                            WHERE pz.IdSucursal=s.ClaveSucursal
                                                                            AND s.ClaveProveedor=p.ClaveProveedor
                                                                            AND pz.ClaveZona=(SELECT z.ClaveZona
                                                                            FROM c_ticket t,c_centrocosto cc,c_zona z
                                                                            WHERE t.ClaveCentroCosto=cc.ClaveCentroCosto
                                                                            AND cc.ClaveZona=z.ClaveZona 
                                                                            AND t.IdTicket='" . $idTicket . "' )");
                                        while ($rs = mysql_fetch_array($query1)) {
                                            $s = "";
                                            if ($idRefaccion[0] != "" && $idRefaccion[0] == $rs['ClaveProveedor']) {
                                                $s = "selected";
                                            }
                                            echo "<option value=" . $rs['ClaveProveedor'] . " " . $s . ">" . $rs['NombreComercial'] . "</option>";
                                        }
                                        ?>
                                    </select>
                                </td>
                            </tr>
                        </table>          
                    </div>

                    <div id="suministro">
                        <?php $contadorComp = 2; ?>
                        <table id="nuevaSuministro">
                            <tr id="filaSuministro1">
                                <td>Suministro:&nbsp;&nbsp;&nbsp;&nbsp;</td>
                                <td>  
                                    <select id='suministro1' name='suministro1' style='width: 600px' class='filtroComponentes'>
                                        <option value='0'>Seleccione un suministro</option>
                                        <?php
                                        $findme = 'Solicitud';
                                        $findme2 = 'resurtido';
                                        $pos0 = strpos($diagnostico, $findme);
                                        $pos1 = strpos($diagnostico, $findme2);
                                        if ($pos0 !== FALSE && $pos1 !== FALSE) {
                                            $catalogo2 = new Catalogo();
                                            $queryAlmacen = $catalogo2->obtenerLista("SELECT c.NoParte,c.Modelo,c.Descripcion,ac.id_almacen 
                                                    FROM c_componente c,k_almacencomponente ac 
                                                    WHERE c.NoParte=ac.NoParte AND c.Activo = 1
                                                    AND ac.id_almacen= (SELECT ml.IdAlmacen FROM k_minialmacenlocalidad ml ,c_ticket t WHERE ml.ClaveCentroCosto=t.ClaveCentroCosto AND t.IdTicket='$idTicket') ORDER BY c.Modelo ASC");
                                            while ($rs = mysql_fetch_array($queryAlmacen)) {
                                                // echo "<option value=\"" . $rs['NoParte'] . "\" >" . $rs['Modelo'] . " / " . $rs['NoParte'] . " / ".$rs['Descripcion']."</option>";
                                                echo "<option value=" . $rs['NoParte'] . " " . $s . ">" . $rs['Modelo'] . " / " . $rs['NoParte'] . " / " . $rs['Descripcion'] . "</option>";
                                            }
                                        } else {
                                            $catalogo2 = new Catalogo();
                                            $query2 = $catalogo2->obtenerLista("SELECT c.NoParte,c.Modelo,c.Descripcion FROM c_componente c 
                                                    LEFT JOIN k_equipocomponentecompatible ec ON ec.NoParteComponente=c.NoParte
                                                    WHERE c.Activo = 1 AND ec.NoParteEquipo IN (SELECT e.NoParte FROM c_pedido p,c_equipo e WHERE p.IdTicket='$idTicket' AND p.Modelo=e.Modelo);");
                                            while ($rs = mysql_fetch_array($query2)) {
                                                $s = "";
                                                if ($idRefaccion[0] != "" && $idRefaccion[0] == $rs['NoParte']) {
                                                    $s = "selected";
                                                }
                                                echo "<option value=" . $rs['NoParte'] . " " . $s . ">" . $rs['Modelo'] . " / " . $rs['NoParte'] . " / " . $rs['Descripcion'] . "(Compatible)</option>";
                                            }
                                            $query3 = $catalogo2->obtenerLista("SELECT c.NoParte,c.Modelo,c.Descripcion 
                                                FROM c_componente c 
                                                WHERE c.NoParte NOT IN (SELECT ec.NoParteComponente FROM k_equipocomponentecompatible ec) 
                                                AND c.IdTipoComponente=2 AND c.Activo = 1");
                                            while ($rs = mysql_fetch_array($query3)) {
                                                $s = "";
                                                if ($idRefaccion[0] != "" && $idRefaccion[0] == $rs['NoParte']) {
                                                    $s = "selected";
                                                }
                                                echo "<option value=" . $rs['NoParte'] . " " . $s . ">" . $rs['Modelo'] . " / " . $rs['NoParte'] . " / " . $rs['Descripcion'] . "</option>";
                                            }
                                        }
                                        ?>
                                    </select>
                                </td>
                                <td> Cantidad:</td>
                                <td><input type='text' style='max-width: 100px' id='cantidadsuministro1' name='cantidadsuministro1' value="<?php echo $cantidadSuministro1; ?>"/></td>
                                <?php if ($externa != "") { ?>
                                <input type="hidden" id="externa" name="externa" value="externa"/>
                                <td><img class="imagenMouse" src="../resources/images/add.png" title="Otra refaccion" onclick='AgregarSuministro("2");' style="float: right; cursor: pointer;" />  </td>                                
                            <?php } else { ?> 
                                <input type="hidden" id="externa" name="externa" value="interna"/>
                                <td><img class="imagenMouse" src="resources/images/add.png" title="Otra refaccion" onclick='AgregarSuministro("1");' style="float: right; cursor: pointer;" />  </td>
                                <td></td>
                            <?php } ?>
                            </tr>
                            <?php
                            $estado1 = $catalogo->obtenerLista("SELECT c.NoParte, c.IdTipoComponente, c.Modelo, c.Descripcion, cc.NoParteEquipo, cc.Soportado
                                                    FROM c_componente AS c
                                                    LEFT JOIN k_equipocomponentecompatible AS cc ON c.NoParte = cc.NoParteComponente
                                                    WHERE c.Activo = 1 AND (cc.NoParteEquipo = (SELECT b.NoParte FROM c_bitacora b WHERE b.NoSerie=(SELECT t.NoSerieEquipo FROM c_ticket t WHERE t.IdTicket='" . $idTicket . "')) OR ISNULL(NoParteEquipo)) AND c.IdTipoComponente=1 ORDER BY c.Modelo ASC");
                            $cf1 = 0;

                            while ($rs = mysql_fetch_array($estado1)) {
                                $modelo[$cf1] = $rs['Modelo'];
                                $parte[$cf1] = $rs['NoParte'];
                                $descripcion[$cf1] = $rs['Descripcion'];
                                $cf1++;
                            }
                            $contadorRef1 = 1;
                            $contadorComp1 = 2;
                            while ($contadorRef1 < count($idRefaccion)) {
                                echo "<tr id='filaSuministro1" . $contadorComp . "'><td>Suministro: &nbsp;&nbsp;&nbsp;&nbsp;</td>
                                    <td><select id='suministro" . $contadorComp . "' name='suministro" . $contadorComp . "' style='width: 600px' class='filtroComponentes'>";
                                echo "<option value='0'>Seleccione una opción</option>";
                                $ct = 0;
                                while ($ct < count($modelo)) {
                                    $s = "";
                                    if ($idRefaccion[$contadorRef1] != "" && $idRefaccion[$contadorRef1] == $parte[$ct]) {
                                        $s = "selected";
                                    }
                                    echo "<option value=" . $parte[$ct] . " " . $s . ">" . $modelo[$ct] . " / " . $parte[$ct] . " / " . $descripcion[$ct] . "</option>";
                                    $ct++;
                                }
                                echo "</select></td><td>Cantidad: </td><td><input type='text' id='cantidad" . $contadorComp . "' name='cantidad" . $contadorComp . "' style='max-width: 100px' value='" . $cantidad[$contadorRef1] . "'/></td>";
                                echo "<td>";
                                ?>
                                <img class="imagenMouse" src="resources/images/Erase.png" title="Otra refaccion" onclick="deleteRow('<?php echo $contadorComp ?>');" style="float: right; cursor: pointer;" />                              
                                <?php
                                echo "</td></tr>";

                                $contadorRef1++;
                                $contadorComp1++;
                            }
                            ?>
                        </table> 
                    </div>
                    <div id="div_contadores" name="div_contadores" style="display:none;">
                        <?php
                        /* $consulta_Tipo = "SELECT t.NoSerieEquipo,SUM(IF(ekfs.IdTipoServicio=1,1,0)) AS color,e.Modelo,lt.ContadorBN AS contadorNegro,
                          lt.ContadorCL AS contadorColor,lt.Fecha AS fechacontador
                          FROM c_ticket t LEFT JOIN c_bitacora b ON t.NoSerieEquipo=b.NoSerie LEFT JOIN c_equipo e ON b.NoParte=e.NoParte
                          LEFT JOIN k_equipocaracteristicaformatoservicio ekfs ON  b.NoParte=ekfs.NoParte
                          LEFT JOIN c_lecturasticket lt ON lt.fk_idticket=t.IdTicket AND t.NoSerieEquipo=lt.ClvEsp_Equipo
                          AND lt.id_lecturaticket=(SELECT MAX(lt2.id_lecturaticket) FROM c_lecturasticket lt2 WHERE lt2.fk_idticket=t.IdTicket)
                          WHERE t.IdTicket=$idTicket;";
                          $query_tipo = $catalogo->obtenerLista($consulta_Tipo); */
                        $lecturas = $lectura_obj->getUltimasLecturasPorSeries(array("'$NoSerieTicket'"));
                        echo " <table style='width:70%'>";
                        $tipo = 0;
                        $noSerie = "";
                        $modelo_e = "";
                        $fechaContadorAnterior = "";
                        $contadorNegroAnterior = "";
                        $contadorColorAnterior = "";
                        $tipo_lectura = 1;
                        if (!empty($lecturas)) {
                            $equipo_caracteristica = new EquipoCaracteristicasFormatoServicio();
                            $noSerie = $NoSerieTicket;
                            $modelo_e = $lecturas[$NoSerieTicket]['modelo'];
                            $fechaContadorAnterior = $lecturas[$NoSerieTicket]['fecha'];
                            $contadorNegroAnterior = $lecturas[$NoSerieTicket]['bn'];
                            $contadorColorAnterior = $lecturas[$NoSerieTicket]['color'];
                            if ($contadorNegroAnterior == "" && $contadorColorAnterior == "") {
                                $lecturaTicket->setNoSerie($noSerie);
                                $lecturaTicket->getLecturaBYNoSerie();
                                $fechaContadorAnterior = $lecturaTicket->getFechaA();
                                $contadorNegroAnterior = $lecturaTicket->getContadorBNA();
                                $contadorColorAnterior = $lecturaTicket->getContadorColorA();
                                $tipo_lectura = 0;
                            }
                            echo " <tr>"
                            . "<td>Contador B/N anterior</td><td><input type='text' id='txt_negro_anterior' name='txt_negro_anterior' value='" . $contadorNegroAnterior . "' readonly style='width:170px'/></td>";
                            if ($equipo_caracteristica->isColor($lecturas[$NoSerieTicket]['noParte'])) {
                                echo "<td>Contador color anterior</td><td><input type='text' id='txt_color_anterior' name='txt_color_anterior' value='" . $contadorColorAnterior . "' readonly style='width:170px'/></td>";
                            }
                            echo "<td>Fecha anterior</td><td><input type='text' id='txt_fechaA' name='txt_fechaA' value='" . $fechaContadorAnterior . "' readonly style='width:170px'/></td>"
                            . "</tr>";

                            echo " <tr>"
                            . "<td>Contador B/N nuevo</td><td><input type='text' id='txt_negro_nuevo' name='txt_negro_nuevo' style='width:170px'/></td>";
                            if ($equipo_caracteristica->isColor($lecturas[$NoSerieTicket]['noParte'])) {
                                echo "<td>Contador color nuevo</td><td><input type='text' id='txt_color_nuevo' name='txt_color_nuevo' style='width:170px'/></td>";
                            }
                            echo "<td></td>"
                            . "</tr>";
                            $tipo = 1;
                            echo "<tr><td>Comentario de lecturas</td><td colspan='5'><input type='text' id='txt_comentario' name='txt_comentario' style='width:100%'/></td></tr>";
                        }
                        echo "</table>";
                        ?>
                        <input type="hidden" id="txt_tipo_equipo" name="txt_tipo_equipo" value="<?php echo $tipo; ?>"/>
                        <input type="hidden" id="txt_serie" name="txt_serie" value="<?php echo $noSerie; ?>"/>
                        <input type="hidden" id="txt_modelo" name="txt_modelo" value="<?php echo $modelo_e; ?>"/>
                        <input type="hidden" id="txt_id_ticket" name="txt_id_ticket" value="<?php echo $idTicket; ?>"/>
                        <input type="hidden" id="txt_tipo_lectura" name="txt_tipo_lectura" value="<?php echo $tipo_lectura; ?>"/>
                    </div>                                        
                    <div id="refacciones" name="refacciones" style="display:none;">   
                        <br/>
                        <a href='#' title='Ver refacciones solicitadas' onclick='lanzarHistoricoRefacciones("<?php echo $NoSerieTicket; ?>");
                                return false;'>
                            <img src='resources/images/Textpreview.png' style='width: 20px; height: 20px;' title='Ver historial refacciones'/>
                        </a>
                        <table id="nuevaRefaccion" name="nuevaRefaccion" style="width:70%">
                            <tr id="filaRefaccion_1">                      
                            <br/>                                                            
                            <?php
                            $modelo1 = "";
                            $descripcion1 = "";
                            $style = "";
                            if (!$refacciones_visibles[0]) {
                                $style = "display:none;";
                                echo "<input type='hidden' id='poner_cero1' name='poner_cero1' value='1'/>"; //SE va a poner en la nota validad cero, para que no vualve a aparecer en el grid de solicitados
                            }
                            ?>
                            <td><span style='<?php echo $style; ?>'>Refacción:&nbsp;&nbsp;&nbsp;&nbsp;</span></td>
                            <td>
                                <?php
                                /* Inicializamos la clase */
                                if (isset($idRefaccion) && !empty($idRefaccion)) {
                                    $catalogo = new Catalogo();
                                    $query = $catalogo->obtenerLista("SELECT * FROM c_componente c WHERE c.NoParte='" . $idRefaccion[0] . "' AND c.Activo = 1");
                                    $queryPrecio = $catalogo->obtenerLista("SELECT ko.PrecioUnitario,c.Rendimiento FROM k_orden_compra AS ko LEFT JOIN k_nota_refaccion AS kn ON kn.NoParteComponente  = ko.NoParteComponente LEFT JOIN c_componente as c on c.NoParte = kn.NoParteComponente  WHERE kn.NoParteComponente  = '" . $idRefaccion[0]. "' ORDER BY ko.FechaCreacion DESC LIMIT 1 ");

                                    while ($rs = mysql_fetch_array($queryPrecio)) {
                                        $precioRefaccion = $rs['PrecioUnitario'];
                                        $rendimiento = $rs['Rendimiento'];
                                    }
                                    if ($precioRefaccion == "") {
                                        $precioRefaccion = "Sin O.C." ;
                                        
                                    }
                                    if ($rendimiento =="") {
                                        $rendimiento = "Sin Capturar";
                                    }
                                    while ($rs = mysql_fetch_array($query)) {
                                        array_push($refacciones_solicitadas, $rs['NoParte']);
                                        $modelo1 = $rs["Modelo"] . " / ";
                                        $descripcion1 = " / " . $rs['Descripcion'];
                                    }
                                } else {
                                    $idRefaccion[0] = "";
                                }

                                ?>

                            <input id="refaccion1" name="refaccion1" value="<?php echo $modelo1 . $idRefaccion[0] . $descripcion1 ?>" class="refaccion" style="width: 250px; <?php echo $style; ?>"/>
                            </td>
                            <td><span style='<?php echo $style; ?>'>Cantidad:</span></td>
                            <td><input type='text' style='max-width: 100px; <?php echo $style; ?>' id='cantidad1' name='cantidad1' value="<?php echo $cantidad1; ?>"/></td>

                            <?php if ($idRefaccion[0] != "") { ?>

                            <td><span style='<?php echo $style; ?>'>Precio:</span></td>
                            <td><input type='text' style='max-width: 100px; <?php echo $style; ?>' id='precio' name='precio' value="<?php echo $precioRefaccion; ?>" readonly /></td>
                            <td><span style='<?php echo $style; ?>'>Rendimiento:</span></td>
                            <td><input type='text' style='max-width: 100px; <?php echo $style; ?>' id='rendimiento' name='rendimiento' value="<?php echo $rendimiento; ?>" readonly/></td>

                            <?php 

                            if ($EdoComponente == "CN") { ?>
                            
                            <td><select id="estadoComponente" name="estadoComponente" type='text'>
                                    <option value='1'>Nuevo</option>;
                                    <option value='2'>Usado</option>;
                            </select></td>
                            <?php } else { ?>
                                <td><select id="estadoComponente" name="estadoComponente" type='text'>
                                    <option value='3'>Usado</option>;
                                    <option value='4'>Nuevo</option>;
                            </select></td>
                                <?php } }

                                 if ($idRefaccion[0] != "") {
                                     if ($externa != "") { ?>
                                    <input type="hidden" id="externa" name="externa" value="externa"/>
                                    <td><img class="imagenMouse" src="../resources/images/add.png" title="Otra refaccion" onclick='otraRefaccion("2");' style="float: right; cursor: pointer;" />  </td>                                
                                <?php } else { ?> 
                                    <input type="hidden" id="externa" name="externa" value="interna"/>
                                    <td><img class="imagenMouse" src="resources/images/add.png" title="Otra refaccion" onclick="otraRefaccion('1');" style="float: right; cursor: pointer;" />  </td>
                                    <td></td>
                                <?php } } else{
                                     if ($externa != "") { ?>
                                    <input type="hidden" id="externa" name="externa" value="externa"/>
                                    <td><img class="imagenMouse" src="../resources/images/add.png" title="Otra refaccion" onclick='otraRefaccionCaptura("2");' style="float: right; cursor: pointer;" />  </td>                                
                                <?php } else { ?> 
                                    <input type="hidden" id="externa" name="externa" value="interna"/>
                                    <td><img class="imagenMouse" src="resources/images/add.png" title="Otra refaccion" onclick="otraRefaccionCaptura('1');" style="float: right; cursor: pointer;" />  </td>
                                    <td></td>

                                <?php } } ?>


                            </tr>
                            <?php
                            $estado = $catalogo->obtenerLista("SELECT c.NoParte, c.IdTipoComponente, c.Modelo, c.Descripcion, cc.NoParteEquipo, cc.Soportado
                                FROM c_componente AS c
                                LEFT JOIN k_equipocomponentecompatible AS cc ON c.NoParte = cc.NoParteComponente
                                WHERE c.Activo = 1 AND (cc.NoParteEquipo = (SELECT b.NoParte FROM c_bitacora b WHERE b.NoSerie=(SELECT t.NoSerieEquipo FROM c_ticket t WHERE t.IdTicket='" . $idTicket . "')) OR ISNULL(NoParteEquipo)) AND c.IdTipoComponente=1 ORDER BY c.Modelo ASC");
                            $cf = 0;

                            while ($rs = mysql_fetch_array($estado)) {
                                $parte[$cf] = $rs['NoParte'];
                                $descripcion[$cf] = $rs['Descripcion'];
                                $cf++;
                            }
                            $contadorRef = 1;
                            $contadorComp = 2;
                            while ($contadorRef < count($idRefaccion)) {
                                $style = "";
                                if (!$refacciones_visibles[$contadorRef]) {
                                    $style = "display:none;";
                                    echo "<input type='hidden' id='poner_cero$contadorComp' name='poner_cero$contadorComp' value='1'/>"; //SE va a poner en la nota validad cero, para que no vualve a aparecer en el grid de solicitados
                                }
                                $modelo1 = "";
                                $descripcion = "";
                                /* Inicializamos la clase */
                                $catalogo = new Catalogo();
                                $query = $catalogo->obtenerLista("SELECT * FROM c_componente c 
                                    WHERE c.NoParte='" . $idRefaccion[$contadorRef] . "' AND c.Activo = 1");
                                while ($rs = mysql_fetch_array($query)) {
                                    array_push($refacciones_solicitadas, $rs['NoParte']);
                                    $modelo1 = $rs["Modelo"] . " / ";
                                    $descripcion = " / " . $rs["Descripcion"];
                                }
                                $queryPrecio = $catalogo->obtenerLista("SELECT ko.PrecioUnitario,kn.EstadoComponente,c.Rendimiento FROM k_orden_compra AS ko LEFT JOIN k_nota_refaccion AS kn ON kn.NoParteComponente  = ko.NoParteComponente LEFT JOIN c_componente as c on c.NoParte = kn.NoParteComponente  WHERE kn.NoParteComponente  = '" . $idRefaccion[$contadorRef]. "'  ");

                                    while ($rs = mysql_fetch_array($queryPrecio)) {
                                        array_push($precioArray, $rs['PrecioUnitario']);
                                        array_push($rendimientoArray, $rs['Rendimiento']);
                                        $EstadoComponente1 = $rs['EstadoComponente'];
                                        
                                    }
                                    if ($precioArray[$contadorRef] == "") {
                                        $precioArray[$contadorRef] = "Sin O.C." ;
                                        
                                    }
                                    if ($rendimientoArray[$contadorRef] =="") {
                                        $rendimientoArray[$contadorRef] = "Sin Capturar";
                                    }

                                    //echo $EstadoComponente1;
                                    //print_r($precioArray[0]);
                                    //print_r($rendimientoArray[$contadorRef]);

                                    
                                    $countPrecio = 0;

                                echo "<tr id='filaRefaccion_" . $contadorComp . "'>
                                    <td><span style='$style'>Refacción : &nbsp;&nbsp;&nbsp;&nbsp;</span></td>
                                    <td><input type='text' id='refaccion" . $contadorComp . "' name='refaccion" . $contadorComp . "' value='" . $modelo1 . $idRefaccion[$contadorRef] . $descripcion . "' class='refaccion' style='width: 250px; $style'/>";
                                echo "</td><td><span style='$style'>Cantidad: </span></td>
                                           <td>
                                            <input type='text' id='cantidad" . $contadorComp . "' name='cantidad" . $contadorComp . "' style='max-width: 100px; $style' value='" . $cantidad[$contadorRef] . "'/>
                                           </td>";
                                
                                echo "</td><td><span style='$style'>Precio: </span></td>
                                           <td>
                                            <input type='text' id='precio" . $contadorComp . "' name='precio" . $contadorComp . "' style='max-width: 100px; $style' value='" . $precioArray[$contadorRef] . " ' readonly/>
                                           </td>";
                                
                                echo "</td><td><span style='$style'>Rendimiento: </span></td>
                                           <td>
                                            <input type='text' id='rendimiento" . $contadorComp . "' name='rendimiento" . $contadorComp . "' style='max-width: 100px; $style' value='" . $rendimientoArray[$contadorRef] . " 'readonly/>
                                           </td>";

                                if ($EstadoComponente1 == "CN") {
                                    echo "<td><select id='estadoComponente' name='estadoComponente' type='text'>
                                                <option value='1'>Nuevo</option>
                                                <option value='2'>Usado</option>
                                                </select></td>";
                                        } else {
                                            echo "<td><select id='estadoComponente' name='estadoComponente' type='text'>
                                                <option value='3'>Usado</option>
                                                <option value='4'>Nuevo</option>
                                                </select></td>";
                                             }

                                echo "<td>";
                                    
                                ?>


                                <img class="imagenMouse" src="resources/images/Erase.png" title="Otra refaccion" onclick="deleteRow('<?php echo $contadorComp ?>');" style="float: right; cursor: pointer; <?php echo $style; ?>" />                              
                                <?php
                                echo "</td></tr>";

                                $contadorRef++;
                                $contadorComp++;
                                $countPrecio++;
                            } 
                            ?>
                        </table>                         
                        <?php if ($idRefaccion[0] != "") { ?>
                        <td><input type="checkbox" id="cotizacion" name="cotizacion" <?php echo $cotizacion; ?>/>Viene de Cotizacion</td>
                        <?php } ?>
                        
                        

                    </div>          
                    <?php if($puede_agregar_nota) {?>
                        <input type="submit" id="botonGuardar" name="botonGuardar"  class="boton" value="<?php echo $boton ?>"/>
                    <?php } ?>
                    <?php if ($areaTickte == "") { ?>
                        <input type="submit" class="boton" value="Cancelar" onclick="cambiarContenidos('<?php echo $pagina_listaRegresar; ?>');
                                    return false;"/>
                           <?php } else { ?>
                        <input type="submit" class="boton" value="Cancelar" onclick="cancelarNota('<?php echo $pagina_lista; ?>', '<?php echo $idTicket; ?>', '<?php echo $areaTickte; ?>');
                                    return false;"/>
                           <?php } ?>
                    <input type="hidden" name="idTicket" id="idTicket" value="<?php echo $idTicket ?>"/>                     
                    <input type="hidden" name="nota" id="nota" value=""/> 
                    <input type="hidden" name="accion" id="accion" value="<?php echo $accion ?>"/> 
                    <input type="hidden" name="paginaLista" id="paginaLista" value="<?php echo $pagina_lista ?>"/>                    
                    <input type='hidden' name='tamano' id='tamano' value='<?php echo $contadorComp ?>'/>
                    <input type='hidden' name='usuario' id='usuario' value='<?php echo $usuarioSolicitud ?>'/>
                    <input type='hidden' name='liga' id='liga' value='<?php echo $_SESSION['liga'] ?>'/>
                    <input type='hidden' name='area' id='area' value='<?php echo $areaTickte ?>'/>
                    <input type='hidden' name='idNotaAnterior' id='idNotaAnterior' value='<?php echo $id; ?>'/>
                    <?php
                    $idNotaValidada = 0;

                    if (isset($_POST['validada']) && $_POST['validada'] == "1") {
                        $partes_aux = "";
                        foreach ($refacciones_solicitadas as $value) {
                            $partes_aux .= "'$value',";
                        }
                        if ($partes_aux != "") {
                            $partes_aux = substr($partes_aux, 0, strlen($partes_aux) - 1);
                        }
                        $consulta = "SELECT nt2.IdNotaTicket FROM c_notaticket AS nt
                                LEFT JOIN c_notaticket AS nt2 ON nt2.IdTicket = nt.IdTicket
                                LEFT JOIN k_nota_refaccion AS nr ON nr.IdNotaTicket = nt2.IdNotaTicket
                                WHERE nt.IdNotaTicket = $id AND nt2.IdEstatusAtencion = 24 AND nr.NoParteComponente IN($partes_aux)
                                GROUP BY nt2.IdNotaTicket;";

                        $result = $catalogo->obtenerLista($consulta);
                        while ($rs = mysql_fetch_array($result)) {
                            $idNotaValidada = $rs['IdNotaTicket'];
                        }
                        $ya_validada = 1;
                    } else {
                        $ya_validada = 0;
                    }
                    ?>
                    <input type='hidden' name='validada' id='validada' value='<?php echo $ya_validada; ?>'/>
                    <input type='hidden' name='refacciones_solicitadas' id='refacciones_solicitadas' value='<?php echo implode(",", $refacciones_solicitadas); ?>'/>
                    <input type='hidden' name='nota_validada' id='nota_validada' value='<?php echo $idNotaValidada; ?>'/>
                </form>
            </fieldset>
        </div>
        <div id="MesajeTicekt"></div>
        <div id="dialog"></div>
    </body>
</html>