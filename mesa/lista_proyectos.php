<?php
session_start();

if (!isset($_SESSION['user']) || $_SESSION['user'] == "") {
    header("Location: ../index.php");
}

include_once("../WEB-INF/Classes/Catalogo.class.php");
include_once("../WEB-INF/Classes/PermisosSubMenu.class.php");
include_once("../WEB-INF/Classes/Usuario.class.php");
include_once("../WEB-INF/Classes/TFSGrupoCliente.class.php");
include_once("../WEB-INF/Classes/ParametroGlobal.class.php");
include_once("../WEB-INF/Classes/Parametros.class.php");

$catalogo = new Catalogo();
$permisos_grid = new PermisosSubMenu();
$permisos_grid2 = new PermisosSubMenu();
$nombre_objeto = $permisos_grid2->getNombreTicketSistema();
$nombre_puesto = $permisos_grid2->getNombreTecnicoSistema();
$nombre_nota = $permisos_grid2->getTitulo(7); //Nombre de las notas en la empresa actual
$nombre_areaAtencion = $permisos_grid2->getTitulo(8);

$historico = "mesa/historico_proyecto.php";

/* Para mantener los filtros y paginados de la tabla */
if (isset($_GET['page']) && isset($_GET['filter'])) {
    $filter = str_replace("_XX__XX_", " ", $_GET['filter']);
    $page = $_GET['page'];
} else {
    $page = "0";
    $filter = "";
}


$parametroGlobal = new ParametroGlobal();

$parametros = new Parametros();
$parametros->getRegistroById("8");
$liga = $parametros->getDescripcion();

$same_page = "mesa/lista_proyectos.php";
$permisos_grid->getPermisosSubmenu($_SESSION['idUsuario'], $same_page);
$lista_actividades = "mesa/lista_actividades.php";
$tiene_filtro = false;

$monitoreo = "mesa/monitoreo_actividades.php";

$tiene_permisoTicket = false;
$tiene_permisoFotografico = false;

if ($parametroGlobal->getRegistroById(15) && $parametroGlobal->getActivo() == "1") {
    $tiene_permisoFotografico = true;
}

if ($parametroGlobal->getRegistroById(16) && $parametroGlobal->getActivo() == "1") {
    $tiene_permisoTicket = true;
}

$pantalla_edicion = "mesa/alta_proyecto.php";
/* if($parametroGlobal->getRegistroById(28) && $parametroGlobal->getActivo() == "1"){
  $pantalla_edicion = $parametroGlobal->getValor();
  } */

$cerradoTicket = "t.EstadoDeTicket <> 2 AND ";
$having = " HAVING ((IdEstatusAtencion <> 16 AND IdEstatusAtencion <> 59) OR ISNULL(IdEstatusAtencion))";
$checked = "";
$morososTicket = "cl.IdEstatusCobranza <> 2 AND ";
$checkedMoroso = "";
$canceladoTicket = "t.EstadoDeTicket <> 4 AND ";
$checkedCancelado = "";
$tipoReporte = "";
$areaAtencion = "";
$cliente = "";
$colorPOST = "";
$estadoNota = "LEFT JOIN c_notaticket AS nt ON nt.IdNotaTicket = (SELECT MAX(IdNotaTicket) FROM c_notaticket AS nt2 WHERE nt2.IdTicket = t.IdTicket)";
$idTicket = "";
$NoSerie = "";
$FechaInicio = "";
$FechaFin = "";
$Where = "";
$NoGuia = "";
$tipo_join_estado = "LEFT";
$urlextra = "";
$cancel = "cancelado=0";
$groupBy = "";

if ((isset($_POST['idTicket']) && $_POST['idTicket'] != "") || (isset($_GET['id']) && $_GET['id'] != "")) {
    $tiene_filtro = true;
    if (isset($_POST['idTicket'])) {
        $idTicket = $_POST['idTicket'];
        $urlextra.="?idTicket=" . $_POST['idTicket'];
    } else {
        $idTicket = $_GET['id'];
        $urlextra.="?idTicket=" . $_GET['id'];
    }
    /* Si se busco un ticket en particular, habilitamos cerrados, morosos y cancelados */
    $checked = "checked='checked'";
    $checkedMoroso = "checked='checked'";
    $checkedCancelado = "checked='checked'";
}

if (isset($_POST['NoSerie']) && $_POST['NoSerie'] != "") {
    $tiene_filtro = true;
    $NoSerie = $_POST['NoSerie'];
    $Where = "WHERE (SELECT CASE WHEN e2.IdEstado = 2 THEN ( SELECT group_concat( ClaveEspEquipo SEPARATOR ', ') 
        FROM `c_pedido` WHERE IdTicket = t.IdTicket) ELSE t.NoSerieEquipo END) LIKE '%$NoSerie%'";
    if ($urlextra == "") {
        $urlextra.="?NoSerie=" . $NoSerie;
    } else {
        $urlextra.="&NoSerie=" . $NoSerie;
    }
}

if ((isset($_POST['FechaInicio']) && $_POST['FechaInicio'] != "") || (isset($_POST['FechaFin']) && $_POST['FechaFin'] != "")) {
    $tiene_filtro = true;
    if (isset($_POST['FechaInicio']) && $_POST['FechaInicio'] != "" && isset($_POST['FechaFin']) && $_POST['FechaFin'] != "") {
        $FechaInicio = $_POST['FechaInicio'];
        $FechaFin = $_POST['FechaFin'];
        if ($Where != "") {
            $Where .= " AND t.FechaHora BETWEEN '$FechaInicio  00:00:00' AND '$FechaFin 23:59:59'";
            $urlextra.="&FechaInicio=" . $FechaInicio . "&FechaFin=" . $FechaFin;
        } else {
            $Where = "WHERE t.FechaHora BETWEEN '$FechaInicio  00:00:00' AND '$FechaFin 23:59:59'";
            $urlextra.="?FechaInicio=" . $FechaInicio . "&FechaFin=" . $FechaFin;
        }
    } else if (isset($_POST['FechaInicio']) && $_POST['FechaInicio'] != "") {
        $FechaInicio = $_POST['FechaInicio'];
        if ($Where != "") {
            $Where .= " AND t.FechaHora >= '$FechaInicio'";
            $urlextra.="&FechaInicio=" . $FechaInicio;
        } else {
            $Where = "WHERE t.FechaHora >= '$FechaInicio'";
            $urlextra.="?FechaInicio=" . $FechaInicio;
        }
    } else if (isset($_POST['FechaFin']) && $_POST['FechaFin'] != "") {
        $FechaFin = $_POST['FechaFin'];
        if ($Where != "") {
            $Where .= " AND t.FechaHora <= '$FechaFin'";
            $urlextra.="&FechaFin=" . $FechaFin;
        } else {
            $Where = "WHERE t.FechaHora <= '$FechaFin'";
            $urlextra.="?FechaFin=" . $FechaFin;
        }
    }
}

