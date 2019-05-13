<?php
session_start();

if (!isset($_SESSION['user']) || $_SESSION['user'] == "") {
    header("Location: index.php");
}

include_once("../WEB-INF/Classes/Catalogo.class.php");
$controlador = $_SESSION['ruta_controler'] . "Controler_Estado.php";

$cabeceras = array("Estado", "Area", "");
$columnas = array("Nombre", "Descripcion", "IdEstado");
$tabla = "c_estado";
$order_by = "Nombre";
$alta = "admin/alta_estado.php";
$same_page = "admin/lista_estado.php";
?>
<!DOCTYPE html>
<html lang="es">
    <head>
        <script type="text/javascript" language="javascript" src="resources/js/paginas/lista.js"></script>
    </head>
    <body>
        <div class="principal">
            <img class="imagenMouse" src="resources/images/add.png" title="Nuevo" onclick='cambiarContenidos("<?php echo $alta; ?>");' style="float: right; cursor: pointer;" />  
            <br/><br/><br/>
            <table id="tAlmacen" class="tabla_datos" style="width: 100%">
                <thead>
                    <tr>
                        <td style="text-align: center;">Estado</td><td style="text-align: center;">Area</td><td></td><td></td>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    /* Inicializamos la clase */
                    $catalogo = new Catalogo();
                    $query = $catalogo->obtenerLista("SELECT e.IdEstado,e.Nombre,e.IdArea,a.Descripcion
                                                        FROM c_estado e,c_area a
                                                        WHERE e.IdArea=a.IdArea");
                    while ($rs = mysql_fetch_array($query)) {
                        echo "<tr>";
                        echo "<td align='center' scope='row'>" . $rs['Nombre'] . "</td>";
                        echo "<td align='center' scope='row'>" . $rs['Descripcion'] . "</td>";
                        ?>
                    <td align='center' scope='row'>                        
                        <a href='#' onclick='editarRegistro("<?php echo $alta; ?>", "<?php echo $rs['IdEstado']; ?>");
                        return false;' title='Editar Registro' >
                            <img src="resources/images/Modify.png"/>
                        </a>
                    </td>

                    <td align='center' scope='row'> <a href='#' onclick='eliminarRegistro("<?php echo $controlador . "?id=" . $rs['IdEstado']; ?>", "<?php echo $same_page; ?>");
                        return false;'><img src="resources/images/Erase.png"/></a></td>                                        
                        <?php
                        echo "</tr>";
                    }
                    ?>
                </tbody>
            </table>
        </div>
    </body>
</html>