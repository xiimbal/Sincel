<?php
session_start();

if (!isset($_SESSION['user']) || $_SESSION['user'] == "") {
    header("Location: index.php");
}

include_once("../WEB-INF/Classes/Catalogo.class.php");
include_once("../WEB-INF/Classes/PermisosSubMenu.class.php");

$permisos_grid = new PermisosSubMenu();
$same_page = "viajes/lista_especial.php";
$permisos_grid->getPermisosSubmenu($_SESSION['idUsuario'], $same_page);


$controlador = $_SESSION['ruta_controler'] . "Viajes/Controller_AutorizarEspecial.php";

$cabeceras = array("Cita", "No. de empleado", "Usuario", "Campaña", "Turno", "Viaje", "Servicio", "", "", "");
$columnas = array("Empleado", "Nombre", "Campania", "Turno", "Viaje", "Fecha");
$alta = "viajes/alta_autoriza_especial.php";

$where = "";
$whereFecha = "";

$filtroCampania = "";
if (isset($_POST['CampaniaFiltro']) && $_POST['CampaniaFiltro'] != "" && $_POST['CampaniaFiltro'] != 0) {
    $filtroCampania = $_POST['CampaniaFiltro'];
    if ($where == NULL) {
        $where = " WHERE ce.idCampania='$filtroCampania' ";
    } else {
        $where .= " AND ce.idCampania='$filtroCampania' ";
    }
}

$filtroTurno = "";
if (isset($_POST['TurnoFiltro']) && $_POST['TurnoFiltro'] != "" && $_POST['TurnoFiltro'] != 0) {
    $filtroTurno = $_POST['TurnoFiltro'];
    if ($where == NULL) {
        $where = " WHERE ce.idTurno='$filtroTurno' ";
    } else {
        $where .= " AND ce.idTurno='$filtroTurno' ";
    }
}

$filtroEmpleado = "";
if (isset($_POST['EmpleadoFiltro']) && $_POST['EmpleadoFiltro'] > 0) {
    $filtroEmpleado = $_POST['EmpleadoFiltro'];
    if ($where === "") {
        $where = " WHERE ce.idUsuario='" . $filtroEmpleado . "' ";
    } else {
        $where .= " AND ce.idUsuario='" . $filtroEmpleado . "' ";
    }
}

$filtroNombre = "";
if (isset($_POST['NombreFiltro']) && $_POST['NombreFiltro'] != "") {
    $filtroNombre = $_POST['NombreFiltro'];
    if ($where == NULL) {
        $where = " WHERE cu.Nombre LIKE '%$filtroNombre%' ";
    } else {
        $where .= " AND cu.Nombre LIKE '%$filtroNombre%' ";
    }
}

$filtroApellidoP = "";
if (isset($_POST['ApellidoPFiltro']) && $_POST['ApellidoPFiltro'] != "") {
    $filtroApellidoP = $_POST['ApellidoPFiltro'];
    if ($where == NULL) {
        $where = " WHERE cu.ApellidoPaterno LIKE '%$filtroApellidoP%' ";
    } else {
        $where .= " AND cu.ApellidoPaterno LIKE '%$filtroApellidoP%' ";
    }
}

$filtroApellidoM = "";
if (isset($_POST['ApellidoMFiltro']) && $_POST['ApellidoMFiltro'] != "") {
    $filtroApellidoM = $_POST['ApellidoMFiltro'];
    if ($where == NULL) {
        $where = " WHERE cu.ApellidoMaterno LIKE '%$filtroApellidoM%' ";
    } else {
        $where .= " AND cu.ApellidoMaterno LIKE '%$filtroApellidoM%' ";
    }
}
?>

