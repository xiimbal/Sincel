<?php
session_start();

if (!isset($_SESSION['user']) || $_SESSION['user'] == "" || !isset($_GET['id'])) {
    header("Location: ../index.php");
}

include_once("../WEB-INF/Classes/Catalogo.class.php");
include_once("../WEB-INF/Classes/PermisosSubMenu.class.php");
include_once("../WEB-INF/Classes/Lectura.class.php");
include_once("../WEB-INF/Classes/Parametros.class.php");
include_once("../WEB-INF/Classes/Almacen.class.php");
include_once("../WEB-INF/Classes/Equipo.class.php");

$permisos_grid = new PermisosSubMenu();
$lectura = new Lectura();
$parametros = new Parametros();
$equipo = new Equipo();
$catalogo = new Catalogo();

$pedir_contador = 1;
if ($parametros->getRegistroById("13")) {//Pedir contadores
    if ($parametros->getValor() == "0") {
        $pedir_contador = "0";
    }
}

$permiso_nosurtir = true;
if ($parametros->getRegistroById("23")) {//poder marcar como no surtir
    if ($parametros->getValor() == "0") {
        $permiso_nosurtir = false;
        ;
    }
}

$idSolicitud = $_GET['id'];
if($permisos_grid->tienePermisoEspecial($_SESSION['idUsuario'], 35)){
$cabeceras = array("", "Modelo", "Estado equipo", "Localidad", "Almacén", "No Serie", "");
}else{
$cabeceras = array("", "Modelo", "Estado equipo", "Localidad", "Almacén", "No Serie");    
}

