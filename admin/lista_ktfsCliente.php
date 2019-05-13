<?php
session_start();

if (!isset($_SESSION['user']) || $_SESSION['user'] == "") {
    header("Location: index.php");
}
//include_once("../WEB-INF/Classes/PartesDelComponenteC.class.php");
include_once("../WEB-INF/Classes/Catalogo.class.php");
include_once("../WEB-INF/Classes/PermisosSubMenu.class.php");
$permisos_grid = new PermisosSubMenu();
$same_page = "admin/lista_ktfsCliente.php";
$permisos_grid->getPermisosSubmenu($_SESSION['idUsuario'], $same_page);

$controlador = $_SESSION['ruta_controler'] . "Controler_TFSCliente.php";
$alta = "admin/alta_tfsCliente.php";

?>
<!DOCTYPE html>
<html lang="es">
    <head>
        <script type="text/javascript" language="javascript" src="resources/js/paginas/lista_partesDelComponente.js"></script> 
    </head>
    <body>
        <div class="principal">
            <?php if($permisos_grid->getAlta()){ ?>
            <img class="imagenMouse" src="resources/images/add.png" title="Nuevo" onclick='cambiarContenidos("<?php echo $alta; ?>");' style="float: right; cursor: pointer;" />  
            <?php } ?>
            <br/><br/><br/>
            <table id="tPartesDelEq" class="tabla_datos" style="width: 100%;">
                <thead>
                    <tr>
                        <td align="center">Usuario</td><td align="center">Cliente</td><td></td><td></td>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $catalogo = new Catalogo();
                    $query = $catalogo->obtenerLista("SELECT c.ClaveCliente,c.NombreRazonSocial,c.RFC,u.IdUsuario,u.Nombre,u.ApellidoPaterno
                                                            FROM k_tfscliente tfs,c_cliente c,c_usuario u
                                                            WHERE tfs.IdUsuario=u.IdUsuario
                                                            AND tfs.Tipo=1
                                                            AND tfs.ClaveCliente=c.ClaveCliente");
                    while ($rs = mysql_fetch_array($query)) {
                        echo "<tr>";
                        echo "<td align='center' scope='row'>" . $rs['Nombre'] . " " . $rs['ApellidoPaterno'] . "</td>";
                        echo "<td align='center' scope='row'>" . $rs['NombreRazonSocial'] . "</td>";
                        ?>
                    <td align='center' scope='row'> 
                        <?php if($permisos_grid->getModificar()){ ?>
                        <a href='#' onclick='editarRegistroProv("<?php echo $alta; ?>", "<?php echo $rs['IdUsuario']; ?>", "<?php echo $rs['ClaveCliente']; ?>");
                        return false;' title='Editar Registro' ><img src="resources/images/Modify.png"/></a>
                        <?php } ?>
                    </td>

                    <td align='center' scope='row'> 
                        <?php if($permisos_grid->getBaja()){ ?>
                        <a href='#' onclick='eliminarRegistroProv("<?php echo $controlador . "?id=" . $rs['IdUsuario'] . "&id2=" . $rs['ClaveCliente'] ?>", "<?php echo $rs['IdUsuario']; ?>", "<?php echo $same_page; ?>");
                        return false;'><img src="resources/images/Erase.png"/></a> 
                        <?php } ?>
                    </td> 
                        <?php
                        echo "</tr>";
                    }
                    ?>
                </tbody>
            </table>
        </div>
    </body>
</html>