<!DOCTYPE html>
<html lang = "es">
    <head>
        <script type="text/javascript" language="javascript" src="resources/js/paginas/lista.js"></script>
    </head>
    <body>
        <div class="principal">            
            <?php if ($permisos_grid->getAlta()) { ?>
                <img class="imagenMouse" src="resources/images/add.png" title="Nuevo" onclick='cambiarContenidos("<?php echo $alta; ?>");' style="float: right; cursor: pointer;" />  
            <?php } ?>
            <br/><br/><br/>
            <table style="width: 95%;">
                <tr>
                    <td>No. Empleado</td>
                    <td>
                        <input type="text" id="Empleado" name="Empleado" value="<?php echo $filtroEmpleado; ?>"/>
                    </td>
                    <td>Apellido Paterno</td>
                    <td>
                        <input type="text" id="ApellidoP" name="ApellidoP" value="<?php echo $filtroApellidoP; ?>"/>
                    </td>
                    <td>Apellido Materno</td>
                    <td>
                        <input type="text" id="ApellidoM" name="ApeliidoM" value="<?php echo $filtroApellidoM; ?>"/>
                    </td>
                    <td>
                        <input type="button" id="mostrar_componentes" name="mostrar_componentes" value="Mostrar" class="button" 
                               onclick="mostrarEspecial('<?php echo $same_page ?>', 'Empleado', 'ApellidoP', 'ApellidoM', 'Nombre', 'CampaniaF', 'TurnoF');"/>
                    </td>
                </tr>
                <tr>
                    <td>Nombre</td>
                    <td>
                        <input type="text" id="Nombre" name="Nombre" value="<?php echo $filtroNombre; ?>"/>
                    </td>
                    <td style="width: 10%">Campaña</td>
                    <td style="width: 15%">
                        <select id="CampaniaF" name="CampaniaF">
                            <option value="0">Todas las Campañas</option>
                            <?php
                            $catalogo = new Catalogo();
                            $queryCampania = $catalogo->getListaAlta("c_area", "Descripcion");
                            while ($rs = mysql_fetch_array($queryCampania)) {
                                $s = "";
                                if ($filtroCampania != "" && $filtroCampania == $rs['IdArea']) {
                                    $s = "selected";
                                }
                                if (($rs['ClaveCentroCosto']) != NULL || ($rs['ClaveCentroCosto']) != "") {
                                    echo "<option value='" . $rs['IdArea'] . "' $s>" . $rs['Descripcion'] . "</option>";
                                }
                            }
                            ?>
                        </select>
                    </td>
                    <td style="width: 11.9%">Turno</td>
                    <td style="width: 15%">
                        <select id="TurnoF" name="TurnoF">
                            <option value="0">Todos los Turnos</option>
                            <?php
                            $catalogo = new Catalogo();
                            $queryTurno = $catalogo->getListaAlta("c_turno", "descripcion");
                            while ($rs = mysql_fetch_array($queryTurno)) {
                                $s = "";
                                if ($filtroTurno != "" && $filtroTurno == $rs['idTurno']) {
                                    $s = "selected";
                                }
                                echo "<option value='" . $rs['idTurno'] . "' $s>" . $rs['descripcion'] . "</option>";
                            }
                            ?>
                        </select>
                    </td>
                    <td></td>
                </tr>   
            </table>            
            <br/><br/>
            <?php
            if (!isset($_POST['mostrar'])) {
                $whereFecha = "WHERE DATE(ce.FechaHora)=DATE(NOW())";
            }
            ?>
            <table id="tAlmacen" class="tabla_datos">
                <thead>
                    <tr>
                        <?php
                        for ($i = 0; $i < (count($cabeceras) - 2); $i++) {
                            echo "<th width=\"2%\" align=\"center\" scope=\"col\">" . $cabeceras[$i] . "</th>";
                        }
                        echo "<th width=\"2%\" align=\"center\" scope=\"col\"></th>";
                        ?>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $catalogo = new Catalogo();
                    if ($where === "") {
                        $query = $catalogo->obtenerLista("SELECT ce.idTicket, ce.idEspecial, ce.idUsuario AS NoEmpleado, cu.Nombre, cu.ApellidoPaterno AS ApellidoPa,
                                                    cu.ApellidoMaterno AS ApellidoMa, ca.Descripcion AS Campania, ct.descripcion AS Turno,
                                                    CONCAT(ce.Origen,' - ',ce.Destino) AS Viaje, FechaHora FROM c_especial ce
                                                    LEFT JOIN c_usuario AS cu ON cu.IdUsuario=ce.idUsuario JOIN c_area AS ca ON ce.idCampania=ca.IdArea
                                                    JOIN c_turno AS ct ON ct.idTurno=ce.idTurno $whereFecha ORDER BY ce.idUsuario ASC");
                    } else {
                        $query = $catalogo->obtenerLista("SELECT ce.idTicket, ce.idEspecial, ce.idUsuario AS NoEmpleado, cu.Nombre, cu.ApellidoPaterno AS ApellidoPa,
                                                    cu.ApellidoMaterno AS ApellidoMa, ca.Descripcion AS Campania, ct.descripcion AS Turno,
                                                    CONCAT(ce.Origen,' - ',ce.Destino) AS Viaje, FechaHora FROM c_especial ce
                                                    LEFT JOIN c_usuario AS cu ON cu.IdUsuario=ce.idUsuario JOIN c_area AS ca ON ce.idCampania=ca.IdArea
                                                    JOIN c_turno AS ct ON ct.idTurno=ce.idTurno $where ORDER BY ce.idUsuario ASC;");
                    }

                    while ($rs = mysql_fetch_array($query)) {
                        $notas = 0;
                        $EstadoTicket = 0;
                        echo "<tr>";
                        echo "<td width=\"2%\" align=\"center\" scope=\"row\">" . $rs['FechaHora'] . "</td>";
                        echo "<td width=\"2%\" align=\"center\" scope=\"row\">" . $rs['NoEmpleado'] . "</td>";
                        echo "<td width=\"2%\" align=\"center\" scope=\"row\">" . $rs['Nombre'] . " " . $rs['ApellidoPa'] . " " . $rs['ApellidoMa'] . "</td>";
                        echo "<td width=\"2%\" align=\"center\" scope=\"row\">" . $rs['Campania'] . "</td>";
                        echo "<td width=\"2%\" align=\"center\" scope=\"row\">" . $rs['Turno'] . "</td>";
                        echo "<td width=\"2%\" align=\"center\" scope=\"row\">" . $rs['Viaje'] . "</td>";
                        echo "<td width=\"2%\" align=\"center\" scope=\"row\">" . $rs['idTicket'] . "</td>";
                        //echo "<td width=\"2%\" align=\"center\" scope=\"row\">...</td>";
                        //echo "<td width=\"2%\" align=\"center\" scope=\"row\">" . $rs['Estatus'] . "</td>";
                        if ($rs['idTicket'] != "") {
                            $catalogo = new Catalogo();
                            $queryEstadoT = $catalogo->obtenerLista("SELECT EstadoDeTicket FROM c_ticket WHERE IdTicket=" . $rs['idTicket'] . ";");
                            $rsET = mysql_fetch_array($queryEstadoT);
                            $EstadoTicket = $rsET['EstadoDeTicket'];

                            $catalogo2 = new Catalogo();
                            $result = $catalogo2->obtenerLista("SELECT IdNotaTicket FROM c_notaticket WHERE IdTicket = " . $rs['idTicket'] . " AND IdEstatusAtencion = 51 ;");
                            if (mysql_num_rows($result) > 0) {
                                $notas = 1;
                            }
                        }
                        ?>
                    <td width="2%" align="center" scope="row">
                        <?php
                        if ($notas == 1) {
                            echo "<input type=\"button\" value =\"Detalle\" onclick=\"cambiarContenidos('viajes/alta_autoriza_especial.php?id=" . $rs['idEspecial'] . "', 'Consulta Detallada de Viaje')\" style=\"float: center; cursor: pointer;\" class=\"boton\"/>";
                        } else {
                            if ($permisos_grid->getModificar()) {
                                if ($EstadoTicket == 0 || $EstadoTicket == 4) {
                                    echo "<input type=\"button\" value =\"Autorizar\" onclick=\"cambiarContenidos('viajes/alta_autoriza_especial.php?id=" . $rs['idEspecial'] . "', 'Autorizar Viaje')\" style=\"float: center; cursor: pointer;\" class=\"boton\"/>";
                                } else {
                                    if ($EstadoTicket == 3) {
                                        echo "<input type=\"button\" value =\"Desautorizar\" onclick=\"cambiarContenidos('viajes/alta_autoriza_especial.php?id=" . $rs['idEspecial'] . "', 'Desautorizar Viaje')\" style=\"float: center; cursor: pointer;\" class=\"boton\"/>";
                                    } else {
                                        echo "<input type=\"button\" value =\"Detalle\" onclick=\"cambiarContenidos('viajes/alta_autoriza_especial.php?id=" . $rs['idEspecial'] . "', 'Consulta Detallada de Viaje')\" style=\"float: center; cursor: pointer;\" class=\"boton\"/>";
                                    }
                                }
                            }
                        }
                        ?>
                    </td>
                    <td width="2%" align='center' scope='row'>
                        <?php
                        if ($notas == 0) {
                            if ($permisos_grid->getModificar()) {
                                ?>
                                <a href='#' onclick='editarRegistro("<?php echo $alta; ?>", "<?php echo $rs['idEspecial']; ?>");
                                                    return false;' title='Editar Registro' >
                                    <img src="resources/images/Modify.png"/>
                                </a>
                                <?php
                            }
                            if ($EstadoTicket == 2 || $EstadoTicket == 1 || $EstadoTicket == 3) {
                                echo "";
                            } else {
                                if ($permisos_grid->getBaja()) {
                                    ?>
                                    <a href='#' onclick='eliminarRegistroPlantilla("<?php echo $controlador . "?id=" . $rs['idEspecial']; ?>", "<?php echo $filtroCampania; ?>", "<?php echo $filtroTurno; ?>", "<?php echo $same_page; ?>");
                                                            return false;' 
                                       title='Eliminar Registro' ><img src="resources/images/Erase.png"/>
                                    </a>
                                    <?php
                                }
                            }
                        }
                        ?>
                    </td>    
                    <?php
                    /* if ($rs['Activo'] == 1){
                      echo "<td align='center' scope='row'>Activo</td>";
                      }else{
                      echo "<td align='center' scope='row'>Inactivo</td>";
                      } */
                    echo "</tr>";
                }
                ?>
                </tbody>
            </table>
        </div>
    </body>
</html>            


