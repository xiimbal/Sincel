<?php
session_start();

if (!isset($_SESSION['user']) || $_SESSION['user'] == "") {
    header("Location: index.php");
}

include_once("../WEB-INF/Classes/Catalogo.class.php");
$catalogo = new Catalogo();


if(!isset($_POST['noParte']) || !isset($_POST['enSa']) || !isset($_POST['fecha']) || !isset($_POST['almacenId'])){
    return "No se han recibido los parámetros correctos.";
}
if($_POST['enSa']==0){
    $almacenO="AND a_entrada.id_almacen= '".$_POST['almacenId']."'";
}else{
    $almacenO="AND a_salida.id_almacen='".$_POST['almacenId']."'";
}
        $consulta = "SELECT mc.IdMovimiento,mc.NoParteComponente,mc.CantidadMovimiento,mc.Fecha,mc.Comentario,mc.UsuarioUltimaModificacion,mc.Pantalla,
        c.Modelo,tc.Nombre AS TipoComponente,mc.IdTicket,mc.id_compra,t.Resurtido,t.TipoReporte,t.NoSerieEquipo,t.ModeloEquipo,
        (CASE WHEN !ISNULL(a_entrada.id_almacen) THEN a_entrada.nombre_almacen WHEN !ISNULL(a_salida.id_almacen) THEN a_salida.nombre_almacen ELSE null END) AS almacen,
        (CASE WHEN !ISNULL(a_entrada.id_almacen) THEN a_entrada.id_almacen WHEN !ISNULL(a_salida.id_almacen) THEN a_salida.id_almacen ELSE null END) AS IdalmacenAux,
        (CASE WHEN !ISNULL(a_entrada.id_almacen) THEN cd1.Ciudad WHEN !ISNULL(a_salida.id_almacen) THEN cd2.Ciudad ELSE null END) AS EstadoAlmacen,
        (CASE WHEN !ISNULL(a_entrada.id_almacen) THEN da1.Delegacion WHEN !ISNULL(a_salida.id_almacen) THEN da2.Delegacion ELSE null END) AS DelegacionAlmacen,
        a_salida.id_almacen AS idAlmacenOrigen,
        (CASE WHEN !ISNULL(c1.ClaveCliente) THEN c1.NombreRazonSocial WHEN !ISNULL(c2.ClaveCliente) THEN c2.NombreRazonSocial 
        WHEN !ISNULL(a_salida.id_almacen) THEN a_salida.nombre_almacen WHEN !ISNULL(a_entrada.id_almacen) THEN a_entrada.nombre_almacen ELSE null END) AS cliente,
        (CASE WHEN !ISNULL(cc1.ClaveCentroCosto) THEN cc1.Nombre WHEN !ISNULL(cc2.ClaveCentroCosto) THEN cc2.Nombre ELSE null END) AS localidad,
        (CASE WHEN !ISNULL(cc1.ClaveCentroCosto) THEN dcc1.Estado WHEN !ISNULL(cc2.ClaveCentroCosto) THEN dcc2.Estado ELSE null END) AS EstadoLocalidad,
        (CASE WHEN !ISNULL(cc1.ClaveCentroCosto) THEN dcc1.Delegacion WHEN !ISNULL(cc2.ClaveCentroCosto) THEN dcc2.Delegacion ELSE null END) AS DelegacionLocalidad,
        (CASE WHEN mc.Entradada_Salida = 0 THEN 'Entrada' ELSE 'Salida' END) AS ES        
        FROM `movimiento_componente` AS mc
        INNER JOIN c_componente AS c ON c.NoParte = mc.NoParteComponente
        INNER JOIN c_tipocomponente AS tc ON tc.IdTipoComponente = c.IdTipoComponente
        LEFT JOIN c_almacen AS a_entrada ON a_entrada.id_almacen = mc.IdAlmacenNuevo
        LEFT JOIN c_almacen AS a_salida ON a_salida.id_almacen = mc.IdAlmacenAnterior
        LEFT JOIN c_domicilio_almacen AS da1 ON da1.IdAlmacen = a_entrada.id_almacen
        LEFT JOIN c_domicilio_almacen AS da2 ON da2.IdAlmacen = a_salida.id_almacen
        LEFT JOIN c_ciudades AS cd1 ON cd1.IdCiudad = da1.Estado
        LEFT JOIN c_ciudades AS cd2 ON cd2.IdCiudad = da2.Estado
        LEFT JOIN c_cliente AS c1 ON c1.ClaveCliente = mc.ClaveClienteNuevo
        LEFT JOIN c_cliente AS c2 ON c2.ClaveCliente = mc.ClaveClienteAnterior
        LEFT JOIN c_centrocosto AS cc1 ON cc1.ClaveCentroCosto = mc.ClaveCentroCostoNuevo
        LEFT JOIN c_centrocosto AS cc2 ON cc2.ClaveCentroCosto = mc.ClaveCentroCostoAnterior
        LEFT JOIN c_domicilio AS dcc1 ON dcc1.ClaveEspecialDomicilio = cc1.ClaveCentroCosto
        LEFT JOIN c_domicilio AS dcc2 ON dcc2.ClaveEspecialDomicilio = cc2.ClaveCentroCosto
        LEFT JOIN c_ticket AS t ON t.IdTicket = mc.IdTicket
        WHERE c.NoParte = '".$_POST['noParte']."' AND mc.Entradada_Salida = ".$_POST['enSa']." AND ".$_POST['fecha']." $almacenO;";

        echo "<input id='tipoComponente' name='tipoComponente' type='hidden' value='".$_POST['componente']."'/>";
        echo "<input id='almacen' name='almacen' type='hidden' value='".implode(",", $_POST['almacen'])."'/>";
        echo "<input id='enSa' name='enSa' type='hidden' value='".$_POST['enSaFiltro']."'/>";
        echo "<input id='mes' name='mes' type='hidden' value='".$_POST['mes']."'/>";
        echo "</br>";
       //value='".implode(",",$_POST['almacen'])."'