/* Filtrar prioridad */
if (isset($_POST['Prioridad']) && $_POST['Prioridad'] != 0) {
    if ($Where != "") {
        $Where .= " AND t.Prioridad = " . $_POST['Prioridad'];
        $urlextra.="&Prioridad=" . $_POST['Prioridad'];
    } else {
        $Where = "WHERE t.Prioridad = " . $_POST['Prioridad'];
        $urlextra.="?Prioridad=" . $_POST['Prioridad'];
    }
}

/* Filtrar estado del ticket */
if (isset($_POST['estadoT']) && $_POST['estadoT'] != "") {
    if ($Where != "") {
        $Where .= " AND t.EstadoDeTicket = " . $_POST['estadoT'];
        $urlextra.="&estadoT=" . $_POST['estadoT'];
    } else {
        $Where = "WHERE t.EstadoDeTicket = " . $_POST['estadoT'];
        $urlextra.="?estadoT=" . $_POST['estadoT'];
    }
}

/* No mostrar suspendidos */
if ($Where != "") {
    $Where .= " AND cl.Suspendido = 0";
} else {
    $Where = " WHERE cl.Suspendido = 0";
}

if ((isset($_POST['cerrado']) && $_POST['cerrado'] != "false") || (isset($_POST['estadoT']) && $_POST['estadoT'] == 2)) {
    $cerradoTicket = "";
    if ($urlextra == "") {
        $urlextra.="?cerrado=1";
    } else {
        $urlextra.="&cerrado=1";
    }

    if (isset($_POST['cancelado']) && $_POST['cancelado'] != "false") {
        $having = "";
        $cancel = "cancelado=1";
    } else {
        $having = " HAVING ((IdEstatusAtencion <> 59) OR ISNULL(IdEstatusAtencion)) ";
        $cancel = "cancelado=0";
    }

    $checked = "checked='checked'";
} else {
    if ($urlextra == "") {
        $urlextra.="?cerrado=0";
    } else {
        $urlextra.="&cerrado=0";
    }
    if (isset($_POST['cancelado']) && $_POST['cancelado'] != "false") {
        $having = " HAVING ((IdEstatusAtencion <> 16) OR ISNULL(IdEstatusAtencion)) ";
        $cancel = "cancelado=1";
    }
}

if (isset($_POST['moroso']) && $_POST['moroso'] != "false") {
    $morososTicket = "";
    $checkedMoroso = "checked='checked'";
    if ($urlextra == "") {
        $urlextra.="?moroso=1";
    } else {
        $urlextra.="&moroso=1";
    }
}

if ((isset($_POST['cancelado']) && $_POST['cancelado'] != "false") || (isset($_POST['estadoT']) && $_POST['estadoT'] == 4)) {
    $canceladoTicket = "";
    $checkedCancelado = "checked='checked'";
    if (isset($_POST['cancelado']) && $_POST['cancelado'] != "false") {
        $cancel = "cancelado=1";
    }
}

if (isset($_POST['area']) && $_POST['area'] != "") {
    $tiene_filtro = true;
    $areaAtencion = " AreaAtencion = " . $_POST['area'] . " AND ";
    if ($urlextra == "") {
        $urlextra.="?area=" . $_POST['area'];
    } else {
        $urlextra.="&area=" . $_POST['area'];
    }
}

if (isset($_POST['tipoReporte']) && $_POST['tipoReporte'] != "") {
    $tiene_filtro = true;
    $tipoReporte = " TipoReporte = " . $_POST['tipoReporte'] . " AND ";
    if ($urlextra == "") {
        $urlextra.="?tipoReporte=" . $_POST['tipoReporte'];
    } else {
        $urlextra.="&tipoReporte=" . $_POST['tipoReporte'];
    }
}

if (isset($_POST['cliente']) && $_POST['cliente'] != "") {
    $tiene_filtro = true;
    $cliente = " AND t.NombreCliente IN (" . $_POST['cliente'] . ")";
    $cliente_array = explode("','", $_POST['cliente']);
    $cliente_array[0] = substr($cliente_array[0], 1, strlen($cliente_array[0]));
    $cliente_array[count($cliente_array) - 1] = substr($cliente_array[count($cliente_array) - 1], 0, strlen($cliente_array[count($cliente_array) - 1]) - 1);
    if ($urlextra == "") {
        $urlextra.="?cliente=" . $cliente;
    } else {
        $urlextra.="&cliente=" . $cliente;
    }
}

if (isset($_POST['color']) && $_POST['color'] != "") {
    $tiene_filtro = true;
    $colorPOST = $_POST['color'];
}

$checked_ultimo = "checked='checked'";
$checked_todo = "";
if (isset($_POST['estado']) && $_POST['estado'] != "") {
    if ($urlextra == "") {
        $urlextra.="?estado=" . $_POST['estado'];
    } else {
        $urlextra.="&estado=" . $_POST['estado'];
    }
    $tiene_filtro = true;
    if (isset($_POST['tipo_busqueda_estado']) && $_POST['tipo_busqueda_estado'] == "0") {//Se busca en la ultima nota
        if ($urlextra == "") {
            $urlextra.="?tipo_busqueda_estado=" . $_POST['tipo_busqueda_estado'];
        } else {
            $urlextra.="&tipo_busqueda_estado=" . $_POST['tipo_busqueda_estado'];
        }
        $estadoNota = "INNER JOIN c_notaticket AS nt ON nt.IdEstatusAtencion = " . $_POST['estado'] . " AND nt.IdNotaTicket = (SELECT MAX(IdNotaTicket) FROM c_notaticket AS nt2 WHERE nt2.IdTicket = t.IdTicket)";
    } else {//Se busca en todos los tickets
        if ($urlextra == "") {
            $urlextra.="?tipo_busqueda_estado=" . $_POST['tipo_busqueda_estado'];
        } else {
            $urlextra.="&tipo_busqueda_estado=" . $_POST['tipo_busqueda_estado'];
        }
        $checked_todo = "checked='checked'";
        $checked_ultimo = "";
        $estadoNota = "INNER JOIN c_notaticket AS nt ON nt.IdNotaTicket = (SELECT MAX(IdNotaTicket) FROM c_notaticket AS nt2 WHERE nt2.IdTicket = t.IdTicket AND nt2.IdEstatusAtencion = " . $_POST['estado'] . ")";
    }

    if ($_POST['estado'] == "16") {/* Si se selecciona el estado de cerrado, habiliatar el checkbox de cerrado también */
        $cerradoTicket = "";
        if ((isset($_POST['cancelado']) && $_POST['cancelado'] != "false") || (isset($_POST['estadoT']) && $_POST['estadoT'] == 4)) {
            $having = "";
            if (isset($_POST['cancelado']) && $_POST['cancelado'] != "false") {
                $cancel = "cancelado=1";
            }
        } else {
            if (isset($_POST['cancelado']) && $_POST['cancelado'] != "false") {
                $cancel = "cancelado=1";
            }
            if (isset($_POST['cancelado']) && $_POST['cancelado'] == "false") {
                $cancel = "cancelado=0";
            }
            $having = " HAVING ((IdEstatusAtencion <> 59) OR ISNULL(IdEstatusAtencion)) ";
        }
        $checked = "checked='checked'";
    }
}

