<?php
session_start();

if (!isset($_SESSION['user']) || $_SESSION['user'] == "") {
    header("Location: index.php");
}
include_once("../WEB-INF/Classes/Catalogo.class.php");
$urlextra = "";
if (isset($_GET['cliente'])) {
    $urlextra .= "?cliente=" . $_GET['cliente'];
    if (isset($_GET['vendedor'])) {
        $urlextra .= "&vendedor=" . $_GET['vendedor'];
    }
}
?>
<!DOCTYPE html>
<html lang="es">
    <head>                
        <script type="text/javascript" language="javascript" src="resources/js/paginas/ventas/Nueva_venta_directa.js"></script>
        <style>
            .size{width: 125px;}
        </style>
    </head>
    <body>
        <div class="principal">       
            <form id="ventadirecta">
                <table>
                    <?php
                    $catalogo = new Catalogo();
                    $query = $catalogo->obtenerLista("SELECT c_puesto.IdPuesto FROM `c_usuario` INNER JOIN c_puesto ON c_usuario.IdPuesto=c_puesto.IdPuesto WHERE c_usuario.IdUsuario=" . $_SESSION['idUsuario']);
                    $rs = mysql_fetch_array($query);
                    if ($rs['IdPuesto'] != 11) {
                        ?>
                        <tr>
                            <td>
                                <label for="vendedor">Vendedor:</label>
                            </td>
                            <td>
                                <select id="vendedor" name="vendedor" class="filtro" width="200" style="width: 200px" onchange="cargarclientes('vendedor', 'cliente')">
                                    <option value="">Selecciona el vendedor</option>
                                    <?php
                                    $query = $catalogo->obtenerLista("SELECT c_usuario.IdUsuario,CONCAT(c_usuario.Nombre,\" \",c_usuario.ApellidoPaterno,\" \",c_usuario.ApellidoMaterno) AS Nombre FROM `c_usuario` INNER JOIN c_puesto ON c_usuario.IdPuesto=c_puesto.IdPuesto WHERE c_puesto.IdPuesto=11 ORDER BY Nombre");
                                    if (isset($_GET['vendedor'])) {
                                        while ($rs = mysql_fetch_array($query)) {
                                            if ($rs['IdUsuario'] == $_GET['vendedor']) {
                                                echo "<option value=\"" . $rs['IdUsuario'] . "\" selected>" . $rs['Nombre'] . "</option>";
                                            } else {
                                                echo "<option value=\"" . $rs['IdUsuario'] . "\">" . $rs['Nombre'] . "</option>";
                                            }
                                        }
                                    } else {
                                        while ($rs = mysql_fetch_array($query)) {
                                            echo "<option value=\"" . $rs['IdUsuario'] . "\">" . $rs['Nombre'] . "</option>";
                                        }
                                    }
                                    ?>
                                </select>
                            </td>
                            <td>
                                <label for="cliente">Cliente:</label>
                            </td>
                            <td>
                                <select id="cliente" name="cliente" class="filtro" width="200" style="width: 200px" onchange="cambiarlocalidad('cliente', 'localidad');">
                                    <option value="">Selecciona el cliente</option>
                                    <?php
                                    if (isset($_GET['cliente'])) {
                                        $query = $catalogo->obtenerLista("SELECT
                                        c_cliente.NombreRazonSocial AS NombreCliente,
                                        c_cliente.ClaveCliente AS ClaveCliente
                                        FROM c_usuario
                                        INNER JOIN c_cliente ON c_cliente.EjecutivoCuenta = c_usuario.IdUsuario
                                        WHERE c_usuario.IdUsuario=" . $_GET['vendedor'] . "
                                        ORDER BY NombreCliente ASC");
                                        while ($rs = mysql_fetch_array($query)) {
                                            if ($rs['ClaveCliente'] == $_GET['cliente']) {
                                                echo "<option value=\"" . $rs['ClaveCliente'] . "\" selected>" . $rs['NombreCliente'] . "</option>";
                                            } else {
                                                echo "<option value=\"" . $rs['ClaveCliente'] . "\">" . $rs['NombreCliente'] . "</option>";
                                            }
                                        }
                                    }
                                    ?>
                                </select>
                            </td>
                            <?php
                        } else {
                            ?>
                        <tr>
                            <td>
                                <label for="cliente">Cliente:</label>
                            </td>
                            <td>
                                <input type="hidden" id="vendedor" name="vendedor" value="<?php echo $_SESSION['idUsuario']; ?>"/>
                                <select id="cliente" name="cliente" width="200" style="width: 200px" onchange="cambiarlocalidad('cliente', 'localidad');">
                                    <option value="">Selecciona el cliente</option>
                                    <?php
                                    $query = $catalogo->obtenerLista("SELECT
                                    c_cliente.NombreRazonSocial AS NombreCliente,
                                    c_cliente.ClaveCliente AS ClaveCliente
                                    FROM c_usuario
                                    INNER JOIN c_cliente ON c_cliente.EjecutivoCuenta = c_usuario.IdUsuario
                                    WHERE c_usuario.IdUsuario=" . $_SESSION['idUsuario'] . "
                                    ORDER BY NombreCliente ASC");
                                    if (isset($_GET['cliente'])) {
                                        while ($rs = mysql_fetch_array($query)) {
                                            if ($rs['ClaveCliente'] == $_GET['cliente']) {
                                                echo "<option value=\"" . $rs['ClaveCliente'] . "\" selected>" . $rs['NombreCliente'] . "</option>";
                                            } else {
                                                echo "<option value=\"" . $rs['ClaveCliente'] . "\">" . $rs['NombreCliente'] . "</option>";
                                            }
                                        }
                                    } else {
                                        while ($rs = mysql_fetch_array($query)) {
                                            echo "<option value=\"" . $rs['ClaveCliente'] . "\">" . $rs['NombreCliente'] . "</option>";
                                        }
                                    }
                                    ?>
                                </select>
                            </td>
                        <?php } ?>
                        <td>Localidad</td>
                        <td>
                            <select id="localidad" name="localidad" class="filtro localidad" width="200" style="width: 200px" >
                                <option value="">Selecciona la localidad</option>
                                <?php
                                if (isset($_GET['cliente'])) {
                                    $query = $catalogo->obtenerLista("SELECT c_centrocosto.ClaveCentroCosto AS ID,c_centrocosto.Nombre AS Nombre 
FROM c_centrocosto 
INNER JOIN c_cliente ON c_cliente.ClaveCliente=c_centrocosto.ClaveCliente
WHERE c_cliente.ClaveCliente='" . $_GET['cliente'] . "'");
                                    while ($rsp = mysql_fetch_array($query)) {
                                        echo "<option value=\"" . $rsp['ID'] . "\" >" . $rsp['Nombre'] . "</option>";
                                    }
                                }
                                ?>
                            </select>
                        </td>
                    </tr>
                    <tr>
                        <td><label for="fecha">Fecha</label></td>
                        <?php
                        $today = getdate();
                        ?>
                        <td><input type="text" id="Fecha" name="Fecha" class="fecha" width="200" style="width: 200px" value="<?php echo $today['year'] . "-" . $today['mon'] . "-" . $today['mday']; ?>"/></td>
                        <td></td><td></td>
                    </tr>
                </table>                
                <br/><br/><br/>
                <table id="pedidos">
                    <tr>
                        <td>
                            <label for="numero1">
                                Cantidad
                            </label>
                        </td>
                        <td>
                            <input type="text" id="numero1" name="numero1" class="size" style="width: 50px;" onkeyup="calcularcostocant('numero1', 'costo1', 'total1', 'costotro1');"/>
                        </td>
                        <td>
                            <label for="tipo1">
                                Tipo
                            </label>
                        </td>
                        <td>
                            <select id="tipo1" class="size filtro" name="tipo1" onchange="cambiarselectmodelo('tipo1', 'modelo1');" >
                                <option value="">Selecciona el tipo</option>
                                <option value="0">Equipo</option>
                                <?php
                                $query2 = $catalogo->obtenerLista("SELECT c_tipocomponente.IdTipoComponente AS ID,c_tipocomponente.Nombre AS Nombre FROM c_tipocomponente ORDER BY Nombre;");
                                while ($rs = mysql_fetch_array($query2)) {
                                    echo "<option value=\"" . $rs['ID'] . "\">" . $rs['Nombre'] . "</option>";
                                }
                                ?>
                            </select>
                        </td>
                        <td>
                            <label for="modelo1">
                                Modelo
                            </label>
                        </td>
                        <td>
                            <select id="modelo1" name="modelo1" class="size filtro" onchange="cargarprecio('tipo1', 'modelo1', 'costo1');"></select>
                        </td>
                        <td>
                            <label for="costo1">
                                Costo
                            </label>
                        </td>
                        <td>
                            <select id="costo1" name="costo1" class="size filtro" onchange="calcularcostop('numero1', 'costo1', 'total1', 'costotro1', 'otrolabel1', 'otroinput1');"></select>
                        </td>
                        <td id="otrolabel1">
                            <label for="costotro1">
                                Otro
                            </label>
                        </td>
                        <td id="otroinput1">
                            <input type="text" id="costotro1" name="costotro1" onkeyup="calcularcosto('numero1', 'costotro1', 'total1');" value="0">
                        </td>
                        <td>
                            <label for="total1">
                                Total
                            </label>
                        </td>
                        <td>
                            <input type="text" id="total1" name="total1" class="size" style="width: 50px;" readonly="readonly"/>
                        </td>
                    </tr>
                </table>
                <img class="imagenMouse" src="resources/images/Erase.png" title="Borrar fila" onclick='eliminarfilaulti();' style="float: right; cursor: pointer;" /><img class="imagenMouse" src="resources/images/add.png" title="Nueva fila" onclick='agregarcamposol();' style="float: right; cursor: pointer;" />  
                <br/><br/>
                <input type="submit" id="aceptar" class="boton" name="aceptar" value="Guardar"/>
                <input type="button" id="cancelar" class="boton" name="cancelar" value="Cancelar" onclick="cambiarContenidos('ventas/Ventas_Directas.php<?php echo $urlextra ?>', 'Ventas directas');"/>
            </form>
        </div>
        <br/><br/>
    </body>
</html>
<script>
<?php
echo "setpaginaExito('ventas/Ventas_Directas.php" . $urlextra . "')";
?>
</script>
