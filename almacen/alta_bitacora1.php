<?php
session_start();

if (!isset($_SESSION['user']) || $_SESSION['user'] == "") {
    header("Location: index.php");
}

include_once("../WEB-INF/Classes/Catalogo.class.php");
include_once("../WEB-INF/Classes/Usuario.class.php");
include_once("../WEB-INF/Classes/Configuracion.class.php");
include_once("../WEB-INF/Classes/CentroCosto.class.php");
include_once("../WEB-INF/Classes/Equipo.class.php");
include_once("../WEB-INF/Classes/EquipoCaracteristicasFormatoServicio.class.php");
include_once("../WEB-INF/Classes/Orden_Compra.class.php");
include_once("../WEB-INF/Classes/Parametros.class.php");
include_once("../WEB-INF/Classes/MovimientoComponente.class.php");
include_once("../WEB-INF/Classes/PermisosSubMenu.class.php");
$psm = new PermisosSubMenu();

$catalogo = new Catalogo();
$usuario = new Usuario();
$equipo = new Equipo();
$ordenCompra = new Orden_Compra();
$parametros = new Parametros();

if ($parametros->getRegistroById(8)) {
    $liga = $parametros->getDescripcion();
} else {
    $liga = "http://genesis2.techra.com.mx/genesis2/";
}

$id = "";
$id_solicitud = '';
$NoParte = '';
$NoSerie = '';
$NoGenesis = '';
$IP = '';
$MAC = '';
$NombreTipoInventario = '';
$ClaveCentroCosto = '';
$IdAnexoClienteCC = '';
$IdServicio = '';
$ClaveCliente = '';
$disabled = '';
$Modelo = '';
$mensaje = "";
$ventaDirecta = "";
$costo = "";
$folio = "";

$funciones = "";
$color = false;
$fa = false;

$obj = new Configuracion();

if (isset($_POST['id']) && $obj->getRegistroById($_POST['id'])) { /* Tenemos el id de la bitacora que se quiere ver */      
    if($obj->getRegistroById($_POST['id'])){
        $id = $obj->getId_bitacora();
        $id_solicitud = $obj->getId_solicitud();
        $NoParte = $obj->getNoParte();
        $NoSerie = $obj->getNoSerie();
        $NoGenesis = $obj->getNoGenesis();
        $IP = $obj->getIP();
        $MAC = $obj->getMac();
        $ClaveCentroCosto = $obj->getClaveCentroCosto();
        $IdAnexoClienteCC = $obj->getIdAnexoClienteCC();
        $IdServicio = $obj->getIdServicio();
        $CC = new CentroCosto();
        if ($CC->getRegistroById($ClaveCentroCosto)) {
            $ClaveCliente = $CC->getClaveCliente();
        }
        if ($obj->getIdTipoInventario() != null) {
            $NombreTipoInventario = $obj->getNombreTipoInventarioById($obj->getIdTipoInventario());
        }

        if ($obj->getVentaDirecta() == "1") {
            $ventaDirecta = "checked='checked'";
        }
    } else {
        $mensaje = "Este equipo no tiene datos";
    }
} else if (isset($_GET['consulta_tiquet']) && isset($_GET['NoSerie'])) {     
    if ($obj->getRegistroByNoSerie(trim($_GET['NoSerie']))) {
        $id = $obj->getId_bitacora();
        $id_solicitud = $obj->getId_solicitud();
        $NoParte = $obj->getNoParte();
        $NoSerie = $obj->getNoSerie();
        $NoGenesis = $obj->getNoGenesis();
        $IP = $obj->getIP();
        $MAC = $obj->getMac();
        $ClaveCentroCosto = $obj->getClaveCentroCosto();
        $IdAnexoClienteCC = $obj->getIdAnexoClienteCC();
        $IdServicio = $obj->getIdServicio();
        $CC = new CentroCosto();
        if ($CC->getRegistroById($ClaveCentroCosto)) {
            $ClaveCliente = $CC->getClaveCliente();
        }
        if ($obj->getIdTipoInventario() != null) {
            $NombreTipoInventario = $obj->getNombreTipoInventarioById($obj->getIdTipoInventario());
        }
    } else {
        $mensaje = "Este equipo con No. de Serie " . $_GET['NoSerie'] . " no tiene bitácora";
    }
}

if ($NoSerie != "") {
    $equipo->getRegistroById($NoParte);
    $Modelo = $equipo->getModelo();
    //$componentes = $obj->getComponentesK();
    $disabled = 'disabled="disabled"';

    $tipo = new EquipoCaracteristicasFormatoServicio();
    $result = $tipo->getTiposDeServicios($NoParte);
    while ($rs = mysql_fetch_array($result)) {
        $funciones .= "<input type='checkbox' checked='checked' disabled='disabled'/> " . $rs['servicio'];
    }

    /* Vemos si ponemos contadores de color o b/n */
    $contadores_color = array(0 => "ContadorBN", 1 => "ContadorCL", 2 => "NivelTonCian", 3 => "NivelTonNegro", 4 => "NivelTonMagenta", 5 => "NivelTonAmarillo");
    $cabeceras_color = array(0 => "Contador BN", 1 => "Contador color", 2 => "Nivel Cian", 3 => "Nivel Negro", 4 => "Nivel Magenta", 5 => "Nivel Amarillo");
    $contadores_bn = array(0 => "ContadorBN", 1 => "NivelTonNegro");
    $cabeceras_bn = array(0 => "Contador BN", 1 => "Nivel negro");
    
    $solicitados_color = array(0 => "TonerNegro", 1 => "TonerCian", 2 => "TonerMagenta", 3 => "TonerAmarillo");
    $cabeceras_s_color = array(0 => "Toner negro(*)", 1 => "Toner Cian(*)", 2 => "Toner Magenta(*)", 3 => "Toner Amarillo(*)");
    $solicitados_bn = array(0 => "TonerNegro");
    $cabeceras_s_bn = array(0 => "Toner Negro(*)");
    if($tipo->isColor($NoParte)){
        $color = true;
    }
    if($tipo->isFormatoAmplio($NoParte)){
        $fa = true;
    }
    
    $resultCompra = $ordenCompra->getCompraBySerie($NoSerie);
    while ($rsCompra = mysql_fetch_array($resultCompra)) {
        $folio = $rsCompra['FolioFactura'];
        $costo = $rsCompra['PrecioUnitario'];
    }
    
    $consulta = "SELECT b.NoSerie, b.NoParte,
        IF(ISNULL(csrg.IdSolicitudRetiroGeneral),'0',
        IF(csr.PendienteRetiro=0 AND csrg.Contestado=0,CONCAT('1',' - ',csr.IdSolicitudRetiroGeneral),/*Solicitud de retiro*/
        IF(csr.PendienteRetiro=1 AND csrg.Contestado=1 AND csrg.Aceptada=1,CONCAT('2',' - ',csr.IdSolicitudRetiroGeneral),
        IF(csrg.Aceptada=1 AND csrg.Contestado=1 AND csr.IdAlmacen!=9 AND cal.id_almacen=9 AND (ISNULL(rh.NumReporte) OR rh.Retirado = 0),CONCAT('3',' - ',rh.NumReporte),/*Solicitud retiro aceptada*/
        IF(rh.Retirado = 1 AND meq.pendiente = 1,CONCAT('4',' - ',rh.NumReporte),/*Retirado*/
        IF(meq.pendiente = 0,'0',/*Aceptado en almacen*/
        '0')))))) AS MoverRojo,
        t.IdTicket, cs.id_solicitud AS IdSolicitud,
        cie.Demo
        FROM c_bitacora AS b
        LEFT JOIN c_solicitudretiro AS csr ON csr.IdSolicitudRetiro = (SELECT MAX(IdSolicitudRetiro) FROM c_solicitudretiro WHERE IdBitacora = b.id_bitacora)
        LEFT JOIN c_solictudretirogeneral AS csrg ON csrg.IdSolicitudRetiroGeneral=csr.IdSolicitudRetiroGeneral
        LEFT JOIN movimientos_equipo AS meq ON meq.id_movimientos = (SELECT MAX(id_movimientos) FROM movimientos_equipo WHERE NoSerie = b.NoSerie AND DATE(Fecha) = DATE(csrg.FechaReporte) AND clave_centro_costo_anterior = csr.ClaveLocalidad)
        LEFT JOIN reportes_movimientos AS rm ON rm.id_movimientos = meq.id_movimientos
        LEFT JOIN reportes_historicos AS rh ON rh.NumReporte = rm.id_reportes
        LEFT JOIN k_almacenequipo AS ke ON ke.NoSerie=b.NoSerie
        LEFT JOIN c_almacen AS cal ON cal.id_almacen=ke.id_almacen
        LEFT JOIN c_ticket AS t ON t.IdTicket = 
        (SELECT MAX(t2.IdTicket) FROM c_ticket AS t2 
        LEFT JOIN c_notaticket AS nt ON nt.IdNotaTicket = (SELECT MAX(IdNotaTicket) FROM c_notaticket WHERE IdTicket = t2.IdTicket)
        LEFT JOIN c_pedido AS p ON p.IdTicket = t2.IdTicket
        WHERE (t2.NoSerieEquipo = b.NoSerie OR p.ClaveEspEquipo = b.NoSerie) AND t2.EstadoDeTicket NOT IN(2,4) AND (ISNULL(nt.IdEstatusAtencion) OR nt.IdEstatusAtencion NOT IN(16,59)))
        LEFT JOIN c_solicitud AS cs ON cs.id_solicitud = b.id_solicitud AND cs.estatus IN(0,1,2)
        LEFT JOIN c_inventarioequipo AS cie ON cie.NoSerie = b.NoSerie
        WHERE b.NoSerie = '$NoSerie';";
    $resultSolicitudes = $catalogo->obtenerLista($consulta);
    $mensajeSolicitudes = "";
    while($rs = mysql_fetch_array($resultSolicitudes)){
        if($rs['MoverRojo'] != "0"){
            $datos_aux = split(" - ", $rs['MoverRojo']);
            if($datos_aux[0] == "1" || $datos_aux[0] == "2"){
                $mensajeSolicitudes .= "<br/>El equipo está en la solicitud de retiro ".$datos_aux[1]." que falta por autorizar";
            }else if($datos_aux[0] == "3"){
                $mensajeSolicitudes .= "<br/>Solicitud de retiro aceptada: "
                        . "<a target='_blank' href='WEB-INF/Controllers/Ventas/Controller_Reporte_Historico.php?noSolicitud=".$datos_aux[1]."'>".$datos_aux[1]."</a>, falta retirar";
            }else if($datos_aux[0] == "4"){
                $mensajeSolicitudes .= "<br/>Falta entrada a almacén destino: "
                        . "<a target='_blank' href='WEB-INF/Controllers/Ventas/Controller_Reporte_Historico.php?noSolicitud=".$datos_aux[1]."'>".$datos_aux[1]."</a>";
            }
        }
        if(isset($rs['IdTicket']) && $rs['IdTicket'] != ""){
            $mensajeSolicitudes .= "<br/>El equipo tiene el ticket abierto: <a target='_blank' href='reportes/reporte_ticket.php?idTicket=".$rs['IdTicket']."'>".$rs['IdTicket']."</a>";
        }
        if(isset($rs['IdSolicitud']) && $rs['IdSolicitud'] != ""){
            $mensajeSolicitudes .= "<br/>El equipo se encuentra asignado a la solicitud: <a target='_blank' href='reportes/SolicitudEquipo.php?noSolicitud=".$rs['IdSolicitud']."'>".$rs['IdSolicitud']."</a>";
        }
        if(isset($rs['Demo']) && $rs['Demo'] == "1"){
            $mensajeSolicitudes .= "<br/>El equipo se encuentra en Demo";
        }
    }
}

