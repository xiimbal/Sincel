<?php
session_start();

if (!isset($_SESSION['user']) || $_SESSION['user'] == "") {
    header("Location: index.php");
}

include_once("../WEB-INF/Classes/Catalogo.class.php");
include_once("../WEB-INF/Classes/PermisosSubMenu.class.php");
$permisos_grid = new PermisosSubMenu();
$same_page = "catalogos/lista_chofer.php";
$permisos_grid->getPermisosSubmenu($_SESSION['idUsuario'], $same_page);
$controlador = $_SESSION['ruta_controler'] . "Controler_UsuarioTurno.php";

$cabeceras = array("Usuario", "Nombre", "Correo", "Tipo de Usuario", "Vehículo Asignado", "Estatus","", "");
$columnas = array("Loggin", "nombre_completo", "correo", "puesto", "IdVehiculo", "IdUsuario");
$tabla = "c_usuario";
$order_by = "loggin";
$alta = "catalogos/alta_chofer.php";

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
                    $query = $catalogo->obtenerLista("SELECT usu.IdUsuario, Loggin, CONCAT(usu.Nombre,' ',usu.ApellidoPaterno,' ',ApellidoMaterno) AS nombre_completo,
                                                        correo, per.Nombre AS puesto,(SELECT CASE WHEN usu.Activo = 1 THEN 'Activo' ELSE 'Inactivo' END) AS Activo,
							CONCAT (cv.Placas,' - ',cv.Modelo) AS Vehiculo
                                                        FROM c_usuario AS usu LEFT JOIN c_puesto AS per ON per.IdPuesto = usu.IdPuesto LEFT JOIN c_domicilio_usturno AS cd ON cd.IdUsuario=usu.IdUsuario 
							LEFT JOIN c_vehiculo AS cv ON cd.IdVehiculo=cv.IdVehiculo WHERE  usu.IdPuesto=101, usu.IdPuesto=108, usu.IdPuesto=109 
                                                        ORDER BY usu.Nombre ASC;");
                    while ($rs = mysql_fetch_array($query)) {
                        echo "<tr>";
                            echo "<td width=\"2%\" align='center' scope='row'>" . $rs["Loggin"] . "</td>";
                            echo "<td width=\"2%\" align='center' scope='row'>" . $rs["nombre_completo"] . "</td>";
                            echo "<td width=\"2%\" align='center' scope='row'>" . $rs["correo"] . "</td>";
                            echo "<td width=\"2%\" align='center' scope='row'>" . $rs["puesto"] . "</td>"; 
                            echo "<td width=\"2%\" align='center' scope='row'>" . $rs["Vehiculo"] . "</td>";
                            echo "<td width=\"2%\" align='center' scope='row'>" . $rs['Activo'] ."</td>";                            
                        ?>
                    <td width="2%" align='center' scope='row'>
                        <?php if($permisos_grid->getModificar()){ ?>
                        <a href='#' onclick='editarRegistro("<?php echo $alta; ?>", "<?php echo $rs[$columnas[count($columnas) - 1]]; ?>");return false;' title='Editar Registro' >
                            <img src="resources/images/Modify.png"/>
                        </a>
                        <?php } ?>
                    </td>
                        <?php
                        //if ($_SESSION['idUsuario'] != $rs[$columnas[count($columnas) - 1]]) {
                            ?>
                        <td width="2%" align='center' scope='row'> 
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
