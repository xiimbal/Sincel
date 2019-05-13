<?php
session_start();

if (!isset($_SESSION['user']) || $_SESSION['user'] == "") {
    header("Location: index.php");
}
//include_once("../WEB-INF/Classes/PartesDelComponenteC.class.php");
include_once("../WEB-INF/Classes/Catalogo.class.php");
$controlador = $_SESSION['ruta_controler'] . "Controler_AlmacenZona.php";
$alta = "admin/alta_almacenZona.php";
$same_page = "admin/lista_almacenZona.php";
$almacen = $_POST['id'];
$catalogo = new Catalogo();
$query = $catalogo->obtenerLista("SELECT * FROM c_almacen WHERE id_almacen='" . $almacen . "'");
if ($rs = mysql_fetch_array($query)) {
    echo "Zonas del almacén: <b>" . $rs['nombre_almacen']."</b>";
}
?>
<!DOCTYPE html>
<html lang="es">
    <head>
        <script type="text/javascript" language="javascript" src="resources/js/paginas/lista.js"></script>
        <script type="text/javascript" language="javascript" src="resources/js/paginas/alta_alamcenZona.js"></script>
    </head>
    <body>
        <div class="principal">
            <img class="imagenMouse" src="resources/images/add.png" title="Nuevo" onclick='cambiarContenidosProv("<?php echo $alta; ?>", "<?php echo $almacen ?>");' style="float: right; cursor: pointer;" />  
            <br/><br/><br/>
            <table id="tAlmacen" class="tabla_datos" style="width: 100%;">
                <thead>
                    <tr>
                        <td align="center">Zona 1</td><td></td><td></td>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $catalogo = new Catalogo();
                    $query = $catalogo->obtenerLista("SELECT az.IdAlmacen,az.IdGZona,az.ClaveZona,CONCAT(z.NombreZona,' - ',gz.nombre) AS zonas
                                                        FROM k_almacenzona az,c_almacen a,c_zona z,c_gzona gz
                                                        WHERE az.IdAlmacen=a.id_almacen AND az.ClaveZona=z.ClaveZona AND z.fk_id_gzona=gz.id_gzona
                                                        AND az.IdAlmacen='" . $almacen . "' ORDER BY zonas ASC");
                    while ($rs = mysql_fetch_array($query)) {
                        echo "<tr>";
                        echo "<td align='center' scope='row'>" . $rs['zonas'] . "</td>";
                        ?>
                    <td align='center' scope='row'> <a href='#' onclick='editarRegistroProv("<?php echo $alta; ?>", "<?php echo $rs['IdAlmacen']; ?>", "<?php echo $rs['ClaveZona']; ?>");
                        return false;' title='Editar Registro' ><img src="resources/images/Modify.png"/></a></td>

                    <td align='center' scope='row'> <a href='#' onclick='eliminarRegistroProv("<?php echo $controlador . "?id=" . $rs['IdAlmacen'] . "&id2=" . $rs['ClaveZona'] ?>", "<?php echo $rs['IdAlmacen']; ?>", "<?php echo $same_page; ?>");
                        return false;'><img src="resources/images/Erase.png"/></a> </td> 
                        <?php
                        echo "</tr>";
                    }
                    ?>
                </tbody>
            </table>
            <br/>
            <input type="button" style="float: right" Value="Regresar a almacén" class="boton" onclick="regresarAlmacen('admin/lista_almacen.php');"/>
        </div>
    </body>
</html>

