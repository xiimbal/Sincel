<?php
session_start();

if (!isset($_SESSION['user']) || $_SESSION['user'] == "") {
    header("Location: index.php");
}

include_once("../WEB-INF/Classes/Catalogo.class.php");
include_once("../WEB-INF/Classes/PermisosSubMenu.class.php");
$permisos_grid = new PermisosSubMenu();
$same_page = "catalogos/lista_empleados_loyalty.php";
$permisos_grid->getPermisosSubmenu($_SESSION['idUsuario'], $same_page);
$controlador = $_SESSION['ruta_controler'] . "Controler_UsuarioTurno.php";

$cabeceras = array("Id", "Usuario", "Nombre", "Correo", "Tipo de Usuario", "Estatus", "", "");
$columnas = array("Loggin", "nombre_completo", "correo", "puesto", "idUsuario");
$tabla = "c_usuario";
$order_by = "loggin";
$alta = "catalogos/alta_empleados_loyalty.php";
?>
<!DOCTYPE html>
<html lang="es">
    <head>
        <script type="text/javascript" language="javascript" src="resources/js/paginas/lista.js"></script>
    </head>
    <body>
        <div class="principal">
            <?php if ($permisos_grid->getAlta()) { ?>
                <img class="imagenMouse" src="resources/images/add.png" title="Nuevo" onclick='cambiarContenidos("<?php echo $alta; ?>");' style="float: right; cursor: pointer;" />  
            <?php } ?>
            <br/><br/><br/>
            <table id="tAlmacen" class="tabla_datos">
                <thead>
                    <tr>
                        <?php
                        for ($i = 0; $i < (count($cabeceras) - 2); $i++) {
                            echo "<th width=\"2%\" align=\"center\" scope=\"col\">" . $cabeceras[$i] . "</th>";
                        }
                        echo "<th width=\"2%\" align=\"center\" scope=\"col\"></th>";
                        echo "<th width=\"2%\" align=\"center\" scope=\"col\"></th>";
                        ?>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    /* Inicializamos la clase */
                    $catalogo = new Catalogo();
                    $query = $catalogo->obtenerLista("SELECT idUsuario, Loggin, CONCAT(usu.Nombre,' ',usu.ApellidoPaterno,' ',ApellidoMaterno) AS nombre_completo,
                                                        correo, per.Nombre AS puesto,(SELECT CASE WHEN usu.Activo = 1 THEN 'Activo' ELSE 'Inactivo' END) AS Activo
							FROM c_usuario AS usu ,c_puesto AS per 
							WHERE  per.IdPuesto = usu.IdPuesto AND per.IdPuesto!=14 AND usu.IdPuesto=100 ORDER BY usu.Nombre ASC;");
                    while ($rs = mysql_fetch_array($query)) {
                        echo "<tr>";
                        echo "<td align='center' scope='row'>" . $rs["idUsuario"] . "</td>";
                        echo "<td align='center' scope='row'>" . $rs["Loggin"] . "</td>";
                        echo "<td align='center' scope='row'>" . $rs["nombre_completo"] . "</td>";
                        echo "<td align='center' scope='row'>" . $rs["correo"] . "</td>";
                        echo "<td align='center' scope='row'>" . $rs["puesto"] . "</td>";
                        echo "<td align='center' scope='row'>" . $rs['Activo'] . "</td>";
                        ?>
                    <td align='center' scope='row'>
                        <?php if ($permisos_grid->getModificar()) { ?>
                            <a href='#' onclick='editarRegistro("<?php echo $alta; ?>", "<?php echo $rs[$columnas[count($columnas) - 1]]; ?>");
                                    return false;' title='Editar Registro' >
                                <img src="resources/images/Modify.png"/>
                            </a>
                        <?php } ?>
                    </td>
                    <?php
                    //if ($_SESSION['idUsuario'] != $rs[$columnas[count($columnas) - 1]]) {
                    ?>
                    <td align='center' scope='row'> 
                        <?php if ($permisos_grid->getBaja()) { ?>
                            <a href='#' onclick='eliminarRegistro("<?php echo $controlador . "?id=" . $rs[$columnas[count($columnas) - 1]]; ?>", "<?php echo $same_page; ?>");
                                    return false;'><img src="resources/images/Erase.png"/></a> 
                           <?php } ?>
                    </td>                                        
                    <?php
                    //} else {
                    //    echo "<td></td>";
                    //}
                    echo "</tr>";
                }
                ?>
                </tbody>
            </table>
        </div>
    </body>
</html>
