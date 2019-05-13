<?php
session_start();

if (!isset($_SESSION['user']) || $_SESSION['user'] == "") {
    header("Location: index.php");
}

include_once("../WEB-INF/Classes/Catalogo.class.php");
include_once("../WEB-INF/Classes/PermisosSubMenu.class.php");

$controlador = $_SESSION['ruta_controler'] . "Controler_FlujoFalla.php";

$cabeceras = array("Id", "Estado", "Area", "Pantalla", "Validar", "Cobrar","Activo", "", "");
$columnas = array("IdEstado", "Nombre", "Area", "Flujos", "Validar", "Cobrar", "Activo", "IdEstado");

$same_page = "admin/lista_flujoFalla.php?tipo=3";
$alta = "admin/alta_flujoFalla.php";

$permisos_grid = new PermisosSubMenu();
$permisos_grid->getPermisosSubmenu($_SESSION['idUsuario'], $same_page);
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
                    $query = $catalogo->obtenerLista("SELECT e.Nombre, a.Descripcion AS Area, GROUP_CONCAT(f.Nombre SEPARATOR ', ') AS Flujos,
                        (CASE WHEN e.Activo = 1 THEN 'Activo' ELSE 'Inactivo' END) AS Activo,
                        e.IdEstado, IF(e.FlagValidacion = 1, 'Sí','No') AS Validar, IF(e.FlagCobrar = 1, 'Sí', 'No') AS Cobrar
                        FROM c_estado AS e
                        LEFT JOIN k_flujoestado AS kfe ON kfe.IdEstado = e.IdEstado
                        LEFT JOIN c_flujo AS f ON f.IdFlujo = kfe.IdFlujo
                        LEFT JOIN c_area AS a ON a.IdArea = e.IdArea
                        GROUP BY e.IdEstado
                        ORDER BY e.Nombre;");
                    while ($rs = mysql_fetch_array($query)) {
                        echo "<tr>";
                        for ($i = 0; $i < count($columnas) - 1; $i++) {
                            echo "<td align='center' scope='row'>" . $rs[$columnas[$i]] . "</td>";
                        }
                        ?>
                    <td align='center' scope='row'>    
                        <?php if($permisos_grid->getModificar()){ ?>
                        <a href='#' onclick='editarRegistro("<?php echo $alta; ?>", "<?php echo $rs[$columnas[count($columnas) - 1]]; ?>");
                        return false;' title='Editar Registro' >
                            <img src="resources/images/Modify.png"/>
                        </a>
                        <?php } ?>
                    </td>                    
                    <td align='center' scope='row'> 
                        <?php if($permisos_grid->getBaja()){ ?>
                        <a href='#' onclick='eliminarRegistro("<?php echo $controlador . "?id=" . $rs['IdEstado'] ?>", "<?php echo $same_page; ?>");
                        return false;'><img src="resources/images/Erase.png"/></a> 
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