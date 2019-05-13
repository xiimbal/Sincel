<?php

session_start();

if (!isset($_SESSION['user']) || $_SESSION['user'] == "" || !isset($_POST['fecha_inicio']) || !isset($_POST['fecha_final'])) {
    header("Location: ../index.php");
}

include_once("../WEB-INF/Classes/Catalogo.class.php");
include_once("../WEB-INF/Classes/Usuario.class.php");
include_once("../WEB-INF/Classes/Configuracion.class.php");
include_once("../WEB-INF/Classes/CentroCosto.class.php");
include_once("../WEB-INF/Classes/Equipo.class.php");
include_once("../WEB-INF/Classes/EquipoCaracteristicasFormatoServicio.class.php");

$catalogo = new Catalogo();
$equipo = new Equipo();

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

$funciones = "";

$obj = new Configuracion();
if (isset($_POST['id']) && $obj->getRegistroById($_POST['id'])) {    
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
    if($obj->getIdTipoInventario() != null){
        $NombreTipoInventario = $obj->getNombreTipoInventarioById($obj->getIdTipoInventario());
    }
    
    $equipo->getRegistroById($NoParte);
    $Modelo = $equipo->getModelo();
    $componentes = $obj->getComponentesK();
    $disabled = 'disabled="disabled"';
    
    $tipo = new EquipoCaracteristicasFormatoServicio();
    $result = $tipo->getTiposDeServicios($NoParte);
    $tipo->setNoParte($NoParte);
    while($rs = mysql_fetch_array($result)){
        $funciones .= "<input type='checkbox' checked='checked' disabled='disabled'/> ".$rs['servicio'];
    }
    
    /*Vemos si ponemos contadores de color o b/n*/
    $contadores_color = array(0=>"ContadorCL",1=>"NivelTonCian",2=>"NivelTonNegro",3=>"NivelTonMagenta",4=>"NivelTonAmarillo");
    $cabeceras_color = array(0=>"Contador color", 1=>"Nivel Cian", 2=>"Nivel Negro", 3=>"Nivel Magenta", 4=>"Nivel Amarillo");
    $contadores_bn = array(0=>"ContadorBN",1=>"NivelTonNegro");
    $cabeceras_bn = array(0=>"Contador BN", 1=>"Nivel negro");
    $solicitados_color = array(0=>"TonerNegro", 1=>"TonerCian", 2=>"TonerMagenta", 3=>"TonerAmarillo");
    $cabeceras_s_color = array(0=>"Toner negro(*)", 1=>"Toner Cian(*)", 2=>"Toner Magenta(*)", 3=>"Toner Amarillo(*)");
    $solicitados_bn = array(0=>"TonerNegro");
    $cabeceras_s_bn = array(0=>"Toner Negro(*)");
    $result = $tipo->getTiposDeServicios($NoParte);
    while($rs = mysql_fetch_array($result)){
        if($rs['IdTipoServicio'] == "1"){
            $color = true;
        }else if($rs['IdTipoServicio'] == "2"){
            $fax = true;
        }else{
            $bn = true;
        }
    }
}

$fechaInicial = $_POST['fecha_inicio'];
$fechaFinal = $_POST['fecha_final'];
$datos_actuales = false;
$clientes = false;
$tickets = false;
$refacciones = false;
$toner = false;
$mantenimiento = false;
$incidencia = false;

if(isset($_POST['datos_actuales'])){
    $datos_actuales = true;
}

if(isset($_POST['h_clientes'])){
    $clientes = true;
}

if(isset($_POST['h_tickets'])){
    $tickets = true;
}

if(isset($_POST['h_refacciones'])){
    $refacciones = true;
}

if(isset($_POST['h_toner'])){
    $toner = true;
}

if(isset($_POST['h_mantenimiento'])){
    $mantenimiento = true;
}

if(isset($_POST['h_incidencias'])){
    $incidencia = true;
}

