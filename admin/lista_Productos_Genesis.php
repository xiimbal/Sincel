<?php
session_start();

if (!isset($_SESSION['user']) || $_SESSION['user'] == "") {
    header("Location: index.php");
}
//include_once("../WEB-INF/Classes/PartesDelComponenteC.class.php");
include_once("../WEB-INF/Classes/Catalogo.class.php");
$alta = "admin/alta_Producto_Genesis.php";
$controlador ="WEB-INF/Controllers/Controller_Eliminar_Prod_Genesis.php";
$exito="admin/lista_Productos_Genesis.php";
$cabeceras = array("Producto","Precio","","");
?>
<!DOCTYPE html>
<html lang="es">
    <head>
        <script type="text/javascript" language="javascript" src="resources/js/paginas/lista_productos_abc.js"></script> 
    </head>
    <body>
        <div class="principal">
            <img class="imagenMouse" src="resources/images/add.png" title="Nuevo" onclick='cambiarContenidos("<?php echo $alta; ?>");' style="float: right; cursor: pointer;" />  
            <br/><br/><br/>
            <table id="tabla_productos" class="tabla_datos" style="width: 100%;">
                <thead>
                    <tr>
                        <?php
                        foreach ($cabeceras as $a) {
                            echo "<th width=\"2%\" align=\"center\" scope=\"col\">" . $a . "</th>";
                        }
                        ?>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $catalogo = new Catalogo();
                    $query = $catalogo->obtenerLista("SELECT
	c_productos_genesis.Nombre AS Nombre,
	c_productos_genesis.Id_Producto_Genesis AS ID,
	c_prod_pre_genesis.Nombre AS Precio
FROM
	c_productos_genesis
INNER JOIN c_prod_pre_genesis ON c_productos_genesis.Id_Precio=c_prod_pre_genesis.Id_Precio
ORDER BY Nombre");
                    while ($rs = mysql_fetch_array($query)) {
                        echo "<tr>";
                        echo "<td align='center' scope='row'>" . $rs['Nombre'] ."</td>";
                        echo "<td align='center' scope='row'>" . $rs['Precio'] . "</td>";
                        ?>
                    <td align='center' scope='row'> <a href='#' onclick='editarRegistro("<?php echo $alta; ?>", "<?php echo $rs['ID']; ?>");
                        return false;' title='Editar Registro' ><img src="resources/images/Modify.png"/></a></td>

                    <td align='center' scope='row'> <a href='#' onclick='eliminarRegistroProv("<?php echo $controlador."?id=".$rs['ID'] ?>","","<?php echo $exito ?>");return false;'><img src="resources/images/Erase.png"/></a> </td> 
                        <?php
                        echo "</tr>";
                    }
                    ?>
                </tbody>
            </table>
        </div>
    </body>
</html>