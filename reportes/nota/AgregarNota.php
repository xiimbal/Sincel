<?php
session_start();


include_once("../WEB-INF/Classes/AgregarNota.class.php");
include_once("../WEB-INF/Classes/Catalogo.class.php");
include_once("../WEB-INF/Classes/AlmacenConmponente.class.php");
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
$fechaHora = "";
$hora = "";
$id = "";
$idRefaccion = array();
$cantidad = array();
$cantidad1 = "";
$array = "";
$contador = 0;
$cancelar = "";
$aceptar = "";
$usuario = "";
$validar = "style='visibility:hidden'";
$tamano = 0;
$boton = "Guardar";
$cantidadSuministro1 = "";


$modelo = "";
$parte = "";
$descripcion = "";
//if (isset($_GET['idTicket1']) && $_GET['idTicket1'] != "")
//    $idTicket = $_GET['idTicket'];
//else
//    $idTicket = "";
$show = "checked='checked'";
$accion = "";

$interna = "";
$externa = "";
$area = "";
$descripcionTicket = "";
if (isset($_GET['idTicket1'])) {
    $interna = $_GET['idTicket1'];
    $idTicket = $interna;
    $pagina_lista = $_SESSION['liga'] . "/Operacion/MesaServicio/ConsultaDetalleTicketToner.aspx?Operacion=&IdTicket=" . $interna . "&Vista=Detalle&uguid=" . $_SESSION['user'];
} else if (isset($_GET['idTicket'])) {
    $externa = $_GET['idTicket'];
    $idTicket = $externa;
    $pagina_lista = $_SESSION['liga'] . "/Operacion/MesaServicio/ConsultaDetalleTicketToner.aspx?Operacion=&IdTicket=" . $externa . "&Vista=Detalle&uguid=" . $_SESSION['user'];
}
//$area = $_GET['area'];
$diagnostico = $_POST['id'];
$usuarioSolicitud = "";


