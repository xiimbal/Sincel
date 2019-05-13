<?php
session_start();

if (!isset($_SESSION['user']) || $_SESSION['user'] == "") {
    header("Location: index.php");
}
include_once("../WEB-INF/Classes/Estado.class.php");
include_once("../WEB-INF/Classes/Catalogo.class.php");
$pagina_lista = "admin/lista_estado.php";
$id = "";
$idEstado = "";
$nombre = "";
$activo = "checked='checked'";
$read = "";
$area = "";
?>
<!DOCTYPE html>
<html lang="es">
    <head>
        <script type="text/javascript" language="javascript" src="resources/js/paginas/alta_estado.js"></script>
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
                $id = $_POST['id'];
                $obj = new Estado();
                $obj->getRegistroById($_POST['id']);
                $read = "readonly='readonly'";
                $idEstado = $obj->getIdEstado();
                $nombre = $obj->getNombre();
                $area = $obj->getArea();
                if ($obj->getActivo() == "0") {
                    $activo = "";
                }
            }
            ?>
            <form id="formEstado" name="formEstado" action="/" method="POST">
                <table style="min-width: 70%">
                    <tr>
                        <td><label for="nombre">Estado</label><span class="obligatorio"> *</span></td>
                        <td><input type="text" id="nombre" name="nombre" value="<?php echo $nombre; ?>"/></td> 
                        <td><label for="area">Área</label></td>
                        <td>
                            <select id="area" name="area">
                                <option value="0">Selecciona una opción</option>
                                <?php
                                $catalogo = new Catalogo();
                                $query = $catalogo->getListaAlta("c_area", "Descripcion");
                                while ($rs = mysql_fetch_array($query)) {
                                    $s = "";
                                    if ($area != "" && $area == $rs['IdArea']) {
                                        $s = "selected";
                                    }
                                    echo "<option value=" . $rs['IdArea'] . " " . $s . ">" . $rs['Descripcion'] . "</option>";
                                }
                                
                                ?>
                            </select>
                        </td>
                    </tr>
                    <tr>
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
