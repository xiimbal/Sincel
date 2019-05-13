<?php
session_start();

if (!isset($_SESSION['user']) || $_SESSION['user'] == "") {
    header("Location: index.php");
}

include_once("../WEB-INF/Classes/Catalogo.class.php");
include_once("../WEB-INF/Classes/PermisosSubMenu.class.php");
$permisos_grid = new PermisosSubMenu();
$same_page = "catalogos/lista_campania.php";
$permisos_grid->getPermisosSubmenu($_SESSION['idUsuario'], $same_page);
$controlador = $_SESSION['ruta_controler'] . "Controler_Campania.php";

$cabeceras = array("Cliente", "Localidad", "Descripción", "Estatus", "", "");
$columnas = array("cliente", "localidad", "descripcion", "IdArea");
$tabla = "c_area";
$order_by = "cliente";
$alta = "catalogos/alta_campania.php";

?>
<!DOCTYPE html>
<html lang="es">
    <head>
        <script type="text/javascript" language="javascript" src="resources/js/paginas/lista.js"></script>
    </head>
    <body>
        <div class="principal">
            <?php if($permisos_grid->getAlta()){ ?>
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
                        echo "<th width=\"2%\" align=\"center\" scope=\"col\"></th>";
                        echo "<th width=\"2%\" align=\"center\" scope=\"col\"></th>";
                        ?>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    /* Inicializamos la clase */
                    $catalogo = new Catalogo();                                                            
                    $query = $catalogo->obtenerLista("SELECT E.IdArea, O.NombreRazonSocial AS cliente, C.Nombre AS localidad, E.Descripcion AS descripcion, (SELECT CASE WHEN E.Activo = 1 THEN 'Activo' ELSE 'Inactivo' END) AS Activo 
                                                      FROM c_centrocosto C JOIN c_cliente O ON C.ClaveCliente = O.ClaveCliente JOIN c_area E ON C.ClaveCentroCosto = E.ClaveCentroCosto ORDER BY O.NombreRazonSocial ASC;");
                    while ($rs = mysql_fetch_array($query)) {
                        echo "<tr>";
                            echo "<td align='center' scope='row'>" . $rs["cliente"] . "</td>";
                            echo "<td align='center' scope='row'>" . $rs["localidad"] . "</td>";
                            echo "<td align='center' scope='row'>" . $rs["descripcion"] . "</td>";                           
                            echo "<td align='center' scope='row'>" . $rs['Activo'] ."</td>";                            
                        ?>
                    <td align='center' scope='row'>
                        <?php if($permisos_grid->getModificar()){ ?>
                        <a href='#' onclick='editarRegistro("<?php echo $alta; ?>", "<?php echo $rs[$columnas[count($columnas) - 1]]; ?>");return false;' title='Editar Registro' >
                            <img src="resources/images/Modify.png"/>
                        </a>
                        <?php } ?>
                    </td>
                        <?php
                        //if ($_SESSION['idUsuario'] != $rs[$columnas[count($columnas) - 1]]) {
                            ?>
                        <td align='center' scope='row'> 
                            <?php if($permisos_grid->getBaja()){ ?>
                            <a href='#' onclick='eliminarRegistro("<?php echo $controlador . "?id=" . $rs[$columnas[count($columnas) - 1]]; ?>", "<?php echo $same_page; ?>");
                            return false;'><img src="resources/images/Erase.png"/></a> 
                            <?php } ?>
                        </td>                                        
                            <?php
                        //} else {
                        //    echo "<td></td>";
                        //}
                        echo "</tr>";
                    }
                    ?>
                </tbody>
            </table>
        </div>
    </body>
</html>
