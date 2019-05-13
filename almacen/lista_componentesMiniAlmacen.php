<?php
session_start();

if (!isset($_SESSION['user']) || $_SESSION['user'] == "") {
    header("Location: index.php");
}
$idminiAlmacen = $_POST["idM"];
$idCliente = $_POST['id'];
$miniAlmacen = "";
include_once("../WEB-INF/Classes/Catalogo.class.php");
$controlador = $_SESSION['ruta_controler'] . "Controler_ComponentesMiniAlmacen.php";
$alta = "almacen/alta_componenteMiniAlmacen.php";
$same_page = "almacen/lista_componentesMiniAlmacen.php";
$consultaCliente = new Catalogo();
$query = $consultaCliente->obtenerLista("SELECT * FROM c_minialmacen ma WHERE ma.IdMiniAlmacen='" . $idminiAlmacen . "'");
if ($rs = mysql_fetch_array($query)) {
    $miniAlmacen = $rs['Nombre'];
}

echo "</br></br>Componentes del mini almacén: <b>" . $miniAlmacen . "</b>";
?>
<!DOCTYPE html>
<html lang="es">
    <head>
        <script type="text/javascript" language="javascript" src="resources/js/paginas/lista.js"></script>      
        <script type="text/javascript" language="javascript" src="resources/js/paginas/alta_miniAlmacen.js"></script>
        <script type="text/javascript" language="javascript" src="resources/js/paginas/alta_ComponentesMinialmacen.js"></script>
    </head>
    <body>
        <div class="principal">
            <img class="imagenMouse" src="resources/images/add.png" title="Nuevo" onclick='agregarComponenteMinialmacen("<?php echo $alta; ?>", "<?php echo $idminiAlmacen; ?>", "<?php echo $idCliente; ?>");' style="float: right; cursor: pointer;" />  
            <br/><br/><br/>
            <table id="tAlmacen" style="width: 100%;" class="tabla_datos">
                <thead>
                    <tr>
                        <td>Componente</td><td>Cantidad existencia</td><td>Cantidad mínima</td><td>Cantidad máxima</td><td></td><td></td>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    /* Inicializamos la clase */
                    $catalogo = new Catalogo();
                    $query = $catalogo->obtenerLista("SELECT cm.IdMiniAlmacen,cm.NoParteComponente,cm.CantidadExistencia,cm.CantidadMinima,cm.CantidadMaxima,c.Modelo,ma.Nombre
                                                        FROM c_minialmacen ma,c_componente c,k_componentes_miniAlmacen cm
                                                        WHERE ma.IdMiniAlmacen=cm.IdMiniAlmacen AND cm.NoParteComponente=c.NoParte AND cm.IdMiniAlmacen='" . $idminiAlmacen . "'");
                    while ($rs = mysql_fetch_array($query)) {
                        echo "<tr>";
                        echo "<td align='center' >" . $rs['Modelo'] . "</td>";
                        echo "<td align='center' >" . $rs['CantidadExistencia'] . "</td>";
                        echo "<td align='center' >" . $rs['CantidadMinima'] . "</td>";
                        echo "<td align='center' >" . $rs['CantidadMaxima'] . "</td>";
                        ?>                   
                    <td align='center' scope='row'> <a href='#' onclick='editarComponenteMini("<?php echo $alta ?>", "<?php echo $idCliente ?>", "<?php echo $rs['IdMiniAlmacen'] ?>", "<?php echo $rs['NoParteComponente'] ?>");
                        return false;' title='Editar Detalle' ><img src="resources/images/Modify.png"/></a></td>
                    <td align='center' scope='row'> <a href='#' onclick='eliminarRegistroMini("<?php echo $controlador . "?id=" . $rs['IdMiniAlmacen']; ?>", "<?php echo $same_page; ?>", "<?php echo $idCliente ?>");
                        return false;'><img src="resources/images/Erase.png"/></a> </td>                                        
                        <?php
                        echo "</tr>";
                    }
                    ?>
                </tbody>
            </table>
            <br/>
            <input type="button" id="regresar" name="regresar" title="Regresar a mini almacén" value="Regresar a mini almacén" class="boton" style="float: right" onclick="editarRegistro('almacen/lista_miniAlmacen.php', '<?php echo $idCliente ?>');"/>
         <br/> <br/>
        </div>
    </body>
</html>