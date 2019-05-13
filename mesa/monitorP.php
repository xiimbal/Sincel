<?php
session_start();

if (!isset($_SESSION['user']) || $_SESSION['user'] == "") {
    header("Location: index.php");
}

include_once("../WEB-INF/Classes/Catalogo.class.php");
include_once("../WEB-INF/Classes/PermisosSubMenu.class.php");
include_once("../WEB-INF/Classes/Puesto.class.php");
include_once("../WEB-INF/Classes/Usuario.class.php");
include_once("../WEB-INF/Classes/ParametroGlobal.class.php");
include_once("../WEB-INF/Classes/Parametros.class.php");

$permisos_grid = new PermisosSubMenu();
$same_page = "mesa/monitorP.php";
$permisos_grid->getPermisosSubmenu($_SESSION['idUsuario'], $same_page);
$tiene_filtro = false;

date_default_timezone_set("America/Mexico_City");

$permisos_grid2 = new PermisosSubMenu();
$nombre_objeto = $permisos_grid2->getNombreTicketSistema();
$nombre_puesto = $permisos_grid2->getNombreTecnicoSistema();
$latitud = $permisos_grid2->getLatitudSistema();
$longitud = $permisos_grid2->getLongitudSistema();

$parametros = new Parametros();
$parametros->getRegistroById("8");
$liga = $parametros->getDescripcion();

$parametroGlobal = new ParametroGlobal();
$tiene_permisoTicket = false;
$tiene_permisoFotografico = false;
$muestra_links = false;

if ($parametroGlobal->getRegistroById(15) && $parametroGlobal->getActivo() == "1") {
    $tiene_permisoFotografico = true;
}
if ($parametroGlobal->getRegistroById(16) && $parametroGlobal->getActivo() == "1") {
    $tiene_permisoTicket = true;
}
if ($parametroGlobal->getRegistroById(26) && $parametroGlobal->getValor()) {
    $muestra_links = true;
}

/* Para mantener los filtros y paginados de la tabla */
if (isset($_GET['page']) && isset($_GET['filter'])) {
    $filter = str_replace("_XX__XX_", " ", $_GET['filter']);
    $page = $_GET['page'];
} else {
    $page = "0";
    $filter = "";
}

$tecnicos = array();
$catalogo = new Catalogo();

