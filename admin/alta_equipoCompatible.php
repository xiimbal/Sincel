<?php
session_start();

if (!isset($_SESSION['user']) || $_SESSION['user'] == "") {
    header("Location: index.php");
}
include_once("../WEB-INF/Classes/PartesEquipo.class.php");
include_once("../WEB-INF/Classes/Catalogo.class.php");
$pagina_lista = "admin/lista_equipoCompatible.php";
$noComponentePadre = "";
$noComponenteHijo = "";
$id = $_POST['idComponente'];
//echo $id;
?>
<!DOCTYPE html>
<html lang="es">
    <head>
        <script type="text/javascript" language="javascript" src="resources/js/paginas/alta_equipoCompatible.js"></script>
        <script>
            $(document).ready(function() {
                $('.boton').button().css('margin-top', '20px');
            });
        </script>
    </head>
    <body>
        <div class="principal">

            <fieldset style="width: 95%; ">
                <legend>Equipos compatibles</legend>
                <form id="formequipoComp" name="formequipoComp" action="/" method="POST">
                    <table id="equipoCompatible">
                        <tr>
                            <td><label for="equipo1">Equipo1</label></td>
                            <td>
                                <select id="equipo1" name="equipo1" style='width: 170px'>
                                    <?php
                                    $catalogo = new Catalogo();

                                    $query = $catalogo->obtenerLista("SELECT * FROM c_equipo e WHERE e.Activo=1 ORDER BY e.Modelo ");
                                    echo "<option value='0' >Selecciona una opción</option>";
                                    while ($rs = mysql_fetch_array($query)) {
                                        echo "<option value=" . $rs['NoParte'] . ">" . $rs['NoParte'] . " - " . $rs['Modelo'] ."</option>";
                                    }
                                    ?>
                                </select>
                            </td>
                            <td><label for="soportado1">Soportado1</label></td>
                            <td><input type="text" id="soportado1" name="soportado1" style='width: 80px' /></td>
                            <td><img class="imagenMouse" src="resources/images/add.png" title="Otra refaccion" onclick='otraEquipo();' style="float: right; cursor: pointer;" />  </td>
                            <td><img class="imagenMouse" src="resources/images/Erase.png" title="Eliminar refacción" onclick='deleteEquipo()' style="float: right; cursor: pointer;" />  </td>

                        </tr>
                    </table>
                    <input type="submit" class="boton" value="Guardar" />
                    <input type="submit" class="boton" value="Cancelar" onclick="regresarListaEq('<?php echo $pagina_lista; ?>', 'equipoCompatible');
                return false;"/>
                           <?php
                           echo "<input type='hidden' id='idComponente' name='idComponente' value='" . $id . "'/> ";
                           ?>
                </form>
            </fieldset>
        </div>        
    </body>
</html>
