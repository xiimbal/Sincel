<?php

header('Content-Type: text/html; charset=utf-8');

require_once "../lib/nusoap.php";
include_once("../WEB-INF/Classes/Evento.class.php");
include_once("../WEB-INF/Classes/Session.class.php");
include_once("../WEB-INF/Classes/Catalogo.class.php");
include_once("../WEB-INF/Classes/Usuario.class.php");
include_once("../WEB-INF/Classes/Parametros.class.php");

/**
 * 
 * @param type $IdSession
 * @param type $IdUsuarios string con los id de usuarios separados por comas
 * @param type $IdEstatus string con los estatus a buscar, separados por comas
 * @param type $FechaInicio1 string con la fecha de inicio, con formato YYYY-MM-DD hh:mm:ss
 * @param type $FechaFin1 string con la fecha de fin, con formato YYYY-MM-DD hh:mm:ss
 * @param type $Latitud
 * @param type $Longitud
 * @param type $Pagina
 * @param type $IdTicket
 * @param type $Serie
 * @param type $MostrarCerrados
 * @param type $MostrarCancelados
 * @param type $MostrarMorosos
 * @param type $AreaAtencion
 * @param type $TipoReporte
 * @param type $Cliente
 */
function getTickets($IdSession, $IdUsuarios, $IdEstatus, $FechaInicio1, $FechaFin1, $Latitud, $Longitud, $Pagina, $IdTicket, 
        $Serie, $MostrarCerrados, $MostrarCancelados, $MostrarMorosos, $AreaAtencion, $TipoReporte, $Cliente, $TodosEstatus) {
    $session = new Session();
    $empresa = $session->obtenerEmpresaBySesion($IdSession);

    if ($empresa == "0") {
        return -100;
    }
    
    $registros_por_pagina = 10;
    $indice = $registros_por_pagina * ($Pagina - 1);
    $session->setEmpresa($empresa);
    $resultadoLoggin = (int) $session->logginWithSession($IdSession);

    if ($resultadoLoggin > 0) {
        $catalogo = new Catalogo();
        $catalogo->setEmpresa($empresa);
        
        $cerradoTicket = "t.EstadoDeTicket <> 2 AND ";
        $canceladoTicket = "t.EstadoDeTicket <> 4 AND ";
        $morososTicket = "cl.IdEstatusCobranza <> 2 AND ";
        $having = " HAVING ((IdEstatusAtencion <> 16 AND IdEstatusAtencion <> 59) OR ISNULL(IdEstatusAtencion))";
        $estadoNota = "LEFT JOIN c_notaticket AS nt ON nt.IdNotaTicket = (SELECT MAX(IdNotaTicket) FROM c_notaticket AS nt2 WHERE nt2.IdTicket = t.IdTicket)";
        $tecnico = "";
        $select_prioridad = "";
        $Where = "";
        $NoSerie = "";
        $areaAtencion = "";
        $tipoReporte = "";
        $cliente = "";
        $tiene_filtro = false;
        $tipo_join = "LEFT";
        $tipo_join_estado = "LEFT";
        $estado_falla = "";
        $vendedor = "";
        $tfs = "";
        $estado = "";
        $group_by = "";
        
        $usuario = new Usuario();
        $usuario->setEmpresa($empresa);
        $idUsuario = $resultadoLoggin;

        /* Verificamos el puesto del usuario */
        if ($usuario->getRegistroById($idUsuario)) {//Buscamos las areas de atencion a las que está asociado este puesto
            $consulta = "SELECT GROUP_CONCAT(CONVERT(IdEstado, CHAR(8)) SEPARATOR ',') estados FROM `k_areapuesto` WHERE IdPuesto = " . $usuario->getPuesto() . ";";
            $result = $catalogo->obtenerLista($consulta);
            if (mysql_numrows($result) > 0) {
                while ($rs = mysql_fetch_array($result)) {
                    if (!empty($rs['estados'])) {
                        $estado = " e2.IdEstado IN (" . $rs['estados'] . ") AND ";
                        $tipo_join_estado = "INNER";
                    } else {
                        $estado = "";
                    }
                }
            } else {
                $estado = "";
            }
        } else {
            $estado = "";
        }
        
        if(!empty($Latitud) && !empty($Longitud)){
            $distancia = "(CASE WHEN !ISNULL(dt.IdDomicilioTicket) 
                THEN 
                111.1111 * DEGREES(ACOS(COS(RADIANS(dt.Latitud))
                                * COS(RADIANS($Latitud))
                                * COS(RADIANS(dt.Longitud - $Longitud))
                                + SIN(RADIANS(dt.Latitud))
                                * SIN(RADIANS($Latitud))))
                ELSE 
                111.1111 * DEGREES(ACOS(COS(RADIANS(dml.Latitud))
                                * COS(RADIANS($Latitud))
                                * COS(RADIANS(dml.Longitud - $Longitud))
                                + SIN(RADIANS(dml.Latitud))
                                * SIN(RADIANS($Latitud))))
                END) AS distance_in_km,";
        }else{
            $distancia = "0 AS distance_in_km, ";
        }

        if (!empty($IdTicket)) {
            $tiene_filtro = true;
        }

        if (!empty($Serie)) {
            $tiene_filtro = true;
            $NoSerie = $Serie;
            $Where = "WHERE (SELECT CASE WHEN e2.IdEstado = 2 THEN ( SELECT group_concat( ClaveEspEquipo SEPARATOR ', ') 
            FROM `c_pedido` WHERE IdTicket = t.IdTicket) ELSE t.NoSerieEquipo END) LIKE '%$NoSerie%'";
        }

        if(empty($IdUsuarios)){//Si no se manda un id de usuario explicito, se toma el id del usuario en sesión
            $IdUsuarios = $resultadoLoggin;
        }
        
        if (!empty($IdUsuarios)) {
            $tecnico = "INNER JOIN k_tecnicoticket AS ktt ON ktt.IdUsuario IN ($IdUsuarios) AND ktt.IdTicket = t.IdTicket 
                        LEFT JOIN c_prioridadticket AS pt ON pt.IdPrioridad = ktt.IdPrioridad
                        LEFT JOIN c_color AS clr ON clr.IdColor = pt.IdColor
                        LEFT JOIN c_tipoprioridad AS tp ON tp.IdTipoPrioridad = pt.IdTipoPrioridad
                        LEFT JOIN c_unidadmedida AS um ON um.IdUnidad = ktt.IdUnidadDuracion";
            $select_prioridad = " pt.*, tp.TipoPrioridad, clr.Color, ktt.Duracion, ktt.IdUnidadDuracion,um.Unidad,ktt.FechaHoraInicio, ";
            $tipo_join = "LEFT";
            $group_by = " ktt.FechaHoraInicio ASC, -pt.Prioridad DESC, ";
        }

        if ($MostrarCerrados == "1") {
            $cerradoTicket = "";
            if ($MostrarCancelados == "1") {
                $having = "";
            } else {
                $having = " HAVING ((IdEstatusAtencion <> 59) OR ISNULL(IdEstatusAtencion)) ";
            }
        } else {
            if ($MostrarCancelados == "1") {
                $having = " HAVING ((IdEstatusAtencion <> 16) OR ISNULL(IdEstatusAtencion)) ";
            }
        }

        if ($MostrarMorosos == "1") {
            $morososTicket = "";
        }

        if ($MostrarCancelados == "1") {
            $canceladoTicket = "";
        }

        if (!empty($AreaAtencion)) {
            $tiene_filtro = true;
            //$areaAtencion = " AreaAtencion = $AreaAtencion AND ";
            if ($having != "") {
                $having .= " AND idArea = ".$_POST['area']." ";
            } else {
                $having = " HAVING idArea = ".$_POST['area']." ";
            }
        }

        if (!empty($TipoReporte)) {
            $tiene_filtro = true;
            $tipoReporte = " TipoReporte = $TipoReporte AND ";
        }

        if (!empty($Cliente)) {
            $tiene_filtro = true;
            $cliente_array = explode(",", $Cliente);
            $cliente = " AND t.ClaveCliente IN ( ";
            foreach ($cliente_array as $value) {
                $cliente .= "'$value',";
            }
            if (!empty($cliente)) {
                $cliente = substr($cliente, 0, strlen($cliente) - 1);
            }
            $cliente .= ")";
        }

        if (!empty($IdEstatus)) {
            if($TodosEstatus == "1"){
                $estadoNota = "INNER JOIN c_notaticket AS nt ON nt.IdNotaTicket = (SELECT MAX(IdNotaTicket) FROM c_notaticket AS nt2 WHERE nt2.IdTicket = t.IdTicket AND nt2.IdEstatusAtencion IN ($IdEstatus) )";
            }else{
                $estadoNota = "INNER JOIN c_notaticket AS nt ON nt.IdEstatusAtencion IN ($IdEstatus) AND nt.IdNotaTicket = (SELECT MAX(IdNotaTicket) FROM c_notaticket AS nt2 WHERE nt2.IdTicket = t.IdTicket)";
            }
            $array_estatus = explode(",", $IdEstatus);
            $tiene_filtro = true;
            if (in_array("16", $array_estatus)) {/* Si se selecciona el estado de cerrado, tenemos que mostrar los ticket cerradoss */
                $cerradoTicket = "";
                if ($MostrarCancelados == "1") {
                    $having = "";
                } else {
                    $having = " HAVING ((IdEstatusAtencion <> 59) OR ISNULL(IdEstatusAtencion) ";
                }
            }
        }

        if ((!empty($FechaInicio1)) || (!empty($FechaFin1))) {
            $tiene_filtro = true;
            if (!empty($FechaInicio1) && !empty($FechaFin1)) {
                $FechaInicio = $FechaInicio1;
                $FechaFin = $FechaFin1;
                if ($Where != "") {
                    $Where .= " AND t.FechaHora BETWEEN '$FechaInicio' AND '$FechaFin'";
                } else {
                    $Where = "WHERE t.FechaHora BETWEEN '$FechaInicio' AND '$FechaFin'";
                }
            } else if (!empty($FechaInicio1)) {
                $FechaInicio = $FechaInicio1;
                if ($Where != "") {
                    $Where .= " AND t.FechaHora >= '$FechaInicio'";
                } else {
                    $Where = "WHERE t.FechaHora >= '$FechaInicio'";
                }
            } else if (!empty($FechaFin1)) {
                $FechaFin = $FechaFin1;
                if ($Where != "") {
                    $Where .= " AND t.FechaHora <= '$FechaFin'";
                } else {
                    $Where = "WHERE t.FechaHora <= '$FechaFin'";
                }
            }
        }

        if ($IdTicket == "") {
            $consulta = "SELECT
            b.id_bitacora,
            t.IdTicket,
            t.NoTicketCliente,
            cl.Suspendido,
            NoTicketDistribuidor,
            DATE(t.FechaHora) AS FechaHora,
            t.DescripcionReporte,
            t.NombreCentroCosto,
            t.TipoReporte,
            (SELECT CASE WHEN e2.IdEstado = 2 THEN (SELECT group_concat(ClaveEspEquipo separator ', ') FROM `c_pedido` WHERE IdTicket = t.IdTicket) ELSE t.NoSerieEquipo END) AS NumSerie,
            DATEDIFF(NOW(),t.FechaHora) AS diferencia,
            t.NombreCliente,t.ClaveCliente,
            clg.Nombre AS NombreGrupo,
            cl.IdEstatusCobranza,
            e.IdEstadoTicket AS estadoTicket,                                         
            tc.IdTipoCliente AS tipoCliente,
            (CASE WHEN !ISNULL(ar.IdArea) THEN ar.Descripcion ELSE ar2.Descripcion END) AS area, 
            (CASE WHEN !ISNULL(ar.IdArea) THEN ar.IdArea ELSE ar2.IdArea END) AS idArea,                                 
            (SELECT CASE WHEN !ISNULL(cgz.NombreZona) THEN cgz.NombreZona WHEN !ISNULL(cgz3.NombreZona) THEN cgz3.NombreZona ELSE cgz2.NombreZona END) AS ubicacionTicket,
            e3.Nombre AS estadoNota,
            nt.IdEstatusAtencion,
            nt.DiagnosticoSol,
            nt.FechaHora AS FechaNota,
            (CASE WHEN !ISNULL(dt.IdDomicilioTicket) THEN dt.Calle ELSE dml.Calle END) AS Calle,
            (CASE WHEN !ISNULL(dt.IdDomicilioTicket) THEN dt.NoExterior ELSE dml.NoExterior END) AS NoExterior,
            (CASE WHEN !ISNULL(dt.IdDomicilioTicket) THEN dt.NoInterior ELSE dml.NoInterior END) AS NoInterior,
            (CASE WHEN !ISNULL(dt.IdDomicilioTicket) THEN dt.Colonia ELSE dml.Colonia END) AS Colonia,
            (CASE WHEN !ISNULL(dt.IdDomicilioTicket) THEN dt.Ciudad ELSE dml.Ciudad END) AS Ciudad,
            (CASE WHEN !ISNULL(dt.IdDomicilioTicket) THEN dt.Estado ELSE dml.Estado END) AS Estado,
            (CASE WHEN !ISNULL(dt.IdDomicilioTicket) THEN dt.Delegacion ELSE dml.Delegacion END) AS Delegacion,
            (CASE WHEN !ISNULL(dt.IdDomicilioTicket) THEN dt.CodigoPostal ELSE dml.CodigoPostal END) AS CodigoPostal,
            (CASE WHEN !ISNULL(dt.Latitud) THEN dt.Latitud ELSE dml.Latitud END) AS Latitud,
            (CASE WHEN !ISNULL(dt.Longitud) THEN dt.Longitud ELSE dml.Longitud END) AS Longitud,
            $distancia
            t.NombreResp,t.Telefono1Resp,t.CorreoEResp,
            t.NombreAtenc,t.Telefono1Atenc,t.CorreoEAtenc,
            t.FechaCheckIn,t.FechaCheckOut,ntCI.FechaHora AS FechaCheckInReal, ntCO.FechaHora AS FechaCheckOutReal,";            
            $consulta2 = $consulta;//consulta para mandar tambien tickets no asociados a nadie
            $consulta .= "$select_prioridad";            
            $consulta_aux = "(SELECT CASE WHEN ISNULL(nt.UsuarioUltimaModificacion) THEN t.UsuarioUltimaModificacion ELSE nt.UsuarioUltimaModificacion END) as UltimoUsuarioNota,
            t.Resurtido,
            (SELECT GROUP_CONCAT(DISTINCT(k_enviotoner.NoGuia) SEPARATOR ', ') AS NoGuia 
            FROM `k_enviotoner`
            INNER JOIN c_pedido ON c_pedido.IdPedido = k_enviotoner.IdSolicitud
            INNER JOIN c_ticket ON c_ticket.IdTicket = c_pedido.IdTicket
            WHERE c_ticket.IdTicket = t.IdTicket GROUP BY c_ticket.IdTicket) AS NoGuia
            FROM c_ticket AS t
            INNER JOIN c_estadoticket AS e ON $tipoReporte $areaAtencion $canceladoTicket $cerradoTicket e.IdEstadoTicket = t.EstadoDeTicket $cliente
            LEFT JOIN c_domicilioticket AS dt ON dt.IdTicket = t.IdTicket 
            LEFT JOIN c_zona AS cgz ON cgz.ClaveZona = dt.ClaveZona
            $tipo_join JOIN c_estado AS e1 ON $estado_falla e1.IdEstado = t.TipoReporte
            INNER JOIN c_cliente AS cl ON $morososTicket cl.ClaveCliente = t.ClaveCliente $vendedor $tfs
            LEFT JOIN c_zona AS cgz2 ON cgz2.ClaveZona = cl.ClaveZona 
            LEFT JOIN c_centrocosto AS cc ON t.ClaveCentroCosto = cc.ClaveCentroCosto
            LEFT JOIN c_zona AS cgz3 ON cgz3.ClaveZona = cc.ClaveZona
            LEFT JOIN c_clientegrupo AS clg ON clg.ClaveGrupo = cl.ClaveGrupo
            LEFT JOIN c_tipocliente AS tc ON tc.IdTipoCliente = cl.IdTipoCliente
            $tipo_join_estado JOIN c_estado AS e2 ON $estado e2.IdEstado = t.AreaAtencion  
            LEFT JOIN c_area AS ar2 ON ar2.IdArea = e2.IdArea
            $estadoNota
            LEFT JOIN c_notaticket AS ntCI ON ntCI.IdNotaTicket = (SELECT MAX(IdNotaTicket) FROM c_notaticket AS nt10 WHERE nt10.IdTicket = t.IdTicket AND nt10.IdEstatusAtencion = 51)
            LEFT JOIN c_notaticket AS ntCO ON ntCO.IdNotaTicket = (SELECT MAX(IdNotaTicket) FROM c_notaticket AS nt11 WHERE nt11.IdTicket = t.IdTicket AND nt11.IdEstatusAtencion = 16)
            LEFT JOIN c_estado AS e3 ON nt.IdEstatusAtencion = e3.IdEstado 
            LEFT JOIN c_area AS ar ON ar.IdArea = e3.IdArea            
            LEFT JOIN  c_bitacora AS b ON b.NoSerie = t.NoSerieEquipo
            LEFT JOIN c_domicilio AS dml ON dml.IdDomicilio = (SELECT MAX(IdDomicilio) FROM c_domicilio WHERE ClaveEspecialDomicilio = t.ClaveCentroCosto) 
             ";
            
            $consulta .= ($consulta_aux." ".$tecnico);//Solo en consulta se agregan los filtros de tecnico, no en consulta2
            $consulta2 .= $consulta_aux;  //consulta para mandar tambien tickets no asociados a nadie                      
                        
            $consulta .= " $Where
            $having
            ORDER BY $group_by ISNULL(distance_in_km), distance_in_km ASC, t.IdTicket DESC LIMIT $indice,$registros_por_pagina;";            
            if($Where != ""){
                $Where.= "AND t.IdTicket NOT IN (SELECT DISTINCT IdTicket FROM k_tecnicoticket)";
            }else{
                $Where = "WHERE t.IdTicket NOT IN (SELECT DISTINCT IdTicket FROM k_tecnicoticket)";
            }
            $consulta2 .= " $Where
                $having
                ORDER BY ISNULL(distance_in_km), distance_in_km ASC, t.IdTicket DESC LIMIT $indice,$registros_por_pagina;";
        } else {
            $consulta = "SELECT
            b.id_bitacora,
            t.IdTicket,                                
            t.NoTicketCliente,
            t.NoTicketDistribuidor,
            DATE(t.FechaHora) AS FechaHora,
            t.DescripcionReporte,
            t.NombreCentroCosto,
            t.TipoReporte,
            (SELECT CASE WHEN e2.IdEstado = 2 
            THEN(SELECT group_concat(ClaveEspEquipo SEPARATOR ', ') FROM `c_pedido` WHERE IdTicket = t.IdTicket)
            ELSE t.NoSerieEquipo END) AS NumSerie,
            DATEDIFF(NOW(), t.FechaHora) AS diferencia,
            t.NombreCliente,t.ClaveCliente,
            clg.Nombre AS NombreGrupo,
            cl.IdEstatusCobranza,
            cl.Suspendido,
            e.IdEstadoTicket AS estadoTicket,                                
            tc.IdTipoCliente AS tipoCliente,
            (CASE WHEN !ISNULL(ar.IdArea) THEN ar.Descripcion ELSE ar2.Descripcion END) AS area, 
            (CASE WHEN !ISNULL(ar.IdArea) THEN ar.IdArea ELSE ar2.IdArea END) AS idArea, 
            (SELECT CASE WHEN !ISNULL(cgz.NombreZona) THEN cgz.NombreZona WHEN !ISNULL(cgz3.NombreZona) THEN cgz3.NombreZona ELSE cgz2.NombreZona END) AS ubicacionTicket,
            e3.Nombre AS estadoNota,
            nt.IdEstatusAtencion,
            nt.DiagnosticoSol,
            nt.FechaHora AS FechaNota,
            (CASE WHEN !ISNULL(dt.IdDomicilioTicket) THEN dt.Calle ELSE dml.Calle END) AS Calle,
            (CASE WHEN !ISNULL(dt.IdDomicilioTicket) THEN dt.NoExterior ELSE dml.NoExterior END) AS NoExterior,
            (CASE WHEN !ISNULL(dt.IdDomicilioTicket) THEN dt.NoInterior ELSE dml.NoInterior END) AS NoInterior,
            (CASE WHEN !ISNULL(dt.IdDomicilioTicket) THEN dt.Colonia ELSE dml.Colonia END) AS Colonia,
            (CASE WHEN !ISNULL(dt.IdDomicilioTicket) THEN dt.Ciudad ELSE dml.Ciudad END) AS Ciudad,
            (CASE WHEN !ISNULL(dt.IdDomicilioTicket) THEN dt.Estado ELSE dml.Estado END) AS Estado,
            (CASE WHEN !ISNULL(dt.IdDomicilioTicket) THEN dt.Delegacion ELSE dml.Delegacion END) AS Delegacion,
            (CASE WHEN !ISNULL(dt.IdDomicilioTicket) THEN dt.CodigoPostal ELSE dml.CodigoPostal END) AS CodigoPostal,
            (CASE WHEN !ISNULL(dt.Latitud) THEN dt.Latitud ELSE dml.Latitud END) AS Latitud,
            (CASE WHEN !ISNULL(dt.Longitud) THEN dt.Longitud ELSE dml.Longitud END) AS Longitud,
            $distancia
            t.NombreResp,t.Telefono1Resp,t.CorreoEResp,
            t.NombreAtenc,t.Telefono1Atenc,t.CorreoEAtenc,
            t.FechaCheckIn,t.FechaCheckOut,ntCI.FechaHora AS FechaCheckInReal, ntCO.FechaHora AS FechaC,";
            $consulta2 = $consulta; //consulta para mandar tambien tickets no asociados a nadie           
            $consulta .= $select_prioridad;                        
            $consulta_aux = "(SELECT CASE WHEN ISNULL(nt.UsuarioUltimaModificacion) THEN t.UsuarioUltimaModificacion ELSE nt.UsuarioUltimaModificacion END) as UltimoUsuarioNota,
            t.Resurtido,
            (SELECT GROUP_CONCAT(DISTINCT(NoGuia) SEPARATOR ', ') AS NoGuia FROM `k_enviotoner`
            INNER JOIN c_pedido ON c_pedido.IdPedido = k_enviotoner.IdSolicitud
            INNER JOIN c_ticket ON c_ticket.IdTicket = c_pedido.IdTicket
            WHERE c_ticket.IdTicket = t.IdTicket GROUP BY c_ticket.IdTicket) AS NoGuia
            FROM
            c_ticket AS t
            INNER JOIN c_estadoticket AS e ON e.IdEstadoTicket = t.EstadoDeTicket
            LEFT JOIN c_domicilioticket AS dt ON dt.IdTicket = t.IdTicket
            LEFT JOIN c_zona AS cgz ON cgz.ClaveZona = dt.ClaveZona
            LEFT JOIN c_estado AS e1 ON e1.IdEstado = t.TipoReporte
            INNER JOIN c_cliente AS cl ON cl.ClaveCliente = t.ClaveCliente $vendedor $tfs
            LEFT JOIN c_zona AS cgz2 ON cgz2.ClaveZona = cl.ClaveZona
            LEFT JOIN c_centrocosto AS cc ON t.ClaveCentroCosto = cc.ClaveCentroCosto
            LEFT JOIN c_zona AS cgz3 ON cgz3.ClaveZona = cc.ClaveZona
            LEFT JOIN c_clientegrupo AS clg ON clg.ClaveGrupo = cl.ClaveGrupo
            LEFT JOIN c_tipocliente AS tc ON tc.IdTipoCliente = cl.IdTipoCliente
            $tipo_join_estado JOIN c_estado AS e2 ON $estado e2.IdEstado = t.AreaAtencion   
            LEFT JOIN c_area AS ar2 ON ar2.IdArea = e2.IdArea
            LEFT JOIN c_notaticket AS nt ON nt.IdNotaTicket = (
            SELECT
                    MAX(IdNotaTicket)
            FROM
                    c_notaticket AS nt2
            WHERE
                    nt2.IdTicket = t.IdTicket
            )
            LEFT JOIN c_notaticket AS ntCI ON ntCI.IdNotaTicket = (SELECT MAX(IdNotaTicket) FROM c_notaticket AS nt10 WHERE nt10.IdTicket = t.IdTicket AND nt10.IdEstatusAtencion = 51)
            LEFT JOIN c_notaticket AS ntCO ON ntCO.IdNotaTicket = (SELECT MAX(IdNotaTicket) FROM c_notaticket AS nt11 WHERE nt11.IdTicket = t.IdTicket AND nt10.IdEstatusAtencion = 16)
            LEFT JOIN c_estado AS e3 ON nt.IdEstatusAtencion = e3.IdEstado 
            LEFT JOIN c_area AS ar ON ar.IdArea = e3.IdArea            
            LEFT JOIN  c_bitacora AS b ON b.NoSerie = t.NoSerieEquipo 
            LEFT JOIN c_domicilio AS dml ON dml.IdDomicilio = (SELECT MAX(IdDomicilio) FROM c_domicilio WHERE ClaveEspecialDomicilio = t.ClaveCentroCosto)";
            $consulta .= " $consulta_aux $tecnico ";
            $consulta2 .= $consulta_aux;//consulta para mandar tambien tickets no asociados a nadie            
            
            if (is_numeric($IdTicket)) {
                $consulta_aux=" WHERE (t.IdTicket = $IdTicket OR NoTicketCliente = '$IdTicket' OR NoTicketDistribuidor = '$IdTicket') ";
            } else {
                $consulta_aux=" WHERE (NoTicketCliente = '$IdTicket' OR NoTicketDistribuidor = '$IdTicket') ";
            }
            
            $consulta_aux = " ORDER BY ISNULL(distance_in_km), distance_in_km ASC, t.IdTicket ;";
            $consulta .= $consulta_aux;
            $consulta2 .= $consulta_aux;//consulta para mandar tambien tickets no asociados a nadie
        }
        
        $busqueda = array();
        
        $result = $catalogo->obtenerLista($consulta);
        if(mysql_num_rows($result) == 0){// Si no tiene tickets asignados 
            $parametro = new Parametros();
            $parametro->setEmpresa($empresa);
            if($parametro->getRegistroById(41) && $parametro->getValor() == "1"){//Si se tiene configurado para enviar tickets no asociados a nadie
                $result = $catalogo->obtenerLista($consulta2);
            }
        }
        
        while($rs = mysql_fetch_array($result)){
            $aux = array();
            $aux['Ticket'] = $rs['IdTicket'];
            $aux['Titulo'] = $rs['DescripcionReporte'];
            $aux['Serie'] = $rs['NumSerie'];
            $aux['ClaveCliente'] = $rs['ClaveCliente'];
            $aux['Cliente'] = $rs['NombreCliente'];
            $aux['Localidad'] = $rs['NombreCentroCosto'];
            $direccion = $rs['Calle'] .
                        ", No ext: " . $rs['NoExterior'] . " No. Int: " . $rs['NoInterior'] . ", Col: " . $rs['Colonia'] .
                        ", Del: " . $rs['Delegacion'] . ", " . $rs['Estado'] . ", México, C.P.: " . $rs['CodigoPostal'];
            $aux['Direccion'] = $direccion;
            $aux['Contacto1Nombre'] = $rs['NombreResp'];
            $aux['Contacto1Telefono'] = $rs['Telefono1Resp'];
            
            $aux['Contacto1Email'] = $rs['CorreoEResp'];
            $aux['Contacto2Nombre'] = $rs['NombreAtenc'];
            $aux['Contacto2Telefono'] = $rs['Telefono1Atenc'];
            $aux['Contacto2Email'] = $rs['CorreoEAtenc'];
            
            $aux['Latitud'] = $rs['Latitud'];
            $aux['Longitud'] = $rs['Longitud'];
            $aux['Distancia'] = $rs['distance_in_km'];
            $aux['FechaNota'] = $rs['FechaNota'];
            $aux['Prioridad'] = $rs['Prioridad'];
            $aux['IdTipoPrioridad'] = $rs['IdTipoPrioridad'];
            $aux['TipoPrioridad'] = $rs['TipoPrioridad'];
            $aux['IdColor'] = $rs['IdColor'];
            $aux['Color'] = $rs['Color'];
            $aux['MinutosAlertamiento'] = $rs['MinutosAlertamiento'];
            $aux['IdPuestoEscalamiento'] = $rs['IdPuestoEscalamiento'];
            $aux['MensajeAlerta'] = $rs['MensajeAlerta'];
            $aux['AlertarDespuesFecha'] = $rs['AlertarDespuesFecha'];
            $aux['EstatusNotaAlertamiento'] = $rs['IdEstatusNota'];
            $aux['TiempoDuracion'] = $rs['Duracion'];
            $aux['UnidadMedidaDuracion'] = $rs['Unidad'];
            $aux['FechaHoraInicio'] = $rs['FechaHoraInicio'];
            $aux['CheckIn'] = $rs['FechaCheckInReal'];
            $aux['CheckOut'] = $rs['FechaCheckOutReal'];
            $aux['CheckInProgramado'] = $rs['FechaHoraInicio'];
            $aux['CheckOutProgramado'] = $rs['FechaCheckOut'];
            $aux['IdUltimoEstado'] = $rs['IdEstatusAtencion'];
            array_push($busqueda, $aux);
        }
        $json = array_values($busqueda);
        return json_encode($json);//Se codifica la respuesta en utf-8
    } else {
        return json_encode($resultadoLoggin);
    }
}

$server = new soap_server();
$server->configureWSDL("consultaTicket", "urn:consultaTicket");
$server->register("getTickets", array("IdSession" => "xsd:string", "IdUsuarios" => "xsd:string", "IdEstatus" => "xsd:string",
    "FechaInicio1" => "xsd:string", "FechaFin1" => "xsd:string",
    "Latitud" => "xsd:float", "Longitud" => "xsd:float", "Pagina" => "xsd:int", "IdTicket" => "xsd:int",
    "Serie" => "xsd:string", "MostrarCerrados" => "xsd:int", "MostrarCancelados" => "xsd:int", "MostrarMorosos" => "xsd:int",
    "AreaAtencion" => "xsd:int", "TipoReporte" => "xsd:int", "Cliente" => "xsd:string", "TodosEstatus" => "xsd:int"), array("return" => "xsd:string"), 
        "urn:consultaTicket", "urn:consultaTicket#getTickets", "rpc", "encoded", 
        "Obtiene los tickets segun los filtros especificados");
$server->service($HTTP_RAW_POST_DATA);
?>