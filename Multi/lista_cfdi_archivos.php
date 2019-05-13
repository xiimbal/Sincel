<?php
session_start();
if (!isset($_SESSION['user']) || $_SESSION['user'] == "") {
    header("Location: ../index.php");
}
include_once("../WEB-INF/Classes/CFDI.class.php");
include_once("../WEB-INF/Classes/PermisosSubMenu.class.php");
$permisos_grid = new PermisosSubMenu();
$same_page = "";
$permisos_grid->getPermisosSubmenu($_SESSION['idUsuario'], $same_page);
$cfdi = new CFDI();
$query = $cfdi->getTabla();
$cabeceras = Array("Nombre", "Archivo CSD", "Key", "","");
?>
<script type="text/javascript" language="javascript" src="resources/js/paginas/Multi/multi_lista_csd.js"></script>
<button class="boton" id="regresa" name="regresar" onclick="cambiarContenidos('Multi/list_empresas.php','Empresas')">Empresas</button>
<br/><br/><br/>
<img class="imagenMouse" src="resources/images/add.png" title="Nuevo" onclick='cambiarContenidos("Multi/Alta_csd.php", "Nuevo CFDI");' style="float: right; cursor: pointer;" />  
<br/><br/><br/>
<table id="tcsd" style="max-width: 100%;">
    <thead>
        <tr>
            <?php
            for ($i = 0; $i < count($cabeceras); $i++) {
                echo "<th width=\"2%\" align=\"center\" scope=\"col\">" . $cabeceras[$i] . "</th>";
            }
            ?>                        
        </tr>
    </thead>
    <tbody>
        <?php
        while ($rs = mysql_fetch_array($query)) {
            echo "<tr>";
            echo "<td align='center' scope='row'>" . $rs['nombre'] . "</td>";
            echo "<td align='center' scope='row'>" . $rs['csd'] . "</td>";
            echo "<td align='center' scope='row'>" . $rs['archivo_key'] . "</td>";
            //echo "<td align='center' scope='row'><a href='#' onclick=\"cambiarContenidos('Multi/Alta_csd.php?id=" . $rs['id_Cfdi'] . "', 'Nuevo CFDI');\" ><img src='resources/images/Modify.png' title='Editar CSD' style='width: 32px; height: 32px;'/></a></td>";
            echo "<td align='center' scope='row'><a href='#' onclick=\"cambiarContenidos('Multi/Alta_csd.php?id=" . $rs['id_Cfdi'] . "', 'Editar CFDI');\"><img src='resources/images/Modify.png' title='Editar CFDI' style='width: 32px; height: 32px;'/></a></td>";
            echo "<td align='center' scope='row'><a href='#' onclick='EliminarCSD(" . $rs['id_Cfdi'] . ")'><img src='resources/images/Erase.png' title='Eliminar CSD' style='width: 32px; height: 32px;'/></a></td>";
            echo "</tr>";
        }
        ?>
    </tbody>
</table>
