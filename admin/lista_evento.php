<?php
session_start();

if (!isset($_SESSION['user']) || $_SESSION['user'] == "") {
    header("Location: index.php");
}

include_once("../WEB-INF/Classes/Evento.class.php");
include_once("../WEB-INF/Classes/PermisosSubMenu.class.php");
$permisos_grid = new PermisosSubMenu();
$same_page = "admin/lista_evento.php";
$permisos_grid->getPermisosSubmenu($_SESSION['idUsuario'], $same_page);
$controlador = $_SESSION['ruta_controler'] . "Controler_Evento.php";

$cabeceras = array("Cliente", "Evento", "Descripción" ,"Vigencia Inicio", "Vigencia Fin", "", "");
$columnas = array("NombreRazonSocial", "Nombre" ,"Descripcion", "FechaInicio", "FechaFin", "IdEvento");

$alta = "admin/alta_evento.php";

?>
<!DOCTYPE html>
<html lang="es">
    <head>
        <script type="text/javascript" language="javascript" src="resources/js/paginas/lista.js"></script>
    </head>
    <body>
        <div class="principal">
            <?php if ($permisos_grid->getAlta()) { ?>
                <img class="imagenMouse" src="resources/images/add.png" title="Nuevo" onclick='cambiarContenidos("<?php echo $alta; ?>");' style="float: right; cursor: pointer;" />  
            <?php } ?>
            <br/><br/><br/>
            <table id="tAlmacen" class="tabla_datos" style="max-width: 100%;">
                <thead>
                    <tr>
                        <?php
                        for ($i = 0; $i < (count($cabeceras) - 2); $i++) {
                            echo "<th width=\"2%\" align=\"center\" scope=\"col\">" . $cabeceras[$i] . "</th>";
                        }
                        echo "<th width=\"2%\" align=\"center\" scope=\"col\"></th>";
                        echo "<th width=\"2%\" align=\"center\" scope=\"col\"></th>";
                        ?>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    /* Inicializamos la clase */
                    $obj = new Evento();
                    $query = $obj->getEventosVigentes("");
                    while ($rs = mysql_fetch_array($query)) {
                        echo "<tr>";
                        for ($i = 0; $i < count($columnas) - 1; $i++) {
                            echo "<td align='center' scope='row'>" . $rs[$columnas[$i]] . "</td>";
                        }
                        ?>
                                <td align='center' scope='row'>                        
                                    <?php if ($permisos_grid->getModificar()) { ?>
                                        <a href='#' onclick='editarRegistro("<?php echo $alta; ?>", "<?php echo $rs[$columnas[count($columnas) - 1]]; ?>");
                                        return false;' title='Editar Registro' >
                                            <img src="resources/images/Modify.png"/>
                                        </a>
                                    <?php } ?>
                                </td>
                                <td align='center' scope='row'> 
                                    <?php if ($permisos_grid->getBaja()) { ?>
                                        <a href='#' onclick='eliminarRegistro("<?php echo $controlador . "?id=" . $rs[$columnas[count($columnas) - 1]]; ?>", "<?php echo $same_page; ?>");
                                        return false;'>
                                            <img src="resources/images/Erase.png"/>
                                        </a> 
                                    <?php } ?>
                                </td>                                        
                        <?php
                        echo "</tr>";
                    }
                    ?>
                </tbody>
            </table>
        </div>
    </body>
</html>