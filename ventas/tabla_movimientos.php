<?php
session_start();

if (!isset($_SESSION['user']) || $_SESSION['user'] == "") {
    header("Location: index.php");
}

include_once("../WEB-INF/Classes/Catalogo.class.php");
include_once("../WEB-INF/Classes/Parametros.class.php");
include_once("../WEB-INF/Classes/PermisosSubMenu.class.php");
include_once("../WEB-INF/Classes/Configuracion.class.php");
$catalogo = new Catalogo();
$permiso = new PermisosSubMenu();
$parametros = new Parametros();
$parametros->getRegistroById(8);
$where = "";
$where_pendiente = "WHERE srg.Contestado = 0";
$mostrar_pendientes_retirar = false;

$permisos_grid = new PermisosSubMenu();
$same_page = "ventas/lista_movimientos.php";
$permisos_grid->getPermisosSubmenu($_SESSION['idUsuario'], $same_page);
$marcar_facturado = $permisos_grid->tienePermisoEspecial($_SESSION['idUsuario'], 42);

$parametrosExcel = "";

if(isset($_POST['tipo']) && ($_POST['tipo'] == "" || $_POST['tipo']=="6")){
    $mostrar_pendientes_retirar = true;
}

if (isset($_POST['noserie']) && $_POST['noserie'] != "") {    
    $configuracion = new Configuracion();
    if($configuracion->getRegistroByNoSerie($_POST['noserie'])){
        $mostrar_pendientes_retirar = true;
        if ($where_pendiente == "") {
            $where_pendiente .= " WHERE sr.IdBitacora = '" . $configuracion->getId_bitacora() . "' ";
        } else {
            $where_pendiente .= " AND sr.IdBitacora = '" . $configuracion->getId_bitacora() . "' ";
        }
    }
    if ($where == "") {
        $where.=" WHERE me.NoSerie='" . $_POST['noserie'] . "' ";
    } else {
        $where.=" AND me.NoSerie='" . $_POST['noserie'] . "' ";
    }
    
    $parametrosExcel = "?noserie = ".$_POST['noserie'];
}
if (isset($_POST['tipo']) && $_POST['tipo'] != "") {
    if ($where == "") {
        $where.=" WHERE me.IdTipoMovimiento='" . $_POST['tipo'] . "' ";
    } else {
        $where.=" AND me.IdTipoMovimiento='" . $_POST['tipo'] . "' ";
    }   
    if($parametrosExcel == ""){
        $parametrosExcel.="?tipo=". $_POST['tipo'];
    }else{
        $parametrosExcel.="&tipo=". $_POST['tipo'];
    }
}

if (isset($_POST['NoRep']) && $_POST['NoRep'] != "") {
    $mostrar_pendientes_retirar = false;
    if ($where == "") {
        $where.=" WHERE rh.NumReporte='" . $_POST['NoRep'] . "' ";
    } else {
        $where.=" AND rh.NumReporte='" . $_POST['NoRep'] . "' ";
    }
    if($parametrosExcel == ""){
        $parametrosExcel.="?NoRep=". $_POST['NoRep'];
    }else{
        $parametrosExcel.="&NoRep=". $_POST['NoRep'];
    }
}

if (isset($_POST['cliente']) && $_POST['cliente'] != "") {
    $mostrar_pendientes_retirar = true;
    if ($where == "") {
        $where .= " WHERE (me.clave_cliente_anterior='" . $_POST['cliente'] . "' OR me.clave_cliente_nuevo='" . $_POST['cliente'] . "') ";
    } else {
        $where .= " AND (me.clave_cliente_anterior='" . $_POST['cliente'] . "' OR me.clave_cliente_nuevo='" . $_POST['cliente'] . "') ";
    }
    
    if ($where_pendiente == "") {
        $where_pendiente .= " WHERE c.ClaveCliente = '" . $_POST['cliente'] . "' ";
    } else {
        $where_pendiente .= " AND c.ClaveCliente = '" . $_POST['cliente'] . "' ";
    }
    
    if($parametrosExcel == ""){
        $parametrosExcel.="?cliente=". $_POST['cliente'];
    }else{
        $parametrosExcel.="&cliente=". $_POST['cliente'];
    }
}

