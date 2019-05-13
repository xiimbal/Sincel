<?php
session_start();

if (!isset($_SESSION['user']) || $_SESSION['user'] == "") {
    header("Location: ../index.php");
}

include_once("../WEB-INF/Classes/Catalogo.class.php");
include_once("../WEB-INF/Classes/PermisosSubMenu.class.php");
include_once("../WEB-INF/Classes/Parametros.class.php");
include_once("../WEB-INF/Classes/Indicadores_Facturacion.class.php");
include_once("../WEB-INF/Classes/Indicadores.class.php");
$indicadores = new Indicadores_Facturacion();
$indicador = new Indicadores();
$catalogo = new Catalogo();
$permisos_grid = new PermisosSubMenu();
$permisos_grid2 = new PermisosSubMenu();
$parametros = new Parametros();
$parametros->getRegistroById(46);
$same_page = "ventas/historico_clientes.php";

$permisos_grid->getPermisosSubmenu($_SESSION['idUsuario'], $same_page);
$nombre_objeto = $permisos_grid2->getNombreTicketSistema();
$nombre_nota = $permisos_grid2->getTitulo(7); //Nombre de las notas en la empresa actual
$id = "";
$nombre = "";
$creado = "";
$modificado = "";
$emisor = "";
$ejecutivo = "";
$total = 0;
$array = array();
$params = "";
$anio_inicial = (int) date("Y");
$anio_anterior = $anio_inicial - 1;
if(isset($_GET['id']) && $_GET['id'] != ""){
    $id = $_GET['id'];
    $params = "?id=$id";
    $result = $catalogo->obtenerLista("SELECT CONCAT(c.NombreRazonSocial, IF(!ISNULL(cg.Nombre),CONCAT(' (',cg.Nombre,')'),'')) AS cliente,
    e.Nombre AS estado, COUNT(*) AS numero, CONCAT('<b>',c.FechaCreacion, '</b> por <b>', c.UsuarioCreacion,'</b>') AS creado,
    CONCAT('<b>',c.FechaUltimaModificacion, '</b> por <b>', c.UsuarioUltimaModificacion,'</b>') AS modificado
    FROM c_cliente c LEFT JOIN c_clientegrupo cg ON cg.ClaveGrupo = c.ClaveGrupo
    LEFT JOIN c_ticket t ON t.ClaveCliente = c.ClaveCliente
    LEFT JOIN c_notaticket nt ON nt.IdNotaTicket = (SELECT MAX(x.IdNotaTicket) FROM c_notaticket x WHERE x.IdTicket = t.IdTicket)
    LEFT JOIN c_estado e ON e.IdEstado = nt.IdEstatusAtencion
    WHERE c.ClaveCliente = '$id' GROUP BY e.IdEstado");
    while($rs = mysql_fetch_array($result)){
        $nombre = $rs['cliente'];
        $creado = $rs['creado'];
        $modificado = $rs['modificado'];
        if(!empty($rs['estado'])){
            $array[$rs['estado']] = $rs['numero']; 
            $total += $rs['numero'];
        }    
        
    }
}else{
    echo "No se pudo completar la acción. Inténtelo de nuevo";
    return;
}
?>
<!DOCTYPE html>
<html>
    <head>
        <title></title>
        <!--script type="text/javascript" src="resources/js/paginas/historico_proyecto.js"></script-->
        <link href="resources/css/historico.css" rel="stylesheet" type="text/css"/>
    </head>
    <body>
        <div class="principal" style='overflow: auto'>
            <h3><?php echo $nombre?></h3>
            <div class='module-summary'>
                <div class="span7">
                    <div class='summaryView'>
                        <h4>Resumen</h4>
                        <hr>
                        <?php
                        $contador = 0;
                        foreach($array AS $clave => $valor){
                            if($contador == 0){//Abrimos el div contenedor
                                echo "<div class='textCenter'>";
                            }
                            echo "<div class='span3'>";
                            echo "<div><b>" . $clave . "</b></div>";
                            echo "<div class='info'>" . $valor . "</div>";
                            echo "</div>";                            
                            if($contador == 3){//Cerramos el div contenedor
                                echo "</div>";
                                $contador = 0;
                            }else{
                                $contador += 1;
                            }
                        }
                        //Vamos a ver si quedo pendiente algo
                        if($contador != 0){
                            for($x = $contador; $x <= 3; $x++){
                                echo "<div></div><div class='info'></div>";                                
                                if($x == 3){
                                    echo "</div>";
                                }
                            }                            
                        }
                        ?>
                    </div>
                    <table style='width: 100%'>
                        <tr class='summaryViewEntries'>
                            <td class='fieldLabel' style='width: 35%'>Nombre del Cliente</td>
                            <td class='fieldValue' style='width: 65%'><?php echo $nombre?></td>
                        </tr>
                        <tr class='summaryViewEntries'>
                            <td class='fieldLabel' style='width: 35%'>Total de <?php echo $nombre_objeto?></td>
                            <td class='fieldValue' style='width: 65%'><?php echo $total?></td>
                        </tr>
                    </table>
                    <div style='width: 100%'>
                        <br>
                        <div>
                            <small>Creado el <?php echo $creado?></small><br>
                            <small>Modificado por última vez el <?php echo $modificado?></small>
                        </div>
                    </div>
                    <?php if($parametros->getValor()){?>
                    <h4>Equipos</h4>
                    <hr>
                    <div class="tasksSummary">
                        <table style="width: 100%">
                            <tr>
                                <th align="center" style="width: 70%">Equipo</th>
                                <th align="center" style="width: 15%">Cantidad</th>
                                <th align="center" style="width: 15%">Costo (US $)</th>
                            </tr>
                            <?php
                            $indicador->obtenerEquiposArrendamiento($emisor, $ejecutivo, $id);
                            $indicador->imprimirTablaTipoSolicitud(3, "En garantía", $emisor, $ejecutivo, $id);
                            ?>
                        </table>   
                    </div>
                    <h4>Movimientos de Equipo</h4>
                    <small>Se muestran los cinco registros más recientes</small>
                    <hr>
                    <div class="tasksSummary">
                        <table style="width: 100%">
                            <tr>
                                <th align="center" style="width: 15%">Tipo</th>
                                <th align="center" style="width: 10%">Fecha</th>
                                <th align="center" style="width: 10%">Almacén</th>
                                <th align="center" style="width: 10%">Movimiento</th>
                                <th align="center" style="width: 5%">Modelo</th>
                                <th align="center" style="width: 5%">Cant</th>
                                <th align="center" style="width: 10%">No Serie</th>
                                <th align="center" style="width: 10%"><?php echo $nombre_objeto?></th>
                                <th align="center" style="width: 25%">Destino</th>
                            </tr>
                            <?php
                            $consultaMov = "SELECT tc.Nombre AS TipoComponente,mc.Fecha,(CASE WHEN !ISNULL(a_entrada.id_almacen) THEN a_entrada.nombre_almacen WHEN !ISNULL(a_salida.id_almacen) THEN a_salida.nombre_almacen ELSE null END) AS almacen,
                            (CASE WHEN mc.Entradada_Salida = 0 THEN 'Entrada' ELSE 'Salida' END) AS ES, c.Modelo,mc.CantidadMovimiento,t.NoSerieEquipo,mc.IdTicket,
                            (CASE WHEN !ISNULL(a_entrada.id_almacen) THEN cd1.Ciudad WHEN !ISNULL(a_salida.id_almacen) THEN cd2.Ciudad ELSE null END) AS Estado,-- ', '
                            (CASE WHEN !ISNULL(a_entrada.id_almacen) THEN da1.Delegacion WHEN !ISNULL(a_salida.id_almacen) THEN da2.Delegacion ELSE null END) AS Municipio
                            FROM `movimiento_componente` AS mc INNER JOIN c_componente AS c ON c.NoParte = mc.NoParteComponente INNER JOIN c_tipocomponente AS tc ON tc.IdTipoComponente = c.IdTipoComponente
                            LEFT JOIN c_almacen AS a_entrada ON a_entrada.id_almacen = mc.IdAlmacenNuevo LEFT JOIN c_almacen AS a_salida ON a_salida.id_almacen = mc.IdAlmacenAnterior
                            LEFT JOIN c_domicilio_almacen AS da1 ON da1.IdAlmacen = a_entrada.id_almacen LEFT JOIN c_domicilio_almacen AS da2 ON da2.IdAlmacen = a_salida.id_almacen
                            LEFT JOIN c_ciudades AS cd1 ON cd1.IdCiudad = da1.Estado LEFT JOIN c_ciudades AS cd2 ON cd2.IdCiudad = da2.Estado LEFT JOIN c_ticket AS t ON t.IdTicket = mc.IdTicket WHERE (!ISNULL(IdAlmacenNuevo) OR !ISNULL(IdAlmacenAnterior)) 
                            AND (ClaveClienteNuevo = '$id' OR ClaveClienteAnterior = '$id') GROUP BY mc.IdMovimiento ORDER BY Fecha DESC LIMIT 5";
                            $resultMov = $catalogo->obtenerLista($consultaMov);
                            while($rs = mysql_fetch_array($resultMov)){
                                echo "<tr>";
                                echo "<td align='center'>" . $rs['TipoComponente'] . "</td>";
                                echo "<td align='center'>" . $rs['Fecha'] . "</td>";
                                echo "<td align='center'>" . $rs['almacen'] . "</td>";
                                echo "<td align='center'>" . $rs['ES'] . "</td>";
                                echo "<td align='center'>" . $rs['Modelo'] . "</td>";
                                echo "<td align='center'>" . $rs['CantidadMovimiento'] . "</td>";
                                echo "<td align='center'>" . $rs['NoSerieEquipo'] . "</td>";
                                echo "<td align='center'>" . $rs['IdTicket'] . "</td>";
                                echo "<td align='center'>" . $rs['Municipio'] . ", "  . $rs['Estado'] . "</td>";
                                echo "<tr>";
                            }
                            ?>
                        </table>   
                    </div>
                    <h4>Mantenimiento Preventivo</h4>
                    <small>Se muestran los cinco registros más recientes</small>
                    <hr>
                    <div class="tasksSummary">
                        <table style="width: 100%">
                            <tr>
                                <th align="center" style="width: 10%">Fecha</th>
                                <th align="center" style="width: 10%">No Serie</th>
                                <th align="center" style="width: 10%">Cliente</th>
                                <th align="center" style="width: 10%">Localidad</th>
                                <th align="center" style="width: 10%">Estatus</th>
                            </tr>
                            <?php
                            $consultaMtto = "SELECT m.NoSerie AS NoSerie,m.Fecha AS Fecha,if(m.Estatus=0,'En proceso','') AS Estatus,
                            cc.Nombre AS CentroCosto,c.NombreRazonSocial AS Cliente FROM c_mantenimiento m INNER JOIN c_centrocosto cc ON cc.ClaveCentroCosto = m.ClaveCentroCosto
                            INNER JOIN c_cliente c ON c.ClaveCliente = cc.ClaveCliente WHERE m.Estatus=0 AND c.ClaveCliente='$id' ORDER BY Fecha DESC LIMIT 5;";
                            $resultMtto = $catalogo->obtenerLista($consultaMtto);
                            while($rs = mysql_fetch_array($result)){
                                echo "<tr>";
                                echo "<td align='center'>" . $rs['NoSerie'] . "</td>";
                                echo "<td align='center'>" . $rs['Fecha'] . "</td>";
                                echo "<td align='center'>" . $rs['Cliente'] . "</td>";
                                echo "<td align='center'>" . $rs['CentroCosto'] . "</td>";
                                echo "<td align='center'>" . $rs['Estatus'] . "</td>";
                                echo "</tr>";
                            }
                            ?>
                        </table>
                    </div>
                    <h4>Solicitudes de refacciones</h4>
                    <small>Se muestran los cinco registros más recientes</small>
                    <hr>
                    <div class="tasksSummary">
                        <table style="width: 100%">
                            <tr>
                                <th align="center" style="width: 10%"><?php echo $nombre_objeto?></th>
                                <th align="center" style="width: 10%">Fecha</th>
                                <th align="center" style="width: 15%">No Serie</th>
                                <th align="center" style="width: 25%">Falla</th>
                                <th align="center" style="width: 20%">Refacción</th>
                                <th align="center" style="width: 20%">Último Estado</th>
                            </tr>
                            <?php
                            $consultaRefacciones = "SELECT t.IdTicket,DATE(t.FechaHora) AS FechaHora,
                            (SELECT CASE WHEN t.AreaAtencion = 2 THEN (SELECT group_concat(ClaveEspEquipo separator ',') FROM `c_pedido` WHERE IdTicket = t.IdTicket) ELSE t.NoSerieEquipo END) AS NumSerie, 
                            t.DescripcionReporte,CONCAT ('(',nr.Cantidad,') ',c.Modelo,' / ',c.NoParte,' / ',c.Descripcion) AS refaccion, e.Nombre AS ultimoEstatus
                            FROM k_nota_refaccion nr LEFT JOIN c_notaticket nt ON  nt.IdNotaTicket=nr.IdNotaTicket LEFT JOIN c_ticket t ON t.IdTicket=nt.IdTicket 
                            LEFT JOIN c_estado AS e2 ON e2.IdEstado = t.AreaAtencion LEFT JOIN c_componente c ON nr.NoParteComponente=c.NoParte                                  
                            LEFT JOIN c_notaticket AS lastnt ON lastnt.IdNotaTicket = (SELECT MAX(IdNotaTicket) FROM c_notaticket WHERE IdTicket = t.IdTicket)
                            LEFT JOIN c_estado e ON lastnt.IdEstatusAtencion = e.IdEstado LEFT JOIN c_estado AS e3 ON lastnt.IdEstatusAtencion = e3.IdEstado 
                            WHERE (nt.IdEstatusAtencion=19 OR nt.IdEstatusAtencion=20 OR nt.IdEstatusAtencion=24) AND t.ClaveCliente = '$id' AND  nr.Cantidad<>0 AND c.IdTipoComponente=1 
                            ORDER BY FechaHora DESC LIMIT 5";
                            $resultRefacciones = $catalogo->obtenerLista($consultaRefacciones);
                            while($rs = mysql_fetch_array($resultRefacciones)){
                                echo "<tr>";
                                echo "<td align='center'>" . $rs['IdTicket'] . "</td>";
                                echo "<td align='center'>" . $rs['FechaHora'] . "</td>";
                                echo "<td align='center'>" . $rs['NumSerie'] . "</td>";
                                echo "<td align='center'>" . $rs['DescripcionReporte'] . "</td>";
                                echo "<td align='center'>" . $rs['refaccion'] . "</td>";
                                echo "<td align='center'>" . $rs['ultimoEstatus'] . "</td>";
                                echo "</tr>";
                            }
                            ?>
                        </table>
                    </div>
                    <h4>Solicitudes de tóner</h4>
                    <small>Se muestran los cinco registros más recientes</small>
                    <hr>
                    <div class="tasksSummary">
                        <table style="width: 100%">
                            <tr>
                                <th align="center" style="width: 10%"><?php echo $nombre_objeto?></th>
                                <th align="center" style="width: 10%">Fecha</th>
                                <th align="center" style="width: 45%">Toner</th>
                                <th align="center" style="width: 15%">Último estado</th>
                                <th align="center" style="width: 10%">Cantidad</th>
                                <th align="center" style="width: 10%">Existencia</th>
                            </tr>
                            <?php
                            $consultaToner = "SELECT t.IdTicket,DATE(t.FechaHora) AS FechaHora,CONCAT ('(',nr.Cantidad,') ',c.Modelo,' / ',c.NoParte,' / ',c.Descripcion) AS refaccion,
                            e3.Nombre AS ultimoEstatus,nr.Cantidad,(SELECT (SELECT CASE WHEN ac.cantidad_existencia IS NULL THEN '0' ELSE ac.cantidad_existencia END) AS cantidade 
                            FROM k_almacencomponente ac WHERE ac.NoParte=nr.NoParteComponente AND ac.id_almacen=6) AS cantidadExistente FROM k_nota_refaccion nr,
                            c_notaticket nt, c_ticket t,c_componente c,c_cliente cl,c_estado e, c_estado e2 LEFT JOIN c_notaticket AS lastnt ON lastnt.IdNotaTicket = (SELECT MAX(IdNotaTicket) FROM c_notaticket WHERE IdTicket = t.IdTicket)
                            LEFT JOIN c_estado AS e3 ON lastnt.IdEstatusAtencion = e3.IdEstado WHERE t.IdTicket=nt.IdTicket AND e2.IdEstado = t.AreaAtencion AND nt.IdNotaTicket=nr.IdNotaTicket AND nr.NoParteComponente=c.NoParte AND t.ClaveCliente=cl.ClaveCliente
                            AND t.ClaveCliente = '$id' AND  nt.IdEstatusAtencion=e.IdEstado AND (nt.IdEstatusAtencion=65 OR nt.IdEstatusAtencion=20) AND nr.Cantidad>0 AND c.IdTipoComponente=2 
                            ORDER BY FechaHora DESC LIMIT 5;";
                            $resultToner = $catalogo->obtenerLista($consultaToner);
                            while($rs = mysql_fetch_array($resultToner)){
                                echo "<tr>";
                                echo "<td align='center'>" . $rs['IdTicket'] . "</td>";
                                echo "<td align='center'>" . $rs['FechaHora'] . "</td>";
                                echo "<td align='center'>" . $rs['refaccion'] . "</td>";
                                echo "<td align='center'>" . $rs['ultimoEstatus'] . "</td>";
                                echo "<td align='center'>" . $rs['Cantidad'] . "</td>";
                                echo "<td align='center'>" . $rs['cantidadExistente'] . "</td>";
                                echo "</tr>";
                            }
                            ?>
                        </table>
                    </div>
                    <h4>Mini almacenes</h4>
                    <small>Se muestran cinco registros</small>
                    <hr>
                    <div class="tasksSummary">
                        <table style="width: 100%">
                            <tr>
                                <th align="center" style="width: 10%">Clave Localidad</th>
                                <th align="center" style="width: 20%">Localidad</th>
                                <th align="center" style="width: 20%">Almacén</th>
                                <th align="center" style="width: 50%">Dirección</th>
                            </tr>
                            <?php
                            $consultaAlmacen = "SELECT cc.ClaveCentroCosto, cc.Nombre AS Localidad, a.nombre_almacen AS Almacen, CONCAT(da.Calle, 'No. Ext.',da.NoExterior ,' No. Int.', da.NoInterior, ', Col. ', da.Colonia, ', Cd. ', da.Ciudad,
                            ', ', da.Delegacion, ', ', cd.Ciudad) AS direccion FROM c_centrocosto cc INNER JOIN k_minialmacenlocalidad k ON k.ClaveCentroCosto = cc.ClaveCentroCosto
                            INNER JOIN c_almacen a ON a.id_almacen = k.IdAlmacen INNER JOIN c_domicilio_almacen da ON da.IdAlmacen = a.id_almacen
                            INNER JOIN c_ciudades cd ON cd.IdCiudad = da.Estado WHERE cc.ClaveCliente = '$id' LIMIT 5; ";
                            $resultAlmacen = $catalogo->obtenerLista($consultaAlmacen);
                            while($rs = mysql_fetch_array($resultAlmacen)){
                                echo "<tr>";
                                echo "<td align='center'>" . $rs['ClaveCentroCosto'] . "</td>";
                                echo "<td align='center'>" . $rs['ClaveCentroCosto'] . "</td>";
                                echo "<td align='center'>" . $rs['Almacen'] . "</td>";
                                echo "<td align='center'>" . $rs['direccion'] . "</td>";
                                echo "</tr>";
                            }
                            ?>
                        </table>
                    </div>
                    <?php }?>
                </div>
                <div class="span5">
                    <div class='summaryView'>
                        <h4>Facturación</h4>
                        <hr>
                        <div class="tasksSummary">
                            <table style="width: 100%">
                                <tr>
                                    <th align="center" style="width: 70%">Facturación</th>
                                    <th align="center" style="width: 30%"></th>
                                </tr>
                                <?php
                                /* Monto facturado */
                                $indicadores->imprimirFila("Monto Facturado", $indicadores->obtenerMontoFacturas(false, false, '', '', $id, '', '', false, false, 2));
                                /* Notas de credito */
                                $indicadores->imprimirFila("Notas de crédito", $indicadores->obtenerMontoFacturas(true, false, '', '', $id, '', '', false, false, false));
                                /* Monto canceladas */
                                $indicadores->imprimirFila("Canceladas", $indicadores->obtenerMontoFacturas(false, true, '', '', $id, '', '', false, false, false));
                                /* Facturas pendientes */
                                //$indicadores->imprimirConceptoCosto("Facturas Pendientes", "0");
                                /* Pendientes de cancelar */
                                $indicadores->imprimirFila("Pendientes de cancelar", $indicadores->obtenerMontoFacturas(false, false, '', '', $id, '', '', true, false, false));
                                /* No Pagadas */
                                $indicadores->imprimirFila("Facturas no pagadas", $indicadores->obtenerMontoFacturas(false, false, '', '', $id, '', '', false, true, false));
                                /* No Pagadas del año anterior */
                                $indicadores->imprimirFila("Facturas no pagadas del años anterior", $indicadores->obtenerMontoFacturas(false, false, '', '', $id, "$anio_anterior-01-01", "$anio_anterior-12-31", false, true, false));
                                /* Facturas de ventas */
                                $indicadores->imprimirFila("Facturas de ventas", $indicadores->obtenerMontoFacturas(false, false, '', '', $id, '', '', false, false, true));
                                /* Facturas no pagadas de ventas */
                                $indicadores->imprimirFila("Facturas no pagadas de ventas", $indicadores->obtenerMontoFacturas(false, false, '', '', $id, '', '', false, true, true));
                                ?>
                            </table>    
                        </div>
                        <h4><?php echo $nombre_objeto?></h4>
                        <small>Sólo se muestran cinco <?php echo $nombre_objeto?> que tienen más días de atraso</small>
                        <hr>
                        <div class="tasksSummary">
                            <table style='width: 100%'>
                                <tr>
                                    <th align='center' style="width: 10%"><?php echo $nombre_objeto?></th>
                                    <th align='center' style="width: 10%">Fecha</th>
                                    <th align='center' style="width: 5%">Color</th>
                                    <th align='center' style="width: 33%">Último estatus <?php echo $nombre_objeto?></th>
                                    <th align='center' style="width: 32%">Último estatus <?php echo $nombre_nota?></th>
                                    <th align='center' style="width: 10%">Días de atraso</th>
                                </tr>
                                <?php
                                $consultaTickets = "SELECT t.IdTicket, DATE_FORMAT(t.FechaHora,'%Y-%m-%d') AS fecha, et.Nombre AS EstadoDeTicket, ee.color, e.Nombre AS EstadoDeNota,
                                (CASE 
                                WHEN (!ISNULL(nt.IdNotaTicket) AND nt.IdEstatusAtencion = 16) THEN
                                0
                                ELSE DATEDIFF(NOW(),t.FechaCreacion) END) 
                                AS DiferenciaDias
                                FROM c_ticket t 
                                INNER JOIN c_estadoticket et ON et.IdEstadoTicket = t.EstadoDeTicket
                                LEFT JOIN c_notaticket nt ON nt.IdNotaTicket = (SELECT MAX(x.IdNotaTicket) FROM c_notaticket x WHERE x.IdTicket = t.IdTicket)
                                LEFT JOIN c_estado e ON e.IdEstado = nt.IdEstatusAtencion
                                LEFT JOIN c_escalamientoEstado ee ON ee.idEscalamiento = (SELECT x.idEscalamiento FROM c_escalamientoEstado x WHERE x.IdEstado = nt.IdEstatusAtencion AND DATEDIFF('$fecha',nt.FechaInicio) >= x.tiempoEnvio
                                ORDER BY x.tiempoEnvio DESC LIMIT 1)
                                WHERE t.EstadoDeTicket NOT IN(2,4) AND nt.IdEstatusAtencion NOT IN(16,59) AND t.ClaveCliente = '$id' ORDER BY DiferenciaDias DESC LIMIT 5;";
                                //echo $consultaTickets;
                                $resultTickets = $catalogo->obtenerLista($consultaTickets);
                                while($rs = mysql_fetch_array($resultTickets)){
                                    $color = "";
                                    if(!empty($rs['color'])){
                                        $color = "backgroud-color: $color";
                                    }
                                    echo "<tr>";
                                    echo "<td align='center'>" . $rs['IdTicket'] . "</td>";
                                    echo "<td align='center'>" . $rs['fecha'] . "</td>";
                                    echo "<td align='center' style=': $color '></td>";
                                    echo "<td align='center'>" . $rs['EstadoDeTicket'] . "</td>";
                                    echo "<td align='center'>" . $rs['EstadoDeNota'] . "</td>";
                                    echo "<td align='center'>" . $rs['DiferenciaDias'] . "</td>";
                                    echo "</tr>";
                                }
                                ?>
                            </table>
                        </div>
                        <h4>Contratos, Anexos y Servicios</h4>
                        <small>Sólo se muestran cinco registros</small>
                        <hr>
                        <div class="tasksSummary">
                            <table style='width: 100%'>
                                <tr>
                                    <th align='center' style='width: 15%'>Número de Contrato</th>
                                    <th align='center' style='width: 15%'>Clave Anexo</th>
                                    <th align='center' style='width: 15%'>Día de Corte</th>
                                    <th align='center' style='width: 55%'>Servicio</th>
                                </tr>
                                <?php 
                                $consultaCAS = "SELECT c.NoContrato, cat.ClaveAnexoTecnico, DAY(kacc.Fecha) AS DiaCorte,
                                (CASE 
                                WHEN !ISNULL(gim.IdKServicioGIM) THEN CONCAT(gim.IdKServicioGIM ,' - Global de impresión')
                                WHEN !ISNULL(gfa.IdKServicioGFA) THEN CONCAT(gfa.IdKServicioGFA, ' - Global de formato amplio')
                                WHEN !ISNULL(im.IdKServicioIM) THEN CONCAT(im.IdKServicioIM,' - Particular de impresión' )
                                WHEN !ISNULL(fa.IdKServicioFA) THEN CONCAT(fa.IdKServicioFA,' - Particular de formato amplio')
                                END) AS servicio
                                FROM c_contrato c 
                                INNER JOIN c_anexotecnico cat ON cat.NoContrato = c.NoContrato
                                INNER JOIN k_anexoclientecc kacc ON kacc.IdAnexoClienteCC = cat.ClaveAnexoTecnico
                                LEFT JOIN k_serviciogim AS gim ON gim.IdAnexoClienteCC = kacc.IdAnexoClienteCC
                                LEFT JOIN c_serviciogim AS cgim ON cgim.IdServicioGIM = gim.IdServicioGIM
                                LEFT JOIN k_serviciogfa AS gfa ON gfa.IdAnexoClienteCC = kacc.IdAnexoClienteCC
                                LEFT JOIN c_serviciogfa AS cgfa ON cgfa.IdServicioGFA = gfa.IdServicioGFA
                                LEFT JOIN k_servicioim AS im ON im.IdAnexoClienteCC = kacc.IdAnexoClienteCC
                                LEFT JOIN c_servicioim AS cim ON cim.IdServicioIM = im.IdServicioIM
                                LEFT JOIN k_serviciofa AS fa ON fa.IdAnexoClienteCC = kacc.IdAnexoClienteCC
                                LEFT JOIN c_serviciofa AS cfa ON cfa.IdServicioFA = fa.IdServicioFA 
                                WHERE c.ClaveCliente = '$id' AND c.Activo = 1 AND cat.Activo = 1 LIMIT 5;";
                                $resultCAS = $catalogo->obtenerLista($consultaCAS);
                                while($rs = mysql_fetch_array($resultCAS)){
                                    echo "<tr>";                                    
                                    echo "<td align='center'>" . $rs['NoContrato'] . "</td>";
                                    echo "<td align='center'>" . $rs['ClaveAnexoTecnico'] . "</td>";
                                    echo "<td align='center'>" . $rs['DiaCorte'] . "</td>";
                                    echo "<td align='center'>" . $rs['servicio'] . "</td>";
                                    echo "</tr>";
                                }
                                ?>
                            </table>
                        </div>
                        <h4>Localidades</h4>
                        <small>Sólo se muestran cinco registros</small>                        
                        <hr>
                        <div class="tasksSummary">
                            <table style='width: 100%'>
                                <tr>
                                    <th align='center' style='width: 15%'>Clave</th>
                                    <th align='center' style='width: 85%'>Nombre</th>
                                </tr>
                                <?php
                                $consultaLocalidad = "SELECT ClaveCentroCosto, Nombre FROM c_centrocosto WHERE ClaveCliente = '$id' AND Activo = 1 LIMIT 5;";
                                $resultLocalidad = $catalogo->obtenerLista($consultaLocalidad);
                                while($rs = mysql_fetch_array($resultLocalidad)){
                                    echo "<tr>";
                                    echo "<td align='center'>" . $rs['ClaveCentroCosto'] . "</td>";
                                    echo "<td align='center'>" . $rs['Nombre'] . "</td>";
                                    echo "</tr>";
                                }
                                ?>
                            </table>                            
                        </div>
                        <h4>Contactos</h4>
                        <small>Sólo se muestran cinco registros</small>                        
                        <hr>
                        <div class="tasksSummary">
                            <table style='width: 100%'>
                                <tr>
                                    <th align='center' style='width: 30%'>Localidad</th>
                                    <th align='center' style='width: 10%'>Clave Contacto</th>
                                    <th align='center' style='width: 30%'>Nombre</th>
                                    <th align='center' style='width: 30%'>Tipo de Contacto</th>
                                </tr>
                                <?php
                                $consultaContactos = "SELECT c.Nombre, ctt.ClaveEspecialContacto, tc.Nombre AS TipoContacto, ctt.Nombre AS Contacto
                                FROM c_centrocosto c
                                LEFT JOIN c_centrocosto cc ON cc.ClaveCliente = c.ClaveCliente
                                LEFT JOIN c_contacto AS ctt ON ctt.ClaveEspecialContacto = cc.ClaveCentroCosto OR ctt.ClaveEspecialContacto = cc.ClaveCliente
                                LEFT JOIN c_tipocontacto AS tc ON tc.IdTipoContacto = ctt.IdTipoContacto
                                WHERE c.ClaveCliente = '$id' AND c.Activo = 1 GROUP BY c.ClaveCentroCosto LIMIT 5";
                                $resultContactos= $catalogo->obtenerLista($consultaContactos);
                                while($rs = mysql_fetch_array($resultContactos)){
                                    echo "<tr>";
                                    echo "<td align='center'>" . $rs['Nombre'] . "</td>";
                                    echo "<td align='center'>" . $rs['ClaveEspecialContacto'] . "</td>";
                                    echo "<td align='center'>" . $rs['Contacto'] . "</td>";
                                    echo "<td align='center'>" . $rs['TipoContacto'] . "</td>";
                                    echo "</tr>";
                                }
                                ?>
                            </table>                            
                        </div>
                    </div>
                </div>
            </div>
            <div class='project-summary'>
                <a href='#' onclick="cambiarContenidos('<?php echo $same_page.$params?>','');">Resumen Cliente</a>  
                <?php
                $permisos_grid3 = new PermisosSubMenu();
                $editarCliente = "ventas/cliente_nuevo.php";
                $grid_tickets = "mesa/lista_ticket.php";
                $miscelaneo = "contrato/alta_cliente_lanzapopup.php";
                $movimientosAlm = "almacen/movimientos_almacen_filtros.php";
                $mttoPreventivo = "ventas/lista_mttos.php";
                $refacciones = "almacen/lista_refaccionesSolicitadas.php";
                $toner = "almacen/toner_solicitado.php";
                /**/
                $permisos_grid3->getPermisosSubmenu($_SESSION['idUsuario'], "ventas/mis_clientes.php");
                if($permisos_grid3->getModificar()){
                    echo "<a href='#' onclick='cambiarContenidos(\"$editarCliente$params&regresar=$same_page$params\",\"\");'>Editar Cliente</a>";
                }
                $permisos_grid3->getPermisosSubmenu($_SESSION['idUsuario'], $grid_tickets);
                if($permisos_grid3->getConsulta()){
                    echo "<a href='#' onclick='cambiarContenidos(\"$grid_tickets?cliente=$id&mostrar=true&regresar=$same_page$params\",\"\");'>Grid $nombre_objeto</a>";
                }
                $permisos_grid3->getPermisosSubmenu($_SESSION['idUsuario'], $miscelaneo);
                if($permisos_grid3->getConsulta()){
                    echo "<a href='#' onclick='cambiarContenidos(\"$miscelaneo?ClaveCliente=$id&regresar=$same_page$params\",\"\");'>Anexo Técnico</a>";
                }
                $permisos_grid3->getPermisosSubmenu($_SESSION['idUsuario'], $movimientosAlm);
                if($permisos_grid3->getConsulta()){
                    echo "<a href='#' onclick='cambiarContenidos(\"$movimientosAlm?cliente=$id&regresar=$same_page$params\",\"\");'>Movimientos de equipo</a>";
                }       
                $permisos_grid3->getPermisosSubmenu($_SESSION['idUsuario'], $mttoPreventivo);
                if($permisos_grid3->getConsulta()){
                    echo "<a href='#' onclick='cambiarContenidos(\"$mttoPreventivo?cliente=$id&regresar=$same_page$params\",\"\");'>Mantenimiento preventivo</a>";
                }  
                $permisos_grid3->getPermisosSubmenu($_SESSION['idUsuario'], $refacciones);
                if($permisos_grid3->getConsulta()){
                    echo "<a href='#' onclick='cambiarContenidos(\"$refacciones?cliente=$id&mostrar=true&regresar=$same_page$params\",\"\");'>Solicitudes refacciones</a>";
                }       
                $permisos_grid3->getPermisosSubmenu($_SESSION['idUsuario'], $toner);
                if($permisos_grid3->getConsulta()){
                    echo "<a href='#' onclick='cambiarContenidos(\"$toner?cliente=$id&regresar=$same_page$params\",\"\");'>Solicitudes tóner</a>";
                }  
                ?>
            </div>
        </div>
    </body>
</html>