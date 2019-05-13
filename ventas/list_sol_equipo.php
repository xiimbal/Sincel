<?php
    session_start();

    if (!isset($_SESSION['user']) || $_SESSION['user'] == "") {
        header("Location: index.php");
    }

    include_once("../WEB-INF/Classes/Catalogo.class.php");
    include_once("../WEB-INF/Classes/Usuario.class.php");
    include_once("../WEB-INF/Classes/PermisosSubMenu.class.php");

    $permisos_grid = new PermisosSubMenu();
    $same_page = "ventas/list_sol_equipo.php";
    $permisos_grid->getPermisosSubmenu($_SESSION['idUsuario'], $same_page);

    $usuario = new Usuario();
    $mostrar = 0;
    if (isset($_GET['mostrar'])) {
        $mostrar = $_GET['mostrar'];
    }
    $catalogo = new Catalogo();
    $query = $catalogo->obtenerLista("SELECT c_puesto.IdPuesto FROM `c_usuario` INNER JOIN c_puesto ON c_usuario.IdPuesto=c_puesto.IdPuesto WHERE c_usuario.IdUsuario=" . $_SESSION['idUsuario']);
    $rs = mysql_fetch_array($query);
    //otra consulta Diego
    $rsHoy = mysql_fetch_array($query);
    $rsProx = mysql_fetch_array($query);
    $rsVen = mysql_fetch_array($query);
    $FechaHoy = date('Y-m-d');
    //fin

    if ($usuario->isUsuarioPuesto($_SESSION['idUsuario'], "24") || $usuario->isUsuarioPuesto($_SESSION['idUsuario'], "27")) {/* Si es de almacen o gerente de almacen */
        
        $mostrar == 1 ? $where = "WHERE (c_solicitud.estatus=1 OR c_solicitud.estatus=5) OR (id_crea = " . $_SESSION['idUsuario'] . ")" : $where = "WHERE (c_solicitud.estatus=1) OR (id_crea = " . $_SESSION['idUsuario'] . ")";
    
    } else {/* Si no es de almacen */
        $mostrar == 1 ? $where = "" : $where = " WHERE (c_solicitud.estatus=0 OR c_solicitud.estatus=1 OR c_solicitud.estatus=2) ";

        if ($rs['IdPuesto'] == 11) {
            $mostrar == 1 ? $where = "WHERE c_solicitud.id_crea=" . $_SESSION['idUsuario'] : $where .= " AND c_solicitud.id_crea=" . $_SESSION['idUsuario'];
        }
    }

    //Modificadas
    $cabeceras = array("Número de solicitud", "Fecha", "Cliente", "Localidades","Fecha Compromiso entrega","Fecha Instalacion","Periodo de Facturacion","Equipos y componentes asignados", "Número de equipos", "Número de componentes", "Tipo", "Venta directa", "Status", "Editar", "Imprimir","Series","Facturar","Cancelar");
    $columnas = array("ID", "Fecha", "Cliente", "localidades","FechaCom","FechaInstalacion","PeriodoFac","EquiComAsig", "NumEquipos", "NumCompo", "TipoSolicitud", "VentaDirecta");


    $consulta ="SELECT
        c_solicitud.fecha_solicitud AS Fecha,
        c_solicitud.FechaCompromiso AS FechaCom,
        c_solicitud.FechaInstalacion AS FechaInstalacion,
        CONCAT( CASE MONTH(c_solicitud.PeriodoFac)
        WHEN 1 THEN 'Enero'
        WHEN 2 THEN 'Febrero'
        WHEN 3 THEN 'Marzo'
        WHEN 4 THEN 'Abril'
        WHEN 5 THEN 'Mayo'
        WHEN 6 THEN 'Junio'
        WHEN 7 THEN 'Julio'
        WHEN 8 THEN 'Agosto'
        WHEN 9 THEN 'Septiembre'
        WHEN 10 THEN 'Octubre'
        WHEN 11 THEN 'Noviembre'
        WHEN 12 THEN 'Diciembre'
        END , ' / ' ,  DATE_FORMAT(c_solicitud.PeriodoFac, '%Y')) AS PeriodoFac,
        c_cliente.NombreRazonSocial AS Cliente,
        c_tiposolicitud.Nombre AS TipoSolicitud,
            CASE
                WHEN SUM(IF(k_solicitud.tipo=0,IF(ISNULL(c_ventadirecta.id_solicitud),k_solicitud.cantidad,FORMAT(k_solicitud.cantidad/2,0) ),0)) > 0 and ( SUM(IF(k_solicitud.tipo=0,IF(ISNULL(c_ventadirecta.id_solicitud),k_solicitud.cantidad,FORMAT(k_solicitud.cantidad/2,0) ),0)) - (SELECT COUNT(c_bitacora.id_solicitud) FROM c_bitacora WHERE c_bitacora.id_solicitud = c_solicitud.id_solicitud)) = 0 and SUM(IF(k_solicitud.tipo=1,IF(ISNULL(c_ventadirecta.id_solicitud),k_solicitud.cantidad,FORMAT(k_solicitud.cantidad/2,0)),0)) = 0 
                    THEN 'Todos los Equipos han sido asignados'
                WHEN SUM(IF(k_solicitud.tipo=0,IF(ISNULL(c_ventadirecta.id_solicitud),k_solicitud.cantidad,FORMAT(k_solicitud.cantidad/2,0) ),0)) > 0 and ( SUM(IF(k_solicitud.tipo=0,IF(ISNULL(c_ventadirecta.id_solicitud),k_solicitud.cantidad,FORMAT(k_solicitud.cantidad/2,0) ),0)) - (SELECT COUNT(c_bitacora.id_solicitud) FROM c_bitacora WHERE c_bitacora.id_solicitud = c_solicitud.id_solicitud)) > 0 and SUM(IF(k_solicitud.tipo=1,IF(ISNULL(c_ventadirecta.id_solicitud),k_solicitud.cantidad,FORMAT(k_solicitud.cantidad/2,0)),0)) = 0 and ( SUM(IF(k_solicitud.tipo=0,IF(ISNULL(c_ventadirecta.id_solicitud),k_solicitud.cantidad,FORMAT(k_solicitud.cantidad/2,0) ),0)) - (SELECT COUNT(c_bitacora.id_solicitud) FROM c_bitacora WHERE c_bitacora.id_solicitud = c_solicitud.id_solicitud)) < SUM(IF(k_solicitud.tipo=0,IF(ISNULL(c_ventadirecta.id_solicitud),k_solicitud.cantidad,FORMAT(k_solicitud.cantidad/2,0) ),0))
                    THEN 'Faltan Equipos por asignar'  
                WHEN SUM(IF(k_solicitud.tipo=0,IF(ISNULL(c_ventadirecta.id_solicitud),k_solicitud.cantidad,FORMAT(k_solicitud.cantidad/2,0) ),0)) > 0 and ( SUM(IF(k_solicitud.tipo=0,IF(ISNULL(c_ventadirecta.id_solicitud),k_solicitud.cantidad,FORMAT(k_solicitud.cantidad/2,0) ),0)) - (SELECT COUNT(c_bitacora.id_solicitud) FROM c_bitacora WHERE c_bitacora.id_solicitud = c_solicitud.id_solicitud)) > 0 and SUM(IF(k_solicitud.tipo=1,IF(ISNULL(c_ventadirecta.id_solicitud),k_solicitud.cantidad,FORMAT(k_solicitud.cantidad/2,0)),0)) = 0 and ( SUM(IF(k_solicitud.tipo=0,IF(ISNULL(c_ventadirecta.id_solicitud),k_solicitud.cantidad,FORMAT(k_solicitud.cantidad/2,0) ),0)) - (SELECT COUNT(c_bitacora.id_solicitud) FROM c_bitacora WHERE c_bitacora.id_solicitud = c_solicitud.id_solicitud)) = SUM(IF(k_solicitud.tipo=0,IF(ISNULL(c_ventadirecta.id_solicitud),k_solicitud.cantidad,FORMAT(k_solicitud.cantidad/2,0) ),0))          
                    THEN 'No hay Equipos Asignados'
                WHEN SUM(IF(k_solicitud.tipo=1,IF(ISNULL(c_ventadirecta.id_solicitud),k_solicitud.cantidad,FORMAT(k_solicitud.cantidad/2,0)),0)) > 0 and (SUM(IF(k_solicitud.tipo=1,IF(ISNULL(c_ventadirecta.id_solicitud),k_solicitud.cantidad,FORMAT(k_solicitud.cantidad/2,0)),0))- SUM(k_solicitud.cantidad_surtida)) = 0 and SUM(IF(k_solicitud.tipo=0,IF(ISNULL(c_ventadirecta.id_solicitud),k_solicitud.cantidad,FORMAT(k_solicitud.cantidad/2,0) ),0)) = 0
                    THEN 'Todos los Componentes han sido asignados'
                WHEN SUM(IF(k_solicitud.tipo=1,IF(ISNULL(c_ventadirecta.id_solicitud),k_solicitud.cantidad,FORMAT(k_solicitud.cantidad/2,0)),0)) > 0 and (SUM(IF(k_solicitud.tipo=1,IF(ISNULL(c_ventadirecta.id_solicitud),k_solicitud.cantidad,FORMAT(k_solicitud.cantidad/2,0)),0))- SUM(k_solicitud.cantidad_surtida)) > 0 and (SUM(IF(k_solicitud.tipo=1,IF(ISNULL(c_ventadirecta.id_solicitud),k_solicitud.cantidad,FORMAT(k_solicitud.cantidad/2,0)),0))- SUM(k_solicitud.cantidad_surtida)) < SUM(IF(k_solicitud.tipo=1,IF(ISNULL(c_ventadirecta.id_solicitud),k_solicitud.cantidad,FORMAT(k_solicitud.cantidad/2,0)),0)) and SUM(IF(k_solicitud.tipo=0,IF(ISNULL(c_ventadirecta.id_solicitud),k_solicitud.cantidad,FORMAT(k_solicitud.cantidad/2,0) ),0)) = 0 
                    THEN 'Faltan Componentes por asignar'
                WHEN SUM(IF(k_solicitud.tipo=1,IF(ISNULL(c_ventadirecta.id_solicitud),k_solicitud.cantidad,FORMAT(k_solicitud.cantidad/2,0)),0)) > 0 and (SUM(IF(k_solicitud.tipo=1,IF(ISNULL(c_ventadirecta.id_solicitud),k_solicitud.cantidad,FORMAT(k_solicitud.cantidad/2,0)),0))- SUM(k_solicitud.cantidad_surtida)) > 0 and (SUM(IF(k_solicitud.tipo=1,IF(ISNULL(c_ventadirecta.id_solicitud),k_solicitud.cantidad,FORMAT(k_solicitud.cantidad/2,0)),0))- SUM(k_solicitud.cantidad_surtida)) = SUM(IF(k_solicitud.tipo=1,IF(ISNULL(c_ventadirecta.id_solicitud),k_solicitud.cantidad,FORMAT(k_solicitud.cantidad/2,0)),0)) and SUM(IF(k_solicitud.tipo=0,IF(ISNULL(c_ventadirecta.id_solicitud),k_solicitud.cantidad,FORMAT(k_solicitud.cantidad/2,0) ),0)) = 0
                    THEN 'No hay Componentes asignados'
                WHEN SUM(IF(k_solicitud.tipo=1,IF(ISNULL(c_ventadirecta.id_solicitud),k_solicitud.cantidad,FORMAT(k_solicitud.cantidad/2,0)),0)) > 0 and SUM(IF(k_solicitud.tipo=0,IF(ISNULL(c_ventadirecta.id_solicitud),k_solicitud.cantidad,FORMAT(k_solicitud.cantidad/2,0) ),0)) > 0 and ( SUM(IF(k_solicitud.tipo=1,IF(ISNULL(c_ventadirecta.id_solicitud),k_solicitud.cantidad,FORMAT(k_solicitud.cantidad/2,0)),0))+ SUM(IF(k_solicitud.tipo=0,IF(ISNULL(c_ventadirecta.id_solicitud),k_solicitud.cantidad,FORMAT(k_solicitud.cantidad/2,0) ),0)) ) - ((SELECT COUNT(c_bitacora.id_solicitud) FROM c_bitacora WHERE c_bitacora.id_solicitud = c_solicitud.id_solicitud)+SUM(k_solicitud.cantidad_surtida) ) = 0 or c_solicitud.id_tiposolicitud = 6
                    THEN 'Todos los Equipos y Componentes han sido asignados' 
                WHEN SUM(IF(k_solicitud.tipo=1,IF(ISNULL(c_ventadirecta.id_solicitud),k_solicitud.cantidad,FORMAT(k_solicitud.cantidad/2,0)),0)) > 0 and SUM(IF(k_solicitud.tipo=0,IF(ISNULL(c_ventadirecta.id_solicitud),k_solicitud.cantidad,FORMAT(k_solicitud.cantidad/2,0) ),0)) > 0 and ( SUM(IF(k_solicitud.tipo=1,IF(ISNULL(c_ventadirecta.id_solicitud),k_solicitud.cantidad,FORMAT(k_solicitud.cantidad/2,0)),0))+ SUM(IF(k_solicitud.tipo=0,IF(ISNULL(c_ventadirecta.id_solicitud),k_solicitud.cantidad,FORMAT(k_solicitud.cantidad/2,0) ),0)) ) - ((SELECT COUNT(c_bitacora.id_solicitud) FROM c_bitacora WHERE c_bitacora.id_solicitud = c_solicitud.id_solicitud)+SUM(k_solicitud.cantidad_surtida) ) > 0 and ((SELECT COUNT(c_bitacora.id_solicitud) FROM c_bitacora WHERE c_bitacora.id_solicitud = c_solicitud.id_solicitud)+SUM(k_solicitud.cantidad_surtida) ) < ( SUM(IF(k_solicitud.tipo=1,IF(ISNULL(c_ventadirecta.id_solicitud),k_solicitud.cantidad,FORMAT(k_solicitud.cantidad/2,0)),0))+ SUM(IF(k_solicitud.tipo=0,IF(ISNULL(c_ventadirecta.id_solicitud),k_solicitud.cantidad,FORMAT(k_solicitud.cantidad/2,0) ),0)) )
                    THEN 'Faltan Equipos y Componentes por asignar'
                WHEN SUM(IF(k_solicitud.tipo=1,IF(ISNULL(c_ventadirecta.id_solicitud),k_solicitud.cantidad,FORMAT(k_solicitud.cantidad/2,0)),0)) > 0 and SUM(IF(k_solicitud.tipo=0,IF(ISNULL(c_ventadirecta.id_solicitud),k_solicitud.cantidad,FORMAT(k_solicitud.cantidad/2,0) ),0)) > 0 and ( SUM(IF(k_solicitud.tipo=1,IF(ISNULL(c_ventadirecta.id_solicitud),k_solicitud.cantidad,FORMAT(k_solicitud.cantidad/2,0)),0))+ SUM(IF(k_solicitud.tipo=0,IF(ISNULL(c_ventadirecta.id_solicitud),k_solicitud.cantidad,FORMAT(k_solicitud.cantidad/2,0) ),0)) ) - ((SELECT COUNT(c_bitacora.id_solicitud) FROM c_bitacora WHERE c_bitacora.id_solicitud = c_solicitud.id_solicitud)+SUM(k_solicitud.cantidad_surtida) ) = ( SUM(IF(k_solicitud.tipo=1,IF(ISNULL(c_ventadirecta.id_solicitud),k_solicitud.cantidad,FORMAT(k_solicitud.cantidad/2,0)),0))+ SUM(IF(k_solicitud.tipo=0,IF(ISNULL(c_ventadirecta.id_solicitud),k_solicitud.cantidad,FORMAT(k_solicitud.cantidad/2,0) ),0)) )
                    THEN 'No hay Equipos ni Componentes asignados' 
                ELSE 'Error'
            END AS EquiComAsig,     
        (CONCAT(u.Nombre,' ',u.ApellidoPaterno,' ',u.ApellidoMaterno)) AS UsuarioAutorizo,
        c_solicitud.id_tiposolicitud AS IdTipoSolicitud,
        (SELECT CASE WHEN !ISNULL(c_ventadirecta.IdVentaDirecta) THEN c_ventadirecta.IdVentaDirecta ELSE 'N/A' END) AS VentaDirecta,
        (SELECT CASE WHEN !ISNULL(c_ventadirecta.id_prefactura) THEN c_ventadirecta.id_prefactura ELSE '' END) AS Prefactura,
        SUM(IF(k_solicitud.tipo=0,IF(ISNULL(c_ventadirecta.id_solicitud),k_solicitud.cantidad,FORMAT(k_solicitud.cantidad/2,0) ),0)) AS NumEquipos,
        SUM(IF(k_solicitud.tipo=1,IF(ISNULL(c_ventadirecta.id_solicitud),k_solicitud.cantidad,FORMAT(k_solicitud.cantidad/2,0)),0)) AS NumCompo,
        es.NombreEstatus  AS Status,
        c_solicitud.id_solicitud AS ID,
        c_solicitud.estatus AS idEstatus,
        (SELECT group_concat(Nombre separator ', ') FROM c_centrocosto AS cc, k_solicitud AS ks
        WHERE ks.id_solicitud = c_solicitud.id_solicitud AND cc.ClaveCentroCosto = ks.ClaveCentroCosto GROUP BY k_solicitud.id_solicitud) AS localidades
        FROM c_solicitud
        INNER JOIN k_solicitud ON k_solicitud.id_solicitud = c_solicitud.id_solicitud
        INNER JOIN c_cliente ON c_solicitud.ClaveCliente = c_cliente.ClaveCliente
        INNER JOIN c_tiposolicitud ON c_tiposolicitud.IdTipoMovimiento = c_solicitud.id_tiposolicitud
        LEFT JOIN c_usuario AS u ON u.IdUsuario = c_solicitud.id_autoriza
        LEFT JOIN c_ventadirecta ON c_ventadirecta.id_solicitud = c_solicitud.id_solicitud
        LEFT JOIN c_estatussolicitud AS es ON es.IdEstatusSolicitud = c_solicitud.estatus
        $where
        GROUP BY ID DESC;";
        //Aqui termina

    $query = $catalogo->obtenerLista($consulta);
    $numeroSolicitudes = mysql_num_rows($query);
    //Consulta para latabla de solicitudes para hoy

    $consultaHoy = "SELECT
        c_solicitud.fecha_solicitud AS Fecha,
        c_solicitud.FechaCompromiso AS FechaCom,
        c_solicitud.FechaInstalacion AS FechaInstalacion,
        c_solicitud.FechaAlarm AS FechaAlarm,
        CONCAT( CASE MONTH(c_solicitud.PeriodoFac)
        WHEN 1 THEN 'Enero'
        WHEN 2 THEN 'Febrero'
        WHEN 3 THEN 'Marzo'
        WHEN 4 THEN 'Abril'
        WHEN 5 THEN 'Mayo'
        WHEN 6 THEN 'Junio'
        WHEN 7 THEN 'Julio'
        WHEN 8 THEN 'Agosto'
        WHEN 9 THEN 'Septiembre'
        WHEN 10 THEN 'Octubre'
        WHEN 11 THEN 'Noviembre'
        WHEN 12 THEN 'Diciembre'
        END , ' / ' ,  DATE_FORMAT(c_solicitud.PeriodoFac, '%Y')) AS PeriodoFac,
        c_cliente.NombreRazonSocial AS Cliente,
        c_tiposolicitud.Nombre AS TipoSolicitud,
            CASE
                WHEN SUM(IF(k_solicitud.tipo=0,IF(ISNULL(c_ventadirecta.id_solicitud),k_solicitud.cantidad,FORMAT(k_solicitud.cantidad/2,0) ),0)) > 0 and ( SUM(IF(k_solicitud.tipo=0,IF(ISNULL(c_ventadirecta.id_solicitud),k_solicitud.cantidad,FORMAT(k_solicitud.cantidad/2,0) ),0)) - (SELECT COUNT(c_bitacora.id_solicitud) FROM c_bitacora WHERE c_bitacora.id_solicitud = c_solicitud.id_solicitud)) = 0 and SUM(IF(k_solicitud.tipo=1,IF(ISNULL(c_ventadirecta.id_solicitud),k_solicitud.cantidad,FORMAT(k_solicitud.cantidad/2,0)),0)) = 0
                    THEN 'Todos los Equipos han sido asignados'
                WHEN SUM(IF(k_solicitud.tipo=0,IF(ISNULL(c_ventadirecta.id_solicitud),k_solicitud.cantidad,FORMAT(k_solicitud.cantidad/2,0) ),0)) > 0 and ( SUM(IF(k_solicitud.tipo=0,IF(ISNULL(c_ventadirecta.id_solicitud),k_solicitud.cantidad,FORMAT(k_solicitud.cantidad/2,0) ),0)) - (SELECT COUNT(c_bitacora.id_solicitud) FROM c_bitacora WHERE c_bitacora.id_solicitud = c_solicitud.id_solicitud)) > 0 and SUM(IF(k_solicitud.tipo=1,IF(ISNULL(c_ventadirecta.id_solicitud),k_solicitud.cantidad,FORMAT(k_solicitud.cantidad/2,0)),0)) = 0 and ( SUM(IF(k_solicitud.tipo=0,IF(ISNULL(c_ventadirecta.id_solicitud),k_solicitud.cantidad,FORMAT(k_solicitud.cantidad/2,0) ),0)) - (SELECT COUNT(c_bitacora.id_solicitud) FROM c_bitacora WHERE c_bitacora.id_solicitud = c_solicitud.id_solicitud)) < SUM(IF(k_solicitud.tipo=0,IF(ISNULL(c_ventadirecta.id_solicitud),k_solicitud.cantidad,FORMAT(k_solicitud.cantidad/2,0) ),0))
                    THEN 'Faltan Equipos por asignar'  
                WHEN SUM(IF(k_solicitud.tipo=0,IF(ISNULL(c_ventadirecta.id_solicitud),k_solicitud.cantidad,FORMAT(k_solicitud.cantidad/2,0) ),0)) > 0 and ( SUM(IF(k_solicitud.tipo=0,IF(ISNULL(c_ventadirecta.id_solicitud),k_solicitud.cantidad,FORMAT(k_solicitud.cantidad/2,0) ),0)) - (SELECT COUNT(c_bitacora.id_solicitud) FROM c_bitacora WHERE c_bitacora.id_solicitud = c_solicitud.id_solicitud)) > 0 and SUM(IF(k_solicitud.tipo=1,IF(ISNULL(c_ventadirecta.id_solicitud),k_solicitud.cantidad,FORMAT(k_solicitud.cantidad/2,0)),0)) = 0 and ( SUM(IF(k_solicitud.tipo=0,IF(ISNULL(c_ventadirecta.id_solicitud),k_solicitud.cantidad,FORMAT(k_solicitud.cantidad/2,0) ),0)) - (SELECT COUNT(c_bitacora.id_solicitud) FROM c_bitacora WHERE c_bitacora.id_solicitud = c_solicitud.id_solicitud)) = SUM(IF(k_solicitud.tipo=0,IF(ISNULL(c_ventadirecta.id_solicitud),k_solicitud.cantidad,FORMAT(k_solicitud.cantidad/2,0) ),0))          
                    THEN 'No hay Equipos Asignados'
                WHEN SUM(IF(k_solicitud.tipo=1,IF(ISNULL(c_ventadirecta.id_solicitud),k_solicitud.cantidad,FORMAT(k_solicitud.cantidad/2,0)),0)) > 0 and (SUM(IF(k_solicitud.tipo=1,IF(ISNULL(c_ventadirecta.id_solicitud),k_solicitud.cantidad,FORMAT(k_solicitud.cantidad/2,0)),0))- SUM(k_solicitud.cantidad_surtida)) = 0 and SUM(IF(k_solicitud.tipo=0,IF(ISNULL(c_ventadirecta.id_solicitud),k_solicitud.cantidad,FORMAT(k_solicitud.cantidad/2,0) ),0)) = 0
                    THEN 'Todos los Componentes han sido asignados'
                WHEN SUM(IF(k_solicitud.tipo=1,IF(ISNULL(c_ventadirecta.id_solicitud),k_solicitud.cantidad,FORMAT(k_solicitud.cantidad/2,0)),0)) > 0 and (SUM(IF(k_solicitud.tipo=1,IF(ISNULL(c_ventadirecta.id_solicitud),k_solicitud.cantidad,FORMAT(k_solicitud.cantidad/2,0)),0))- SUM(k_solicitud.cantidad_surtida)) > 0 and (SUM(IF(k_solicitud.tipo=1,IF(ISNULL(c_ventadirecta.id_solicitud),k_solicitud.cantidad,FORMAT(k_solicitud.cantidad/2,0)),0))- SUM(k_solicitud.cantidad_surtida)) < SUM(IF(k_solicitud.tipo=1,IF(ISNULL(c_ventadirecta.id_solicitud),k_solicitud.cantidad,FORMAT(k_solicitud.cantidad/2,0)),0)) and SUM(IF(k_solicitud.tipo=0,IF(ISNULL(c_ventadirecta.id_solicitud),k_solicitud.cantidad,FORMAT(k_solicitud.cantidad/2,0) ),0)) = 0 
                    THEN 'Faltan Componentes por asignar'
                WHEN SUM(IF(k_solicitud.tipo=1,IF(ISNULL(c_ventadirecta.id_solicitud),k_solicitud.cantidad,FORMAT(k_solicitud.cantidad/2,0)),0)) > 0 and (SUM(IF(k_solicitud.tipo=1,IF(ISNULL(c_ventadirecta.id_solicitud),k_solicitud.cantidad,FORMAT(k_solicitud.cantidad/2,0)),0))- SUM(k_solicitud.cantidad_surtida)) > 0 and (SUM(IF(k_solicitud.tipo=1,IF(ISNULL(c_ventadirecta.id_solicitud),k_solicitud.cantidad,FORMAT(k_solicitud.cantidad/2,0)),0))- SUM(k_solicitud.cantidad_surtida)) = SUM(IF(k_solicitud.tipo=1,IF(ISNULL(c_ventadirecta.id_solicitud),k_solicitud.cantidad,FORMAT(k_solicitud.cantidad/2,0)),0)) and SUM(IF(k_solicitud.tipo=0,IF(ISNULL(c_ventadirecta.id_solicitud),k_solicitud.cantidad,FORMAT(k_solicitud.cantidad/2,0) ),0)) = 0
                    THEN 'No hay Componentes asignados'
                WHEN SUM(IF(k_solicitud.tipo=1,IF(ISNULL(c_ventadirecta.id_solicitud),k_solicitud.cantidad,FORMAT(k_solicitud.cantidad/2,0)),0)) > 0 and SUM(IF(k_solicitud.tipo=0,IF(ISNULL(c_ventadirecta.id_solicitud),k_solicitud.cantidad,FORMAT(k_solicitud.cantidad/2,0) ),0)) > 0 and ( SUM(IF(k_solicitud.tipo=1,IF(ISNULL(c_ventadirecta.id_solicitud),k_solicitud.cantidad,FORMAT(k_solicitud.cantidad/2,0)),0))+ SUM(IF(k_solicitud.tipo=0,IF(ISNULL(c_ventadirecta.id_solicitud),k_solicitud.cantidad,FORMAT(k_solicitud.cantidad/2,0) ),0)) ) - ((SELECT COUNT(c_bitacora.id_solicitud) FROM c_bitacora WHERE c_bitacora.id_solicitud = c_solicitud.id_solicitud)+SUM(k_solicitud.cantidad_surtida) ) = 0 or c_solicitud.id_tiposolicitud = 6
                    THEN 'Todos los Equipos y Componentes han sido asignados' 
                WHEN SUM(IF(k_solicitud.tipo=1,IF(ISNULL(c_ventadirecta.id_solicitud),k_solicitud.cantidad,FORMAT(k_solicitud.cantidad/2,0)),0)) > 0 and SUM(IF(k_solicitud.tipo=0,IF(ISNULL(c_ventadirecta.id_solicitud),k_solicitud.cantidad,FORMAT(k_solicitud.cantidad/2,0) ),0)) > 0 and ( SUM(IF(k_solicitud.tipo=1,IF(ISNULL(c_ventadirecta.id_solicitud),k_solicitud.cantidad,FORMAT(k_solicitud.cantidad/2,0)),0))+ SUM(IF(k_solicitud.tipo=0,IF(ISNULL(c_ventadirecta.id_solicitud),k_solicitud.cantidad,FORMAT(k_solicitud.cantidad/2,0) ),0)) ) - ((SELECT COUNT(c_bitacora.id_solicitud) FROM c_bitacora WHERE c_bitacora.id_solicitud = c_solicitud.id_solicitud)+SUM(k_solicitud.cantidad_surtida) ) > 0 and ((SELECT COUNT(c_bitacora.id_solicitud) FROM c_bitacora WHERE c_bitacora.id_solicitud = c_solicitud.id_solicitud)+SUM(k_solicitud.cantidad_surtida) ) < ( SUM(IF(k_solicitud.tipo=1,IF(ISNULL(c_ventadirecta.id_solicitud),k_solicitud.cantidad,FORMAT(k_solicitud.cantidad/2,0)),0))+ SUM(IF(k_solicitud.tipo=0,IF(ISNULL(c_ventadirecta.id_solicitud),k_solicitud.cantidad,FORMAT(k_solicitud.cantidad/2,0) ),0)) )
                    THEN 'Faltan Equipos y Componentes por asignar'
                WHEN SUM(IF(k_solicitud.tipo=1,IF(ISNULL(c_ventadirecta.id_solicitud),k_solicitud.cantidad,FORMAT(k_solicitud.cantidad/2,0)),0)) > 0 and SUM(IF(k_solicitud.tipo=0,IF(ISNULL(c_ventadirecta.id_solicitud),k_solicitud.cantidad,FORMAT(k_solicitud.cantidad/2,0) ),0)) > 0 and ( SUM(IF(k_solicitud.tipo=1,IF(ISNULL(c_ventadirecta.id_solicitud),k_solicitud.cantidad,FORMAT(k_solicitud.cantidad/2,0)),0))+ SUM(IF(k_solicitud.tipo=0,IF(ISNULL(c_ventadirecta.id_solicitud),k_solicitud.cantidad,FORMAT(k_solicitud.cantidad/2,0) ),0)) ) - ((SELECT COUNT(c_bitacora.id_solicitud) FROM c_bitacora WHERE c_bitacora.id_solicitud = c_solicitud.id_solicitud)+SUM(k_solicitud.cantidad_surtida) ) = ( SUM(IF(k_solicitud.tipo=1,IF(ISNULL(c_ventadirecta.id_solicitud),k_solicitud.cantidad,FORMAT(k_solicitud.cantidad/2,0)),0))+ SUM(IF(k_solicitud.tipo=0,IF(ISNULL(c_ventadirecta.id_solicitud),k_solicitud.cantidad,FORMAT(k_solicitud.cantidad/2,0) ),0)) )
                    THEN 'No hay Equipos ni Componentes asignados' 
                ELSE 'Error'
            END AS EquiComAsig, 
        (CONCAT(u.Nombre,' ',u.ApellidoPaterno,' ',u.ApellidoMaterno)) AS UsuarioAutorizo,
        c_solicitud.id_tiposolicitud AS IdTipoSolicitud,
        (SELECT CASE WHEN !ISNULL(c_ventadirecta.IdVentaDirecta) THEN c_ventadirecta.IdVentaDirecta ELSE 'N/A' END) AS VentaDirecta,
        (SELECT CASE WHEN !ISNULL(c_ventadirecta.id_prefactura) THEN c_ventadirecta.id_prefactura ELSE '' END) AS Prefactura,
        SUM(IF(k_solicitud.tipo=0,IF(ISNULL(c_ventadirecta.id_solicitud),k_solicitud.cantidad,FORMAT(k_solicitud.cantidad/2,0) ),0)) AS NumEquipos,
        SUM(IF(k_solicitud.tipo=1,IF(ISNULL(c_ventadirecta.id_solicitud),k_solicitud.cantidad,FORMAT(k_solicitud.cantidad/2,0)),0)) AS NumCompo,
        es.NombreEstatus  AS Status,
        c_solicitud.id_solicitud AS ID,
        c_solicitud.estatus AS idEstatus,
        (SELECT group_concat(Nombre separator ', ') FROM c_centrocosto AS cc, k_solicitud AS ks
        WHERE ks.id_solicitud = c_solicitud.id_solicitud AND cc.ClaveCentroCosto = ks.ClaveCentroCosto GROUP BY k_solicitud.id_solicitud) AS localidades
        FROM c_solicitud
        INNER JOIN k_solicitud ON k_solicitud.id_solicitud = c_solicitud.id_solicitud
        INNER JOIN c_cliente ON c_solicitud.ClaveCliente = c_cliente.ClaveCliente
        INNER JOIN c_tiposolicitud ON c_tiposolicitud.IdTipoMovimiento = c_solicitud.id_tiposolicitud
        LEFT JOIN c_usuario AS u ON u.IdUsuario = c_solicitud.id_autoriza
        LEFT JOIN c_ventadirecta ON c_ventadirecta.id_solicitud = c_solicitud.id_solicitud
        LEFT JOIN c_estatussolicitud AS es ON es.IdEstatusSolicitud = c_solicitud.estatus
        $where AND FechaInstalacion = '".$FechaHoy."'
        GROUP BY ID DESC;";


    $queryHoy = $catalogo->obtenerLista($consultaHoy);
    $numSolHoy = mysql_num_rows($queryHoy);

    //Consulta para la tabla de Solicitudes proximas
    $consultaProx = "SELECT
        c_solicitud.fecha_solicitud AS Fecha,
        c_solicitud.FechaCompromiso AS FechaCom,
        c_solicitud.FechaInstalacion AS FechaInstalacion,
        c_solicitud.FechaAlarm AS FechaAlarm,
        CONCAT( CASE MONTH(c_solicitud.PeriodoFac)
        WHEN 1 THEN 'Enero'
        WHEN 2 THEN 'Febrero'
        WHEN 3 THEN 'Marzo'
        WHEN 4 THEN 'Abril'
        WHEN 5 THEN 'Mayo'
        WHEN 6 THEN 'Junio'
        WHEN 7 THEN 'Julio'
        WHEN 8 THEN 'Agosto'
        WHEN 9 THEN 'Septiembre'
        WHEN 10 THEN 'Octubre'
        WHEN 11 THEN 'Noviembre'
        WHEN 12 THEN 'Diciembre'
        END , ' / ' ,  DATE_FORMAT(c_solicitud.PeriodoFac, '%Y')) AS PeriodoFac,
        c_cliente.NombreRazonSocial AS Cliente,
        c_tiposolicitud.Nombre AS TipoSolicitud,
            CASE
                WHEN SUM(IF(k_solicitud.tipo=0,IF(ISNULL(c_ventadirecta.id_solicitud),k_solicitud.cantidad,FORMAT(k_solicitud.cantidad/2,0) ),0)) > 0 and ( SUM(IF(k_solicitud.tipo=0,IF(ISNULL(c_ventadirecta.id_solicitud),k_solicitud.cantidad,FORMAT(k_solicitud.cantidad/2,0) ),0)) - (SELECT COUNT(c_bitacora.id_solicitud) FROM c_bitacora WHERE c_bitacora.id_solicitud = c_solicitud.id_solicitud)) = 0 and SUM(IF(k_solicitud.tipo=1,IF(ISNULL(c_ventadirecta.id_solicitud),k_solicitud.cantidad,FORMAT(k_solicitud.cantidad/2,0)),0)) = 0
                    THEN 'Todos los Equipos han sido asignados'
                WHEN SUM(IF(k_solicitud.tipo=0,IF(ISNULL(c_ventadirecta.id_solicitud),k_solicitud.cantidad,FORMAT(k_solicitud.cantidad/2,0) ),0)) > 0 and ( SUM(IF(k_solicitud.tipo=0,IF(ISNULL(c_ventadirecta.id_solicitud),k_solicitud.cantidad,FORMAT(k_solicitud.cantidad/2,0) ),0)) - (SELECT COUNT(c_bitacora.id_solicitud) FROM c_bitacora WHERE c_bitacora.id_solicitud = c_solicitud.id_solicitud)) > 0 and SUM(IF(k_solicitud.tipo=1,IF(ISNULL(c_ventadirecta.id_solicitud),k_solicitud.cantidad,FORMAT(k_solicitud.cantidad/2,0)),0)) = 0 and ( SUM(IF(k_solicitud.tipo=0,IF(ISNULL(c_ventadirecta.id_solicitud),k_solicitud.cantidad,FORMAT(k_solicitud.cantidad/2,0) ),0)) - (SELECT COUNT(c_bitacora.id_solicitud) FROM c_bitacora WHERE c_bitacora.id_solicitud = c_solicitud.id_solicitud)) < SUM(IF(k_solicitud.tipo=0,IF(ISNULL(c_ventadirecta.id_solicitud),k_solicitud.cantidad,FORMAT(k_solicitud.cantidad/2,0) ),0))
                    THEN 'Faltan Equipos por asignar'  
                WHEN SUM(IF(k_solicitud.tipo=0,IF(ISNULL(c_ventadirecta.id_solicitud),k_solicitud.cantidad,FORMAT(k_solicitud.cantidad/2,0) ),0)) > 0 and ( SUM(IF(k_solicitud.tipo=0,IF(ISNULL(c_ventadirecta.id_solicitud),k_solicitud.cantidad,FORMAT(k_solicitud.cantidad/2,0) ),0)) - (SELECT COUNT(c_bitacora.id_solicitud) FROM c_bitacora WHERE c_bitacora.id_solicitud = c_solicitud.id_solicitud)) > 0 and SUM(IF(k_solicitud.tipo=1,IF(ISNULL(c_ventadirecta.id_solicitud),k_solicitud.cantidad,FORMAT(k_solicitud.cantidad/2,0)),0)) = 0 and ( SUM(IF(k_solicitud.tipo=0,IF(ISNULL(c_ventadirecta.id_solicitud),k_solicitud.cantidad,FORMAT(k_solicitud.cantidad/2,0) ),0)) - (SELECT COUNT(c_bitacora.id_solicitud) FROM c_bitacora WHERE c_bitacora.id_solicitud = c_solicitud.id_solicitud)) = SUM(IF(k_solicitud.tipo=0,IF(ISNULL(c_ventadirecta.id_solicitud),k_solicitud.cantidad,FORMAT(k_solicitud.cantidad/2,0) ),0))          
                    THEN 'No hay Equipos Asignados'
                WHEN SUM(IF(k_solicitud.tipo=1,IF(ISNULL(c_ventadirecta.id_solicitud),k_solicitud.cantidad,FORMAT(k_solicitud.cantidad/2,0)),0)) > 0 and (SUM(IF(k_solicitud.tipo=1,IF(ISNULL(c_ventadirecta.id_solicitud),k_solicitud.cantidad,FORMAT(k_solicitud.cantidad/2,0)),0))- SUM(k_solicitud.cantidad_surtida)) = 0 and SUM(IF(k_solicitud.tipo=0,IF(ISNULL(c_ventadirecta.id_solicitud),k_solicitud.cantidad,FORMAT(k_solicitud.cantidad/2,0) ),0)) = 0 
                    THEN 'Todos los Componentes han sido asignados'
                WHEN SUM(IF(k_solicitud.tipo=1,IF(ISNULL(c_ventadirecta.id_solicitud),k_solicitud.cantidad,FORMAT(k_solicitud.cantidad/2,0)),0)) > 0 and (SUM(IF(k_solicitud.tipo=1,IF(ISNULL(c_ventadirecta.id_solicitud),k_solicitud.cantidad,FORMAT(k_solicitud.cantidad/2,0)),0))- SUM(k_solicitud.cantidad_surtida)) > 0 and (SUM(IF(k_solicitud.tipo=1,IF(ISNULL(c_ventadirecta.id_solicitud),k_solicitud.cantidad,FORMAT(k_solicitud.cantidad/2,0)),0))- SUM(k_solicitud.cantidad_surtida)) < SUM(IF(k_solicitud.tipo=1,IF(ISNULL(c_ventadirecta.id_solicitud),k_solicitud.cantidad,FORMAT(k_solicitud.cantidad/2,0)),0)) and SUM(IF(k_solicitud.tipo=0,IF(ISNULL(c_ventadirecta.id_solicitud),k_solicitud.cantidad,FORMAT(k_solicitud.cantidad/2,0) ),0)) = 0 
                    THEN 'Faltan Componentes por asignar'
                WHEN SUM(IF(k_solicitud.tipo=1,IF(ISNULL(c_ventadirecta.id_solicitud),k_solicitud.cantidad,FORMAT(k_solicitud.cantidad/2,0)),0)) > 0 and (SUM(IF(k_solicitud.tipo=1,IF(ISNULL(c_ventadirecta.id_solicitud),k_solicitud.cantidad,FORMAT(k_solicitud.cantidad/2,0)),0))- SUM(k_solicitud.cantidad_surtida)) > 0 and (SUM(IF(k_solicitud.tipo=1,IF(ISNULL(c_ventadirecta.id_solicitud),k_solicitud.cantidad,FORMAT(k_solicitud.cantidad/2,0)),0))- SUM(k_solicitud.cantidad_surtida)) = SUM(IF(k_solicitud.tipo=1,IF(ISNULL(c_ventadirecta.id_solicitud),k_solicitud.cantidad,FORMAT(k_solicitud.cantidad/2,0)),0)) and SUM(IF(k_solicitud.tipo=0,IF(ISNULL(c_ventadirecta.id_solicitud),k_solicitud.cantidad,FORMAT(k_solicitud.cantidad/2,0) ),0)) = 0
                    THEN 'No hay Componentes asignados'
                WHEN SUM(IF(k_solicitud.tipo=1,IF(ISNULL(c_ventadirecta.id_solicitud),k_solicitud.cantidad,FORMAT(k_solicitud.cantidad/2,0)),0)) > 0 and SUM(IF(k_solicitud.tipo=0,IF(ISNULL(c_ventadirecta.id_solicitud),k_solicitud.cantidad,FORMAT(k_solicitud.cantidad/2,0) ),0)) > 0 and ( SUM(IF(k_solicitud.tipo=1,IF(ISNULL(c_ventadirecta.id_solicitud),k_solicitud.cantidad,FORMAT(k_solicitud.cantidad/2,0)),0))+ SUM(IF(k_solicitud.tipo=0,IF(ISNULL(c_ventadirecta.id_solicitud),k_solicitud.cantidad,FORMAT(k_solicitud.cantidad/2,0) ),0)) ) - ((SELECT COUNT(c_bitacora.id_solicitud) FROM c_bitacora WHERE c_bitacora.id_solicitud = c_solicitud.id_solicitud)+SUM(k_solicitud.cantidad_surtida) ) = 0 or c_solicitud.id_tiposolicitud = 6
                    THEN 'Todos los Equipos y Componentes han sido asignados' 
                WHEN SUM(IF(k_solicitud.tipo=1,IF(ISNULL(c_ventadirecta.id_solicitud),k_solicitud.cantidad,FORMAT(k_solicitud.cantidad/2,0)),0)) > 0 and SUM(IF(k_solicitud.tipo=0,IF(ISNULL(c_ventadirecta.id_solicitud),k_solicitud.cantidad,FORMAT(k_solicitud.cantidad/2,0) ),0)) > 0 and ( SUM(IF(k_solicitud.tipo=1,IF(ISNULL(c_ventadirecta.id_solicitud),k_solicitud.cantidad,FORMAT(k_solicitud.cantidad/2,0)),0))+ SUM(IF(k_solicitud.tipo=0,IF(ISNULL(c_ventadirecta.id_solicitud),k_solicitud.cantidad,FORMAT(k_solicitud.cantidad/2,0) ),0)) ) - ((SELECT COUNT(c_bitacora.id_solicitud) FROM c_bitacora WHERE c_bitacora.id_solicitud = c_solicitud.id_solicitud)+SUM(k_solicitud.cantidad_surtida) ) > 0 and ((SELECT COUNT(c_bitacora.id_solicitud) FROM c_bitacora WHERE c_bitacora.id_solicitud = c_solicitud.id_solicitud)+SUM(k_solicitud.cantidad_surtida) ) < ( SUM(IF(k_solicitud.tipo=1,IF(ISNULL(c_ventadirecta.id_solicitud),k_solicitud.cantidad,FORMAT(k_solicitud.cantidad/2,0)),0))+ SUM(IF(k_solicitud.tipo=0,IF(ISNULL(c_ventadirecta.id_solicitud),k_solicitud.cantidad,FORMAT(k_solicitud.cantidad/2,0) ),0)) )
                    THEN 'Faltan Equipos y Componentes por asignar'
                WHEN SUM(IF(k_solicitud.tipo=1,IF(ISNULL(c_ventadirecta.id_solicitud),k_solicitud.cantidad,FORMAT(k_solicitud.cantidad/2,0)),0)) > 0 and SUM(IF(k_solicitud.tipo=0,IF(ISNULL(c_ventadirecta.id_solicitud),k_solicitud.cantidad,FORMAT(k_solicitud.cantidad/2,0) ),0)) > 0 and ( SUM(IF(k_solicitud.tipo=1,IF(ISNULL(c_ventadirecta.id_solicitud),k_solicitud.cantidad,FORMAT(k_solicitud.cantidad/2,0)),0))+ SUM(IF(k_solicitud.tipo=0,IF(ISNULL(c_ventadirecta.id_solicitud),k_solicitud.cantidad,FORMAT(k_solicitud.cantidad/2,0) ),0)) ) - ((SELECT COUNT(c_bitacora.id_solicitud) FROM c_bitacora WHERE c_bitacora.id_solicitud = c_solicitud.id_solicitud)+SUM(k_solicitud.cantidad_surtida) ) = ( SUM(IF(k_solicitud.tipo=1,IF(ISNULL(c_ventadirecta.id_solicitud),k_solicitud.cantidad,FORMAT(k_solicitud.cantidad/2,0)),0))+ SUM(IF(k_solicitud.tipo=0,IF(ISNULL(c_ventadirecta.id_solicitud),k_solicitud.cantidad,FORMAT(k_solicitud.cantidad/2,0) ),0)) )
                    THEN 'No hay Equipos ni Componentes asignados' 
                ELSE 'Error'
            END AS EquiComAsig, 
        (CONCAT(u.Nombre,' ',u.ApellidoPaterno,' ',u.ApellidoMaterno)) AS UsuarioAutorizo,
        c_solicitud.id_tiposolicitud AS IdTipoSolicitud,
        (SELECT CASE WHEN !ISNULL(c_ventadirecta.IdVentaDirecta) THEN c_ventadirecta.IdVentaDirecta ELSE 'N/A' END) AS VentaDirecta,
        (SELECT CASE WHEN !ISNULL(c_ventadirecta.id_prefactura) THEN c_ventadirecta.id_prefactura ELSE '' END) AS Prefactura,
        SUM(IF(k_solicitud.tipo=0,IF(ISNULL(c_ventadirecta.id_solicitud),k_solicitud.cantidad,FORMAT(k_solicitud.cantidad/2,0) ),0)) AS NumEquipos,
        SUM(IF(k_solicitud.tipo=1,IF(ISNULL(c_ventadirecta.id_solicitud),k_solicitud.cantidad,FORMAT(k_solicitud.cantidad/2,0)),0)) AS NumCompo,
        es.NombreEstatus  AS Status,
        c_solicitud.id_solicitud AS ID,
        c_solicitud.estatus AS idEstatus,
        (SELECT group_concat(Nombre separator ', ') FROM c_centrocosto AS cc, k_solicitud AS ks
        WHERE ks.id_solicitud = c_solicitud.id_solicitud AND cc.ClaveCentroCosto = ks.ClaveCentroCosto GROUP BY k_solicitud.id_solicitud) AS localidades
        FROM c_solicitud
        INNER JOIN k_solicitud ON k_solicitud.id_solicitud = c_solicitud.id_solicitud
        INNER JOIN c_cliente ON c_solicitud.ClaveCliente = c_cliente.ClaveCliente
        INNER JOIN c_tiposolicitud ON c_tiposolicitud.IdTipoMovimiento = c_solicitud.id_tiposolicitud
        LEFT JOIN c_usuario AS u ON u.IdUsuario = c_solicitud.id_autoriza
        LEFT JOIN c_ventadirecta ON c_ventadirecta.id_solicitud = c_solicitud.id_solicitud
        LEFT JOIN c_estatussolicitud AS es ON es.IdEstatusSolicitud = c_solicitud.estatus
        $where AND FechaInstalacion > '".$FechaHoy."'
        GROUP BY ID DESC;";

    $queryProx = $catalogo->obtenerLista($consultaProx);
    $numSolProx = mysql_num_rows($queryProx);

    //Consulta para la tabla de solicitudes Vencidas
    $consultaVen = "SELECT
        c_solicitud.fecha_solicitud AS Fecha,
        c_solicitud.FechaCompromiso AS FechaCom,
        c_solicitud.FechaInstalacion AS FechaInstalacion,
        c_solicitud.FechaAlarm AS FechaAlarm,
        CONCAT( CASE MONTH(c_solicitud.PeriodoFac)
        WHEN 1 THEN 'Enero'
        WHEN 2 THEN 'Febrero'
        WHEN 3 THEN 'Marzo'
        WHEN 4 THEN 'Abril'
        WHEN 5 THEN 'Mayo'
        WHEN 6 THEN 'Junio'
        WHEN 7 THEN 'Julio'
        WHEN 8 THEN 'Agosto'
        WHEN 9 THEN 'Septiembre'
        WHEN 10 THEN 'Octubre'
        WHEN 11 THEN 'Noviembre'
        WHEN 12 THEN 'Diciembre'
        END , ' / ' ,  DATE_FORMAT(c_solicitud.PeriodoFac, '%Y')) AS PeriodoFac,
        c_cliente.NombreRazonSocial AS Cliente,
        c_tiposolicitud.Nombre AS TipoSolicitud,
        CASE
                WHEN SUM(IF(k_solicitud.tipo=0,IF(ISNULL(c_ventadirecta.id_solicitud),k_solicitud.cantidad,FORMAT(k_solicitud.cantidad/2,0) ),0)) > 0 and ( SUM(IF(k_solicitud.tipo=0,IF(ISNULL(c_ventadirecta.id_solicitud),k_solicitud.cantidad,FORMAT(k_solicitud.cantidad/2,0) ),0)) - (SELECT COUNT(c_bitacora.id_solicitud) FROM c_bitacora WHERE c_bitacora.id_solicitud = c_solicitud.id_solicitud)) = 0 and SUM(IF(k_solicitud.tipo=1,IF(ISNULL(c_ventadirecta.id_solicitud),k_solicitud.cantidad,FORMAT(k_solicitud.cantidad/2,0)),0)) = 0
                    THEN 'Todos los Equipos han sido asignados'
                WHEN SUM(IF(k_solicitud.tipo=0,IF(ISNULL(c_ventadirecta.id_solicitud),k_solicitud.cantidad,FORMAT(k_solicitud.cantidad/2,0) ),0)) > 0 and ( SUM(IF(k_solicitud.tipo=0,IF(ISNULL(c_ventadirecta.id_solicitud),k_solicitud.cantidad,FORMAT(k_solicitud.cantidad/2,0) ),0)) - (SELECT COUNT(c_bitacora.id_solicitud) FROM c_bitacora WHERE c_bitacora.id_solicitud = c_solicitud.id_solicitud)) > 0 and SUM(IF(k_solicitud.tipo=1,IF(ISNULL(c_ventadirecta.id_solicitud),k_solicitud.cantidad,FORMAT(k_solicitud.cantidad/2,0)),0)) = 0 and ( SUM(IF(k_solicitud.tipo=0,IF(ISNULL(c_ventadirecta.id_solicitud),k_solicitud.cantidad,FORMAT(k_solicitud.cantidad/2,0) ),0)) - (SELECT COUNT(c_bitacora.id_solicitud) FROM c_bitacora WHERE c_bitacora.id_solicitud = c_solicitud.id_solicitud)) < SUM(IF(k_solicitud.tipo=0,IF(ISNULL(c_ventadirecta.id_solicitud),k_solicitud.cantidad,FORMAT(k_solicitud.cantidad/2,0) ),0))
                    THEN 'Faltan Equipos por asignar'  
                WHEN SUM(IF(k_solicitud.tipo=0,IF(ISNULL(c_ventadirecta.id_solicitud),k_solicitud.cantidad,FORMAT(k_solicitud.cantidad/2,0) ),0)) > 0 and ( SUM(IF(k_solicitud.tipo=0,IF(ISNULL(c_ventadirecta.id_solicitud),k_solicitud.cantidad,FORMAT(k_solicitud.cantidad/2,0) ),0)) - (SELECT COUNT(c_bitacora.id_solicitud) FROM c_bitacora WHERE c_bitacora.id_solicitud = c_solicitud.id_solicitud)) > 0 and SUM(IF(k_solicitud.tipo=1,IF(ISNULL(c_ventadirecta.id_solicitud),k_solicitud.cantidad,FORMAT(k_solicitud.cantidad/2,0)),0)) = 0 and ( SUM(IF(k_solicitud.tipo=0,IF(ISNULL(c_ventadirecta.id_solicitud),k_solicitud.cantidad,FORMAT(k_solicitud.cantidad/2,0) ),0)) - (SELECT COUNT(c_bitacora.id_solicitud) FROM c_bitacora WHERE c_bitacora.id_solicitud = c_solicitud.id_solicitud)) = SUM(IF(k_solicitud.tipo=0,IF(ISNULL(c_ventadirecta.id_solicitud),k_solicitud.cantidad,FORMAT(k_solicitud.cantidad/2,0) ),0))          
                    THEN 'No hay Equipos Asignados'
                WHEN SUM(IF(k_solicitud.tipo=1,IF(ISNULL(c_ventadirecta.id_solicitud),k_solicitud.cantidad,FORMAT(k_solicitud.cantidad/2,0)),0)) > 0 and (SUM(IF(k_solicitud.tipo=1,IF(ISNULL(c_ventadirecta.id_solicitud),k_solicitud.cantidad,FORMAT(k_solicitud.cantidad/2,0)),0))- SUM(k_solicitud.cantidad_surtida)) = 0 and SUM(IF(k_solicitud.tipo=0,IF(ISNULL(c_ventadirecta.id_solicitud),k_solicitud.cantidad,FORMAT(k_solicitud.cantidad/2,0) ),0)) = 0
                    THEN 'Todos los Componentes han sido asignados'
                WHEN SUM(IF(k_solicitud.tipo=1,IF(ISNULL(c_ventadirecta.id_solicitud),k_solicitud.cantidad,FORMAT(k_solicitud.cantidad/2,0)),0)) > 0 and (SUM(IF(k_solicitud.tipo=1,IF(ISNULL(c_ventadirecta.id_solicitud),k_solicitud.cantidad,FORMAT(k_solicitud.cantidad/2,0)),0))- SUM(k_solicitud.cantidad_surtida)) > 0 and (SUM(IF(k_solicitud.tipo=1,IF(ISNULL(c_ventadirecta.id_solicitud),k_solicitud.cantidad,FORMAT(k_solicitud.cantidad/2,0)),0))- SUM(k_solicitud.cantidad_surtida)) < SUM(IF(k_solicitud.tipo=1,IF(ISNULL(c_ventadirecta.id_solicitud),k_solicitud.cantidad,FORMAT(k_solicitud.cantidad/2,0)),0)) and SUM(IF(k_solicitud.tipo=0,IF(ISNULL(c_ventadirecta.id_solicitud),k_solicitud.cantidad,FORMAT(k_solicitud.cantidad/2,0) ),0)) = 0 
                                THEN 'Faltan Componentes por asignar'
                WHEN SUM(IF(k_solicitud.tipo=1,IF(ISNULL(c_ventadirecta.id_solicitud),k_solicitud.cantidad,FORMAT(k_solicitud.cantidad/2,0)),0)) > 0 and (SUM(IF(k_solicitud.tipo=1,IF(ISNULL(c_ventadirecta.id_solicitud),k_solicitud.cantidad,FORMAT(k_solicitud.cantidad/2,0)),0))- SUM(k_solicitud.cantidad_surtida)) > 0 and (SUM(IF(k_solicitud.tipo=1,IF(ISNULL(c_ventadirecta.id_solicitud),k_solicitud.cantidad,FORMAT(k_solicitud.cantidad/2,0)),0))- SUM(k_solicitud.cantidad_surtida)) = SUM(IF(k_solicitud.tipo=1,IF(ISNULL(c_ventadirecta.id_solicitud),k_solicitud.cantidad,FORMAT(k_solicitud.cantidad/2,0)),0)) and SUM(IF(k_solicitud.tipo=0,IF(ISNULL(c_ventadirecta.id_solicitud),k_solicitud.cantidad,FORMAT(k_solicitud.cantidad/2,0) ),0)) = 0
                    THEN 'No hay Componentes asignados'
                WHEN SUM(IF(k_solicitud.tipo=1,IF(ISNULL(c_ventadirecta.id_solicitud),k_solicitud.cantidad,FORMAT(k_solicitud.cantidad/2,0)),0)) > 0 and SUM(IF(k_solicitud.tipo=0,IF(ISNULL(c_ventadirecta.id_solicitud),k_solicitud.cantidad,FORMAT(k_solicitud.cantidad/2,0) ),0)) > 0 and ( SUM(IF(k_solicitud.tipo=1,IF(ISNULL(c_ventadirecta.id_solicitud),k_solicitud.cantidad,FORMAT(k_solicitud.cantidad/2,0)),0))+ SUM(IF(k_solicitud.tipo=0,IF(ISNULL(c_ventadirecta.id_solicitud),k_solicitud.cantidad,FORMAT(k_solicitud.cantidad/2,0) ),0)) ) - ((SELECT COUNT(c_bitacora.id_solicitud) FROM c_bitacora WHERE c_bitacora.id_solicitud = c_solicitud.id_solicitud)+SUM(k_solicitud.cantidad_surtida) ) = 0 or c_solicitud.id_tiposolicitud = 6
                    THEN 'Todos los Equipos y Componentes han sido asignados' 
                WHEN SUM(IF(k_solicitud.tipo=1,IF(ISNULL(c_ventadirecta.id_solicitud),k_solicitud.cantidad,FORMAT(k_solicitud.cantidad/2,0)),0)) > 0 and SUM(IF(k_solicitud.tipo=0,IF(ISNULL(c_ventadirecta.id_solicitud),k_solicitud.cantidad,FORMAT(k_solicitud.cantidad/2,0) ),0)) > 0 and ( SUM(IF(k_solicitud.tipo=1,IF(ISNULL(c_ventadirecta.id_solicitud),k_solicitud.cantidad,FORMAT(k_solicitud.cantidad/2,0)),0))+ SUM(IF(k_solicitud.tipo=0,IF(ISNULL(c_ventadirecta.id_solicitud),k_solicitud.cantidad,FORMAT(k_solicitud.cantidad/2,0) ),0)) ) - ((SELECT COUNT(c_bitacora.id_solicitud) FROM c_bitacora WHERE c_bitacora.id_solicitud = c_solicitud.id_solicitud)+SUM(k_solicitud.cantidad_surtida) ) > 0 and ((SELECT COUNT(c_bitacora.id_solicitud) FROM c_bitacora WHERE c_bitacora.id_solicitud = c_solicitud.id_solicitud)+SUM(k_solicitud.cantidad_surtida) ) < ( SUM(IF(k_solicitud.tipo=1,IF(ISNULL(c_ventadirecta.id_solicitud),k_solicitud.cantidad,FORMAT(k_solicitud.cantidad/2,0)),0))+ SUM(IF(k_solicitud.tipo=0,IF(ISNULL(c_ventadirecta.id_solicitud),k_solicitud.cantidad,FORMAT(k_solicitud.cantidad/2,0) ),0)) )
                    THEN 'Faltan Equipos y Componentes por asignar'
                WHEN SUM(IF(k_solicitud.tipo=1,IF(ISNULL(c_ventadirecta.id_solicitud),k_solicitud.cantidad,FORMAT(k_solicitud.cantidad/2,0)),0)) > 0 and SUM(IF(k_solicitud.tipo=0,IF(ISNULL(c_ventadirecta.id_solicitud),k_solicitud.cantidad,FORMAT(k_solicitud.cantidad/2,0) ),0)) > 0 and ( SUM(IF(k_solicitud.tipo=1,IF(ISNULL(c_ventadirecta.id_solicitud),k_solicitud.cantidad,FORMAT(k_solicitud.cantidad/2,0)),0))+ SUM(IF(k_solicitud.tipo=0,IF(ISNULL(c_ventadirecta.id_solicitud),k_solicitud.cantidad,FORMAT(k_solicitud.cantidad/2,0) ),0)) ) - ((SELECT COUNT(c_bitacora.id_solicitud) FROM c_bitacora WHERE c_bitacora.id_solicitud = c_solicitud.id_solicitud)+SUM(k_solicitud.cantidad_surtida) ) = ( SUM(IF(k_solicitud.tipo=1,IF(ISNULL(c_ventadirecta.id_solicitud),k_solicitud.cantidad,FORMAT(k_solicitud.cantidad/2,0)),0))+ SUM(IF(k_solicitud.tipo=0,IF(ISNULL(c_ventadirecta.id_solicitud),k_solicitud.cantidad,FORMAT(k_solicitud.cantidad/2,0) ),0)) )
                    THEN 'No hay Equipos ni Componentes asignados' 
                ELSE 'Error'
            END AS EquiComAsig,
        (CONCAT(u.Nombre,' ',u.ApellidoPaterno,' ',u.ApellidoMaterno)) AS UsuarioAutorizo,
        c_solicitud.id_tiposolicitud AS IdTipoSolicitud,
        (SELECT CASE WHEN !ISNULL(c_ventadirecta.IdVentaDirecta) THEN c_ventadirecta.IdVentaDirecta ELSE 'N/A' END) AS VentaDirecta,
        (SELECT CASE WHEN !ISNULL(c_ventadirecta.id_prefactura) THEN c_ventadirecta.id_prefactura ELSE '' END) AS Prefactura,
        SUM(IF(k_solicitud.tipo=0,IF(ISNULL(c_ventadirecta.id_solicitud),k_solicitud.cantidad,FORMAT(k_solicitud.cantidad/2,0) ),0)) AS NumEquipos,
        SUM(IF(k_solicitud.tipo=1,IF(ISNULL(c_ventadirecta.id_solicitud),k_solicitud.cantidad,FORMAT(k_solicitud.cantidad/2,0)),0)) AS NumCompo,
        es.NombreEstatus  AS Status,
        c_solicitud.id_solicitud AS ID,
        c_solicitud.estatus AS idEstatus,
        (SELECT group_concat(Nombre separator ', ') FROM c_centrocosto AS cc, k_solicitud AS ks
        WHERE ks.id_solicitud = c_solicitud.id_solicitud AND cc.ClaveCentroCosto = ks.ClaveCentroCosto GROUP BY k_solicitud.id_solicitud) AS localidades
        FROM c_solicitud
        INNER JOIN k_solicitud ON k_solicitud.id_solicitud = c_solicitud.id_solicitud
        INNER JOIN c_cliente ON c_solicitud.ClaveCliente = c_cliente.ClaveCliente
        INNER JOIN c_tiposolicitud ON c_tiposolicitud.IdTipoMovimiento = c_solicitud.id_tiposolicitud
        LEFT JOIN c_usuario AS u ON u.IdUsuario = c_solicitud.id_autoriza
        LEFT JOIN c_ventadirecta ON c_ventadirecta.id_solicitud = c_solicitud.id_solicitud
        LEFT JOIN c_estatussolicitud AS es ON es.IdEstatusSolicitud = c_solicitud.estatus
        $where AND es.NombreEstatus != 'Autorizada'  AND FechaInstalacion < '".$FechaHoy."'
        GROUP BY ID DESC;";

    $queryVen = $catalogo->obtenerLista($consultaVen);   
    $numSolVen = mysql_num_rows($queryVen);

    // fin de consulta

    $alta = "ventas/NuevaSolicitud.php";

    $permiso_serie = false;
    $permisos_grid->tienePermisoEspecial($_SESSION['idUsuario'], 1) || $permisos_grid->tienePermisoEspecial($_SESSION['idUsuario'], 2) ? $permiso_serie = true : null ;

    $permiso_imprimir = false;
    $permisos_grid->tienePermisoEspecial($_SESSION['idUsuario'], 3) ? $permiso_imprimir = true : null;

    $factura_vd = false;
    $permisos_grid->tienePermisoEspecial($_SESSION['idUsuario'], 24) ? $factura_vd = true : null;

    function printTable($cabeceras, $columnas, $query, $permisos_grid, $permiso_serie) {
        echo '<thead class="thead-dark">
                <tr>';
        foreach ($cabeceras as $cabecera) echo "<th>" . $cabecera . "</th>";
        echo '</tr>
            </thead>'; 

        $contador = 0;
                        
        if (!$mostrar) { 
            #recorrer filas
            while ($rs = mysql_fetch_array($query)) {
                if ($rs['Status'] != "Surtida" || $rs['Status'] != "Cancelada") {
                    echo "<tr>";
                    foreach ($columnas as $columna) echo "<td> $rs[$columna] </td>";
                    #Cuabdo el status no es 0
                    echo ($rs['idEstatus'] != "0" 
                        ? "<td > $rs->Status / $rs->UsuarioAutorizo </td>" 
                        : "<td> $rs->Status </td>");
                    # Si no está surtida, cancelada ni autorizada
                    if ($rs['Status'] != "Surtida" || $rs['Status'] != "Cancelada" || $rs['Status'] != "Autorizada") {
                        #Si no está surtida y es posible modificarla
                        echo ( $rs['Status'] != "Surtida" && $permisos_grid->getModificar() 
                            ? "<td><a href='#' onclick=\"editarRegistro('$alta', '".$rs[$columnas[0]]."');return false;\" title='Editar Registro' ><img src='resources/images/Modify.png'/></a></td>" 
                            : "<td></td>");
                    } else {
                        echo "<t'></td>";
                    }
                    echo "<td >";
                    if ($permiso_imprimir && $rs['idEstatus'] == "0") {
                        echo ($rs['VentaDirecta'] == "N/A" 
                            ? "<a href='reportes/SolicitudEquipo.php?noSolicitud=".$rs['ID']."' target='_blank' title='Reporte' ><img src='resources/images/icono_impresora.png' width='24' height='24'/></a>"
                            : "<a href='ventas/imprimir_ventad.php?id=$rs".['VentaDirecta']."' target='_blank' title='Reporte' ><img src='resources/images/icono_impresora.png' width='24' height='24'/></a>" );
                    }
                    echo "</td>";
                    if (($rs['idEstatus'] == "1" || $rs['idEstatus'] == "5") && $permiso_serie) {
                        echo "<td > 
                                <a href='#' onclick='cambiarContenidos(\"ventas/lista_solicitud_series.php?id=".$rs['ID']."\", \"Serie de equipos\");return false;' title='Agregar series'>
                                    <img src='resources/images/Apply.png' width='24' height='24'/>
                                </a>
                            </td>";
                    } else {
                        echo '<td ></td>';
                    }
                }

                echo "<td>";
                if($factura_vd && $rs['VentaDirecta'] != "N/A" && ($rs['idEstatus'] == "1" || $rs['idEstatus'] == "5")){
                    echo "<a onclick='facturarvd(\"" . $rs['VentaDirecta'] . "\",\"".$rs['ID']."\");'>
                            <img src='resources/images/facturar.png'/>
                        </a>";
                }            
                echo "</td>";
                echo "<td>";
                if ($permisos_grid->getBaja()) {
                    echo "<a onclick='cancelarsolicitud(\"" . $rs['ID'] . "\");' title=''>
                            <img src='resources/images/Erase.png'/>
                        </a>";
                }
                echo "</td>
                    </tr>";
                $contador++;
            }
        } else {
            #recorrer filas en caso de que no se deba de mostrar
            while ($rs = mysql_fetch_array($query)) {
                echo "<tr>";
                foreach ($columnas as $columna) echo "<td class='table__item'> $rs[$columna] </td>";
                #Cuabdo el status no es 0
                echo ($rs['idEstatus'] != "0" 
                    ? "<td> $rs->Status / $rs->UsuarioAutorizo </td>" 
                    : "<td> $rs->Status </td>");
                # Si no está surtida, cancelada ni autorizada
                if ($rs['Status'] != "Surtida" || $rs['Status'] != "Cancelada" || $rs['Status'] != "Autorizada") {
                    #Si no está surtida y es posible modificarla
                    echo ( $rs['Status'] != "Surtida" && $permisos_grid->getModificar() 
                        ? "<td><a href='#' onclick=\"editarRegistro('$alta', '".$rs[$columnas[0]]."');return false;\" title='Editar Registro' ><img src='resources/images/Modify.png'/></a></td>" 
                        : "<td></td>");
                } else {
                    echo "<td></td>";
                }
                if ($permiso_imprimir) {
                    echo ($rs['VentaDirecta'] == "N/A" 
                        ? "<a href='reportes/SolicitudEquipo.php?noSolicitud=".$rs['ID']."' target='_blank' title='Reporte' ><img src='resources/images/icono_impresora.png' width='24' height='24'/></a>"
                        : "<a href='ventas/imprimir_ventad.php?id=$rs".['VentaDirecta']."' target='_blank' title='Reporte' ><img src='resources/images/icono_impresora.png' width='24' height='24'/></a>" );
                }
                echo "</td>";
                if (($rs['idEstatus'] == "1" || $rs['idEstatus'] == "5") && $permiso_serie) {
                    echo "<td > 
                            <a href='#' onclick=\"cambiarContenidos('ventas/lista_solicitud_series.php?id=".$rs['ID']."', 'Serie de equipos');return false;\" title='Agregar series'>
                                <img src='resources/images/Apply.png' width='24' height='24'/>
                            </a>
                        </td>";
                } else {
                    echo '<td></td>';
                }
                echo "<td>";
                if($factura_vd && $rs['VentaDirecta'] != "N/A" && ($rs['idEstatus'] == "1" || $rs['idEstatus'] == "5")){
                    echo "<a onclick=\"facturarvd('" . $rs['VentaDirecta'] . "','".$rs['ID']."');\">
                            <img src='resources/images/facturar.png'/>
                        </a>";
                }            
                echo "</td>";
                echo "<td>";
                if ($permisos_grid->getBaja()) {
                    echo "<a onclick=\"cancelarsolicitud('" . $rs['ID'] . "');\" title=''>
                            <img src='resources/images/Erase.png'/>
                        </a>";
                }
                echo "</td>
                    </tr>";
                $contador++;
            }
        }
    }
