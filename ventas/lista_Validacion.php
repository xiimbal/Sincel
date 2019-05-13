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
    <body>
        <div class="table-responsive">
            <table class="table">
                <thead class="thead-dark">
                    <tr>
                        <?php
                            foreach ($cabeceras as $cabecera) echo "<th>".$cabecera."</th>";
                            echo "<th></th>";                        
                        ?>
                    </tr>
                </thead>
                <?php
                    $catalogo = new Catalogo();
                    $query = $catalogo->obtenerLista("SELECT t.IdTicket,t.FechaHora,t.NombreCliente, e.Nombre AS estado, e1.Nombre AS tipo, t.ClaveCentroCosto FROM `$tabla` AS t INNER JOIN c_estadoticket AS e ON (t.ActualizarInfoCliente = 1 OR t.ActualizarInfoEquipo = 1) AND e.IdEstadoTicket = t.EstadoDeTicket INNER JOIN c_estado AS e1 ON e1.IdEstado = t.TipoReporte ORDER BY ".$order_by.";");
                    while ($rs = mysql_fetch_array($query)):
                        echo "<tr>";
                        foreach ($columnas as $columna) echo "<td>" .$rs[$columna]. "</td>";
                ?>
                <td> 
                    <?php if($permisos_grid->getModificar()): ?>
                        <a href='#' onclick='editarRegistro("<?php echo $alta; ?>","<?php echo $rs[$columnas[count($columnas)-1]]; ?>"); return false;' title='Editar Registro' >
                            <i class="far fa-edit" style="font-size: 1.2rem;"></i>
                        </a>
                    <?php endif; ?>
                </td>                    
                <?php
                    echo "</tr>";
                    endwhile;
                ?>
            </table>
        </div>
        <script type="text/javascript" language="javascript" src="resources/js/paginas/lista.js"></script>
    </body>
</html>