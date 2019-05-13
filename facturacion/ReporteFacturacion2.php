<?php
session_start();

if (!isset($_SESSION['user']) || $_SESSION['user'] == "") {
    header("Location: index.php");
}

include_once("../WEB-INF/Classes/ReporteFacturacion.class.php");
include_once("../WEB-INF/Classes/Catalogo.class.php");
include_once("../WEB-INF/Classes/DatosFacturacionEmpresa.class.php");
include_once("../WEB-INF/Classes/CatalogoFacturacion.class.php");
include_once("../WEB-INF/Classes/PermisosSubMenu.class.php");
include_once("../WEB-INF/Classes/Empresa.class.php");
$permisos_grid = new PermisosSubMenu();
$same_page = "facturacion/ReporteFacturacion.php";
$permisos_grid->getPermisosSubmenu($_SESSION['idUsuario'], $same_page);
$catalogo = new Catalogo();
$empresa = new Empresa();
$mostrarBoton33 = $empresa->hayEmpresasCFDI33();

?>

<script type="text/javascript" language="javascript" src="resources/js/paginas/facturacion/reportefacturacion.js"></script>
<br/><br/>
<form id="rfactura">
    <table style="width: 100%;">
        <tr>

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
            <td>Folio</td>
            <td>
                <input type="text" id="folio" name="folio"/>
            </td>
        </tr>
        <tr>
            <td>RFC Cliente</td>
            <td>
                <select id="rfccliente" name="rfccliente" style="width: 200px;" >
                    <?php
                    $consulta = "SELECT DISTINCT(c.RFC) AS RFC FROM c_cliente AS c WHERE Activo = 1 AND RFC<>\"\" ORDER BY RFC";
                    $query = $catalogo->obtenerLista($consulta);
                    echo "<option value=''>RFC Cliente</option>";
                    while ($rs = mysql_fetch_array($query)) {
                        echo "<option value='" . $rs['RFC'] . "' >" . $rs['RFC'] . "</option>";
                    }
                    ?> 
                </select>
            </td>       
            <td>Cliente</td>
            <td>
                <select id="cliente" name="cliente" style="width: 200px;">
                    <?php
                    echo "<option value=''>Todos los clientes</option>";
                    $consulta = "SELECT DISTINCT(c.RFC) AS RFC, c.NombreRazonSocial AS Nombre FROM c_cliente AS c WHERE Activo = 1 AND RFC<>\"\" ORDER BY Nombre";
                    $query = $catalogo->obtenerLista($consulta);
                    while ($rs = mysql_fetch_array($query)) {
                        echo "<option value='" . $rs['RFC'] . "' >" . $rs['Nombre'] . "</option>";
                    }
                    ?>
                </select>
            </td>
            <td></td>
            <td>
            </td>
        </tr>

        <tr>
            <td>Estado</td>
            <td>
                <select id="status" name="status" style="width: 200px;">
                    <?php
                    echo "<option value=''>Todos</option>";
                    echo "<option value='2'>Cancelada</option>";
                    echo "<option value='0'>No Pagada</option>";
                    echo "<option value='3'>Incobrable</option>";
                    echo "<option value='4'>Pagadas</option>";
                    ?>
                </select>
            </td>       
            <!--<td>Tipo de documento</td>
            <td>
                <select id="docto" name="docto" style="width: 200px;">
            <?php
            echo "<option value=''>Todos</option>";
            echo "<option value='ingreso'>Factura</option>";
            //echo "<option value='egreso'>Nota de crédito</option>";
            ?>
                </select>
            </td>
            -->
            <td></td>
            <td>
            </td>
        </tr>

    </table>
    <input type="submit" id="enviar" value="Mostrar" class="boton" style="margin-left: 83%;"/>
</form>
<br/><br/>
<div style="float: right;">
<?php if ($permisos_grid->getAlta()) { ?>
    Factura 3.2 <img src="resources/images/add.png" title="Nuevo" onclick='cambiarContenidos("facturacion/alta_factura.php", "Facturación");' style="cursor: pointer;margin-bottom: -10px;"/>&nbsp;&nbsp;&nbsp;&nbsp;
<?php } ?>
<?php if ($mostrarBoton33) { ?>
    Factura 3.3 <img src="resources/images/add.png" title="Nuevo" onclick='cambiarContenidos("facturacion/alta_factura_33.php", "Facturación 3.3");' style="cursor: pointer;margin-bottom: -10px;"/>  
<?php } ?>
</div>
    
    <div id="tablamensajeinfo"></div>
    <div id="tablainfo"></div>
