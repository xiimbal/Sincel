<?php
session_start();

if (!isset($_SESSION['user']) || $_SESSION['user'] == "") {
    header("Location: ../index.php");
}

include_once("../WEB-INF/Classes/Catalogo.class.php");
include_once("../WEB-INF/Classes/PermisosSubMenu.class.php");
include_once("../WEB-INF/Classes/NotaTicket.class.php");

$catalogo = new Catalogo();
$permisos_grid = new PermisosSubMenu();
$permisos_grid2 = new PermisosSubMenu();
$nombre_proyecto = $permisos_grid2->getNombreTicketSistema();
$nombre_nota = $permisos_grid2->getTitulo(7); //Nombre de las notas en la empresa actual
$idTicket = "";
$id = "";
$nombre = "";
$prioridad = "";
$tipo = "";
$codigo = "";
$relacionado = "";
$usuario = "";
$estado = "";
$progreso = "";
$horasTrabajadas = "";
$fechaInicio = "";
$fechaFin = "";
$descripcion = "";
$pagina_anterior = "mesa/lista_proyectos.php";
$params = "";
$monitoreo = "mesa/monitoreo_actividades.php";
if(isset($_POST['idTicket']) && $_POST['idTicket'] != ""){
    $idTicket = $_POST['idTicket'];
    include_once("../WEB-INF/Classes/Ticket.class.php");
    $ticket = new Ticket();
    $ticket->getRegistroProyecto($idTicket);
    $relacionado = $ticket->getNombre();
    if(isset($_POST['idActividad']) && $_POST['idActividad'] != ""){
        $id = $_POST['idActividad'];
        $actividad = new NotaTicket();
        $actividad->getRegistroById($id);
        $nombre = $actividad->getDiagnostico();
        $prioridad = $actividad->getPrioridad();
        $tipo = $actividad->getIdEstatus();
        $codigo = $actividad->getCodigo();
        $usuario = $actividad->getIdTecnicoAsignado();
        $estado = $actividad->getIdEstadoNota();
        $progreso = $actividad->getProgreso();
        $horasTrabajadas = $actividad->getHorasTrabajadas();
        $fechaInicio = $actividad->getFechaInicio();
        $fechaFin = $actividad->getFechaFin();
        $descripcion = $actividad->getDescripcion();
    }
}

if (isset($_POST['regresar']) && $_POST['regresar']!="") {
    $pagina_listaRegresar = $_POST['regresar'] . "?id=$idTicket";
} else if(isset($_GET['regresar']) && $_GET['regresar'] != ""){//Aquí ya tiene incluido el id del ticket
    $pagina_listaRegresar = $_GET['regresar'];
}else{
    $pagina_listaRegresar = "mesa/lista_actividades.php?id=$idTicket";
}

