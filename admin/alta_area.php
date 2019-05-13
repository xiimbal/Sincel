<?php
session_start();

if (!isset($_SESSION['user']) || $_SESSION['user'] == "") {
    header("Location: index.php");
}
include_once("../WEB-INF/Classes/Area.class.php");
$pagina_lista = "admin/lista_area.php";
$idArea = "";
$descripcion = "";
$activo = "checked='checked'";
$read = "";
?>
<html>
    <head>
        <script type="text/javascript" language="javascript" src="resources/js/paginas/alta_area.js"></script>
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
                $obj = new Area();
                $obj->getRegistroById($_POST['id']);
                $read = "readonly='readonly'";
                $idArea = $obj->getIdArea();
                $descripcion = $obj->getDescripcion();
                if ($obj->getActivo() == "0") {
                    $activo = "";
                }
            }
            ?>
            <form id="formArea" name="formArea" action="/" method="POST">
                <table style="width: 50%;">
                    <tr>
                        <td>Descripción</td><td><input type='text' id='descripcion' name='descripcion' value='<?php echo $descripcion; ?>'/></td>
                        <td><input type="checkbox" name="activo" id="activo" <?php echo $activo; ?>/>Activo</td><td></td>
                    </tr>
                </table>
                <input type="submit" class="boton" value="Guardar" />
                <input type="submit" class="boton" value="Cancelar" onclick="cambiarContenidos('<?php echo $pagina_lista; ?>');
                return false;"/>
                <input type='hidden' id='id' name='id' value='<?php echo $idArea ?>'/>
            </form>
        </div>
    </body>
</html>