<?php
session_start();

if (!isset($_SESSION['user']) || $_SESSION['user'] == "") {
    header("Location: index.php");
}
include_once("../WEB-INF/Classes/ReporteFacturacion.class.php");
include_once("../WEB-INF/Classes/Catalogo.class.php");

$catalogo = new Catalogo();
?>
<script type="text/javascript" language="javascript" src="resources/js/paginas/facturacion/MenuPDF.js"></script>
<br/><br/>
<form id="rfactura">
    <table style="width: 100%;">
        <tr>
            <td>RFC</td>
            <td>
                <select id="RFC" name="RFC">
                    <option value="">Selecciona el RFC</option>
                    <?php
                    $query = $catalogo->obtenerLista("SELECT DISTINCT c_cliente.RFC FROM c_cliente ORDER BY RFC");
                    while ($rs = mysql_fetch_array($query)) {
                        echo "<option value='" . $rs['RFC'] . "' >" . $rs['RFC'] . "</option>";
                    }
                    ?>
                </select>
            </td>
            <td>Fecha inicio</td>
            <td>
                <input type="text" id="fecha1" name="fecha1" value="<?php
                if (isset($_GET['fecha1']) && $_GET['fecha1'] != "") {
                    $llamar = true;
                    echo $_GET['fecha1'];
                }
                ?>"/>
            </td>
            <td>Fecha Fin</td>
            <td>
                <input type="text" id="fecha2" name="fecha2" value="<?php
                if (isset($_GET['fecha2']) && $_GET['fecha2'] != "") {
                    $llamar = true;
                    echo $_GET['fecha2'];
                }
                ?>"/>
            </td>
        </tr>
        <tr>
            <td>Vendedor</td>
            <td>
                <select id="vendedor" name="vendedor" style="width: 200px;" onchange="cargarclientes('vendedor', 'cliente');">
                    <?php
                    $query = $catalogo->obtenerLista("SELECT CONCAT(c_usuario.ApellidoPaterno,' ',c_usuario.ApellidoMaterno,' ',c_usuario.Nombre) AS Nombre,
	c_usuario.IdUsuario AS ID
 FROM c_usuario
INNER JOIN c_puesto on c_usuario.IdPuesto=c_puesto.IdPuesto
WHERE c_puesto.IdPuesto=11
ORDER BY Nombre");
                    echo "<option value=''>Todos los vendedores</option>";
                    if (isset($_GET['vendedor']) && $_GET['vendedor'] != "") {
                        $llamar = true;
                        while ($rs = mysql_fetch_array($query)) {
                            if ($_GET['vendedor'] == $rs['ID']) {
                                echo "<option value='" . $rs['ID'] . "' selected>" . $rs['Nombre'] . "</option>";
                            } else {
                                echo "<option value='" . $rs['ID'] . "' >" . $rs['Nombre'] . "</option>";
                            }
                        }
                    } else {
                        while ($rs = mysql_fetch_array($query)) {
                            echo "<option value='" . $rs['ID'] . "' >" . $rs['Nombre'] . "</option>";
                        }
                    }
                    ?> 
                </select>
            </td>       
            <td>Cliente</td>
            <td>
                <select id="cliente" name="cliente" style="width: 200px;">
                    <?php
                    echo "<option value=''>Todos los clientes</option>";
                    ?> 
                </select>
            </td>
            <td></td>
            <td></td>
        </tr>

    </table>
    <input type="submit" id="enviar" value="Mostrar" class="boton" style="margin-left: 83%;"/>
</form>
<div id="tablainfo"></div>