if (isset($_POST['NoGuia']) && $_POST['NoGuia'] != "") {
    $NoGuia = $_POST['NoGuia'];
    if ($urlextra == "") {
        $urlextra.="?NoGuia=" . $_POST['NoGuia'];
    } else {
        $urlextra.="&NoGuia=" . $_POST['NoGuia'];
    }
    if ($having != "") {
        $having .= " AND NoGuia LIKE '%$NoGuia%' ";
    } else {
        $having = " HAVING NoGuia LIKE '%$NoGuia%' ";
    }
}

if ($urlextra == "") {
    $urlextra.="?" . $cancel;
} else {
    $urlextra.="&=" . $cancel;
}

$usuario = new Usuario();
$permisoReabrir = ($usuario->puedeReabrir($_SESSION['idUsuario']) ? true : false); //Si es super, se le dan permisos para reabrir tickets
$idUsuario = $_SESSION['idUsuario'];

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
                $estados = "";
            }
        }
    } else {
        $estado = "";
    }
} else {
    $estado = "";
}

//$estado = "";
$estado_falla = "";
$tipo_join = "LEFT";
$tecnico = "";

//GERENTE DE SW
if ($usuario->isUsuarioPuesto($idUsuario, 19)) {
    //$estado = "e2.IdEstado = 6 AND ";
    $estado_falla = "e1.IdEstado <> 15 AND ";
    $tipo_join = "INNER";
}

//GERENTE DE HW
if ($usuario->isUsuarioPuesto($idUsuario, 17)) {
    //$estado = "e2.IdEstado = 5 AND ";
    $estado_falla = "e1.IdEstado <> 15 AND ";
    $tipo_join = "INNER";
}

//TECNICO SW
if ($usuario->isUsuarioPuesto($idUsuario, 20)) {
    //$estado = "e2.IdEstado = 6 AND ";
    $estado_falla = "e1.IdEstado <> 15 AND ";
    $tipo_join = "INNER";
    $tecnico = "INNER JOIN k_tecnicoticket AS ktt ON ktt.IdUsuario = $idUsuario AND ktt.tipo = 2 AND ktt.IdTicket = t.IdTicket";
}

//TECNICO HW
if ($usuario->isUsuarioPuesto($idUsuario, 18)) {
    //$estado = "e2.IdEstado = 5 AND ";
    $estado_falla = "e1.IdEstado <> 15 AND ";
    $tipo_join = "INNER";
    $tecnico = "INNER JOIN k_tecnicoticket AS ktt ON ktt.IdUsuario = $idUsuario AND ktt.tipo = 1 AND ktt.IdTicket = t.IdTicket";
}

if(empty($tecnico)){
    $tecnico = "LEFT JOIN k_tecnicoticket AS ktt ON ktt.IdTicket = t.IdTicket";
}

//Vendedor
$vendedor = "";
if ($usuario->isUsuarioPuesto($idUsuario, 11)) {
    $vendedor = " AND EjecutivoCuenta = $idUsuario ";
}

