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
    <div class="bg-light p-4 rounded">
        <div class="form-row">
            <div class="form-group col-12 col-md-4">
                <label for="" class="m-0">Fecha inicio</label>
                <input type="text" id="fecha1" name="fecha1" value="<?php
                    if (isset($_GET['fecha1']) && $_GET['fecha1'] != "") {
                        $llamar = true;
                        echo $_GET['fecha1'];
                    }
                ?>" class="form-control"/>
            </div>
            <div class="form-group col-12 col-md-4">
                <label for="" class="m-0">Fecha Fin</label>
                <input type="text" id="fecha2" name="fecha2" value="<?php
                    if (isset($_GET['fecha2']) && $_GET['fecha2'] != "") {
                        $llamar = true;
                        echo $_GET['fecha2'];
                    }
                ?>" class="form-control"/>
            </div>
            <div class="form-group col-12 col-md-4">
                <label for="" class="m-0">Folio</label>
                <input type="text" id="folio" name="folio" class="form-control"/>
            </div>
        </div>
        <div class="form-row">
            <div class="form-group col-12 col-md-4">
                <label for="" class="m-0">RFC Cliente</label>
                <select id="rfccliente" name="rfccliente" class="custom-select">
                    <?php
                        $consulta = "SELECT DISTINCT(c.RFC) AS RFC FROM c_cliente AS c WHERE Activo = 1 AND RFC<>\"\" ORDER BY RFC";
                        $query = $catalogo->obtenerLista($consulta);
                        echo "<option value=''>RFC Cliente</option>";
                        while ($rs = mysql_fetch_array($query)) {
                            echo "<option value='" . $rs['RFC'] . "' >" . $rs['RFC'] . "</option>";
                        }
                    ?> 
                </select>
            </div>
            <div class="form-group col-12 col-md-4">
                <label for="selcliente" class="m-0">Cliente</label>
                <select id="selcliente" name="cliente" class="custom-select">
                    <?php
                        echo "<option value=''>Todos los clientes</option>";
                        $consulta = "SELECT DISTINCT(c.RFC) AS RFC, c.NombreRazonSocial AS Nombre FROM c_cliente AS c WHERE Activo = 1 AND RFC<>\"\" ORDER BY Nombre";
                        $query = $catalogo->obtenerLista($consulta);
                        while ($rs = mysql_fetch_array($query)) {
                            echo "<option value='" . $rs['RFC'] . "' >" . $rs['Nombre'] . "</option>";
                        }
                    ?>
                </select>
            </div>
            <div class="form-group col-12 col-md-4">
                <label for="estado" class="m-0">Estado</label>
                <select id="estado" name="status" class="custom-select">
                    <?php
                        echo "<option value=''>Todos</option>
                            <option value='2'>Cancelada</option>
                            <option value='0'>No Pagada</option>
                            <option value='3'>Incobrable</option>
                            <option value='4'>Pagadas</option>";
                    ?>
                </select>
            </div>
        </div>
        <div class="form-row">
            <div class="form-group col-12 col-md-4">
                <input type="submit" id="enviar" value="Mostrar" class="btn btn-secondary"/>
            </div>
        </div>
    </div>
</form>
<br/><br/>
<div style="float: left;">
<?php if ($permisos_grid->getAlta()) { ?>
    Factura 3.2 <img src="resources/images/add.png" title="Nuevo" onclick='cambiarContenidos("facturacion/alta_factura.php", "Facturación");' style="cursor: pointer;margin-bottom: -10px;"/>&nbsp;&nbsp;&nbsp;&nbsp;
<?php } ?>
<?php if ($mostrarBoton33) { ?>
    Factura 3.3 <img src="resources/images/add.png" title="Nuevo" onclick='cambiarContenidos("facturacion/alta_factura_33.php", "Facturación 3.3");' style="cursor: pointer;margin-bottom: -10px;"/>  
<?php } ?>
</div>
    
    <div id="tablamensajeinfo"></div>
    <div id="tablainfo"></div>