?>
<head>
    <!-- <script type="text/javascript" src="js/tab.js"></script>
    <script type="text/javascript" src="js/tables.js"></script> -->
    <!-- se agrego esta linea para hacer los tableros-->
    <script type="text/javascript" src="resources/js/paginas/resumen.js"></script>
    <script type="text/javascript" src="resources/js/paginas/ventas/lista_sol_equipo.js"></script>
    
    
    <link href="resources/css/resumen.css" rel="stylesheet" type="text/css"/> <!-- se agrego esta linea para hacer los tableros-->
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
</head>

<?php if ($permisos_grid->getAlta()): ?>
    <img class="imagenMouse" src="resources/images/add.png" title="Nuevo" onclick='cambiarContenidos("ventas/NuevaSolicitud.php", "Solicitudes");' style="float: right; cursor: pointer;" />  
<?php endif; ?>


<ul class="nav nav-tabs" id="myTab" role="tablist">
    <li class="nav-item">
        <a class="nav-link active" id="todas-tab" data-toggle="tab" href="#todas" role="tab" aria-controls="todas" aria-selected="true">Todas las solicitudes</a>
    </li>
    <li class="nav-item">
        <a class="nav-link" id="hoy-tab" data-toggle="tab" href="#hoy" role="tab" aria-controls="hoy" aria-selected="false">Solicitudes de hoy</a>
    </li>
    <li class="nav-item">
        <a class="nav-link" id="proximas-tab" data-toggle="tab" href="#proximas" role="tab" aria-controls="proximas" aria-selected="false">Solicitudes proximas</a>
    </li>
    <li class="nav-item">
        <a class="nav-link" id="vencidas-tab" data-toggle="tab" href="#vencidas" role="tab" aria-controls="vencidas" aria-selected="false">Solicitudes vencidas</a>
    </li>
