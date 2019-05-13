<?php
session_start();

if (!isset($_SESSION['user']) || $_SESSION['user'] == "") {
    header("Location: index.php");
}

include_once("../WEB-INF/Classes/PermisosSubMenu.class.php");

$permisos_grid = new PermisosSubMenu();
$same_page = "Bancos/lista_bancos.php";
$permisos_grid->getPermisosSubmenu($_SESSION['idUsuario'], $same_page);

$cabeceras = array("Banco", "Descripcion", "Estatus","", "");
?>
<script type="text/javascript" language="javascript" src="resources/js/paginas/catalogos/lista_bancos.js"></script>
<?php if ($permisos_grid->getAlta()) { ?>
    <img class="imagenMouse" src="resources/images/add.png" title="Nuevo" onclick='cambiarContenidos("Bancos/alta_banco.php", "Nuevo Banco");' style="float: right; cursor: pointer;" />  
<?php } ?>
<br/><br/><br/>

<table id="tbanco">
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
        $query = $catalogo->obtenerLista("SELECT Nombre, Descripcion, IdBanco ,IF(Activo=1,'Activo','Inactivo') AS Activo FROM `c_banco`;");
        while ($rs = mysql_fetch_array($query)) {
            echo "<tr>";
            echo "<td width=\"2%\" align=\"center\" scope=\"row\">" . $rs['Nombre'] . "</td>";
            echo "<td width=\"2%\" align=\"center\" scope=\"row\">" . $rs['Descripcion'] . "</td>";
            echo "<td width=\"2%\" align=\"center\" scope=\"row\">" . $rs['Activo'] . "</td>";
            if ($permisos_grid->getModificar()) {
                echo "<td width=\"2%\" align=\"center\" scope=\"row\"><a href='#' onclick=\"cambiarContenidos('Bancos/alta_banco.php?id=" . $rs['IdBanco'] . "', 'Editar Banco');
                        return false;\" title='Editar'><img src=\"resources/images/Modify.png\" width=\"24\" height=\"24\"/></a></td>";
            } else {
                echo "<td width=\"2%\" align=\"center\" scope=\"row\"></td>";
            }
            if ($permisos_grid->getBaja()) {
                echo "<td width=\"2%\" align=\"center\" scope=\"row\"><a href='#' onclick=\"eliminarBanco('".$rs['IdBanco']."');
                        return false;\" title='Eliminar'><img src=\"resources/images/Erase.png\" width=\"24\" height=\"24\"/></a></td>";
            } else {
                echo "<td width=\"2%\" align=\"center\" scope=\"row\"></td>";
            }
            echo "</tr>";
        }
        ?>
    </tbody>

</table>

