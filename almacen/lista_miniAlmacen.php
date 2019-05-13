<?php
session_start();

if (!isset($_SESSION['user']) || $_SESSION['user'] == "") {
    header("Location: index.php");
}
$idCliente = $_POST["id"];
$cliente = "";
include_once("../WEB-INF/Classes/Catalogo.class.php");
$controlador = $_SESSION['ruta_controler'] . "Controler_MiniAlmacen.php";
$alta = "almacen/alta_minialmacen.php";
$same_page = "almacen/lista_miniAlmacen.php";
$consultaCliente = new Catalogo();
$query = $consultaCliente->obtenerLista("SELECT * FROM c_cliente c,c_centrocosto cc  WHERE c.ClaveCliente='" . $idCliente . "' AND c.ClaveCliente=cc.ClaveCliente");
if ($rs = mysql_fetch_array($query)) {
    $cliente = $rs['NombreRazonSocial'];
}

echo "</br></br>Mini almacen del cliente: <b>" . $cliente . "</b>";
?>
<!DOCTYPE html>
<html lang="es">
    <head>
        <script type="text/javascript" language="javascript" src="resources/js/paginas/lista.js"></script>      
        <script type="text/javascript" language="javascript" src="resources/js/paginas/alta_miniAlmacen.js"></script>
    </head>
    <body>
        <div class="principal">
            <img class="imagenMouse" src="resources/images/add.png" title="Nuevo" onclick='editarRegistro("<?php echo $alta; ?>", "<?php echo $idCliente; ?>");' style="float: right; cursor: pointer;" />  
            <br/><br/><br/>
            <table id="tAlmacen" style="width: 100%;" class="tabla_datos">
                <thead>
                    <tr>
                        <td>Mini almacén</td><td>Localidad</td><td>Encargado</td><td></td><td></td><td></td>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    /* Inicializamos la clase */
                    $catalogo = new Catalogo();
                    $query = $catalogo->obtenerLista("SELECT ma.Nombre AS nombreMini,ma.Descripcion,cc.Nombre AS localidad,CONCAT(u.Nombre,' ',u.ApellidoPaterno) AS encargado,ma.ClaveCentroCosto,ma.ClaveEncargado,c.NombreRazonSocial AS cliente,ma.IdMiniAlmacen
                                                        FROM c_minialmacen ma,c_centrocosto cc,c_usuario u,c_cliente c
                                                        WHERE ma.ClaveCentroCosto=cc.ClaveCentroCosto
                                                        AND ma.ClaveEncargado=u.IdUsuario 
                                                        AND cc.ClaveCliente=c.ClaveCliente AND c.ClaveCliente='" . $idCliente . "'");
                    while ($rs = mysql_fetch_array($query)) {
                        echo "<tr>";
                        echo "<td align='center' >" . $rs['nombreMini'] . "</td>";
                        echo "<td align='center' >" . $rs['localidad'] . "</td>";
                        echo "<td align='center' >" . $rs['encargado'] . "</td>";
                        ?>
                    <td align='center' scope='row'>                        
                        <a href='#' onclick='editarRegistroMini("almacen/lista_componentesMiniAlmacen.php", "<?php echo $rs['IdMiniAlmacen'] ?>","<?php echo $idCliente ?>");
                        return false;' title='Lista componentes' >
                            <img src="resources/images/Apply.png"/>
                        </a>
                    </td>
                    <td align='center' scope='row'> <a href='#' onclick='editarRegistroMini("<?php echo $alta ?>", "<?php echo $rs['IdMiniAlmacen'] ?>", "<?php echo $idCliente ?>");
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
            <input type="button" id="regresar" name="regresar" title="Regresar a la lista de clientes" value="Regresar a Clientes" class="boton" style="float: right" onclick="cambiarContenidos('ventas/mis_clientes.php');"/>
        </div>
    </body>
</html>