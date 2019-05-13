<?php
session_start();

if (!isset($_SESSION['user']) || $_SESSION['user'] == "") {
    header("Location: index.php");
}
include_once("../WEB-INF/Classes/Catalogo.class.php");
include_once("../WEB-INF/Classes/PermisosSubMenu.class.php");
$permisos_grid = new PermisosSubMenu();
$same_page = "ventas/Ventas_Directas.php";
$permisos_grid->getPermisosSubmenu($_SESSION['idUsuario'], $same_page);
?>
<!DOCTYPE html>
<html lang="es">
    <head>                
        <script type="text/javascript" language="javascript" src="resources/js/paginas/ventas/ventas_directas.js"></script>
    </head>
    <body>
        <div class="principal">         
            <?php if ($permisos_grid->getAlta()) { ?>
                <img class="imagenMouse" src="resources/images/add.png" title="Nuevo" onclick='cambiarContenidosvd("ventas/Nueva_venta_directa.php", "Detalle ventas directas");' style="float: right; cursor: pointer;" />  
            <?php } ?>
            <br/><br/><br/>
            <form>
                &nbsp;&nbsp;&nbsp;
                <div style="float: right;">
                    <label for="checksc">Surtidas y Canceladas</label><input type="checkbox" id="checksc" value="1" onchange="surtidasycanceladas();" <?php
                    if (isset($_GET['surtida'])) {
                        if ($_GET['surtida'] == 1) {
                            echo "checked";
                        }
                    }
                    ?>/>
                </div>
            </form>
            <br/><br/>
            <div id="divinfo"></div>
        </div>
        <br/><br/>
    </body>
</html>

<?php
    echo "<script> cargarTablaVD() </script>";
?>