//TFS
$tfs = "";
if ($usuario->isUsuarioPuesto($idUsuario, 21)) {
    $TFSGrupo = new TFSGrupoCliente();
    if ($TFSGrupo->tieneGrupo($idUsuario)) {
        $tfs = " INNER JOIN k_tfsgrupo AS ktg ON ktg.IdTfs = $idUsuario AND cl.ClaveGrupo = ktg.ClaveGrupo ";
    } else {
        $tfs = " INNER JOIN k_tfscliente AS tfs ON tfs.IdUsuario = " . $idUsuario . " AND tfs.Tipo = 1 AND tfs.ClaveCliente = t.ClaveCliente ";
    }
}
?>
<!DOCTYPE html>
<html>
    <head>
        <script type="text/javascript" src="resources/js/paginas/lista_proyecto.js"></script>
        <!-- Bootstrap core CSS -->
        <link href="resources/css/Bootstrap 4/bootstrap.min.css" rel="stylesheet">
        <!-- FontAwesome para iconos -->
        <link href="resources/css/Bootstrap 4/fontawesome/css/all.min.css" rel="stylesheet">
    </head>  
    <body>
        <div class="principal">
        <div style="font-size:30px; cursor:pointer; display:inline;" onclick="openNav()" id="open">☰</div>
        <div  id="mySidenav" class="sidenav">
            <a href="javascript:void(0)" class="closeSidenav float-right" onclick="closeNav()">&times;</a><br>
            <a href="#" onclick="cambiarContenidos('<?php echo $same_page; ?>','<?php echo $nombre_objeto; ?>'); return false;">Lista de <?php echo $nombre_objeto; ?></a><br>
            <a href="#" onclick="cambiarContenidos('<?php echo $lista_actividades; ?>','<?php echo $nombre_nota; ?>'); return false;">Lista de <?php echo $nombre_nota; ?></a><br>   
            <a href="#" onclick="monitorActividades('<?php echo $monitoreo; ?>'); return false;">Monitor de <?php echo $nombre_nota; ?></a>         
        </div>
        <div id="main_panel">            
            <form action="#" method="post">
                <div class="container-fluid">  
                <div class="form-row">        
                    <div class="form-group col-md-4"> 
                      <label>Folio <?php echo $nombre_objeto; ?></label>
                      <input class="form-control" id="busqueda_ticket" name="busqueda_ticket" value="<?php echo $idTicket; ?>" />
                      <div id="error_busqueda_ticket" style="display: none; color:red;">Ingresa s&oacute;lo n&uacute;meros por favor</div>
                    </div>                           

               <div class="form-group col-md-4"> 
                      <label><?php echo $nombre_puesto; ?></label>
                      <input class="form-control" id="tecnico" name="tecnico" value="<?php echo $NoSerie; ?>" />
                    </div>    
                <div class="form-group col-md-4"> 
                      <label>Fecha inicio</label>
                      <input class="fecha" id="fecha_inicio" name="fecha_inicio" class="fecha" value="<?php echo $FechaInicio; ?>" />
                    </div>      
                <div class="form-group col-md-4"> 
                      <label>Fecha final</label>
                      <input class="fecha"  id="fecha_fin" name="fecha_fin" class="fecha" value="<?php echo $FechaFin; ?>"/>      
                    </div>   

               <div class="form-group col-md-4"> 
                      <label><?php echo $nombre_areaAtencion; ?></label>
                            <select class="form-control" id="area_ticket" name="area_ticket" >
                                <?php
                                /* Inicializamos la clase */
                                $query = $catalogo->obtenerLista("SELECT DISTINCT(e.IdEstado) AS IdEstado, e.Nombre FROM c_estado AS e
                                INNER JOIN k_flujoestado AS kfe ON kfe.IdEstado = e.IdEstado AND (kfe.IdFlujo = 2 OR e.IdEstado = 2) ORDER BY Nombre;");
                                echo "<option value=''>Todas las áreas</option>";
                                while ($rs = mysql_fetch_array($query)) {
                                    $s = "";
                                    if (isset($_POST['area']) && $_POST['area'] == $rs['IdEstado']) {
                                        $s = "selected='selected'";
                                    }
                                    echo "<option value='" . $rs['IdEstado'] . "' $s>" . $rs['Nombre'] . "</option>";
                                }
                                ?> 
                            </select>
                        </div>

                <div class="form-group col-md-4"> 
                      <label>Tipo reporte</label>
                            <select class="form-control" id="reporte_ticket" name="reporte_ticket" >
                                <?php
                                /* Inicializamos la clase */
                                $query = $catalogo->obtenerLista("SELECT e.IdEstado, e.Nombre FROM c_estado AS e
                                INNER JOIN k_flujoestado AS kfe ON kfe.IdEstado = e.IdEstado AND kfe.IdFlujo = 1 ORDER BY Nombre;");
                                echo "<option value=''>Todos los tipos</option>";
                                while ($rs = mysql_fetch_array($query)) {
                                    $s = "";
                                    if (isset($_POST['tipoReporte']) && $_POST['tipoReporte'] == $rs['IdEstado']) {
                                        $s = "selected='selected'";
                                    }
                                    echo "<option value='" . $rs['IdEstado'] . "' $s>" . $rs['Nombre'] . "</option>";
                                }
                                ?> 
                            </select>
                    </div>

                <div class="form-group col-md-4"> 
                      <label>Estado</label>
                            <select class="form-control" id="estadot" name="estadot" >
                                <?php
                                echo "<option value=''>Todos los estados</option>";
                                $resultET = $catalogo->getListaAlta("c_estadoticket", "Nombre");
                                while ($rsET = mysql_fetch_array($resultET)) {
                                    $s = "";
                                    if (isset($_POST['estadoT']) && $_POST['estadoT'] == $rsET['IdEstadoTicket']) {
                                        $s = "selected='selected'";
                                    }
                                    echo "<option value='" . $rsET['IdEstadoTicket'] . "' $s>" . $rsET['Nombre'] . "</option>";
                                }
                                ?>
                            </select>
                          </div>
                              
                    <div class="form-group col-md-4"> 
                      <label>SubEstado</label>
                            <select class="form-control" id="estado_ticket" name="estado_ticket"  >
                                <?php
                                /* Inicializamos la clase */
                                $query = $catalogo->obtenerLista("SELECT  e.IdEstado, e.Nombre FROM c_estado AS e
                                INNER JOIN k_flujoestado AS kfe ON kfe.IdEstado = e.IdEstado AND kfe.IdFlujo = 6 ORDER BY Nombre;");
                                echo "<option value=''>Todos los estados</option>";
                                while ($rs = mysql_fetch_array($query)) {
                                    if ($rs['IdEstado'] == "2") {
                                        continue;
                                    }
                                    $s = "";
                                    if (isset($_POST['estado']) && $_POST['estado'] == $rs['IdEstado']) {
                                        $s = "selected='selected'";
                                    }
                                    echo "<option value='" . $rs['IdEstado'] . "' $s>" . $rs['Nombre'] . "</option>";
                                }
                                ?> 
                            </select>
                                              
                    </div>  

                    <div class="form-group col-md-4"> 
                      <label>Prioridad del <?php echo $nombre_objeto; ?>: </label>
                            <?php
                            $result2 = $catalogo->obtenerLista("SELECT  pt.IdPrioridad, pt.Prioridad, tp.TipoPrioridad,  c.Hexadecimal
                                    FROM `c_prioridadticket` AS pt
                                    LEFT JOIN c_color AS c ON c.IdColor = pt.IdColor
                                    LEFT JOIN c_tipoprioridad AS tp ON tp.IdTipoPrioridad = pt.IdTipoPrioridad WHERE pt.Activo = 1;");
                            echo "<select  class='form-control'  id='prioridad' name='prioridad' >";
                            echo "<option value = 0 >Todas las prioridades</option>";
                            while ($rs2 = mysql_fetch_array($result2)) {
                                $s = "";
                                if (isset($_POST['Prioridad']) && $rs2['IdPrioridad'] == $_POST['Prioridad']) {
                                    $s = "selected='selected'";
                                }
                                echo "<option value='" . $rs2['IdPrioridad'] . "' style='background: #" . $rs2['Hexadecimal'] . ";' $s>" . $rs2['Prioridad'] . " (" . $rs2['TipoPrioridad'] . ")</option>";
                            }
                            echo "</select>";
                            ?>
                        </div>

                <div class="form-group col-md-4">
                       <br>
                       <br>
                       <input type="checkbox" id="ticket_cerrado" name="ticket_cerrado" <?php echo $checked; ?> />
                       <label>Ver <?php echo $nombre_objeto; ?>s cerrados</label><br> 
                       <input type="checkbox" id="ticket_cancelado" name="ticket_cancelado" <?php echo $checkedCancelado; ?> />
                       <label>Ver <?php echo $nombre_objeto; ?>s cancelados</label>

                   </div>          
                <div class="form-group col-md-4">
                           
                            <label>Tipo busqueda de estado:</label>
                            <br>
                            <br>
                            <input type="radio" id="ultimo_estado0" name="ultimo_estado" value="0" <?php echo $checked_ultimo; ?>/>
                            <label>Último estado</label><br> 
                            <input type="radio" id="ultimo_estado1" name="ultimo_estado" value="1" <?php echo $checked_todo; ?>/>
                            <label>Todos los estado del <?php echo $nombre_objeto; ?> </label>
                    </div>

                <div class="form-group col-md-4">
                    <label>Cliente</label>
                            <select class="form-control" id="cliente_ticket" name="cliente_ticket[]" class="multiselect"  multiple="multiple">
                                <?php
                                if (empty($clientes_permitidos)) {
                                    $query = $catalogo->getListaAlta("c_cliente", "NombreRazonSocial");
                                } else {
                                    $query = $catalogo->obtenerLista("SELECT NombreRazonSocial, ClaveCliente FROM `c_cliente` WHERE ClaveCliente IN($array_clientes) AND Activo = 1 ORDER BY cliente;");
                                }
                                echo "<option value=''>Todos los clientes</option>";
                                while ($rs = mysql_fetch_array($query)) {
                                    $s = "";
                                    if (isset($_POST['cliente']) && $_POST['cliente'] != "" && in_array($rs['ClaveCliente'], $cliente_array)) {
                                        $s = "selected='selected'";
                                    }
                                    echo "<option value='" . $rs['ClaveCliente'] . "' $s>" . $rs['NombreRazonSocial'] . "</option>";
                                }
                                ?> 
                            </select>
                        </div>

                          
                            <?php if($permisos_grid->getAlta()){  ?>
                            <input type="button" class="button btn btn-lg btn-block btn-outline-success mt-3 mb-3" value="&#9769; Agregar <?php echo $nombre_objeto; ?>" 
                                   onclick="cambiarContenidos('<?php echo $pantalla_edicion."?regresar=$same_page"; ?>'); return false;"/>
                            <?php } ?>
                     
                      
                            <input type="button" class="button btn btn-lg btn-block btn-outline-secondary mt-3 mb-3" onclick="recargarListaTicket('mesa/lista_proyectos.php', 'ticket_cerrado', 'cliente_ticket', 'ticket_color',
                                            'estado_ticket', 'ticket_moroso', 'ticket_cancelado', true, 'tecnico', 'fecha_inicio',
                                            'fecha_fin', 'area_ticket', 'reporte_ticket', 'no_guia', 'prioridad', 'estadot');
                                    return false;" id="boton_aceptar" name="boton_aceptar" value="Mostrar <?php echo $nombre_objeto; ?>s"/>
                                       
                <div>  
                </div>
            </form>
            <br/><br/>
</div>
            <?php
            if ((isset($_POST['mostrar']) && $_POST['mostrar'] == "true") || $idTicket != "") {/* Si se quiere mostrar el grid */
            ?>
            <div class="form-row">  
                <div class="form-group col-md-12"> 
                    <!-- Se edito el JS lista-proyectos.js para quitar estilos en esta tabla, la linea 101 -->
                <table id="tAlmacen" class="table-responsive">
                    <thead>
                        <tr>
                            <?php
                            $cabeceras = array("Cliente",$nombre_objeto, "Nombre","Fecha Inicial","Fecha Final Prevista" ,"Fecha Final Real", $nombre_areaAtencion, "Presupuesto","Progreso","Último estatus $nombre_objeto", "Última $nombre_nota", "Fecha $nombre_nota", "$nombre_puesto asignado", /*"Día atraso",*/ "", "", "","");
                            if ($tiene_permisoTicket) {//Si tiene permiso para reporte de tickets
                                array_push($cabeceras, "");
                            }

                            if ($tiene_permisoFotografico) {//Si tiene permiso para reporte fotografico
                                array_push($cabeceras, "");
                            }

                            for ($i = 0; $i < (count($cabeceras)); $i++) {
                                echo "<th width=\"2%\" align=\"center\" scope=\"col\">" . $cabeceras[$i] . "</th>";
                            }
                            ?>                        
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        if ($idTicket == "") {
                            $consulta = "SELECT
                                $idCampanias
                                b.id_bitacora,
                                t.IdTicket,t.Nombre AS proyecto,
                                t.NoTicketCliente,
                                cl.Suspendido,
                                NoTicketDistribuidor,
                                DATE(t.FechaHora) AS FechaHora,t.FechaFinPrevisto, t.FechaFinReal,
                                t.DescripcionReporte,t.Presupuesto, t.Progreso,
                                t.NombreCentroCosto,
                                t.TipoReporte,
                                CONCAT(usut.Nombre,' ',usut.ApellidoPaterno,' ',usut.ApellidoMaterno) AS tecnicoAtiende,
                                (CASE WHEN (!ISNULL(nt.IdNotaTicket) AND nt.IdEstatusAtencion = 16) THEN DATEDIFF(nt.FechaHora,t.FechaCreacion) ELSE DATEDIFF(NOW(),t.FechaCreacion) END) AS DiferenciaDias,
                                (SELECT CASE WHEN e2.IdEstado = 2 THEN (SELECT group_concat(ClaveEspEquipo separator ', ') FROM `c_pedido` WHERE IdTicket = t.IdTicket) ELSE t.NoSerieEquipo END) AS NumSerie,
                                DATEDIFF(NOW(),t.FechaHora) AS diferencia,
                                t.NombreCliente,
                                clg.Nombre AS NombreGrupo,
                                cl.IdEstatusCobranza,
                                e.IdEstadoTicket AS estadoTicket,                                         
                                tc.IdTipoCliente AS tipoCliente,
                                e2.Nombre AS area,
                                e2.IdEstado AS idArea,                                
                                (SELECT CASE WHEN !ISNULL(cgz.NombreZona) THEN cgz.NombreZona WHEN !ISNULL(cgz3.NombreZona) THEN cgz3.NombreZona ELSE cgz2.NombreZona END) AS ubicacionTicket,
                                e3.Nombre AS estadoNota,
                                nt.IdEstatusAtencion,
                                nt.DiagnosticoSol,
                                cee.color,
                                col.Hexadecimal,
                                t.NoGuia AS NoGuia2,
                                nt.FechaHora AS FechaNota,
                                (SELECT CASE WHEN ISNULL(nt.UsuarioUltimaModificacion) THEN t.UsuarioUltimaModificacion ELSE nt.UsuarioUltimaModificacion END) as UltimoUsuarioNota,
                                t.Resurtido,
                                (SELECT GROUP_CONCAT(DISTINCT(k_enviotoner.NoGuia) SEPARATOR ', ') AS NoGuia FROM `k_enviotoner`
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
                                $estadoNota
                                LEFT JOIN c_estado AS e3 ON nt.IdEstatusAtencion = e3.IdEstado
                                LEFT JOIN c_escalamientoEstado AS cee ON (cee.idEstado = nt.IdEstatusAtencion AND cee.prioridad = t.Prioridad)
                                LEFT JOIN c_prioridadticket AS pt ON t.Prioridad = pt.IdPrioridad
                                LEFT JOIN c_color AS col ON pt.IdColor = col.IdColor
                                $campaniasLEFT
                                LEFT JOIN  c_bitacora AS b ON b.NoSerie = t.NoSerieEquipo
                                $tecnico 
                                LEFT JOIN c_usuario AS usut ON usut.IdUsuario = ktt.IdUsuario 
                                $Where
                                $groupBy
                                $having
                                ORDER BY IdTicket";
                            if (!$tiene_filtro) {
                                $consulta.=" DESC LIMIT 0,500";
                            }
                            $consulta.=";";
                        } else {
                            $consulta = "SELECT
                                $idCampanias
                                b.id_bitacora,
                                t.IdTicket,t.Nombre AS proyecto,                 
                                t.NoTicketCliente,
                                t.NoTicketDistribuidor,
                                DATE(t.FechaHora) AS FechaHora,t.FechaFinPrevisto, t.FechaFinReal,
                                t.DescripcionReporte,t.Presupuesto, t.Progreso,
                                t.NombreCentroCosto,
                                t.TipoReporte,
                                CONCAT(usut.Nombre,' ',usut.ApellidoPaterno,' ',usut.ApellidoMaterno) AS tecnicoAtiende,
                                t.NoGuia AS NoGuia2,
                                (SELECT CASE WHEN e2.IdEstado = 2 
                                THEN(SELECT group_concat(ClaveEspEquipo SEPARATOR ', ') FROM `c_pedido` WHERE IdTicket = t.IdTicket)
                                ELSE t.NoSerieEquipo END) AS NumSerie,
                                DATEDIFF(NOW(), t.FechaHora) AS diferencia,
                                t.NombreCliente,
                                clg.Nombre AS NombreGrupo,
                                cl.IdEstatusCobranza,
                                cl.Suspendido,
                                e.IdEstadoTicket AS estadoTicket,                                
                                tc.IdTipoCliente AS tipoCliente,
                                e2.Nombre AS area,
                                e2.IdEstado AS idArea,
                                (SELECT CASE WHEN !ISNULL(cgz.NombreZona) THEN cgz.NombreZona WHEN !ISNULL(cgz3.NombreZona) THEN cgz3.NombreZona ELSE cgz2.NombreZona END) AS ubicacionTicket,
                                e3.Nombre AS estadoNota,
                                nt.IdEstatusAtencion,
                                nt.DiagnosticoSol,
                                nt.FechaHora AS FechaNota,
                                cee.color,
                                col.Hexadecimal,
                                (SELECT CASE WHEN ISNULL(nt.UsuarioUltimaModificacion) THEN t.UsuarioUltimaModificacion ELSE nt.UsuarioUltimaModificacion END) as UltimoUsuarioNota,
                                t.Resurtido,
                                (SELECT GROUP_CONCAT(DISTINCT(k_enviotoner.NoGuia) SEPARATOR ', ') AS NoGuia FROM `k_enviotoner`
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
                                LEFT JOIN c_notaticket AS nt ON nt.IdNotaTicket = (
                                SELECT
                                        MAX(IdNotaTicket)
                                FROM
                                        c_notaticket AS nt2
                                WHERE
                                        nt2.IdTicket = t.IdTicket
                                )
                                LEFT JOIN c_estado AS e3 ON nt.IdEstatusAtencion = e3.IdEstado
                                LEFT JOIN c_escalamientoEstado AS cee ON (cee.idEstado = nt.IdEstatusAtencion AND cee.prioridad = t.Prioridad)
                                LEFT JOIN c_prioridadticket AS pt ON t.Prioridad = pt.IdPrioridad
                                LEFT JOIN c_color AS col ON pt.IdColor = col.IdColor
                                $campaniasLEFT
                                LEFT JOIN  c_bitacora AS b ON b.NoSerie = t.NoSerieEquipo 
                                $tecnico 
                                LEFT JOIN c_usuario AS usut ON usut.IdUsuario = ktt.IdUsuario ";
                            if (is_numeric($idTicket)) {
                                $consulta.=" WHERE (t.IdTicket = $idTicket OR NoTicketCliente = '$idTicket' OR NoTicketDistribuidor = '$idTicket') ";
                            } else {
                                $consulta.=" WHERE (NoTicketCliente = '$idTicket' OR NoTicketDistribuidor = '$idTicket') ";
                            }
                            $consulta.=" ORDER BY IdTicket;";
                        }
//                        if($idCampanias != ""){echo $consulta;}
                        $query = $catalogo->obtenerLista($consulta);
                        while ($rs = mysql_fetch_array($query)) {/* Recorremos todos los tickets resultantes del query */
                            /*                             * *********************    Obtenemos el color de la fila   ******************************** */
                            $booleanFecha = FALSE;
                            if(empty($rs['FechaHora'])){
                                $booleanFecha = TRUE;
                            }else{
                                $fecha_limite = strtotime("2014-03-31");
                                $fecha_ticket = strtotime($rs['FechaHora']);
                                if ($fecha_ticket >= $fecha_limite) {
                                    $booleanFecha = TRUE;
                                } else {
                                    $booleanFecha = FALSE;
                                }                                
                            }
                            $color = "#F7F7DE";
                            if ($rs['Suspendido'] != "0") {
                                echo "<br/><h2>El $nombre_objeto " . $rs['IdTicket'] . " pertenece al cliente suspendido " . $rs['NombreCliente'] . "</h2><br/>";
                                break;
                            }
                            if (isset($rs['IdEstatusAtencion'])) {/* Si hay estado de la ultima nota */
                                if ($rs['IdEstatusAtencion'] != "16" && (isset($rs['estadoTicket']) && $rs['estadoTicket'] != "2")) {/* Si el ticket no esta cerrado */
                                    if (strtoupper($rs['tipoCliente']) == "1") {/* Si el cliente es VIP */
                                        if (number_format($rs['diferencia']) >= 2) {/* Si ya van mas de 2 dias que se levanto el ticket */
                                            if ($colorPOST != "" && $colorPOST != "rojo") {
                                                continue;
                                            }
                                            $color = "#DC381F";
                                        } else {
                                            if ($colorPOST != "" && $colorPOST != "amarillo") {
                                                continue;
                                            }
                                            $color = "#FFF380";
                                        }
                                    } else {/* Si no es cliente VIP */
                                        if (number_format($rs['diferencia']) >= 7) {/* Si ya van mas de 7 dias que se levanto el ticket */
                                            if ($colorPOST != "" && $colorPOST != "rojo") {
                                                continue;
                                            }
                                            $color = "#DC381F";
                                        }
                                    }
                                }
                            } else {/* Si no hay notas, vemos el estado del ticket */
                                if ($rs['estadoTicket'] != "2") {/* Si el ticket no esta cerrado */
                                    if (strtoupper($rs['tipoCliente']) == "1") {/* Si el cliente es VIP */
                                        if (number_format($rs['diferencia']) >= 2) {/* Si ya van mas de 2 dias que se levanto el ticket */
                                            if ($colorPOST != "" && $colorPOST != "rojo") {
                                                continue;
                                            }
                                            $color = "#DC381F";
                                        } else {
                                            if ($colorPOST != "" && $colorPOST != "amarillo") {
                                                continue;
                                            }
                                            $color = "#FFF380";
                                        }
                                    } else {/* Si no es cliente VIP */
                                        if (number_format($rs['diferencia']) >= 7) {/* Si ya van mas de 7 dias que se levanto el ticket */
                                            if ($colorPOST != "" && $colorPOST != "rojo") {
                                                continue;
                                            }
                                            $color = "#DC381F";
                                        }
                                    }
                                }
                            }

                            /* En dado caso que se un ticekt verde pero en el filtro se selecciono otro color */
                            if ($color == "#F7F7DE" && ($colorPOST != "verde" && $colorPOST != "")) {
                                continue;
                            }

                            if ($rs['IdEstatusCobranza'] == "2") {/* Cliente moroso */
                                $color = "#D462FF";
                            }

                            if ($rs['estadoTicket'] == "2" || $rs['IdEstatusAtencion'] == "16") {/* Ticket cerrado */
                                $color = "#F7F7DE";
                            }

                            if ($rs['estadoTicket'] == "4" || $rs['IdEstatusAtencion'] == "59") {/* Ticket cancelado */
                                $color = "#D1D0CE";
                            }

                            if ($rs['TipoReporte'] == "26") {/* Si es Mtto preventivo */
                                $color = "#00FFFF";
                            }

                            if (isset($rs['Hexadecimal']) && $rs['Hexadecimal']) {
                                $color = "#" . $rs['Hexadecimal'];
                            }
                            echo "<tr style='background-color: $color; color:black;'>";
                            
                            $grupo = "";
                            if (isset($rs['NombreGrupo']) && $rs['NombreGrupo'] != "") {
                                $grupo = "(" . $rs['NombreGrupo'] . ") ";
                            }
                            echo "<td align='center' scope='row'>$grupo" . $rs['NombreCliente'] . " - " . $rs['NombreCentroCosto'] . "</td>";
                            
                            $ticket = $rs['IdTicket'];
                            if (isset($rs['NoTicketCliente']) && $rs['NoTicketCliente'] != "") {
                                $ticket.=(" / TC: " . $rs['NoTicketCliente']);
                            }
                            if (isset($rs['NoTicketDistribuidor']) && $rs['NoTicketDistribuidor'] != "") {
                                $ticket.=(" / TD: " . $rs['NoTicketDistribuidor']);
                            }
                            echo "<td align='center' scope='row'>" . $ticket . "</td>";
                            
                            $nombreProyecto = $rs['proyecto'];
                            echo "<td align='center' scope='row'>" . $nombreProyecto . "</td>";

                            if ($idCampanias != "") {
                                echo "<td align='center' scope='row'>" . $rs['FechaHoraCompleta'] . "</td>";
                            } else {
                                echo "<td align='center' scope='row'>" . $rs['FechaHora'] . "</td>";
                            }

                            echo "<td align='center' scope='row'>" . $rs['FechaFinPrevisto'] . "</td>";
                            echo "<td align='center' scope='row'>" . $rs['FechaFinReal'] . "</td>";
                            /*if ($rs['Resurtido'] == '1') {
                                echo "<td align='center' scope='row'>Resurtido de míni almacén</td>";
                            } else if (isset($rs['id_bitacora'])) {
                                echo "<td data-toggle='tooltip' title='" . $rs['DatosUsuario'] . "' align='center' scope='row'><a href='#' onclick='cambiarContenidos(\"almacen/alta_bitacora.php?consulta_tiquet=mesa/lista_ticket.php&NoSerie=" . $rs['NumSerie'] . "\"); return false;'>" . $rs['NumSerie'] . "</a></td>";
                            } else {
                                $series = explode(",", $rs['NumSerie']);
                                $texto = "";
                                foreach ($series as $value) {
                                    $texto.= "<a href='#' onclick='cambiarContenidos(\"almacen/alta_bitacora.php?consulta_tiquet=mesa/lista_ticket.php&NoSerie=$value\"); return false;'>" . $value . "</a>,";
                                }
                                $texto = substr($texto, 0, strlen($texto) - 1);
                                echo "<td align='center' scope='row'>$texto</td>";
                            }*/
                            

                            echo "<td align='center' scope='row'>" . $rs['area'] . "</td>";
                            
                            /*if ($idCampanias != "") {
                                if (isset($rs['CampaniaP']) && $rs['CampaniaP'] != "") {
                                    echo "<td align='center' scope='row'>" . $rs['CampaniaP'] . "</td>";
                                } else {
                                    if (isset($rs['CampaniaE']) && $rs['CampaniaE'] != "") {
                                        echo "<td align='center' scope='row'>" . $rs['CampaniaE'] . "</td>";
                                    } else {
                                        echo "<td align='center' scope='row'>Otro</td>";
                                    }
                                }
                            } else {
                                echo "<td align='center' scope='row'>" . $rs['ubicacionTicket'] . "</td>";
                            }*/

                            //cho "<td align='center' scope='row'>" . $rs['DescripcionReporte'] . "</td>";
                            if(!empty($rs['Presupuesto'])){
                                echo "<td align='center' scope='row'>$" . number_format($rs['Presupuesto'],2,".",",") . "</td>";
                            }else{
                                echo "<td align='center' scope='row'></td>";
                            }
                            
                            echo "<td align='center' scope='row'>";
                            if(!empty($rs['Progreso'])){
                                echo $rs['Progreso'] . "%";
                            }
                            echo "</td>";

                            if (isset($rs['estadoNota'])) {
                                $c = "";
                                if (isset($rs['color'])) {
                                    $c = "bgcolor = '" . $rs['color'] . "'";
                                }
                                echo "<td align='center' scope='row' $c>" . $rs['estadoNota'] . "</td>";
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

                            echo "<td align='center' scope='row'>" . $rs['tecnicoAtiende'] . "</td>";

                            /*if ($idCampanias != "") {
                                $src_destino = "viajes/MapaReferencias.php?LatitudO=" . $rs['LatitudO'] . "&LatitudD=" . $rs['LatitudD'] . "&LongitudO=" . $rs['LongitudO'] . "&LongitudD=" . $rs['LongitudD'];
                                echo "<td align='center' scope='row'>" . $rs['LugarDestino'] . "
                                      <a title='Destino' href='" . $liga . "principal.php?mnu=viajes&action=MapaReferencias&id=" . $rs['IdTicket'] . "' target='_blank'><img src=\"resources/images/mapa.png\" width=\"28\" height=\"28\" style=\"margin-left: 18%;\"/></a>
                                      </td>";
                            } else {
                                if (isset($rs['NoGuia']) && $rs['NoGuia'] != "") {
                                    echo "<td align='center' scope='row'>" . $rs['NoGuia'] . "</td>";
                                } else {
                                    echo "<td align='center' scope='row'>" . $rs['NoGuia2'] . "</td>";
                                }
                            }*/

                            //echo "<td align='center' scope='row'>" . $rs['DiferenciaDias'] . "</td>";

                            if ($rs['idArea'] == "2") {
                                $src = $_SESSION['liga'] . "/Operacion/MesaServicio/ConsultaDetalleTicketToner.aspx?Operacion=&IdTicket=" . $rs['IdTicket'] . "&Vista=Detalle&uguid=" . $_SESSION['user'];
                            } else {
                                $src = $_SESSION['liga'] . "/Operacion/MesaServicio/ConsultaDetalleTicketFalla.aspx?Operacion=&IdTicket=" . $rs['IdTicket'] . "&Vista=Detalle&uguid=" . $_SESSION['user'];
                            }
                            ?>
                            <td align='center' scope='row'> 
                                <?php if ($permisos_grid->getConsulta()) { ?>
                                    <?php
                                    if ($booleanFecha) {
                                        ?>
                                        <a href='#' onclick='detalleTicket("mesa/alta_ticketphp.php", "<?php echo $rs['IdTicket']; ?>", "<?php echo $rs['TipoReporte']; ?>", "1", "", "0");
                                                                return false;' title='Detalle' ><img src="resources/images/Textpreview.png"/></a>
                                       <?php } else { ?>
                                        <a href='#' onclick='detalleTicket("mesa/alta_ticketphp.php", "<?php echo $rs['IdTicket']; ?>", "<?php echo $rs['TipoReporte']; ?>", "1", "", "1");
                                                                return false;' title='Detalle' ><img src="resources/images/Textpreview.png"/></a>
                                           <?php
                                       }
                                       ?>
                                   <?php } ?>
                            </td>
                            <?php
                            if ($rs['estadoTicket'] != "2" && $rs['estadoTicket'] != "4" && $rs['IdEstatusAtencion'] != "16" && $rs['IdEstatusAtencion'] != "59") {
                                if ($rs['idArea'] == "2") {
                                    $src = $_SESSION['liga'] . "/Operacion/MesaServicio/ConsultaDetalleTicketToner.aspx?Operacion=&IdTicket=" . $rs['IdTicket'] . "&Vista=Modificar&uguid=" . $_SESSION['user'];
                                } else {
                                    $src = $_SESSION['liga'] . "/Operacion/MesaServicio/ConsultaDetalleTicketFalla.aspx?Operacion=&IdTicket=" . $rs['IdTicket'] . "&Vista=Modificar&uguid=" . $_SESSION['user'];
                                }
                                ?>
                                <td align='center' scope='row'> 
                                    <?php if ($permisos_grid->getModificar()) { ?>
                                        <?php
                                        if ($booleanFecha) {
                                            ?>
                                            <a href='#' onclick='editarTicket("<?php echo $pantalla_edicion; ?>", "<?php echo $rs['IdTicket']; ?>", "<?php echo $rs['TipoReporte']; ?>", "0");
                                                                        return false;' title='Modificar' ><img src="resources/images/Modify.png"/></a>
                                           <?php } ?>  
                                       <?php } ?>
                                </td>                            
                                <td align='center' scope='row'>
                                    <?php if ($permisos_grid->getModificar()) { ?>
                                        <a href='#' onclick='AgregarNotaTicketLista("mesa", "lista_actividades",<?php echo $rs['IdTicket']; ?>, "");
                                                                return false;' title='<?php echo $nombre_nota; ?>' >
                                            <img src="resources/images/notes.ico" style="width:24px; height: 24px; "/>
                                        </a>
                                    <?php } ?>
                                </td>

                                <?php
                            } else {
                                if ($permisoReabrir) {
                                    echo "<td align='center' scope='row'><a href='#' title='Re-abrir $nombre_objeto' onclick='reabrirTicket(\"" . $rs['IdTicket'] . "\"); return false;'><img src='resources/images/Apply.png'/></a></td>";
                                } else {
                                    echo "<td align='center' scope='row'></td>";
                                }
                                echo "<td align='center' scope='row'></td>";
                            }
                            ?>
                            <td align="center" scope="row">
                                <a href='#' onclick="cambiarContenidos('<?php echo $historico . "?id=" . $rs['IdTicket']?>','Mesa de Ayuda > Histórico de <?php echo $nombre_objeto?>')" title='Histórico' >
                                    <img src="resources/images/historico.png" width="25" height="25"/>
                                </a>
                            </td>
                            <?php
                            if ($tiene_permisoTicket) {
                                if ((int) $rs['Resurtido'] == 1) {  //104 en produccion, 201 pruebas
                                    ?>
                                    <td align='center' scope='row'>                             
                                        <a href='reportes/reporte_ticket_resurtido.php?idTicket=<?php echo $rs['IdTicket']; ?>' target="_blank" title='Reporte' >
                                            <img src="resources/images/icono_impresora.png" width="35" height="35"/>
                                        </a>
                                    </td>
                                    <?php
                                } else {
                                    ?>
                                    <td align='center' scope='row'>                                    
                                        <a href='reportes/reporte_ticket.php?idTicket=<?php echo $rs['IdTicket']; ?>' target="_blank" title='Reporte' >
                                            <img src="resources/images/icono_impresora.png" width="35" height="35"/>
                                        </a>
                                    </td>
                                    <?php
                                }
                            }
                            if ($tiene_permisoFotografico) {
                                ?>
                                <td align='center' scope='row'>                             
                                    <a href='reportes/reporte_fotografico.php?idTicket=<?php echo $rs['IdTicket']; ?>' target="_blank" title='Reporte' >
                                        <img src="resources/images/camera2.png" width="35" height="35"/>
                                    </a>
                                </td>
                                <?php
                            }
                            echo "</tr>";
                        }
                    ?>
                    </tbody>
                    <input type="hidden" id="color_hidden" name="color_hidden" value="<?php echo $colorPOST; ?>"/>           
                    <input type="hidden" id="page" name="page" value="<?php echo $page; ?>"/>
                    <input type="hidden" id="filter" name="filter" value="<?php echo $filter; ?>"/>
                    <input type="hidden" id="regresar" name="regresar" value="<?php echo $same_page ?>"/>
                </table>
                </div>
            </div>
                <?php
                //Fin de si se quiere mostrar el grid
                //echo "$consulta<br>";
            } else {
                echo "<input type='hidden' id='vacio' name='vacio' value='vacio' />";
            }
            ?>

    </body>
</html> 