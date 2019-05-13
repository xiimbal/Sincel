<?php
session_start();

if (!isset($_SESSION['user']) || $_SESSION['user'] == "") {
    header("Location: ../index.php");
}

include_once("../WEB-INF/Classes/Catalogo.class.php");
include_once("../WEB-INF/Classes/PermisosSubMenu.class.php");
include_once("../WEB-INF/Classes/Ticket.class.php");

$catalogo = new Catalogo();
$permisos_grid = new PermisosSubMenu();
$permisos_grid2 = new PermisosSubMenu();

$paginaAnterior = "mesa/lista_proyectos.php";//Pondremos esta pagina, ya que para llegar a lista_actividades.php debieron llegar desde ahí :O
$same_page = "mesa/lista_actividades.php";
$pantalla_edicion = "mesa/alta_actividad.php";
$controlador = "WEB-INF/Controllers/Controler_Actividad.php";
$permisos_grid->getPermisosSubmenu($_SESSION['idUsuario'], $paginaAnterior);
$nombre_proyecto = $permisos_grid2->getNombreTicketSistema();
$nombre_objeto = $permisos_grid2->getTitulo(7); //Nombre de las notas en la empresa actual
$id = "";
$where = "";
$params = "";
$monitoreo = "mesa/monitoreo_actividades.php";
if(isset($_GET['id']) && $_GET['id'] != ""){//Un proyecto en específico
    $id = $_GET['id'];
    $ticket = new Ticket();
    $params = "?id=$id";
    $ticket->getRegistroProyecto($id);
    $nombre = $ticket->getNombre();
    $where = "WHERE nt.IdTicket = $id";
}else{//Todos los proyectos
    //return;
    $nombre = "Todos";
}

/* Para mantener los filtros y paginados de la tabla */
if (isset($_GET['page']) && isset($_GET['filter'])) {
    $filter = str_replace("_XX__XX_", " ", $_GET['filter']);
    $page = $_GET['page'];
} else {
    $page = "0";
    $filter = "";
}
?>
<!DOCTYPE html>
<html>
    <head>
        <title></title>
        <script type="text/javascript" src="resources/js/paginas/lista_actividades.js"></script>
    </head>
    <body>
        <div id="mySidenav" class="sidenav">
            <a href="javascript:void(0)" class="closebtn" onclick="closeNav()">&times;</a>
            <a href="#" onclick="cambiarContenidos('<?php echo $paginaAnterior; ?>','<?php echo $nombre_proyecto; ?>'); return false;">Lista de <?php echo $nombre_proyecto; ?></a>
            <a href="#" onclick="cambiarContenidos('<?php echo $same_page.$params; ?>','<?php echo $nombre_objeto; ?>'); return false;">Lista de <?php echo $nombre_objeto; ?></a>   
            <a href="#" onclick="monitorActividades('<?php echo $monitoreo; ?>'); return false;">Monitor de <?php echo $nombre_objeto; ?></a>         
        </div>
        <div id="main_panel">
            <span style="font-size:30px;cursor:pointer" onclick="openNav()" id="open">&#9776;</span>
            <h2><?php echo "<b>$nombre_proyecto</b>: $nombre"?> </h2>
            <?php if($permisos_grid->getAlta()){ ?>
            <input type="button" class="button" value="&plus; Agregar <?php echo $nombre_objeto; ?>" onclick="crearActividad(); return false;"/>
            <br><br>
            <?php }?>
            <table id="tActividades" style="width: 100%">
                <thead>
                    <tr>
                        <?php
                        if(!empty($id)){
                            $cabeceras = array("Nombre $nombre_objeto", "Tipo", "Asignado a", "Progreso", "Fecha Inicio", "Fecha Fin","","");                            
                        }else{
                            $cabeceras = array("Nombre $nombre_objeto", "Nombre $nombre_proyecto","Tipo", "Asignado a", "Progreso", "Fecha Inicio", "Fecha Fin","","");
                        }
                        for($x = 0; $x < count($cabeceras); $x++){
                            echo "<th width=\"2%\" align=\"center\" scope=\"col\">" . $cabeceras[$x] . "</th>";
                        }
                        ?>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $consulta = "SELECT nt.DiagnosticoSol, t.IdTicket, t.Nombre AS proyecto, e.Nombre, CONCAT(u.Nombre, ' ',u.ApellidoPaterno, ' ', u.ApellidoMaterno) AS usuario,
                    nt.Progreso, nt.FechaInicio, nt.FechaFin, nt.IdNotaTicket FROM c_notaticket nt INNER JOIN c_estado e ON e.IdEstado = nt.IdEstatusAtencion
                    INNER JOIN c_usuario u ON u.IdUsuario = nt.IdTecnicoAsignado INNER JOIN c_ticket t ON t.IdTicket = nt.IdTicket $where";
                    $result = $catalogo->obtenerLista($consulta);
                    while($rs = mysql_fetch_array($result)){
                        echo "<tr>";
                        echo "<td align='center' scope='row'>" . $rs['DiagnosticoSol'] . "</td>";
                        if(empty($id)){
                            echo "<td align='center' scope='row'>" . $rs['proyecto'] . "</td>";
                        }
                        echo "<td align='center' scope='row'>" . $rs['Nombre'] . "</td>";
                        echo "<td align='center' scope='row'>" . $rs['usuario'] . "</td>";
                        echo "<td align='center' scope='row'>";
                        if(!empty($rs['Progreso'])){
                            echo $rs['Progreso'] . "%";
                        }                                
                        echo "</td>";
                        echo "<td align='center' scope='row'>" . $rs['FechaInicio'] . "</td>";
                        echo "<td align='center' scope='row'>" . $rs['FechaFin'] . "</td>";
                        echo "<td align='center' scope='row'>";
                        if($permisos_grid->getModificar()){
                            echo "<a href='#' onclick='editarActividad(" . $rs['IdNotaTicket'] . ", " . $rs['IdTicket'] . "); return false;' title='Modificar' ><img src='resources/images/Modify.png'/></a>";
                        }
                        echo "</td>";
                        echo "<td align='center' scope='row'>";
                        if($permisos_grid->getBaja()){
                            echo "<a href='#' onclick='eliminarActividad(" . $rs['IdNotaTicket'] . "); return false;' title='Modificar' ><img src='resources/images/Erase.png'/></a>";
                        }
                        echo "</td>";
                        echo "</tr>";
                    }
                    ?>
                </tbody>
            </table>            
        </div>
        <input type="hidden" id="regresar" name="regresar" value="<?php echo $same_page ?>"/>
        <input type="hidden" id="id" name="id" value="<?php echo $id?>"/>
        <input type="hidden" id="page" name="page" value="<?php echo $page; ?>"/>
        <input type="hidden" id="actividad" name="actividad" value="<?php echo $nombre_objeto?>"/>
        
    </body>
</html>