?>
<!DOCTYPE html>
<html>
    <head>
        <meta charset="UTF-8">
        <style>
                table{
                    border-collapse:collapse;
                    width: 100%;
                }            
                .borde{border: 1px solid #000;}
            </style>
            <title>Reporte de movimientos en almacén</title>
            <link rel="icon" href="../resources/images/logos/ra4.png" type="image/x-icon"/>
            <script>
            function regresar(){
                
                  var almacen = $("#almacen").val().split(",");  


                $("#contenidos").load("almacen/reporte_entradas_salidas.php",{componente:$("#tipoComponente").val(),almacen:almacen,enSa:$("#enSa").val(),mes:$("#mes").val(),mostrar:true});
            }
            </script>
    </head>
    <body>
        <a href="#" onclick="regresar();" style="margin: 15%;">Regresar</a>
        <a href=javascript:window.print(); style="margin: 85%;">Imprimir</a>
        <?php
        $query = $catalogo->obtenerLista($consulta);
        echo "<br/><br/><table>";
            echo "<thead><tr>";
            echo "<th class='borde'>Tipo</th>
                    <th class='borde'>Fecha</th><th class='borde'>Almacen</th><th class='borde'>Movimiento</th><th class='borde'>Modelo</th>
                    <th class='borde'>Cantidad</th>";
            echo "<th class='borde'>NoSerie</th><th class='borde'>Cliente o almacén</th><th class='borde'>Localidad</th>
                    <th class='borde'>Comentario</th><th class='borde'>Ticket</th>
                    <th class='borde'>Serie Destino</th><th class='borde'>Modelo Destino</th>
                    <th class='borde'>Estado Destino</th><th class='borde'>Delegación Destino</th>
                    <th class='borde'>Usuario</th>";
            echo "</tr></thead>";
            echo "<tbody>";
        while ($rs = mysql_fetch_array($query)) {
            $almacen = "";
            if(isset($rs['almacen'])){
                $almacen = $rs['almacen'];
            }
            $cliente = "";
            if(isset($rs['cliente']) && $rs['cliente']!=$almacen){
                $cliente = $rs['cliente'];
            }
            
            $es = $rs['ES'];
            if($rs['IdalmacenAux'] != $Almacen && $rs['ES'] == "Salida"){//Cambiamos para que las "entradas" de otros almacenes, sean salidas del almacen seleccionado
                $aux = $almacen;
                $almacen = $cliente;
                $cliente = $aux;
                $es = "Salida";
            }
            
            echo "<tr>";            
            echo "<td class='borde'>" .$rs['TipoComponente']."</td>";
            echo "<td class='borde'>".$rs['Fecha']."</td>";
            echo "<td class='borde'>$almacen</td>";
            echo "<td class='borde'>$es</td>";
            echo "<td class='borde'>".$rs['Modelo']."</td>";
            echo "<td class='borde'>".$rs['CantidadMovimiento']."</td>";
            echo "<td class='borde'>".$rs['NoParteComponente']."</td>";
            echo "<td class='borde'>$cliente</td>";
            echo "<td class='borde'>".$rs['localidad']."</td>";
            echo "<td class='borde'>".$rs['Comentario']."</td>";
            echo "<td class='borde'>";
            $serie = ""; $modelo = "";
            if(isset($rs['IdTicket']) && $rs['IdTicket']!=""){
                echo "Ticket: <a href='../principal.php?mnu=mesa&action=lista_ticket&id=".$rs['IdTicket']."' target='_blank' title='Ver ticket ".$rs['IdTicket']."'>".$rs['IdTicket']."</a>";
                if($rs['TipoReporte'] != "15"){
                    $serie = $rs['NoSerieEquipo'];
                    $modelo = $rs['ModeloEquipo'];
                }else if($rs['Resurtido'] == "1"){
                    $serie = "Resurtido de mini-almacén";
                }else{//En caso que sea un ticket de toner y que no sea de resurtido, se busca a que equipo había sido solicitado                    
                    $consulta = "SELECT dnr.Componente, dnr.NoSerieEquipo, dnr.Cantidad, e.Modelo  
                        FROM c_notaticket AS nt
                        LEFT JOIN k_detalle_notarefaccion AS dnr ON dnr.IdNota = nt.IdNotaTicket
                        LEFT JOIN c_bitacora AS b ON b.NoSerie = dnr.NoSerieEquipo
                        LEFT JOIN c_equipo AS e ON e.NoParte = b.NoParte
                        WHERE nt.IdTicket = ".$rs['IdTicket']." AND nt.IdEstatusAtencion = 67 AND dnr.Componente = '".$rs['NoParteComponente']."'
                        ORDER BY NoSerieEquipo;";
                    $result2 = $catalogo->obtenerLista($consulta);
                    while($rs2 = mysql_fetch_array($result2)){                        
                        if(!isset($array_series_destino[$rs['IdTicket']][$rs['NoParteComponente']][$rs2['NoSerieEquipo']])){
                            $serie = $rs2['NoSerieEquipo'];
                            $modelo = $rs2['Modelo'];
                            $array_series_destino[$rs['IdTicket']][$rs['NoParteComponente']][$rs2['NoSerieEquipo']] = true;
                            break;
                        }
                    }
                }
            }else if($rs['id_compra'] && is_numeric($rs['id_compra'])){
                echo "Compra: <a href='../compras/reporte_orden_compra.php?id=" . $rs['id_compra'] . "' target='_blank'>". $rs['id_compra'] ." </a>";
            }else{
                if($rs['Pantalla']=="PHP Movimiento_equipos_solicitud"){
                    echo "Solicitud de equipo";
                }else if($rs['Pantalla']=="Entrada al almacén" || $rs['Pantalla']=="Salida del almacén"){
                    echo "Entrada manual al almacén";
                }
            }
            echo "</td>";
            echo "<td class='borde'>$serie</td>";
            echo "<td class='borde'>$modelo</td>";
            
            $estado = ""; $delegacion = "";
            if($rs['ES'] == "Salida"){//Salida del almacen, es decir, destino es la localidad
                $estado = $rs['EstadoLocalidad'];
                $delegacion = $rs['DelegacionLocalidad'];
            }else if($rs['ES'] == "Entrada"){//Entrada al almacen, es decir. destino es almacen
                $estado = $rs['EstadoAlmacen'];
                $delegacion = $rs['DelegacionAlmacen'];
            }
            
            echo "<td class='borde'>$estado</td>";
            echo "<td class='borde'>$delegacion</td>";
            
            echo "<td class='borde'>".$rs['UsuarioUltimaModificacion']."</td>";
            echo "</tr>";
        }        
        echo "</tbody>";
            echo "</table>";
        ?>
    </body>
</html>
