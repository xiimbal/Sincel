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
<?php if ($permisos_grid->getAlta()): ?>
    <img class="imagenMouse" src="resources/images/add.png" title="Nuevo" onclick='cambiarContenidos("ventas/NuevaPrecioABC.php", "Precios ABC");' style="float: right; cursor: pointer;" />  
<?php endif; ?>



<div class="table-responsive">
    <table class="table">
        <thead class="thead-dark">
            <tr>
                <?php
                    foreach ($cabeceras as $cabecera) echo "<th>".$cabecera."</th>";
                    echo "<th></th>";                        
                ?>
            </tr>
        </thead>
        <?php
            $catalogo = new Catalogo();
            $query = $catalogo->obtenerLista("SELECT pabc.Id_precio_abc, pabc.Precio_A, pabc.Precio_B, pabc.Precio_C, (CASE WHEN !ISNULL(e.NoParte) THEN 'Equipo' ELSE tc.Nombre END) AS Tipo, (CASE WHEN !ISNULL(e.NoParte) THEN CONCAT(e.Modelo,' / ',e.NoParte,' / ',e.Descripcion) ELSE CONCAT(c.Modelo,' / ',c.NoParte,' / ',c.Descripcion) END) AS Modelo, a.nombre_almacen FROM `c_precios_abc` AS pabc LEFT JOIN c_equipo AS e ON e.NoParte = pabc.NoParteEquipo LEFT JOIN c_componente AS c ON c.NoParte = pabc.NoParteComponente LEFT JOIN c_tipocomponente AS tc ON tc.IdTipoComponente = c.IdTipoComponente LEFT JOIN c_almacen AS a ON pabc.IdAlmacen = a.id_almacen WHERE (!ISNULL(e.NoParte) AND e.Activo = 1) OR (!ISNULL(c.NoParte) AND c.Activo = 1);");
            while ($rs = mysql_fetch_array($query)) {
                echo "<tr class='table__line'>
                        <td>" . $rs['Modelo'] . "</td>
                        <td>" . $rs['Tipo'] . "</td>
                        <td>$" . number_format($rs['Precio_A']) . "</td>
                        <td>$" . number_format($rs['Precio_B']) . "</td>
                        <td>$" . number_format($rs['Precio_C']) . "</td>
                        <td>" . $rs['nombre_almacen'] . "</td>" .
                        ($permisos_grid->getModificar()
                            ? "<td>
                                <a href='#' onclick='cambiarContenidos(\"ventas/EditarPrecioABC.php?id=" . $rs['Id_precio_abc'] . "\", \"Editar Precio ABC\"); return false;' title='Editar'>
                                    <i class='fal fa-file-edit'></i>
                                </a>
                            </td>"
                            : "<td></td>") . 
                    "</tr>";
            }
        ?>
        
    </table>
</div>