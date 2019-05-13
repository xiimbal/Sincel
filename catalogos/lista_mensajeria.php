<?php
session_start();

if (!isset($_SESSION['user']) || $_SESSION['user'] == "") {
    header("Location: index.php");
}
include_once("../WEB-INF/Classes/PermisosSubMenu.class.php");
include_once("../WEB-INF/Classes/Catalogo.class.php");
$permisos_grid = new PermisosSubMenu();
$same_page = "catalogos/lista_mensajeria.php";
$permisos_grid->getPermisosSubmenu($_SESSION['idUsuario'], $same_page);
$cabeceras = array("Nombre", "Activo", "", "");
?>
<script type="text/javascript" language="javascript" src="resources/js/paginas/catalogos/lista_mensajeria.js"></script>
<?php if ($permisos_grid->getAlta()) { ?>
    <img class="imagenMouse" src="resources/images/add.png" title="Nuevo" onclick='cambiarContenidos("catalogos/Alta_mensajeria.php", "Nuevo Mensajería");' style="float: right; cursor: pointer;" />  
<?php } ?>
<br/><br/><br/>

<table id="tmensajeria">
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
        $catalogo = new Catalogo();
        $query = $catalogo->obtenerLista("SELECT Nombre,
	IF(Activo=1,'Activo','Inactivo') AS Activo,
        IdMensajeria
 FROM `c_mensajeria`;");
        while ($rs = mysql_fetch_array($query)) {
            echo "<tr>";
            echo "<td width=\"2%\" align=\"center\" scope=\"row\">" . $rs['Nombre'] . "</td>";
            echo "<td width=\"2%\" align=\"center\" scope=\"row\">" . $rs['Activo'] . "</td>";
            if ($permisos_grid->getModificar()) {
                echo "<td width=\"2%\" align=\"center\" scope=\"row\"><a href='#' onclick=\"cambiarContenidos('catalogos/Alta_mensajeria.php?id=" . $rs['IdMensajeria'] . "', 'Editar Mensajería');
                        return false;\" title='Editar'><img src=\"resources/images/Modify.png\" width=\"24\" height=\"24\"/></a></td>";
            } else {
                echo "<td width=\"2%\" align=\"center\" scope=\"row\"></td>";
            }
            if ($permisos_grid->getBaja()) {
                echo "<td width=\"2%\" align=\"center\" scope=\"row\"><a href='#' onclick=\"eliminarMensajeria('".$rs['IdMensajeria']."');
                        return false;\" title='Eliminar'><img src=\"resources/images/Erase.png\" width=\"24\" height=\"24\"/></a></td>";
            } else {
                echo "<td width=\"2%\" align=\"center\" scope=\"row\"></td>";
            }
            echo "</tr>";
        }
        ?>
    </tbody>

</table>
