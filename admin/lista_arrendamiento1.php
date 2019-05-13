<?php
session_start();

if (!isset($_SESSION['user']) || $_SESSION['user'] == "") {
    header("Location: index.php");
}
include_once("../WEB-INF/Classes/Catalogo.class.php");

?>
<!DOCTYPE html>
<html lang="es">
    <head>
        <title>Productos y servicios</title>
    </head>
    <body>       
        <table>
            <tr>
                <td>
                    <select id="modalidad" name="modalidad" onchange="cargarArrendamiento(this.value);">
                        <?php
                        $catalogo = new Catalogo();
                        $query = $catalogo->getListaAlta('c_modalidad', 'Nombre');
                        echo "<option value='0' >Selecciona una opción</option>";
                        while ($rs = mysql_fetch_array($query)) {
                            echo "<option value=" . $rs['Pagina'] . " >" . $rs['Nombre'] . "</option>";
                        }
                        ?>
                    </select>
                </td>
            </tr>
        </table>
        <br/><br/>
         <div id="arrendamientos"></div>

    </body>
</html>