$query = $catalogo->obtenerLista("SELECT c_solicitud.comentario, c_cliente.NombreRazonSocial, c_solicitud.id_almacen, c_solicitud.ClaveCliente 
    FROM c_solicitud INNER JOIN c_cliente ON c_cliente.ClaveCliente = c_solicitud.ClaveCliente WHERE id_solicitud = " . $idSolicitud . ";");
$comentario = "";
$cliente = "";
$ClaveCliente = "";
$IdAlmacenDestino = "";
while ($rs = mysql_fetch_array($query)) {
    $comentario = $rs['comentario'];
    $cliente = $rs['NombreRazonSocial'];
    $ClaveCliente = $rs['ClaveCliente'];
    if (isset($rs['id_almacen']) && $rs['id_almacen'] != "") {
        $IdAlmacenDestino = $rs['id_almacen'];
        $almacen_objeto = new Almacen();
        if ($almacen_objeto->getRegistroById($IdAlmacenDestino)) {
            echo "<br/><div style='margin-left: 20px;'><b>Almacén destino: " . $almacen_objeto->getNombre() . "<b/></div>";
        }
    }
}

$consulta = "SELECT ks.*, e.Modelo AS modelo_equipo, e.NoParte AS parte, c.ClaveCentroCosto ,c.Nombre, cli.NombreRazonSocial, ti.Nombre AS estadoEquipo, ti.idTipo
FROM `k_solicitud` AS ks
INNER JOIN c_equipo AS e ON ks.id_solicitud = " . $idSolicitud . " AND ks.tipo = 0 AND e.NoParte = ks.Modelo
LEFT JOIN c_centrocosto AS c ON c.ClaveCentroCosto = ks.ClaveCentroCosto
LEFT JOIN c_cliente AS cli ON cli.ClaveCliente = c.ClaveCliente 
LEFT JOIN c_tipoinventario AS ti ON ti.idTipo = ks.TipoInventario
ORDER BY ks.ClaveCentroCosto;";

$query = $catalogo->obtenerLista($consulta);

$consulta = "SELECT b.id_bitacora,b.NoParte,TRIM(b.NoSerie) AS NoSerie,b.ClaveCentroCosto,
(SELECT CASE WHEN ISNULL(a.nombre_almacen) THEN 'Asignado' ELSE a.nombre_almacen END) almacen, 
(SELECT CASE WHEN ISNULL(a.nombre_almacen) THEN NULL ELSE a.id_almacen END) id_almacen, 
msj.IdEnvio, 
(SELECT CASE WHEN !ISNULL(msj.IdMensajeria) THEN CONCAT(msj1.Nombre,' ',msj.NoGuia) ELSE CONCAT(CONCAT(v.Placas,' - ',v.Modelo),', Conductor: ',CONCAT(c.Nombre,' ',c.ApellidoPaterno)) END) AS mensajeria
FROM `c_bitacora` AS b
LEFT JOIN k_almacenequipo AS kae ON kae.NoSerie = b.NoSerie
LEFT JOIN c_almacen AS a ON a.id_almacen = kae.id_almacen
LEFT JOIN k_enviosmensajeria AS msj ON msj.NoSerie = b.NoSerie AND msj.IdSolicitud = b.id_solicitud
LEFT JOIN c_vehiculo AS v ON v.IdVehiculo = msj.IdVehiculo
LEFT JOIN c_conductor AS c ON c.IdConductor = msj.IdConductor
LEFT JOIN c_mensajeria AS msj1 ON msj1.IdMensajeria = msj.IdMensajeria
WHERE b.id_solicitud = $idSolicitud ORDER BY NoParte, ClaveCentroCosto;";
$query_bitacoras = $catalogo->obtenerLista($consulta);
//echo $consulta;

$isThereBitacora = "false";
$datos_bitacora = array();
$id_bitacora = array();
$almacen_bitacora = array();
$id_almacen_bitacora = array();
$enviado = array();
$no_parte_anterior = "";
$cc_anterior = "";
$contador = 0;
$series_capturadas = false; //Si hay series capturadas.

while ($rs = mysql_fetch_array($query_bitacoras)) {
    $no_parte = str_replace(" ", "", $rs['NoParte']);
    if ($no_parte_anterior != $no_parte || $cc_anterior != $rs['ClaveCentroCosto']) {
        $contador = 0;
    }
    $isThereBitacora = "true";

    $datos_bitacora[$no_parte . '_' . $rs['ClaveCentroCosto'] . '_' . $contador] = $rs['NoSerie'];
    if ($rs['almacen'] != "Asignado") {/* Todavia está en alamacen */
        $almacen_bitacora[$no_parte . '_' . $rs['ClaveCentroCosto'] . '_' . $contador] = $rs['almacen'];
        $id_almacen_bitacora[$no_parte . '_' . $rs['ClaveCentroCosto'] . '_' . $contador] = $rs['id_almacen'];
    } else {
        $almacen_bitacora[$no_parte . '_' . $rs['ClaveCentroCosto'] . '_' . $contador] = "Enviado: " . $rs['mensajeria'];
        $id_almacen_bitacora[$no_parte . '_' . $rs['ClaveCentroCosto'] . '_' . $contador] = NULL;
    }

    if (isset($rs['IdEnvio']) && $rs['IdEnvio'] != "") {/* Si ya fue enviado el equipo */
        $enviado[$no_parte . '_' . $rs['ClaveCentroCosto'] . '_' . $contador] = true;
    } else {
        $enviado[$no_parte . '_' . $rs['ClaveCentroCosto'] . '_' . $contador] = false;
    }

    $id_bitacora[$no_parte . '_' . $rs['ClaveCentroCosto'] . '_' . $contador] = $rs['id_bitacora'];
    //$centro_bitacora[$rs['NoParte'].'_'.$contador] = $rs['ClaveCentroCosto'];
    $no_parte_anterior = $no_parte;
    $cc_anterior = $rs['ClaveCentroCosto'];
    $contador++;
}

//almacen filtro
$idUsuario = "";
$userAlmacen = "";
$almacen1 = $catalogo->obtenerLista("SELECT * FROM c_usuario WHERE Loggin='" . $_SESSION['user'] . "'");
if ($rs = mysql_fetch_array($almacen1)) {
    $idUsuario = $rs['IdPuesto'];
    $userAlmacen = $rs['IdUsuario'];
}


$consulta = "SELECT * FROM k_responsablealmacen ra,c_almacen a ,c_usuario us WHERE ra.IdUsuario='" . $userAlmacen . "' 
    AND (a.TipoAlmacen = 1 OR a.Surtir = 1) AND a.Activo=1 AND ra.IdAlmacen=a.id_almacen AND ra.IdUsuario=us.IdUsuario ORDER BY a.nombre_almacen ASC";
$result = $catalogo->obtenerLista($consulta);

if (mysql_num_rows($result) == 0) {//Si no tiene almacen predeterminado
    $consulta = "SELECT * FROM c_almacen a WHERE (a.TipoAlmacen = 1 OR a.Surtir = 1) AND a.Activo=1 ORDER BY a.nombre_almacen ASC";
    $result = $catalogo->obtenerLista($consulta);
}

$almacenes = "<option value=''>Selecciona el almacén</option>";
while ($rs = mysql_fetch_array($result)) {
    if ($rs['id_almacen'] != $IdAlmacenDestino) {
        $almacenes .= "<option value='" . $rs['id_almacen'] . "'>" . $rs['nombre_almacen'] . "</option>";
    }
}

$obj_almacen = new Almacen();
$resultAlmacenes = $obj_almacen->getAlmacenResponsable($_SESSION['idUsuario']);
$array_almacenes = explode(",", $resultAlmacenes);

?>
<!DOCTYPE html>
<html lang="es">
    <head>        
        <script type="text/javascript" language="javascript" src="resources/js/paginas/lista_solicitudes_series.js"></script>        
        <style>
            .mensajeria{display: none;}
            .propio{display: none;}
        </style>
    </head>
    <body>
        <div class="principal">     
            <a href='reportes/SolicitudEquipo.php?noSolicitud=<?php echo $idSolicitud; ?>' target="_blank" title='Reporte' style="float: right;"><img src="resources/images/icono_impresora.png" width="30" height="30" /></a>
            <br/><br/>
            Solicitud <b>#<?php echo $idSolicitud; ?></b> para el cliente: <h2><?php echo $cliente; ?></h2><br/>            
            <form id='formSerie' name='formSeries'>                
                Comentarios:
                <textarea id="comentario_solicitud" name="comentario_solicitud" style="resize: none; width: 100%; height: 40px;"><?php echo $comentario ?></textarea>
                <br/>
                <h3>Equipos</h3>
                <table id="tAlmacen" class="tabla_datos" style="max-width: 100%;">
                    <thead>
                        <tr>
                            <?php
                            for ($i = 0; $i < (count($cabeceras)); $i++) {
                                echo "<th width=\"2%\" align=\"center\" scope=\"col\">" . $cabeceras[$i] . "</th>";
                            }
                            if ($isThereBitacora == "true") {
                                echo "<th width=\"2%\" align=\"center\" scope=\"col\"></th>";
                                echo "<th width=\"2%\" align=\"center\" scope=\"col\"></th>";
                            }
                            ?>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $contador = 0;
                        //$array_equipo = array();
                        $array_equipo_procesado = array();
                        $no_parte_anterior = "";
                        $fila = 0;
                        while ($rs = mysql_fetch_array($query)) {
                            for ($j = 0; $j < intval($rs['cantidad_autorizada']); $j++, $contador++) {
                                $modelo = str_replace(" ", "", $rs['Modelo']);
                                if (!isset($array_equipo_procesado[$modelo . "_" . $rs['ClaveCentroCosto']])) {
                                    //$array_equipo[$rs['Modelo']] = 0;
                                    $array_equipo_procesado[$modelo . "_" . $rs['ClaveCentroCosto']] = 0;
                                }

                                if ($isThereBitacora == "true" && isset($datos_bitacora[$modelo . "_" . $rs['ClaveCentroCosto'] . "_" . $array_equipo_procesado[$modelo . "_" . $rs['ClaveCentroCosto']]])) {
                                    $serie = $datos_bitacora[$modelo . "_" . $rs['ClaveCentroCosto'] . "_" . $array_equipo_procesado[$modelo . "_" . $rs['ClaveCentroCosto']]];
                                    $almacen = $almacen_bitacora[$modelo . "_" . $rs['ClaveCentroCosto'] . "_" . $array_equipo_procesado[$modelo . "_" . $rs['ClaveCentroCosto']]];
                                    $idAlmacen = $id_almacen_bitacora[$modelo . "_" . $rs['ClaveCentroCosto'] . "_" . $array_equipo_procesado[$modelo . "_" . $rs['ClaveCentroCosto']]];
                                    $result3 = $lectura->getMaximaLecturaNoSerie($serie); //Buscamos sus maximos contadores y lo guardamos en campos ocultos
                                    while ($rs3 = mysql_fetch_array($result3)) {
                                        echo "<input type='hidden' id='max_contador_bn_$serie' name='max_contador_bn_$serie' value='" . $rs3['MaxContadorBN'] . "'/>";
                                        echo "<input type='hidden' id='max_contador_color_$serie' name='max_contador_bn_$serie' value='" . $rs3['MaxContadorCL'] . "'/>";
                                    }
                                } else {
                                    $serie = "";
                                    $almacen = "";
                                    $idAlmacen = NULL;
                                }

                                echo "<tr id='fila_$contador'>";
                                echo "<td align='center' scope='row' style='max-width: 55px;'>";
                                echo "<input type='hidden' id='partida_equipo_" . $contador . "' name='partida_equipo_" . $contador . "' value='" . $rs['id_partida'] . "'/>";
                                if ($serie != "" && !$enviado[$modelo . "_" . $rs['ClaveCentroCosto'] . "_" . $array_equipo_procesado[$modelo . "_" . $rs['ClaveCentroCosto']]]) {/* Si hay serie */
                                    echo "<input type='checkbox' id='check_solicitud_" . $contador . "' name='check_solicitud_" . $serie . "' disabled='disabled'/>";
                                } else {
                                    echo "<a href='#' onclick='eliminarEquipoDeSolicitud(\"$idSolicitud\",\"" . $rs['id_partida'] . "\"); return false;'>
                                        <img src='resources/images/Erase.png' title='No surtir serie'/></a>";
                                }
                                echo "</td>";
                                //echo "<td align='center' scope='row' style='max-width: 55px;'></td>";
                                echo "<td align='center' scope='row'>
                                    <input type='hidden' id='solicitud_" . $contador . "' name='solicitud_" . $contador . "' value='" . $rs['id_solicitud'] . "' readonly='readonly' style='background: #D1D0CE; max-width: 45px;'/>";
                                $equipo->setNoParte($rs['parte']);
                                $resultSimilares = $equipo->getEquiposSimilares();
                                if (mysql_num_rows($resultSimilares) > 0) {
                                    echo "Equipo solicitado: <br/>";
                                    echo "<input type='text' id='modelo_" . $contador . "_1' name='modelo_" . $contador . "_1' value='" . $rs['modelo_equipo'] . "' title='" . $rs['modelo_equipo'] . "' alt='" . $rs['modelo_equipo'] . "' readonly='readonly' style='background: #D1D0CE; max-width: 90px;'/>";
                                    echo "<br/>Equipo a surtir: <br/>";
                                    echo "<select id='modelo_" . $contador . "' name='modelo_" . $contador . "' 
                                        onchange='cargarEquiposByAlmacen(\"almacen_sol_$fila\",\"serie_$contador\",\"modelo_" . $contador . "\");'>";
                                    $tiene_asimismo = false;
                                    while ($rsSimilar = mysql_fetch_array($resultSimilares)) {
                                        $s = "";
                                        if ($rsSimilar['NoParteEquipoSimilar'] == $rs['parte']) {
                                            $s = "selected='selected'";
                                            $tiene_asimismo = true;
                                        }
                                        echo "<option value='" . $rsSimilar['NoParteEquipoSimilar'] . "' $s>" . $rsSimilar['Modelo'] . " / " . $rsSimilar['NoParteEquipoSimilar'] . "</option>";
                                    }
                                    if (!$tiene_asimismo) {
                                        echo "<option value='" . $rs['parte'] . "' selected='selected'>" . $rs['modelo_equipo'] . " / " . $rs['parte'] . "</option>";
                                    }
                                    echo "</select>";
                                } else {
                                    echo "<input type='text' id='modelo_" . $contador . "_1' name='modelo_" . $contador . "_1' value='" . $rs['modelo_equipo'] . "' title='" . $rs['modelo_equipo'] . "' alt='" . $rs['modelo_equipo'] . "' readonly='readonly' style='background: #D1D0CE; max-width: 90px;'/>";
                                    echo "<input type='hidden' id='modelo_" . $contador . "' name='modelo_" . $contador . "' value='" . $rs['parte'] . "' readonly='readonly' style='background: #D1D0CE; max-width: 90px;'/>";
                                }
                                echo "<input type='hidden' id='modelo_original_" . $contador . "' name='modelo_original_" . $contador . "' value='" . $rs['parte'] . "' readonly='readonly' style='background: #D1D0CE; max-width: 90px;'/>";
                                echo "</td>";
                                echo "<td align='center' scope='row'>
                                    <input type='hidden' id='cliente_" . $contador . "_1' name='cliente_" . $contador . "_1' value='" . $cliente . "' title='$cliente' alt='$cliente' readonly='readonly' style='background: #D1D0CE;'/>
                                    <input type='text' id='estado_equipo_$contador' name='estado_equipo_$contador' value='" . $rs['estadoEquipo'] . "' title='" . $rs['estadoEquipo'] . "' alt='" . $rs['estadoEquipo'] . "' readonly='readonly' style='background: #D1D0CE;'/>    
                                    </td>";
                                if (isset($rs['ClaveCentroCosto'])) {
                                    $cc = $rs['Nombre'];
                                } else {
                                    $cc = "Sin localidad";
                                }
                                echo "<td align='center' scope='row'>
                                    <input type='text' id='cc_" . $contador . "_1' name='cc_" . $contador . "_1' value='" . $cc . "' title='$cc' alt='$cc' readonly='readonly' style='background: #D1D0CE; max-width:'150px;'/>
                                    <input type='hidden' id='cc_" . $contador . "' name='cc_" . $contador . "' value='" . $rs['ClaveCentroCosto'] . "'></td>";
                                if ($serie != "") {//Si ya hay serie cargada para este registro
                                    echo "<td align='center' scope='row'>$almacen</td>";
                                    echo "<td align='center' scope='row'>$serie</td>";
                                    $series_capturadas = true;
                                } else {
                                    echo "<td align='center' scope='row'><select id='almacen_sol_$fila' name='almacen_sol_$fila' style='width:150px;' 
                                        onchange='cargarEquiposByAlmacen(\"almacen_sol_$fila\",\"serie_$contador\",\"modelo_" . $contador . "\");'>$almacenes</select></td>";
                                    echo "<td align='center' scope='row'>
                                        <select id='serie_$contador' name='serie_$contador' style='width:150px;' onchange='addRow(\"" . $rs['parte'] . "\",\"serie_$contador\",\"fila_$contador\",\"" . $rs['idTipo'] . "\");'>
                                            <option value=\"\">Selecciona el No. de Serie</option>
                                        </select>
                                        </td>";
                                }
                                if ($isThereBitacora == "true") {
                                    if ($serie != "") {
                                        echo "<td align='center' scope='row'>
                                            <a href='#' onclick='editarRegistro(\"almacen/configuracion.php?regresar=ventas/lista_solicitud_series.php?id=" . $idSolicitud . "\",\"" . $id_bitacora[$modelo . "_" . $rs['ClaveCentroCosto'] . "_" . $array_equipo_procesado[$modelo . "_" . $rs['ClaveCentroCosto']]] . "\"); return false'><img src='resources/images/Apply.png' title='Configura el equipo'/></a>
                                            <input type='hidden' id='bitacora_" . $contador . "' name='bitacora_" . $contador . "' value='" . $id_bitacora[$modelo . "_" . $rs['ClaveCentroCosto'] . "_" . $array_equipo_procesado[$modelo . "_" . $rs['ClaveCentroCosto']]] . "' readonly='readonly' style='background: #D1D0CE; max-width: 45px;'/></td>";
                                        if(in_array($idAlmacen, $array_almacenes)){//Si el usuario actual es el usuario encargado del almacen origen, puede desasociar las series
                                            echo "<td><a href='#' onclick='eliminarRegistro(\"WEB-INF/Controllers/Controler_Bitacora.php?desasociar=" . $id_bitacora[$modelo . "_" . $rs['ClaveCentroCosto'] . "_" . $array_equipo_procesado[$modelo . "_" . $rs['ClaveCentroCosto']]] . "\",\"ventas/lista_solicitud_series.php?id=$idSolicitud\"); return false;'><img src='resources/images/Erase.png' title='Desasociar serie'/></a></td>";
                                        }else{
                                            echo "<td></td>";
                                        }
                                        $array_equipo_procesado[$modelo . "_" . $rs['ClaveCentroCosto']]++;
                                    } else {
                                        echo "<td></td>";
                                        echo "<td></td>";
                                    }
                                }
                                if($permisos_grid->tienePermisoEspecial($_SESSION['idUsuario'], 35)){
                                ?>
                                <td align="center"><a href="#" title="Ver existencias" onclick="lanzarPopUp('Ver existencias', 'ventas/verificarExistenciasTipoModelo.php?tipo=0&modelo=<?php echo $rs['parte']; ?>');
                            return false;"><img src="resources/images/existencias.png" width="28" height="28"/></a></td>
                                <?
                                }
                                echo "</tr>";
                                echo "<input type='hidden' id='serie_" . $contador . "' name='serie_" . $contador . "' value='$serie'/>";

                                $no_parte_anterior = $modelo;
                                $fila++;
                            }
                        }
                        ?>
                    </tbody>
                </table>
                <br/>
                <?php
                $consulta = "SELECT ks.*, ket.IdEnvio, CONCAT(cdt.Nombre,' ',cdt.ApellidoPaterno,' ') AS conductor, CONCAT(v.Modelo,' - ',v.Placas) AS vehiculo, 
                    cm.Nombre AS mensajeria, ket.NoGuia, ks.cantidad_surtida,
                    ksa.IdAlmacen, al.nombre_almacen, e.Modelo AS modelo_equipo, e.NoParte AS parte, c.ClaveCentroCosto ,c.Nombre, 
                    cli.NombreRazonSocial, ti.Nombre AS estadoEquipo, ti.idTipo, 
                    (SELECT SUM(Cantidad) FROM k_enviotoner WHERE NoParte = ks.Modelo AND IdSolicitudEquipo = ks.id_solicitud 
                    AND ClaveCentroCosto = ks.ClaveCentroCosto) AS enviados,
                    e.IdColor, e.IdTipoComponente
                    FROM `k_solicitud` AS ks
                    INNER JOIN c_componente AS e ON ks.id_solicitud = $idSolicitud AND ks.tipo = 1 AND e.NoParte = ks.Modelo
                    LEFT JOIN c_centrocosto AS c ON c.ClaveCentroCosto = ks.ClaveCentroCosto
                    LEFT JOIN c_cliente AS cli ON cli.ClaveCliente = c.ClaveCliente 
                    LEFT JOIN c_tipoinventario AS ti ON ti.idTipo = ks.TipoInventario
                    LEFT JOIN k_solicitud_asignado AS ksa ON ksa.id_solicitud = ks.id_solicitud AND ksa.id_partida = ks.id_partida
                    LEFT JOIN c_almacen AS al ON al.id_almacen = ksa.IdAlmacen
                    LEFT JOIN k_enviotoner AS ket ON ket.IdEnvio = (SELECT MAX(IdEnvio) FROM k_enviotoner WHERE NoParte = ks.Modelo AND IdSolicitudEquipo = ks.id_solicitud AND ClaveCentroCosto = ks.ClaveCentroCosto)
                    LEFT JOIN c_vehiculo AS v ON v.IdVehiculo = ket.IdVehiculo
                    LEFT JOIN c_conductor AS cdt ON cdt.IdConductor = ket.IdConductor
                    LEFT JOIN c_mensajeria AS cm ON cm.IdMensajeria = ket.IdMensajeria 
                    WHERE e.IdTipoComponente <> 7 
                    ORDER BY ks.ClaveCentroCosto;";
                $query = $catalogo->obtenerLista($consulta);
                ?>
                <h3>Componentes</h3>
                <table id="tAlmacen" class="tabla_datos" style="width: 100%;">
                    <thead>
                        <tr>
                            <?php
                            $hay_componentes = false;
                            if($permisos_grid->tienePermisoEspecial($_SESSION['idUsuario'], 35)){
                                for ($i = 0; $i < (count($cabeceras)) - 3; $i++) {
                                    echo "<th align=\"center\" scope=\"col\">" . $cabeceras[$i] . "</th>";
                                }
                            }else{
                                for ($i = 0; $i < (count($cabeceras)) - 2; $i++) {
                                    echo "<th align=\"center\" scope=\"col\">" . $cabeceras[$i] . "</th>";
                                }
                            }
                            echo "<th align=\"center\" scope=\"col\">Solicitados</th>";
                            echo "<th align=\"center\" scope=\"col\">Surtir</th>";
                            echo "<th align=\"center\" scope=\"col\">Almacén</th>";
                            echo "<th align=\"center\" scope=\"col\">Existencias</th>";
                            echo "<th align=\"center\" scope=\"col\">Desasociar</th>";
                            if($permisos_grid->tienePermisoEspecial($_SESSION['idUsuario'], 35)){
                                echo "<th align=\"center\" scope=\"col\"> </th>";
                            }
                            ?>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $contador2 = 0;
                        while ($rs = mysql_fetch_array($query)) {
                            echo "<tr>";
                            $checked = '';
                            if ($rs['NoSurtir'] == "1") {
                                $checked = "checked='checked'";
                            }
                            $listo_enviar = false;

                            echo "<td align='center' scope='row'>";
                            if (!isset($rs['IdEnvio']) || $rs['cantidad_surtida'] > $rs['enviados']) {
                                echo "<input type='checkbox' id='check_solicitud2_" . $contador2 . "' name='check_solicitud2_$contador2' disabled='disabled'/>";
                                $listo_enviar = true;
                            }
                            echo "</td>";
                            //echo "<td align='center' scope='row' style='max-width: 55px;'></td>";
                            echo "<td align='center' scope='row'>                                    
                                    <input type='hidden' id='solicitud2_" . $contador2 . "' name='solicitud2_" . $contador2 . "' value='" . $rs['id_solicitud'] . "' 
                                        readonly='readonly' style='background: #D1D0CE; max-width: 40px;'/>
                                    <input type='hidden' id='partida_" . $contador2 . "' name='partida_" . $contador2 . "' value='" . $rs['id_partida'] . "'/>";
                            if ($rs['IdTipoComponente'] == "2" && $checked == "" && (!isset($rs['IdAlmacen']) || $rs['IdAlmacen'] == "") && (!isset($rs['IdEnvio']) || $rs['IdEnvio'] == "")) {//En caso que el componente sea toner, no esté marcado como no surtir, no tenga ya asignado almacén
                                echo "Modelo solicitado: <br/>
                                            <input type='text' id='modelo2_" . $contador2 . "_1' name='modelo2_" . $contador2 . "_1' value='" . $rs['modelo_equipo'] . " / " . $rs['parte'] . "' 
                                                title='" . $rs['modelo_equipo'] . "' alt='Componente' readonly='readonly' style='background: #D1D0CE; max-width: 140px;'/>";
                                echo "<input type='hidden' id='modelo2_" . $contador2 . "_original' name='modelo2_" . $contador2 . "_original' value='" . $rs['parte'] . "' />";
                                $consultaCompatibles = "SELECT ks.id_solicitud, ks.id_partida, c.NoParte, c.Modelo
                                            FROM k_solicitud AS ks
                                            LEFT JOIN k_equipocomponentecompatible AS kecc ON kecc.NoParteComponente = ks.Modelo
                                            LEFT JOIN k_equipocomponentecompatible AS kecc2 ON kecc2.NoParteEquipo = kecc.NoParteEquipo
                                            LEFT JOIN c_componente AS c ON c.NoParte = kecc2.NoParteComponente
                                            WHERE ks.id_partida = " . $rs['id_partida'] . " AND ks.id_solicitud = " . $rs['id_solicitud'] . " AND c.IdTipoComponente = 2 AND c.IdColor = " . $rs['IdColor'] . " AND c.Activo = 1
                                            GROUP BY c.NoParte;";

                                echo "<br/>Modelo a surtir: <br/>";
                                $resultCompatible = $catalogo->obtenerLista($consultaCompatibles);

                                if (mysql_num_rows($resultCompatible) > 1) {
                                    echo "<select id='modelo2_" . $contador2 . "' name='modelo2_" . $contador2 . "'
                                                onchange='getExistenciasAlmacen(\"existencia2_$contador2\",\"almacen_sol2_$contador2\",\"modelo2_$contador2\");
                                                ponerCantidad(\"cantidad2_$contador2\",\"cantidad2_sur_$contador2\",\"almacen_sol2_$contador2\");'>";
                                    while ($rsCompatible = mysql_fetch_array($resultCompatible)) {
                                        $s = "";
                                        if ($rs['parte'] == $rsCompatible['NoParte']) {
                                            $s = "selected = 'selected'";
                                        }
                                        echo "<option value='" . $rsCompatible['NoParte'] . "' $s>" . $rsCompatible['Modelo'] . " / " . $rsCompatible['NoParte'] . "</option>";
                                    }
                                    echo "</select>";
                                } else {
                                    echo "<input type='text' id='modelo2_" . $contador2 . "_1' name='modelo2_" . $contador2 . "_1' value='" . $rs['modelo_equipo'] . " / " . $rs['parte'] . "' 
                                                    title='" . $rs['modelo_equipo'] . "' alt='Componente' readonly='readonly' style='background: #D1D0CE; max-width: 140px;'/>"
                                    . "<input type='hidden' id='modelo2_" . $contador2 . "' name='modelo2_" . $contador2 . "' value='" . $rs['parte'] . "' />";
                                }
                            } else {
                                echo "<input type='text' id='modelo2_" . $contador2 . "_1' name='modelo2_" . $contador2 . "_1' value='" . $rs['modelo_equipo'] . " / " . $rs['parte'] . "' 
                                                title='" . $rs['modelo_equipo'] . "' alt='Componente' readonly='readonly' style='background: #D1D0CE; max-width: 140px;'/>"
                                . "<input type='hidden' id='modelo2_" . $contador2 . "' name='modelo2_" . $contador2 . "' value='" . $rs['parte'] . "' />";
                            }
                            echo "</td>";
                            echo "<td align='center' scope='row'>
                            <input type='hidden' id='cliente2_" . $contador2 . "_1' name='cliente2_" . $contador2 . "_1' value='" . $cliente . "' title='$cliente' alt='$cliente' readonly='readonly' style='background: #D1D0CE;'/>
                            <input type='text' id='estado_equipo2_$contador2' name='estado_equipo2_$contador2' value='" . $rs['estadoEquipo'] . "' 
                                title='" . $rs['estadoEquipo'] . "' alt='" . $rs['estadoEquipo'] . "' readonly='readonly' style='background: #D1D0CE; max-width: 70px;'/>    
                            </td>";
                            if (isset($rs['ClaveCentroCosto'])) {
                                $cc = $rs['Nombre'];
                            } else {
                                $cc = "Sin localidad";
                            }
                            echo "<td align='center' scope='row'>
                                <input type='text' id='cc2_" . $contador2 . "_1' name='cc2_" . $contador2 . "_1' value='" . $cc . "' title='$cc' alt='$cc' readonly='readonly' style='background: #D1D0CE; max-width:'150px;'/>
                                <input type='hidden' id='cc2_" . $contador2 . "' name='cc2_" . $contador2 . "' value='" . $rs['ClaveCentroCosto'] . "'></td>";

                            echo "<td align='center' scope='row' style='max-width: 85px;'>
                                <input type='text' id='cantidad2_$contador2' name='cantidad2_$contador2' value='" . $rs['cantidad_autorizada'] . "' 
                                    readonly='readonly' style='background: #D1D0CE; position: relative; max-width: 30px;'/>
                                </td>";

                            $faltan_asignar = false;
                            /* Campo para que digiten la cantidad que se va a surtir */
                            if (isset($rs['IdEnvio']) && $rs['IdEnvio'] != "" && $rs['enviados'] == $rs['cantidad_autorizada']) {
                                echo "<td align='center' scope='row' style='max-width: 85px;'>OK</td>";
                            } else if (isset($rs['IdAlmacen']) && $rs['IdAlmacen'] != "") {
                                echo "<td align='center' scope='row' style='max-width: 85px;'>
                                <input type='text' id='cantidad_surti_$contador2' name='cantidad_surti_$contador2' value='" . $rs['cantidad_surtida'] . "' readonly='readonly' style='background: #D1D0CE; position: relative; max-width: 30px;'/>                                
                                </td>";
                                if (intval($rs['cantidad_autorizada']) > intval($rs['cantidad_surtida'])) {
                                    $faltan_asignar = true;
                                }
                            } else {
                                echo "<td align='center' scope='row' style='max-width: 85px;'>";
                                if ($checked == "") {//Si no esta marcado como no surtir
                                    echo "<input type='text' id='cantidad2_sur_$contador2' name='cantidad2_sur_$contador2' value='' 
                                    style='position: relative; max-width: 30px;'/>";
                                }
                                echo "</td>";
                            }

                            echo "<td align='center' scope='row'>
                                <input type='hidden' id='almacen_" . $contador2 . "_seleccionado' name='almacen_" . $contador2 . "_seleccionado' 
                                 value='" . $rs['IdAlmacen'] . "'>";
                            
                            if (isset($rs['IdAlmacen']) && $rs['IdAlmacen'] != "") {
                                $hay_componentes = true;
                                if (isset($rs['IdEnvio']) && $rs['IdEnvio'] != "" && $rs['cantidad_surtida'] == $rs['enviados']) {
                                    if (isset($rs['conductor'])) {
                                        echo "Enviado con: " . $rs['conductor'] . " en el vehículo " . $rs['vehiculo'];
                                    } else if (isset($rs['mensajeria'])) {
                                        echo "Enviado por: " . $rs['mensajeria'] . " con número de guía " . $rs['NoGuia'];
                                    }
                                }else{
                                    if ($checked == "" || $listo_enviar) {//Si no esta marcado como no surtir
                                        echo "<select id='almacen_sol2_$contador2' name='almacen_sol2_$contador2' style='width:150px;' 
                                        onchange='getExistenciasAlmacen(\"existencia2_$contador2\",\"almacen_sol2_$contador2\",\"modelo2_$contador2\");
                                            ponerCantidad(\"cantidad2_$contador2\",\"cantidad2_sur_$contador2\",\"almacen_sol2_$contador2\");'>                                                
                                        $almacenes
                                        </select>";
                                    }
                                }
                            }
                            /*if (isset($rs['IdAlmacen']) && $rs['IdAlmacen'] != "") {
                                if (isset($rs['IdEnvio']) && $rs['IdEnvio'] != "" && $rs['cantidad_surtida'] == $rs['enviados']) {
                                    if (isset($rs['conductor'])) {
                                        echo "Enviado con: " . $rs['conductor'] . " en el vehículo " . $rs['vehiculo'];
                                    } else if (isset($rs['mensajeria'])) {
                                        echo "Enviado por: " . $rs['mensajeria'] . " con número de guía " . $rs['NoGuia'];
                                    }
                                } else {
                                    if ($checked == "" || $listo_enviar) {//Si no esta marcado como no surtir
                                        echo "<select id='almacen_sol2_$contador2' name='almacen_sol2_$contador2' style='width:150px;' 
                                                onchange='getExistenciasAlmacen(\"existencia2_$contador2\",\"almacen_sol2_$contador2\",\"modelo2_$contador2\");'>
                                                <option value='" . $rs['IdAlmacen'] . "'>" . $rs['nombre_almacen'] . "</option>
                                            </select>";
                                    }
                                }
                                $hay_componentes = true;
                            } else {
                                if ($checked == "") {//Si no esta marcado como no surtir
                                    echo "<select id='almacen_sol2_$contador2' name='almacen_sol2_$contador2' style='width:150px;' 
                                            onchange='getExistenciasAlmacen(\"existencia2_$contador2\",\"almacen_sol2_$contador2\",\"modelo2_$contador2\");
                                                ponerCantidad(\"cantidad2_$contador2\",\"cantidad2_sur_$contador2\",\"almacen_sol2_$contador2\");'>
                                            $almacenes
                                        </select>";
                                }
                            }*/
                            if ((!isset($rs['IdAlmacen']) || $rs['IdAlmacen'] == "") && $checked == "") {//Si no esta marcado como no surtir
                                echo "<select id='almacen_sol2_$contador2' name='almacen_sol2_$contador2' style='width:150px;' 
                                        onchange='getExistenciasAlmacen(\"existencia2_$contador2\",\"almacen_sol2_$contador2\",\"modelo2_$contador2\");
                                            ponerCantidad(\"cantidad2_$contador2\",\"cantidad2_sur_$contador2\",\"almacen_sol2_$contador2\");'>                                                
                                        $almacenes
                                    </select>";
                            }
                            echo "</td>";

                            echo "<td align='center' scope='row' style='max-width: 100px;'>
                                <input type='text' id='existencia2_$contador2' name='existencia2_$contador2' value='0' readonly='readonly' 
                                    style='background: #D1D0CE; relative; max-width: 30px;'/></td>";
                            if (isset($rs['IdEnvio']) && $rs['IdEnvio'] != "") {
                                echo "<td></td>";
                            } else if (isset($rs['IdAlmacen']) && $rs['IdAlmacen'] != "") {
                                echo "<td>                                                                        
                                    <a href='#' onclick='eliminarRegistro(\"WEB-INF/Controllers/Controler_Bitacora.php?solicitud=$idSolicitud&partida=" . $rs['id_partida'] . "&NoParte=" . str_replace(" ", "||__||", $rs['parte']) . "&cantidad=" . $rs['cantidad_surtida'] . "&almacen=" . $rs['IdAlmacen'] . "\",
                                    \"ventas/lista_solicitud_series.php?id=$idSolicitud\"); return false;'><img src='resources/images/Erase.png' title='Desasociar almacén'/></a></td>";
                            } else {
                                if ($permiso_nosurtir) {
                                    echo "<td><input type='checkbox' id='no_surtir_$contador2' name='no_surtir_$contador2' $checked
                                        onchange='marcarNoSurtido(\"no_surtir_$contador2\",\"" . $rs['id_solicitud'] . "\",\"" . $rs['id_partida'] . "\");'/>No surtir</td>";
                                } else {
                                    echo "<td></td>";
                                }
                            }
                            if($permisos_grid->tienePermisoEspecial($_SESSION['idUsuario'], 35)){
                            ?>
                            <td align="center"><a href="#" title="Ver existencias" onclick="lanzarPopUp('Ver existencias', 'ventas/verificarExistenciasTipoModelo.php?tipo=<?php echo $rs['IdTipoComponente']; ?>&modelo=<?php echo $rs['parte']; ?>');
                            return false;"><img src="resources/images/existencias.png" width="28" height="28"/></a></td>
                            <?
                            }
                            echo "</tr>";

                            if (isset($rs['IdEnvio']) && $rs['IdEnvio'] != "" && $rs['enviados'] != $rs['cantidad_autorizada']) {
                                $faltan_asignar = true;
                            }
                            if (intval(($rs['cantidad_autorizada']) - intval($rs['cantidad_surtida'])) <= 0) {
                                $faltan_asignar = false;
                            }

                            $contador2++;
                            if ($faltan_asignar) {
                                echo "<tr>";
                                echo "<td></td>";

                                //echo "<td align='center' scope='row' style='max-width: 55px;'></td>";
                                echo "<td align='center' scope='row'>
                                        <input type='hidden' id='solicitud2_" . $contador2 . "' name='solicitud2_" . $contador2 . "' value='" . $rs['id_solicitud'] . "' 
                                            readonly='readonly' style='background: #D1D0CE; max-width: 40px;'/>
                                        <input type='hidden' id='partida_" . $contador2 . "' name='partida_" . $contador2 . "' value='" . $rs['id_partida'] . "'/>
                                        <input type='text' id='modelo2_" . $contador2 . "_1' name='modelo2_" . $contador2 . "_1' value='" . $rs['modelo_equipo'] . "' 
                                            title='" . $rs['modelo_equipo'] . "' alt='Componente' readonly='readonly' style='background: #D1D0CE; max-width: 70px;'/>
                                        <input type='hidden' id='modelo2_" . $contador2 . "' name='modelo2_" . $contador2 . "' value='" . $rs['parte'] . "' readonly='readonly' style='background: #D1D0CE; max-width: 90px;'/>
                                        </td>";
                                echo "<td align='center' scope='row'>
                                <input type='hidden' id='cliente2_" . $contador2 . "_1' name='cliente2_" . $contador2 . "_1' value='" . $cliente . "' title='$cliente' alt='$cliente' readonly='readonly' style='background: #D1D0CE;'/>
                                <input type='text' id='estado_equipo2_$contador2' name='estado_equipo2_$contador2' value='" . $rs['estadoEquipo'] . "' 
                                    title='" . $rs['estadoEquipo'] . "' alt='" . $rs['estadoEquipo'] . "' readonly='readonly' style='background: #D1D0CE; max-width: 70px;'/>    
                                </td>";
                                if (isset($rs['ClaveCentroCosto'])) {
                                    $cc = $rs['Nombre'];
                                } else {
                                    $cc = "Sin localidad";
                                }
                                echo "<td align='center' scope='row'>
                                    <input type='text' id='cc2_" . $contador2 . "_1' name='cc2_" . $contador2 . "_1' value='" . $cc . "' title='$cc' alt='$cc' readonly='readonly' style='background: #D1D0CE; max-width:'150px;'/>
                                    <input type='hidden' id='cc2_" . $contador2 . "' name='cc2_" . $contador2 . "' value='" . $rs['ClaveCentroCosto'] . "'></td>";
                                $restante = intval($rs['cantidad_autorizada']) - intval($rs['cantidad_surtida']);
                                echo "<td align='center' scope='row' style='max-width: 85px;'>
                                    <input type='text' id='cantidad2_$contador2' name='cantidad2_$contador2' value='$restante' 
                                        readonly='readonly' style='background: #D1D0CE; position: relative; max-width: 30px;'/>
                                    </td>";

                                echo "<td align='center' scope='row' style='max-width: 85px;'>";
                                if ($checked == "") {//Si no esta marcado como no surtir
                                    echo "<input type='text' id='cantidad2_sur_$contador2' name='cantidad2_sur_$contador2' value='' 
                                    style='position: relative; max-width: 30px;'/>";
                                }
                                echo "</td>";

                                echo "<td align='center' scope='row'>
                                    <input type='hidden' id='almacen_" . $contador2 . "_seleccionado' name='almacen_" . $contador2 . "_seleccionado' 
                                     value='" . $rs['IdAlmacen'] . "'>";
                                if (isset($rs['IdAlmacen']) && $rs['IdAlmacen'] != "") {
                                    $hay_componentes = true;
                                }                                

                                if (isset($rs['IdAlmacen']) && $rs['IdAlmacen'] != "") {
                                    if ($checked == "") {//Si no esta marcado como no surtir
                                        echo "<select id='almacen_sol2_$contador2' name='almacen_sol2_$contador2' style='width:150px;' 
                                                onchange='getExistenciasAlmacen(\"existencia2_$contador2\",\"almacen_sol2_$contador2\",\"modelo2_$contador2\");'>
                                                <option value='" . $rs['IdAlmacen'] . "'>" . $rs['nombre_almacen'] . "</option>
                                            </select>";
                                    }
                                    $hay_componentes = true;
                                } else {
                                    if ((!isset($rs['IdAlmacen']) || $rs['IdAlmacen'] == "") && $checked == "") {//Si no esta marcado como no surtir
                                        echo "<select id='almacen_sol2_$contador2' name='almacen_sol2_$contador2' style='width:150px;' 
                                                onchange='getExistenciasAlmacen(\"existencia2_$contador2\",\"almacen_sol2_$contador2\",\"modelo2_$contador2\");
                                                    ponerCantidad(\"cantidad2_$contador2\",\"cantidad2_sur_$contador2\",\"almacen_sol2_$contador2\");'>
                                                        <option value=''>3</option>
                                                $almacenes
                                            </select>";
                                    }
                                }
                                
                                echo "</td>";

                                echo "<td align='center' scope='row' style='max-width: 100px;'>
                                    <input type='text' id='existencia2_$contador2' name='existencia2_$contador2' value='0' readonly='readonly' 
                                    style='background: #D1D0CE; relative; max-width: 30px;'/></td>";

                                if ($permiso_nosurtir) {
                                    echo "<td><input type='checkbox' id='no_surtir_$contador2' name='no_surtir_$contador2' $checked
                                        onchange='marcarNoSurtido(\"no_surtir_$contador2\",\"" . $rs['id_solicitud'] . "\",\"" . $rs['id_partida'] . "\");'/>No surtir</td>";
                                } else {
                                    echo "<td></td>";
                                }
                                if($permisos_grid->tienePermisoEspecial($_SESSION['idUsuario'], 35)){
                                ?>
                                <td align="center"><a href="#" title="Ver existencias" onclick="lanzarPopUp('Ver existencias', 'ventas/verificarExistenciasTipoModelo.php?tipo=<?php echo $rs['IdTipoComponente']; ?>&modelo=<?php echo $rs['parte']; ?>');
                            return false;"><img src="resources/images/existencias.png" width="28" height="28"/></a></td>
                                <?
                                }
                                echo "</tr>";
                                $contador2++;
                            }
                        }
                        ?>
                    </tbody>
                </table>
                <br/>
                <div id="div_envios" style="display: none;">
                    <input type="radio" name="tipo_envio" id="tipo_envio_mensajeria" value="mensajeria" onclick="mostrarMensajeria(1);"/>Mensajer&iacute;a
                    <input type="radio" name="tipo_envio" id="tipo_envio_propio" value="propio" onclick="mostrarMensajeria(2);"/>Transporte propio
                    <input type="radio" name="tipo_envio" id="tipo_envio_otro" value="otro" onclick="mostrarMensajeria(3);"/>Otros
                    <br/><br/>
                    <select id="mensajeria" name="mensajeria" class="mensajeria" style="margin-right: 5%;">
                        <?php
                        $query = $catalogo->getListaAlta("c_mensajeria", "Nombre");
                        echo "<option value=''>Selecciona la mensajería</option>";
                        while ($rs1 = mysql_fetch_array($query)) {
                            echo "<option value='" . $rs1['IdMensajeria'] . "'>" . $rs1['Nombre'] . "</option>";
                        }
                        ?>
                    </select>
                    <label for="no_guia" class="mensajeria">No. de guía</label><input type="text" id="no_guia" name="no_guia" class="mensajeria" value=""/>
                    <select id="vehiculo" name="vehiculo" class="propio" style="margin-right: 5%;">
                        <?php
                        $query = $catalogo->getListaAlta("c_vehiculo", "Modelo");
                        echo "<option value=''>Selecciona el vehículo</option>";
                        while ($rs1 = mysql_fetch_array($query)) {
                            echo "<option value='" . $rs1['IdVehiculo'] . "'>" . $rs1['Modelo'] . " / " . $rs1['Placas'] . "</option>";
                        }
                        ?>
                    </select>
                    <select id="conductor" name="conductor" class="propio" style="margin-right: 5%;">
                        <?php
                        $query = $catalogo->getListaAlta("c_conductor", "Nombre");
                        echo "<option value=''>Selecciona el conductor</option>";
                        while ($rs1 = mysql_fetch_array($query)) {
                            echo "<option value='" . $rs1['IdConductor'] . "'>" . $rs1['Nombre'] . " " . $rs1['ApellidoPaterno'] . " " . $rs1['ApellidoMaterno'] . "</option>";
                        }
                        ?>
                    </select>
                    <label for="envio_otro" class="otro_envio"></label> <input type="text" id="envio_otro" name="envio_otro" class="otro_envio"/>
                </div>                
                <br/>
                <input type="hidden" id="editar" name="editar" value="<?php echo $isThereBitacora; ?>"/>
                <input type="hidden" id="total" name="total" value="<?php echo $contador; ?>"/>
                <input type="hidden" id="total_componentes" name="total_componentes" value="<?php echo $contador2; ?>"/>
                <input type="hidden" id="pedir_contador" name="pedir_contador" value="<?php echo $pedir_contador; ?>"/>
                <input type="hidden" id="id_solicitud" name="id_solicitud" value="<?php echo $idSolicitud; ?>"/>                
                <input type="hidden" id="clave_cliente" name="clave_cliente" value="<?php echo $ClaveCliente; ?>"/>
                <input type="hidden" id="almacen_destino" name="almacen_destino" value="<?php echo $IdAlmacenDestino; ?>"/>                
                <input type='button' class='button' id='cancelar_series' name='cancelar_series' value='Cancelar' onclick="cambiarContenidos('ventas/list_sol_equipo.php', 'Solicitudes');
                            return false;" style='float: right;'/>                                
                <?php
                if ($permisos_grid->tienePermisoEspecial($_SESSION['idUsuario'], 1)) {//Si tiene permiso de asignar series
                    echo "<input type='submit' class='button' id='submit_series' name='submit_series' onclick='cambiarTipoAccion(1);' value='Guardar' style='float: right;'/>";
                }
                if (($series_capturadas || $hay_componentes) && $permisos_grid->tienePermisoEspecial($_SESSION['idUsuario'], 2)) {/* Si todas las series estan capturadas, entonces ya se pueden transferir los equipos */
                    //echo "<input type='submit' class='button' id='mensajeria_series' name='mensajeria_series' onclick='cambiarTipoAccion(3);' value='Enviar por mensajería' style='float: right;'/>";
                    echo "<input type='checkbox' id='requiere_ticket' name='requiere_ticket' value='si' checked='checked'/>Requiere generar ticket";
                    echo "<input type='submit' class='button' id='mover_series' name='mover_series' onclick='cambiarTipoAccion(2);' value='Enviar a cliente' style='float: right;'/>";
                }
                ?>
                <br/><br/>
            </form>
        </div>
    </body>
</html>