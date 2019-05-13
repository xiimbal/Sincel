<?php
    session_start();
    
    if (!isset($_SESSION['user']) || $_SESSION['user'] == "") {
        header("Location: index.php");
    }
    
    include_once("../WEB-INF/Classes/Catalogo.class.php");
    include_once("../WEB-INF/Classes/PermisosSubMenu.class.php");
    $permisos_grid = new PermisosSubMenu();
    $same_page = "ventas/lista_Validacion.php";
    $permisos_grid->getPermisosSubmenu($_SESSION['idUsuario'], $same_page);

    $controlador = $_SESSION['ruta_controler']."Controler_Validacion.php";
    
    $cabeceras = array("Folio","Fecha","Cliente","Estado","Tipo","Clave","","");
    $columnas = array("IdTicket","FechaHora","NombreCliente","estado","tipo","ClaveCentroCosto","IdTicket");
    $tabla = "c_ticket";
    $order_by = "IdTicket";
    $alta = "ventas/alta_validacion.php";
?>
<!DOCTYPE html>
<html lang="es">
    <head>        
        <script type="text/javascript" language="javascript" src="resources/js/paginas/lista.js"></script>

 <!--link responsivo-->
        <link href="resources/css/Bootstrap 4/bootstrap.min.css" rel="stylesheet">
        <link href="resources/css/Bootstrap 4/fontawesome/css/all.min.css" rel="stylesheet"> 
    </head>
    <body>
        <div class="principal">            
            <br/><br/><br/>
            <table id="tAlmacen" class="table-responsive">
                <thead>
                    <tr>
                        <?php
                        for($i=0; $i<(count($cabeceras)-2); $i++) {
                            echo "<th width=\"2%\" align=\"center\" scope=\"col\">".$cabeceras[$i]."</th>";
                        }
                        echo "<th width=\"2%\" align=\"center\" scope=\"col\"></th>";                        
                        ?>                        
                    </tr>
                </thead>
                <tbody>
                    <?php
                    /* Inicializamos la clase */
                    $catalogo = new Catalogo();
                    $query = $catalogo->obtenerLista("SELECT t.IdTicket,t.FechaHora,t.NombreCliente, e.Nombre AS estado, 
                        e1.Nombre AS tipo, t.ClaveCentroCosto FROM `$tabla` AS t 
                        INNER JOIN c_estadoticket AS e ON (t.ActualizarInfoCliente = 1 OR t.ActualizarInfoEquipo = 1) AND e.IdEstadoTicket = t.EstadoDeTicket 
                        INNER JOIN c_estado AS e1 ON e1.IdEstado = t.TipoReporte ORDER BY ".$order_by.";");
                    while ($rs = mysql_fetch_array($query)) {
                        echo "<tr>";
                        for($i=0; $i<count($columnas)-1; $i++){
                            echo "<td align='center' scope='row'>" .$rs[$columnas[$i]]. "</td>";
                        }
                        ?>
                    <td align='center' scope='row'> 
                        <?php if($permisos_grid->getModificar()){ ?>
                        <a href='#' onclick='editarRegistro("<?php echo $alta; ?>","<?php echo $rs[$columnas[count($columnas)-1]]; ?>"); return false;' title='Editar Registro' ><img src="resources/images/Apply.png"/></a>
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