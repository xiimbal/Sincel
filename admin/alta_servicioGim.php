<?php
session_start();

if (!isset($_SESSION['user']) || $_SESSION['user'] == "") {
    header("Location: index.php");
}
include_once("../WEB-INF/Classes/ServicioGimGfa.class.php");
include_once("../WEB-INF/Classes/Catalogo.class.php");
$pagina_lista = "admin/lista_servicioGim.php";
$idServicio = "";
$claveEsp = $_POST['id'];
$claveCentroCosto = "";
$idCliente = $_POST['id2'];
$read = "readonly='readonly'";
?>
<!DOCTYPE html>
<html lang="es">
    <head>
        <script type="text/javascript" language="javascript" src="resources/js/paginas/alta_servicioGim.js"></script>
        <script>
            $(document).ready(function() {
                $('.boton').button().css('margin-top', '20px');
            });
        </script>
    </head>
    <body>
        <div class="principal">
            <?php
            $obj = new ServicioGim();
            if (isset($_POST['id']) && isset($_POST['id2']) && isset($_POST['id3'])) {
                $idServicio = $_POST['id'];
                $claveEsp = $_POST['id2'];
                $idCliente = $_POST['id3'];
                $obj->getRegistroById($idServicio, $claveEsp, $idCliente);
                $claveCentroCosto = $obj->getClaveCentroCosto();
            }
            ?>
            <fieldset style="width: 95%; ">
                <legend>Servicio particulares</legend>
                <form id="formServicioG" name="formServicioG" action="/" method="POST">
                    <table>
                        <tr>
                            <td><label for="claveEspecial">Clave especial servicio</label></td>
                            <td><input type="text" name="claveEspecial" id="claveEspecial" value="<?php echo $claveEsp; ?>" <?php echo $read; ?>/></td>
                            <td><label for="clave">Clave centro costo</label></td>
                            <td>
                                <select id="clave" name="clave">
                                    <?php
                                    $catalogo = new Catalogo();

                                    $query = $catalogo->getListaAlta("c_centrocosto", "Nombre");
                                    echo "<option value='0' >Selecciona una opción</option>";
                                    while ($rs = mysql_fetch_array($query)) {
                                        $s = "";
                                        if ($claveCentroCosto != "" && $claveCentroCosto == $rs['ClaveCentroCosto']) {
                                            $s = "selected";
                                        }
                                        echo "<option value=" . $rs['ClaveCentroCosto'] . " " . $s . ">" . $rs['Nombre'] . "</option>";
                                    }
                                    ?>
                                </select>
                            </td>
                        </tr>
                        <tr>
                            <td><label for="cliente">Id cliente</label></td>
                            <td><input type="text" name="cliente" id="cliente" value="<?php echo $idCliente; ?>" <?php echo $read; ?> /></td>
                        </tr>
                    </table>
                    <input type="submit" class="boton" value="Guardar" />
                    <input type="submit" class="boton" value="Cancelar" onclick="editarRegistroProv('<?php echo $pagina_lista; ?>', '<?php echo $claveEsp; ?>','<?php echo $idCliente; ?>');
                return false;"/>
                           <?php
                            echo "<input type='hidden' id='id' name='id' value='" . $claveCentroCosto . "'/> ";
                            echo "<input type='hidden' id='idServicio' name='idServicio' value='" . $idServicio . "'/> ";
                           ?>
                </form>
            </fieldset>

        </div>
    </body>
</html>