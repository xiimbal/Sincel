<?php
include_once ("Catalogo.class.php");
/**
 * Description of MovimientoAlmacen
 *
 * @author MAGG
 */
class MovimientoAlmacen {
    private $entrada_salida;
    private $id_almacen;
    /**
     * Se imprime las filas que se reciben en el resultSet
     * @param type $result ResultSet a imprimir.
     */
    public function imprimirFilasComponentes($result, $entrada_es, $Almacen){
        $i = 1;
        $array_series_destino = array();
        $catalogo = new Catalogo();
        while($rs = mysql_fetch_array($result)){
            /*if($entrada_es == 2 && $Almacen!=null && $Almacen != "" && $Almacen != "0" && $Almacen != $rs['idAlmacenOrigen']){//Si es salida, no se pintan salidas desde otro almacen
                continue;
            }*/
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
    }
    /**
     * Regresa la consulta que hay que ejecutar para obtener los movimientos de los componentes, dependiendo de los filtros que se reciben.
     * @param type $TipoComponente NoSerie del equipo. (Si no se requiere, mandar null o cadena vacia)
     * @param type $NoSerie NoSerie del equipo. (Si no se requiere, mandar null o cadena vacia)
     * @param type $Modelo Modelo del equipo.
     * @param type $e_s Si es entrada o salida (1 o 2).
     * @param type $ClaveCliente Clave del cliente.
     * @param type $ClaveLocalidad Clave de la localidad.
     * @param type $FechaInicio Fecha de inicio limite para los movimientos.
     * @param type $FechaFin Fecha Final limite de los movimientos.
     * @param type $Almacen id del almacen. 
     * @return type String con consulta que se genero con los filtros especificados.
     */
    public function generarConsultaComponentes($TipoComponente,$NoSerie, $Modelo, $e_s, $ClaveCliente, $ClaveLocalidad, $FechaInicio, 
            $FechaFin, $Almacen, $idTicket, $compras){
        $where = "";
        /*Filtro de NoSerie*/
        if($NoSerie!=null && $NoSerie!=""){
            $where = "WHERE mc.NoSerieAnterior LIKE '%$NoSerie%' OR mc.NoSerieNuevo LIKE '%$NoSerie%'";
        }
        
        /*Filtro de tipo de componente*/
        if($idTicket!=null && $idTicket!=""){
            if(is_numeric($idTicket)){
                if($where != "" ){
                    $where .= " AND mc.IdTicket = $idTicket ";
                }else{
                    $where = " WHERE mc.IdTicket = $idTicket ";
                }
            }else{
                echo "<br/>Error: el ticket tiene que ser un folio númerico";
            }
        }
        
        if($compras!=null && $compras=="on"){
            if($where != "" ){
                $where .= " AND !ISNULL(Id_compra) ";
            }else{
                $where = " WHERE !ISNULL(Id_compra) ";
            }
        }
        
        /*Filtro de tipo de componente*/
        if($TipoComponente!=null && !empty($TipoComponente)){
            if($where != "" ){
                $where .= " AND c.IdTipoComponente IN (".  implode(",", $TipoComponente).") ";
            }else{
                $where = " WHERE c.IdTipoComponente IN (".  implode(",", $TipoComponente).") ";
            }
        }
        
        /*Filtro de Modelo*/
        if($Modelo!=null && $Modelo!=""){
            if($where != "" ){
                $where .= " AND c.NoParte = '$Modelo'";                
            }else{
                $where = " WHERE c.NoParte = '$Modelo'";
            }
        }
        
        /*Filtro de fechas*/
        if (($FechaInicio != null && $FechaInicio != "") || ($FechaFin != null && $FechaFin != "")) {
            if ($FechaInicio != null && $FechaInicio != "" && $FechaFin != null && $FechaFin != "") {
                if ($where != "") {
                    $where .= " AND mc.Fecha BETWEEN '$FechaInicio  00:00:00' AND '$FechaFin 23:59:59'";
                } else {
                    $where = "WHERE mc.Fecha BETWEEN '$FechaInicio  00:00:00' AND '$FechaFin 23:59:59'";
                }
            } else if ($FechaInicio != null && $FechaInicio != "") {
                if ($where != "") {
                    $where .= " AND mc.Fecha >= '$FechaInicio'";
                } else {
                    $where = "WHERE mc.Fecha >= '$FechaInicio'";
                }
            } else if ($FechaFin != null && $FechaFin != "") {
                if ($where != "") {
                    $where .= " AND mc.Fecha <= '$FechaFin'";
                } else {
                    $where = "WHERE mc.Fecha <= '$FechaFin'";
                }
            }
        }
        
        if($e_s == 0){/*Entrada y salida*/
            if ($where != "") {
                $where .= " AND (!ISNULL(IdAlmacenNuevo) OR !ISNULL(IdAlmacenAnterior))";
            } else {
                $where = "WHERE (!ISNULL(IdAlmacenNuevo) OR !ISNULL(IdAlmacenAnterior))";
            }
            
            if($Almacen!=null && $Almacen != "" && $Almacen != "0"){
                if ($where != "") {
                    $where .= " AND ((IdAlmacenAnterior = '$Almacen' AND mc.Entradada_Salida = 1) OR (IdAlmacenNuevo = '$Almacen'  AND mc.Entradada_Salida = 0))";
                } else {
                    $where = "WHERE ((IdAlmacenAnterior = '$Almacen' AND mc.Entradada_Salida = 1) OR (IdAlmacenNuevo = '$Almacen'  AND mc.Entradada_Salida = 0))";
                }
            }
            
            if($ClaveCliente!=null && $ClaveCliente != "" && $ClaveCliente != "0"){//Si seleccionan cliente                
                if ($where != "") {
                    $where .= " AND (ClaveClienteNuevo = '$ClaveCliente' OR ClaveClienteAnterior = '$ClaveCliente')";
                } else {
                    $where = "WHERE (ClaveClienteNuevo = '$ClaveCliente' OR ClaveClienteAnterior = '$ClaveCliente')";
                }
            }
            
            if($ClaveLocalidad!=null && $ClaveLocalidad != ""){//Si seleccionan localidad                
                if ($where != "") {
                    $where .= " AND (ClaveCentroCostoNuevo = '$ClaveLocalidad' OR ClaveCentroCostoAnterior = '$ClaveLocalidad')";
                } else {
                    $where = "WHERE (ClaveCentroCostoNuevo = '$ClaveLocalidad' OR ClaveCentroCostoAnterior = '$ClaveLocalidad')";
                }
            }                        
        }else if($e_s == 1){/*Solo entrada*/
            $hay_entrada = false;
            if($Almacen!=null && $Almacen != "" && $Almacen != "0"){
                $hay_entrada = true;
                if ($where != "") {
                    $where .= " AND (IdAlmacenNuevo = '$Almacen' AND mc.Entradada_Salida = 0)";
                } else {
                    $where = "WHERE (IdAlmacenNuevo = '$Almacen' AND mc.Entradada_Salida = 0)";
                }
            }else{
                if ($where != "") {
                    $where .= " AND Entradada_Salida = 0 ";
                } else {
                    $where = "WHERE Entradada_Salida = 0 ";
                }
            }                        
            
            if($ClaveCliente!=null && $ClaveCliente != "" && $ClaveCliente != "0"){//Si seleccionan cliente                
                $hay_entrada = true;
                if ($where != "") {
                    $where .= " AND (ClaveClienteAnterior = '$ClaveCliente')";
                } else {
                    $where = "WHERE (ClaveClienteAnterior = '$ClaveCliente')";
                }
            }
            
            if($ClaveLocalidad!=null && $ClaveLocalidad != ""){//Si seleccionan localidad                
                $hay_entrada = true;
                if ($where != "") {
                    $where .= " AND (ClaveCentroCostoAnterior = '$ClaveLocalidad')";
                } else {
                    $where = "WHERE (ClaveCentroCostoAnterior = '$ClaveLocalidad')";
                }
            } 
            
            if(!$hay_entrada){/*Si no seleccionan algun almacen especifico de entrada, mostramos todas las entradas de todos los almacenes*/
                if ($where != "") {
                    $where .= " AND (!ISNULL(IdAlmacenNuevo))";
                } else {
                    $where = "WHERE (!ISNULL(IdAlmacenNuevo))";
                }
            }
        }else if($e_s == 2){/*Solo salida*/
            $hay_salida = false;                        
            
            if($Almacen!=null && $Almacen != "" && $Almacen != "0"){//Si seleccionan almacen
                $hay_salida = true;
                if ($where != "") {
                    $where .= " AND (IdAlmacenAnterior = '$Almacen' AND mc.Entradada_Salida = 1)";
                } else {
                    $where = "WHERE (IdAlmacenAnterior = '$Almacen' AND mc.Entradada_Salida = 1)";
                }
            }else{
                if ($where != "") {
                    $where .= " AND Entradada_Salida = 1 ";
                } else {
                    $where = "WHERE Entradada_Salida = 1 ";
                }
            }
                        
            
            if($ClaveCliente!=null && $ClaveCliente != "" && $ClaveCliente != "0"){//Si seleccionan cliente
                $hay_salida = true;
                if ($where != "") {
                    $where .= " AND (ClaveClienteNuevo = '$ClaveCliente')";
                } else {
                    $where = "WHERE (ClaveClienteNuevo = '$ClaveCliente')";
                }
            }
            
            if($ClaveLocalidad!=null && $ClaveLocalidad != ""){//Si seleccionan localidad
                $hay_salida = true;
                if ($where != "") {
                    $where .= " AND (ClaveCentroCostoNuevo = '$ClaveLocalidad')";
                } else {
                    $where = "WHERE (ClaveCentroCostoNuevo = '$ClaveLocalidad')";
                }
            }
            
            if(!$hay_salida){/*Si solo seleccionan salidas, ponemos todo lo que haya salido de cualquier almacen*/
                if ($where != "") {
                    $where .= " AND (!ISNULL(IdAlmacenAnterior))";
                } else {
                    $where = "WHERE (!ISNULL(IdAlmacenAnterior))";
                }
            }
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
        $where
        GROUP BY mc.IdMovimiento ORDER BY Fecha DESC;";
        //echo $consulta;
        return $consulta;
    }
    
    /**
     * Se imprime las filas que se reciben en el resultSet
     * @param type $result ResultSet a imprimir.
     */
    public function imprimirFilasEquipos($result){
        $i = 1;
        while($rs = mysql_fetch_array($result)){
            $almacen = "";
            if(isset($rs['almacen'])){
                $almacen = $rs['almacen'];
            }
            
            $cliente = "";
            if(isset($rs['cliente']) && $rs['cliente']!=$almacen){
                $cliente = $rs['cliente'];
            }
            
            echo "<tr>";            
            echo "<td class='borde'>Equipo</td>";
            echo "<td class='borde'>".$rs['Fecha']."</td>";
            echo "<td class='borde'>$almacen</td>";
            echo "<td class='borde'>".$rs['ES']."</td>";
            echo "<td class='borde'>".$rs['Modelo']."</td>";
            echo "<td class='borde'>1</td>";
            echo "<td class='borde'>".$rs['NoSerie']."</td>";
            echo "<td class='borde'>$cliente</td>";
            echo "<td class='borde'>".$rs['localidad']."</td>";
            echo "<td class='borde'>".$rs['Comentario']."</td>";
            echo "<td class='borde'>";
            if(isset($rs['id_reportes'])){                
                echo "Movimiento: <a href='../WEB-INF/Controllers/Ventas/Controller_Reporte_Historico.php?noSolicitud=" . $rs['id_reportes'] . "' target='_blank'>". $rs['id_reportes'] ." </a>";
            }else if($rs['id_compra'] && is_numeric($rs['id_compra'])){
                echo "Compra: <a href='../compras/reporte_orden_compra.php?id=" . $rs['id_compra'] . "' target='_blank'>". $rs['id_compra'] ." </a>";
            }else{
                switch ($rs['Pantalla']) {
                    case "Entrada_Orden_Individual":
                        echo "Entrada por compra";
                        break;
                    case "PHP Almacen-Equipo":
                        echo "Entrada inventario equipo";
                        break;
                    case "PHP Movimiento_equipos_solicitud":
                        echo "Solicitud de equipo";
                        break;
                    default:
                        echo $rs['Pantalla'];
                        break;
                }                
            }
            echo "</td>";
            echo "<td class='borde'></td>";
            echo "<td class='borde'></td>";
            
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
    }
    
    /**
     * Regresa la consulta que hay que ejecutar para obtener los movimientos de los equipos, dependiendo de los filtros que se reciben.
     * @param type $NoSerie NoSerie del equipo. (Si no se requiere, mandar null o cadena vacia)
     * @param type $Modelo Modelo del equipo.
     * @param type $e_s Si es entrada o salida (1 o 2).
     * @param type $ClaveCliente Clave del cliente.
     * @param type $ClaveLocalidad Clave de la localidad.
     * @param type $FechaInicio Fecha de inicio limite para los movimientos.
     * @param type $FechaFin Fecha Final limite de los movimientos.
     * @param type $Almacen id del almacen. 
     * @return type String con consulta que se genero con los filtros especificados.
     */
    public function generarConsultaEquipos($NoSerie, $Modelo, $e_s, $ClaveCliente, $ClaveLocalidad, $FechaInicio, $FechaFin, $Almacen, $idTicket, $compras){
        $where = "";
        /*Filtro de NoSerie*/
        if($NoSerie!=null && $NoSerie!=""){
            $where = "WHERE meq.NoSerie LIKE '%$NoSerie%'";
        }
        
        /*Filtro de Modelo*/
        if($Modelo!=null && $Modelo!=""){
            if($where != "" ){
                $where .= " AND e.NoParte = '$Modelo'";                
            }else{
                $where = " WHERE e.NoParte = '$Modelo'";
            }
        }
        
        if($compras!=null && $compras=="on"){
            if($where != "" ){
                $where .= " AND !ISNULL(Id_compra) ";
            }else{
                $where = " WHERE !ISNULL(Id_compra) ";
            }
        }
        
        /*Filtro de fechas*/
        if (($FechaInicio != null && $FechaInicio != "") || ($FechaFin != null && $FechaFin != "")) {            
            if ($FechaInicio != null && $FechaInicio != "" && $FechaFin != null && $FechaFin != "") {                
                if ($where != "") {
                    $where .= " AND meq.Fecha BETWEEN '$FechaInicio  00:00:00' AND '$FechaFin 23:59:59'";
                } else {
                    $where = "WHERE meq.Fecha BETWEEN '$FechaInicio  00:00:00' AND '$FechaFin 23:59:59'";
                }
            } else if ($FechaInicio != null && $FechaInicio != "") {                
                if ($where != "") {
                    $where .= " AND meq.Fecha >= '$FechaInicio'";
                } else {
                    $where = "WHERE meq.Fecha >= '$FechaInicio'";
                }
            } else if ($FechaFin != null && $FechaFin != "") {                
                if ($where != "") {
                    $where .= " AND meq.Fecha <= '$FechaFin'";
                } else {
                    $where = "WHERE meq.Fecha <= '$FechaFin'";
                }
            }
        }
        
        if($e_s == 0){/*Entrada y salida*/
            if ($where != "") {
                $where .= " AND (!ISNULL(almacen_nuevo) OR !ISNULL(almacen_anterior))";
            } else {
                $where = "WHERE (!ISNULL(almacen_nuevo) OR !ISNULL(almacen_anterior))";
            }
            
            if($Almacen!=null && $Almacen != "" && $Almacen != "0"){
                if ($where != "") {
                    $where .= " AND ((almacen_anterior = '$Almacen') OR (almacen_nuevo = '$Almacen'))";
                } else {
                    $where = "WHERE ((almacen_anterior = '$Almacen') OR (almacen_nuevo = '$Almacen'))";
                }
            }
            
            if($ClaveCliente!=null && $ClaveCliente != "" && $ClaveCliente != "0"){//Si seleccionan cliente                
                if ($where != "") {
                    $where .= " AND (clave_cliente_nuevo = '$ClaveCliente' OR clave_cliente_anterior = '$ClaveCliente')";
                } else {
                    $where = "WHERE (clave_cliente_nuevo = '$ClaveCliente' OR clave_cliente_anterior = '$ClaveCliente')";
                }
            }
            
            if($ClaveLocalidad!=null && $ClaveLocalidad != ""){//Si seleccionan localidad                
                if ($where != "") {
                    $where .= " AND (clave_centro_costo_nuevo = '$ClaveLocalidad' OR clave_centro_costo_anterior = '$ClaveLocalidad')";
                } else {
                    $where = "WHERE (clave_centro_costo_nuevo = '$ClaveLocalidad' OR clave_centro_costo_anterior = '$ClaveLocalidad')";
                }
            }                        
        }else if($e_s == 1){/*Solo entrada*/
            $hay_entrada = false;
            if($Almacen!=null && $Almacen != "" && $Almacen != "0"){
                $hay_entrada = true;
                if ($where != "") {
                    $where .= " AND (almacen_nuevo = '$Almacen')";
                } else {
                    $where = "WHERE (almacen_nuevo = '$Almacen')";
                }
            }
            
            if($ClaveCliente!=null && $ClaveCliente != "" && $ClaveCliente != "0"){//Si seleccionan cliente                
                $hay_entrada = true;
                if ($where != "") {
                    $where .= " AND (clave_cliente_anterior = '$ClaveCliente')";
                } else {
                    $where = "WHERE (clave_cliente_anterior = '$ClaveCliente')";
                }
            }
            
            if($ClaveLocalidad!=null && $ClaveLocalidad != ""){//Si seleccionan localidad                
                $hay_entrada = true;
                if ($where != "") {
                    $where .= " AND (clave_centro_costo_anterior = '$ClaveLocalidad')";
                } else {
                    $where = "WHERE (clave_centro_costo_anterior = '$ClaveLocalidad')";
                }
            } 
            
            if(!$hay_entrada){/*Si no seleccionan algun almacen especifico de entrada, mostramos todas las entradas de todos los almacenes*/
                if ($where != "") {
                    $where .= " AND (!ISNULL(almacen_nuevo))";
                } else {
                    $where = "WHERE (!ISNULL(almacen_nuevo))";
                }
            }
        }else if($e_s == 2){/*Solo salida*/
            $hay_salida = false;
            
            if($Almacen!=null && $Almacen != "" && $Almacen != "0"){//Si seleccionan almacen
                $hay_salida = true;
                if ($where != "") {
                    $where .= " AND (almacen_anterior = '$Almacen')";
                } else {
                    $where = "WHERE (almacen_anterior = '$Almacen')";
                }
            }
            
            if($ClaveCliente!=null && $ClaveCliente != "" && $ClaveCliente != "0"){//Si seleccionan cliente
                $hay_salida = true;
                if ($where != "") {
                    $where .= " AND (clave_cliente_nuevo = '$ClaveCliente')";
                } else {
                    $where = "WHERE (clave_cliente_nuevo = '$ClaveCliente')";
                }
            }
            
            if($ClaveLocalidad!=null && $ClaveLocalidad != ""){//Si seleccionan localidad
                $hay_salida = true;
                if ($where != "") {
                    $where .= " AND (clave_centro_costo_nuevo = '$ClaveLocalidad')";
                } else {
                    $where = "WHERE (clave_centro_costo_nuevo = '$ClaveLocalidad')";
                }
            }
            
            if(!$hay_salida){/*Si solo seleccionan salidas, ponemos todo lo que haya salido de cualquier almacen*/
                if ($where != "") {
                    $where .= " AND (!ISNULL(almacen_anterior))";
                } else {
                    $where = "WHERE (!ISNULL(almacen_anterior))";
                }
            }
        }
        
        $consulta = "SELECT meq.id_movimientos,meq.NoSerie, meq.Fecha, meq.causa_movimiento AS Comentario,meq.UsuarioUltimaModificacion,
            meq.Pantalla,rm.id_reportes,e.Modelo,meq.id_compra,
            (CASE WHEN !ISNULL(a_entrada.id_almacen) THEN a_entrada.nombre_almacen WHEN !ISNULL(a_salida.id_almacen) THEN a_salida.nombre_almacen ELSE null END) AS almacen,
            (CASE WHEN !ISNULL(a_entrada.id_almacen) THEN cd1.Ciudad WHEN !ISNULL(a_salida.id_almacen) THEN cd2.Ciudad ELSE null END) AS EstadoAlmacen,
            (CASE WHEN !ISNULL(a_entrada.id_almacen) THEN da1.Delegacion WHEN !ISNULL(a_salida.id_almacen) THEN da2.Delegacion ELSE null END) AS DelegacionAlmacen,
            (CASE WHEN !ISNULL(c1.ClaveCliente) THEN c1.NombreRazonSocial WHEN !ISNULL(c2.ClaveCliente) THEN c2.NombreRazonSocial
            WHEN !ISNULL(a_salida.id_almacen) THEN a_salida.nombre_almacen WHEN !ISNULL(a_entrada.id_almacen) THEN a_entrada.nombre_almacen ELSE null END) AS cliente,
            (CASE WHEN !ISNULL(cc1.ClaveCentroCosto) THEN cc1.Nombre WHEN !ISNULL(cc2.ClaveCentroCosto) THEN cc2.Nombre ELSE null END) AS localidad,
            (CASE WHEN !ISNULL(cc1.ClaveCentroCosto) THEN dcc1.Estado WHEN !ISNULL(cc2.ClaveCentroCosto) THEN dcc2.Estado ELSE null END) AS EstadoLocalidad,
            (CASE WHEN !ISNULL(cc1.ClaveCentroCosto) THEN dcc1.Delegacion WHEN !ISNULL(cc2.ClaveCentroCosto) THEN dcc2.Delegacion ELSE null END) AS DelegacionLocalidad,
            (CASE WHEN !ISNULL(a_entrada.id_almacen) THEN 'Entrada' WHEN !ISNULL(a_salida.id_almacen) THEN 'Salida' ELSE 'No almacen' END) AS ES
            FROM `movimientos_equipo` AS meq
            INNER JOIN c_bitacora AS b ON b.NoSerie = meq.NoSerie
            INNER JOIN c_equipo AS e ON e.NoParte = b.NoParte
            LEFT JOIN c_almacen AS a_entrada ON a_entrada.id_almacen = meq.almacen_nuevo
            LEFT JOIN c_almacen AS a_salida ON a_salida.id_almacen = meq.almacen_anterior
            LEFT JOIN c_domicilio_almacen AS da1 ON da1.IdAlmacen = a_entrada.id_almacen
            LEFT JOIN c_domicilio_almacen AS da2 ON da2.IdAlmacen = a_salida.id_almacen
            LEFT JOIN c_ciudades AS cd1 ON cd1.IdCiudad = da1.Estado
            LEFT JOIN c_ciudades AS cd2 ON cd2.IdCiudad = da2.Estado
            LEFT JOIN c_cliente AS c1 ON c1.ClaveCliente = meq.clave_cliente_nuevo
            LEFT JOIN c_cliente AS c2 ON c2.ClaveCliente = meq.clave_cliente_anterior
            LEFT JOIN c_centrocosto AS cc1 ON cc1.ClaveCentroCosto = meq.clave_centro_costo_nuevo
            LEFT JOIN c_centrocosto AS cc2 ON cc2.ClaveCentroCosto = meq.clave_centro_costo_anterior 
            LEFT JOIN c_domicilio AS dcc1 ON dcc1.ClaveEspecialDomicilio = cc1.ClaveCentroCosto
            LEFT JOIN c_domicilio AS dcc2 ON dcc2.ClaveEspecialDomicilio = cc2.ClaveCentroCosto
            LEFT JOIN reportes_movimientos AS rm ON rm.id_movimientos = meq.id_movimientos $where 
            GROUP BY meq.id_movimientos ORDER BY Fecha DESC;";
        //echo $consulta;
        return $consulta;
    }
    
    function getEntrada_salida() {
        return $this->entrada_salida;
    }

    function getId_almacen() {
        return $this->id_almacen;
    }

    function setEntrada_salida($entrada_salida) {
        $this->entrada_salida = $entrada_salida;
    }

    function setId_almacen($id_almacen) {
        $this->id_almacen = $id_almacen;
    }


}

?>
