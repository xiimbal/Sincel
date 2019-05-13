<?php
session_start();

if (!isset($_SESSION['user']) || $_SESSION['user'] == "") {
    header("Location: index.php");
}

include_once("../WEB-INF/Classes/Catalogo.class.php");
include_once("../WEB-INF/Classes/PermisosSubMenu.class.php");

$permisos_grid = new PermisosSubMenu();
$same_page = "catalogos/lista_ubicacion.php";
$permisos_grid->getPermisosSubmenu($_SESSION['idUsuario'], $same_page);


$controlador = $_SESSION['ruta_controler'] . "Controler_Ubicacion.php";

$cabeceras = array("Nombre", "Domicilio", "Estatus", "", "","","");
$alta = "catalogos/alta_ubicacion.php";
?>

<!DOCTYPE html>
<html lang = "es">
    <head>
        <script type="text/javascript" language="javascript" src="resources/js/paginas/lista.js"></script>
    </head>
    <body>
        <div class="principal">            
            <?php if ($permisos_grid->getAlta()) { ?>
                <img class="imagenMouse" src="resources/images/add.png" title="Nuevo" onclick='cambiarContenidos("<?php echo $alta; ?>");' style="float: right; cursor: pointer;" />  
            <?php } ?>
            <br/><br/><br/>
            <table id="tAlmacen" class="tabla_datos">
                <thead>
                    <tr>
                        <?php
                        for ($i = 0; $i < (count($cabeceras) - 2); $i++) {
                            echo "<th width=\"2%\" align=\"center\" scope=\"col\">" . $cabeceras[$i] . "</th>";
                        }
                        ?>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $catalogo = new Catalogo();
                    $query = $catalogo->obtenerLista("SELECT IdUbicacion,  Descripcion AS Nombre, CONCAT(Calle,' ',NoExterior,' ',Colonia,' ',CodigoPostal,' ',Estado,' ',Delegacion) AS Domicilio, Activo  FROM c_ubicaciones 
                                                     ORDER BY Nombre ASC");
                    while ($rs = mysql_fetch_array($query)) {
                        $notas = 0;
                        $EstadoTicket = 0;
                        echo "<tr>";
                        echo "<td width=\"2%\" align=\"center\" scope=\"row\">" . $rs['Nombre'] . "</td>";
                        echo "<td width=\"2%\" align=\"center\" scope=\"row\">" . $rs['Domicilio'] . "</td>";
                        echo "<td width=\"2%\" align=\"center\" scope=\"row\">" . $rs['Activo'] . "</td>";
                        
                        if ($permisos_grid->getModificar()) {
                            echo "<td width=\"2%\" align=\"center\" scope=\"row\"><a href='#' onclick=\"cambiarContenidos('catalogos/alta_ubicacion.php?id=" . $rs['IdUbicacion'] . "', 'Editar Ubicación');
                        return false;\" title='Editar'><img src=\"resources/images/Modify.png\" width=\"24\" height=\"24\"/></a></td>";
                        } else {
                            echo "<td width=\"2%\" align=\"center\" scope=\"row\"></td>";
                        }
                        if ($permisos_grid->getBaja()) {
                            echo "<td width=\"2%\" align=\"center\" scope=\"row\"><a href='#' onclick=\"eliminarRegistroPlantilla('".$controlador."?id=" . $rs['IdUbicacion'] . "',0,0,'".$same_page."');
                        return false;\" title='Eliminar'><img src=\"resources/images/Erase.png\" width=\"24\" height=\"24\"/></a></td>";
                           
                        } else {
                            echo "<td width=\"2%\" align=\"center\" scope=\"row\"></td>";
                        }
                        echo "</tr>";
                    }
                    ?>
                </tbody>
            </table>
        </div>
    </body>
</html>            


