<?php
session_start();

if (!isset($_SESSION['user']) || $_SESSION['user'] == "") {
    header("Location: index.php");
}
include_once("../WEB-INF/Classes/PermisosSubMenu.class.php");
include_once("../WEB-INF/Classes/Catalogo.class.php");
$permisos_grid = new PermisosSubMenu();
$same_page = "ventas/lista_precios_abc.php";
$permisos_grid->getPermisosSubmenu($_SESSION['idUsuario'], $same_page);
$cabeceras = array("Modelo", "Tipo", "Precio A", "Precio B", "Precio C", "Almacén" ,"");
?>
<script type="text/javascript" language="javascript" src="resources/js/paginas/ventas/lista_precio_abc.js"></script>
<?php if ($permisos_grid->getAlta()) { ?>
    <img class="imagenMouse" src="resources/images/add.png" title="Nuevo" onclick='cambiarContenidos("ventas/NuevaPrecioABC.php", "Precios ABC");' style="float: right; cursor: pointer;" />  
<?php } ?>
<br/><br/><br/>

<table id="tpreciosabc">
    <thead>
        <tr>
            <?php
            for ($i = 0; $i < (count($cabeceras) - 1); $i++) {
                echo "<th width=\"2%\" align=\"center\" scope=\"col\">" . $cabeceras[$i] . "</th>";
            }
            echo "<th width=\"2%\" align=\"center\" scope=\"col\"></th>";
            ?>                        
        </tr>
    </thead>
    <tbody>
        <?php
        $catalogo = new Catalogo();
        $query = $catalogo->obtenerLista("SELECT pabc.Id_precio_abc, pabc.Precio_A, pabc.Precio_B, pabc.Precio_C, 
            (CASE WHEN !ISNULL(e.NoParte) THEN 'Equipo' ELSE tc.Nombre END) AS Tipo,
            (CASE WHEN !ISNULL(e.NoParte) THEN
            CONCAT(e.Modelo,' / ',e.NoParte,' / ',e.Descripcion) ELSE
            CONCAT(c.Modelo,' / ',c.NoParte,' / ',c.Descripcion) END) AS Modelo,
            a.nombre_almacen
            FROM `c_precios_abc` AS pabc
            LEFT JOIN c_equipo AS e ON e.NoParte = pabc.NoParteEquipo
            LEFT JOIN c_componente AS c ON c.NoParte = pabc.NoParteComponente
            LEFT JOIN c_tipocomponente AS tc ON tc.IdTipoComponente = c.IdTipoComponente
            LEFT JOIN c_almacen AS a ON pabc.IdAlmacen = a.id_almacen
            WHERE (!ISNULL(e.NoParte) AND e.Activo = 1) OR (!ISNULL(c.NoParte) AND c.Activo = 1);");
        while ($rs = mysql_fetch_array($query)) {
            echo "<tr>";
            echo "<td width=\"2%\" align=\"center\" scope=\"row\">" . $rs['Modelo'] . "</td>";
            echo "<td width=\"2%\" align=\"center\" scope=\"row\">" . $rs['Tipo'] . "</td>";
            echo "<td width=\"2%\" align=\"center\" scope=\"row\">$" . number_format($rs['Precio_A']) . "</td>";
            echo "<td width=\"2%\" align=\"center\" scope=\"row\">$" . number_format($rs['Precio_B']) . "</td>";
            echo "<td width=\"2%\" align=\"center\" scope=\"row\">$" . number_format($rs['Precio_C']) . "</td>";
            echo "<td width=\"2%\" align=\"center\" scope=\"row\">" . $rs['nombre_almacen'] . "</td>";
            if ($permisos_grid->getModificar()) {
                echo "<td width=\"2%\" align=\"center\" scope=\"row\"><a href='#' onclick=\"cambiarContenidos('ventas/EditarPrecioABC.php?id=" . $rs['Id_precio_abc'] . "', 'Editar Precio ABC');
                        return false;\" title='Editar'><img src=\"resources/images/Modify.png\" width=\"24\" height=\"24\"/></a></td>";
            } else {
                echo "<td width=\"2%\" align=\"center\" scope=\"row\"></td>";
            }
            echo "</tr>";
        }
        ?>
    </tbody>

</table>
