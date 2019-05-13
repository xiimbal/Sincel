<?php
session_start();

if (!isset($_SESSION['user']) || $_SESSION['user'] == "") {
    header("Location: index.php");
}

include_once("../WEB-INF/Classes/Catalogo.class.php");
include_once("../WEB-INF/Classes/PermisosSubMenu.class.php");

$permisos_grid = new PermisosSubMenu();
$same_page = "viajes/lista_actualizar_plantilla.php";
$permisos_grid->getPermisosSubmenu($_SESSION['idUsuario'], $same_page);


$controlador = "WEB-INF/Controllers/Viajes/Controller_Plantilla.php";

$cabeceras = array("No. Plantilla","Fecha / Hora", "Viajes", "Actualizar/Autorizar","Estatus","", "", "");
$columnas = array("Fecha / Hora", "Viajes" , "Actualizar/Autorizar", "Estatus");
$alta = "viajes/lista_autorizar_plantilla.php";

$where = "";

$filtroCampania = "";
if (isset($_POST['CampaniaFiltro']) && $_POST['CampaniaFiltro'] != "" && $_POST['CampaniaFiltro'] != 0) {
    $filtroCampania = $_POST['CampaniaFiltro'];
    $where = " WHERE cp.idCampania='$filtroCampania' ";
}

