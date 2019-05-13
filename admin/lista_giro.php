<?php
session_start();

if (!isset($_SESSION['user']) || $_SESSION['user'] == "") {//Si no tiene sesión activa, lo manda al login
    header("Location: index.php");
}

include_once("../WEB-INF/Classes/Catalogo.class.php");
include_once("../WEB-INF/Classes/PermisosSubMenu.class.php");

$permisos_grid = new PermisosSubMenu();
$same_page = "admin/lista_giro.php";//nombre de este script
$permisos_grid->getPermisosSubmenu($_SESSION['idUsuario'], $same_page);//Se obtiene los permisos que tiene sobre esta pagina el usuario

$cabeceras = array("Id","Giro","","");//Columnas que se muestran en el grid
$columnas = array("IdGiro","Nombre","IdGiro");//Nombre de los campos para mostrar la información(es importante que al final siempre venga el PrimaryKey de la tabla)
$controlador = $_SESSION['ruta_controler'] . "Controler_Giro.php";
$tabla = "c_giro";//nombre de la tabla de la bd
$order_by = "Nombre"; //Campo por el que se va a ordenar la consulta
$alta = "admin/alta_giro.php"; //Pagina para alta
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
            <table id="tAlmacen" class="tabla_datos">
                <thead>
                    <tr>
                        <?php
                        for ($i = 0; $i < (count($cabeceras) - 2); $i++) {//Se imprimen las cabeceras
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
                    $query = $catalogo->getListaAlta($tabla, $order_by);
                    while ($rs = mysql_fetch_array($query)) {//Recorremos el resultado de la consulta
                        echo "<tr>";
                        for ($i = 0; $i < count($columnas) - 1; $i++) {
                            echo "<td align='center' scope='row'>" . $rs[$columnas[$i]] . "</td>";
                        }
                        ?>
                        <td align='center' scope='row'>  <!-- Sirve para editar registro -->
                            <?php if($permisos_grid->getModificar()){ ?>
                            <a href='#' onclick='editarRegistro("<?php echo $alta; ?>", "<?php echo $rs[$columnas[count($columnas) - 1]]; ?>");return false;' title='Editar Registro' >
                                <img src="resources/images/Modify.png"/>
                            </a>
                            <?php } ?>
                        </td>
                        
                        <td align='center' scope='row'> <!--    Eliminar registro   -->
                            <?php if($permisos_grid->getBaja()){ ?>
                            <a href='#' onclick='eliminarRegistro("<?php echo $controlador . "?id=" . $rs[$columnas[count($columnas) - 1]]; ?>", "<?php echo $same_page; ?>");
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