?>
<!DOCTYPE HTML>
<html lang="es">
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
        <style>
            body{font-family: Arial; font-size: 15px;}
            .titulo{font-weight: bold; font-size: 18px;}
            table{border-collapse:collapse;}
        </style>
        <title>Reporte de bitácora</title>
        <link rel="shortcut icon" href="../resources/images/logos/ra4.png" type="image/x-icon"/>
    </head>
    <body>
        <a href=javascript:window.print(); style="margin: 85%;">Imprimir</a>
        <?php
            if($datos_actuales){
                ?>
                <fieldset>
                    <legend>Datos actuales</legend>
                    <fieldset><!--Producto-->
                    <legend>Producto</legend>
                    <table style='width: 100%;'>
                        <tr>
                            <td><label for='no_parte'>No. de parte</label></td>
                            <td><?php echo $NoParte; ?></td>
                            <td><label for='no_parte_anterior'>No. de parte anterior</label></td>
                            <td></td>
                        </tr>
                        <tr>
                            <td><label for='modelo'>Modelo</label></td>
                            <td><?php echo $Modelo; ?></td>
                            <td>Tipo</td>
                            <td>Equipo</td>
                        </tr>
                        <tr>
                            <td><label for='no_serie'>No. de serie</label></td>
                            <td><?php echo $NoSerie; ?></td>
                            <td><label for='no_genesis'>No. Génesis</label></td>
                            <td><?php echo $NoGenesis; ?></td>
                        </tr>
                        <tr>
                            <td>IP</td>
                            <td><?php echo $IP; ?></td>
                            <td></td>
                            <td></td>
                        </tr>
                        <tr>
                            <td>MAC Address</td>
                            <td><?php echo $MAC; ?></td>
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
                    //echo $consulta;
                    $result = $catalogo->obtenerLista($consulta);
                    $lugar = "";
                    $ubicacion = "";
                    $ubicacion_inventario = "";
                    $cc = "";
                    while ($rs = mysql_fetch_array($result)) {
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
                                <td>$ubicacion_inventario</td>
                                <td></td>
                                <td></td>
                            </tr>                        
                            <tr>
                                <td>Ubicaci&oacute;n</td>
                                <td>$ubicacion</td>
                                <td></td>
                                <td></td>
                            </tr>
                        </table>";
                    }
                    if ($lugar == "") {
                        $consulta = "SELECT a.nombre_almacen FROM k_almacenequipo AS kae 
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
                </fieldset>
                <br/>
                <?php
            }
            if($clientes){
                ?>
                <fieldset>
                    <legend>H. Clientes</legend>
                    <fieldset>
                    <legend>Clientes donde ha estado el equipo</legend>                    
                    <table class='dataTable'>
                        <thead>
                            <tr>
                                <?php
                                echo "<th width=\"2%\" align=\"center\" scope=\"col\">Fecha</th>";
                                echo "<th width=\"2%\" align=\"center\" scope=\"col\">Cliente</th>";
                                echo "<th width=\"2%\" align=\"center\" scope=\"col\">Localidad</th>";                                
                                echo "<th width=\"2%\" align=\"center\" scope=\"col\">Contador BN Pag.</th>";
                                echo "<th width=\"2%\" align=\"center\" scope=\"col\">Contador Color Pags.</th>";
                                echo "<th width=\"2%\" align=\"center\" scope=\"col\">Contador BN ML</th>";
                                echo "<th width=\"2%\" align=\"center\" scope=\"col\">Contador Color ML</th>";
                                ?>
                            </tr>                        
                        </thead>
                        <tbody>
                            <?php
                            if ($id != "") {
                                $consulta = "SELECT 
                                meq.id_movimientos, meq.tipo_movimiento, meq.NoSerie, meq.pendiente, meq.Fecha, 
                                CONCAT(c1.ClaveCliente,' - ',c1.NombreRazonSocial) AS cliente_anterior, c1.RFC AS rfc_anterior, con1.Nombre AS contacto_anterior, con1.Telefono AS telefono_anterior,
                                CONCAT(cc1.ClaveCentroCosto, ' - ' ,cc1.Nombre) AS centro_anterior, c2.RFC AS rfc_nuevo, con2.Nombre AS contacto_nuevo, con2.Telefono AS telefono_nuevo,
                                CONCAT(c2.ClaveCliente,' - ',c2.NombreRazonSocial) AS cliente_nuevo,
                                CONCAT(cc2.ClaveCentroCosto, ' - ' ,cc2.Nombre) AS centro_nuevo,
                                CONCAT(dom1.Calle,' No. Ext. ',dom1.NoExterior, ' No. Int. ',dom1.NoInterior) AS calle_anterior, 
                                dom1.Colonia AS colonia_anterior, dom1.Delegacion AS delegacion_anterior, dom1.Ciudad AS ciudad_anterior,
                                CONCAT(dom2.Calle,' No. Ext. ',dom2.NoExterior, ' No. Int. ',dom2.NoInterior) AS calle_nuevo, 
                                dom2.Colonia AS colonia_nuevo, dom2.Delegacion AS delegacion_nuevo, dom2.Ciudad AS ciudad_nuevo,
                                alm1.nombre_almacen AS almacen_anterior, alm2.nombre_almacen AS almacen_nuevo,
                                (SELECT CASE WHEN ISNULL(cliente_nuevo) THEN alm2.nombre_almacen ELSE cliente_nuevo END) AS destino,
                                cl.ContadorBNML, cl.ContadorBNPaginas, cl.ContadorColorML, cl.ContadorColorPaginas, cl.NivelTonAmarillo, cl.NivelTonCian, cl.NivelTonMagenta, cl.NivelTonNegro
                                FROM `movimientos_equipo` AS meq 
                                LEFT JOIN c_cliente AS c1 ON c1.ClaveCliente = meq.clave_cliente_anterior
                                LEFT JOIN c_cliente AS c2 ON c2.ClaveCliente = meq.clave_cliente_nuevo
                                LEFT JOIN c_centrocosto AS cc1 ON cc1.ClaveCentroCosto = meq.clave_centro_costo_anterior
                                LEFT JOIN c_centrocosto AS cc2 ON cc2.ClaveCentroCosto = meq.clave_centro_costo_nuevo
                                LEFT JOIN c_contacto AS con1 ON con1.ClaveEspecialContacto = cc1.ClaveCentroCosto
                                LEFT JOIN c_contacto AS con2 ON con2.ClaveEspecialContacto = cc2.ClaveCentroCosto
                                LEFT JOIN c_domicilio AS dom1 ON dom1.ClaveEspecialDomicilio = cc1.ClaveCentroCosto
                                LEFT JOIN c_domicilio AS dom2 ON dom2.ClaveEspecialDomicilio = cc2.ClaveCentroCosto
                                LEFT JOIN c_almacen AS alm1 ON alm1.id_almacen = meq.almacen_anterior
                                LEFT JOIN c_almacen AS alm2 ON alm2.id_almacen = meq.almacen_nuevo
                                LEFT JOIN c_lectura AS cl ON cl.IdLectura = meq.id_lectura
                                WHERE meq.NoSerie = '$NoSerie' AND meq.Fecha BETWEEN '$fechaInicial' AND '$fechaFinal';";
                                $result = $catalogo->obtenerLista($consulta);
                                while ($rs = mysql_fetch_array($result)) {
                                    echo "<tr>";
                                    echo "<td align='center' scope='row'>" . $rs['Fecha'] . "</td>";
                                    echo "<td align='center' scope='row'>" . $rs['destino'] . "</td>";
                                    echo "<td align='center' scope='row'>" . $rs['centro_nuevo'] . "</td>";                                    
                                    echo "<td align='center' scope='row'>" . $rs['ContadorBNPaginas'] . "</td>";
                                    echo "<td align='center' scope='row'>" . $rs['ContadorColorPaginas'] . "</td>";
                                    echo "<td align='center' scope='row'>" . $rs['ContadorBNML'] . "</td>";
                                    echo "<td align='center' scope='row'>" . $rs['ContadorColorML'] . "</td>";
                                    echo "</tr>";
                                }
                            }
                            ?>
                        </tbody>
                    </table>
                </fieldset>
                </fieldset>  
                <br/>
                <?php
            }
            if($tickets){
                ?>
                <fieldset>
                    <legend>H. Tickets</legend>
                    <fieldset>
                    <legend>Tickets del equipo</legend>
                    <table class='dataTable'>
                        <thead>
                            <tr>
                                <?php
                                    $cabeceras = array("Ticket", "Fecha", "No Serie", "Cliente", "Área de atención", "Ubicación", "Falla", "Último estatus ticket", "Última Nota", "Fecha nota");
                                    for ($i = 0; $i < (count($cabeceras)); $i++) {
                                        echo "<th width=\"2%\" align=\"center\" scope=\"col\">" . $cabeceras[$i] . "</th>";
                                    }
                                ?>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $result = $catalogo->obtenerLista("SELECT
                                    t.IdTicket,
                                    t.FechaHora,
                                    t.DescripcionReporte,
                                    t.NombreCentroCosto,
                                    t.TipoReporte,
                                    (SELECT CASE WHEN e2.IdEstado = 2 THEN ( SELECT group_concat( ClaveEspEquipo SEPARATOR ', ') FROM `c_pedido` WHERE IdTicket = t.IdTicket) ELSE t.NoSerieEquipo END) AS NumSerie,
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
                                    nt.FechaHora AS FechaNota
                            FROM c_ticket AS t
                            INNER JOIN c_estadoticket AS e ON e.IdEstadoTicket = t.EstadoDeTicket
                            LEFT JOIN c_domicilioticket AS dt ON dt.IdTicket = t.IdTicket
                            LEFT JOIN c_gzona AS cgz ON cgz.id_gzona = dt.Id_gzona
                            LEFT JOIN c_estado AS e1 ON e1.IdEstado = t.TipoReporte
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
                            WHERE (SELECT CASE WHEN e2.IdEstado = 2 THEN ( SELECT group_concat( ClaveEspEquipo SEPARATOR ', ') FROM `c_pedido` WHERE IdTicket = t.IdTicket) ELSE t.NoSerieEquipo END) LIKE '%$NoSerie%'
                            AND t.FechaHora BETWEEN '$fechaInicial' AND '$fechaFinal'
                            ORDER BY IdTicket;");
                            while ($rs = mysql_fetch_array($result)) {
                                echo "<tr>";
                                echo "<td align='center' scope='row'>" . $rs['IdTicket'] . "</td>";
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
                            ?>
                        </tbody>                        
                    </table>
                </fieldset>
                </fieldset> 
                <br/>
                <?php
            }
            if($refacciones){
                ?>
                <fieldset>
                    <legend>H. Refacciones</legend>
                    <fieldset>
                    <legend>Piezas cambiadas</legend>
                    <table class='dataTable'>
                        <thead>
                            <tr>
                                <?php
                                echo "<tr>";
                                echo "<th width=\"2%\" align=\"center\" scope=\"col\">No. Serie</th>";
                                echo "<th width=\"2%\" align=\"center\" scope=\"col\">No. de parte</th>";
                                echo "<th width=\"2%\" align=\"center\" scope=\"col\">Tipo</th>";
                                echo "<th width=\"2%\" align=\"center\" scope=\"col\">Modelo</th>";
                                echo "<th width=\"2%\" align=\"center\" scope=\"col\">Cantidad</th>";
                                echo "<th width=\"2%\" align=\"center\" scope=\"col\">Fecha de préstamo</th>";
                                echo "<th width=\"2%\" align=\"center\" scope=\"col\">Fecha de regreso</th>";
                                echo "<th width=\"2%\" align=\"center\" scope=\"col\">Ticket</th>";
                                echo "</tr>";
                                ?>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $result = $catalogo->obtenerLista("SELECT c.Modelo,tc.Nombre AS tipoComponente, m.* FROM `movimiento_componente_en_equipo` AS m
                            INNER JOIN c_componente AS c ON m.NoSerieOrigen = '$NoSerie' AND m.Tipo = 2 AND c.NoParte = m.NoParte AND m.FechaInicio >= '$fechaInicial' AND m.FechaFin <= '$fechaFinal'
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
                            }
                            ?>
                        </tbody>                        
                    </table>
                </fieldset>   
                <fieldset>
                    <legend>Piezas que este este equipo ha prestado</legend>
                    <table class='dataTable'>
                        <thead>
                            <tr>
                                <?php
                                echo "<tr>";
                                echo "<th width=\"2%\" align=\"center\" scope=\"col\">No. Serie</th>";
                                echo "<th width=\"2%\" align=\"center\" scope=\"col\">No. de parte</th>";
                                echo "<th width=\"2%\" align=\"center\" scope=\"col\">Tipo</th>";
                                echo "<th width=\"2%\" align=\"center\" scope=\"col\">Modelo</th>";
                                echo "<th width=\"2%\" align=\"center\" scope=\"col\">Cantidad</th>";
                                echo "<th width=\"2%\" align=\"center\" scope=\"col\">Fecha de préstamo</th>";
                                echo "<th width=\"2%\" align=\"center\" scope=\"col\">Fecha de regreso</th>";
                                echo "<th width=\"2%\" align=\"center\" scope=\"col\">Ticket</th>";
                                echo "</tr>";
                                ?>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $result = $catalogo->obtenerLista("SELECT c.Modelo,tc.Nombre AS tipoComponente, m.* FROM `movimiento_componente_en_equipo` AS m
                            INNER JOIN c_componente AS c ON m.NoSerieOrigen = '$NoSerie' AND m.Tipo = 1 AND c.NoParte = m.NoParte AND m.FechaInicio >= '$fechaInicial' AND m.FechaFin <= '$fechaFinal'
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
                            }
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
                                echo "<tr>";
                                echo "<th width=\"2%\" align=\"center\" scope=\"col\">No. Serie</th>";
                                echo "<th width=\"2%\" align=\"center\" scope=\"col\">No. de parte</th>";
                                echo "<th width=\"2%\" align=\"center\" scope=\"col\">Tipo</th>";
                                echo "<th width=\"2%\" align=\"center\" scope=\"col\">Modelo</th>";
                                echo "<th width=\"2%\" align=\"center\" scope=\"col\">Cantidad</th>";
                                echo "<th width=\"2%\" align=\"center\" scope=\"col\">Fecha de préstamo</th>";
                                echo "<th width=\"2%\" align=\"center\" scope=\"col\">Fecha de regreso</th>";
                                echo "<th width=\"2%\" align=\"center\" scope=\"col\">Ticket</th>";
                                echo "</tr>";
                                ?>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $result = $catalogo->obtenerLista("SELECT c.Modelo,tc.Nombre AS tipoComponente, m.* FROM `movimiento_componente_en_equipo` AS m
                            INNER JOIN c_componente AS c ON m.NoSerieDestino = '$NoSerie' AND m.Tipo = 1 AND c.NoParte = m.NoParte AND m.FechaInicio >= '$fechaInicial' AND m.FechaFin <= '$fechaFinal'
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
                            }
                            ?>
                        </tbody>                        
                    </table>
                </fieldset>
                <fieldset>
                    <legend>Piezas agregadas</legend>
                    <table class='dataTable'>
                        <thead>
                            <tr>
                                <?php
                                echo "<tr>";
                                echo "<th width=\"2%\" align=\"center\" scope=\"col\">No. Serie</th>";
                                echo "<th width=\"2%\" align=\"center\" scope=\"col\">No. de parte</th>";
                                echo "<th width=\"2%\" align=\"center\" scope=\"col\">Tipo</th>";
                                echo "<th width=\"2%\" align=\"center\" scope=\"col\">Modelo</th>";
                                echo "<th width=\"2%\" align=\"center\" scope=\"col\">Cantidad</th>";
                                echo "<th width=\"2%\" align=\"center\" scope=\"col\">Fecha de préstamo</th>";
                                echo "<th width=\"2%\" align=\"center\" scope=\"col\">Fecha de regreso</th>";
                                echo "<th width=\"2%\" align=\"center\" scope=\"col\">Ticket</th>";
                                echo "</tr>";
                                ?>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $result = $catalogo->obtenerLista("SELECT c.Modelo,tc.Nombre AS tipoComponente, m.* FROM `movimiento_componente_en_equipo` AS m
                            INNER JOIN c_componente AS c ON m.NoSerieDestino = '$NoSerie' AND m.Tipo = 3 AND c.NoParte = m.NoParte AND m.FechaInicio >= '$fechaInicial' AND m.FechaFin <= '$fechaFinal'
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
                            }
                            ?>
                        </tbody>                        
                    </table>
                </fieldset>
                </fieldset>     
                <br/>
                <?php
            }
            if($toner){
                ?>
                <fieldset>
                    <legend>H. Toner</legend>
                    <fieldset>
                            <legend>Tóner solicitado para este equipo</legend>
                            <?php
                                $consulta = "SELECT
                                    t.IdTicket,
                                    t.FechaHora,
                                    t.DescripcionReporte,";                        
                                if($color){
                                    foreach ($solicitados_color as $value) {
                                        $consulta.="p.$value,";
                                    }
                                    foreach ($contadores_color as $value) {
                                        $consulta.= "lt.$value,";
                                    }
                                }else{
                                    foreach ($solicitados_bn as $value) {
                                        $consulta.="p.$value,";
                                    }
                                    foreach ($contadores_bn as $value) {
                                        $consulta.= "lt.$value,";
                                    }
                                }
                                $consulta .="CONCAT(t.NombreCliente,' - ',t.NombreCentroCosto) AS cliente                            
                                    FROM c_ticket AS t
                                    INNER JOIN c_pedido AS p ON t.AreaAtencion = 2 AND p.ClaveEspEquipo = '$NoSerie' AND p.IdTicket = t.IdTicket
                                    LEFT JOIN c_lecturasticket AS lt ON p.IdLecturaTicket = lt.id_lecturaticket
                                    ORDER BY IdTicket;";
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
                                        if($color){
                                            foreach ($cabeceras_s_color as $value) {
                                                echo "<th width=\"2%\" align=\"center\" scope=\"col\">$value</th>";  
                                            }
                                            foreach ($cabeceras_color as $value) {
                                                echo "<th width=\"2%\" align=\"center\" scope=\"col\">$value</th>";  
                                            }
                                        }else{
                                            foreach ($cabeceras_s_bn as $value) {
                                                echo "<th width=\"2%\" align=\"center\" scope=\"col\">$value</th>";  
                                            }
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
                                    $result = $catalogo->obtenerLista($consulta);
                                    while ($rs = mysql_fetch_array($result)) {
                                        echo "<tr>";
                                        echo "<td align='center' scope='row'>" . $rs['cliente'] . "</td>";
                                        echo "<td align='center' scope='row'>" . $rs['FechaHora'] . "</td>";
                                        echo "<td align='center' scope='row'>" . $rs['DescripcionReporte'] . "</td>";
                                        echo "<td align='center' scope='row'>" . $rs['IdTicket'] . "</td>"; 
                                        if($color){
                                            foreach ($solicitados_color as $value) {
                                                echo "<td align='center' scope='row'>" . $rs[$value] . "</td>"; 
                                            }
                                            foreach ($contadores_color as $value) {
                                                echo "<td align='center' scope='row'>" . $rs[$value] . "</td>"; 
                                            }
                                        }else{
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
                </fieldset>        
                <br/>
                <?php
            }
            if($mantenimiento){
                ?>
                <fieldset>
                    <legend>H. Mantenimientos</legend>
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
                                    WHERE m.Fecha BETWEEN '$fechaInicial' AND '$fechaFinal'
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
                                WHERE m.Fecha BETWEEN '$fechaInicial' AND '$fechaFinal'
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
                </fieldset> 
                <br/>
                <?php
            }
            if($incidencia){
                ?>
                <fieldset>
                    <legend>H. Incidencias</legend>
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
                                echo "<th width=\"2%\" align=\"center\" scope=\"col\">Diagnostico</th>";
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
                            WHERE i.NoSerie = '$NoSerie' AND i.Fecha BETWEEN '$fechaInicial' AND '$fechaFinal'
                            ORDER BY i.Fecha DESC;");
                            while ($rs = mysql_fetch_array($result)) {
                                echo "<tr>";
                                echo "<td align='center' scope='row'>" . $rs['IdTicket'] . "</td>";
                                echo "<td align='center' scope='row'>" . $rs['Fecha'] . "</td>";
                                echo "<td align='center' scope='row'>" . $rs['FechaFin'] . "</td>";
                                echo "<td align='center' scope='row'>" . $rs['Descripcion'] . "</td>";
                                echo "<td align='center' scope='row'></td>";                                
                                echo "<td align='center' scope='row'></td>";
                                echo "</tr>";
                            }
                            ?>
                        </tbody>                        
                    </table>
                </fieldset>
                </fieldset>                
                <?php
            }
        ?>
    </body>
</html>