?>
<!DOCTYPE html>
<html>
    <head>
        <title></title>
        <script type="text/javascript" src="resources/js/paginas/alta_actividad.js"></script>
    </head>
    <body>
        <div id="mySidenav" class="sidenav">
            <a href="javascript:void(0)" class="closebtn" onclick="closeNav()">&times;</a>
            <a href="#" onclick="cambiarContenidos('<?php echo $pagina_anterior; ?>','<?php echo $nombre_proyecto; ?>'); return false;">Lista de <?php echo $nombre_proyecto; ?></a>
            <a href="#" onclick="cambiarContenidos('<?php echo $pagina_listaRegresar ?>','<?php echo $nombre_nota; ?>'); return false;">Lista de <?php echo $nombre_nota; ?></a>   
            <a href="#" onclick="monitorActividades('<?php echo $monitoreo; ?>'); return false;">Monitor de <?php echo $nombre_nota; ?></a>         
        </div>
        <div id="main_panel">
            <span style="font-size:30px;cursor:pointer" onclick="openNav()" id="open">&#9776;</span>
            <form id="frmActividad" name="frmActividad" method="POST" action="/">
                <br>
                <div class="tabs">
                    <ul>
                        <li><a href="#tabs-1">Información de <?php echo $nombre_nota; ?></a></li>
                    </ul>
                    <table style="widows: 95%">
                        <tr>
                            <td>Nombre <?php echo $nombre_nota?> <span class="obligatorio">*</span></td>
                            <td><input type="text" id="nombre" name="nombre" value="<?php echo $nombre?>"></td>
                            <td>Prioridad</td>
                            <td>
                                <select id='prioridad' name='prioridad' class="select">
                                    <option value="">Seleccione una prioridad</option>
                                    <?php
                                    $result2 = $catalogo->obtenerLista("SELECT pt.IdPrioridad, pt.Prioridad, tp.TipoPrioridad,  c.Hexadecimal
                                            FROM `c_prioridadticket` AS pt
                                            LEFT JOIN c_color AS c ON c.IdColor = pt.IdColor
                                            LEFT JOIN c_tipoprioridad AS tp ON tp.IdTipoPrioridad = pt.IdTipoPrioridad WHERE pt.Activo = 1;");
                                    while ($rs2 = mysql_fetch_array($result2)) {
                                        $s = "";
                                        if(!empty($prioridad) && $prioridad == $rs2['IdPrioridad']){
                                            $s = "selected";
                                        }
                                        echo "<option value='" . $rs2['IdPrioridad'] . "' style='background: #" . $rs2['Hexadecimal'] . ";' $s>" . $rs2['Prioridad'] . " (" . $rs2['TipoPrioridad'] . ")</option>";
                                    }
                                    ?>
                                </select>
                            </td>
                        </tr>
                        <tr>
                            <td>Tipo <span class="obligatorio">*</span></td>
                            <td>
                                <select id="tipo" name="tipo" class="select">
                                    <option value="">Selecciona tipo</option>
                                    <?php 
                                    $resultTipo = $catalogo->obtenerLista("SELECT c.IdEstado, c.Nombre FROM c_estado c INNER JOIN k_flujoestado k ON k.IdEstado = c.IdEstado WHERE k.IdFlujo = 11;");
                                    while($rs = mysql_fetch_array($resultTipo)){
                                        $s = "";
                                        if(!empty($tipo) && $tipo == $rs['IdEstado']){
                                            $s = "selected";
                                        }
                                        echo "<option value='" . $rs['IdEstado'] . "' $s>" . $rs['Nombre'] . "</option>";
                                    }
                                    ?>
                                </select>
                            </td>
                            <td>Código <?php echo $nombre_nota?></td>
                            <td><input type="text" value="<?php echo $codigo?>" id="codigo" name="codigo"></td>
                        </tr>
                        <tr>
                            <td>Relacionado a <span class="obligatorio">*</span></td>
                            <td>
                                <!--input type="text" disabled value="<!?php echo $relacionado?>"-->
                                <select class="select" id="relacionado" name="relacionado">
                                    <option value="">Selecciona un <?php echo $nombre_proyecto?></option>
                                    <?php
                                    $whereSorpresa = "";
                                    if(!empty($idTicket)){
                                        $whereSorpresa = "AND IdTicket = $idTicket";
                                    }
                                    $consultaRelacionado = "SELECT IdTicket, Nombre FROM c_ticket WHERE !ISNULL(Nombre) $whereSorpresa";
                                    $resultRelacionado = $catalogo->obtenerLista($consultaRelacionado);
                                    while($rs = mysql_fetch_array($resultRelacionado)){
                                        $s = "";
                                        if(!empty($idTicket) && $idTicket == $rs['IdTicket']){
                                            $s = "selected";
                                        }
                                        echo "<option value='" . $rs['IdTicket'] . "' $s>" . $rs['Nombre'] . "</option>";
                                    }
                                    ?>
                                </select>
                            </td>
                            <td>Asignado a <span class="obligatorio">*</span></td>
                            <td>
                                <select id="usuario" name="usuario" class="select" style="width: 200px">
                                    <option value="">Seleccione el usuario</option>
                                    <?php 
                                    $queryUsuarioOrigen = "SELECT u.IdUsuario, CONCAT(u.Nombre, ' ', u.ApellidoPaterno, ' ', u.ApellidoMaterno, ' (',p.Nombre,')') AS usuario FROM c_usuario u INNER JOIN c_puesto p ON p.IdPuesto = u.IdPuesto";
                                    $resultUsuarioOrigen = $catalogo->obtenerLista($queryUsuarioOrigen);
                                    while($rs = mysql_fetch_array($resultUsuarioOrigen)){
                                        $s = "";
                                        if(!empty($usuario) && $usuario == $rs['IdUsuario']){
                                            $s = "selected";
                                        }
                                        echo "<option value='" . $rs['IdUsuario'] . "' $s>" . $rs['usuario'] . "</option>";
                                    }
                                    ?>
                                </select>
                            </td>
                        </tr>
                        <tr>
                            <td>Estado <span class="obligatorio">*</span></td>
                            <td>
                                <select id="estado" name="estado" class="select">
                                    <option value="">Seleccione el estado</option>
                                    <?php
                                    $resultEstado = $catalogo->obtenerLista("SELECT IdEstadoNota, Nombre FROM c_estadonota;");
                                    while($rs = mysql_fetch_array($resultEstado)){
                                        $s = "";
                                        if(!empty($estado) && $estado == $rs['IdEstadoNota']){
                                            $s = "selected";
                                        }
                                        echo "<option value='" . $rs['IdEstadoNota'] . "' $s>" . $rs['Nombre'] . "</option>";
                                    }
                                    ?>
                                </select>
                            </td>
                            <td></td>
                            <td></td>
                        </tr>
                    </table>
                </div>
                <br>
                <div class="tabs">
                    <ul>
                        <li><a href="#tabs-1">Información específica</a></li>
                    </ul>
                    <table style="width:100%">
                        <tr>
                            <td>Progreso (%)</td>
                            <td><input type="text" value="<?php echo $progreso?>" id="amount" name="amount" readonly style="border:0; color:#f6931f; font-weight:bold;"></td>
                            <td colspan="2"><div id="slider"></div></td>
                        </tr>
                        <tr>
                            <td>Horas trabajadas</td>
                            <td><input type="text" id="horasT" name="horasT" value="<?php echo $horasTrabajadas?>"></td>
                            <td>Fecha de Inicio<span class="obligatorio">*</span></td>
                            <td><input type="text" id="fechaI" name="fechaI" value="<?php echo $fechaInicio?>" class="fecha"></td>
                        </tr>
                        <tr>
                            <td>Fecha de fin</td>
                            <td><input type="text" id="fechaF" name="fechaF" value="<?php echo $fechaFin?>" class="fecha"></td>
                            <td colspan="2"></td>
                        </tr>
                    </table>
                </div>
                <br>
                <div class="tabs">
                    <ul>
                        <li><a href="#tabs-1">Información específica</a></li>
                    </ul>
                    <table style="width: 95%">
                        <tr>
                            <td>Descripción</td>
                            <td>
                                <textarea id="descripcion" name="descripcion" cols="30" rows="6" style="resize: none"><?php echo $descripcion?></textarea>
                            </td>
                        </tr>
                    </table>
                </div>
                <br>
                <input type = "submit" id = "botonGuardar" name = "botonGuardar" class = "boton" value = "Guardar"/>                    
                <input type = "submit" class = "boton" value = "Cancelar" onclick = "cambiarContenidos('<?php echo $pagina_listaRegresar; ?>'); return false;"/>
                <input type="hidden" id="idTicket" name="idTicket" value="<?php echo $idTicket?>">
                <input type="hidden" id="id" name="id" value="<?php echo $id?>">
                <input type="hidden" id="paginaExito" value="<?php echo $pagina_listaRegresar?>">
            </form>
        </div>
    </body>
</html>