if (isset($_POST['localidad']) && $_POST['localidad'] != "") {
    $mostrar_pendientes_retirar = true;
    if ($where == "") {
        $where .= " WHERE (me.clave_centro_costo_anterior='" . $_POST['localidad'] . "' OR me.clave_centro_costo_nuevo='" . $_POST['localidad'] . "') ";
    } else {
        $where .= " AND (me.clave_centro_costo_anterior='" . $_POST['localidad'] . "' OR me.clave_centro_costo_nuevo='" . $_POST['localidad'] . "') ";
    }
    
    if ($where_pendiente == "") {
        $where_pendiente .= " WHERE cc.ClaveCentroCosto = '" . $_POST['localidad'] . "' ";
    } else {
        $where_pendiente .= " AND cc.ClaveCentroCosto = '" . $_POST['localidad'] . "' ";
    }
    
    if($parametrosExcel == ""){
        $parametrosExcel.="?localidad=". $_POST['localidad'];
    }else{
        $parametrosExcel.="&localidad=". $_POST['localidad'];
    }
}

if (isset($_POST['retirado']) && $_POST['retirado'] == "0") {
    //$mostrar_pendientes_retirar = false;
    if ($where == "") {
        $where.= " WHERE rh.Retirado='0' ";
    } else {
        $where.= " AND rh.Retirado='0' ";
    }
    
    if($parametrosExcel == ""){
        $parametrosExcel.="?retirado=". $_POST['retirado'];
    }else{
        $parametrosExcel.="&retirado=". $_POST['retirado'];
    }
}

if (isset($_POST['fecha1']) && $_POST['fecha1'] != "" && isset($_POST['fecha2']) && $_POST['fecha2'] != "") {
    $mostrar_pendientes_retirar = true;
    if ($where == "") {
        $where.=" WHERE me.Fecha BETWEEN '" . $_POST['fecha1'] . " 00:00:00' AND '" . $_POST['fecha2'] . " 23:59:59' ";
    } else {
        $where.=" AND me.Fecha BETWEEN '" . $_POST['fecha1'] . "  00:00:00' AND '" . $_POST['fecha2'] . " 23:59:59' ";
    }
    
    if ($where_pendiente == "") {
        $where_pendiente.=" WHERE srg.FechaCreacion BETWEEN '" . $_POST['fecha1'] . "  00:00:00' AND '" . $_POST['fecha2'] . " 23:59:59' ";
    } else {
        $where_pendiente.=" AND srg.FechaCreacion BETWEEN '" . $_POST['fecha1'] . "  00:00:00' AND '" . $_POST['fecha2'] . " 23:59:59' ";
    }
    
    if($parametrosExcel == ""){
        $parametrosExcel.="?fecha1=". $_POST['fecha1']."&fecha2=". $_POST['fecha2'];
    }else{
        $parametrosExcel.="&fecha1=". $_POST['fecha1']."&fecha2=". $_POST['fecha2'];
    }
}

$consulta = "SELECT 
IF(ISNULL(me.clave_cliente_anterior),CONCAT('Almacén: ',(SELECT nombre_almacen FROM c_almacen WHERE id_almacen=me.almacen_anterior)),CONCAT('Cliente: ',(SELECT NombreRazonSocial FROM c_cliente WHERE ClaveCliente=me.clave_cliente_anterior),' - Localidad: ',(SELECT Nombre FROM c_centrocosto WHERE ClaveCentroCosto=me.clave_centro_costo_anterior))) AS Origen,
IF(ISNULL(me.clave_cliente_nuevo),CONCAT('Almacén: ',(SELECT nombre_almacen FROM c_almacen WHERE id_almacen=me.almacen_nuevo)),CONCAT('Cliente: ',(SELECT NombreRazonSocial FROM c_cliente WHERE ClaveCliente=me.clave_cliente_nuevo),' Localidad: ',(SELECT Nombre FROM c_centrocosto WHERE ClaveCentroCosto=me.clave_centro_costo_nuevo))) AS Destino,
GROUP_CONCAT(me.NoSerie,' (',e.Modelo,')') AS Equipos,
me.tipo_movimiento AS Tipo,
me.causa_movimiento AS Causa,
me.IdTipoMovimiento AS IdTipoMovimiento,
me.UsuarioCreacion AS usuario,
tm.Nombre AS TipoMovNombre,
rh.NumReporte AS NumReporte,
rh.Retirado AS Retirado,
me.Fecha AS FechaMovimiento,
me.FacturarMovimiento
FROM
reportes_historicos AS rh
INNER JOIN reportes_movimientos AS rm ON rm.id_reportes=rh.NumReporte
INNER JOIN movimientos_equipo AS me ON me.id_movimientos=rm.id_movimientos
LEFT JOIN c_tipomovimiento AS tm ON tm.IdTipoMovimiento=me.IdTipoMovimiento
LEFT JOIN c_bitacora AS b ON b.NoSerie=me.NoSerie
LEFT JOIN c_equipo AS e ON e.NoParte=b.NoParte
$where
GROUP BY rh.NumReporte ORDER BY me.Fecha DESC";
$query = $catalogo->obtenerLista($consulta);

