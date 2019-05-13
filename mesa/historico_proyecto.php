<?php
session_start();

if (!isset($_SESSION['user']) || $_SESSION['user'] == "") {
    header("Location: ../index.php");
}

include_once("../WEB-INF/Classes/Catalogo.class.php");
include_once("../WEB-INF/Classes/PermisosSubMenu.class.php");

$catalogo = new Catalogo();
$permisos_grid = new PermisosSubMenu();
$permisos_grid2 = new PermisosSubMenu();
$lista_proyecto = "mesa/lista_proyectos.php";
$lista_actividades = "mesa/lista_actividades.php";
$monitoreo = "mesa/monitoreo_actividades.php";
$same_page = "mesa/historico_proyecto.php";
$params = "";

$permisos_grid->getPermisosSubmenu($_SESSION['idUsuario'], $lista_proyecto);

$nombre_objeto = $permisos_grid2->getNombreTicketSistema();
$nombre_puesto = $permisos_grid2->getNombreTecnicoSistema();
$nombre_nota = $permisos_grid2->getTitulo(7); //Nombre de las notas en la empresa actual
$nombre_areaAtencion = $permisos_grid2->getTitulo(8);
$nombre_UsuarioOrigen = $permisos_grid2->getTitulo(9);

$id = "";
$nombre = "";
$progreso = "";
$abiertos = 0;
$cerrados = 0;
$total = 0;
$fechaInicio = "";
$asignado = "";
$fechaFin = "";
$estado = "";
$creado = "";
$modificado = "";
if(isset($_GET['id']) && $_GET['id'] != ""){
    $id = $_GET['id'];
    $result = $catalogo->obtenerLista("SELECT t.Nombre AS proyecto, IF(ISNULL(t.Progreso),'0%',CONCAT(t.Progreso,'%')) AS Progreso, 
    (SELECT COUNT(x.IdNotaTicket) FROM c_notaticket x WHERE x.IdTicket = t.IdTicket AND x.IdEstadoNota = 1) AS abiertos,
    (SELECT COUNT(x.IdNotaTicket) FROM c_notaticket x WHERE x.IdTicket = t.IdTicket AND x.IdEstadoNota = 2) AS cerrados,
    t.FechaHora AS FechaInicio, CONCAT(u.Nombre, ' ', u.ApellidoPaterno, ' ', u.ApellidoMaterno) AS asignado, t.FechaFinReal,
    et.Nombre AS Estado, CONCAT('<b>',t.FechaCreacion,'</b> por <b>',t.UsuarioCreacion , '</b>') AS creado,
    CONCAT('<b>',t.FechaUltimaModificacion,'</b> por <b>',t.UsuarioUltimaModificacion , '</b>') AS cerrado
    FROM c_ticket t LEFT JOIN c_usuario u ON u.IdUsuario = t.IdTecnicoAsignado
    LEFT JOIN c_estadoticket et ON et.IdEstadoTicket = t.EstadoDeTicket
    WHERE t.IdTicket = $id");
    $params = "?id=$id";
    while($rs = mysql_fetch_array($result)){
        $nombre = $rs['proyecto'];
        $progreso = $rs['Progreso'];
        $abiertos = $rs['abiertos'];
        $cerrados = $rs['cerrados'];
        $total = $abiertos + $cerrados;
        $fechaInicio = $rs['FechaInicio'];
        $asignado = $rs['asignado'];
        $fechaFin = $rs['FechaFinReal'];
        $estado = $rs['Estado'];
        $creado = $rs['creado'];
        $modificado = $rs['cerrado'];
    }
}else{
    return;
}
?>
<!DOCTYPE html>
<html>
    <head>
        <title></title>
        <script type="text/javascript" src="resources/js/paginas/historico_proyecto.js"></script>
        <link href="resources/css/historico.css" rel="stylesheet" type="text/css"/>
    </head>
    <body>
        <div id="mySidenav" class="sidenav">
            <a href="javascript:void(0)" class="closebtn" onclick="closeNav()">&times;</a>
            <a href="#" onclick="cambiarContenidos('<?php echo $lista_proyecto; ?>', '<?php echo $nombre_objeto; ?>'); return false;">Lista de <?php echo $nombre_objeto; ?></a>
            <a href="#" onclick="cambiarContenidos('<?php echo $lista_actividades.$params; ?>','<?php echo $nombre_nota; ?>'); return false;">Lista de <?php echo $nombre_nota; ?></a>            
            <a href="#" onclick="monitorActividades('<?php echo $monitoreo; ?>'); return false;">Monitor de <?php echo $nombre_nota; ?></a>    
        </div>
        <div id="main_panel" style="clear: both;overflow: hidden;height: 1%;">  
            <span style="font-size:30px;cursor:pointer" onclick="openNav()" id="open">&#9776;</span>
            <h3><?php echo $nombre?></h3>
            <div class='module-summary'>
                <div class='span7'>
                    <div class='summaryView'>
                        <h4>Resumen</h4>
                        <hr>
                        <div class='textCenter'>
                            <div class='span3'>
                                <div><b>Progreso</b></div>
                                <div class='info'><?php echo $progreso?></div>
                            </div>
                            <div class='span3'>
                                <div><b><?php echo $nombre_nota?> abiertas</b></div>
                                <div class='info'><?php echo $abiertos?></div>
                            </div>
                            <div class='span3'>
                                <div><b><?php echo $nombre_nota?> cerradas</b></div>
                                <div class='info'><?php echo $cerrados?></div>
                            </div>
                            <div class='span3'>
                                <div><b><?php echo $nombre_nota?> totales</b></div>
                                <div class='info'><?php echo $total?></div>
                            </div>
                        </div>
                        <table style='width: 100%'>
                            <tr class='summaryViewEntries'>
                                <td class='fieldLabel' style='width: 35%'>Nombre del Proyecto</td>
                                <td class='fieldValue' style='width: 65%'><?php echo $nombre?></td>
                            </tr>
                            <tr class='summaryViewEntries'>
                                <td class='fieldLabel' style='width: 35%'>Fecha de Inicio</td>
                                <td class='fieldValue' style='width: 65%'><?php echo $fechaInicio?></td>
                            </tr>
                            <tr class='summaryViewEntries'>
                                <td class='fieldLabel' style='width: 35%'>Asignado a</td>
                                <td class='fieldValue' style='width: 65%'><?php echo $asignado?></td>
                            </tr>
                            <tr class='summaryViewEntries'>
                                <td class='fieldLabel' style='width: 35%'>Fecha de Fin</td>
                                <td class='fieldValue' style='width: 65%'><?php echo $fechaFin?></td>
                            </tr>
                            <tr class='summaryViewEntries'>
                                <td class='fieldLabel' style='width: 35%'>Estado</td>
                                <td class='fieldValue' style='width: 65%'><?php echo $estado?></td>
                            </tr>
                        </table>
                        <div style='width: 100%'>
                            <br>
                            <div>
                                <small>Creado el <?php echo $creado?></small><br>
                                <small>Modificado por última vez el <?php echo $modificado?></small>
                            </div>
                        </div>
                    </div>                    
                </div>
                <div class='span5'>
                    <div class='summaryView'>
                        <h4><?php echo $nombre_nota?></h4>
                        <hr>
                        <div class="tasksSummary">
                            <table style="width: 100%">
                                <tr>
                                    <th align="center" style="width: 70%"><?php echo $nombre_nota?></th>
                                    <th align="center" style="width: 30%">Progreso</th>
                                </tr>
                                <?php
                                $consulta = "SELECT nt.DiagnosticoSol, IF(!ISNULL(nt.Progreso),CONCAT(nt.Progreso,'%'),'0%') AS Progreso FROM c_notaticket nt INNER JOIN c_estado e ON e.IdEstado = nt.IdEstatusAtencion
                                INNER JOIN k_flujoestado k ON k.IdEstado = e.IdEstado WHERE k.IdFlujo = 11 AND nt.IdTicket = $id";
                                $result = $catalogo->obtenerLista($consulta);
                                while($rs = mysql_fetch_array($result)){
                                    echo "<tr>";
                                    echo "<td align='center'>" . $rs['DiagnosticoSol'] . "</td>";
                                    echo "<td align='center'>" . $rs['Progreso'] . "</td>";
                                    echo "</tr>";
                                }
                                ?>
                            </table>
                        </div> 
                    </div>
                </div>
            </div>
            <div class='project-summary'>
                <a href='#' onclick="cambiarContenidos('<?php echo $same_page.$params?>','');">Resumen <?php echo $nombre_objeto?></a>
                <a href='#' onclick="editarElementos('mesa/alta_proyecto.php',<?php echo $id?>);">Editar <?php echo $nombre_objeto?></a>
                <a href='#' onclick="editarElementos('mesa/alta_actividad.php',<?php echo $id?>);">Agregar <?php echo $nombre_nota?></a>
            </div>
        </div>
    </body>
</html>