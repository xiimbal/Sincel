<?php
session_start();

if (!isset($_SESSION['user']) || $_SESSION['user'] == "") {
    header("Location: index.php");
}

include_once("../WEB-INF/Classes/Catalogo.class.php");
include_once("../WEB-INF/Classes/PermisosSubMenu.class.php");
include_once("../WEB-INF/Classes/Usuario.class.php");

$permisos_grid = new PermisosSubMenu();
$same_page = "facturacion/lista_periodosinFacturar.php";
$alta = "facturacion/alta_peridosinFacturar.php";
$permisos_grid->getPermisosSubmenu($_SESSION['idUsuario'], $same_page);
$catalogo = new Catalogo();
$usuario = new Usuario();

$cabeceras = array("Periodo","Cliente","Serie","Color o B/N","Servicio","Renta B/N", "Inc. B/N", "Inc. Color", 
    "Excedentes B/N", "Excedentes Color", "Procesados B/N", "Procesados Color", "Mes Completo", "Facturado" ,"Comentario", "");
$columnas = array("Periodo","Cliente","NoSerie","ColorBN","Servicio","RentaMensual","IncluidasBN","IncluidasColor","CostoExcedentesBN",
    "CostoExcedentesColor","CostoProcesadaBN","CostoProcesadaColor","FacturaTodoElMes","EquipoFacturado","Comentario");

$where = " WHERE kef.EquipoFacturado = 0 ";
$having = "";
$tiene_filtro = false;
$periodo = "";
$serie = "";
$facturado = "";

if(isset($_POST['facturado']) && $_POST['facturado'] == "1"){
    $facturado = "checked = 'checked'";
    $where = " WHERE kef.EquipoFacturado IN (0,1) ";
}

if (isset($_POST['cliente']) && $_POST['cliente'] != "") {
    $tiene_filtro = true;
    $where .= " AND c.ClaveCliente IN (" . $_POST['cliente'] . ") ";
    $cliente_array = explode("','", $_POST['cliente']);
    $cliente_array[0] = substr($cliente_array[0], 1, strlen($cliente_array[0]));
    $cliente_array[count($cliente_array) - 1] = substr($cliente_array[count($cliente_array) - 1], 0, strlen($cliente_array[count($cliente_array) - 1]) - 1);
}

if (isset($_POST['servicio']) && $_POST['servicio'] != "") {
    $tiene_filtro = true;
    $having = " HAVING IdServicio IN (" . $_POST['servicio'] . ") ";
    $servicio_array = explode(",", $_POST['servicio']);
    //$servicio_array[0] = substr($servicio_array[0], 1, strlen($servicio_array[0]));
    //$servicio_array[count($servicio_array) - 1] = substr($servicio_array[count($servicio_array) - 1], 0, strlen($servicio_array[count($servicio_array) - 1]) - 1);
}

if(isset($_POST['periodo']) && $_POST['periodo'] != ""){
    $tiene_filtro = true;
    $periodo = $_POST['periodo'];
    $where .= " AND MONTH(p.Periodo) = ".  substr($_POST['periodo'], 0, 2)." AND YEAR(p.Periodo) =  ".  substr($_POST['periodo'], 3, 4)." ";
}