</ul>
<div class="tab-content" id="myTabContent">
    <div class="tab-pane fade show active" id="todas" role="tabpanel" aria-labelledby="todas-tab">
        <h3>Todas las solicitudes - <?php echo "$numeroSolicitudes"; ?></h3>
        <!-- Mostrar rechazadas -->
        <div>
            <a href="ventas/list_sol_equipoXLS.php?mostrar=<?php echo $mostrar; ?>" target="_blank" class="boton">
                <img src="resources/images/excel.png">
            </a>
            <label for="checksc">Surtidas y Rechazadas</label>
            <input type="checkbox" id="checksc" value="1" onchange="surtidasycanceladas();" <?php echo ($mostrar == 1 ? "checked" : null); ?>/>  
        </div>
        <div class="table-responsive">
            <table class="table">
                <?php printTable($cabeceras, $columnas, $query, $permisos_grid, $permiso_serie); ?>
            </table>
        </div>
    </div>
    <div class="tab-pane fade" id="hoy" role="tabpanel" aria-labelledby="hoy-tab">
        <h3>Solicitudes para el dia de hoy - <?php echo "$numSolHoy"; ?></h3>
        <div class="table-responsive">
            <table class="table">
                <?php printTable($cabeceras, $columnas, $queryHoy, $permisos_grid, $permiso_serie); ?>
            </table>
        </div>
    </div>
    <div class="tab-pane fade" id="proximas" role="tabpanel" aria-labelledby="proximas-tab">
        <h3>Solicitudes proximas - <?php echo "$numSolProx"; ?></h3>
        <div class="table-responsive">
            <table class="table">
                <?php printTable($cabeceras, $columnas, $queryProx, $permisos_grid, $permiso_serie); ?>
            </table>
        </div>
    </div>
    <div class="tab-pane fade" id="vencidas" role="tabpanel" aria-labelledby="vencidas-tab">
        <h3>Solicitudes vencidas - <?php echo "$numSolVen"; ?></h3>
        <div class="table-responsive">
            <table class="table">
                <?php printTable($cabeceras, $columnas, $queryVen, $permisos_grid, $permiso_serie); ?>
            </table>
        </div>
    </div>
</div>