<?php

session_start();

if (!isset($_SESSION['user']) || $_SESSION['user'] == "") {
    header("Location: ../index.php");
}

include_once("../WEB-INF/Classes/Catalogo.class.php");
include_once("../WEB-INF/Classes/ResurtidoToner.class.php");

if(isset($_GET['idTicket']) && $_GET['idTicket'] != ""){
$catalogo = new Catalogo();
$verificarTicket = "SELECT Resurtido, TipoReporte FROM c_ticket t WHERE t.IdTicket = ".$_GET['idTicket'];
$resultVerTicket = $catalogo->obtenerLista($verificarTicket);
if(mysql_num_rows($resultVerTicket) > 0){
if($rsVerTicket = mysql_fetch_array($resultVerTicket)){
    $ticketResurtido1 = (int)$rsVerTicket['Resurtido'];
    $tipoReporte12 = $rsVerTicket['TipoReporte'];
}
if($ticketResurtido1 == 1){
    $resurtido = new ResurtidoToner();
    $idTicket = $_GET['idTicket'];
    $resurtido->setIdTicket($idTicket);
    $query = $resurtido->getTabla();
    $primeraFila1 = "";
    $primeraFila2 = "";
    $tabla = "";
    $almacen = ""; 
    $idAlmacen = "";
    $fecha = "";
    $cliente = "";
    $localidad = "";
    $claveLocalidad = "";
    $val = false;
    $claveCliente = "";
    $rowspan = 1;
    $rowspan2 = 0;
    $contestada = 0;
    $filas = "";
    $tablaComponentes = "";
    $arrayNoTicketComponente = array();
    $arrayComponenteModelo = array();
    $arrayCantidadSolicitadaComponente = array();
    
    $querySurtidosCompatibles = "SELECT c.Modelo, nr.Cantidad, '0' AS Solicitado, ag.cantidad_existencia, c.PrecioDolares, nr.NoParteComponente, rt.IdAlmacen FROM k_nota_refaccion nr
        LEFT JOIN c_notaticket nt2 ON nt2.IdNotaTicket = nr.IdNotaTicket
        INNER JOIN c_componente c ON c.NoParte = nr.NoParteComponente
        LEFT JOIN k_almacencomponente AS ag ON ag.NoParte = nr.NoParteComponente AND ag.id_almacen = 6
        LEFT JOIN k_resurtidotoner AS rt ON rt.IdTicket = nt2.IdTicket
        WHERE nr.IdNotaTicket IN (SELECT nt.IdNotaTicket FROM c_notaticket nt WHERE nt.IdTicket = $idTicket) 
        AND nt2.IdEstatusAtencion = 66 AND nr.NoParteComponente NOT IN(SELECT rt.NoComponenteToner FROM k_resurtidotoner rt WHERE rt.IdTicket = $idTicket)
        GROUP BY NoParteComponente";
    $queryasdasd = $catalogo->obtenerLista($querySurtidosCompatibles);
    while($rs = mysql_fetch_array($queryasdasd)){
        $compatibleNuevo = true;
        $rowspan2++;
        $tablaComponentes.= "<tr>";
        $tablaComponentes.= "<td align='center'>".$rs['Modelo']."</td>";
        $tablaComponentes.= "<td class='borde centrado'>" . $rs['PrecioDolares'] . "</td>";
        $tablaComponentes.= "<td class='borde centrado'>0</td>";
        $tablaComponentes.= "<td class='borde centrado'>".$rs['Cantidad']."</td>";
        $tablaComponentes.= "<td class='borde centrado'>".$rs['Cantidad']."</td>";
        $tablaComponentes.= "<td class='borde centrado'>0</td>";
        $tablaComponentes.= "<td class='borde centrado'>0</td>";
        $tablaComponentes.= "<tr>";
    }
    
    
    while ($resultSet = mysql_fetch_array($query)) {
        
        if($primeraFila1 == ""){
            $primeraFila1.= "<td class='borde centrado'>" . $resultSet['ModeloT'] . "</td>";
            $primeraFila1.= "<td class='borde centrado'>" . $resultSet['precio'] . "</td>";
            $primeraFila1.= "<td class='borde centrado'>" . $resultSet['CantidadSolicitada'] . "</td>";
            if((int)$resultSet['mail'] == 1){
                if(isset($resultSet['Cantidad']) && $resultSet['Cantidad'] != ""){
                    $primeraFila1.= "<td class='borde centrado'>" . $resultSet['Cantidad'] . "</td>";
                }else{
                    $primeraFila1.= "<td class='borde centrado'>" . 0 . "</td>";
                }      
                if(isset($resultSet['existencia'])){
                    $primeraFila2.= "<td class='borde centrado'>" . $resultSet['existencia'] . "</td>";
                }else{
                    $primeraFila2.= "<td class='borde centrado'>N/A</td>";
                    $nota = true;
                }
                if(isset($resultSet['minimo'])){
                    $primeraFila2.= "<td class='borde centrado'>" . $resultSet['minimo'] . "</td>";
                }else{
                    $primeraFila2.= "<td class='borde centrado'>N/A</td>";
                    $nota = true;
                }
                if(isset($resultSet['maximo'])){
                    $primeraFila2.= "<td class='borde centrado'>" . $resultSet['maximo'] . "</td>";
                }else{
                    $primeraFila2.= "<td class='borde centrado'>N/A</td>";
                    $nota = true;
                }
            }else{
                if(isset($resultSet['existenciaA'])){
                    $primeraFila2.= "<td class='borde centrado'>" . $resultSet['existenciaA'] . "</td>";
                }else{
                    $primeraFila2.= "<td class='borde centrado'>N/A</td>";
                    $nota = true;
                }
                if(isset($resultSet['minimoA'])){
                    $primeraFila2.= "<td class='borde centrado'>" . $resultSet['minimoA'] . "</td>";
                }else{
                    $primeraFila2.= "<td class='borde centrado'>N/A</td>";
                    $nota = true;
                }
                if(isset($resultSet['maximoA'])){
                    $primeraFila2.= "<td class='borde centrado'>" . $resultSet['maximoA'] . "</td>";
                }else{
                    $primeraFila2.= "<td class='borde centrado'>N/A</td>";
                    $nota = true;
                }
            }
            $rowspan = 0;            
            $idTicketAnteriorComponente = $resurtido->ticketAnteriorResurtidoPorComponenteYAlmacen($resultSet['NoComponenteToner'],$idTicket, $resultSet['IdAlmacen']);
            $arrayNoTicketComponente[''.$resultSet['NoComponenteToner']] = $idTicketAnteriorComponente;
            $arrayComponenteModelo[''.$resultSet['NoComponenteToner']] = $resultSet['ModeloT'];
            $arrayCantidadSolicitadaComponente[''.$resultSet['NoComponenteToner']] = (int)$resultSet['CantidadSolicitada'];
        }else{
            $filas.= "<tr>";
            $filas.= "<td class='borde centrado'>" . $resultSet['ModeloT'] . "</td>";
            $filas.= "<td class='borde centrado'>" . $resultSet['precio'] . "</td>";
            $filas.= "<td class='borde centrado'>" . $resultSet['CantidadSolicitada'] . "</td>";
            if((int)$resultSet['mail'] == 1){
                if(isset($resultSet['Cantidad']) && $resultSet['Cantidad'] != ""){
                    $filas.= "<td class='borde centrado'>" . $resultSet['Cantidad'] . "</td>";
                }else{
                    $filas.= "<td class='borde centrado'>" . 0 . "</td>";
                }
                if(isset($resultSet['existencia'])){
                    $filas.= "<td class='borde centrado'>" . $resultSet['existencia'] . "</td>";
                }else{
                    $filas.= "<td class='borde centrado'>N/A</td>";
                    $nota = true;
                }
                if(isset($resultSet['minimo'])){
                    $filas.= "<td class='borde centrado'>" . $resultSet['minimo'] . "</td>";
                }else{
                    $filas.= "<td class='borde centrado'>N/A</td>";
                    $nota = true;
                }
                if(isset($resultSet['maximo'])){
                    $filas.= "<td class='borde centrado'>" . $resultSet['maximo'] . "</td>";
                }else{
                    $filas.= "<td class='borde centrado'>N/A</td>";
                    $nota = true;
                }
            }else{
                if(isset($resultSet['existenciaA'])){
                    $filas.= "<td class='borde centrado'>" . $resultSet['existenciaA'] . "</td>";
                }else{
                    $filas.= "<td class='borde centrado'>N/A</td>";
                    $nota = true;
                }
                if(isset($resultSet['minimoA'])){
                    $filas.= "<td class='borde centrado'>" . $resultSet['minimoA'] . "</td>";
                }else{
                    $filas.= "<td class='borde centrado'>N/A</td>";
                    $nota = true;
                }
                if(isset($resultSet['maximoA'])){
                    $filas.= "<td class='borde centrado'>" . $resultSet['maximoA'] . "</td>";
                }else{
                    $filas.= "<td class='borde centrado'>N/A</td>";
                    $nota = true;
                }
            }
            $idTicketAnteriorComponente = $resurtido->ticketAnteriorResurtidoPorComponenteYAlmacen($resultSet['NoComponenteToner'],$idTicket, $resultSet['IdAlmacen']);
            $arrayNoTicketComponente[''.$resultSet['NoComponenteToner']] = $idTicketAnteriorComponente;
            $arrayComponenteModelo[''.$resultSet['NoComponenteToner']] = $resultSet['ModeloT'];
            $arrayCantidadSolicitadaComponente[''.$resultSet['NoComponenteToner']] = (int)$resultSet['CantidadSolicitada'];
            $filas.= "</tr>";
        }
        $rowspan++;
        $fecha = $resultSet['Fecha'];
        $almacen = $resultSet['almacen'];
        $idAlmacen = $resultSet['IdAlmacen'];
        $cliente = $resultSet['cliente'];
        $localidad = $resultSet['localidad'];
        $claveLocalidad = $resultSet['ClaveCentroCosto'];
        $val = true;
        $claveCliente = $resultSet['ClaveCliente'];
        $contestada = (int)$resultSet['mail'];
    }
    if ($val == true){
        $rowspan += $rowspan2;
        $tabla.= "<tr>";
        $tabla.= "<td class='borde centrado' rowspan='$rowspan'>" . $idTicket . "</td>";
        $tabla.= "<td class='borde centrado' rowspan='$rowspan'>" . $fecha . "</td>";

        $tabla.= $primeraFila1;
        if($contestada != 1){
            $tabla.= "<td class='borde centrado' rowspan='$rowspan'>Sin autorizar</td>";
        }
        $tabla.=$primeraFila2;
        
        $tabla.= "</tr>";       
        $tabla.= $filas;
        $tabla.= $tablaComponentes;
    }
    if ($val == false) {
        $tabla.= "<tr>";
        $tabla.= "<td class='borde centrado' colspan='13'>No se encontraron datos que coincidieran con su búsqueda</td>";
        $tabla.= "</tr>";
    }
    
    $consultaTickets = "SELECT lt.ClvEsp_Equipo AS NoSerie, nr.NoParteComponente AS NoParte, t.FechaHora AS Fecha,
        c.Modelo AS Modelo, c.Descripcion AS Descripcion, nr.Cantidad AS Cantidad, t.IdTicket AS NoTicket,
        a.nombre_almacen AS Almacen, t.NombreCliente AS Cliente, t.NombreCentroCosto AS Localidad,
        lt.ContadorBN AS ContadorBN, lt.ContadorCL AS ContadorCL, lt.ModeloEquipo AS Equipo,
        (lt.ContadorBN - lt2.ContadorBN) AS Impresiones, c.Rendimiento AS Rendimiento,
        lt2.ContadorBN AS ContadorBNAnterior, lt2.ContadorCL AS ContadorCLAnterior, lt2.Fecha AS FechaAnterior
        FROM c_ticket t 
        INNER JOIN c_notaticket AS nt ON nt.IdTicket = t.IdTicket
        LEFT JOIN c_lecturasticket AS lt ON fk_idticket = t.IdTicket
        LEFT JOIN k_nota_refaccion AS nr ON nt.IdNotaTicket = nr.IdNotaTicket
        LEFT JOIN c_almacen AS a ON a.id_almacen = nr.IdAlmacen
        LEFT JOIN c_componente AS c ON c.NoParte = nr.NoParteComponente
        LEFT JOIN c_ticket AS ta ON ta.IdTicket = (SELECT MAX(t2.IdTicket) FROM c_ticket t2 
            WHERE t2.IdTicket < $idTicket AND t2.Resurtido = 1 AND t2.ClaveCliente = '$claveCliente' AND t2.EstadoDeTicket = 2)
        LEFT JOIN c_mailpedidotoner AS mpt ON mpt.IdTicket = ta.IdTicket
        LEFT JOIN c_lecturasticket AS lt2 ON lt2.id_lecturaticket = 
            (SELECT MAX(lta.id_lecturaticket) FROM c_lecturasticket lta 
            LEFT JOIN c_ticket AS ta ON lta.fk_idticket = ta.IdTicket
            INNER JOIN c_notaticket nt3 ON nt3.IdTicket=ta.IdTicket 
            INNER JOIN k_nota_refaccion nr3 ON nt3.IdNotaTicket=nr3.IdNotaTicket 
            INNER JOIN c_componente c2 ON c2.NoParte=nr3.NoParteComponente
            WHERE lta.ClvEsp_Equipo = lt.ClvEsp_Equipo AND ta.Resurtido = 0 AND lta.id_lecturaticket <  lt.id_lecturaticket AND c2.IdColor=c.IdColor)
        WHERE t.TipoReporte = 15 AND t.Resurtido = 0 AND t.FechaHora < '$fecha' AND a.id_almacen = ".$idAlmacen
        ." AND c.NoParte IN (SELECT c2.NoParte FROM c_componente c2 INNER JOIN k_resurtidotoner AS rt2 ON rt2.NoComponenteToner = c2.NoParte WHERE rt2.IdTicket = $idTicket)
        AND t.FechaHora > mpt.FechaUltimaModificacion 
        GROUP BY t.IdTicket ORDER BY nr.NoParteComponente,t.IdTicket";
    $resultTickets = $catalogo->obtenerLista($consultaTickets);
    //echo $consultaTickets;
    $logo = "";
    //Query para obtener el logo
    $consultaLogo = "SELECT ImagenPHP FROM c_datosfacturacionempresa dfe "
            . "INNER JOIN c_cliente AS c ON c.IdDatosFacturacionEmpresa = dfe.IdDatosFacturacionEmpresa"
            . " WHERE c.ClaveCliente = '$claveCliente'";
    $resultLogo = $catalogo->obtenerLista($consultaLogo);
    if(mysql_num_rows($resultLogo) > 0){
        if($rsLogo = mysql_fetch_array($resultLogo)){
            $logo = "../".$rsLogo['ImagenPHP'];
        }
    }

    if($logo == ""){
        $parametroGlobal = new ParametroGlobal();
        $parametroGlobal->getRegistroById(5);
        $logo = "../".$parametroGlobal->getValor();
    }
?>
<!DOCTYPE HTML>
<html lang="es">
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
        <style>
            body{font-family: Arial; font-size: 15px;}
            .titulo{font-weight: bold; font-size: 18px;}
            table{
                border-collapse:collapse;
            }            
            .borde{border: 1px solid #000;}
            .mediano{width: 30%;}
            .gigantes{width: 600px;}
            /*.pagebreak { page-break-after: always; page-break-before: always; }*/
            .espacio{min-height: 100px;}
            /*.obscuro{background-color: #404040; color: white; text-align: center;  font-style: italic; -webkit-print-color-adjust:exact;}
            .gris{background-color: #C0C0C0; font-weight: bold;  -webkit-print-color-adjust:exact;}
            .color{background-color: #3333CC; color: white; -webkit-print-color-adjust:exact; }
            .bn{background-color: #000; color: white; }*/
            .obscuro{color: black; text-align: center;  font-style: italic;}
            .gris{font-weight: bold; }
            .color{color: black;}
            .bn{color: black; }

            .pie{font-size: 10px; color: #800000;}
            .centrado {text-align: center;}
            .completeSize{width: 50%;}
            .tablaCompleta{width: 90%;}
        </style>
        <title>Reporte</title>
        <link rel="shortcut icon" href="../resources/images/logos/ra4.png" type="image/x-icon"/>
    </head>
    <body>
        <a href=javascript:window.print(); style="margin: 85%;">Imprimir</a>
        <div class="principal">            
            <img src="<?php echo $logo; ?>" style="float:right; margin: 0% 20% 5% 0%; height: 45px;"/>                      
        </div>
        <?php
        echo "<h3>Ticket de resurtido: $idTicket</h3>";
        echo "<h3>Cliente: $cliente</h3>";
        echo "<h3>Localidad: $localidad</h3>";
        echo "<h3>Almacen: $almacen </h3>";
        echo "<h3>Pedido: </h3>";
        echo "<br/>";
        ?>
        <table class="completeSize">
            <tr>
                <th class="borde centrado">Ticket</th> 
                <th class="borde centrado">Fecha</th>
                <th class="borde centrado">Modelo</th>
                <th class="borde centrado">Precio USD</th>               
                <th class="borde centrado">Cantidad Solicitada</th>
                <th class="borde centrado">Cantidad Surtida</th>
                <th class="borde centrado">Existencia</th>
                <th class="borde centrado">Mínimo</th>
                <th class="borde centrado">Máximo</th>
            </tr>
            <?php
                echo $tabla;
            ?>
        </table>
        <br/>
        <?php 
            $ticketAnterior = 0;
            $fechaAnterior = "";
            $ticketAnteriorConsulta = "SELECT t.IdTicket AS ticketAnterior, t.FechaHora FROM c_ticket t
                WHERE t.IdTicket = (SELECT MAX(t2.IdTicket) FROM c_ticket t2 
                WHERE t2.IdTicket < $idTicket AND t2.Resurtido = 1 AND t2.ClaveCliente = '$claveCliente')";
            $resultTicketAnterior = $catalogo->obtenerLista($ticketAnteriorConsulta);
            if($rsTicketAnterior = mysql_fetch_array($resultTicketAnterior)){
                $ticketAnterior = $rsTicketAnterior['ticketAnterior'];
                $fechaAnterior = $rsTicketAnterior['FechaHora'];
            }
            /*if($ticketAnterior != 0){
                echo "Para consultar el ticket de resurtido anterior de este almacén haga clic <a href='reporte_toner_ticket.php?idTicket=$ticketAnterior'  target='_blank'>"
                        . " <img src='../resources/images/icono_impresora.png' width='20' height='20'></a>";
            }*/
            //Vamos a mostrar los cambios de máximos y mínimos si es que hubo.
            $queryCambiosMinimosMaximos = "SELECT cma.*,c.Modelo FROM k_cambiosminialmacen cma 
                    LEFT JOIN c_componente AS c ON c.NoParte = cma.NoParte 
                    WHERE cma.IdAlmacen = $idAlmacen AND cma.Fecha < '$fecha' AND cma.Fecha > '$fechaAnterior' AND 
                    cma.NoParte IN (SELECT c2.NoParte FROM c_componente c2 INNER JOIN k_resurtidotoner AS rt2 ON rt2.NoComponenteToner = c2.NoParte WHERE rt2.IdTicket = $idTicket)";
            $resultCambios = $catalogo->obtenerLista($queryCambiosMinimosMaximos);
            if(mysql_num_rows($resultCambios) > 0){
                echo "<h5>Ha habido cambios en los mínimos y máximos de un modelo";
                echo "<table>";
                echo "<tr>";
                echo "<th>Modelo</th>";
                echo "<th>Fecha</th>";
                echo "<th>Min Anterior</th>";
                echo "<th>Max Anterior</th>";
                echo "<th>Min</th>";
                echo "<th>Max</th>";
                echo "</tr>";
                while($rsCambios = mysql_fetch_array($resultCambios)){
                    echo "<tr>";
                    echo "<td>".$rsCambios['Modelo']."</td>";
                    echo "<td>".$rsCambios['Fecha']."</td>";
                    echo "<td>".$rsCambios['MinimoAnterior']."</td>";
                    echo "<td>".$rsCambios['MaximoAnterior']."</td>";
                    echo "<td>".$rsCambios['MinimoNuevo']."</td>";
                    echo "<td>".$rsCambios['MaximoNuevo']."</td>";
                    echo "</tr>";
                }
                echo "</table>";
            }
        ?>
        <br/>
        <h4>Los toner que se cambiaron fueron:</h4>
        <table class="tablaCompleta">
            <tr>
                <th class="borde centrado">Ticket</th> 
                <th class="borde centrado">Fecha</th>
                <th class="borde centrado">Equipo</th>
                <th class="borde centrado">Serie</th>               
                <th class="borde centrado">NoParte</th>
                <th class="borde centrado">Modelo</th>
                <th class="borde centrado">Contador Actual</th>
                <th class="borde centrado">Contador Anterior</th>
                <th class="borde centrado">Impresiones</th>
                <th class="borde centrado">Rendimiento</th>
                <th class="borde centrado">Localidad</th>
            </tr>
            <?php 
                while($rsTickets = mysql_fetch_array($resultTickets)){
                    //Calculamos el porcentaje del rendimiento
                    $rendimientoTotal = 0;
                    if(isset($rsTickets['Rendimiento']) && $rsTickets['Rendimiento'] != ""){
                        $rendimientoTotal = (int)$rsTickets['Rendimiento'];
                    } 
                    $impresiones = $rsTickets['Impresiones'];
                    $porcentajeRendimiento = 0;
                    if($rendimientoTotal != 0){
                        $porcentajeRendimiento = ($impresiones * 100) / $rendimientoTotal;
                    }
                            
                    echo "<tr>";
                    echo "<td class='borde centrado'>".$rsTickets['NoTicket']."</td>";
                    echo "<td class='borde centrado'>".$rsTickets['Fecha']."</td>";
                    echo "<td class='borde centrado'>".$rsTickets['Equipo']."</td>";
                    echo "<td class='borde centrado'>".$rsTickets['NoSerie']."</td>";
                    echo "<td class='borde centrado'>".$rsTickets['NoParte']."</td>";
                    echo "<td class='borde centrado'>".$rsTickets['Modelo']."</td>";
                    echo "<td class='borde centrado'>".$rsTickets['ContadorBN']."</td>";
                    echo "<td class='borde centrado'>".$rsTickets['FechaAnterior']."<br/>".$rsTickets['ContadorBNAnterior']."</td>";
                    echo "<td class='borde centrado'>".$rsTickets['Impresiones']."</td>";
                    if($porcentajeRendimiento == 0){
                        if(!isset($rsTickets['ContadorBNAnterior']) || $rsTickets['ContadorBNAnterior'] == ""){
							echo "<td class='borde centrado'>Sin rendimiento por lectura anterior</td>";
						}else{
							echo "<td class='borde centrado'>Sin rendimiento</td>";
						}
                    }else{
                        if($porcentajeRendimiento < 0){
                            echo "<td class='borde centrado'> 0 % de <br/>".$rsTickets['Rendimiento']."</td>";
                        }else{
                            echo "<td class='borde centrado'> ".number_format($porcentajeRendimiento) ."% de <br/>".$rsTickets['Rendimiento']."</td>";
                        }
                    }
                    echo "<td class='borde centrado'>".$rsTickets['Localidad']."</td>";
                    echo "</tr>";
                    $arrayCantidadSolicitadaComponente[''.$rsTickets['NoParte']]--;
                }
            ?>
        </table>
        <br/>
        <?php
            $primeraVez = true;
            foreach ($arrayCantidadSolicitadaComponente as $key => $value) {
                if($value != 0){
                    if($primeraVez){
                        echo "<h5>Los siguientes modelos tienen inconsistencias en la cantidad solicitada y "
                        . "los cambios de tóner desde el último resurtido</h5>";
                        $primeraVez = false;
                    }
                    echo "Para el modelo: ".$arrayComponenteModelo[$key]." el ticket anterior de resurtido es: ";
                    if($arrayNoTicketComponente[$key] == ""){
                        echo "No hay ticket anterior de resurtido<br/>";
                    }else{
                        echo " <a href='".$url."reporte_toner_ticket.php?idTicket=".$arrayNoTicketComponente[$key]."'  target='_blank'>".$arrayNoTicketComponente[$key]."</a><br/>";
                    }
                }
            }
            $consultaMovimientosComponentes = "SELECT mc.CantidadMovimiento, c.Modelo, mc.Fecha,
                    (CASE WHEN !ISNULL(mc.IdAlmacenAnterior) THEN 'Salida' ELSE 'Entrada' END) AS Tipo, mc.UsuarioCreacion
                    FROM movimiento_componente mc 
                    LEFT JOIN c_componente AS c ON c.NoParte = mc.NoParteComponente
                    WHERE (mc.IdAlmacenAnterior = $idAlmacen OR mc.IdAlmacenNuevo = $idAlmacen) 
                    AND mc.Fecha < '$fecha' AND mc.Fecha > '$fechaAnterior' AND mc.IdTicket IS NULL 
                    AND mc.NoParteComponente IN (SELECT c2.NoParte FROM c_componente c2 INNER JOIN k_resurtidotoner AS rt2 ON rt2.NoComponenteToner = c2.NoParte WHERE rt2.IdTicket = $idTicket) ";
            $resultMovimientosComponente = $catalogo->obtenerLista($consultaMovimientosComponentes);
            if(mysql_num_rows($resultMovimientosComponente)){
                echo "<h5>Hubo cambios manuales en este almacen</h5>";
                echo "<table>";
                echo "<tr>";
                echo "<th>Modelo</th>";
                echo "<th>Fecha</th>";
                echo "<th>Tipo</th>";
                echo "<th>CantidadMovimiento</th>";
                echo "<th>CantidadMovimiento</th>";
                echo "</tr>";
                while($rsMovimientosComponente = mysql_fetch_array($resultMovimientosComponente)){
                    echo "<tr>";
                    echo "<td>".$rsMovimientosComponente['Modelo']."</td>";
                    echo "<td>".$rsMovimientosComponente['Fecha']."</td>";
                    echo "<td>".$rsMovimientosComponente['Tipo']."</td>";
                    echo "<td class='centrado'>".$rsMovimientosComponente['CantidadMovimiento']."</td>";
                    echo "<td>".$rsMovimientosComponente['UsuarioCreacion']."</td>";
                    echo "</tr>";
                }
                echo "</table>";
            }
        ?>
    </body>
</html>
<?
}else{
    ?>
    <!DOCTYPE HTML>
    <html lang="es">
        <head>
            <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
            <style>
                body{font-family: Arial; font-size: 15px;}
                .titulo{font-weight: bold; font-size: 18px;}
                table{
                    border-collapse:collapse;
                }            
                .borde{border: 1px solid #000;}
                .mediano{width: 30%;}
                .gigantes{width: 600px;}
                /*.pagebreak { page-break-after: always; page-break-before: always; }*/
                .espacio{min-height: 100px;}
                /*.obscuro{background-color: #404040; color: white; text-align: center;  font-style: italic; -webkit-print-color-adjust:exact;}
                .gris{background-color: #C0C0C0; font-weight: bold;  -webkit-print-color-adjust:exact;}
                .color{background-color: #3333CC; color: white; -webkit-print-color-adjust:exact; }
                .bn{background-color: #000; color: white; }*/
                .obscuro{color: black; text-align: center;  font-style: italic;}
                .gris{font-weight: bold; }
                .color{color: black;}
                .bn{color: black; }

                .pie{font-size: 10px; color: #800000;}
                .centrado {text-align: center;}
                .completeSize{width: 50%;}
                .tablaCompleta{width: 90%;}
            </style>
            <title>Reporte</title>
            <link rel="shortcut icon" href="../resources/images/logos/ra4.png" type="image/x-icon"/>
            <!-- JS -->
            <link rel="stylesheet" href="../resources/css/genesis/jquery-ui-1.10.3.custom.css" type="text/css" media="screen" />
            <script src="../resources/js/jquery/jquery-1.11.3.min.js"></script>
            <script src="../resources/js/jquery/jquery-ui.min.js"></script>  
            <script type="text/javascript" src="../resources/js/jquery/jquery.validate.js"></script>
            <script type="text/javascript" src="../resources/js/file_validate/js/file-validator.js"></script>
            <link href="../resources/js/file_validate/css/file-validator.css" rel="stylesheet" type="text/css">          
            <script type="text/javascript" src="../resources/js/jquery/jquery-ui-timepicker-addon.js"></script>
            <script type="text/javascript" src="../resources/js/jquery/jquery.maskedinput.min.js"></script>
            <script type="text/javascript" src="../resources/js/funciones.js"></script>   
            <script type="text/javascript" language="javascript" src="../resources/js/paginas/lista_ticket.js"></script>
        </head>
        <body>
            <b>Este ticket no es de resurtido, ingrese a ver el detalle aqui 
            <a href='#' onclick='detalleTicket("../mesa/alta_ticketphp.php", "<?php echo $_GET['idTicket']; ?>", "<?php echo $tipoReporte12; ?>", "1", "", "0");
                    return false;' title='Detalle' ><img src="../resources/images/Textpreview.png"/></a></b>
        </body>
    </html>
    <?php
}
}else{
?>
<!DOCTYPE HTML>
<html lang="es">
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
        <style>
            body{font-family: Arial; font-size: 15px;}
            .titulo{font-weight: bold; font-size: 18px;}
            table{
                border-collapse:collapse;
            }            
            .borde{border: 1px solid #000;}
            .mediano{width: 30%;}
            .gigantes{width: 600px;}
            /*.pagebreak { page-break-after: always; page-break-before: always; }*/
            .espacio{min-height: 100px;}
            /*.obscuro{background-color: #404040; color: white; text-align: center;  font-style: italic; -webkit-print-color-adjust:exact;}
            .gris{background-color: #C0C0C0; font-weight: bold;  -webkit-print-color-adjust:exact;}
            .color{background-color: #3333CC; color: white; -webkit-print-color-adjust:exact; }
            .bn{background-color: #000; color: white; }*/
            .obscuro{color: black; text-align: center;  font-style: italic;}
            .gris{font-weight: bold; }
            .color{color: black;}
            .bn{color: black; }

            .pie{font-size: 10px; color: #800000;}
            .centrado {text-align: center;}
            .completeSize{width: 50%;}
            .tablaCompleta{width: 90%;}
        </style>
        <title>Reporte</title>
        <link rel="shortcut icon" href="../resources/images/logos/ra4.png" type="image/x-icon"/>
    </head>
    <body>
        <b>El ticket que ha ingresado no existe, favor de verificar el número de ticket</b>
    </body>
</html>
<?php
}
}else{
    echo "<b>Ingrese al reporte desde el sistema</b>";
}