<?php
session_start();

if (!isset($_SESSION['user']) || $_SESSION['user'] == "") {
    header("Location: index.php");
}
include_once("../WEB-INF/Classes/PartesEquipo.class.php");
include_once("../WEB-INF/Classes/Catalogo.class.php");
$pagina_lista = "admin/lista_componenteNecesariosC.php";
$noComponentePadre = "";
$noComponenteHijo = "";
$id = $_POST['idComponente'];
?>
<!DOCTYPE html>
<html lang="es">
    <head>
        <script type="text/javascript" language="javascript" src="resources/js/paginas/alta_CompnentesNecesarioC.js"></script>
        <script>
            $(document).ready(function() {
                $('.boton').button().css('margin-top', '20px');
            });
        </script>
    </head>
    <body>
        <div class="principal">

            <fieldset style="width: 95%; ">
                <legend>Componentes necesarios</legend>
                <form id="formCompNeces" name="formCompNeces" action="/" method="POST">
                    <table>
                        <tr>
                            <td><label for="componente">Componente</label></td>
                            <td>
                                <select id="componente2" name="componente" style="width: 250px;">
                                    <?php
                                    $catalogo = new Catalogo();

                                    $query = $catalogo->obtenerLista("SELECT * FROM c_componente c WHERE c.NoParte <> '" . $id . "' ORDER BY c.Modelo ASC");
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
                    <input type="submit" class="boton" value="Cancelar" onclick="regresarListaEq('<?php echo $pagina_lista; ?>','ComponentesNecesarios' );
                return false;"/>
                     <?php
                           echo "<input type='hidden' id='idE' name='idE' value='" .$id."'/> ";
                           ?>
                </form>
            </fieldset>
        </div>        
    </body>
</html>
