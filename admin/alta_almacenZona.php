<?php
session_start();

if (!isset($_SESSION['user']) || $_SESSION['user'] == "") {
    header("Location: index.php");
}
include_once("../WEB-INF/Classes/AlmacenZona.class.php");
include_once("../WEB-INF/Classes/Catalogo.class.php");
$pagina_lista = "admin/lista_almacenZona.php";
if (isset($_POST['id']))
    $almacen = $_POST['id'];

//echo $almacen;
$zona = "";
$gZona = "";
$accion = "";
if (isset($_POST['gzona'])) {
    $gzona = $_POST['gzona'];
    $almacen = $_POST['almacen'];
    $zona=$_POST['zona'];
    $accion=$_POST['accion'];
}
//$almacenCatalogo = new Catalogo();
//$queryAlmacen = $almacenCatalogo->obtenerLista("SELECT * FROM c_almacen WHERE id_almacen='" . $almacen . "'");
//if ($rs = mysql_fetch_array($queryAlmacen)) {
//    echo "Zonas del almacén: " . $rs['nombre_almacen'];
//}
?>
<!DOCTYPE html>
<html lang="es">
    <head>
        <script type="text/javascript" language="javascript" src="resources/js/paginas/alta_alamcenZona.js"></script>
        <script>
            $(document).ready(function() {
                $('.boton').button().css('margin-top', '20px');
            });
        </script>
    </head>
    <body>
        <div class="principal">
            <?php
            $obj = new AlmacenZona();
            if (isset($_POST['id']) && isset($_POST['id2'])) {

                $obj->getRegistroById($_POST['id'], $_POST['id2']);
                $almacen = $obj->getIdAlmacen();
                $gzona = $obj->getIdGZona();
                $zona = $obj->getClaveZona();
                $accion = "editar";
            }
            ?>
            <form id="formAlmacenZona" name="formAlmacenZona" action="/" method="POST">
                <table id="almacenNota">
                    <tr>
                        <td>Grupo de zona<span class="obligatorio"> *</span></td>
                        <td>
                            <select id='gzona'name='gzona' onchange="verZonas('admin/alta_almacenZona.php');">
                                <?php
                                $catalogo = new Catalogo();
                                $query = $catalogo->getListaAlta("c_gzona", "nombre");
                                echo "<option value='0' >Selecciona una opción</option>";
                                while ($rs = mysql_fetch_array($query)) {
                                    $s = "";
                                    if ($gzona == $rs['id_gzona']) {
                                        $s = "selected";
                                    }
                                    echo "<option value=" . $rs['id_gzona'] . " " . $s . ">" . $rs['nombre'] . "</option>";
                                }
                                ?>
                            </select>
                        </td>
                        <td></td><td></td>
                    </tr>
                    <tr>
                        <td>Zonas<span class="obligatorio"> *</span></td>
                        <td>
                            <select id="zona1" name="zona1" style="width:200px">
                                <option value="0">Selecccione una opción</option>
                                <?php
                                $catalogo = new Catalogo();
                                $query = $catalogo->obtenerLista("SELECT * FROM c_zona WHERE fk_id_gzona='" . $gzona . "'");
                                while ($rs = mysql_fetch_array($query)) {
                                    $s = "";
                                    if ($zona == $rs['ClaveZona']) {
                                        $s = "selected";
                                    }
                                    echo "<option value=" . $rs['ClaveZona'] . " " . $s . ">" . $rs['NombreZona'] . "</option>";
                                }
                                ?>
                            </select>
                        </td>
                        <?php
                        if ($accion == "") {
                            ?>

                            <td><img class="imagenMouse" src="resources/images/add.png" title="Otra refaccion" onclick='agregarNota();' style="float: right; cursor: pointer;" />  </td>
                            <td><img class="imagenMouse" src="resources/images/Erase.png" title="Eliminar refacción" onclick='eliminarNota();' style="float: right; cursor: pointer;" />  </td>
                        <?php } ?>
                    </tr>
                </table>
                <input type="submit" class="boton" value="Guardar" />
                <input type="submit" class="boton" value="Cancelar" onclick="cambiarContenidosProv('<?php echo $pagina_lista; ?>', '<?php echo $almacen; ?>');
                return false;"/>
                <input type='hidden' id='almacen' name='almacen' value='<?php echo $almacen ?>'/>
                <input type='hidden' id='accion' name='accion' value='<?php echo $accion ?>'/>
                <input type='hidden' id='zona' name='zona' value='<?php echo $zona ?>'/>
            </form>
        </div>
    </body>
</html>