$FechaHoraNow = date('Y') . "-" . date('m') . "-" . date('d') . " " . date('H') . ":" . date('i') . ":" . date('s');
$having = " HAVING (IdEstatusAtencion <> 16 AND IdEstatusAtencion <> 59) OR ISNULL(IdEstatusAtencion)";
$hav = "(nt.IdEstatusAtencion <> 16 AND nt.IdEstatusAtencion <> 59) OR ISNULL(nt.IdEstatusAtencion)";
$hav2 = "AND t.EstadoDeTicket NOT IN(2,4)";
$cerradoTicket = "t.EstadoDeTicket <> 2 AND ";
$checked = "";
$checkedMoroso = "";
$canceladoTicket = "t.EstadoDeTicket <> 4 AND ";
$checkedCancelado = "";
$cliente = "";
$colorPOST = "";
$idTicket = "";
$estadoNota = "LEFT JOIN c_notaticket AS nt ON nt.IdNotaTicket = (SELECT MAX(IdNotaTicket) FROM c_notaticket AS nt2 WHERE nt2.IdTicket = t.IdTicket)";
$morososTicket = "cl.IdEstatusCobranza <> 2 AND ";
$tipoJoin = "LEFT";
$usuario = new Usuario();
$idUsuario = $_SESSION['idUsuario'];
$where = "";
$viatico = "";
$horasAntes = date('Y-m-d H:i:s', strtotime('-4 hour'));
$horasDespues = date('Y-m-d H:i:s', strtotime('+4 hour'));
$citasFechaHora = " AND ce.FechaHora <= '$horasDespues' ";
$citasFechaHoraAsignados = "AND ce.FechaHora <= '$horasDespues' ";
/* Verificamos el puesto del usuario */
if ($usuario->getRegistroById($idUsuario)) {//Buscamos las areas de atencion a las que está asociado este puesto
    $consulta = "SELECT GROUP_CONCAT(CONVERT(IdEstado, CHAR(8)) SEPARATOR ',') estados FROM `k_areapuesto` WHERE IdPuesto = " . $usuario->getPuesto() . ";";
    $result = $catalogo->obtenerLista($consulta);
    if (mysql_numrows($result) > 0) {
        while ($rs = mysql_fetch_array($result)) {
            if (!empty($rs['estados'])) {
                $estado = " t.AreaAtencion IN (" . $rs['estados'] . ") AND ";
                $tipoJoin = "INNER";
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

if (isset($_POST['idTicket']) && $_POST['idTicket'] != "") {
    $tiene_filtro = true;
    $idTicket = $_POST['idTicket'];
    /* Si se busco un ticket en particular, habilitamos cerrados, morosos y cancelados */
//$checked = "checked='checked'";
    $checkedMoroso = "checked='checked'";
    $where .= " t.IdTicket = $idTicket AND ";
//$checkedCancelado = "checked='checked'";
}

if (isset($_POST['cerrado']) && $_POST['cerrado'] != "false") {
    $cerradoTicket = "";
    $checked = "checked='checked'";
    if (isset($_POST['cancelado']) && $_POST['cancelado'] != "false") {
        $having = "";
    } else {
        $having = " HAVING (IdEstatusAtencion <> 59) OR ISNULL(IdEstatusAtencion) ";
    }
} else {
    if (isset($_POST['cancelado']) && $_POST['cancelado'] != "false") {
        $having = " HAVING (IdEstatusAtencion <> 16) OR ISNULL(IdEstatusAtencion) ";
    }
}

if (isset($_POST['moroso']) && $_POST['moroso'] != "false") {
    $morososTicket = "";
    $checkedMoroso = "checked='checked'";
}

if (isset($_POST['cancelado']) && $_POST['cancelado'] != "false") {
    $canceladoTicket = "";
    $checkedCancelado = "checked='checked'";
}

if (isset($_POST['cliente']) && $_POST['cliente'] != "") {
    $tiene_filtro = true;
    $cliente = " t.NombreCliente IN (" . $_POST['cliente'] . ") AND ";
    $cliente_array = explode("','", $_POST['cliente']);
    $cliente_array[0] = substr($cliente_array[0], 1, strlen($cliente_array[0]));
    $cliente_array[count($cliente_array) - 1] = substr($cliente_array[count($cliente_array) - 1], 0, strlen($cliente_array[count($cliente_array) - 1]) - 1);
    $where .= " $cliente ";
}

if (isset($_POST['color'])) {
    $colorPOST = $_POST['color'];
}

if (isset($_POST['estado']) && $_POST['estado'] != "") {
    if ($_POST['estado'] == 1) {
        $s1 = "selected='selected'";
        $estadoNota = "INNER JOIN c_notaticket AS nt ON nt.IdEstatusAtencion = 22 AND nt.IdNotaTicket = (SELECT MAX(IdNotaTicket) FROM c_notaticket AS nt2 WHERE nt2.IdTicket = t.IdTicket)";
    } else {
        if ($_POST['estado'] == 2) {
            $s2 = "selected='selected'";
            $estadoNota = "INNER JOIN c_notaticket AS nt ON nt.IdEstatusAtencion = 16 AND nt.IdNotaTicket = (SELECT MAX(IdNotaTicket) FROM c_notaticket AS nt2 WHERE nt2.IdTicket = t.IdTicket)";
            $citasFechaHora = "AND nt6.FechaHora >= '$horasAntes' ";
            $hav = "(nt.IdEstatusAtencion <> 59) OR ISNULL(nt.IdEstatusAtencion)";
            $hav2 = "AND t.EstadoDeTicket NOT IN(4)";
        } else {
            if ($_POST['estado'] == 4) {
                $s4 = "selected='selected'";
                $hav = "ISNULL(nt.IdEstatusAtencion)";
            } else {
                $s3 = "selected='selected'";
                $hav = "(nt.IdEstatusAtencion <> 16 AND nt.IdEstatusAtencion <> 59 AND nt.IdEstatusAtencion <> 22) AND !ISNULL(nt.IdEstatusAtencion)";
            }
        }
    }
//    $estadoNota = "INNER JOIN c_notaticket AS nt ON nt.IdEstatusAtencion = " . $_POST['estado'] . " AND nt.IdNotaTicket = (SELECT MAX(IdNotaTicket) FROM c_notaticket AS nt2 WHERE nt2.IdTicket = t.IdTicket)";
    if ($_POST['estado'] == "16") {/* Si se selecciona el estado de cerrado, habiliatar el checkbox de cerrado también */
        $cerradoTicket = "";
        $citasFechaHora = "AND nt6.FechaHora >= '$horasAntes' ";
        if (isset($_POST['cancelado']) && $_POST['cancelado'] != "false") {
            $having = "";
            $hav = "";
            $hav2 = "";
        } else {
            $having = " HAVING (IdEstatusAtencion <> 59) OR ISNULL(IdEstatusAtencion) ";
            $hav = "(nt.IdEstatusAtencion <> 59) OR ISNULL(nt.IdEstatusAtencion)";
            $hav2 = "AND t.EstadoDeTicket NOT IN(4)";
        }
        $checked = "checked='checked'";
    }
}

$usuarios = array();
if (isset($_POST['tecnico']) && !empty($_POST['tecnico'])) {
    $usuarios = $_POST['tecnico'];
    $where .= " ktt.IdUsuario IN (" . implode(",", $usuarios) . ") AND ";
}

$prioridades = "";
if (isset($_POST['prioridad']) && $_POST['prioridad'] != "") {
    $prioridades = $_POST['prioridad'];
    $where .= " ktt.IdPrioridad = $prioridades AND ";
}

$ticketMensaje = "";
if (isset($_POST['ticketMensaje']) && $_POST['ticketMensaje'] != "") {
    $ticketMensaje = $_POST['ticketMensaje'];
}
if ($idTicket != "") {
    $ticketMensaje = $idTicket;
}

$tickets_mostrados = array();
?>
<!DOCTYPE html>
<html lang="es">
    <head>              
        <script type="text/javascript" language="javascript" src="resources/js/paginas/lista_ticket.js"></script>       
        <link href="resources/css/mapa/asigna_tecnico.css" rel="stylesheet" type="text/css">
        <script type="text/javascript" language="javascript" src="resources/js/mapas/Label.js"></script>
        <script type="text/javascript" language="javascript" src="resources/js/mapas/mapas.js"></script>  
        <script type='text/javascript' language='javascript' src='resources/js/paginas/ordenOperador.js'></script>
<!--        <style>
            .circleRed{width:25px;height:25px;border-radius:50px;font-size:20px;color:#fff;line-height:100px;text-align:center;background:#ED1C24}
            .circleBlue{width:25px;height:25px;border-radius:50px;font-size:20px;color:#fff;line-height:100px;text-align:center;background:#00A2E8}
            .circleGreen{width:25px;height:25px;border-radius:50px;font-size:20px;color:#fff;line-height:100px;text-align:center;background:#22B14C}
            .circleOrange{width:25px;height:25px;border-radius:50px;font-size:20px;color:#fff;line-height:100px;text-align:center;background:#FF7F27}
        </style>-->
          <!-- Bootstrap core CSS -->
        <link href="resources/css/Bootstrap 4/bootstrap.min.css" rel="stylesheet">  
        <!-- FontAwesome para iconos -->
        <link href="resources/css/Bootstrap 4/fontawesome/all.min.css" rel="stylesheet">
    </head>

    <body>
        <div class="principal"> 
            <?php
            $consultaLista = "SELECT 
                ce.idUsuario AS NoEmpleado, cu.Nombre, cu.ApellidoPaterno AS ApellidoPa,
                cu.ApellidoMaterno AS ApellidoMa, ca.Descripcion AS Campania, ct.descripcion AS Turno,
                CONCAT(ce.Origen,' - ',ce.Destino) AS Viaje, ce.FechaHora, t.AreaAtencion,

                t.IdTicket, u.IdUsuario, u.Loggin, CONCAT(u.Nombre,' ',u.ApellidoPaterno,' ',u.ApellidoMaterno) AS Usuario,
                nt.IdEstatusAtencion, t.TipoReporte,
                (CASE WHEN !ISNULL(dt.Latitud) THEN dt.Latitud WHEN !ISNULL(dcc.Latitud) THEN dcc.Latitud  ELSE $latitud END) AS Latitud, 
                (CASE WHEN !ISNULL(dt.Longitud) THEN dt.Longitud WHEN !ISNULL(dcc.Longitud) THEN dcc.Longitud  ELSE $longitud END) AS Longitud,
                GROUP_CONCAT(DISTINCT nt3.DiagnosticoSol SEPARATOR '<br/>*') AS Mensajes,
                (CASE WHEN !ISNULL(ubu.IdUbicacion) THEN ubu.Fecha ELSE 'SR' END) AS FechaUbicacion,
                (CASE WHEN !ISNULL(ubu.IdUbicacion) THEN ubu.Latitud ELSE $latitud END) AS LatitudUser,
                (CASE WHEN !ISNULL(ubu.IdUbicacion) THEN ubu.Longitud ELSE $longitud END) AS LongitudUser,
                ubu.PorcentajeBateria,
                pt.Prioridad, ktt.Duracion, um.Unidad, 
                (CASE WHEN !ISNULL(nt4.FechaHora) THEN nt4.FechaHora ELSE ktt.FechaHoraInicio END) AS FechaHoraInicio,
                (CASE WHEN !ISNULL(nt4.IdEstatusAtencion) THEN nt4.IdEstatusAtencion ELSE 'null' END) AS IdEstatusAtencion2                                        
                FROM k_tecnicoticket AS ktt
                LEFT JOIN c_usuario AS u ON u.IdUsuario = ktt.IdUsuario
                LEFT JOIN c_prioridadticket AS pt ON ktt.IdPrioridad = pt.IdPrioridad
                LEFT JOIN c_unidadmedida AS um ON um.IdUnidad = ktt.IdUnidadDuracion
                $tipoJoin JOIN c_ticket AS t ON $estado t.IdTicket = ktt.IdTicket
                $estadoNota 
                LEFT JOIN c_notaticket AS nt3 ON nt3.IdNotaTicket = (SELECT MAX(IdNotaTicket) FROM c_notaticket WHERE IdTicket = t.IdTicket)
                LEFT JOIN c_notaticket AS nt4 ON nt4.IdNotaTicket = (SELECT MAX(IdNotaTicket) FROM c_notaticket WHERE IdTicket = t.IdTicket AND IdEstatusAtencion IN(51,16,14))
                LEFT JOIN c_notaticket AS nt5 ON nt5.IdNotaTicket = (SELECT MAX(IdNotaTicket) FROM c_notaticket WHERE IdTicket = t.IdTicket AND IdEstatusAtencion IN(230)) 
                LEFT JOIN c_notaticket AS nt6 ON nt6.IdNotaTicket = (SELECT MAX(IdNotaTicket) FROM c_notaticket WHERE IdTicket = t.IdTicket AND IdEstatusAtencion IN (285))
                LEFT JOIN c_cliente AS c ON t.ClaveCliente = c.ClaveCliente
                LEFT JOIN c_centrocosto AS cc ON t.ClaveCentroCosto = cc.ClaveCentroCosto
                LEFT JOIN c_domicilio AS dcc ON dcc.IdDomicilio = (SELECT MIN(IdDomicilio) FROM c_domicilio WHERE ClaveEspecialDomicilio = cc.ClaveCentroCosto)
                LEFT JOIN c_domicilioticket AS dt ON dt.IdTicket = t.IdTicket
                LEFT JOIN c_ubicacionusuario AS ubu ON ubu.IdUbicacion = (SELECT MAX(IdUbicacion) FROM c_ubicacionusuario WHERE IdUsuario = u.IdUsuario)                                        

                LEFT JOIN c_especial AS ce ON t.IdTicket=ce.idTicket
                LEFT JOIN c_usuario AS cu ON cu.IdUsuario=ce.idUsuario 
                JOIN c_area AS ca ON ce.idCampania=ca.IdArea
                JOIN c_turno AS ct ON ct.idTurno=ce.idTurno

                WHERE $where   
                (
                    (
                        ((nt.IdEstatusAtencion <> 16 AND nt.IdEstatusAtencion <> 59) OR ISNULL(nt.IdEstatusAtencion)) AND t.EstadoDeTicket NOT IN(2,4)
                    )
                        $citasFechaHoraAsignados AND (ISNULL(nt5.IdNotaTicket) OR (nt6.IdNotaTicket > nt5.IdNotaTicket) OR TIMESTAMPDIFF(MINUTE,nt5.FechaHora,NOW()) <15)
                )
                GROUP BY t.IdTicket
                ORDER BY u.IdUsuario,t.IdTicket;";
            ?>

        </div>
            <div class="container-fluid"> 
                        <div class="form-row">                     
                                         <div class="form-group col-md-3">
                                            <label>Ticket</label>
                                            <input class="form-control" type="text" id="busqueda_ticket" name="busqueda_ticket" value="<?php echo $idTicket; ?>" />
                                         </div>   
                                         <div class="form-group col-md-3">
                                                <label>Estado</label>
                                                <select class="form-control" id="estado_ticket" name="estado_ticket" >
                                                    <?php
                                                    /* Inicializamos la clase */
//                                                    $query = $catalogo->obtenerLista("SELECT e.IdEstado, e.Nombre FROM c_estado AS e
//                                                                            INNER JOIN k_flujoestado AS kfe ON kfe.IdEstado = e.IdEstado AND kfe.IdFlujo = 6 ORDER BY Nombre;");
                                                    echo "<option value=''>Todos los estados sin 'Cerrado'</option>";
//                                                    while ($rs = mysql_fetch_array($query)) {
//                                                        $s = "";
//                                                        if (isset($_POST['estado']) && $_POST['estado'] == $rs['IdEstado']) {
//                                                            $s = "selected='selected'";
//                                                        }
//                                                        echo "<option value='" . $rs['IdEstado'] . "' $s>" . $rs['Nombre'] . "</option>";
//                                                    }
                                                    echo "<option value='1' $s1>Asignados</option>";
                                                    echo "<option value='2' $s2>Cerrados</option>";
                                                    echo "<option value='3' $s3>En Proceso</option>";
                                                    echo "<option value='4' $s4>Solicitados</option>";
                                                    ?> 
                                                </select>
                                         </div>

                                        <div class="form-group col-md-3">
                                            <label>Cliente</label><br>
                                            <select class= "form-control" id="cliente_ticket" name="cliente_ticket"   multiple="multiple">
                                                <?php
                                                $query = $catalogo->obtenerLista("SELECT DISTINCT(NombreCliente) AS cliente FROM `c_ticket` ORDER BY cliente;");
                                                while ($rs = mysql_fetch_array($query)) {
                                                    if ($rs['cliente'] == "") {
                                                        continue;
                                                    }
                                                    $s = "";
                                                    if (isset($_POST['cliente']) && $_POST['cliente'] != "" && in_array($rs['cliente'], $cliente_array)) {
                                                        $s = "selected='selected'";
                                                    }
                                                    echo "<option value='" . $rs['cliente'] . "&_X_&' $s>" . $rs['cliente'] . "</option>";
                                                }
                                                ?> 
                                            </select>
                                        </div>

                             <div class="form-group col-md-3">
                                     <label>Operador</label><br>
                                     <select  id="tecnico" name="tecnico" class="form-control" multiple>
                                        <?php
                                        /* Inicializamos la clase */
                                        $query = $catalogo->getListaAlta("c_usuario", "Nombre");
                                        while ($rs = mysql_fetch_array($query)) {
                                            $s = "";
                                            if (!empty($usuarios) && in_array($rs['IdUsuario'], $usuarios)) {
                                                $s = "selected='selected'";
                                            }
                                            if ($rs['IdPuesto'] == 101) {
                                                echo "<option value='" . $rs['IdUsuario'] . "' $s>" . $rs['Loggin'] . " - " . $rs['Nombre'] . " " . $rs['ApellidoPaterno'] . " " . $rs['ApellidoMaterno'] . "</option>";
                                            }
                                        }
                                        ?> 
                                    </select>
                                </div>  
                                        
                                           
                             <input type="button" class="button btn btn-lg btn-block btn-outline-success mt-3 mb-3" onclick="  recargarListaTicketTecnico('mesa/monitorP.php', 'busqueda_ticket', null, 'cliente_ticket', 'ticket_color',
                                                                 'estado_ticket', null, null, true, null, null, null, 'area', null, null, 'tecnico', 'prioridad');
                                                                 return false;" 
                                                                 id="boton_aceptar" name="boton_aceptar" value="Mostrar / Actualizar"/>
                                                
                                        <div class="form-group col-md-12"><!-- Div que contiene el mapa y tabla -->
                                             <div id="mapaSi">Ver Mapa:
                                                <input type='radio' id='ver_mapa' name='ver_mapa' onclick="VerMapa()"/>
                                            </div>
                                             <div id="ServiciosSi">Ver Servicios:<input type='radio' id='ver_lista' name='ver_lista' onclick="VerLista()"/>
                                             </div>
                                            <div id="map-canvas" style="height: 480px;">Aquí mapa</div>
                                            <!--- Tabla que se muestra al inicio-->
                                            <div id="lista_servicios">
                                               <fieldset>
                                                    <legend>Monitor de Servicios</legend>
                                                    <table  class="tablaUsuarios table-responsive" style="width: 100%;">
                                                        <thead>
                                                            <tr>
                                                                <?php
                                                                $cabeceras = array("", "Cita", "Registro", "Id", "Tipo", "Estatus", "Vale", "Operador", "Usuario", "Campaña", "Arribo", "Abordo", "Fin", "Origen", "Destino", "", "");
                                                                for ($i = 0; $i < (count($cabeceras) - 2); $i++) {
                                                                    echo "<th width=\"2%\" align=\"center\" scope=\"col\">" . $cabeceras[$i] . "</th>";
                                                                }
                                                                ?>
                                                            </tr>
                                                        </thead>
                                                        <tbody>
                                                            <?php
                                                            //cu usuario, u operador
                                                            $consultaListaS = "SELECT 
                                                                ce.idUsuario AS NoEmpleado, cu.Nombre, cu.ApellidoPaterno AS ApellidoPa,
                                                                cu.ApellidoMaterno AS ApellidoMa, LEFT(ca.Descripcion,4) AS Campania, ca.Descripcion AS CampaniaDetalle, ct.descripcion AS Turno,
                                                                ce.Origen AS Origen, CONCAT(ce.Calle_or,' ',ce.NoExterior_or,', ',ce.Colonia_or,', ',ce.Delegacion_or,', C.P. ',ce.CodigoPostal_or,', ',cdo.Ciudad) AS DomicilioOrigen,
                                                                ce.Destino AS Destino, CONCAT(ce.Calle_des,' ',ce.NoExterior_des,', ',ce.Colonia_des,', ',ce.Delegacion_des,', C.P. ',ce.CodigoPostal_des,', ',cdd.Ciudad) AS DomicilioDestino,
                                                                ce.DatoContacto, cf.Nombre AS FormaContacto, ce.FechaHora, t.AreaAtencion,
                                                                t.UsuarioUltimaModificacion AS Registro, UPPER(CONCAT(LEFT(cur.Nombre,1),'',LEFT(cur.ApellidoPaterno,1),'',LEFT(cur.ApellidoMaterno,1))) AS RegistroIni,
                                                                t.IdTicket, u.IdUsuario, u.Loggin, u.Telefono AS TelOperador,
                                                                CONCAT(u.Nombre,' ',u.ApellidoPaterno,' ',u.ApellidoMaterno) AS Usuario, 
                                                                GROUP_CONCAT(DISTINCT nt3.DiagnosticoSol SEPARATOR '*') AS Mensajes, 
                                                                (CASE WHEN ce.TipoServicio=0 THEN 'RS' ELSE 'MM' END) AS TipoServicio,
                                                                (CASE WHEN ce.TipoServicio=0 THEN 'Reservado' ELSE 'Al momento' END) AS TipoServicioDet,
                                                                cest.Nombre AS Estatus,
                                                                (CASE WHEN !ISNULL(nt.IdEstatusAtencion) THEN nt.IdEstatusAtencion ELSE 'null' END) AS IdEstatusAtencion,
                                                                (CASE WHEN !ISNULL(nt4.IdEstatusAtencion) THEN 'SI' ELSE 'NO' END) AS Asignado, 
                                                                (CASE WHEN !ISNULL(nt5.IdEstatusAtencion) THEN 'SI' ELSE 'NO' END) AS Abordo,
                                                                nt5.FechaHora AS FechaHoraAbordo,
                                                                (CASE WHEN !ISNULL(nt6.IdEstatusAtencion) THEN 'SI' ELSE 'NO' END) AS FinS,  
                                                                nt6.FechaHora AS FechaHoraFin,
                                                                (CASE WHEN !ISNULL(nt7.IdEstatusAtencion) THEN 'SI' ELSE 'NO' END) AS ValeFisico,  
                                                                nt7.FechaHora AS FechaHoraVale,
                                                                nt8.FechaHora AS FechaHoraArribo,
                                                                nt9.FechaHora AS FechaHoraCerrado
                                                                FROM c_ticket AS t
                                                                LEFT JOIN k_tecnicoticket AS ktt ON t.IdTicket=ktt.IdTicket
                                                                LEFT JOIN c_usuario AS u ON u.IdUsuario = ktt.IdUsuario 
                                                                $estadoNota 
                                                                LEFT JOIN c_notaticket AS nt3 ON nt3.IdNotaTicket = (SELECT MAX(IdNotaTicket) FROM c_notaticket WHERE IdTicket = t.IdTicket) 
                                                                LEFT JOIN c_notaticket AS nt4 ON nt4.IdNotaTicket = (SELECT MAX(IdNotaTicket) FROM c_notaticket WHERE IdTicket = t.IdTicket AND IdEstatusAtencion IN(22)) 
                                                                LEFT JOIN c_notaticket AS nt5 ON nt5.IdNotaTicket = (SELECT MAX(IdNotaTicket) FROM c_notaticket WHERE IdTicket = t.IdTicket AND IdEstatusAtencion IN(51)) 
                                                                LEFT JOIN c_notaticket AS nt6 ON nt6.IdNotaTicket = (SELECT MAX(IdNotaTicket) FROM c_notaticket WHERE IdTicket = t.IdTicket AND IdEstatusAtencion IN(284))
                                                                LEFT JOIN c_notaticket AS nt7 ON nt7.IdNotaTicket = (SELECT MAX(IdNotaTicket) FROM c_notaticket WHERE IdTicket = t.IdTicket AND IdEstatusAtencion IN(278))
                                                                LEFT JOIN c_notaticket AS nt8 ON nt8.IdNotaTicket = (SELECT MAX(IdNotaTicket) FROM c_notaticket WHERE IdTicket = t.IdTicket AND IdEstatusAtencion IN(241))
                                                                LEFT JOIN c_notaticket AS nt9 ON nt9.IdNotaTicket = (SELECT MAX(IdNotaTicket) FROM c_notaticket WHERE IdTicket = t.IdTicket AND IdEstatusAtencion IN(16))
                                                                LEFT JOIN c_notaticket AS nt10 ON nt10.IdNotaTicket = (SELECT MAX(IdNotaTicket) FROM c_notaticket WHERE IdTicket = t.IdTicket AND IdEstatusAtencion IN(230)) 
                                                                LEFT JOIN c_notaticket AS nt11 ON nt11.IdNotaTicket = (SELECT MAX(IdNotaTicket) FROM c_notaticket WHERE IdTicket = t.IdTicket AND IdEstatusAtencion IN (285))
                                                                
                                                                LEFT JOIN c_cliente AS c ON t.ClaveCliente = c.ClaveCliente LEFT JOIN c_centrocosto AS cc ON t.ClaveCentroCosto = cc.ClaveCentroCosto 
                                                                LEFT JOIN c_domicilio AS dcc ON dcc.IdDomicilio = (SELECT MIN(IdDomicilio) FROM c_domicilio WHERE ClaveEspecialDomicilio = cc.ClaveCentroCosto) 
                                                                LEFT JOIN c_domicilioticket AS dt ON dt.IdTicket = t.IdTicket 
                                                                LEFT JOIN c_ubicacionusuario AS ubu ON ubu.IdUbicacion = (SELECT MAX(IdUbicacion) FROM c_ubicacionusuario WHERE IdUsuario = u.IdUsuario) 

                                                                LEFT JOIN c_especial AS ce ON t.IdTicket=ce.idTicket
                                                                LEFT JOIN c_usuario AS cu ON cu.IdUsuario=ce.idUsuario 
                                                                LEFT JOIN c_area AS ca ON ce.idCampania=ca.IdArea
                                                                LEFT JOIN c_turno AS ct ON ct.idTurno=ce.idTurno
                                                                LEFT JOIN c_estado AS cest ON cest.IdEstado=nt.IdEstatusAtencion
                                                                LEFT JOIN c_ciudades AS cdo ON ce.Estado_or=cdo.IdCiudad
                                                                LEFT JOIN c_ciudades AS cdd ON ce.Estado_des=cdd.IdCiudad 
                                                                LEFT JOIN c_usuario AS cur ON cur.Loggin=t.UsuarioUltimaModificacion
                                                                LEFT JOIN c_formacontacto AS cf ON cf.IdFormaContacto=ce.IdFormaContacto

                                                                WHERE $where ( ( ($hav) $hav2 ) $citasFechaHora) AND (ISNULL(nt10.IdNotaTicket) OR (nt11.IdNotaTicket > nt10.IdNotaTicket) OR TIMESTAMPDIFF(MINUTE,nt10.FechaHora,NOW()) <15)
                                                                GROUP BY t.IdTicket ORDER BY u.IdUsuario,t.IdTicket; ";
                                                                //OR DATE(ktt.FechaHoraInicio) >= DATE(NOW())                                        
                                                            //echo $consultaListaS;
                                                            $result = $catalogo->obtenerLista($consultaListaS);
                                                            while ($rs = mysql_fetch_array($result)) {
                                                                $notas = 0;
                                                                $EstadoTicket = 0;
                                                                $datetime1 = new DateTime($FechaHoraNow);
                                                                $datetime2 = new DateTime($rs['FechaHora']); //"2016-05-12 5:47:00"
                                                                $res = $datetime1->diff($datetime2);
                                                                $diferencia_m = ($res->h * 60) + $res->i;
                    //                                                        printf('%d años, %d meses, %d días, %d horas, %d minutos', $res->y, $res->m, $res->d, $res->h, $res->i);

                                                                if ($FechaHoraNow >= $rs['FechaHora']) {
                                                                    if ($rs['FechaHoraAbordo'] == "") {
                                                                        $color_td = "style='background-color: red;'";
                                                                    } else {
                                                                        $color_td = "";
                                                                        if ($rs['FechaHoraFin'] != "") {
                                                                            $color_td = "style='background-color: #F9960A;'"; //Se termino el Servicio y es despues de la cita
                                                                        } else {
                                                                            $color_td = "style='background-color: darksalmon;'"; //Usuario Abordo sin terminar viaje despues de la cita
                                                                        }
                                                                        if ($rs['FechaHoraCerrado'] != "") {
                                                                            $color_td = "style='background-color: #6E0000;'"; //Servicio Cerrado
                                                                        }
                                                                    }
                                                                } else {
                                                                    if ($res->d == 0) {
                                                                        $color_td = "";
                                                                        if ($diferencia_m <= 240) {
                                                                            $color_td = "style='background-color: green;'";
                                                                            if ($diferencia_m <= 120 && $rs['Asignado'] == 'NO') {
                                                                                $color_td = "style='background-color: yellow;'";
                                                                                if ($diferencia_m <= 60) {
                                                                                    $color_td = "style='background-color: red;'";
                                                                                }
                                                                            }
                                                                            if ($diferencia_m <= 15 && $rs['Asignado'] == 'SI') {
                                                                                if ($rs['FechaHoraArribo'] == "") {
                                                                                    $color_td = "style='background-color: red;'";
                                                                                }
                                                                            }
                                                                            if ($rs['FechaHoraCerrado'] != "") {
                                                                                $color_td = "style='background-color: #6E0000;'"; //Servicio Cerrado
                                                                            }
                                                                        } else {
                                                                            if ($rs['FechaHoraCerrado'] != "") {
                                                                                $color_td = "style='background-color: #6E0000;'"; //Servicio Cerrado
                                                                            }
                                                                            if ($rs['IdEstatusAtencion'] == 22) {
                                                                                $color_td = "style='background-color: blue;'";
                                                                            } else {
                                                                                $color_td = "";
                                                                            }
                                                                        }
                                                                    } else {
                                                                        $color_td = "";
                                                                    }
                                                                }
                                                                
                                                                echo "<tr>";
                    //                                          ("Cita", "Registro","Id" ,"Tipo", "Estatus", "Vale", "Operador", "Usuario", "Campaña", "Arribo","Abordo","Fin","Turno","Origen","Destino")
                                                                echo "<td " . $color_td . " width=\"2%\" align=\"center\" scope=\"row\"></td>";
                                                                echo "<td width=\"2%\" align=\"center\" scope=\"row\">";
                                                                if ($rs['FechaHora'] != "") {
                                                                    echo date('dmy H:i', strtotime($rs['FechaHora']));
                                                                } else {
                                                                    echo "";
                                                                }
                                                                echo "</td>";
                                                                echo "<td title='" . $rs['Registro'] . "' width=\"2%\" align=\"center\" scope=\"row\">" . $rs['RegistroIni'] . "</td>";
                                                                echo "<td width=\"2%\" align=\"center\" scope=\"row\"><a href='#' onclick='detalleReporte(\"reportes/reporte_ticket.php\", \" " . $rs['IdTicket'] . " \", \" " . $rs['AreaAtencion'] . " \", \"0\"); return false;' title='Reporte' >" . $rs['IdTicket'] . "</a></td>";
                                                                echo "<td title='" . $rs['TipoServicioDet'] . "' width=\"2%\" align=\"center\" scope=\"row\">" . $rs['TipoServicio'] . " </td>";
                                                                if ($rs['IdEstatusAtencion'] == 22) {
                                                                    $estatusGenerico = "Asignado";
                                                                } else {
                                                                    if ($rs['IdEstatusAtencion'] == 16) {
                                                                        $estatusGenerico = "Cerrado";
                                                                    } else {
                                                                        if ($rs['IdEstatusAtencion'] == 'null') {
                                                                            $estatusGenerico = "Solicitado";
                                                                        } else {
                                                                            $estatusGenerico = "En Proceso";
                                                                        }
                                                                    }
                                                                }
                                                                echo "<td title='" . $rs['Estatus'] . "' width=\"2%\" align=\"center\" scope=\"row\">" . $estatusGenerico . "</td>";
                                                                echo "<td width=\"2%\" align=\"center\" scope=\"row\">" . $rs['ValeFisico'] . "</td>";
                                                                echo "<td title='" . $rs['TelOperador'] . "' width=\"2%\" align=\"center\" scope=\"row\">" . $rs['Loggin'] . "</td>";
                                                                echo "<td title='" . $rs['Nombre'] . " " . $rs['ApellidoPa'] . " " . $rs['ApellidoMa'] . " FORMA DE CONTACTAR:" . $rs['FormaContacto'] . " " . $rs['DatoContacto'] . "' width=\"2%\" align=\"center\" scope=\"row\">" . substr($rs['Nombre'] . $rs['ApellidoPa'] . $rs['ApellidoMa'], 0, 30) . "...</td>";
                                                                echo "<td title='" . $rs['CampaniaDetalle'] . "' width=\"2%\" align=\"center\" scope=\"row\">" . $rs['Campania'] . "</td>";
                                                                echo "<td width=\"2%\" align=\"center\" scope=\"row\">";
                                                                if ($rs['FechaHoraArribo'] != "") {
                                                                    echo date('ymd H:i', strtotime($rs['FechaHoraArribo']));
                                                                } else {
                                                                    echo "";
                                                                }
                                                                echo "</td>";
                                                                echo "<td width=\"2%\" align=\"center\" scope=\"row\">";
                                                                if ($rs['FechaHoraAbordo'] != "") {
                                                                    echo date('ymd H:i', strtotime($rs['FechaHoraAbordo']));
                                                                } else {
                                                                    echo "";
                                                                }
                                                                echo "</td>";
                                                                echo "<td width=\"2%\" align=\"center\" scope=\"row\">";
                                                                if ($rs['FechaHoraFin'] != "") {
                                                                    echo date('ymd H:i', strtotime($rs['FechaHoraFin']));
                                                                } else {
                                                                    echo "";
                                                                }
                                                                echo "</td>";
                                                                echo "<td title='" . $rs['DomicilioOrigen'] . "' width=\"2%\" align=\"center\" scope=\"row\">" . $rs['Origen'] . "</td>";
                                                                echo "<td title='" . $rs['DomicilioDestino'] . "' width=\"2%\" align=\"center\" scope=\"row\">" . $rs['Destino'] . "</td>";
                                                                //echo "<td width=\"2%\" align=\"center\" scope=\"row\">...</td>";
                                                                //echo "<td width=\"2%\" align=\"center\" scope=\"row\">" . $rs['Estatus'] . "</td>";
                                                                ?>
                                                                <?php
                                                                /* if ($rs['Activo'] == 1){
                                                                  echo "<td align='center' scope='row'>Activo</td>";
                                                                  }else{
                                                                  echo "<td align='center' scope='row'>Inactivo</td>";
                                                                  } */
                                                                echo "</tr>";
                                                            }
                                                            ?>
                                                        </tbody>
                                                    </table>
                                                </fieldset>
                                            </div>

                                        </div>

                                         <?php if ($permisos_grid->getModificar() && ($rs['estadoTicket'] != "2" && $rs['estadoTicket'] != "4" && $rs['IdEstatusAtencion'] != "16" && $rs['IdEstatusAtencion'] != "59")) {
                            ?> 
         <hr size="20"align="center" width="100%">                                                 
                            </div> 


                            <form id="formEnviarMensaje" name="formEnviarMensaje" action="/" method="POST" enctype="multipart/form-data">
                                    <?php // if ($muestra_links) { //Links mostrados para Loyalty     ?> 
        

                                <div class="form-row">
                                    
                                                <div class="form-group col-md-4">
                                       <label>Ticket</label>
                                            <input class="form-control" type='hidden' id='otraNota' name='otraNota' value=$muestra_links />
                                            <select class="form-control"  id="ticket_mensaje2" name="ticket_mensaje2" onchange="recargarListaTicketTecnico('mesa/monitorP.php', 'busqueda_ticket', null, 'cliente_ticket', 'ticket_color',
                                                                'estado_ticket', null, null, true, null, null, null, 'area', null, null, 'tecnico', 'prioridad');" >
                                                <option value="0">Selecciona el <?php echo $nombre_objeto; ?> para mensaje</option>
                                                <?php
                                                $tickets_mostrados_ord = $tickets_mostrados;
                                                sort($tickets_mostrados_ord);
                                                foreach ($tickets_mostrados_ord as $value) {
                                                    $s = "";
                                                    if ($ticketMensaje != "" && $ticketMensaje == $value) {
                                                        $s = "selected";
                                                    }
                                                    echo "<option value='$value' $s>$value</option>";
                                                }
                                                ?>
                                            </select>
                                            <span id="error_ticket2" style="color: red;"></span>
                                        </div>


                                    <div class="form-group  col-md-4">
                                        <label>Subir imagen:</label>
                                        <input class="form-control" type='file' name='file' id='file' >
                                        
                                    </div>
                                    <?php
                                    $FechaNota = date('Y') . "-" . date('m') . "-" . date('d');
                                    $HoraNota = date('H') . ":" . date('i') . ":" . date('s');
                                    ?>

                                    <div class="form-group col-md-4">
                                        <label for="fecha">Fecha</label>
                                        <span class="obligatorio"> *</span>
                                        <input class="form-control" type="text" id="fecha" name="fecha" value="<?php echo $FechaNota; ?>" />
                                    </div>

                                    <div class="form-group  col-md-4">
                                        <label for="orden">Hora<span class="obligatorio"> *</span></label>
                                        <input class="form-control" type="text" id="hora" name="hora" value="<?php echo $HoraNota; ?>" /> 
                                    </div>

                                    <div class="form-group col-md-4">
                                       <label>Eventos</label>
                                       <select  class="form-control"  id="estatusN" name="estatusN"  onchange="mostrarRefacciones();" onkeyup="copia('5');">
                                                <?php
                                                $catalogo = new Catalogo();
                                                if(empty($ticketMensaje)){
                                                    $ticketMensaje = 0;
                                                }
                                                $consul1 = "SELECT cu.IdPuesto, cnt.IdNotaTicket AS IdNota, cnt.DiagnosticoSol AS DS, IdEstatusAtencion AS IdEA 
                                                    FROM k_tecnicoticket AS ktt LEFT JOIN c_usuario AS cu ON ktt.IdUsuario=cu.IdUsuario
                                                    LEFT JOIN c_notaticket AS cnt ON cnt.IdTicket=ktt.IdTicket AND cnt.IdNotaTicket = (SELECT MAX(IdNotaTicket) FROM c_notaticket)
                                                    WHERE ktt.IdTicket=" . $ticketMensaje . ";";
                                                $query = $catalogo->obtenerLista($consul1);
                                                $rs = mysql_fetch_array($query);
                                                $idArea = "";
                                                if ($rs['IdPuesto'] == 101) {
                                                    $idArea = 101;
                                                } else {
                                                    if ($rs['IdPuesto'] == 108) {
                                                        $idArea = 102;
                                                    } else {
                                                        if ($rs['IdPuesto'] == 109) {
                                                            $idArea = 103;
                                                        }
                                                    }
                                                }
                                                $EstatusAc = $rs['IdEA'];
                                                $consul2 = "SELECT e.IdEstado, e.Nombre FROM c_estado AS e
                                                    LEFT JOIN k_flujoestado AS kfe ON kfe.IdEstado = e.IdEstado  
                                                    WHERE kfe.IdFlujo = 10 AND e.Activo = 1 ORDER BY e.Nombre;"; 
                                                $query = $catalogo->obtenerLista($consul2);
                                                echo "<option value=0>Selecciona un evento</option>";
                                                while ($rs = mysql_fetch_array($query)) {
                                                    $s = "";
                                                    if ($EstatusAc != "" && $EstatusAc == $rs['IdEstado']) {
                                                        $s = "selected='selected'";
                                                    }
                                                    echo "<option value='" . $rs['IdEstado'] . "' $s>" . $rs['Nombre'] . "</option>";
                                                }
                                                ?> 
                                            </select>
                                        </div>    
                                    <div class="form-group  col-md-4">
                                       <label>Mensaje</label>
                                       <input class="form-control" type="text" id="mensaje_enviar2" name="mensaje_enviar2" maxlength="500"/>
                                            <span id="error_mensaje2" style="color: red;"></span>
                                    </div>
                                            <input class="button btn btn-lg btn-block btn-outline-success mt-3 mb-3" type="submit" id="enviar_mensaje2" class="boton" value="Enviar Nota" />
                                      

                                     </div> 
        <hr size="20"align="center" width="100%">     
                                     <div>
                                            <?php
                                            if ($muestra_links) {
                                                if ($permisos_grid->getAlta()) {
                                                    echo "<td><a title='Nuevo Servicio' href='principal.php?mnu=viajes&action=alta_autoriza_especial&id='' target='_blank'><button id='nuevo_servicio' class='button'>Nuevo Servicio</button></a></td>";
                                                } else {
                                                    echo "<td></td>";
                                                }
                                                echo "<td><a title='Consulta Detalle de Servicios' href='principal.php?mnu=mesa&action=lista_ticket&id='' target='_blank'><button id='consulta_ticket' class='button'>Consulta Servicios</button></a></td>";
                                            } else {
                                                echo "<td></td><td></td>";
                                            }
                                            ?>
                                        </div>

                        
                                    <legend>Servicios asignados</legend> 
                                        
                                                    <?php
//                                                    }
                                                    $LatitudesTecnicos = "";
                                                    $LongitudesTecnicos = "";
                                                    $LatitudesTecnicosDia = "";
                                                    $LongitudesTecnicosDia = "";
                                                    $FechaHoraTecnicos = "";
                                                    $userTecnico = "";
                                                    $EstatusAtencion = "";
                                                    $LatitudesTickets = "";
                                                    $LongitudesTickets = "";
                                                    $NumeroTicket = "";
                                                    $PrioridadesTicket = "";
                                                    $Tiempo = "";
                                                    $FechaInicio = "";
                                                    $PorcentajeBateria = "";
                                                    ?>
                                <div class="form-group col-md-12">
                                    <h3>Mensajes</h3>     
                                    <table class="table">
                                        <thead>
                                            <tr>
                                                <?php
                                                $cabeceras = array("Operador", $nombre_objeto, "Seleccionar");
                                                $tecnicos_procesados = array();
//
//                                        if ($tiene_permisoTicket) {//Si tiene permiso para reporte de tickets
//                                            array_push($cabeceras, "");
//                                        }
//
//                                        if ($tiene_permisoFotografico) {//Si tiene permiso para reporte fotografico
//                                            array_push($cabeceras, "");
//                                        }
                                                //Cabeceras de la tabla
                                                for ($i = 0; $i < (count($cabeceras)); $i++) {
                                                    echo "<th scope=\"col\">" . $cabeceras[$i] . "</th>";
                                                }
                                                ?> 
                                            <tr>
                                            </thead>
                                            <?php
                                            $inactivo30T = "";
                                            $result = $catalogo->obtenerLista($consultaLista);
                                            $cuenta = 0;
                                            while ($rs = mysql_fetch_array($result)) {
                                                array_push($tickets_mostrados, $rs['IdTicket']);
                                                $LatitudesTickets .= ("/" . $rs['Latitud']);
                                                $LongitudesTickets .= ("/" . $rs['Longitud']);
                                                $NumeroTicket .= ("/" . $rs['IdTicket']);
                                                $PrioridadesTicket .= ("/" . $rs['Prioridad']);
                                                $Tiempo .= ("/" . $rs['Duracion'] . " " . $rs['Unidad']);
                                                $FechaInicio .= ("/" . $rs['FechaHoraInicio']);
                                                $EstatusAtencion .= ("/" . $rs['IdEstatusAtencion']);
                                                $today = date("Y-m-d");
                                                $fecha = $rs['FechaUbicacion'];
                                                $comentarioAdd = "";
                                                if (strcmp($rs['IdEstatusAtencion'], "null")) {
                                                    if (strcmp($rs['IdEstatusAtencion'], "51") == 0) {
                                                    } else if (strcmp($rs['IdEstatusAtencion'], "14") == 0) {
                                                        $comentarioAdd = "Check-out fallido " . $rs['FechaHoraInicio'];
                                                    }
                                                }
                                                //Tecnicos
                                                if (!in_array($rs['Loggin'], $tecnicos_procesados)) {
                                                    if (!isset($rs['IdUsuario']) || empty($rs['IdUsuario'])) {
                                                        continue;
                                                    }
                                                    $inactivo30 = "false";
                                                    if (strcmp($today, substr($fecha, 0, 10)) == 0) {
                                                        $horaActual = date("H");
                                                        $minutoActual = date("i");
                                                        $horaSis = substr($fecha, 11, 2);
                                                        $minSis = substr($fecha, 14, 2);
                                                        if ($horaActual - $horaSis > 1) {
                                                            $inactivo30 = "true";
                                                        } else if ($horaActual - $horaSis) {
                                                            $difSis = 60 - $minSis;
                                                            if ($minutoActual + $difSis > 30) {
                                                                $inactivo30 = "true";
                                                            }
                                                        } else if ($horaActual == $horaSis) {
                                                            if ($minutoActual - $minSis > 30) {
                                                                $inactivo30 = "true";
                                                            }
                                                        } else {
                                                            $inactivo30 = "true";
                                                        }
                                                    } else {
                                                        $inactivo30 = "true";
                                                    }
                                                    $inactivo30T.=("/" . $inactivo30);
                                                    $LatitudesTecnicos .= ("/" . $rs['LatitudUser']);
                                                    $LongitudesTecnicos .= ("/" . $rs['LongitudUser']);
                                                    $FechaHoraTecnicos .= ("/" . $rs['FechaUbicacion']);
                                                    $userTecnico .= ("/" . $rs['Loggin']);
                                                    array_push($tecnicos_procesados, $rs['Loggin']);
                                                    $consulta = "SELECT Latitud,Longitud,PorcentajeBateria FROM `c_ubicacionusuario` WHERE IdUsuario = " . $rs['IdUsuario'] . " AND DATE(Fecha) >= DATE(NOW());";
                                                    $result2 = $catalogo->obtenerLista($consulta);
                                                    $PorcentajeBateria .= ("/");
                                                    $LatitudesTecnicosDia .= ("/");
                                                    $LongitudesTecnicosDia .= ("/");
                                                    if (mysql_num_rows($result2) > 0) {
                                                        while ($rs2 = mysql_fetch_array($result2)) {
                                                            $PorcentajeBateria .= ($rs2['PorcentajeBateria'] . ",");
                                                            $LatitudesTecnicosDia .= ($rs2['Latitud'] . ",");
                                                            $LongitudesTecnicosDia .= ($rs2['Longitud'] . ",");
                                                        }
                                                        $PorcentajeBateria = substr($PorcentajeBateria, 0, strlen($PorcentajeBateria) - 1);
                                                        $LatitudesTecnicosDia = substr($LatitudesTecnicosDia, 0, strlen($LatitudesTecnicosDia) - 1);
                                                        $LongitudesTecnicosDia = substr($LongitudesTecnicosDia, 0, strlen($LongitudesTecnicosDia) - 1);
                                                    }
                                                    /* $PorcentajeBateria .= ("/".$rs['PorcentajeBateria']);
                                                    $LatitudesTecnicosDia .= ("/".$rs['LatitudesDia']);
                                                    $LongitudesTecnicosDia .= ("/".$rs['LongitudesDia']); */
                                                }

                                                $catalogoes = new Catalogo();
                                                $notes = "";
                                                $numes = 0;
                                                $consultaes = "SELECT * FROM c_notaticket cn INNER JOIN c_especial ce ON ce.idTicket=cn.IdTicket WHERE ce.idTicket=" . $rs['IdTicket'] . ";";
                                                $resultes = $catalogoes->obtenerLista($consultaes);
                                                if (mysql_num_rows($resultes) > 0) {
                                                    while ($rses = mysql_fetch_array($resultes)) {
                                                        $numes++;
                                                        $notes = $notes . $numes . ". " . $rses['DiagnosticoSol'] . "  ";
                                                    }
                                                }
                                                //Columnas de datos
                                                echo "<tr>";
                                                echo "<td title='" . $rs['Usuario'] . "'  scope='row'>" . $rs['Loggin'] . "</td>";
                                                echo "<td scope='row'><a href='#' onclick='detalleReporte(\"reportes/reporte_ticket.php\", \" " . $rs['IdTicket'] . " \", \" " . $rs['AreaAtencion'] . " \", \"0\"); return false;' title='Reporte' >" . $rs['IdTicket'] . "</a></td>";
                                                echo "<td scope='row'>" . "<input type='checkbox' id='servicio_" . $rs['IdTicket'] . "' name='servicio_" . $rs['IdTicket'] . "' value='" . $rs['IdTicket'] . "' onchange = 'seleccionarTicket();'/>" . "<input type='hidden' id='kservicio_$cuenta' name='kservicio_$cuenta' value='".$rs['IdTicket']."' />" . "</td>";
                                                echo "</tr>";
                                                $cuenta++;
                                            }
                                            ?>
                                            <br/>
                                            <?php
                                            echo "<input type='hidden' id='nombre_ticket' value='" . $nombre_objeto . "' />";
                                            echo "<div id='error_tecnico' style='color:red;'></div>";
                                            echo "<input type='hidden' id='LatitudesTickets' value='" . $LatitudesTickets . "' />";
                                            echo "<input type='hidden' id='LongitudesTickets' value='" . $LongitudesTickets . "' />";
                                            echo "<input type='hidden' id='NumeroTicket' value='" . $NumeroTicket . "' />";
                                            echo "<input type='hidden' id='EstatusTicket' value='" . $EstatusAtencion . "' />";
                                                    //Datos del tecnico
                                            echo "<input type='hidden' id='LatitudesTecnico' value='" . $LatitudesTecnicos . "' />";
                                            echo "<input type='hidden' id='LongitudesTecnico' value='" . $LongitudesTecnicos . "' />";
                                            echo "<input type='hidden' id='FechaTecnico' value='" . $FechaHoraTecnicos . "' />";
                                            echo "<input type='hidden' id='UsuarioTecnico' value='" . $userTecnico . "' />";
                                            echo "<input type='hidden' id='PrioridadesT' value='" . $PrioridadesTicket . "' />";
                                            echo "<input type='hidden' id='TiempoT' value='" . $Tiempo . "' />";
                                            echo "<input type='hidden' id='FechaInicioT' value='" . $FechaInicio . "' />";
                                            echo "<input type='hidden' id='PorcentajeBateria' value='" . $PorcentajeBateria . "' />";
                                            echo "<input type='hidden' id='LatitudesTecnicoDia' value='" . $LatitudesTecnicosDia . "' />";
                                            echo "<input type='hidden' id='LongitudesTecnicoDia' value='" . $LongitudesTecnicosDia . "' />";
                                            echo "<input type='hidden' id='inactivo30' value='" . $inactivo30T . "' />";
                                            echo "<input type='hidden' id='monitorServicios' value=true />";
                                            ?>
                                            <?php
                                            $parametroGlobal = new ParametroGlobal();
                                            for ($i = 20; $i < 26; $i++) {
                                                if ($parametroGlobal->getRegistroById($i)) {
                                                    $valor = $parametroGlobal->getValor();
                                                    echo "<input type='hidden' id='valor_" . $i . "' value='" . $valor . "' />";
                                                }
                                            }
                                            ?>
                                            <input type="checkbox" id="ticket_cerrado" name="ticket_cerrado" <?php echo $checked; ?> style="display: none;"/>
                                        </table>
                                    </div>
                                </div>
                                <hr size="20"align="center" width="100%">  
                        
                 
        
                           <table>
                                    <tr>
                                        <td valign="top" colspan="4">
                                            <div id="viatico">
                                                <table>
                                                    <tr>
                                                        <td>Tipo de viático:</td>
                                                        <td>
                                                            <select id='tipo_viatico' name='tipo_viatico' >
                                                                <option value='0'>Seleccione una opción</option>
                                                                <?php
                                                                $catalogo1 = new Catalogo();
                                                                $query1 = $catalogo1->obtenerLista("SELECT * FROM c_tipoviatico WHERE activo=1 ORDER BY nombre;");
                                                                while ($rs = mysql_fetch_array($query1)) {
                                                                    $s = "";
                                                                    if ($viatico != "" && $viatico == $rs['idTipoViatico']) {
                                                                        $s = "selected";
                                                                    }
                                                                    echo "<option value='" . $rs['idTipoViatico'] . "' $s>" . $rs['nombre'] . "</option>";
                                                                }
                                                                ?>
                                                            </select>
                                                        </td>
                                                        <td> Monto:</td>
                                                        <td><input type='text'  id='monto' name='monto' value="<?php
                                                            $monto = "";
                                                            echo $monto;
                                                            ?>" onkeyup="copia('4');"/>
                                                        </td>
                                                        <!--td><input type="checkbox" id="cobrar" name="cobrar" value="1" checked="checked"/> Cobrar al cliente</td>
                                                        <td><input type="checkbox" id="pagar" name="pagar" value="1"/> Pagar a operador</td-->
                                                    </tr>
                                                </table>                        
                                            </div>
                                            <div id="kmdiv">
                                                <table>
                                                    <tr>
                                                        <td> Kilómetro:</td>
                                                        <td><input type='text' style='max-width: 100px' id='km' name='km' value="<?php
                                                            $km = "";
                                                            echo $km;
                                                            ?>" onkeyup="copia('1');"/></td>
                                                    </tr>
                                                </table>  

                                            </div>
                                            <div id="tiempoE">
                                                <table>
                                                    <tr>
                                                        <td> Tiempo Real m:</td>
                                                        <td><input type='text' style='max-width: 100px' id='tiempo_esperaR' name='tiempo_esperaR' value="<?php
                                                            $tiempoER = "";
                                                            echo $tiempoER;
                                                            ?>" onkeyup="copia('3');"/></td>
                                                        <td> Tiempo Restado m:</td>
                                                        <td><input type='text' style='max-width: 100px' id='tiempo_esperaM' name='tiempo_esperaM' value="<?php
                                                            $tiempoEM = 0;
                                                            echo $tiempoEM;
                                                            ?>"/></td>
                                                    </tr>
                                                </table>                        
                                            </div>
                                            <div id="noBoleto">
                                                <table>
                                                    <tr>
                                                        <td> No Boleto:</td>
                                                        <td><input type='text' style='max-width: 100px' id='no_boleto' name='no_boleto' value="<?php
                                                            $noBoleto = "";
                                                            echo $noBoleto;
                                                            ?>" onkeyup="copia('2');"/></td>
                                                    </tr>
                                                </table>                        
                                            </div>
                                        </td>
                                    </tr>
                                <?php } ?>
                            </table>
                        </form>
                    


                    
    <hr size="20"align="center" width="100%"> 
                
                    <legend>Operadores disponibles</legend>
                            <form  id="formOrdenOperador" name="formOrdenOperador" action="/" method="POST"> 
                                <div class="form-row">
                                            <?php
                                            $Fecha = date('Y') . "-" . date('m') . "-" . date('d');
                                            $tecnicos = array();
                                            $servicio = "";
                                            $areas = "";
                                            $base = 1;
                                            $catalogo = new Catalogo();
                                            ?>
                             <div class="form-group col-md-4">
                                   <label>Base:</label>
                                        <select class="form-control" id="base" name="base" >
                                                            <?php
                                                            /* Inicializamos la clase */
                                                            $query = $catalogo->obtenerLista("SELECT * FROM c_base_operador WHERE Activo = 1 ORDER BY IdBase;");
                                                            while ($rs = mysql_fetch_array($query)) {
                                                                $s = "";
                                                                if (!empty($base) && $rs['IdBase'] == $base) {
                                                                    $s = "selected='selected'";
                                                                }
                                                                echo "<option value='" . $rs['IdBase'] . "' $s>" . $rs['Nombre'] . "</option>";
                                                            }
                                                            ?> 
                                                        </select>
                                 </div>
                                  <div class="form-group col-md-4">
                                      <label>Operador:</label>
                                            <select class="form-control" id="operador" name="operador" >
                                                            <option value="" >Selecciona un Operador</option>
                                                            <?php
                                                            $puestos = "OR IdPuesto= 108 OR IdPuesto= 109";
                                                            /* Inicializamos la clase */
                                                            $query = $catalogo->obtenerLista("SELECT (SELECT CASE WHEN ISNULL(coo.IdUsuario) THEN cu.IdUsuario ELSE '' END) AS IdUsuariosU, CONCAT(cu.Loggin,'-',cu.Nombre,' ',cu.ApellidoPaterno,' ',cu.ApellidoMaterno) AS Usuario FROM c_usuario AS cu
                                                                LEFT JOIN c_orden_operador AS coo ON coo.IdUsuario=cu.IdUsuario AND DATE(coo.FechaHora)=DATE(NOW())
                                                                WHERE cu.Activo = 1 AND (IdPuesto= 101)
                                                                GROUP BY cu.IdUsuario
                                                                ORDER BY cu.Loggin;");
                                                            while ($rs = mysql_fetch_array($query)) {
                                                                $s = "";
                                                                if (!empty($operador) && $rs['IdUsuario'] == $operador) {
                                                                    $s = "selected='selected'";
                                                                }
                                                                if ($rs['IdUsuariosU'] != '') {
                                                                    echo "<option value='" . $rs['IdUsuariosU'] . "' $s>" . $rs['Usuario'] . "</option>";
                                                                }
                                                            }
                                                            ?> 
                                           </select>
                                     </div>
                                 <div class="form-group col-md-4">
                                        <label>Servicios:</label>
                                                <select class="form-control" id="servicio" name="servicio" >
                                                            <option value="" >Selecciona un Servicio</option>
                                                            <?php
                                                            /* Inicializamos la clase */
                                                            $tickets = array();
                                                            $query = $catalogo->obtenerLista("SELECT t.IdTicket, nt.IdNotaTicket
                                                                FROM c_ticket AS t
                                                                LEFT JOIN c_notaticket AS nt ON nt.IdNotaTicket = (SELECT MAX(IdNotaTicket) FROM c_notaticket WHERE IdTicket = t.IdTicket AND IdEstatusAtencion = 22)
                                                                WHERE t.Activo = 1 AND t.EstadoDeTicket NOT IN(2,4);");
                                                            while ($rs = mysql_fetch_array($query)) {
                                                                $s = "";
                                                                if (!empty($servicio) && $value == $servicio) {
                                                                    $s = "selected='selected'";
                                                                }
                                                                if (!isset($rs['IdNotaTicket']) || empty($rs['IdNotaTicket'])) {
                                                                    echo "<option value='" . $rs['IdTicket'] . "' $s>" . $rs['IdTicket'] . "</option>";
                                                                } else {
                                                                    echo "<option value='" . $rs['IdTicket'] . "' $s>" . $rs['IdTicket'] . " (Asignado)</option>";
                                                                }
                                                            }
                                                            ?> 
                                                 </select>
                                             </div>
                             
                                            <button class="button btn btn-lg btn-block btn-outline-success mt-3 mb-3" id="agregarOperador" onclick="AgregarOperador('operador', 'base', 'mesa/monitorP.php');
                                                 return false;">Agregar Operador</button>
                                        <div id='error_agregar' style='color:red;'></div>
                                   

                               
                                             <button class="button btn btn-lg btn-block btn-outline-dark mt-3 mb-3" id="asigna_operador" onclick="relacionarOperadorServicio('mesa/monitorP.php');
                                                        return false;">Asignar ticket</button>
                                        <div id='error_operador' style='color:red;'></div>
                                  
                                   
                                    <div class="form-group col-12 col-md-4">  
                                            <div id='error_accion' style='color:red;'></div>
                                                <center> <a href='#' onclick='subirOperador("<?php echo $same_page; ?>");
                                                                return false;' id="subirOperador" title="Subir Operador" cursor: pointer;"><img  src="resources/images/dFlecha.png" width="24" height="24" /></a></center>
                                                
                                         </div> 
                                         <div class="form-group col-12 col-md-4">  
                                            <div id='error_accion' style='color:red;'></div>
                                                
                                                 <center><a href='#' onclick='bajarOperador("<?php echo $same_page; ?>");
                                                                return false;' id="bajarOperador" title="Bajar Operador"   cursor: pointer;"><img  src="resources/images/upFlecha.png" width="24" height="24"  /></a></center>
                                                
                                         </div> 
                                         <div class="form-group col-12 col-md-4">  
                                            <div id='error_accion' style='color:red;'></div>
                                                
                                                 <center><a href='#' onclick='eliminarOperador("<?php echo $same_page; ?>");
                                                                return false;' id="eliminarOperador" title="Quitar Operador"  cursor: pointer;"><img  src="resources/images/Erase.png" width="24" height="24"  /></a></center>
                                         </div> 

                                     </div>
                                     
                </form>
        
                     
     <hr size="20"align="center" width="100%"> 

                                    <legend>Operadores</legend>
                                            <?php
                                            $l = "AND coo.Activo = 1";
                                            $consulta = "SELECT coo.*, cb.Nombre AS Base, cb.Descripcion AS BaseDes, cp.Nombre AS Puesto, cu.Loggin, CONCAT(cu.Nombre,' ',cu.ApellidoPaterno,' ',cu.ApellidoMaterno) AS Usuario FROM c_orden_operador AS coo
                                                LEFT JOIN c_usuario AS cu ON cu.IdUsuario = coo.IdUsuario
                                                LEFT JOIN c_puesto AS cp ON cp.IdPuesto = cu.IdPuesto
                                                LEFT JOIN c_base_operador  AS cb ON coo.IdBase=cb.IdBase
                                                WHERE DATE(FechaHora) = '" . $Fecha . "' 
                                                ORDER BY coo.Orden;";
                                            $result = $catalogo->obtenerLista($consulta);
                                            ?> 
                                            <table class="tablaUsuarios table-responsive" style="width: 100%;">
                                                <thead>
                                                    <tr>
                                                        <?php
                                                        $cabeceras = array("Posición", "Base", "Operador", "Selecionar");
                                                        for ($i = 0; $i < (count($cabeceras)); $i++) {
                                                            echo "<th width=\"2%\" align=\"center\" scope=\"col\">" . $cabeceras[$i] . "</th>";
                                                        }
                                                        ?>                                                                      
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <?php
                                                    while ($rs = mysql_fetch_array($result)) {
                                                        echo "<tr>";
                                                        echo "<td align='center' scope='row'>" . $rs['Orden'] . "</td>";
                                                        echo "<td title='" . $rs['BaseDes'] . "' align='center' scope='row'>" . $rs['Base'] . "</td>";
//                                                        if ($rs['Activo'] == 0) {
//                                                            echo "<td align='center' scope='row'>Asignado</td>";
//                                                        } else {
//                                                            echo "<td align='center' scope='row'></td>";
//                                                        }
                                                        echo "<td  title='" . $rs['Usuario'] . "' align='center' scope='row'>" . $rs['Loggin'] . "</td>";
                                                        echo "<td align='center' scope='row'><input type='radio' id='radio_op" . $rs['IdUsuario'] . "' name='radio_op' value='" . $rs['IdOrdenOperador'] . "'/></td>";
                                                        echo "</tr>";
                                                    }
                                                    ?>
                                                    </tbody>
                                             </table>                                       
                                </div>
                </div>
         </body>
</html>