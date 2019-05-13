<?php
session_start();

if (!isset($_SESSION['user']) || $_SESSION['user'] == "") {
    header("Location: ../index.php");
}
include_once("../WEB-INF/Classes/Catalogo.class.php");
include_once("../WEB-INF/Classes/PermisosSubMenu.class.php");

$permisos_grid = new PermisosSubMenu();
$same_page = "catalogos/lista_cuadrante.php";
$permisos_grid->getPermisosSubmenu($_SESSION['idUsuario'], $same_page);
$controlador = $_SESSION['ruta_controler'] . "Controller_Cuadrante.php";
$cabeceras = array("Cuadrante", "Latitud", "Longitud", "Estatus", "", "");
$alta = "catalogos/alta_cuadrante.php";
?>

<!DOCTYPE html>
<html lang="es">
    <head>
        <script type="text/javascript" language="javascript" src="resources/js/paginas/lista.js"></script>
    </head>
    <body>
        <div class="principal">
            <?php if ($permisos_grid->getAlta()) { ?>
                <img class="imagenMouse" src="resources/images/add.png" title="Nuevo" onclick='cambiarContenidos("catalogos/alta_cuadrante.php", "Nuevo Cuadrante");' style="float: right; cursor: pointer;" />  
            <?php } ?>
            <br/><br/><br/>

            <table id="tAlmacen" class="tabla_datos"  width="100%">
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
                    $query = $catalogo->obtenerLista("SELECT IdArea,Descripcion,IF(Activo=1,'Activo','Inactivo') AS Activo, Latitud, Longitud FROM `c_area` WHERE Latitud!='NULL' AND Longitud!='NULL';");
                    while ($rs = mysql_fetch_array($query)) {
                        echo "<tr>";
                        echo "<td width=\"20%\" align=\"center\" scope=\"row\">" . $rs['Descripcion'] . "</td>";
                        echo "<td width=\"20%\" align=\"center\" scope=\"row\">" . $rs['Latitud'] . "</td>";
                        echo "<td width=\"20%\" align=\"center\" scope=\"row\">" . $rs['Longitud'] . "</td>";
                        echo "<td width=\"20%\" align=\"center\" scope=\"row\">" . $rs['Activo'] . "</td>";
                        ?> 
                        <?php if ($permisos_grid->getModificar()) { ?>
                        <td align='center' scope='row'>
                            <a href='#' onclick='editarRegistro("<?php echo $alta; ?>", "<?php echo $rs["IdArea"]; ?>");
                                            return false;' title='Editar Registro' >
                                <img src="resources/images/Modify.png"/>
                            </a>
                        </td>
                        <?php
                    } else {
                        echo "<td width=\"20%\" align=\"center\" scope=\"row\"></td>";
                    }
                    ?>

                    <?php if ($permisos_grid->getBaja()) { ?>
                        <td align='center' scope='row'> 
                            <a href='#' onclick='eliminarRegistro("<?php echo $controlador . "?id=" . $rs["IdArea"]; ?>", "<?php echo $same_page; ?>");
                                            return false;'>
                                <img src="resources/images/Erase.png"/></a> 
                        </td>  
                        <?php
                    } else {
                        echo "<td width=\"20%\" align=\"center\" scope=\"row\"></td>";
                    }
                    echo "</tr>";
                }
                ?>
                </tbody>
            </table>
        </div>
    </body>
</html>