if ($usuario->isUsuarioPuesto($_SESSION['idUsuario'], 22)) {/* Si es de almacen, solo puede poner el numero de serie */
    $soloSerie = "true";
} else {
    $soloSerie = "false";
}

$selects = Array("SELECT c_serviciofa.IdServicioFA AS ID,
				c_serviciofa.Nombre AS Nombre
    FROM c_serviciofa;", "SELECT c_serviciogfa.IdServicioGFA AS ID,
                           c_serviciogfa.Nombre
    FROM c_serviciogfa;", "SELECT c_serviciogim.IdServicioGIM AS ID,
                           c_serviciogim.Nombre AS Nombre
    FROM c_serviciogim;", "SELECT c_servicioim.IdServicioIM AS ID,
                           c_servicioim.Nombre
    FROM c_servicioim;");

$servicio = "<option value='null'>Selecciona el servicio</option>";
foreach ($selects as $select) {
    $query = $catalogo->obtenerLista($select);
    while ($rs = mysql_fetch_array($query)) {
        $s = "";
        if ($IdServicio != "" && $IdServicio == $rs['ID']) {
            $s = "selected='selected'";
        }
        $servicio = $servicio . "<option value=\"" . $rs['ID'] . "\" $s>" . $rs['Nombre'] . "</option>";
    }
}

$query = $catalogo->getListaAlta("c_componente", "NoParte");
$partes = "<option value=''>Selecciona el No. de parte</option>";
while ($rs = mysql_fetch_array($query)) {
    $partes = $partes . "<option value='" . $rs['NoParte'] . "'>" . $rs['NoParte'] . "</option>";
}

if (!isset($_GET['consulta_tiquet'])) {
    $atras = "almacen/lista_bitacora.php";
    $titulo_atras = "Bitácoras";
} else {
    $atras = $_GET['consulta_tiquet'];
    $titulo_atras = "Consulta ticket";
}
?>
<!DOCTYPE html>
<html lang="es">
    <head>                  
        <script type="text/javascript" language="javascript" src="resources/js/paginas/configuracion.js"></script>
        <script type="text/javascript" language="javascript" src="resources/js/paginas/alta_bitacora.js"></script>
        <style>
            .area_trabajo {margin: 5% 5% 0 5%;}
            .entrada {width: 200px;}
        </style>
        <script>
            $(function() {
                $("#tabs").tabs();
            });
        </script>
    </head>
    <body>        
        <?php
        if ($NoSerie != "") {
            echo "<div style='margin-left: 38%; font-size:22px; font-weight: bold;'>Bitácora de equipo: $NoSerie</div>";
        }
        ?>
        <?php
        if ($mensaje != "") {
            echo "<div style='margin-left: 25%; font-size:22px; font-weight: bold;'>$mensaje</div>";
        }
        ?>
        <div id="tabs">
            <ul>
                <li><a href="#tabs-1">Datos Actuales</a></li>
                <li><a href="#tabs-2">H. Clientes</a></li>
                <li><a href="#tabs-3">H. Tickets</a></li>
                <li><a href="#tabs-4">H. Refacciones</a></li>
                <li><a href="#tabs-5">H. Toner</a></li>
                <li><a href="#tabs-6">H. Mantenimientos</a></li>
                <li><a href="#tabs-7">H. Incidencias</a></li>
                <li><a href="#tabs-8">H. Lecturas Corte</a></li>
                <li><a href="#tabs-9">H. Todas Lecturas</a></li>
            </ul>
            <div id="tabs-1">
                <fieldset><!--Producto-->
                    <legend>Producto</legend>
                    <table style='width: 100%;'>
                        <tr>
                            <td><label for='no_parte'>No. de parte</label></td>
                            <td><input type='text' name='no_parte' id='no_parte' <?php echo $disabled; ?> value='<?php echo $NoParte; ?>'/></td>
                            <td><label for='no_parte_anterior'>No. de parte anterior</label></td>
                            <td><input type='text' name='no_parte_anterior' id='no_parte_anterior' <?php echo $disabled; ?>/></td>
                        </tr>
                        <tr>
                            <td><label for='modelo'>Modelo</label></td>
                            <td><input type='text' name='modelo' id='modelo' <?php echo $disabled; ?> value='<?php echo $Modelo; ?>'/></td>
                            <td>Tipo</td>
                            <td>Equipo</td>
                        </tr>
                        <tr>
                            <td><label for='no_serie'>No. de serie</label></td>
                            <td><input type='text' name='no_serie' id='no_serie' <?php echo $disabled; ?> value='<?php echo $NoSerie; ?>'/></td>
                            <td><label for='no_genesis'>No. Génesis</label></td>
                            <td><input type='text' name='no_genesis' id='no_genesis' <?php echo $disabled; ?> value='<?php echo $NoGenesis; ?>'/></td>
                        </tr>
                        <tr>
                            <td>IP</td>
                            <td><input type='text' name='no_serie' id='no_serie' <?php echo $disabled; ?> value='<?php echo $IP; ?>'/></td>
                            <td></td>
                            <td></td>
                        </tr>
                        <tr>
                            <td>MAC Address</td>
                            <td><input type='text' name='mac' id='mac' <?php echo $disabled; ?> value='<?php echo $MAC; ?>'/></td>
                            <td colspan="2" rowspan="2">
                                <fieldset>
                                    <legend>Funciones</legend>
                                    <?php echo $funciones; ?>
                                </fieldset>
                            </td>
                        </tr>
                        <tr>
                            <td>Tipo Inventario</td>
                            <td><?php echo $NombreTipoInventario; ?></td>  
                            <td></td>
                            <td></td>
                        </tr>  
                        <tr>
                            <td>
                                Costo compra:<br/><br/>
                                Folio compra:
                            </td>
                            <td>
                                <?php
                                echo "$" . number_format($costo, 2);
                                echo "<br/><br/>";
                                echo $folio;
                                ?>
                            </td>  
                            <td></td>
                            <td></td>
                        </tr>
                        <tr>
                            <td></td>
                            <td><input type="checkbox" <?php echo $ventaDirecta; ?> disabled="disabled"/>Equipo por venta directa</td>
                            <td>Estado del equipo</td>
                            <td><?php echo $mensajeSolicitudes; ?></td>
                        </tr>
                    </table>
                </fieldset>

                <fieldset>
                    <legend>Ubicación</legend>
                    <?php
                    $consulta = "SELECT 
                    (CASE WHEN !ISNULL(c2.ClaveCliente) THEN c2.NombreRazonSocial ELSE c.NombreRazonSocial END) AS NombreRazonSocial, 
                    (CASE WHEN !ISNULL(cc2.ClaveCentroCosto) THEN cc2.Nombre ELSE cc.Nombre END) AS centro,  
                    cinv.Ubicacion AS Ubicacion_inventario,
                    (CASE WHEN !ISNULL(cc2.ClaveCentroCosto) THEN
                    CONCAT(d2.Calle,' No. Ext: ',d2.NoExterior,' No. Int: ',d2.NoInterior,' Col: ',d2.Colonia,', C.P. ',d2.CodigoPostal,' ',d2.Ciudad,' ',d2.Delegacion,' ',d2.Estado)
                    ELSE 
                    CONCAT(d.Calle,' No. Ext: ',d.NoExterior,' No. Int: ',d.NoInterior,' Col: ',d.Colonia,', C.P. ',d.CodigoPostal,' ',d.Ciudad,' ',d.Delegacion,' ',d.Estado)
                    END) AS ubicacion
                    FROM `c_inventarioequipo` AS cinv
                    LEFT JOIN k_anexoclientecc AS ka ON ka.IdAnexoClienteCC = cinv.IdAnexoClienteCC
                    LEFT JOIN c_centrocosto AS cc ON cc.ClaveCentroCosto = ka.CveEspClienteCC
                    LEFT JOIN c_cliente AS c ON c.ClaveCliente = cc.ClaveCliente
                    LEFT JOIN c_domicilio AS d ON d.ClaveEspecialDomicilio = cc.ClaveCentroCosto
                    LEFT JOIN k_serviciogimgfa AS ks ON ks.IdKserviciogimgfa = cinv.IdKserviciogimgfa
                    LEFT JOIN c_centrocosto AS cc2 ON cc2.ClaveCentroCosto = ks.ClaveCentroCosto
                    LEFT JOIN c_cliente AS c2 ON c2.ClaveCliente = cc2.ClaveCliente
                    LEFT JOIN c_domicilio AS d2 ON d2.ClaveEspecialDomicilio = cc2.ClaveCentroCosto
                    WHERE cinv.NoSerie = '$NoSerie' AND !ISNULL(cinv.IdAnexoClienteCC);";
                    $result = $catalogo->obtenerLista($consulta);
                    $lugar = "";
                    $ubicacion = "";
                    $ubicacion_inventario = "";
                    $cc = "";
                    if ($rs = mysql_fetch_array($result)) {
                        $lugar = $rs['NombreRazonSocial'];
                        $ubicacion = $rs['ubicacion'];
                        $ubicacion_inventario = $rs['Ubicacion_inventario'];
                        $cc = $rs['centro'];
                        echo "<table>
                            <tr>
                                <td>Lugar</td>
                                <td>$lugar</td>
                                <td></td>
                                <td></td>
                            </tr>
                            <tr>
                                <td>Localidad</td>
                                <td>$cc</td>
                                <td></td>
                                <td></td>
                            </tr>
                            <tr>
                                <td>Domicilio localidad</td>
                                <td>$ubicacion</td>
                                <td></td>
                                <td></td>
                            </tr>                        
                            <tr>
                                <td>Ubicaci&oacute;n</td>
                                <td>$ubicacion_inventario</td>
                                <td></td>
                                <td></td>
                            </tr>
                        </table>";
                    } else {
                        $consulta = "SELECT a.nombre_almacen, kae.Ubicacion FROM k_almacenequipo AS kae 
                                INNER JOIN c_almacen AS a ON kae.NoSerie = '$NoSerie' AND kae.id_almacen = a.id_almacen;";
                        $result = $catalogo->obtenerLista($consulta);
                        while ($rs = mysql_fetch_array($result)) {
                            $lugar = $rs['nombre_almacen'];
                            echo "<table>
                            <tr>
                                <td>Almacén</td>
                                <td>$lugar</td>
                                <td></td>
                                <td></td>
                            </tr>
                            <tr>
                                <td>Ubicaci&oacute;n</td>
                                <td>" . $rs['Ubicacion'] . "</td>
                                <td></td>
                                <td></td>
                            </tr>
                            </table>";
                        }
                    }
                    ?>

                </fieldset>

                <fieldset>
                    <legend>Accesorios</legend>
                    <table class='dataTable'>
                        <thead>
                            <tr>
                                <?php
                                echo "<th width=\"2%\" align=\"center\" scope=\"col\">No. de Parte</th>";
                                echo "<th width=\"2%\" align=\"center\" scope=\"col\">Modelo</th>";
                                echo "<th width=\"2%\" align=\"center\" scope=\"col\">Descripción</th>";
                                echo "<th width=\"2%\" align=\"center\" scope=\"col\">Fecha</th>";
                                ?>
                            </tr>                        
                        </thead>
                        <tbody>
                            <?php
                            if ($id != "") {
                                $result = $catalogo->obtenerLista("SELECT kb.NoParte, c.Modelo, c.Descripcion, kb.fecha FROM `k_bitacora` AS kb
                                INNER JOIN c_componente AS c ON kb.id_bitacora = $id AND kb.NoParte = c.NoParte;");
                                while ($rs = mysql_fetch_array($result)) {
                                    echo "<tr>";
                                    echo "<td align='center' scope='row'>" . $rs['NoParte'] . "</td>";
                                    echo "<td align='center' scope='row'>" . $rs['Modelo'] . "</td>";
                                    echo "<td align='center' scope='row'>" . $rs['Descripcion'] . "</td>";
                                    echo "<td align='center' scope='row'>" . $rs['fecha'] . "</td>";
                                    echo "</tr>";
                                }
                            }
                            ?>
                        </tbody>
                    </table>
                </fieldset>
                <table style="width: 100%;">
                    <tr>
                        <td>
                            <fieldset>
                                <legend>Partes que le han prestado a este equipo</legend>
                                <table class='dataTable'>
                                    <thead>
                                        <tr>
                                            <?php
                                            echo "<tr>";
                                            echo "<th width=\"2%\" align=\"center\" scope=\"col\">No. de parte</th>";
                                            echo "<th width=\"2%\" align=\"center\" scope=\"col\">Tipo</th>";
                                            echo "<th width=\"2%\" align=\"center\" scope=\"col\">Modelo</th>";
                                            echo "<th width=\"2%\" align=\"center\" scope=\"col\">Fecha de préstamo</th>";
                                            echo "</tr>";
                                            ?>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php
                                        $result = $catalogo->obtenerLista("SELECT c.Modelo,tc.Nombre AS tipoComponente, m.* FROM `movimiento_componente_en_equipo` AS m
                                        INNER JOIN c_componente AS c ON m.NoSerieDestino = '$NoSerie' AND m.Tipo = 1 AND c.NoParte = m.NoParte
                                        INNER JOIN c_tipocomponente AS tc ON tc.IdTipoComponente = c.IdTipoComponente
                                        LEFT JOIN c_ticket AS t ON m.IdTicket = t.IdTicket;");
                                        while ($rs = mysql_fetch_array($result)) {
                                            echo "<tr>";
                                            echo "<td align='center' scope='row'>" . $rs['NoParte'] . "</td>";
                                            echo "<td align='center' scope='row'>" . $rs['tipoComponente'] . "</td>";
                                            echo "<td align='center' scope='row'>" . $rs['Modelo'] . "</td>";
                                            echo "<td align='center' scope='row'>" . $rs['FechaInicio'] . "</td>";
                                            echo "</tr>";
                                        }
                                        ?>
                                    </tbody>                        
                                </table>
                            </fieldset>
                        </td>
                        <td>
                            <fieldset>
                                <legend>Partes extraídas</legend>
                                <table class='dataTable'>
                                    <thead>
                                        <tr>
                                            <?php
                                            echo "<tr>";
                                            echo "<th width=\"2%\" align=\"center\" scope=\"col\">No. de parte</th>";
                                            echo "<th width=\"2%\" align=\"center\" scope=\"col\">Tipo</th>";
                                            echo "<th width=\"2%\" align=\"center\" scope=\"col\">Modelo</th>";
                                            echo "<th width=\"2%\" align=\"center\" scope=\"col\">Fecha de préstamo</th>";
                                            echo "</tr>";
                                            ?>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php
                                        $result = $catalogo->obtenerLista("SELECT c.Modelo,tc.Nombre AS tipoComponente, m.* FROM `movimiento_componente_en_equipo` AS m
                                            INNER JOIN c_componente AS c ON m.NoSerieOrigen = '$NoSerie' AND m.Tipo = 4 AND c.NoParte = m.NoParte
                                            INNER JOIN c_tipocomponente AS tc ON tc.IdTipoComponente = c.IdTipoComponente
                                            LEFT JOIN c_ticket AS t ON m.IdTicket = t.IdTicket;");
                                        while ($rs = mysql_fetch_array($result)) {
                                            echo "<tr>";
                                            echo "<td align='center' scope='row'>" . $rs['NoParte'] . "</td>";
                                            echo "<td align='center' scope='row'>" . $rs['tipoComponente'] . "</td>";
                                            echo "<td align='center' scope='row'>" . $rs['Modelo'] . "</td>";
                                            echo "<td align='center' scope='row'>" . $rs['FechaInicio'] . "</td>";
                                            echo "</tr>";
                                        }
                                        ?>
                                    </tbody>                        
                                </table>
                            </fieldset>
                        </td>
                    </tr>
                </table>
            </div>
            <div id="tabs-2">
                <fieldset>
                    <legend>Clientes donde ha estado el equipo</legend>                    
                    <table class='dataTable'>
                        <thead>
                            <tr>
                                <?php
                                echo "<th width=\"2%\" align=\"center\" scope=\"col\">Fecha</th>";
                                echo "<th width=\"2%\" align=\"center\" scope=\"col\">Origen</th>";
                                echo "<th width=\"2%\" align=\"center\" scope=\"col\">Destino</th>";
                                echo "<th width=\"2%\" align=\"center\" scope=\"col\">Localidad</th>";
                                echo "<th width=\"2%\" align=\"center\" scope=\"col\">Contador BN Pag.</th>";
                                echo "<th width=\"2%\" align=\"center\" scope=\"col\">Contador Color Pags.</th>";
                                echo "<th width=\"2%\" align=\"center\" scope=\"col\">Contador BN ML</th>";
                                echo "<th width=\"2%\" align=\"center\" scope=\"col\">Contador Color ML</th>";
                                echo "<th width=\"2%\" align=\"center\" scope=\"col\">Causa</th>";
                                ?>
                            </tr>                        
                        </thead>
                        <tbody>
                            <?php
                            if ($id != "") {
                                $consulta = "SELECT 
                                    meq.id_movimientos, meq.tipo_movimiento, meq.NoSerie, meq.pendiente, meq.Fecha, meq.causa_movimiento, 
                                    CONCAT(c1.ClaveCliente,' - ',c1.NombreRazonSocial) AS cliente_anterior, c1.RFC AS rfc_anterior,
                                    CONCAT(cc1.ClaveCentroCosto, ' - ' ,cc1.Nombre) AS centro_anterior, c2.RFC AS rfc_nuevo,
                                    CONCAT(c2.ClaveCliente,' - ',c2.NombreRazonSocial) AS cliente_nuevo,
                                    CONCAT(cc2.ClaveCentroCosto, ' - ' ,cc2.Nombre) AS centro_nuevo,
                                    alm1.nombre_almacen AS almacen_anterior, alm2.nombre_almacen AS almacen_nuevo,
                                    (SELECT CASE WHEN ISNULL(cliente_anterior) THEN alm1.nombre_almacen ELSE cliente_anterior END) AS origen,
                                    (SELECT CASE WHEN ISNULL(cliente_nuevo) THEN alm2.nombre_almacen ELSE cliente_nuevo END) AS destino,
                                    (CASE WHEN !ISNULL(cl2.IdLectura) THEN cl2.ContadorBNML ELSE cl.ContadorBNML END) AS ContadorBNML,
                                    (CASE WHEN !ISNULL(cl2.IdLectura) THEN cl2.ContadorBNPaginas ELSE cl.ContadorBNPaginas END) AS ContadorBNPaginas,
                                    (CASE WHEN !ISNULL(cl2.IdLectura) THEN cl2.ContadorColorML ELSE cl.ContadorColorML END) AS ContadorColorML,
                                    (CASE WHEN !ISNULL(cl2.IdLectura) THEN cl2.ContadorColorPaginas ELSE cl.ContadorColorPaginas END) AS ContadorColorPaginas,
                                    (CASE WHEN !ISNULL(cl2.IdLectura) THEN cl2.NivelTonAmarillo ELSE cl.NivelTonAmarillo END) AS NivelTonAmarillo,
                                    (CASE WHEN !ISNULL(cl2.IdLectura) THEN cl2.NivelTonCian ELSE cl.NivelTonCian END) AS NivelTonCian,
                                    (CASE WHEN !ISNULL(cl2.IdLectura) THEN cl2.NivelTonMagenta ELSE cl.NivelTonMagenta END) AS NivelTonMagenta,
                                    (CASE WHEN !ISNULL(cl2.IdLectura) THEN cl2.NivelTonNegro ELSE cl.NivelTonNegro END) AS NivelTonNegro
                                    FROM `movimientos_equipo` AS meq 
                                    LEFT JOIN c_cliente AS c1 ON c1.ClaveCliente = meq.clave_cliente_anterior
                                    LEFT JOIN c_cliente AS c2 ON c2.ClaveCliente = meq.clave_cliente_nuevo
                                    LEFT JOIN c_centrocosto AS cc1 ON cc1.ClaveCentroCosto = meq.clave_centro_costo_anterior
                                    LEFT JOIN c_centrocosto AS cc2 ON cc2.ClaveCentroCosto = meq.clave_centro_costo_nuevo
                                    LEFT JOIN c_almacen AS alm1 ON alm1.id_almacen = meq.almacen_anterior
                                    LEFT JOIN c_almacen AS alm2 ON alm2.id_almacen = meq.almacen_nuevo
                                    LEFT JOIN c_lectura AS cl ON cl.IdLectura = meq.id_lectura
                                    LEFT JOIN c_lectura AS cl2 ON cl2.IdLectura = meq.id_lectura2
                                    WHERE meq.NoSerie = '$NoSerie';";
                                $result = $catalogo->obtenerLista($consulta);
                                while ($rs = mysql_fetch_array($result)) {
                                    echo "<tr>";
                                    echo "<td align='center' scope='row'>" . $rs['Fecha'] . "</td>";
                                    echo "<td align='center' scope='row'>" . $rs['origen'] . "</td>";
                                    echo "<td align='center' scope='row'>" . $rs['destino'] . "</td>";
                                    echo "<td align='center' scope='row'>" . $rs['centro_nuevo'] . "</td>";
                                    echo "<td align='center' scope='row'>" . $rs['ContadorBNPaginas'] . "</td>";
                                    echo "<td align='center' scope='row'>" . $rs['ContadorColorPaginas'] . "</td>";
                                    echo "<td align='center' scope='row'>" . $rs['ContadorBNML'] . "</td>";
                                    echo "<td align='center' scope='row'>" . $rs['ContadorColorML'] . "</td>";
                                    echo "<td align='center' scope='row'>" . $rs['causa_movimiento'] . "</td>";
                                    echo "</tr>";
                                }
                            }
                            ?>
                        </tbody>
                    </table>
                </fieldset>
            </div>
            <div id="tabs-3">                
                <fieldset>
                    <legend>Tickets del equipo</legend>
                    <form id="formBitacoraTicket">
                    <table class='dataTable'>
                        <thead>
                            <tr>
                                <?php
                                if ($psm->tienePermisoEspecial($_SESSION['idUsuario'], 37)){
                                    $cabeceras = array("Ticket", "Fecha", "No Serie", "Cliente", "Área de atención", "Ubicación", "Falla",
                                    "Último estatus ticket", "Contador B/N", "Contador color", "Hoja de estado","Última Nota", "Fecha nota");
                                }else{
                                    $cabeceras = array("Ticket", "Fecha", "No Serie", "Cliente", "Área de atención", "Ubicación", "Falla",
                                    "Último estatus ticket", "Contador B/N", "Contador color","Última Nota", "Fecha nota");
                                }
                                for ($i = 0; $i < (count($cabeceras)); $i++) {
                                    echo "<th width=\"2%\" align=\"center\" scope=\"col\">" . $cabeceras[$i] . "</th>";
                                }
                                ?>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            if (!empty($NoSerie)) {
                                $consulta = "SELECT
                                    t.IdTicket,
                                    t.FechaHora,
                                    t.DescripcionReporte,
                                    t.NombreCentroCosto,
                                    t.ClaveCentroCosto,
                                    t.TipoReporte,
                                    (SELECT CASE WHEN e2.Suministro = 1 THEN ( SELECT group_concat( ClaveEspEquipo SEPARATOR ', ') FROM `c_pedido` WHERE IdTicket = t.IdTicket) ELSE t.NoSerieEquipo END) AS NumSerie,
                                    DATEDIFF(NOW(), t.FechaHora) AS diferencia,
                                    t.NombreCliente,
                                    cl.IdEstatusCobranza,
                                    e.IdEstadoTicket AS estadoTicket,
                                    e1.Nombre AS tipo,
                                    tc.IdTipoCliente AS tipoCliente,
                                    e2.Nombre AS area,
                                    e2.IdEstado AS idArea,
                                    u.Nombre AS ubicacion,
                                    cgz.nombre AS ubicacionTicket,
                                    e3.Nombre AS estadoNota,
                                    nt.IdEstatusAtencion,
                                    nt.DiagnosticoSol,
                                    nt.FechaHora AS FechaNota,
                                    lt.ContadorBN, lt.ContadorCL, lt.id_lecturaticket 
                                    FROM c_ticket AS t
                                    INNER JOIN c_estadoticket AS e ON e.IdEstadoTicket = t.EstadoDeTicket
                                    LEFT JOIN c_domicilioticket AS dt ON dt.IdTicket = t.IdTicket
                                    LEFT JOIN c_gzona AS cgz ON cgz.id_gzona = dt.Id_gzona
                                    LEFT JOIN c_estado AS e1 ON e1.IdEstado = t.TipoReporte
                                    LEFT JOIN c_lecturasticket AS lt ON lt.fk_idticket = t.IdTicket AND lt.ClvEsp_Equipo LIKE '%$NoSerie%'
                                    INNER JOIN c_cliente AS cl ON cl.ClaveCliente = t.ClaveCliente
                                    LEFT JOIN c_tipocliente AS tc ON tc.IdTipoCliente = cl.IdTipoCliente
                                    LEFT JOIN c_estado AS e2 ON e2.IdEstado = t.AreaAtencion
                                    LEFT JOIN c_ubicacionticket AS u ON u.IdUbicacion = t.Ubicacion
                                    LEFT JOIN c_notaticket AS nt ON nt.IdNotaTicket = (
                                            SELECT
                                                    MAX(IdNotaTicket)
                                            FROM
                                                    c_notaticket AS nt2
                                            WHERE
                                                    nt2.IdTicket = t.IdTicket
                                    )
                                    LEFT JOIN c_estado AS e3 ON nt.IdEstatusAtencion = e3.IdEstado
                                    WHERE (SELECT CASE WHEN e2.Suministro = 1 THEN ( SELECT group_concat( ClaveEspEquipo SEPARATOR ', ') FROM `c_pedido` WHERE IdTicket = t.IdTicket) ELSE t.NoSerieEquipo END) LIKE '%$NoSerie%'
                                    GROUP BY IdTicket ORDER BY IdTicket;";
                                //echo $consulta;
                                $result = $catalogo->obtenerLista($consulta);
                                $cont = 0;
                                while ($rs = mysql_fetch_array($result)) {
                                    echo "<tr>";
                                    echo "<td align='center' scope='row'>";
                                    echo "<a href='" . $liga . "/principal.php?mnu=mesa&action=lista_ticket&id=" . $rs['IdTicket'] . "' target='_blank' title='Ver ticket " . $rs['IdTicket'] . "'>" . $rs['IdTicket'] . "</a>";
                                    echo "</td>";
                                    echo "<td align='center' scope='row'>" . $rs['FechaHora'] . "</td>";
                                    echo "<td align='center' scope='row'>" . $rs['NumSerie'] . "</td>";
                                    echo "<td align='center' scope='row'>" . $rs['NombreCliente'] . " - " . $rs['NombreCentroCosto'] . "</td>";

                                    echo "<td align='center' scope='row'>" . $rs['area'] . "</td>";
                                    echo "<td align='center' scope='row'>" . $rs['ubicacionTicket'] . "</td>";
                                    echo "<td align='center' scope='row'>" . $rs['DescripcionReporte'] . "</td>";

                                    if (isset($rs['estadoNota'])) {
                                        echo "<td align='center' scope='row'>" . $rs['estadoNota'] . "</td>";
                                    } else {
                                        echo "<td align='center' scope='row'></td>";
                                    }
                                    if ($psm->tienePermisoEspecial($_SESSION['idUsuario'], 37)){
                                        if(isset($rs['ContadorBN']))
                                        {   
                                            $cont++;
                                            echo "<td><center>".$rs['ContadorBN']."</center><br/><input type='text' id='contadorbn_". $cont ."' name='contadorbn_". $cont ."' value='".$rs['ContadorBN']."' size='10'/></td>";
                                            if($tipo->isColor($NoParte)){
                                                echo "<td><center>".$rs['ContadorCL']."</center><br/><input type='text' id='contadorcl_". $cont ."' name='contadorcl_". $cont ."' value='".$rs['ContadorCL']."' size='10'/></td>";
                                            }else{
                                                echo "<td></td>";
                                            }
                                            echo "<input type='hidden' name='lectura_$cont' id='lectura_$cont' value='".$rs['id_lecturaticket']."' />";
                                            echo "<input type='hidden' name='ticket_$cont' id='ticket_$cont' value='".$rs['IdTicket']."' />";
                                            echo "<input type='hidden' name='ccc_$cont' id='ccc_$cont' value='".$rs['ClaveCentroCosto']."' />";
                                            echo "<td><input type='file' id='hoja_$cont' name='hoja_$cont' /></td>";
                                        }else{
                                            echo "<td></td>";
                                            echo "<td></td>";
                                            echo "<td></td>";
                                        }    
                                    }else{
                                        if(isset($rs['ContadorBN']))
                                        {   
                                            echo "<td><center>".$rs['ContadorBN']."</center></td>";
                                            if($tipo->isColor($NoParte)){
                                                echo "<td><center>".$rs['ContadorCL']."</center></td>";
                                            }else{
                                                echo "<td></td>";
                                            }
                                        }else{
                                            echo "<td></td>";
                                            echo "<td></td>";
                                        }   
                                    }
                                    if (isset($rs['DiagnosticoSol'])) {
                                        echo "<td align='center' scope='row'>" . $rs['DiagnosticoSol'] . "</td>";
                                    } else {
                                        echo "<td align='center' scope='row'></td>";
                                    }

                                    if (isset($rs['FechaNota'])) {
                                        echo "<td align='center' scope='row'>" . $rs['FechaNota'] . "</td>";
                                    } else {
                                        echo "<td align='center' scope='row'></td>";
                                    }
                                    echo "</tr>";
                                }
                            }
                            ?>
                        </tbody>                        
                    </table>
                        <?php if ($psm->tienePermisoEspecial($_SESSION['idUsuario'], 37)){ ?>
                        <input type="hidden" id="numRows" name="numRows" value="<?php echo $cont; ?>" />
                        <input type="hidden" id="idB" name="idB" value="<?php echo $id; ?>" />
                        <input type="hidden" id="NoSerie" name="NoSerie" value="<?php echo $NoSerie; ?>" />
                        <input type="submit" id="submit" name="submit" value="Cambiar contadores" class="button" style="margin:10px 0 0 80%;"/>
                        <?php } ?>
                    </form>
                </fieldset>
            </div>
            <div id="tabs-4">
                <fieldset>
                    <!--<legend>Piezas cambiadas</legend>
                    <table class='dataTable'>
                        <thead>
                            <tr>
                    <?php
                    /* echo "<tr>";
                      echo "<th width=\"2%\" align=\"center\" scope=\"col\">No. Serie</th>";
                      echo "<th width=\"2%\" align=\"center\" scope=\"col\">No. de parte</th>";
                      echo "<th width=\"2%\" align=\"center\" scope=\"col\">Tipo</th>";
                      echo "<th width=\"2%\" align=\"center\" scope=\"col\">Modelo</th>";
                      echo "<th width=\"2%\" align=\"center\" scope=\"col\">Cantidad</th>";
                      echo "<th width=\"2%\" align=\"center\" scope=\"col\">Fecha de préstamo</th>";
                      echo "<th width=\"2%\" align=\"center\" scope=\"col\">Fecha de regreso</th>";
                      echo "<th width=\"2%\" align=\"center\" scope=\"col\">Ticket</th>";
                      echo "</tr>"; */
                    ?>
                            </tr>
                        </thead>
                        <tbody>
                    <?php
                    /* $result = $catalogo->obtenerLista("SELECT c.Modelo,tc.Nombre AS tipoComponente, m.* FROM `movimiento_componente_en_equipo` AS m
                      INNER JOIN c_componente AS c ON m.NoSerieOrigen = '$NoSerie' AND m.Tipo = 2 AND c.NoParte = m.NoParte
                      INNER JOIN c_tipocomponente AS tc ON tc.IdTipoComponente = c.IdTipoComponente
                      LEFT JOIN c_ticket AS t ON m.IdTicket = t.IdTicket;");
                      while ($rs = mysql_fetch_array($result)) {
                      echo "<tr>";
                      echo "<td align='center' scope='row'>" . $rs['NoSerieOrigen'] . "</td>";
                      echo "<td align='center' scope='row'>" . $rs['NoParte'] . "</td>";
                      echo "<td align='center' scope='row'>" . $rs['tipoComponente'] . "</td>";
                      echo "<td align='center' scope='row'>" . $rs['Modelo'] . "</td>";
                      echo "<td align='center' scope='row'>" . $rs['Cantidad'] . "</td>";
                      echo "<td align='center' scope='row'>" . $rs['FechaInicio'] . "</td>";
                      echo "<td align='center' scope='row'>" . $rs['FechaFin'] . "</td>";
                      echo "<td align='center' scope='row'>" . $rs['IdTicket'] . "</td>";
                      echo "</tr>";
                      } */
                    ?>
                        </tbody>                        
                    </table>
                </fieldset>   
                <fieldset>
                    <legend>Piezas que este equipo ha prestado</legend>
                    <table class='dataTable'>
                        <thead>
                            <tr>
                    <?php
                    /* echo "<tr>";
                      echo "<th width=\"2%\" align=\"center\" scope=\"col\">No. Serie</th>";
                      echo "<th width=\"2%\" align=\"center\" scope=\"col\">No. de parte</th>";
                      echo "<th width=\"2%\" align=\"center\" scope=\"col\">Tipo</th>";
                      echo "<th width=\"2%\" align=\"center\" scope=\"col\">Modelo</th>";
                      echo "<th width=\"2%\" align=\"center\" scope=\"col\">Cantidad</th>";
                      echo "<th width=\"2%\" align=\"center\" scope=\"col\">Fecha de préstamo</th>";
                      echo "<th width=\"2%\" align=\"center\" scope=\"col\">Fecha de regreso</th>";
                      echo "<th width=\"2%\" align=\"center\" scope=\"col\">Ticket</th>";
                      echo "</tr>"; */
                    ?>
                            </tr>
                        </thead>
                        <tbody>
                    <?php
                    /* $result = $catalogo->obtenerLista("SELECT c.Modelo,tc.Nombre AS tipoComponente, m.* FROM `movimiento_componente_en_equipo` AS m
                      INNER JOIN c_componente AS c ON m.NoSerieOrigen = '$NoSerie' AND m.Tipo = 1 AND c.NoParte = m.NoParte
                      INNER JOIN c_tipocomponente AS tc ON tc.IdTipoComponente = c.IdTipoComponente
                      LEFT JOIN c_ticket AS t ON m.IdTicket = t.IdTicket;");
                      while ($rs = mysql_fetch_array($result)) {
                      echo "<tr>";
                      echo "<td align='center' scope='row'>" . $rs['NoSerieDestino'] . "</td>";
                      echo "<td align='center' scope='row'>" . $rs['NoParte'] . "</td>";
                      echo "<td align='center' scope='row'>" . $rs['tipoComponente'] . "</td>";
                      echo "<td align='center' scope='row'>" . $rs['Modelo'] . "</td>";
                      echo "<td align='center' scope='row'>" . $rs['Cantidad'] . "</td>";
                      echo "<td align='center' scope='row'>" . $rs['FechaInicio'] . "</td>";
                      echo "<td align='center' scope='row'>" . $rs['FechaFin'] . "</td>";
                      echo "<td align='center' scope='row'>" . $rs['IdTicket'] . "</td>";
                      echo "</tr>";
                      } */
                    ?>
                        </tbody>                        
                    </table>
                </fieldset>
                <fieldset>
                    <legend>Piezas que le han prestado a este equipo</legend>
                    <table class='dataTable'>
                        <thead>
                            <tr>
                    <?php
                    /* echo "<tr>";
                      echo "<th width=\"2%\" align=\"center\" scope=\"col\">No. Serie</th>";
                      echo "<th width=\"2%\" align=\"center\" scope=\"col\">No. de parte</th>";
                      echo "<th width=\"2%\" align=\"center\" scope=\"col\">Tipo</th>";
                      echo "<th width=\"2%\" align=\"center\" scope=\"col\">Modelo</th>";
                      echo "<th width=\"2%\" align=\"center\" scope=\"col\">Cantidad</th>";
                      echo "<th width=\"2%\" align=\"center\" scope=\"col\">Fecha de préstamo</th>";
                      echo "<th width=\"2%\" align=\"center\" scope=\"col\">Fecha de regreso</th>";
                      echo "<th width=\"2%\" align=\"center\" scope=\"col\">Ticket</th>";
                      echo "</tr>"; */
                    ?>
                            </tr>
                        </thead>
                        <tbody>
                    <?php
                    /* $result = $catalogo->obtenerLista("SELECT c.Modelo,tc.Nombre AS tipoComponente, m.* 
                      FROM `movimiento_componente_en_equipo` AS m
                      INNER JOIN c_componente AS c ON m.NoSerieDestino = '$NoSerie' AND m.Tipo = 1 AND c.NoParte = m.NoParte
                      INNER JOIN c_tipocomponente AS tc ON tc.IdTipoComponente = c.IdTipoComponente
                      LEFT JOIN c_ticket AS t ON m.IdTicket = t.IdTicket;");
                      while ($rs = mysql_fetch_array($result)) {
                      echo "<tr>";
                      echo "<td align='center' scope='row'>" . $rs['NoSerieOrigen'] . "</td>";
                      echo "<td align='center' scope='row'>" . $rs['NoParte'] . "</td>";
                      echo "<td align='center' scope='row'>" . $rs['tipoComponente'] . "</td>";
                      echo "<td align='center' scope='row'>" . $rs['Modelo'] . "</td>";
                      echo "<td align='center' scope='row'>" . $rs['Cantidad'] . "</td>";
                      echo "<td align='center' scope='row'>" . $rs['FechaInicio'] . "</td>";
                      echo "<td align='center' scope='row'>" . $rs['FechaFin'] . "</td>";
                      echo "<td align='center' scope='row'>" . $rs['IdTicket'] . "</td>";
                      echo "</tr>";
                      } */
                    ?>
                        </tbody>                        
                    </table>
                </fieldset>-->
                    <fieldset>
                        <legend>Piezas cambiadas</legend>
                        <table class='dataTable' style='width: 100%;'>
                            <thead>
                                <tr>
                                    <?php
                                    echo "<tr>";
                                    echo "<th width=\"2%\" align=\"center\" scope=\"col\">No. de parte</th>";
                                    echo "<th width=\"2%\" align=\"center\" scope=\"col\">Tipo</th>";
                                    echo "<th width=\"2%\" align=\"center\" scope=\"col\">Modelo</th>";
                                    echo "<th width=\"2%\" align=\"center\" scope=\"col\">Cantidad</th>";
                                    echo "<th width=\"2%\" align=\"center\" scope=\"col\">Fecha de entrega</th>";
                                    echo "<th width=\"2%\" align=\"center\" scope=\"col\">Ticket</th>";
                                    echo "<th width=\"2%\" align=\"center\" scope=\"col\">Contador BN</th>";
                                    echo "<th width=\"2%\" align=\"center\" scope=\"col\">Contador Color</th>";
                                    echo "</tr>";
                                    ?>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $result = $catalogo->obtenerLista("SELECT t.IdTicket, nt.DiagnosticoSol, nr.NoParteComponente, tc.Nombre AS TipoComponente, 
                                nr.Cantidad, nr.FechaCreacion AS FechaEntrega, CONCAT(c.Modelo,' / ',c.NoParte,' / ',c.Descripcion) AS Modelo,
                                lt.ContadorBN, lt.ContadorCL
                                FROM `c_ticket` AS t
                                LEFT JOIN c_notaticket AS nt ON t.IdTicket = nt.IdTicket
                                LEFT JOIN k_nota_refaccion AS nr ON nr.IdNotaTicket = nt.IdNotaTicket
                                LEFT JOIN c_componente AS c ON nr.NoParteComponente = c.NoParte
                                LEFT JOIN c_tipocomponente AS tc ON tc.IdTipoComponente = c.IdTipoComponente
                                LEFT JOIN c_lecturasticket AS lt ON lt.id_lecturaticket = (SELECT MAX(id_lecturaticket) FROM c_lecturasticket WHERE fk_idticket = t.IdTicket)
                                WHERE t.TipoReporte <> 15 AND t.NoSerieEquipo = '$NoSerie' AND nt.IdEstatusAtencion = 17 AND !ISNULL(nr.IdNotaTicket)
                                GROUP BY t.IdTicket, nr.NoParteComponente
                                ORDER BY t.IdTicket;");
                                $idTicketAnterior = "";
                                $movimiento_componente = new MovimientoComponente();
                                while ($rs = mysql_fetch_array($result)) {
                                    if($idTicketAnterior != $rs['IdTicket']){
                                        $resultDevoluciones = $movimiento_componente->getDevolucionByTicket($rs['IdTicket']);
                                        while($rsDevolucion = mysql_fetch_array($resultDevoluciones)){
                                            echo "<tr>";                        
                                            echo "<td align='center' scope='row'>" . $rsDevolucion['NoParteComponente'] . "</td>";
                                            echo "<td align='center' scope='row'>" . $rsDevolucion['TipoComponente'] . "</td>";
                                            echo "<td align='center' scope='row'>" . $rsDevolucion['Modelo'] . "</td>";
                                            echo "<td align='center' scope='row'>" . $rsDevolucion['Cantidad'] . "</td>";
                                            echo "<td align='center' scope='row'>" . $rsDevolucion['FechaEntrega'] . "</td>";                        
                                            echo "<td align='center' scope='row'>";
                                            echo "<a href='" . $liga . "/principal.php?mnu=mesa&action=lista_ticket&id=" . $rsDevolucion['IdTicket'] . "' target='_blank' title='Ver ticket " . 
                                                    $rsDevolucion['IdTicket'] . "'>" . $rsDevolucion['IdTicket'] . "</a>";
                                            echo "</td>";
                                            echo "<td align='center' scope='row'></td>";
                                            echo "<td align='center' scope='row'></td>";
                                            echo "</tr>";
                                        }
                                    }
                                    $idTicketAnterior = $rs['IdTicket'];
                                    echo "<tr>";
                                    echo "<td align='center' scope='row'>" . $rs['NoParteComponente'] . "</td>";
                                    echo "<td align='center' scope='row'>" . $rs['TipoComponente'] . "</td>";
                                    echo "<td align='center' scope='row'>" . $rs['Modelo'] . "</td>";
                                    echo "<td align='center' scope='row'>" . $rs['Cantidad'] . "</td>";
                                    echo "<td align='center' scope='row'>" . $rs['FechaEntrega'] . "</td>";
                                    echo "<td align='center' scope='row'>";
                                    echo "<a href='" . $liga . "/principal.php?mnu=mesa&action=lista_ticket&id=" . $rs['IdTicket'] . "' target='_blank' title='Ver ticket " . $rs['IdTicket'] . "'>" . $rs['IdTicket'] . "</a>";
                                    echo "</td>";
                                    echo "<td align='center' scope='row'>" . $rs['ContadorBN'] . "</td>";
                                    echo "<td align='center' scope='row'>" . $rs['ContadorCL'] . "</td>";
                                    echo "</tr>";                                    
                                }
                                ?>
                            </tbody>                        
                        </table>
                    </fieldset>
            </div>
            <div id="tabs-5">
                <fieldset>
                    <legend>Tóner solicitado para este equipo</legend>
                    <?php
                    $consulta = "SELECT
                            t.IdTicket,
                            t.FechaHora,
                            t.DescripcionReporte,";
                    if ($color) {
                        if (isset($solicitados_color) && isset($contadores_color)) {
                            foreach ($solicitados_color as $value) {
                                $consulta.="p.$value,";
                            }
                            foreach ($contadores_color as $value) {
                                $consulta.= "lt.$value,";
                            }
                        }
                    } else {
                        if (isset($solicitados_bn) && isset($contadores_bn)) {
                            foreach ($solicitados_bn as $value) {
                                $consulta.="p.$value,";
                            }
                            foreach ($contadores_bn as $value) {
                                $consulta.= "lt.$value,";
                            }
                        }
                    }
                    $consulta .="CONCAT(t.NombreCliente,' - ',t.NombreCentroCosto) AS cliente                            
                            FROM c_ticket AS t
                            INNER JOIN c_pedido AS p ON p.ClaveEspEquipo = '$NoSerie' AND p.IdTicket = t.IdTicket
                            INNER JOIN c_estado AS e2 ON e2.IdEstado = t.AreaAtencion AND e2.Suministro = 1
                            LEFT JOIN c_lecturasticket AS lt ON p.IdLecturaTicket = lt.id_lecturaticket
                            GROUP BY t.IdTicket ORDER BY t.IdTicket;";
                    //echo $consulta;
                    ?>
                    <table class='dataTable'>
                        <thead>
                            <tr>
                                <?php
                                echo "<tr>";
                                echo "<th width=\"2%\" align=\"center\" scope=\"col\">Cliente</th>";
                                echo "<th width=\"2%\" align=\"center\" scope=\"col\">Fecha</th>";
                                echo "<th width=\"2%\" align=\"center\" scope=\"col\">Descripcion</th>";
                                echo "<th width=\"2%\" align=\"center\" scope=\"col\">Ticket</th>";
                                if ($color) {
                                    if (isset($cabeceras_color) && isset($cabeceras_s_color)) {
                                        foreach ($cabeceras_s_color as $value) {
                                            echo "<th width=\"2%\" align=\"center\" scope=\"col\">$value</th>";
                                        }
                                        foreach ($cabeceras_color as $value) {
                                            echo "<th width=\"2%\" align=\"center\" scope=\"col\">$value</th>";
                                        }
                                    }
                                } else {
                                    if (isset($cabeceras_bn) && isset($cabeceras_s_bn)) {
                                        foreach ($cabeceras_s_bn as $value) {
                                            echo "<th width=\"2%\" align=\"center\" scope=\"col\">$value</th>";
                                        }
                                        foreach ($cabeceras_bn as $value) {
                                            echo "<th width=\"2%\" align=\"center\" scope=\"col\">$value</th>";
                                        }
                                    }
                                }
                                echo "</tr>";
                                ?>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $result = $catalogo->obtenerLista($consulta);
                            while ($rs = mysql_fetch_array($result)) {
                                echo "<tr>";
                                echo "<td align='center' scope='row'>" . $rs['cliente'] . "</td>";
                                echo "<td align='center' scope='row'>" . $rs['FechaHora'] . "</td>";
                                echo "<td align='center' scope='row'>" . $rs['DescripcionReporte'] . "</td>";
                                echo "<td align='center' scope='row'>" . $rs['IdTicket'] . "</td>";
                                if ($color) {
                                    foreach ($solicitados_color as $value) {
                                        echo "<td align='center' scope='row'>" . $rs[$value] . "</td>";
                                    }
                                    foreach ($contadores_color as $value) {
                                        echo "<td align='center' scope='row'>" . $rs[$value] . "</td>";
                                    }
                                } else {
                                    foreach ($solicitados_bn as $value) {
                                        echo "<td align='center' scope='row'>" . $rs[$value] . "</td>";
                                    }
                                    foreach ($contadores_bn as $value) {
                                        echo "<td align='center' scope='row'>" . $rs[$value] . "</td>";
                                    }
                                }
                                echo "</tr>";
                            }
                            ?>
                        </tbody>                        
                    </table>
                    <div style="color: red; font-size: 9px; margin-left: 85%;">(*) Cantidad solicitada</div><br/>
                </fieldset>
            </div>
            <div id="tabs-6">                
                <fieldset>
                    <legend>Mantenimientos preventivos por realizar</legend>
                    <table class='dataTable'>
                        <thead>
                            <tr>
                                <?php
                                echo "<th width=\"2%\" align=\"center\" scope=\"col\">Cliente</th>";
                                echo "<th width=\"2%\" align=\"center\" scope=\"col\">Fecha</th>";
                                echo "<th width=\"2%\" align=\"center\" scope=\"col\">Técnico</th>";
                                ?>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $result = $catalogo->obtenerLista("SELECT c.NombreRazonSocial,m.Fecha AS fecha_planeada, CONCAT(u.Nombre,' ',u.ApellidoPaterno,' ',u.ApellidoMaterno) AS tecnico FROM `c_mantenimiento` AS m
                                    INNER JOIN c_centrocosto AS cc ON m.NoSerie = '$NoSerie' AND m.Estatus = 0 AND cc.ClaveCentroCosto = m.ClaveCentroCosto
                                    INNER JOIN c_cliente AS c ON cc.ClaveCliente = c.ClaveCliente
                                    LEFT JOIN c_ticket AS t ON m.IdTicket = t.IdTicket
                                    LEFT JOIN k_tecnicoticket AS kt ON kt.IdTicket = t.IdTicket
                                    LEFT JOIN c_usuario AS u ON u.IdUsuario = kt.IdUsuario
                                    ORDER BY Fecha;");
                            while ($rs = mysql_fetch_array($result)) {
                                echo "<tr>";
                                echo "<td align='center' scope='row'>" . $rs['NombreRazonSocial'] . "</td>";
                                echo "<td align='center' scope='row'>" . $rs['fecha_planeada'] . "</td>";
                                echo "<td align='center' scope='row'>" . $rs['tecnico'] . "</td>";
                                echo "</tr>";
                            }
                            ?>
                        </tbody>                        
                    </table>
                </fieldset>
                <fieldset>
                    <legend>MK solicitados para este equipo</legend>
                </fieldset>
                <fieldset>
                    <legend>Mantenimientos preventivos realizados</legend>
                    <table class='dataTable'>
                        <thead>
                            <tr>
                                <?php
                                echo "<tr>";
                                echo "<th width=\"2%\" align=\"center\" scope=\"col\">Cliente</th>";
                                echo "<th width=\"2%\" align=\"center\" scope=\"col\">Fecha</th>";
                                echo "<th width=\"2%\" align=\"center\" scope=\"col\">Técnico</th>";
                                echo "</tr>";
                                ?>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $result = $catalogo->obtenerLista("SELECT c.NombreRazonSocial,m.Fecha AS fecha_planeada, CONCAT(u.Nombre,' ',u.ApellidoPaterno,' ',u.ApellidoMaterno) AS tecnico, 
                                (SELECT CASE WHEN ISNULL(knr.IdNotaTicket) THEN 'NO' ELSE 'SI' END) AS solicito_refaccion, knr.IdNotaTicket
                                FROM `c_mantenimiento` AS m
                                INNER JOIN c_centrocosto AS cc ON m.NoSerie = '$NoSerie' AND m.Estatus = 1 AND cc.ClaveCentroCosto = m.ClaveCentroCosto
                                INNER JOIN c_cliente AS c ON cc.ClaveCliente = c.ClaveCliente
                                LEFT JOIN c_ticket AS t ON m.IdTicket = t.IdTicket
                                LEFT JOIN k_tecnicoticket AS kt ON kt.IdTicket = t.IdTicket
                                LEFT JOIN c_usuario AS u ON u.IdUsuario = kt.IdUsuario
                                LEFT JOIN c_notaticket AS nt ON nt.IdTicket = t.IdTicket
                                LEFT JOIN k_nota_refaccion AS knr ON knr.IdNotaTicket = nt.IdNotaTicket
                                ORDER BY Fecha;");
                            while ($rs = mysql_fetch_array($result)) {
                                echo "<tr>";
                                echo "<td align='center' scope='row'>" . $rs['NombreRazonSocial'] . "</td>";
                                echo "<td align='center' scope='row'>" . $rs['fecha_planeada'] . "</td>";
                                echo "<td align='center' scope='row'>" . $rs['tecnico'] . "</td>";
                                echo "</tr>";
                            }
                            ?>
                        </tbody>                        
                    </table>
                </fieldset>
            </div>
            <div id="tabs-7">
                <fieldset>
                    <legend>Incidencias de este equipo</legend>
                    <table class='dataTable'>
                        <thead>
                            <tr>
                                <?php
                                echo "<tr>";
                                echo "<th width=\"2%\" align=\"center\" scope=\"col\">Ticket</th>";
                                echo "<th width=\"2%\" align=\"center\" scope=\"col\">Fecha Inico</th>";
                                echo "<th width=\"2%\" align=\"center\" scope=\"col\">Fecha Fin</th>";
                                echo "<th width=\"2%\" align=\"center\" scope=\"col\">Descripción</th>";
                                echo "<th width=\"2%\" align=\"center\" scope=\"col\">Estatus</th>";
                                echo "<th width=\"2%\" align=\"center\" scope=\"col\">Técnico</th>";
                                echo "</tr>";
                                ?>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $result = $catalogo->obtenerLista("SELECT t.IdTicket, i.Fecha, i.FechaFin, i.Descripcion, 
                            (CASE WHEN !ISNULL(u.IdUsuario) THEN CONCAT(u.Nombre,' ',u.ApellidoMaterno,' ',u.ApellidoPaterno) ELSE i.UsuarioCreacion END) AS tecnico,
                            (SELECT CASE WHEN ISNULL(nt.IdNotaTicket) THEN (SELECT et.Nombre FROM c_estadoticket AS et WHERE et.IdEstadoTicket = t.EstadoDeTicket) 
                            ELSE (SELECT e.Nombre FROM c_estado AS e WHERE e.IdEstado = nt.IdEstatusAtencion) END) AS estado, e3.Nombre AS tipo 
                            FROM `c_incidencias` AS i 
                            LEFT JOIN c_ticket AS t ON i.Id_Ticket = t.IdTicket
                            LEFT JOIN k_tecnicoticket AS ktt ON ktt.IdTicket = t.IdTicket
                            LEFT JOIN c_usuario AS u ON ktt.IdUsuario = u.IdUsuario
                            LEFT JOIN c_notaticket AS nt ON nt.IdNotaTicket = (SELECT MAX(IdNotaTicket) FROM c_notaticket AS nt2 WHERE nt2.IdTicket = t.IdTicket)
                            LEFT JOIN c_estado AS e3 ON nt.IdEstatusAtencion = e3.IdEstado
                            WHERE i.NoSerie = '$NoSerie' 
                            ORDER BY i.Fecha DESC;");
                            while ($rs = mysql_fetch_array($result)) {
                                echo "<tr>";
                                echo "<td align='center' scope='row'>" . $rs['IdTicket'] . "</td>";
                                echo "<td align='center' scope='row'>" . $rs['Fecha'] . "</td>";
                                echo "<td align='center' scope='row'>" . $rs['FechaFin'] . "</td>";
                                if(substr($rs['Descripcion'], 0, 3) == "../"){
                                    echo "<td><a href='".substr($rs['Descripcion'],3)."'>Ver incidencia</a></td>";
                                }else{
                                    echo "<td align='center' scope='row'>" . $rs['Descripcion'] . "</td>";
                                }
                                echo "<td align='center' scope='row'></td>";

                                echo "<td align='center' scope='row'></td>";
                                echo "</tr>";
                            }
                            ?>
                        </tbody>                        
                    </table>
                </fieldset>
            </div>
            <div id="tabs-8">
                <fieldset>
                    <legend>Lecturas de corte</legend>
                    <table class='dataTable'>
                        <thead>
                            <tr>
                                <?php
                                echo "<tr>";
                                echo "<th width=\"2%\" align=\"center\" scope=\"col\">Fecha</th>";    
                                echo "<th width=\"2%\" align=\"center\" scope=\"col\">Mes</th>";                                
                                if($color){
                                    foreach ($cabeceras_color as $value) {
                                        echo "<th width=\"2%\" align=\"center\" scope=\"col\">$value</th>";
                                    }                                    
                                }else{
                                    foreach ($cabeceras_bn as $value) {
                                        echo "<th width=\"2%\" align=\"center\" scope=\"col\">$value</th>";
                                    }
                                }
                                echo "</tr>";
                                ?>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $result = $catalogo->obtenerLista("SELECT * FROM c_lectura WHERE NoSerie = '$NoSerie' AND LecturaCorte = 1 ORDER BY Fecha;");
                            while ($rs = mysql_fetch_array($result)) {
                                echo "<tr>";
                                echo "<td align='center' scope='row'>" . substr($rs['Fecha'], 0, 7) . "</td>";                                
                                echo "<td align='center' scope='row'>" . substr($catalogo->formatoFechaReportes($rs['Fecha']), 15) . "</td>";                                
                                if(!$fa){
                                    echo "<td align='center' scope='row'>" . $rs['ContadorBNPaginas'] . "</td>";
                                    if($color){
                                        echo "<td align='center' scope='row'>" . $rs['ContadorColorPaginas'] . "</td>";
                                    }
                                }else{
                                    echo "<td align='center' scope='row'>" . $rs['ContadorBNML'] . "</td>";
                                    if($color){
                                        echo "<td align='center' scope='row'>" . $rs['ContadorColorML'] . "</td>";
                                    }
                                }
                                foreach ($contadores_color as $key => $value) {  
                                    if(($key > 1 && $color) || (!$color && $key == 1)){
                                        echo "<td align='center' scope='row'>" . $rs[$value] . "</td>";
                                    }
                                }

                                echo "</tr>";
                            }
                            ?>
                        </tbody>
                    </table>
                </fieldset>
            </div>
            <div id="tabs-9">
                <fieldset>
                    <legend>Todas las lecturas</legend>
                    <table class='dataTable' style="width:100%">
                        <thead>
                            <tr>
                                <?php
                                echo "<tr width:100%>";
                                echo "<th align=\"center\" scope=\"col\">Fecha</th>";                                  
                                if($color){
                                    foreach ($cabeceras_color as $value) {
                                        echo "<th align=\"center\" scope=\"col\">$value</th>";
                                    }                                    
                                }else{
                                    foreach ($cabeceras_bn as $value) {
                                        echo "<th align=\"center\" scope=\"col\">$value</th>";
                                    }
                                }
                                echo "<th>Origen</th>";
                                echo "<th>Usuario</th>";
                                echo "<th></th>";
                                echo "</tr>";
                                ?>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $consulta = "(SELECT c.Fecha, (CASE WHEN !ISNULL(c.ContadorBNPaginas) THEN c.ContadorBNPaginas ELSE c.ContadorBNML END) AS ContadorBN, 
                                (CASE WHEN !ISNULL(c.ContadorColorPaginas) THEN c.ContadorColorPaginas ELSE c.ContadorColorML END) AS ContadorColor,
                                c.NivelTonAmarillo, c.NivelTonCian, c.NivelTonMagenta, c.NivelTonNegro, c.Pantalla,
                                CONCAT_WS(' ',u.Nombre,u.ApellidoPaterno,u.ApellidoMaterno) AS Nombre,
                                CONCAT('1',(CASE WHEN !ISNULL(rm.id_reportes) THEN rm.id_reportes ELSE rm2.id_reportes END)) AS Extra,
                                '0' AS Resurtido
                                FROM c_lectura c 
                                LEFT JOIN movimientos_equipo AS me ON me.id_lectura = c.IdLectura
                                LEFT JOIN movimientos_equipo AS me2 ON me2.id_lectura2 = c.IdLectura
                                LEFT JOIN reportes_movimientos AS rm ON me.id_movimientos = rm.id_movimientos
                                LEFT JOIN reportes_movimientos AS rm2 ON me2.id_movimientos = rm2.id_movimientos
                                LEFT JOIN c_usuario AS u ON u.Loggin = c.UsuarioCreacion
                                WHERE c.NoSerie = '$NoSerie')
                                UNION
                                (SELECT Fecha, lt.ContadorBN AS ContadorBN, lt.ContadorCL AS ContadorColor, 
                                lt.NivelTonAmarillo, lt.NivelTonCian, lt.NivelTonMagenta, lt.NivelTonNegro, lt.Pantalla, 
                                CONCAT_WS(' ',u.Nombre,u.ApellidoPaterno,u.ApellidoMaterno) AS Nombre, CONCAT('2',lt.fk_idticket) AS Extra,
                                t.Resurtido AS Resurtido
                                FROM c_lecturasticket lt 
                                LEFT JOIN c_usuario AS u ON u.Loggin = lt.UsuarioCreacion
                                LEFT JOIN c_ticket AS t ON t.IdTicket = lt.fk_idticket
                                WHERE lt.ClvEsp_Equipo = '$NoSerie')
                                 ORDER BY Fecha;";
                            $result = $catalogo->obtenerLista($consulta);
                            while ($rs = mysql_fetch_array($result)) {
                                echo "<tr width:100%>";
                                echo "<td align='center' scope='row'>" . $rs['Fecha'] . "</td>";                                                            
                                echo "<td align='center' scope='row'>" . number_format($rs['ContadorBN']) . "</td>";
                                if($color){
                                    echo "<td align='center' scope='row'>" . number_format($rs['ContadorColor']) . "</td>";
                                }
                                foreach ($contadores_color as $key => $value) {  
                                    if(($key > 1 && $color) || (!$color && $key == 1)){
                                        echo "<td align='center' scope='row'>" . $rs[$value] . "</td>";
                                    }
                                }
                                if($rs['Pantalla']=="PHP Movimiento_equipos_solicitud"){
                                    echo "<td align='center' scope='row'>Solicitud de equipo</td>";
                                }else if($rs['Pantalla']=="Entrada al almacén" || $rs['Pantalla']=="Salida del almacén"){
                                    echo "<td align='center' scope='row'>Entrada manual al almacén</td>";
                                }else if($rs['Pantalla']=="LecturaFileSave PHP"){
                                    echo "<td align='center' scope='row'>Lectura por archivo</td>";
                                }else if($rs['Pantalla']=="ASP.operacion_altalectura_aspx"){
                                    echo "<td align='center' scope='row'>Cambio de equipo</td>";
                                }else{
                                    echo "<td align='center' scope='row'>".$rs['Pantalla']."</td>";
                                }
                                echo "<td align='center' scope='row'>" . $rs['Nombre'] . "</td>";
                                if($rs['Pantalla']=="PHP Lectura corte"){
                                    echo "<td align='center' scope='row'>" . substr($catalogo->formatoFechaReportes($rs['Fecha']), 15) . "</td>";
                                }else if(isset($rs['Extra']) && $rs['Extra'] != "")
                                {    
                                    if(substr($rs['Extra'],0,1) == "2"){
                                        if($rs['Resurtido'] == "1"){
                                            echo "<td align='center' scope='row'>Ticket: <a href='../reportes/reporte_ticket_resurtido.php?idTicket=" . substr($rs['Extra'],1) . "'>" . substr($rs['Extra'],1) . "</td>";
                                        }else{
                                            echo "<td align='center' scope='row'>Ticket: <a href='../reportes/reporte_ticket.php?idTicket=" . substr($rs['Extra'],1) . "'>" . substr($rs['Extra'],1) . "</td>";
                                        }   
                                    }else{
                                        echo "<td align='center' scope='row'>Movimiento: <a href='../WEB-INF/Controllers/Ventas/Controller_Reporte_Historico.php?noSolicitud=" . substr($rs['Extra'],1) . "'>" . $rs['Extra'] . "</td>";
                                    }
                                }else{
                                    echo "<td align='center' scope='row'></td>";
                                }
                                echo "</tr>";
                            }
                            ?>
                        </tbody>
                    </table>
                </fieldset>
            </div>
        </div>
        <input type="submit" class="button" value="Cancelar" style="margin:10px 0 0 90%;" onclick="cambiarContenidos('<?php echo $atras; ?>');
                return false;"/>
    </body>
</html>