$cabeceras = array("Fecha","Reporte", "Equipo","Tipo", "Origen", "Destino","Causa","Usuario","","Retirados");
if($marcar_facturado){
    array_push($cabeceras, "Facturar");
}
?>
<a href="ventas/tabla_movimientosXLS.php<?php echo $parametrosExcel; ?>" target="_blank" class="boton"><img src="resources/images/excel.png"></a>
<script type="text/javascript" language="javascript" src="resources/js/paginas/ventas/tabla_movimiento.js"></script>
<table class="table-responsive" id="movimientos">
    <thead>
        <tr>
            <?php
            foreach ($cabeceras as $a) {
                echo "<th width=\"2%\" align=\"center\" scope=\"col\">" . $a . "</th>";
            }
            ?>
        </tr>
    </thead>
    <tbody>
        <?php
        while ($rs = mysql_fetch_array($query)) {
            echo "<tr>";
            echo "<td width=\"2%\" align=\"center\" scope=\"col\">" . $rs['FechaMovimiento'] . "</td>";
            echo "<td width=\"2%\" align=\"center\" scope=\"col\">" . $rs['NumReporte'] . "</td>";
            echo "<td width=\"2%\" align=\"center\" scope=\"col\">" . $rs['Equipos'] . "</td>";
            //echo "<td width=\"2%\" align=\"center\" scope=\"col\">" . $rs['Modelo'] . "</td>";
            echo "<td width=\"2%\" align=\"center\" scope=\"col\">" . $rs['TipoMovNombre'] . "</td>";            
            echo "<td width=\"2%\" align=\"center\" scope=\"col\">" . $rs['Origen'] . "</td>";
            echo "<td width=\"2%\" align=\"center\" scope=\"col\">" . $rs['Destino'] . "</td>";
            echo "<td width=\"2%\" align=\"center\" scope=\"col\">" . $rs['Causa'] . "</td>";
            echo "<td width=\"2%\" align=\"center\" scope=\"col\">" . $rs['usuario'] . "</td>";
            echo "<td width=\"2%\" align=\"center\" scope=\"col\"><a href='WEB-INF/Controllers/Ventas/Controller_Reporte_Historico.php?noSolicitud=" . $rs['NumReporte'] . "' target='_blank' style='float: right;'><img src='resources/images/icono_impresora.jpg' width='30' height='30'/></a></td>";
            $s="";
            if($rs['Retirado']==1){
                $s="checked";
            }
            echo "<td width=\"2%\" align=\"center\" scope=\"col\">"; 
            if($permisos_grid->getModificar()){
                echo "<input type='checkbox' value='1' name='check".$rs['NumReporte']."' id='check".$rs['NumReporte']."' onclick='cambiarestatus(".$rs['NumReporte'].")' $s/>"; 
            }else{
                if(strcmp($s, "checked") == 0){
                    echo "<input type='checkbox' value='1' name='check".$rs['NumReporte']."' id='check".$rs['NumReporte']."' onclick='cambiarestatus(".$rs['NumReporte'].")' $s disabled/>"; 
                }
            }
            echo "</td>";
            if($marcar_facturado){
                $checked = "";
                if($rs['FacturarMovimiento'] == "1"){
                    $checked = "checked='checked'";
                }
                echo "<td width=\"2%\" align=\"center\" scope=\"col\">"
                . "<input type='checkbox' value='1' name='check_fac_".$rs['NumReporte']."' id='check_fac_".$rs['NumReporte']."' onclick='cambiarestatusFac(".$rs['NumReporte'].");' $checked/>"
                . "</td>";
            }
            echo "</tr>";
        }
        
        if($mostrar_pendientes_retirar){
            $query = $catalogo->obtenerLista("SELECT 'Pendiente' AS NumReporte, GROUP_CONCAT(b.NoSerie,' (',e.Modelo,')') AS Equipos, 
                CONCAT('Retiro pendiente de autorizar: ',srg.IdSolicitudRetiroGeneral) AS TipoMovNombre, DATE(srg.FechaReporte) AS FechaMovimiento, 
                CONCAT('Cliente: ', GROUP_CONCAT(c.NombreRazonSocial SEPARATOR ' '),' - Localidad: ',GROUP_CONCAT(cc.Nombre SEPARATOR ' ')) AS Origen,
                CONCAT('Almacén: ',a.nombre_almacen) AS Destino, srg.Causa_Movimiento AS Causa, srg.UsuarioCreacion AS usuario, srg.Clave, srg.IdSolicitudRetiroGeneral
                FROM `c_solictudretirogeneral` AS srg
                LEFT JOIN c_solicitudretiro AS sr ON sr.IdSolicitudRetiroGeneral = srg.IdSolicitudRetiroGeneral
                LEFT JOIN c_bitacora AS b ON b.id_bitacora = sr.IdBitacora
                LEFT JOIN c_equipo AS e ON e.NoParte = b.NoParte
                LEFT JOIN c_centrocosto AS cc ON cc.ClaveCentroCosto = sr.ClaveLocalidad
                LEFT JOIN c_cliente AS c ON c.ClaveCliente = cc.ClaveCliente
                LEFT JOIN c_almacen AS a ON a.id_almacen = sr.IdAlmacen
                $where_pendiente GROUP BY srg.IdSolicitudRetiroGeneral;");            
            while($rs = mysql_fetch_array($query)){
                echo "<tr>";
                echo "<td width=\"2%\" align=\"center\" scope=\"col\">" . $rs['FechaMovimiento'] . "</td>";
                echo "<td width=\"2%\" align=\"center\" scope=\"col\">" . $rs['NumReporte'];
                if($permiso->tienePermisoEspecial($_SESSION['idUsuario'], 13)){
                    echo "<br/><a target='_blank' href='".$parametros->getDescripcion()."aceptaRetiro.php?clv=".$rs['Clave']."&soli=".$rs['IdSolicitudRetiroGeneral']."&awr=1&uguid=".$_SESSION['idEmpresa']."'>Autorizar</a>
                        <br/><br/><a target='_blank' href='".$parametros->getDescripcion()."aceptaRetiro.php?clv=".$rs['Clave']."&soli=".$rs['IdSolicitudRetiroGeneral']."&awr=2&uguid=".$_SESSION['idEmpresa']."'>Rechazar</a>";
                }
                echo "</td>";
                echo "<td width=\"2%\" align=\"center\" scope=\"col\">" . $rs['Equipos'] . "</td>";
                //echo "<td width=\"2%\" align=\"center\" scope=\"col\">" . $rs['Modelo'] . "</td>";
                echo "<td width=\"2%\" align=\"center\" scope=\"col\">" . $rs['TipoMovNombre'] . "</td>";                
                echo "<td width=\"2%\" align=\"center\" scope=\"col\">" . $rs['Origen'] . "</td>";
                echo "<td width=\"2%\" align=\"center\" scope=\"col\">" . $rs['Destino'] . "</td>";
                echo "<td width=\"2%\" align=\"center\" scope=\"col\">" . $rs['Causa'] . "</td>";
                echo "<td width=\"2%\" align=\"center\" scope=\"col\">" . $rs['usuario'] . "</td>";
                echo "<td width=\"2%\" align=\"center\" scope=\"col\"></td>";                
                echo "<td width=\"2%\" align=\"center\" scope=\"col\"></td>";
                if($marcar_facturado){
                    echo "<td width=\"2%\" align=\"center\" scope=\"col\"></td>";
                }
                echo "</tr>";
            }
        }
        ?>
    </tbody>
</table>