if(isset($_POST['serie']) && $_POST['serie'] != ""){
    $tiene_filtro = true;
    $serie = $_POST['serie'];
    $where .= " AND b.NoSerie = '".$_POST['serie']."' ";
}
?>
<!DOCTYPE html>
<html lang="es">
    <head>
        <script type="text/javascript" language="javascript" src="resources/js/paginas/lista.js"></script>
    </head>
    <body>
        <div class="principal">
            <table style="width: 100%;">
                <tr>
                    <td><label for="cliente">Cliente</label></td>
                    <td>
                        <select id="cliente" name="cliente[]" style="width: 200px;" multiple="multiple" class="multiselect">                            
                                <?php
                                    if($usuario->isUsuarioPuesto($_SESSION['idUsuario'], 21)){
                                        $query = $catalogo->obtenerLista("SELECT
                                            c_cliente.NombreRazonSocial AS NombreRazonSocial,
                                            c_cliente.ClaveCliente AS ClaveCliente
                                            FROM c_usuario
                                            INNER JOIN k_tfscliente ON k_tfscliente.IdUsuario=c_usuario.IdUsuario
                                            INNER JOIN c_cliente ON c_cliente.ClaveCliente = k_tfscliente.ClaveCliente
                                            WHERE c_usuario.IdUsuario=" . $_SESSION['idUsuario'] . " AND c_cliente.Activo=1
                                            ORDER BY NombreRazonSocial ASC");
                                    }else if($usuario->isUsuarioPuesto($_SESSION['idUsuario'], 11)){
                                        $query = $catalogo->obtenerLista("SELECT
                                            c_cliente.NombreRazonSocial AS NombreRazonSocial,
                                            c_cliente.ClaveCliente AS ClaveCliente
                                            FROM c_usuario
                                            INNER JOIN c_cliente ON c_cliente.EjecutivoCuenta = c_usuario.IdUsuario
                                            WHERE c_usuario.IdUsuario=" . $_SESSION['idUsuario'] . " AND c_cliente.Activo=1
                                            ORDER BY NombreRazonSocial ASC;");
                                    }else{
                                        $query = $catalogo->obtenerLista("SELECT * FROM c_cliente WHERE Activo=1 ORDER BY NombreRazonSocial");
                                    }
                                    while ($rs = mysql_fetch_array($query)) {   
                                        $s = "";
                                        if (isset($_POST['cliente']) && $_POST['cliente'] != "" && in_array($rs['ClaveCliente'], $cliente_array)) {
                                            $s = "selected='selected'";
                                        }
                                        echo "<option value=\"" . $rs['ClaveCliente'] . "\" $s>" . $rs['NombreRazonSocial'] . "</option>";
                                    }
                                ?>
                        </select>
                    </td>
                    <td><label for="tipo_servicio">Tipo de servicio</label></td>
                    <td>
                        <select id="tipo_servicio" name="tipo_servicio[]" style="width: 200px;" multiple="multiple" class="multiselect">                            
                            <?php
                                $consulta = "SELECT IdServicioIM AS IdServicio, Nombre, 'Impresión Particular' AS tipo FROM c_servicioim WHERE Activo = 1
                                    UNION
                                    SELECT IdServicioGIM AS IdServicio, Nombre, 'Impresión Global' AS tipo FROM c_serviciogim WHERE Activo = 1
                                    UNION
                                    SELECT IdServicioFA AS IdServicio, Nombre, 'FA Particular' AS tipo FROM c_serviciofa WHERE Activo = 1
                                    UNION
                                    SELECT IdServicioGFA AS IdServicio, Nombre, 'FA Global' AS tipo FROM c_serviciogfa WHERE Activo = 1
                                    ORDER BY tipo DESC, IdServicio;";
                                $result = $catalogo->obtenerLista($consulta);
                                while($rs = mysql_fetch_array($result)){
                                    $s = "";
                                    if (isset($_POST['servicio']) && $_POST['servicio'] != "" && in_array($rs['IdServicio'], $servicio_array)) {
                                        $s = "selected='selected'";
                                    }                                        
                                    echo "<option value=\"" . $rs['IdServicio'] . "\" $s>" . $rs['Nombre'] . " (".$rs['tipo'].")</option>";
                                }
                            ?>
                        </select>
                    </td>
                    <td><label for="periodo">Periodo</label></td>
                    <td>
                        <input type="text" id="periodo_psf" name="periodo_psf" class="fecha_periodo" value="<?php echo $periodo; ?>"/>
                    </td>
                    <td><label for="periodo">No. Serie</label></td>
                    <td>
                        <input type="text" id="no_serie_psf" name="no_serie_psf" value="<?php echo $serie; ?>"/>
                    </td>
                    <td>
                        <input type="checkbox" id="facturado" name="facturado" <?php echo $facturado; ?>/>Facturado
                    </td>
                </tr>
                <tr>
                    <td colspan="7"></td>
                    <td><input type="button" id="buscar_equipos" name="buscar_equipos" value="Buscar" 
                               onclick="recargarPeriodoSinFacturar('<?php echo $same_page?>','cliente','tipo_servicio','periodo_psf','no_serie_psf',
                                           'facturado'); return false;" class="boton" style="margin:0 0 0 33%; width: 49%;"/></td>
                </tr>
            </table>
            <br/><br/>
             <?php
                if (isset($_POST['mostrar']) && $_POST['mostrar'] == "true") {/* Si se quiere mostrar el grid */
            ?>
            <table id="tAlmacen" class="tabla_datos" style="max-width: 100%;">
                <thead>
                    <tr>
                        <?php
                        for ($i = 0; $i < (count($cabeceras)); $i++) {
                            echo "<th width=\"2%\" align=\"center\" scope=\"col\">" . $cabeceras[$i] . "</th>";
                        }                        
                        ?>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    /* Inicializamos la clase */                    
                    $consulta = "SELECT p.Periodo, p.IdPeriodo, c.ClaveCliente, c.RFC, c.NombreRazonSocial AS Cliente, b.NoSerie, b.id_bitacora, (CASE WHEN kef.ColorBN = 0 THEN 'B/N' ELSE 'Color' END) AS ColorBN, 
                            (CASE WHEN !ISNULL(cgim.IdServicioGIM) THEN cgim.Nombre 
                            WHEN !ISNULL(cgfa.IdServicioGFA) THEN cgfa.Nombre 
                            WHEN !ISNULL(cim.IdServicioIM) THEN cim.Nombre
                            WHEN !ISNULL(cfa.IdServicioFA) THEN cfa.Nombre END) AS Servicio,
                            (CASE WHEN !ISNULL(cgim.IdServicioGIM) THEN cgim.IdServicioGIM 
                            WHEN !ISNULL(cgfa.IdServicioGFA) THEN cgfa.IdServicioGFA 
                            WHEN !ISNULL(cim.IdServicioIM) THEN cim.IdServicioIM
                            WHEN !ISNULL(cfa.IdServicioFA) THEN cfa.IdServicioFA END) AS IdServicio,
                            kef.RentaMensual, kef.IncluidasBN, kef.IncluidasColor, kef.CostoExcedentesBN, kef.CostoExcedentesColor, kef.CostoProcesadaBN, kef.CostoProcesadaColor,
                            (CASE WHEN !ISNULL(fpRenta.FolioTimbrado) THEN fpRenta.FolioTimbrado ELSE CONCAT('Prefactura: ',kef.RentaMensualFacturado) END) AS RentaMensualFacturado, 
                            (CASE WHEN !ISNULL(fpExcedentesBN.FolioTimbrado) THEN fpExcedentesBN.FolioTimbrado ELSE CONCAT('Prefactura: ',kef.CostoExcedentesBNFacturado) END) AS CostoExcedentesBNFacturado,
                            (CASE WHEN !ISNULL(fpExcedentesCL.FolioTimbrado) THEN fpExcedentesCL.FolioTimbrado ELSE CONCAT('Prefactura: ',kef.CostoExcedentesColorFacturado) END) AS CostoExcedentesCLFacturado, 
                            (CASE WHEN !ISNULL(fpCostoPBN.FolioTimbrado) THEN fpCostoPBN.FolioTimbrado ELSE CONCAT('Prefactura: ',kef.CostoProcesadaBNFacturado) END) AS CostroProBNFacturado, 
                            (CASE WHEN !ISNULL(fpCostoPCL.FolioTimbrado) THEN fpCostoPCL.FolioTimbrado ELSE CONCAT('Prefactura: ',kef.CostoProcesadaColorFacturado) END) AS CostroProCLFacturado, 
                            (CASE WHEN kef.FacturaTodoElMes = 0 THEN 'No' ELSE 'Si' END) AS FacturaTodoElMes,
                            (CASE WHEN kef.EquipoFacturado = 0 THEN 'No' ELSE 'Si' END) AS EquipoFacturado,
                            kef.Comentario
                            FROM `k_equiposporfacturar` AS kef 
                            LEFT JOIN c_bitacora AS b ON b.id_bitacora = kef.IdBitacora
                            LEFT JOIN c_cliente AS c ON c.ClaveCliente = kef.ClaveCliente
                            LEFT JOIN c_periodo AS p ON p.IdPeriodo = kef.IdPeriodo
                            LEFT JOIN c_serviciogim AS cgim ON cgim.IdServicioGIM = kef.IdServicio
                            LEFT JOIN c_serviciogfa AS cgfa ON cgfa.IdServicioGFA = kef.IdServicio
                            LEFT JOIN c_servicioim AS cim ON cim.IdServicioIM = kef.IdServicio
                            LEFT JOIN c_serviciofa AS cfa ON cfa.IdServicioFA = kef.IdServicio
                            LEFT JOIN c_factura AS faRenta ON faRenta.IdFactura = kef.RentaMensualFacturado
                            LEFT JOIN c_folio_prefactura AS fpRenta ON fpRenta.Folio = faRenta.Folio AND fpRenta.IdEmisor = faRenta.RFCEmisor
                            LEFT JOIN c_factura AS faExcedentesBN ON faExcedentesBN.IdFactura = kef.CostoExcedentesBNFacturado
                            LEFT JOIN c_folio_prefactura AS fpExcedentesBN ON fpExcedentesBN.Folio = faExcedentesBN.Folio AND fpExcedentesBN.IdEmisor = faExcedentesBN.RFCEmisor
                            LEFT JOIN c_factura AS faExcedentesCL ON faExcedentesCL.IdFactura = kef.CostoExcedentesColorFacturado
                            LEFT JOIN c_folio_prefactura AS fpExcedentesCL ON fpExcedentesCL.Folio = faExcedentesCL.Folio AND fpExcedentesCL.IdEmisor = faExcedentesCL.RFCEmisor
                            LEFT JOIN c_factura AS faCostoPBN ON faCostoPBN.IdFactura = kef.CostoProcesadaBNFacturado
                            LEFT JOIN c_folio_prefactura AS fpCostoPBN ON fpCostoPBN.Folio = faCostoPBN.Folio AND fpCostoPBN.IdEmisor = faCostoPBN.RFCEmisor
                            LEFT JOIN c_factura AS faCostoPCL ON faCostoPCL.IdFactura = kef.CostoProcesadaColorFacturado
                            LEFT JOIN c_folio_prefactura AS fpCostoPCL ON fpCostoPCL.Folio = faCostoPCL.Folio AND fpCostoPCL.IdEmisor = faCostoPCL.RFCEmisor
                            $where $having";
                    if (!$tiene_filtro) {
                        $consulta.=" LIMIT 0,500";
                    }
                    $consulta .= ";";
                    //echo $consulta;
                    $query = $catalogo->obtenerLista($consulta);
                    while ($rs = mysql_fetch_array($query)) {
                        if($rs['EquipoFacturado'] == "No"){
                            $color = "#BB6C74";
                        }else{
                            $color = "#A0D08B";
                        }
                        
                        echo "<tr style='background-color: $color; font-weight: bold;'>";
                        echo "<td align='center' scope='row'>" . substr($catalogo->formatoFechaReportes($rs[$columnas[0]]), 5) . "</td>";                        
                        for($i=1;$i<count($columnas);$i++){
                            $liga = "";
                            if(($i == 5 || $i == 6 || $i==7) && isset($rs['RentaMensualFacturado']) && isset($rs[$columnas[$i]])){
                                if (strpos($rs['RentaMensualFacturado'],'Prefactura') !== false) {
                                    $liga = $rs['RentaMensualFacturado'];
                                }else{
                                    $liga = "<a href='principal.php?mnu=facturacion&action=ReporteFacturacion_net&id=" . $rs['RentaMensualFacturado'] . "&param4=".$rs['RFC']."&param1=0&param2=0&param3=0' target='_blank'>(" .  $rs['RentaMensualFacturado'] . ")</a>";
                                }
                            }
                            
                            
                            if(($i == 8) && isset($rs['CostoExcedentesBNFacturado']) && isset($rs[$columnas[$i]])){                                
                                if (strpos($rs['CostoExcedentesBNFacturado'],'Prefactura') !== false) {
                                    $liga = $rs['CostoExcedentesBNFacturado'];
                                }else{
                                    $liga = "<a href='principal.php?mnu=facturacion&action=ReporteFacturacion_net&id=" . $rs['CostoExcedentesBNFacturado'] . "&param4=".$rs['RFC']."&param1=0&param2=0&param3=0' target='_blank'>(" .  $rs['CostoExcedentesBNFacturado'] . ")</a>";
                                }
                            }
                            
                            
                            if( ($i == 9) && isset($rs['CostoExcedentesCLFacturado']) && isset($rs[$columnas[$i]]) ){
                                if (strpos($rs['CostoExcedentesCLFacturado'],'Prefactura') !== false) {
                                    $liga = $rs['CostoExcedentesCLFacturado'];
                                }else{
                                    $liga = "<a href='principal.php?mnu=facturacion&action=ReporteFacturacion_net&id=" . $rs['CostoExcedentesCLFacturado'] . "&param4=".$rs['RFC']."&param1=0&param2=0&param3=0' target='_blank'>(" .  $rs['CostoExcedentesCLFacturado'] . ")</a>";
                                }
                            }
                            
                            
                            if(($i == 10) && isset($rs['CostroProBNFacturado']) && isset($rs[$columnas[$i]])){
                                if (strpos($rs['CostroProBNFacturado'],'Prefactura') !== false) {
                                    $liga = $rs['CostroProBNFacturado'];
                                }else{
                                    $liga = "<a href='principal.php?mnu=facturacion&action=ReporteFacturacion_net&id=" . $rs['CostroProBNFacturado'] . "&param4=".$rs['RFC']."&param1=0&param2=0&param3=0' target='_blank'>(" .  $rs['CostroProBNFacturado'] . ")</a>";
                                }
                            }
                            
                            
                            if(($i == 11) && isset($rs['CostroProCLFacturado']) && isset($rs[$columnas[$i]])){
                                if (strpos($rs['CostroProCLFacturado'],'Prefactura') !== false) {
                                    $liga = $rs['CostroProCLFacturado'];
                                }else{
                                    $liga = "<a href='principal.php?mnu=facturacion&action=ReporteFacturacion_net&id=" . $rs['CostroProCLFacturado'] . "&param4=".$rs['RFC']."&param1=0&param2=0&param3=0' target='_blank'>(" .  $rs['CostroProCLFacturado'] . ")</a>";
                                }
                            }
                            
                            echo "<td align='center' scope='row'>" . $rs[$columnas[$i]] . " $liga</td>";
                        }
                        echo "<td align='center' scope='row'>";
                        if($permisos_grid->getModificar() && $rs['EquipoFacturado'] == "No"){
                            echo "<a href='#' onclick='editarComponentesAlmacen(\"$alta\",\"".$rs['IdPeriodo']."\",\"".$rs['id_bitacora']."\",\"".$rs['ClaveCliente']."\"); 
                                return false;' title='Editar Registro' > <img src=\"resources/images/Modify.png\"/></a>";
                        }
                        echo "</td>";                        
                    }
                    ?>
                </tbody>
            </table>
            <?php
                }//Fin del is para mostrar la tabla
            ?>
        </div>
    </body>
</html>