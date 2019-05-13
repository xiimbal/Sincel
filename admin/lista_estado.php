<?php
session_start();

if (!isset($_SESSION['user']) || $_SESSION['user'] == "") {
    header("Location: index.php");
}

include_once("../WEB-INF/Classes/Catalogo.class.php");
include_once("../WEB-INF/Classes/PermisosSubMenu.class.php");
$permisos_grid = new PermisosSubMenu();
$same_page = "admin/lista_estado.php";
$permisos_grid->getPermisosSubmenu($_SESSION['idUsuario'], $same_page);

$controlador = $_SESSION['ruta_controler'] . "Controler_Estado.php";

$cabeceras = array("Estado", "Área", "");
$columnas = array("Nombre", "Descripcion", "IdEstado");
$tabla = "c_estado";
$order_by = "Nombre";
$alta = "admin/alta_estado.php";

?>
<!DOCTYPE html>
<html lang="es">
    <head>
        <script type="text/javascript" language="javascript" src="resources/js/paginas/lista.js"></script>
    </head>
    <body>
        <div class="principal">
            <?php if($permisos_grid->getAlta()){ ?>
            <img class="imagenMouse" src="resources/images/add.png" title="Nuevo" onclick='cambiarContenidos("<?php echo $alta; ?>");' style="float: right; cursor: pointer;" />  
            <?php } ?>
            <br/><br/><br/>
            <table id="tAlmacen" class="tabla_datos" style="width: 100%">
                <thead>
                    <tr>
                        <td style="text-align: center;">Estado</td><td style="text-align: center;">Area</td><td style="text-align: center;">Estatus</td><td></td><td></td>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    /* Inicializamos la clase */
                    $catalogo = new Catalogo();
                    $query = $catalogo->obtenerLista("SELECT e.IdEstado,e.Nombre,a.IdArea,a.Descripcion,kf.IdKFlujo,kf.IdFlujo,e.Activo
                    FROM c_estado e LEFT JOIN c_area a ON e.IdArea=a.IdArea LEFT JOIN k_flujoestado kf ON e.IdEstado=kf.IdEstado and kf.IdFlujo=6 ORDER BY e.Nombre ASC");
                    while ($rs = mysql_fetch_array($query)) {
                        echo "<tr>";
                        echo "<td align='center' scope='row'>" . $rs['Nombre'] . "</td>";
                        echo "<td align='center' scope='row'>" . $rs['Descripcion'] . "</td>";
                         if ($rs['Activo'] == 1)
                           echo "<td align='center' scope='row'>Activo</td>";
                        else
                           echo "<td align='center' scope='row'>Inactivo</td>";
                        ?>
                        <td align='center' scope='row'>
                            <?php if($permisos_grid->getModificar()){ ?>
                            <a href='#' onclick='editarRegistro("<?php echo $alta; ?>", "<?php echo $rs['IdEstado']; ?>");
                            return false;' title='Editar Registro' >
                                <img src="resources/images/Modify.png"/>
                            </a>
                            <?php } ?>
                        </td>

                        <td align='center' scope='row'> 
                            <?php if($permisos_grid->getBaja()){ ?>
                            <a href='#' onclick='eliminarRegistro("<?php echo $controlador . "?id=" . $rs['IdEstado'] . "&id2=" . $rs['IdKFlujo']; ?>", "<?php echo $same_page; ?>");
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