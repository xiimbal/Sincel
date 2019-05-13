<?php
session_start();

if (!isset($_SESSION['user']) || $_SESSION['user'] == "") {
    header("Location: index.php");
}
include_once("../WEB-INF/Classes/Zona.class.php");
include_once("../WEB-INF/Classes/Catalogo.class.php");
$pagina_lista = "admin/lista_zona.php";
$id="";
$idZona="";
$nombre="";
$descripcion="";
$idgZona="";
$orden="";
$activo="checked='checked'";
$read = "";
?>
<!DOCTYPE html>
<html lang="es">
    <head>
        <script type="text/javascript" language="javascript" src="resources/js/paginas/alta_zona.js"></script>
        <script>
            $(document).ready(function() {
                $('.boton').button().css('margin-top', '20px');
            });
        </script>
    </head>
    <body>
        <div class="principal">
            <?php
            if (isset($_POST['id'])) {
                $id=$_POST['id'];
                $obj = new Zona();
                $obj->getRegistroById($_POST['id']);
                $read = "readonly='readonly'";
                $idZona = $obj->getIdZona();
                $nombre = $obj->getNombre();
                $descripcion = $obj->getDescripcion();
                $idgZona = $obj->getIdGZona();
                $orden = $obj->getOrden();
                if($obj->getActivo()=="0"){
                    $activo = "";
                }
            }
            ?>
            <form id="formZona" name="formZona" action="/" method="POST">
                <table style="min-width: 90%;">
                    <tr>
                        <td><label for="clave">Clave Zona</label><span class="obligatorio"> *</span></td><td><input type="text" id="clave" name="clave" value="<?php echo $idZona; ?>"<?php echo $read; ?>/></td>
                         <td><label for="nombre">Nombre</label><span class="obligatorio"> *</span></td><td><input type="text" id="nombre" name="nombre" value="<?php echo $nombre; ?>"/></td>
                    </tr> 
                    <tr>
                        <td><label for="descripcion">Descripción</label></td><td><input type="text" id="descripcion" name="descripcion" value="<?php echo $descripcion; ?>"/></td>
                        <td><label for="gZona">Grupo-zona</label></td>
                        <td>
                            <select id="gZona" name="gZona">
                                <?php
                                $catalogo = new Catalogo();
                                $query = $catalogo->getListaAlta("c_gzona", "nombre");
                                echo "<option value='0' >Selecciona una opción</option>";
                                while ($rs = mysql_fetch_array($query)) {
                                    $s = "";
                                    if ($idgZona != "" && $idgZona == $rs['id_gzona']) {
                                        $s = "selected";
                                    }
                                    echo "<option value=" . $rs['id_gzona'] . " " . $s . ">" . $rs['nombre'] . "</option>";
                                }
                                ?>
                            </select>
                        </td>
                    </tr>
                    <tr>
                        <td><label for="orden">Orden</label></td><td><input type="text" id="orden" name="orden" value="<?php echo $orden; ?>"/></td>
                        <td></td>
                        <td><input type="checkbox" name="activo" id="activo" <?php echo $activo; ?>/>Activo</td>
                    </tr>
                </table>
                <input type="submit" class="boton" value="Guardar" />
                <input type="submit" class="boton" value="Cancelar" onclick="cambiarContenidos('<?php echo $pagina_lista; ?>');
                return false;"/>
                <?php
                echo "<input type='hidden' id='id' name='id' value='" . $id . "'/> ";
                ?>
            </form>
        </div>
    </body>
</html>
