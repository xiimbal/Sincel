<?php

session_start();

if (!isset($_SESSION['user']) || $_SESSION['user'] == "") {
    header("Location: index.php");
}

include_once("../WEB-INF/Classes/Catalogo.class.php");
include_once("../WEB-INF/Classes/Serie.class.php");
include_once("../WEB-INF/Classes/PermisosSubMenu.class.php");
$permisos_grid = new PermisosSubMenu();
$same_page = "catalogos/lista_series.php";
$permisos_grid->getPermisosSubmenu($_SESSION['idUsuario'], $same_page);

$cabeceras = array("Prefijo", "Folio de Inicio", "Activo", "", "");
?>
<script type="text/javascript" language="javascript" src="resources/js/paginas/catalogos/lista_series.js"></script>
<?php if ($permisos_grid->getAlta()) { ?>
    <img class="imagenMouse" src="resources/images/add.png" title="Nuevo" onclick='cambiarContenidos("catalogos/alta_serie.php", "Nueva Serie");' style="float: right; cursor: pointer;" />  
<?php } ?>
<br/><br/><br/>
<table id="tserie">
    <thead>
        <tr>
            <?php
            for ($i = 0; $i < (count($cabeceras)); $i++) {
                echo "<th width=\"2%\" align=\"center\" scope=\"col\">" . $cabeceras[$i] . "</th>";
            }
            ?>                        
        </tr>
    </thead>
    <tbody>
        <?php
        $serieClase = new Serie();
        $query = $serieClase->buscarTodo();
        while ($rs = mysql_fetch_array($query)) {
            echo "<tr>";
            echo "<td width=\"2%\" align=\"center\" scope=\"row\">" . $rs['Prefijo'] . "</td>";
            echo "<td width=\"2%\" align=\"center\" scope=\"row\">" . $rs['FolioInicio'] . "</td>";
            if($rs['Activo'] == 1){
                echo "<td width=\"2%\" align=\"center\" scope=\"row\">Activo</td>";
            }else{
                echo "<td width=\"2%\" align=\"center\" scope=\"row\">Inactivo</td>";
            }
            if ($permisos_grid->getModificar()) {
                echo "<td width=\"2%\" align=\"center\" scope=\"row\"><a href='#' onclick=\"cambiarContenidos('catalogos/alta_serie.php?id=" . $rs['IdSerie'] . "', 'Editar Serie');
                        return false;\" title='Editar'><img src=\"resources/images/Modify.png\" width=\"24\" height=\"24\"/></a></td>";
            } else {
                echo "<td width=\"2%\" align=\"center\" scope=\"row\"></td>";
            }
            if ($permisos_grid->getBaja()) {
                echo "<td width=\"2%\" align=\"center\" scope=\"row\"><a href='#' onclick=\"eliminarSerie('".$rs['IdSerie']."');
                        return false;\" title='Eliminar'><img src=\"resources/images/Erase.png\" width=\"24\" height=\"24\"/></a></td>";
            } else {
                echo "<td width=\"2%\" align=\"center\" scope=\"row\"></td>";
            }
            echo "</tr>";
        }
        ?>
    </tbody>
</table>
    
