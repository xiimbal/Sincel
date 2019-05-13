<?php
session_start();
if (!isset($_SESSION['user']) || $_SESSION['user'] == "") {
    header("Location: index.php");
}
include_once("../WEB-INF/Classes/ComponentesDetalle.class.php");
include_once("../WEB-INF/Classes/Catalogo.class.php");
$pagina_ListaPadre = "admin/lista_formoComponentes.php";
$pagina_ListaHijo = "admin/lista_partesDelComponente.php";
$noPartePadre = "";
$noParteHijo = "";
$read = "readonly";
$div = "";
$id = $_POST['idComponente'];
$tipo = $_POST['tipo'];
if ($tipo == "padre") {
    $nombre = "Formo parte de(los) componente(s)";
    $pagina_lista = "admin/lista_formoComponentes.php";
    $div = "formatoComponentes";
} else if ($tipo == "hijo") {
    $nombre = "Partes del componente";
    $pagina_lista = "admin/lista_partesDelComponente.php";
    $div = "partesDelComponente";
}
?>
<!DOCTYPE html>
<html lang="es">
    <head>
        <script type="text/javascript" language="javascript" src="resources/js/paginas/alta_detalleComponente.js"></script>
        <script>
            $(document).ready(function() {
                $('.boton').button().css('margin-top', '20px');
            });
        </script>
    </head>
    <body>
        <div class="principal">
            <fieldset style="width: 95%; ">
                <legend><?php echo $nombre; ?></legend>
                <form id="fromDetalleComponente" name="detalleComponente" action="/" method="POST">
                    <table style="border-spacing:  5px;">
                        <tr>
                            <td>
                                <label for="forma">Forma parte de:</label>
                                <input type="text" id="idE" name="idE" <?php echo $read ?> value="<?php echo $id ?>">
                            </td>
                            <td>
                                <label for="componente">Componente</label>
                                <select id="componente" name="componente" style="width: 250px;">
                                    <?php
                                    $catalogo = new Catalogo();

                                    $query = $catalogo->obtenerLista("SELECT * FROM c_componente c WHERE c.NoParte <> '".$id."' ORDER BY c.Modelo ASC");
                                    echo "<option value='' >Selecciona una opción</option>";
                                    while ($rs = mysql_fetch_array($query)) {
                                        echo "<option value=" . $rs['NoParte'] . ">" . $rs['NoParte'] . " - " . $rs['Modelo'] . " - ".$rs['Descripcion']."</option>";
                                    }
                                    ?>
                                </select>
                            </td>
                        </tr>
                    </table>
                    <input type="submit" class="boton" value="Guardar" />
                    <input type="submit" class="boton" value="Cancelar" onclick="regresarListaEq('<?php echo $pagina_lista; ?>', '<?php echo $div; ?>');
                return false;"/>
                           <?php
                           echo "<input type='hidden' id='tipo' name='tipo' value='" . $tipo . "'/> ";
                           echo "<input type='hidden' id='div' name='div' value='" . $div . "'/> ";
                           echo "<input type='hidden' id='paginaLista' name='paginaLista' value='" . $pagina_lista . "'/> ";
                           ?>
                </form>
            </fieldset>
        </div>
    </body>
</html>
