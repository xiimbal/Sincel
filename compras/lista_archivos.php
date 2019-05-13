<?php
session_start();
if (!isset($_SESSION['user']) || $_SESSION['user'] == "") {
    header("Location: ../../index.php");
}
include_once("../WEB-INF/Classes/Catalogo.class.php");
include_once("../WEB-INF/Classes/PermisosSubMenu.class.php");
$catalogo = new Catalogo();
$alta = "compras/alta_archivos.php";
$rutaPdf = "WEB-INF/Controllers/documentos/facturasProveedor/factura/";
$rutaxml = "WEB-INF/Controllers/documentos/facturasProveedor/xml/";
$controlador = "WEB-INF/Controllers/compras/Controler_Archivos_Proveedor.php";
$same_page = "compras/lista_archivos.php";
$permisos_grid = new PermisosSubMenu();
$permisos_grid->getPermisosSubmenu($_SESSION['idUsuario'], $same_page);
$folio = "";
$proveedor = "";
$mostrar = "";
$FechaInicio = "";
$FechaFin = "";
$empresa = "";
$where = "";
$limit = "LIMIT 100";

if (isset($_POST['folio']) && $_POST['folio'] != "") {
    $folio = $_POST['folio'];
    $mostrar = "checked";
    $limit = "";
}
if (isset($_POST['proveedor']) && $_POST['proveedor'] != "0") {
    $proveedor = $_POST['proveedor'];
    $limit = "";
    if ($where == "") {
        $where = " WHERE p.ClaveProveedor='$proveedor'";
    } else {
        $where .= " AND p.ClaveProveedor='$proveedor'";
    }
}
if (isset($_POST['mostrar']) && $_POST['mostrar'] == "1") {
    $mostrar = "checked";
    $limit = "";
}
if (isset($_POST['fechaInicio']) && $_POST['fechaInicio'] != "" && isset($_POST['fechaFin']) && $_POST['fechaFin'] != "") {
    $FechaInicio = $_POST['fechaInicio'];
    $FechaFin = $_POST['fechaFin'];
    $limit = "";
    if ($where == "") {
        $where = " WHERE fp.Fecha BETWEEN '$FechaInicio' AND '$FechaFin'";
    } else {
        $where .= " AND fp.Fecha BETWEEN '$FechaInicio' AND '$FechaFin'";
    }
}
if ($FechaInicio != "" && $FechaFin != "") {
    $limit = "";
}
if (isset($_POST['empresa']) && $_POST['empresa'] != "0") {
    $empresa = $_POST['empresa'];
    $limit = "";
    if ($where == "") {
        $where = " WHERE df.IdDatosFacturacionEmpresa='$empresa'";
    } else {
        $where .= " AND df.IdDatosFacturacionEmpresa ='$empresa'";
    }
}
?>
<!DOCTYPE html>
<html>
    <head>
        <script type="text/javascript" language="javascript" src="resources/js/paginas/lista.js"></script>
        <script type="text/javascript" language="javascript" src="resources/js/paginas/compras/alta_archivos.js"></script>
        <script>
            $(".button").button();
        </script>
    </head>
    <body>
        <div class="principal">
            <table style="width: 100%;">
                <tr>
                    <td>Folio factura</td><td><input type="text" id="txtFolioL" name="txtFolioL" value="<?php echo $folio ?>"/></td>
                    <td>Proveedor</td>
                    <td>
                        <select id="slProveedorL" name="slProveedorL" style="width: 155px">
                            <option value="0">Todos los proveedores</option>
                            <?php
                            $queryProveedor = $catalogo->getListaAlta("c_proveedor", "NombreComercial");
                            while ($rs = mysql_fetch_array($queryProveedor)) {
                                $s = "";
                                if ($proveedor == $rs['ClaveProveedor']) {
                                    $s = "selected";
                                }
                                echo "<option value='" . $rs['ClaveProveedor'] . "' $s>" . $rs['NombreComercial'] . "</option>";
                            }
                            ?>
                        </select>    
                    </td>
                    <td>Mostrar mas de 100</td><td><input type="checkbox" id="ckMostrar" name="ckMostrar" <?php echo $mostrar; ?>/></td>
                </tr>
                <tr>
                    <td>Fecha inicio</td><td><input type="text" id="txtFechaInicioL" name="txtFechaInicioL" value="<?php echo $FechaInicio; ?>"/></td>
                    <td>Fecha fin</td><td><input type="text" id="txtFechaFinL" name="txtFechaFinL" value="<?php echo $FechaFin; ?>"/></td>
                    <td></td><td></td>
                </tr>
                <tr>
                    <td>Empresa que compra</td>
                    <td>
                        <select id="slEmpresaL" name="slEmpresaL" style="width: 155px">
                            <option value="0">Todos las empresas</option>
                            <?php
                            $queryEmpresa = $catalogo->getListaAlta("c_datosfacturacionempresa", "RazonSocial");
                            while ($rs = mysql_fetch_array($queryEmpresa)) {
                                $s = "";
                                if ($empresa == $rs['IdDatosFacturacionEmpresa']) {
                                    $s = "selected";
                                }
                                echo "<option value='" . $rs['IdDatosFacturacionEmpresa'] . "' $s>" . $rs['RazonSocial'] . "</option>";
                            }
                            ?>
                        </select>    
                    </td>
                    <td></td><td></td>
                    <td></td><td></td>
                </tr>
            </table>            
            <br/>
            <input type="button" value="Buscar" id="btnBuscar" name="btnBuscar" class="button" style="float: left" onclick="buscarFactura();"/>
            <?php if($permisos_grid->getAlta()){ ?>
            <img class="imagenMouse" src="resources/images/add.png" title="Nuevo" onclick='cambiarContenidos("<?php echo $alta; ?>");' style="float: right; cursor: pointer;" />  
            <?php } ?>
            <br/><br/><br/>
            <table id="tAlmacen" style="width: 100%;">
                <thead>
                    <tr>
                        <th align='center' scope='row' style="width: 10%">Factura</th>
                        <th align='center' scope='row' style="width: 15%">Fecha factura</th>
                        <th align='center' scope='row' style="width: 20%">Proveedor</th>
                        <th align='center' scope='row' style="width: 25%">Empresa que compro</th>
                        <th align='center' scope='row' style="width: 15%">Importe</th>    
                        <th align='center' scope='row' style="width: 5%">Consultar</th>
                        <th align='center' scope='row' style="width: 5%">Consultar</th>
                        <th align='center' scope='row' style="width: 5%">Eliminar</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    if ($folio == "") {
                        $consulta = "SELECT fp.IdFacturaProveedor,fp.Folio,fp.Fecha,p.NombreComercial,df.RazonSocial,fp.Total,fp.PathFactura,fp.PathFactura,fp.PathXML,fp.IdOrdenCompra   
                                FROM c_factura_proveedor fp LEFT JOIN c_proveedor p ON fp.IdEmisor=p.ClaveProveedor LEFT JOIN c_datosfacturacionempresa df ON df.IdDatosFacturacionEmpresa=fp.IdReceptor $where ORDER BY fp.Fecha DESC $limit";
                    } else {
                        $consulta = "SELECT fp.IdFacturaProveedor,fp.Folio,fp.Fecha,p.NombreComercial,df.RazonSocial,fp.Total,fp.PathFactura,fp.PathFactura,fp.PathXML,fp.IdOrdenCompra   
                                FROM c_factura_proveedor fp LEFT JOIN c_proveedor p ON fp.IdEmisor=p.ClaveProveedor LEFT JOIN c_datosfacturacionempresa df ON df.IdDatosFacturacionEmpresa=fp.IdReceptor WHERE fp.Folio='$folio' ORDER BY fp.Fecha";
                    }
                    $query = $catalogo->obtenerLista($consulta);
                    while ($rs = mysql_fetch_array($query)) {
                        echo "<tr>";
                        echo "<td align='center' scope='row'>" . $rs['Folio'] . "</td>";
                        echo "<td align='center' scope='row'>" . $rs['Fecha'] . "</td>";
                        echo "<td align='center' scope='row'>" . $rs['NombreComercial'] . "</td>";
                        echo "<td align='center' scope='row'>" . $rs['RazonSocial'] . "</td>";
                        echo "<td align='center' scope='row'>" . $rs['Total'] . "</td>";
                        ?>
                    <td align='center' scope='row'><a href="<?php echo $rutaxml . $rs['PathXML'] ?>" target="_blank"><img src="resources/images/icono_xml.png" width="25" height="25"/></a></td>
                    <td align='center' scope='row'><a href="<?php echo $rutaPdf . $rs['PathFactura'] ?>" target="_blank"><img src="resources/images/pdf_descarga.png" width="25" height="25"/></a></td>
                    <td align='center' scope='row'>
                        <?php if($permisos_grid->getBaja()){ ?>
                        <a href='#' onclick='eliminarRegistro("<?php echo $controlador . "?id=" . $rs['IdFacturaProveedor'] . "&idOrden=" . $rs['IdOrdenCompra']; ?>", "<?php echo $same_page; ?>");
                                return false;' title='Reporte' ><img src="resources/images/Erase.png" width="25" height="25"/></a>
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
