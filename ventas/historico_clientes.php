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
        <div class="row">
            <h3><?php echo $nombre?></h3>
            <div class="col-md-8 col-12 order-md-2">
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
            <div class="col-md-8 col-12 order-md-1">
                <div class="container">
                    <div class="row">
                        <div class="col-md-6 col-12">
                            <h4>Resumen</h4>
                            <hr>
                            <?php
                                $contador = 0;
                                foreach($array AS $clave => $valor){
                                    if($contador == 0){//Abrimos el div contenedor
                                        echo "<div class='summary__card'>";
                                    }
                                    echo "<div class='summary__card-item'>
                                            <b>" . $clave . "</b>
                                            <p>" . $valor . "</p>
                                        </div>";                            
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
                                        echo "<div></div><div></div>";                                
                                        if($x == 3){
                                            echo "</div>";
                                        }
                                    }                            
                                }
                            ?>
                            <div class="summary__info">
                                <p class="summary__info-field">Nombre del Cliente</p>
                                <p class="summary__info-value"><?php echo $nombre?></p>
                                <p class="summary__info-field">Total de <?php echo $nombre_objeto?></p>
                                <p class="summary__info-value"><?php echo $total?></p>
                            </div>
                            <small>Creado el <?php echo $creado?></small><br>
                            <small>Modificado por última vez el <?php echo $modificado?></small>
                        </div>
                    <?php if($parametros->getValor()): ?>
                            <div class="col-md-6 col-12">
                                <h4>Equipos</h4>
                                <hr>
                                <div class="table-responsive">
                                    <table class="table">
                                        <thead class="thead-dark">
                                            <tr>
                                                <th>Equipo</th>
                                                <th>Cantidad</th>
                                                <th>Costo (US $)</th>
                                            </tr>
                                        </thead>
                                        <?php
                                            $indicador->obtenerEquiposArrendamiento($emisor, $ejecutivo, $id);
                                            $indicador->imprimirTablaTipoSolicitud(3, "En garantía", $emisor, $ejecutivo, $id);
                                        ?>
                                    </table>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6 col-12">
                                <h4>Movimientos de Equipo</h4>
                                <small>Se muestran los cinco registros más recientes</small>
                                <hr>
                                <div class="table-responsive">
                                    <table class="table">
                                        <thead class="thead-dark">
                                            <tr>
                                                <th>Tipo</th>
                                                <th>Fecha</th>
                                                <th>Almacén</th>
                                                <th>Movimiento</th>
                                                <th>Modelo</th>
                                                <th>Cant</th>
                                                <th>No Serie</th>
                                                <th><?php echo $nombre_objeto?></th>
                                                <th>Destino</th>
                                            </tr>
                                        </thead>
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
                                                echo "<tr>
                                                        <td>" . $rs['TipoComponente'] . "</td>
                                                        <td>" . $rs['Fecha'] . "</td>
                                                        <td>" . $rs['almacen'] . "</td>
                                                        <td>" . $rs['ES'] . "</td>
                                                        <td>" . $rs['Modelo'] . "</td>
                                                        <td>" . $rs['CantidadMovimiento'] . "</td>
                                                        <td>" . $rs['NoSerieEquipo'] . "</td>
                                                        <td>" . $rs['IdTicket'] . "</td>
                                                        <td>" . $rs['Municipio'] . ", "  . $rs['Estado'] . "</td>
                                                    <tr>";
                                            }
                                        ?>
                                    </table>
                                </div>
                            </div>
                            <div class="col-md-6 col-12">
                                <h4>Mantenimiento Preventivo</h4>
                                <small>Se muestran los cinco registros más recientes</small>
                                <hr>
                                <div class="table-responsiver">
                                    <table class="table">
                                        <tr>
                                            <th>No. Serie</th>
                                            <th>Fecha</th>
                                            <th>Cliente</th>
                                            <th>Localidad</th>
                                            <th>Estatus</th>
                                        </tr>
                                        <?php
                                            $consultaMtto = "SELECT m.NoSerie AS NoSerie,m.Fecha AS Fecha,if(m.Estatus=0,'En proceso','') AS Estatus,
                                            cc.Nombre AS CentroCosto,c.NombreRazonSocial AS Cliente FROM c_mantenimiento m INNER JOIN c_centrocosto cc ON cc.ClaveCentroCosto = m.ClaveCentroCosto
                                            INNER JOIN c_cliente c ON c.ClaveCliente = cc.ClaveCliente WHERE m.Estatus=0 AND c.ClaveCliente='$id' ORDER BY Fecha DESC LIMIT 5;";
                                            $resultMtto = $catalogo->obtenerLista($consultaMtto);
                                            while($rs = mysql_fetch_array($result)){
                                                echo "<tr>
                                                        <td>" . $rs['NoSerie'] . "</td>
                                                        <td>" . $rs['Fecha'] . "</td>
                                                        <td>" . $rs['Cliente'] . "</td>
                                                        <td>" . $rs['CentroCosto'] . "</td>
                                                        <td>" . $rs['Estatus'] . "</td>
                                                    </tr>";
                                            }
                                        ?>
                                    </table>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6 col-12">
                                <h4>Solicitudes de refacciones</h4>
                                <small>Se muestran los cinco registros más recientes</small>
                                <hr>
                                <div class="table-responsive">
                                    <table class="table">
                                        <thead class="thead-dark">
                                            <tr>
                                                <th><?php echo $nombre_objeto?></th>
                                                <th>Fecha</th>
                                                <th>No. Serie</th>
                                                <th>Falla</th>
                                                <th>Refacción</th>
                                                <th>Ultimo Estado</th>
                                            </tr>
                                        </thead>
                                        <?php
                                            $consultaRefacciones = "SELECT t.IdTicket,DATE(t.FechaHora) AS FechaHora,
                                            (SELECT CASE WHEN e2.Suministro = 1 THEN (SELECT group_concat(ClaveEspEquipo separator ',') FROM `c_pedido` WHERE IdTicket = t.IdTicket) ELSE t.NoSerieEquipo END) AS NumSerie, 
                                            t.DescripcionReporte,CONCAT ('(',nr.Cantidad,') ',c.Modelo,' / ',c.NoParte,' / ',c.Descripcion) AS refaccion, e.Nombre AS ultimoEstatus
                                            FROM k_nota_refaccion nr LEFT JOIN c_notaticket nt ON  nt.IdNotaTicket=nr.IdNotaTicket LEFT JOIN c_ticket t ON t.IdTicket=nt.IdTicket 
                                            LEFT JOIN c_estado AS e2 ON e2.IdEstado = t.AreaAtencion LEFT JOIN c_componente c ON nr.NoParteComponente=c.NoParte                                  
                                            LEFT JOIN c_notaticket AS lastnt ON lastnt.IdNotaTicket = (SELECT MAX(IdNotaTicket) FROM c_notaticket WHERE IdTicket = t.IdTicket)
                                            LEFT JOIN c_estado e ON lastnt.IdEstatusAtencion = e.IdEstado LEFT JOIN c_estado AS e3 ON lastnt.IdEstatusAtencion = e3.IdEstado                             
                                            WHERE (nt.IdEstatusAtencion=19 OR nt.IdEstatusAtencion=20 OR nt.IdEstatusAtencion=24) AND t.ClaveCliente = '$id' AND  nr.Cantidad<>0 AND c.IdTipoComponente=1 
                                            ORDER BY FechaHora DESC LIMIT 5";
                                            $resultRefacciones = $catalogo->obtenerLista($consultaRefacciones);
                                            while($rs = mysql_fetch_array($resultRefacciones)){
                                                echo "<tr>
                                                        <td>" . $rs['IdTicket'] . "</td>
                                                        <td>" . $rs['FechaHora'] . "</td>
                                                        <td>" . $rs['NumSerie'] . "</td>
                                                        <td>" . $rs['DescripcionReporte'] . "</td>
                                                        <td>" . $rs['refaccion'] . "</td>
                                                        <td>" . $rs['ultimoEstatus'] . "</td>
                                                    </tr>";
                                            }
                                        ?>
                                    </table>
                                </div>
                            </div>
                            <div class="col-md-6 col-12">
                                <h4>Solicitudes de tóner</h4>
                                <small>Se muestran los cinco registros más recientes</small>
                                <hr>
                                <div class="table-responsive">
                                    <table class="table">
                                        <thead class="thead-dark">
                                            <tr>
                                                <th><?php echo $nombre_objeto?></th>
                                                <th>Fecha</th>
                                                <th>Toner</th>
                                                <th>Último estado</th>
                                                <th>Cantidad</th>
                                                <th>Existencia</th>
                                            </tr>
                                        </thead>
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
                                                echo "<tr>
                                                        <td>" . $rs['IdTicket'] . "</td>
                                                        <td>" . $rs['FechaHora'] . "</td>
                                                        <td>" . $rs['refaccion'] . "</td>
                                                        <td>" . $rs['ultimoEstatus'] . "</td>
                                                        <td>" . $rs['Cantidad'] . "</td>
                                                        <td>" . $rs['cantidadExistente'] . "</td>
                                                    </tr>";
                                            }
                                        ?>
                                    </table>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6 col-12">
                                <h4>Mini almacenes</h4>
                                <small>Se muestran cinco registros</small>
                                <hr>
                                <div class="table-responsive">
                                    <table class="table">
                                        <thead class="thead-dark">
                                            <tr>
                                                <th>Clave Localidad</th>
                                                <th>Localidad</th>
                                                <th>Almacen</th>
                                                <th>Dirección</th>
                                            </tr>
                                        </thead>
                                        <?php
                                            $consultaAlmacen = "SELECT cc.ClaveCentroCosto, cc.Nombre AS Localidad, a.nombre_almacen AS Almacen, CONCAT(da.Calle, 'No. Ext.',da.NoExterior ,' No. Int.', da.NoInterior, ', Col. ', da.Colonia, ', Cd. ', da.Ciudad,
                                            ', ', da.Delegacion, ', ', cd.Ciudad) AS direccion FROM c_centrocosto cc INNER JOIN k_minialmacenlocalidad k ON k.ClaveCentroCosto = cc.ClaveCentroCosto
                                            INNER JOIN c_almacen a ON a.id_almacen = k.IdAlmacen INNER JOIN c_domicilio_almacen da ON da.IdAlmacen = a.id_almacen
                                            INNER JOIN c_ciudades cd ON cd.IdCiudad = da.Estado WHERE cc.ClaveCliente = '$id' LIMIT 5; ";
                                            $resultAlmacen = $catalogo->obtenerLista($consultaAlmacen);
                                            while($rs = mysql_fetch_array($resultAlmacen)){
                                                echo "<tr>
                                                        <td>" . $rs['ClaveCentroCosto'] . "</td>
                                                        <td>" . $rs['ClaveCentroCosto'] . "</td>
                                                        <td>" . $rs['Almacen'] . "</td>
                                                        <td>" . $rs['direccion'] . "</td>
                                                    </tr>";
                                            }
                                        ?>
                                    </table>
                                </div>
                            </div>
                    <?php endif; ?>
                        <div class="col-md-6 col-12">
                            <h4>Facturación</h4>
                            <hr>
                            <div class="table-responsive">
                                <table class="table">
                                    <thead class="thead-dark">
                                        <tr class="table__line">
                                            <th>Facturacion</th>
                                            <th></th>
                                        </tr>
                                    </thead>
                                    <?php
                                        $indicadores->imprimirFila("Monto Facturado", $indicadores->obtenerMontoFacturas(false, false, '', '', $id, '', '', false, false, 2));
                                        /* Notas de credito */
                                        $indicadores->imprimirFila("Notas de crédito", $indicadores->obtenerMontoFacturas(true, false, '', '', $id, '', '', false, false, false));
                                        /* Monto canceladas */
                                        $indicadores->imprimirFila("Canceladas", $indicadores->obtenerMontoFacturas(false, true, '', '', $id, '', '', false, false, false));
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
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 col-12">
                            <h4><?php echo $nombre_objeto?></h4>
                            <small>Sólo se muestran cinco <?php echo $nombre_objeto?> que tienen más días de atraso</small>
                            <hr>
                            <div class="table-responsive">
                                <table class="table">
                                    <thead class="thead-dark">
                                        <tr>
                                            <th><?php echo $nombre_objeto?></th>
                                            <th>Fecha</th>
                                            <th>Color</th>
                                            <th>Último estatus <?php echo $nombre_objeto?></th>
                                            <th>Último estatus <?php echo $nombre_nota?></th>
                                            <th>Días de atraso</th>
                                        </tr>
                                    </thead>
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
                                            echo "<tr>
                                                    <td>" . $rs['IdTicket'] . "</td>
                                                    <td>" . $rs['fecha'] . "</td>
                                                    <td style='$color'></td>
                                                    <td>" . $rs['EstadoDeTicket'] . "</td>
                                                    <td>" . $rs['EstadoDeNota'] . "</td>
                                                    <td>" . $rs['DiferenciaDias'] . "</td>
                                                </tr>";
                                        }
                                    ?>
                                </table>
                            </div>
                        </div>
                        <div class="col-md-6 col-12">
                            <h4>Contratos, Anexos y Servicios</h4>
                            <small>Sólo se muestran cinco registros</small>
                            <hr>
                            <div class="table-responsive">
                                <table class="table">
                                    <thead class="thead-dark">
                                        <tr>
                                            <th>Número de Contrato</th>
                                            <th>Clave Anexo</th>
                                            <th>Día de Corte</th>
                                            <th>Servicio</th>
                                        </tr>
                                    </thead>
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
                                            echo "<tr>
                                                    <td>" . $rs['NoContrato'] . "</td>
                                                    <td>" . $rs['ClaveAnexoTecnico'] . "</td>
                                                    <td>" . $rs['DiaCorte'] . "</td>
                                                    <td>" . $rs['servicio'] . "</td>
                                                </tr>";
                                        }
                                    ?>
                                </table>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 col-12">
                             <h4>Localidades</h4>
                            <small>Sólo se muestran cinco registros</small>                        
                            <hr>
                            <div class="table-responsive">
                                <table class="table">
                                    <thead class="thead-dark">
                                        <tr>
                                            <th>Clave</th>
                                            <th>Nombre</th>
                                        </tr>
                                    </thead>
                                    <?php
                                        $consultaLocalidad = "SELECT ClaveCentroCosto, Nombre FROM c_centrocosto WHERE ClaveCliente = '$id' AND Activo = 1 LIMIT 5;";
                                        $resultLocalidad = $catalogo->obtenerLista($consultaLocalidad);
                                        while($rs = mysql_fetch_array($resultLocalidad)){
                                            echo "<tr>
                                                    <td>" . $rs['ClaveCentroCosto'] . "</td>
                                                    <td>" . $rs['Nombre'] . "</td>
                                                </tr>";
                                        }
                                    ?>
                                </table>
                            </div>
                        </div>
                        <div class="col-md-6 col-12">
                            <h4>Contactos</h4>
                            <small>Sólo se muestran cinco registros</small>                        
                            <hr>
                            <div class="table-responsive">
                                <table class="table">
                                    <thead class="thead-dark">
                                        <tr>
                                            <th>Localidad</th>
                                            <th>Clave Contacto</th>
                                            <th>Nombre</th>
                                            <th>Tipo de Contacto</th>
                                        </tr>
                                    </thead>
                                    <?php
                                        $consultaContactos = "SELECT c.Nombre, ctt.ClaveEspecialContacto, tc.Nombre AS TipoContacto, ctt.Nombre AS Contacto
                                        FROM c_centrocosto c
                                        LEFT JOIN c_centrocosto cc ON cc.ClaveCliente = c.ClaveCliente
                                        LEFT JOIN c_contacto AS ctt ON ctt.ClaveEspecialContacto = cc.ClaveCentroCosto OR ctt.ClaveEspecialContacto = cc.ClaveCliente
                                        LEFT JOIN c_tipocontacto AS tc ON tc.IdTipoContacto = ctt.IdTipoContacto
                                        WHERE c.ClaveCliente = '$id' AND c.Activo = 1 GROUP BY c.ClaveCentroCosto LIMIT 5";
                                        $resultContactos= $catalogo->obtenerLista($consultaContactos);
                                        while($rs = mysql_fetch_array($resultContactos)){
                                            echo "<tr>
                                                    <td>" . $rs['Nombre'] . "</td>
                                                    <td>" . $rs['ClaveEspecialContacto'] . "</td>
                                                    <td>" . $rs['Contacto'] . "</td>
                                                    <td>" . $rs['TipoContacto'] . "</td>
                                                </tr>";
                                        }
                                    ?>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </body>
</html>