//if($tipo=='nuevo'){
//    $cancelar="";
//    $validar="hidden";
//}
//else if($tipo=="editar"){
//    $cancelar="";
//}
$lista = array();
$lis = array();
?>
<!DOCTYPE html>
<html lang="es">
    <head>
        <?php
        $consultaComponentes = "SELECT * FROM c_componente c WHERE c.IdTipoComponente='1'";
        if ($consultaComponentes != "") {
            ?>
            <script>
                var arreglo = new Array();
                $(function() {

                    var otra = "otra";
                    var availableTags = [
    <?php
    $obj = new AlmacenComponente();
    $obj->serchNoSerie($consultaComponentes);
    $lista = $obj->getArreglo_php();
    $lis = $obj->getArreglo_php2();
    for ($x = 0; $x < count($lista); $x++) {
        echo "'" . $lista[$x] . " / " . $lis[$x] . "',";
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
            $(document).ready(function() {
                $('.boton').button().css('margin-top', '20px');
            });
        </script>
        <script>
            $(function() {
                $('#fecha').datepicker({dateFormat: 'yy-mm-dd'});
                $('#hora').mask("99:99:99");

            });


//            if ($("#fecha").val() === "") {
//                $("#fecha").val(getFechaF(new Date()));
//            }
            if ($("#hora").val() === "") {
                $("#hora").val(getHoraF(new Date()));
            }
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
                list($fechaHora, $hora) = explode(' ', $obj->getFechaHora());
                //$fechaHora = $obj->getFechaHora();
                if ($obj->getActivo() == "0") {
                    $activo = "";
                }
                $refaccion = $obj->getRefaccionesById($id);
                while ($rs = mysql_fetch_array($refaccion)) {
                    $idRefaccion[$contador] = $rs['NoParteComponente'];
                    $cantidad[$contador] = $rs['Cantidad'];

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
                // echo count($idRefaccion);
            }
            if ($fechaHora == "")
                $fechaActual = $fecha;
            else
                $fechaActual = $fechaHora
                ?>
            <fieldset>
                <legend>Nota de diagnóstico y atención</legend>
                <form id="formAgregarNota" name="formAgregarNota" action="/" method="POST">
                    <table>
                        <tr>
                            <td>Fecha: </td>
                            <td><input type='text' id='fecha' name='fecha' value="<?php echo $fechaActual ?>"/></td>   
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
                                                                            AND fe.IdFlujo=f.IdFlujo
                                                                            AND fe.IdFlujo=6 ORDER BY nombreEstado ASC");
                                    echo "<option value='0' >Selecciona una opción</option>";
                                    while ($rs = mysql_fetch_array($query)) {
                                        $s = "";
                                        if ($idestatus != "" && $idestatus == $rs['IdEstado']) {
                                            $s = "selected";
                                        }
                                        echo "<option value=" . $rs['IdEstado'] . " " . $s . ">" . $rs['nombreEstado'] . "</option>";
                                    }
                                    ?>
                                </select>
                            </td>
                            <td><input type="checkbox" name="activo" id="activo" <?php echo $activo; ?>/>Activo</td>
                            <td><input type="checkbox" name="show" id="show" <?php echo $show; ?>/>Mostrar a cliente</td>
                        </tr> 
                    </table> 
                    <!--                    <div class="ui-widget">
                                            <label for="tags">Tags: </label>
                                            <input id="tags">
                                        </div>-->
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
                                        $query1 = $catalogo1->obtenerLista("SELECT * FROM c_area ORDER BY Descripcion");
                                        while ($rs = mysql_fetch_array($query1)) {
                                            $s = "";
                                            if ($idRefaccion[0] != "" && $idRefaccion[0] == $rs['IdArea']) {
                                                $s = "selected";
                                            }
                                            echo "<option value=" . $rs['IdArea'] . " " . $s . ">" . $rs['Descripcion'] . "</option>";
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
                                            $queryAlmacen = $catalogo2->obtenerLista("SELECT c.NoParte,c.Modelo,c.Descripcion,ac.id_almacen FROM c_componente c,k_almacencomponente ac 
                                                                                    WHERE c.NoParte=ac.NoParte 
                                                                                    AND ac.id_almacen= (SELECT ml.IdAlmacen FROM k_minialmacenlocalidad ml ,c_ticket t WHERE ml.ClaveCentroCosto=t.ClaveCentroCosto AND t.IdTicket='$idTicket') ORDER BY c.Modelo ASC");
                                            while ($rs = mysql_fetch_array($queryAlmacen)) {
                                                // echo "<option value=\"" . $rs['NoParte'] . "\" >" . $rs['Modelo'] . " / " . $rs['NoParte'] . " / ".$rs['Descripcion']."</option>";
                                                echo "<option value=" . $rs['NoParte'] . " " . $s . ">" . $rs['Modelo'] . " / " . $rs['NoParte'] . " / " . $rs['Descripcion'] . "</option>";
                                            }
                                        } else {
                                            $catalogo2 = new Catalogo();
                                            $query2 = $catalogo2->obtenerLista("SELECT c.NoParte,c.Modelo,c.Descripcion FROM c_componente c LEFT JOIN k_equipocomponentecompatible ec ON ec.NoParteComponente=c.NoParte
                                                                                WHERE ec.NoParteEquipo IN (SELECT e.NoParte FROM c_pedido p,c_equipo e WHERE p.IdTicket='$idTicket' AND p.Modelo=e.Modelo);");
                                            while ($rs = mysql_fetch_array($query2)) {
                                                $s = "";
                                                if ($idRefaccion[0] != "" && $idRefaccion[0] == $rs['NoParte']) {
                                                    $s = "selected";
                                                }
                                                echo "<option value=" . $rs['NoParte'] . " " . $s . ">" . $rs['Modelo'] . " / " . $rs['NoParte'] . " / " . $rs['Descripcion'] . "(Compatible)</option>";
                                            }
                                            $query3 = $catalogo2->obtenerLista("SELECT c.NoParte,c.Modelo,c.Descripcion FROM c_componente c WHERE c.NoParte NOT IN (SELECT ec.NoParteComponente FROM k_equipocomponentecompatible ec) AND c.IdTipoComponente=2");
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
                                                                            WHERE (cc.NoParteEquipo = (SELECT b.NoParte FROM c_bitacora b WHERE b.NoSerie=(SELECT t.NoSerieEquipo FROM c_ticket t WHERE t.IdTicket='" . $idTicket . "')) OR ISNULL(NoParteEquipo)) AND c.IdTipoComponente=1 ORDER BY c.Modelo ASC");
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
//                                echo "<tr><td>" . $idRefaccion[$contadorRef] . "</td></tr>";
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
                    <div id="refacciones" name="refacciones" style="display:none;">
                        <table id="nuevaRefaccion">
                            <tr id="filaRefaccion_1">                      
                            <br/>                            
                            <td>Refacción:&nbsp;&nbsp;&nbsp;&nbsp;</td>
                            <td>
                                <?php
                                $modelo1 = "";
                                /* Inicializamos la clase */
                                if (isset($idRefaccion) && !empty($idRefaccion)) {
                                    $catalogo = new Catalogo();
                                    $query = $catalogo->obtenerLista("SELECT * FROM c_componente c WHERE c.NoParte='" . $idRefaccion[0] . "'");
                                    while ($rs = mysql_fetch_array($query)) {
                                        $modelo1 = $rs["Modelo"] . " / ";
                                    }
                                } else
                                    $idRefaccion[0] = "";
                                ?>
                                <input id="refaccion1" name="refaccion1" value="<?php echo $modelo1 . $idRefaccion[0] ?>" class="refaccion" style="width: 250px"/>
                            </td>
                            <td>Cantidad:</td>
                            <td><input type='text' style='max-width: 100px' id='cantidad1' name='cantidad1' value="<?php echo $cantidad1; ?>"/></td>
                            <?php if ($externa != "") { ?>
                                <input type="hidden" id="externa" name="externa" value="externa"/>
                                <td><img class="imagenMouse" src="../resources/images/add.png" title="Otra refaccion" onclick='otraRefaccion("2");' style="float: right; cursor: pointer;" />  </td>                                
                            <?php } else { ?> 
                                <input type="hidden" id="externa" name="externa" value="interna"/>
                                <td><img class="imagenMouse" src="resources/images/add.png" title="Otra refaccion" onclick="otraRefaccion('1');" style="float: right; cursor: pointer;" />  </td>
                                <td></td>
                            <?php } ?>
                            </tr>
                            <?php
                            $estado = $catalogo->obtenerLista("SELECT c.NoParte, c.IdTipoComponente, c.Modelo, c.Descripcion, cc.NoParteEquipo, cc.Soportado
                                                                            FROM c_componente AS c
                                                                            LEFT JOIN k_equipocomponentecompatible AS cc ON c.NoParte = cc.NoParteComponente
                                                                            WHERE (cc.NoParteEquipo = (SELECT b.NoParte FROM c_bitacora b WHERE b.NoSerie=(SELECT t.NoSerieEquipo FROM c_ticket t WHERE t.IdTicket='" . $idTicket . "')) OR ISNULL(NoParteEquipo)) AND c.IdTipoComponente=1 ORDER BY c.Modelo ASC");
                            $cf = 0;

                            while ($rs = mysql_fetch_array($estado)) {
                                $modelo[$cf] = $rs['Modelo'];
                                $parte[$cf] = $rs['NoParte'];
                                $descripcion[$cf] = $rs['Descripcion'];
                                $cf++;
                            }
                            $contadorRef = 1;
                            $contadorComp = 2;
                            while ($contadorRef < count($idRefaccion)) {

                                $modelo1 = "";
                                /* Inicializamos la clase */
                                $catalogo = new Catalogo();
                                $query = $catalogo->obtenerLista("SELECT * FROM c_componente c WHERE c.NoParte='" . $idRefaccion[$contadorRef] . "'");
                                while ($rs = mysql_fetch_array($query)) {
                                    $modelo1 = $rs["Modelo"] . " / ";
                                }

//                                echo "<tr><td>" . $idRefaccion[$contadorRef] . "</td></tr>";
                                echo "<tr id='filaRefaccion_" . $contadorComp . "'><td>Refección: &nbsp;&nbsp;&nbsp;&nbsp;</td>
                                    <td><input type='text' id='refaccion" . $contadorComp . "' name='refaccion" . $contadorComp . "' value='" . $modelo1 . $idRefaccion[$contadorRef] . "' class='refaccion' style='width: 250px'/>";
                                echo "</td><td>Cantidad: </td><td><input type='text' id='cantidad" . $contadorComp . "' name='cantidad" . $contadorComp . "' style='max-width: 100px' value='" . $cantidad[$contadorRef] . "'/></td>";
                                echo "<td>";
                                ?>
                                <img class="imagenMouse" src="resources/images/Erase.png" title="Otra refaccion" onclick="deleteRow('<?php echo $contadorComp ?>');" style="float: right; cursor: pointer;" />                              
                                <?php
                                echo "</td></tr>";

                                $contadorRef++;
                                $contadorComp++;
                            }
                            ?>
                        </table> 
                    </div>

                    <input type="submit" id="botonGuardar" name="botonGuardar"  class="boton" value="<?php echo $boton ?>"/>
                    <input type="submit" class="boton" value="Cancelar" onclick="cambiarContenidos('<?php echo $pagina_listaRegresar; ?>');
                            return false;"/>
                    <input type="hidden" name="idTicket" id="idTicket" value="<?php echo $idTicket ?>"/> 
                    <input type="hidden" name="nota" id="nota" value=""/> 
                    <input type="hidden" name="accion" id="accion" value="<?php echo $accion ?>"/> 
                    <input type="hidden" name="paginaLista" id="paginaLista" value="<?php echo $pagina_lista ?>"/>                    
                    <input type='hidden' name='tamano' id='tamano' value='<?php echo $contadorComp ?>'/>
                    <input type='hidden' name='usuario' id='usuario' value='<?php echo $usuarioSolicitud ?>'/>
                    <input type='hidden' name='liga' id='liga' value='<?php echo $_SESSION['liga'] ?>'/>


                </form>
            </fieldset>
        </div>

    </body>
</html>