$filtroTurno = "";
if (isset($_POST['TurnoFiltro']) && $_POST['TurnoFiltro'] != "" && $_POST['TurnoFiltro'] != 0) {
    $filtroTurno = $_POST['TurnoFiltro'];
    if ($where == "") {
        $where .= " WHERE cp.idTurno='$filtroTurno' ";
    } else {
        $where .= " AND cp.idTurno='$filtroTurno' ";
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
            <br/><br/>
            <table style="width: 95%;">
                <tr>
                    <td style="width: 10%">Campaña</td>
                    <td style="width: 15%">
                        <select id="CampaniaFiltro" name="CampaniaFiltro">
                            <option value="0">Todas las Campaña</option>
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
                        <select id="TurnoFiltro" name="TurnoFiltro">
                            <option value="0">Todos los Turno</option>
                            <?php
                            $catalogo = new Catalogo();
                            $queryTurno = $catalogo->getListaAlta("c_turno", "descripcion");
                            while ($rs = mysql_fetch_array($queryTurno)) {
                                $s = "";
                                if ($filtroTurno != "" && $filtroTurno == $rs['idTurno'])
                                    $s = "selected";
                                echo "<option value='" . $rs['idTurno'] . "' $s>" . $rs['descripcion'] . "</option>";
                            }
                            ?>
                        </select>
                    </td>
                    <td>
                        <input type="button" id="mostrar_componentes" name="mostrar_componentes" value="Mostrar" class="button" 
                               onclick="mostrarCampaniaTurno('<?php echo $same_page ?>', 'CampaniaFiltro', 'TurnoFiltro');"/>
                    </td>
                </tr>   
            </table>
            <?php if ($permisos_grid->getAlta()) { ?>
                <img class="imagenMouse" src="resources/images/add.png" title="Nuevo" onclick='cambiarContenidos("<?php echo $alta; ?>");' style="float: right; cursor: pointer;" />  
            <?php } ?>
            <br/><br/><br/>
<?php if(isset($_POST['mostrar'])){   ?>
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
                    $query = $catalogo->obtenerLista("SELECT cp.idPlantilla, cp.idTicket, cp.Fecha AS Fecha, cp.Hora, ct.descripcion, ca.Descripcion, cp.Estatus, cp.Activo FROM c_plantilla AS cp
                                                      LEFT JOIN c_turno AS ct ON cp.idTurno=ct.idTurno LEFT JOIN c_area AS ca ON cp.idCampania=ca.IdArea
                                                      $where
                                                      ORDER BY cp.Fecha ASC");

                    while ($rs = mysql_fetch_array($query)) {
                        echo "<tr>";
                        echo "<td width=\"2%\" align=\"center\" scope=\"row\">" . $rs['idPlantilla'] . "</td>";
                        echo "<td width=\"2%\" align=\"center\" scope=\"row\">" . $rs['Fecha'] ." ". $rs['Hora'] ."</td>";
                        echo "<td width=\"2%\" align=\"center\" scope=\"row\">".$rs['Descripcion']." / ". $rs['descripcion'] . "</td>";

                            $catalogo = new Catalogo();
                            $notas=0;
                            $consul = $catalogo->obtenerLista("SELECT kpa.IdTicket FROM k_plantilla_asistencia kpa INNER JOIN k_plantilla kp ON kp.idK_Plantilla=kpa.idK_Plantilla
                                                                 WHERE kp.idPlantilla=".$rs['idPlantilla']." AND kpa.IdTicket != 'NULL';");
                            while ($rste = mysql_fetch_array($consul)) {
                                $catalogo2 = new Catalogo();
                                $result = $catalogo2->obtenerLista("SELECT IdNotaTicket FROM c_notaticket WHERE IdTicket = ".$rste['IdTicket']." AND IdEstatusAtencion = 51;");
                                if(mysql_num_rows($result) > 0){
                                    $notas++;
                                }
                            }
                        ?>
                    <td width="2%" align="center" scope="row" 
                        <?php 
                            if ($permisos_grid->getModificar()) { if($rs['Estatus']==0){$boton="Actualizar"; $titulo="Actualizar Plantilla"; echo ">";}else{if($rs['Estatus']==1){$boton="Autorizar"; $titulo="Autorizar Plantilla"; echo "bgcolor =\"#E3DBCD\">";}else{if($rs['Estatus']==2){if($notas>0){$boton="Detalle"; $titulo="Consulta Detallada de Plantilla"; echo ">";}else{$boton="Desautorizar"; $titulo="Desautorizar Plantilla"; echo ">";}}}}
                            //<input type="button" value ="Validar" onclick='cambiarContenidos("<?php echo "viajes/lista_autorizar_plantilla.php"; ");' style="float: left; cursor: pointer;" class="boton"/> ?>
                                <?php //if($rs['Estatus']==2){echo "<input type=\"button\" value ='".$boton."' onclick=\"cambiarContenidos('viajes/lista_actualizar_plantilla.php?idactu=" . $rs['idPlantilla'] . "', '".$titulo."'); return false;\" style=\"float: center; cursor: pointer;\" class=\"boton\"/>";}
                            echo "<input type=\"button\" value ='".$boton."' onclick=\"cambiarContenidos('viajes/lista_autorizar_plantilla.php?idactu=" . $rs['idPlantilla'] . "', '".$titulo."'); return false;\" style=\"float: center; cursor: pointer;\" class=\"boton\"/>"; ?>  
                        <?php /*echo $rs['Estatus'];*/ }?>
                    </td>
                    <?php

                    if ($rs['Activo'] == 1) {
                        echo "<td width=\"2%\" align='center' scope='row'>Activo</td>";
                    } else {
                        echo "<td width=\"2%\" align='center' scope='row'>Inactivo</td>";
                    }
                    ?>
                    <td width="2%" align='center' scope='row'>       
                        <?php if($rs['Estatus']==1){if ($permisos_grid->getModificar()) { ?>
                            <a href='#' onclick='editarRegistroComponentes("<?php echo $alta; ?>", "<?php echo $rs['idPlantilla']; ?>");
                                return false;' title='Editar Registro' >
                                <img src="resources/images/Modify.png"/>
                            </a>
                        <?php } } ?>
                    </td>
                    <td width="2%" align='center' scope='row'>       
                        <?php if($notas==0){if ($permisos_grid->getBaja()) { ?>
                            <a href='#' onclick='eliminarRegistroPlantilla("<?php echo $controlador . "?idelim=" . $rs['idPlantilla']; ?>", "<?php echo $filtroCampania;?>", "<?php echo $filtroTurno;?>","<?php echo $same_page; ?>"); return false;' 
                               title='Eliminar Registro' ><img src="resources/images/Erase.png"/>
                            </a>
                        <?php }} ?>
                    </td>
                    <?php
                    echo "</tr>";
                }
                ?>
                </tbody>
            </table>
            <?php }  ?>
        </div>
    </body>
</html>            


