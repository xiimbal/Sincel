<?php
session_start();
if (!isset($_SESSION['user']) || $_SESSION['user'] == "") {
    header("Location: index.php");
}

include_once("../WEB-INF/Classes/Catalogo.class.php");
$controlador = $_SESSION['ruta_controler'] . "Controler_PermisosMenu.php";
$same_page = "admin/lista_permisoMenu.php";
$alta = "admin/alta_permisosMenu.php";
?>
<!DOCTYPE html>
<html>
    <head>
        <script type="text/javascript" language="javascript" src="resources/js/paginas/lista.js"></script>        
    </head>
    <body>
        <div class="principal">
           <img class="imagenMouse" src="resources/images/add.png" title="Nuevo" onclick='cambiarContenidos("<?php echo $alta; ?>");' style="float: right; cursor: pointer;" />  
            <br/><br/><br/>
            <table id="tAlmacen" class="tabla_datos" style="width: 900px">
                <thead>
                    <tr>
                        <td>Puesto</td>
                        <td>Submenu</td>
                        <td>Descipción</td>
                        <td></td>                        
                        <td></td>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $catalogo = new Catalogo();
                    $query = $catalogo->obtenerLista("SELECT p.Nombre,sm.nom_sub,sm.id_sub,p.IdPuesto,sm.descripcion
                                                        FROM m_dpuestomenu pm, m_submenu sm,c_puesto p
                                                        WHERE pm.IdPuesto=p.IdPuesto
                                                        AND pm.IdSubmenu=sm.id_sub
                                                        ORDER BY p.Nombre");
                    while ($rs = mysql_fetch_array($query)) {
                        echo "<tr>";
                        echo "<td align='center' scope='row'>" . $rs['Nombre'] . "</td>";
                        echo "<td align='center' scope='row'>" . $rs['nom_sub'] . "</td>";
                        echo "<td align='center' scope='row'>" . $rs['descripcion'] . "</td>";
                        ?>



                    <td align='center' scope='row'> <a href='#' onclick='editarRegistroProv("<?php echo $alta; ?>", "<?php echo $rs['IdPuesto']; ?>", "<?php echo $rs['id_sub']; ?>");
                        return false;' title='Editar Registro' ><img src="resources/images/Modify.png"/></a></td>

                    <td align='center' scope='row'> <a href='#' onclick='eliminarRegistroProv("<?php echo $controlador . "?id=" . $rs['IdPuesto'] . "&id2=" . $rs['id_sub'] ?>", "<?php echo $rs['IdPuesto']; ?>", "<?php echo $same_page; ?>");
                        return false;'><img src="resources/images/Erase.png"/></a> </td> 
                        <?php
                        echo "</tr>";
                    }
                    ?>
                </tbody>
            </table>
        </div>        
    </body>
</html>