<?php
session_start();

if (!isset($_SESSION['user']) || $_SESSION['user'] == "") {
    header("Location: index.php");
}

include_once("../WEB-INF/Classes/Catalogo.class.php");
include_once("../WEB-INF/Classes/PermisosSubMenu.class.php");

$permisos_grid = new PermisosSubMenu();
$same_page = "almacen/toner_solicitado.php";
$permisos_grid->getPermisosSubmenu($_SESSION['idUsuario'], $same_page);

$tecnicos = array();
$catalogo = new Catalogo();
$query = $catalogo->obtenerLista("SELECT IdUsuario, CONCAT(Nombre,' ',ApellidoPaterno,' ',ApellidoMaterno) AS tecnico FROM `c_usuario` WHERE IdPuesto = 18;");

$tecnicos["0"] = "Selecciona al técnico";
while ($rs = mysql_fetch_array($query)) {
    $tecnicos[$rs['IdUsuario']] = $rs['tecnico'];
}

$paginaActual = "almacen/toner_solicitado.php";
$idTicket = "";
$cliente = "";
$color = "";
$colorPOST = "";
$checked = "";
$cerrados = "";
$cancelados = "";
$enviados = "";
$moroso = "checked";
$whereCerrado = "AND (t.EstadoDeTicket=1 OR t.EstadoDeTicket=3 OR t.EstadoDeTicket=5)";
$whereMoroso = "AND cl.IdEstatusCobranza=1";
$claveCentroCosto = "";
$claveCliente = "";
$mostrarGrid = "0";
$whereEnviados = " AND (Cantidad - CantidadSurtida) > 0 ";

if(isset($_GET['etoner'])){
    $paginaActual.="?etoner=1";
}else if(isset($_GET['stoner']) && (int)$_GET['stoner'] == 1){
    $paginaActual.="?stoner=1";
}

if (isset($_POST["ticket"]) && $_POST["ticket"] != "") {
    $idTicket = $_POST["ticket"];
    $cerrados = "checked='checked'";
    $cancelados = "checked='checked'";
    $moroso = "checked='checked'";
    $enviados = "checked='checked'";
    $mostrarGrid = "1";
}

if (isset($_POST["cliente"]) && $_POST["cliente"] != "") {
    $cliente = "t.ClaveCliente = '" . $_POST['cliente'] . "' AND ";
    $claveCliente = $_POST["cliente"];
    $mostrarGrid = "1";
}else if(isset($_GET['cliente']) && $_GET['cliente'] != ""){
    $cliente = "t.ClaveCliente = '" . $_GET['cliente'] . "' AND ";
    $claveCliente = $_GET["cliente"];
    $mostrarGrid = "1";
}
if (isset($_POST["color"]) && $_POST["color"] != ""){
    $colorPOST = $_POST["color"];
    $mostrarGrid = "1";
}

if (isset($_POST["cerrado"]) && $_POST["cerrado"] == "1") {
    $cerrados = "checked";
    $mostrarGrid = "1";
}

if (isset($_POST["cancelado"]) && $_POST["cancelado"] == "1") {
    $cancelados = "checked";
    $mostrarGrid = "1";
}

if (isset($_POST["enviados"]) && $_POST["enviados"] == "1") {
    $enviados = "checked='checked'";
    $mostrarGrid = "1";
    $whereEnviados = "";
}

$ultimo_cambio = "";
if(isset($_POST['ultimo_cambio']) && $_POST['ultimo_cambio']){
    $ultimo_cambio = "checked='checked'";
}

if ($cerrados != "" && $cancelados != "") {
    $whereCerrado = "";
} else {
    if ($cerrados != "")
        $whereCerrado = "AND t.EstadoDeTicket<>4";
    else if ($cancelados != "")
        $whereCerrado = "AND t.EstadoDeTicket<>2";
}

if ( (isset($_POST["moroso"]) && $_POST["moroso"] == "1") || !isset($_POST['moroso'])) {
    //$moroso = "checked";
    $whereMoroso = "AND (cl.IdEstatusCobranza=1 OR cl.IdEstatusCobranza=2)";
    //$mostrarGrid = "1";
}


$idestatus = array();
$nombreEstatus = array();
$contador = 0;
$estado = $catalogo->obtenerLista("SELECT * FROM c_estado e WHERE e.IdEstado = '20' OR e.IdEstado = '21' OR e.IdEstado = '68'   
                                    ORDER BY e.Nombre ASC");
while ($rs = mysql_fetch_array($estado)) {
    $idestatus[$contador] = $rs['IdEstado'];
    $nombreEstatus[$contador] = $rs['Nombre'];
    $contador++;
}

$contador = 0;
$idUsuario = "";
$userAlmacen = "";
$almacenPredeterminado = "6";
$almacen1 = $catalogo->obtenerLista("SELECT IdPuesto,IdUsuario,IdAlmacen  FROM c_usuario WHERE IdUsuario='" . $_SESSION['idUsuario'] . "'");
while ($rs = mysql_fetch_array($almacen1)) {
    $idUsuario = $rs['IdPuesto'];
    $userAlmacen = $rs['IdUsuario'];
    if(isset($rs['IdAlmacen'])){
        $almacenPredeterminado = $rs['IdAlmacen'];
    }
}

$whereLocalidad = "";
if (isset($_POST['localidad']) && $_POST['localidad'] != "") {    
    $claveCentroCosto = $_POST['localidad'];
    $whereLocalidad = " AND t.ClaveCentroCosto='$claveCentroCosto' ";
    $mostrarGrid = "1";
}

?>
<!DOCTYPE html>
<html lang="es">
    <head>               
        <script type="text/javascript" language="javascript" src="resources/js/paginas/listaValidarRefaccion.js"></script>
        <script type="text/javascript" language="javascript" src="resources/js/paginas/validadRefaccion.js"></script>               
    </head>
    <body>
        <style>
            .iu-multiselect{
                width: 100%!important;
            }
        </style>
        <div class="principal">  
        <div class="form-row">
            <div class="form-group col-md-3">
                <label for="busqueda_ticket">Ticket</label>
                <input class="form-control" type="text" id="busqueda_ticket" name="busqueda_ticket" value="<?php echo $idTicket ?>"/>
                <div id="error_busqueda_ticket" style="display: none; color:red;">Ingresa s&oacute;lo n&uacute;meros por favor</div>
            </div>
            <div class="form-group col-md-3">
                <label for="ticket_color">Color</label>
                <select id="ticket_color" name="ticket_color" class="custom-select">
                    <option value="">Todos</option>
                    <option value="rojo" style="background: #DC381F;">Urgente</option>
                    <option value="amarillo" style="background: #FFF380;">Importante</option>
                    <option value="verde" style="background: #F7F7DE;">Normal</option>
                </select>
            </div>
            <div class="form-group col-md-3">
                <label for="cliente_ticket">Cliente</label>
                <select id="cliente_ticket" name="cliente_ticket" onchange="LocalidadesCliente(this.value);" class="custom-select filtro">
                            <?php                            
                            $query = $catalogo->getListaAlta("c_cliente", "NombreRazonSocial");
                            echo "<option value=''>Seleccione un cliente</option>";
                            while ($rs = mysql_fetch_array($query)) {
                                $s = "";
                                if (!empty($claveCliente) && $claveCliente == $rs['ClaveCliente']) {
                                    $s = "selected='selected'";
                                }
                                echo "<option value='" . $rs['ClaveCliente'] . "' $s>" . $rs['NombreRazonSocial'] . "</option>";
                            }
                            ?> 
                </select>
                <div id="errorCliente"></div>
            </div>
            <div class="form-group col-md-3">
                <label for="localidad">Localidad</label>
                <select id="localidad" name="localidad" class="custom-select filtro">
                            <option value="">Seleccione una localidad</option>
                            <?php                                                        
                            $query = $catalogo->obtenerLista("SELECT * FROM `c_centrocosto` AS cc WHERE cc.ClaveCliente = '$claveCliente';
                            ORDER BY cc.Nombre;");
                            while ($rs1 = mysql_fetch_array($query)) {
                                $s = "";
                                if (isset($_POST['localidad']) && $_POST['localidad'] == $rs1['ClaveCentroCosto']) {
                                    $s = "selected='selected'";
                                }
                                echo "<option value='" . $rs1['ClaveCentroCosto'] . "' $s>" . $rs1['Nombre'] . "</option>";
                            }
                            ?>                            
                </select>
                <div id="errorLocalidad"></div>
            </div>
        </div>
        <div class="form-row p-4">
            <div class="form-group col-md-2">
                <input type="checkbox" name="verCerrado" id="verCerrado" <?php echo $cerrados ?> value="0"/>Ver cerrados
            </div>
            <div class="form-group col-md-2">
                <input type="checkbox" name="verCancelado" id="verCancelado" <?php echo $cancelados ?> value="0"/>Ver cancelados
            </div>
            <div class="form-group col-md-2">
                <input type="checkbox" name="ultimo_cambio" id="ultimo_cambio" <?php echo $ultimo_cambio; ?>/>Ver último cambio tóner
            </div>
            <div class="form-group col-md-2">
                <input type="checkbox" name="verEnviados" id="verEnviados" <?php echo $enviados; ?>/>Ver enviados
            </div>
            <div class="form-group col-md-2">
                <input type="checkbox" name="verMoroso" id="verMoroso" <?php echo $moroso ?> value="1"/>Ver Morosos
            </div>
        </div>   
        <div class="form-row">
                <div class="col-md-3">
                    <input type="Button" id="boton_aceptar" name="boton_aceptar" value="Buscar ticket" class="btn btn-success btn-block" onclick="BuscarTicketSolicitud('<?php echo $paginaActual; ?>');"/>
                </div>
                <?php if(isset($_GET['regresar']) && $_GET['regresar'] != ""){?>
                    <div class="col-md-3">
                        <input type="button" class="btn btn-danger btn-block" value="Regresar" onclick="cambiarContenidos('<?php echo $_GET['regresar']?>','');">
                    </div>
                <?php }?>
        </div>    
        
            
        

            <?php if( (isset($_POST['mostrar']) && $_POST['mostrar']=="true") || $idTicket != ""){ 
                    if(!isset($_GET['etoner']))
                    {
                        echo "<br/><br/>";
                        if(!isset($_GET['stoner'])){
                            echo "<div class='form-row'><div class='col-md-12'><h1>T&oacute;ner solicitado</h1></div></div>";
                        }
                if($permisos_grid->getModificar()){ ?>
                <div class="form-row">
                <?php if(!isset($_GET['stoner'])){ ?>
                
                    <div class="col-md-3"><br>
                        <input type="button" class="btn btn-outline-success btn-block" value="Antender y Enviar"  onclick="atenderNotaTonerSolicitado('almacen/toner_solicitado.php', '1');" />
                    </div>
                <?php  } ?>
                <div class="col-md-3"><br>
                <input type="Button" id="boton_atender" name="boton_atender" value="Atender" class="btn btn-outline-info btn-block" onclick="atenderNotaTonerSolicitado('<?php echo $paginaActual; ?>','0');"/><br/>
                </div>
            <?php } echo"</div>";
                    if(isset($_GET['stoner']) && (int)$_GET['stoner'] == 1){
                        echo "<input type = 'hidden' id='stoner' name='stoner' value='1' />";
                    }else{
                        echo "<a href='#' id='liga_solicitado' onclick='mostrarSolicitados(); return false;'>Mostrar solicitados</a>";
                    }
            ?>
            <div style="display: inline; float: right;"><input type="checkbox" id="slc_todo_solicitado" onclick="seleccionarTodosSolicitados();"/>
                <div id="mensaje_sel" style="display: inline; ">Seleccionar todo</div></div>
            <div class="table-responsive">
                <table id="tAlmacen" class="tabla_grid" style="display: none; width: 100%;">
                <thead>
                    <tr>
                        <?php                            
                        if($ultimo_cambio == ""){
                            $cabeceras = array("Ticket", "Fecha" ,"Cliente", "", "Toner", "Área atención", "Último estado" , "Agregar Nota", "Cantidad", "Almacén", "Existencia", "Estatus", "", "");
                        }else{
                            $cabeceras = array("Ticket", "Fecha" ,"Cliente", "Fecha último cambio tóner", "", "Toner", "Área atención", "Último estado", "Agregar Nota", "Cantidad", "Almacén", "Existencia", "Estatus", "", "");
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
                        $consulta = "SELECT t.IdTicket,t.Resurtido,nt.IdNotaTicket,c.Modelo,c.IdColor,cl.NombreRazonSocial,nr.CantidadSurtida,nr.NoSerieEquipo,
                            (SELECT ar.Descripcion FROM c_area ar WHERE ar.IdArea=t.AreaAtencion)AS area, DATE(t.FechaHora) AS FechaHora,
                            ((5 * (DATEDIFF(NOW(), t.FechaHora) DIV 7) + MID('0123444401233334012222340111123400001234000123440', 7 * WEEKDAY(t.FechaHora) + WEEKDAY(NOW()) + 1, 1))) AS diasCreacionNota,
                            ((5 * (DATEDIFF(NOW(), lastnt.FechaHora) DIV 7) + MID('0123444401233334012222340111123400001234000123440', 7 * WEEKDAY(lastnt.FechaHora) + WEEKDAY(NOW()) + 1, 1))) AS diasUltimaNota,
                            DATE_FORMAT(NOW(), '%H') AS ahora,
                            DATE_FORMAT(t.FechaHora,'%H') AS horaTicket,
                            DATE_FORMAT(lastnt.FechaHora, '%H') AS horaUltimaModificacion,
                            e3.Nombre AS ultimoEstatus,
                            (CASE WHEN !ISNULL(ar.IdArea) THEN ar.Descripcion ELSE ar2.Descripcion END) AS areaAtencion2,
                            (SELECT e2.Nombre FROM c_estado e2 WHERE e2.IdEstado=t.AreaAtencion)AS areaAtencion,
                            CONCAT ('(',nr.Cantidad,') ',c.Modelo,' / ',c.NoParte,' / ',c.Descripcion) AS refaccion,DATEDIFF(NOW(), t.FechaHora) AS diferencia,nt.IdEstatusAtencion
                            ,t.EstadoDeTicket,cl.IdTipoCliente,cl.IdEstatusCobranza,t.TipoReporte,e.Nombre AS ultimoEstatus,nt.DiagnosticoSol,t.NombreCentroCosto,t.DescripcionReporte,nr.Cantidad,c.NoParte,nt.MostrarCliente,nt.Activo,nt.UsuarioSolicitud,
                            (SELECT (SELECT CASE WHEN ac.cantidad_existencia IS NULL THEN '0' ELSE ac.cantidad_existencia END) AS cantidade 
                            FROM k_almacencomponente ac 
                            WHERE ac.NoParte=nr.NoParteComponente AND ac.id_almacen=$almacenPredeterminado) AS cantidadExistente,
                            (
                            SELECT MIN(c_notaticket.IdNotaTicket) 
                            FROM c_notaticket 
                            LEFT JOIN k_nota_refaccion ON c_notaticket.IdNotaTicket = k_nota_refaccion.IdNotaTicket
                            WHERE c_notaticket.IdTicket = nt.IdTicket AND c_notaticket.IdEstatusAtencion = 67 
                            AND k_nota_refaccion.NoParteComponente = nr.NoParteComponente
                            ) AS IdNotaSolicitud
                            FROM k_nota_refaccion nr,c_notaticket nt,c_ticket t,c_componente c,c_cliente cl,c_estado e, c_estado e2
                            LEFT JOIN c_area AS ar2 ON ar2.IdArea = e2.IdArea  
                            LEFT JOIN c_notaticket AS lastnt ON lastnt.IdNotaTicket = (SELECT MAX(IdNotaTicket) FROM c_notaticket WHERE IdTicket = t.IdTicket)
                            LEFT JOIN c_estado AS e3 ON lastnt.IdEstatusAtencion = e3.IdEstado 
                            LEFT JOIN c_area AS ar ON ar.IdArea = e3.IdArea
                            WHERE t.IdTicket=nt.IdTicket AND e2.IdEstado = t.AreaAtencion AND nt.IdNotaTicket=nr.IdNotaTicket AND nr.NoParteComponente=c.NoParte AND t.ClaveCliente=cl.ClaveCliente
                            AND $cliente nt.IdEstatusAtencion=e.IdEstado
                            AND (nt.IdEstatusAtencion=65 OR nt.IdEstatusAtencion=20)
                            AND nr.Cantidad>0 AND c.IdTipoComponente=2 $whereCerrado $whereMoroso
                            $whereLocalidad 
                            HAVING !ISNULL(IdNotaSolicitud)
                            ORDER BY nt.IdNotaTicket,c.Modelo ASC";
                    } else if ($idTicket != "") {
                        $consulta = "SELECT t.IdTicket,t.Resurtido,nt.IdNotaTicket,c.Modelo,c.IdColor,cl.NombreRazonSocial,nr.CantidadSurtida,nr.NoSerieEquipo,
                            (SELECT ar.Descripcion FROM c_area ar WHERE ar.IdArea=t.AreaAtencion)AS area, DATE(t.FechaHora) AS FechaHora,
                            ((5 * (DATEDIFF(NOW(), t.FechaHora) DIV 7) + MID('0123444401233334012222340111123400001234000123440', 7 * WEEKDAY(t.FechaHora) + WEEKDAY(NOW()) + 1, 1))) AS diasCreacionNota,
                            ((5 * (DATEDIFF(NOW(), lastnt.FechaHora) DIV 7) + MID('0123444401233334012222340111123400001234000123440', 7 * WEEKDAY(lastnt.FechaHora) + WEEKDAY(NOW()) + 1, 1))) AS diasUltimaNota,
                            DATE_FORMAT(NOW(), '%H') AS ahora,
                            DATE_FORMAT(t.FechaHora,'%H') AS horaTicket,
                            DATE_FORMAT(lastnt.FechaHora, '%H') AS horaUltimaModificacion,
                            (SELECT e2.Nombre FROM c_estado e2 WHERE e2.IdEstado=t.AreaAtencion)AS areaAtencion,
                            (CASE WHEN !ISNULL(ar.IdArea) THEN ar.Descripcion ELSE ar2.Descripcion END) AS areaAtencion2,
                            e3.Nombre AS ultimoEstatus,
                            CONCAT ('(',nr.Cantidad,') ',c.Modelo,' / ',c.NoParte,' / ',c.Descripcion) AS refaccion,DATEDIFF(NOW(), t.FechaHora) AS diferencia,nt.IdEstatusAtencion
                            ,t.EstadoDeTicket,cl.IdTipoCliente,cl.IdEstatusCobranza,t.TipoReporte,e.Nombre AS ultimoEstatus,nt.DiagnosticoSol,t.NombreCentroCosto,t.DescripcionReporte,nr.Cantidad,c.NoParte,nt.MostrarCliente,nt.Activo,nt.UsuarioSolicitud,
                            (SELECT (SELECT CASE WHEN ac.cantidad_existencia IS NULL THEN '0' ELSE ac.cantidad_existencia END) AS cantidade 
                            FROM k_almacencomponente ac 
                            WHERE ac.NoParte=nr.NoParteComponente AND ac.id_almacen=$almacenPredeterminado) AS cantidadExistente,
                            (
                            SELECT MIN(c_notaticket.IdNotaTicket) 
                            FROM c_notaticket 
                            LEFT JOIN k_nota_refaccion ON c_notaticket.IdNotaTicket = k_nota_refaccion.IdNotaTicket
                            WHERE c_notaticket.IdTicket = nt.IdTicket AND c_notaticket.IdEstatusAtencion = 67 
                            AND k_nota_refaccion.NoParteComponente = nr.NoParteComponente
                            ) AS IdNotaSolicitud
                            FROM k_nota_refaccion nr,c_notaticket nt,c_ticket t,c_componente c,c_cliente cl,c_estado e, c_estado e2
                            LEFT JOIN c_area AS ar2 ON ar2.IdArea = e2.IdArea  
                            LEFT JOIN c_notaticket AS lastnt ON lastnt.IdNotaTicket = (SELECT MAX(IdNotaTicket) FROM c_notaticket WHERE IdTicket = t.IdTicket)
                            LEFT JOIN c_estado AS e3 ON lastnt.IdEstatusAtencion = e3.IdEstado 
                            LEFT JOIN c_area AS ar ON ar.IdArea = e3.IdArea
                            WHERE t.IdTicket=nt.IdTicket AND nt.IdNotaTicket=nr.IdNotaTicket AND e2.IdEstado = t.AreaAtencion AND nr.NoParteComponente=c.NoParte AND t.ClaveCliente=cl.ClaveCliente
                            AND nt.IdEstatusAtencion=e.IdEstado
                            AND t.IdTicket=" . $idTicket . "
                            AND (nt.IdEstatusAtencion=65 OR nt.IdEstatusAtencion=20)
                            AND nr.Cantidad>0 AND c.IdTipoComponente=2  
                            HAVING !ISNULL(IdNotaSolicitud)
                            ORDER BY nt.IdNotaTicket,c.Modelo ASC";
                    }
                    $contadorFila = 1;
                    //echo $consulta;
                    $query = $catalogo->obtenerLista($consulta);
                    $array_contadores_por_ticket = array();
                    while ($rs = mysql_fetch_array($query)) {/* Recorremos todos los tickets resultantes del query */
                        $booleanFecha = FALSE;
                        $fecha_limite = strtotime("2014-03-31");
                        $fecha_ticket = strtotime($rs['FechaHora']);
                        $diasCreacion = $rs['diasCreacionNota'];
                        $diasUltimaModificacion = $rs['diasUltimaNota'];
                        $actual = $rs['ahora'];
                        $horasCreacion = $rs['horaTicket'];
                        if($actual >= $horasCreacion){
                            $diffT = $actual - $horasCreacion;
                        }else{
                            $diasCreacion--;
                            $diffT = 24 - ($horasCreacion - $actual);
                        }
                        if($actual >= $horasModificacion){
                            $diffUT = $actual - $horasModificacion;
                        }else{
                            $diasUltimaModificacion--;
                            $diffUT = 24 - ($horasModificacion - $actual);
                        }
                        if ($fecha_ticket >= $fecha_limite) {
                            $booleanFecha = TRUE;
                        } else {
                            $booleanFecha = FALSE;
                        }
                        if ($checked == "" && $rs['IdEstatusAtencion'] == "16") {/* Si ya esta cerrado por nota, saltamos */
                            continue;
                        }

                        /*                             * *********************    Obtenemos el color de la fila   ******************************** */
                        $color = "#F7F7DE";

                        if (isset($rs['IdEstatusAtencion'])) {/* Si hay estado de la ultima nota */
                            if ($rs['IdEstatusAtencion'] != "16" && (isset($rs['EstadoDeTicket']) && $rs['EstadoDeTicket'] != "2")) {/* Si el ticket no esta cerrado */
                                if (strtoupper($rs['IdTipoCliente']) == "1") {/* Si el cliente es VIP */
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
                                if (strtoupper($rs['IdTipoCliente']) == "1") {/* Si el cliente es VIP */
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

                        if ($rs['EstadoDeTicket'] == "4") {/* Ticket cancelado */
                            $color = "#D1D0CE";
                        }

                        if ($rs['TipoReporte'] == "26") {/* Si es Mtto preventivo */
                            $color = "#00FFFF";
                        }
                        if($ultimo_cambio != ""){//Si se quiere mostrar la columna de ultimo cambio toner
                            if(isset($array_contadores_por_ticket[$rs['IdTicket']])){
                                $textoContadores = $array_contadores_por_ticket[$rs['IdTicket']];
                            }else{                        
                                /*Obtenemos las diferencias de contadores de cada equipo del ticket*/
                                $consulta = "SELECT DISTINCT(p.ClaveEspEquipo) AS serie, lt.id_lecturaticket, lt.ContadorBN, lt.ContadorCL,
                                (SELECT id_lecturaticket FROM c_lecturasticket 
                                WHERE ClvEsp_Equipo = p.ClaveEspEquipo AND !ISNULL(fk_idticket) AND id_lecturaticket < lt.id_lecturaticket
                                ORDER BY fk_idticket DESC LIMIT 0,1) AS id_lecturaticket_anterior,
                                (SELECT DATE(t.FechaHora) FROM c_lecturasticket INNER JOIN c_ticket AS t2 ON t2.IdTicket = fk_idticket 
                                WHERE id_lecturaticket = id_lecturaticket_anterior) AS FechaA,
                                (SELECT (lt.ContadorBN - ContadorBN) FROM c_lecturasticket WHERE id_lecturaticket = id_lecturaticket_anterior) AS DiferenciaBN,
                                (SELECT (lt.ContadorCL - ContadorCL) FROM c_lecturasticket WHERE id_lecturaticket = id_lecturaticket_anterior) AS DiferenciaCL
                                FROM c_ticket AS t
                                LEFT JOIN c_pedido AS p ON p.IdTicket = t.IdTicket
                                LEFT JOIN c_lecturasticket AS lt ON lt.fk_idticket = t.IdTicket AND lt.ClvEsp_Equipo = p.ClaveEspEquipo
                                WHERE t.IdTicket = " . $rs['IdTicket'] . " GROUP BY p.ClaveEspEquipo ORDER BY p.ClaveEspEquipo;";
                                $result = $catalogo->obtenerLista($consulta);
                                $textoContadores = "";
                                while($rs2 = mysql_fetch_array($result)){
                                    $textoContadores.= ("<br/>* ".$rs2['serie']." ".$rs2['FechaA']."<br/>");
                                    if(isset($rs2['DiferenciaBN']) && $rs2['DiferenciaBN']!="" && $rs2['DiferenciaBN']>"0"){
                                        $textoContadores.= ("Impresiones negro: ".$rs2['DiferenciaBN']."<br/>");
                                    }else if(isset ($rs2['ContadorBN']) && $rs2['ContadorBN']!=""){
                                        $textoContadores.= ("Contador BN: ".$rs2['ContadorBN']."<br/>");
                                    }else{
                                        $textoContadores.= "Contador BN: No hay información";
                                    }
                                    if(isset($rs2['DiferenciaCL']) && $rs2['DiferenciaCL']!="" && $rs2['DiferenciaCL']>"0"){
                                        $textoContadores.= ("Impresiones color: ".$rs2['DiferenciaCL']."<br/>");
                                    }else if(isset ($rs2['ContadorCL']) && $rs2['ContadorCL']!=""){
                                        $textoContadores.= ("Contador CL: ".$rs2['ContadorCL']."<br/>");
                                    }else{
                                        $textoContadores.= "Contador CL: No hay información";
                                    }
                                }
                                $array_contadores_por_ticket[$rs['IdTicket']] = $textoContadores;
                            }
                        }

                        echo "<tr style='background-color: $color; color:black;'>";
                        echo "<td align='center' scope='row'><a href='#' title='Filtar ticket ".$rs['IdTicket']."'
                            onclick='BuscarTicketById(\"".$rs['IdTicket']."\",\"".$paginaActual."\"); return false;'>" . $rs['IdTicket'] . "</a></td>";
                        echo "<td align='center' scope='row'>" . $rs['FechaHora'] . " (". $diasCreacion. " días ".$diffT ." horas)". "</td>";                            
                        echo "<td align='center' scope='row'>" . $rs['NombreRazonSocial'] . " - " . $rs['NombreCentroCosto'] . "</td>";
                        if($ultimo_cambio != ""){
                            echo "<td align='center' scope='row'>$textoContadores</td>";
                        }
                        ?>
                    <td align='center' scope='row'>
                        <?php if ($booleanFecha) { ?>
                            <a href='#' onclick='detalleTicketAlmacen("mesa/alta_ticketphp.php", "<?php echo $rs['IdTicket']; ?>", "<?php echo "15"; ?>", "1");
                                    return false;' title='Detalle' ><img src="resources/images/Textpreview.png"/></a> 
                               <?php
                           } else {
                               if ($rs['idArea'] == "2") {
                                   $src = $_SESSION['liga'] . "/Operacion/MesaServicio/ConsultaDetalleTicketToner.aspx?Operacion=&IdTicket=" . $rs['IdTicket'] . "&Vista=Detalle&uguid=" . $_SESSION['user'];
                               } else {
                                   $src = $_SESSION['liga'] . "/Operacion/MesaServicio/ConsultaDetalleTicketFalla.aspx?Operacion=&IdTicket=" . $rs['IdTicket'] . "&Vista=Detalle&uguid=" . $_SESSION['user'];
                               }
                               ?>
                            <a href='#' onclick='lanzarPopUp("Detalle", "<?php echo $src; ?>");
                                    return false;' title='Detalle' ><img src="resources/images/Textpreview.png"/></a>
                               <?php
                           }
                           ?>
                    </td>
                    <?php
                    echo "<td align='center' scope='row'>"; 
                        echo "Solicitado: <br/>";
                        echo $rs['refaccion']; 
                        echo "<input type='hidden' id='serie_toner_$contadorFila' name='serie_toner_$contadorFila' value='".$rs['NoSerieEquipo']."'/>";
                        /*Obtenemos los toner compatibles por equipo*/
                        echo "<br/>Pieza a surtir: <br/>";
                        /*if($rs['Resurtido'] == "1"){//Si el ticket es de resurtido
                            $consultaCompatibles = "SELECT cinv.NoSerie, kecc.NoParteComponente AS ComponenteOriginal, kecc.NoParteEquipo, c.NoParte, c.Modelo, c.Descripcion 
                                FROM `c_inventarioequipo` AS cinv
                                LEFT JOIN c_bitacora AS b ON b.NoSerie = cinv.NoSerie
                                LEFT JOIN k_serviciogimgfa AS ks ON ks.IdKserviciogimgfa = cinv.IdKserviciogimgfa
                                RIGHT JOIN k_anexoclientecc AS kacc ON kacc.IdAnexoClienteCC = cinv.IdAnexoClienteCC
                                INNER JOIN c_ticket AS t ON t.IdTicket = ".$rs['IdTicket']." AND (ks.ClaveCentroCosto = t.ClaveCentroCosto OR kacc.CveEspClienteCC = t.ClaveCentroCosto)
                                INNER JOIN k_equipocomponentecompatible AS kecc ON kecc.NoParteComponente = '".$rs['NoParte']."' AND kecc.NoParteEquipo = cinv.NoParteEquipo 
                                LEFT JOIN k_equipocomponentecompatible AS kecc2 ON kecc2.NoParteEquipo = cinv.NoParteEquipo
                                INNER JOIN c_componente AS c ON c.NoParte = kecc2.NoParteComponente AND c.IdTipoComponente = 2 AND c.IdColor = ".$rs['IdColor']."
                                WHERE !ISNULL(cinv.NoSerie)
                                GROUP BY c.NoParte;";                                                        
                        }else{
                            $consultaCompatibles = "SELECT c.NoParte, c.Modelo, c.Descripcion FROM c_notaticket AS nt
                                LEFT JOIN k_nota_refaccion AS nr ON nr.IdNotaTicket = nt.IdNotaTicket
                                LEFT JOIN c_bitacora AS b ON b.NoSerie = nr.NoSerieEquipo
                                LEFT JOIN k_equipocomponentecompatible AS kecc ON kecc.NoParteEquipo = b.NoParte
                                LEFT JOIN c_componente AS c ON c.NoParte = kecc.NoParteComponente
                                WHERE nt.IdTicket = ".$rs['IdTicket']." AND nt.IdEstatusAtencion = 67 AND nr.NoParteComponente = '".$rs['NoParte']."' 
                                   AND c.IdTipoComponente = 2 AND c.IdColor = ".$rs['IdColor']." AND c.Activo = 1 
                                GROUP BY c.NoParte;";                            
                        }*/
                        
                        $consultaCompatibles = "SELECT kecc.NoParteComponente AS ComponenteOriginal, kecc.NoParteEquipo, c.NoParte, c.Modelo, c.Descripcion 
                            FROM k_equipocomponentecompatible AS kecc 
                            LEFT JOIN k_equipocomponentecompatible AS kecc2 ON kecc2.NoParteEquipo = kecc.NoParteEquipo
                            INNER JOIN c_componente AS c ON c.NoParte = kecc2.NoParteComponente AND c.IdTipoComponente = 2 
                            AND c.IdColor = ".$rs['IdColor']." AND c.Activo = 1
                            WHERE kecc.NoParteComponente = '".$rs['NoParte']."'
                            GROUP BY c.NoParte;";
                        
                        $resultCompatible = $catalogo->obtenerLista($consultaCompatibles);
                            
                        if(mysql_num_rows($resultCompatible) > 1){
                            echo "<select id='cambiarComponente_$contadorFila' name='cambiarComponente_$contadorFila' onchange='mostrarExistencias(\"$contadorFila\",\"cambiarComponente_$contadorFila\");'>"; 
                            while($rsCompatible = mysql_fetch_array($resultCompatible)){
                                $s = "";
                                if($rsCompatible['NoParte'] == $rs['NoParte']){//Se selcciona el no de parte que se habia pedido originalmente
                                    $s = "selected = 'selected'";
                                }
                                echo "<option value='".$rsCompatible['NoParte']."' $s>".$rsCompatible['Modelo']." / ".$rsCompatible['NoParte']." / ".$rsCompatible['Descripcion']."</option>";
                            }
                            echo "</select>";
                        }else{
                            echo "<input type='text' value='".$rs['NoParte']."' readonly='readonly' id='cambiarComponente_$contadorFila' name='cambiarComponente_$contadorFila'/>";
                        }
                        
                        
                    echo "</td>";
                    echo "<td>".$rs['areaAtencion2']."(".$rs['areaAtencion'].")</td>";
                    echo "<td align='center' scope='row'>" . $rs['ultimoEstatus'] . " (". $diasUltimaModificacion ." días ".$diffUT ." horas)</td>";    ?>
                    <td align='center' scope='row'>
                        <?php if ($permisos_grid->getModificar()) { ?>
                            <a href='#' onclick='                                
                                AgregarNotaTicketLista("nota","AgregarNota",<?php echo $rs['IdTicket']; ?>,"<?php echo $paginaActual; ?>");
                        return false;' title='Agregar nota' >
                                <img src="resources/images/notes.ico" style="width:24px; height: 24px; "/>
                            </a>
                        <?php } ?>
                    </td>
                    <?php
                    $cantidadRestante = (int) $rs['Cantidad']; // - (int) $rs['CantidadSurtida'];
                    echo "<td align='center' scope='row'><input type='text' name='cantidadRestante$contadorFila' id='cantidadRestante$contadorFila' size='2' value='$cantidadRestante'/><br/><div id='errorCantidad" . $contadorFila . "'></div></td>";
                    echo "<td align='center' scope='row'>";
                    //$contadorFila++;
                    ?>
                    <select style='width: 140px' id="almacen<?php echo $contadorFila ?>" onchange="mostrarExistencias('<?php echo $contadorFila ?>', 'cambiarComponente_<?php echo $contadorFila; ?>');" name="almacen<?php echo $contadorFila ?>">
                        <option value="0">Seleccione un almacén</option>
                        <?php
                        $mystring = $rs['DiagnosticoSol'];
                        $findme = 'Solicitud';
                        $findme2 = 'resurtido';
                        $pos0 = strpos($mystring, $findme);
                        $pos1 = strpos($mystring, $findme2);
                        if ($idUsuario == '24') {
                            $consulta1 = "SELECT * FROM k_responsablealmacen ra,c_almacen a ,c_usuario us WHERE ra.IdUsuario='" . $userAlmacen . "' AND a.Activo=1 AND ra.IdAlmacen=a.id_almacen AND ra.IdUsuario=us.IdUsuario ORDER BY a.nombre_almacen ASC";
                        } else {
                            $consulta1 = "SELECT * FROM c_almacen a WHERE a.Activo=1 AND (a.TipoAlmacen = 1 OR a.Surtir = 1) AND a.id_almacen<>9 ORDER BY a.nombre_almacen ASC";
                        }

                        $query1 = $catalogo->obtenerLista($consulta1);
                        while ($rs1 = mysql_fetch_array($query1)) {
                            $s = "";
                            if ($almacenPredeterminado != "" && $almacenPredeterminado == $rs1['id_almacen']){
                                $s = "selected";
                            }
                            echo "<option value=" . $rs1['id_almacen'] . " " . $s . ">" . $rs1['nombre_almacen'] . "</option>";
                        }
                        ?>
                    </select>
                    <?php
                    $cantidadALamcenExistente = "";
                    if ($rs['cantidadExistente'] == "" || $rs['cantidadExistente'] == "0")
                        $cantidadALamcenExistente = 0;
                    else
                        $cantidadALamcenExistente = $rs['cantidadExistente'];
                    echo "<br/><div id='errorAlmacen$contadorFila'></div></td>";
                    echo "<td align='center' scope='row'><div id='cantidadExistente$contadorFila'>$cantidadALamcenExistente</div><input type='hidden' id='cantidadExix$contadorFila' name=id='cantidadExix$contadorFila' value='$cantidadALamcenExistente' /></td>";
                    echo "<td>
                       <select id='estatus_" . $contadorFila . "' name='estatus_" . $contadorFila . "' style='width: 140px'>
                       <option value='0'>Seleccione un estado</option>";
                    $c1 = 0;
                    while ($c1 < count($idestatus)) {
                        $s = "";
                        $selectEstatusToner = "";
                        if ($cantidadALamcenExistente != "0")
                            $selectEstatusToner = "21";
                        else
                            $selectEstatusToner = "20";
                        if ($selectEstatusToner == $idestatus[$c1])
                            $s = "selected";
                        echo"<option value='" . $idestatus[$c1] . "' " . $s . ">" . $nombreEstatus[$c1] . "</option>    ";
                        $c1++;
                    }
                    echo" </select>
                        <br/><div id='errorEstado" . $contadorFila . "'></div>
                       </td>";
                    echo "<td align='center' scope='row'>";
                    ?>
                    <input type="checkbox" name="ckTonerSeleccionado<?php echo $contadorFila; ?>" id="ckTonerSeleccionado<?php echo $contadorFila; ?>" value="<?php echo $contadorFila . " /** " . $rs['IdNotaTicket'] . " /** " . $rs['NoParte'] . " /** " . $rs['Cantidad'] ?>" />
                    <!--<input type="button" class="boton" value="Guardar" onclick="CambiarEstatusRefaccionToner('<?php echo $contadorFila; ?>', '<?php echo $rs['IdNotaTicket'] ?>', '<?php echo $rs['NoParte'] ?>', '<?php echo $rs['Cantidad'] ?>');"/>--> 
                    <?php echo "</td>"; ?>
                    <td align='center' scope='row'> 
                        <?php
                        if ($booleanFecha) {
                            if(isset($rs['Resurtido']) && $rs['Resurtido'] == "1")
                            {
                                ?>
                                <a href='#' onclick='detalleReporte("reportes/reporte_ticket_resurtido.php", "<?php echo $rs['IdTicket']; ?>", "<?php echo $rs['idArea']; ?>", "0");
                                        return false;' title='Reporte' ><img src="resources/images/icono_impresora.png" width="35" height="35"/></a>
                            <?php }else{ ?>
                                <a href='#' onclick='detalleReporte("reportes/reporte_ticket.php", "<?php echo $rs['IdTicket']; ?>", "<?php echo $rs['idArea']; ?>", "0");
                                        return false;' title='Reporte' ><img src="resources/images/icono_impresora.png" width="35" height="35"/></a>
                            <?php }    
                            } else { ?>
                            <a href='<?php echo $_SESSION['liga']; ?>/operacion/MesaServicio/ReporteTicket.aspx?IdTicket=<?php echo $rs['IdTicket']; ?>&uguid=<?php echo $_SESSION['user']; ?>' 
                               target="_blank" title='Reporte' ><img src="resources/images/icono_impresora.png" width="35" height="35"/></a>
                            <?php
                        }
                        ?>                                                                      
                    </td>
                    <?php
                    echo "</tr>";
                    $contadorFila++;
                }
                ?>
                </tbody>
                <input type="hidden" id="contador_solicitados" name="contador_solicitados" value="<?php echo $contadorFila; ?>"/>
                <input type="hidden" id="color_hidden" name="color_hidden" value="<?php echo $colorPOST; ?>"/>
                <input type="hidden" id="mostrar_grid" name="mostrar_grid" value="<?php echo $mostrarGrid; ?>"/>
                </table>
            </div>
            <?php if($permisos_grid->getModificar()){ ?>
                <div class="form-row">
                <?php if(!isset($_GET['stoner'])){ ?>
                <div class="col-md-3"><br>
                <input type="button" class="btn btn-outline-success btn-block" value="Antender y Enviar"  onclick="atenderNotaTonerSolicitado('almacen/toner_solicitado.php', '1');" />
                </div>
                <?php  } ?>
                <div class="col-md-3"><br>
                <input type="Button" id="boton_atender" name="boton_atender" value="Atender" class="btn btn-outline-info btn-block"             
                    onclick="atenderNotaTonerSolicitado('<?php echo $paginaActual; ?>','0');"/>
                </div>
                </div>
            <?php } ?>
            <?php   }
                    if(!isset($_GET['stoner'])){   
                        echo "<br/><br/>";
                        if(!isset($_GET['etoner'])){
                            echo "<div class='form-row'><div class='col-md-12'><h1>Env&iacute;o de t&oacute;ner</h1></div></div>";
                        }
                    if($permisos_grid->getModificar()){ 
                        $varAux = "null";
                        if(isset($_GET['etoner'])){
                            $varAux = "etoner"; 
                        }?>
            <div class="form-row">
                <div class="col-md-3"><input type="button" id="enviar" name="enviar" onclick="envioMultiple('<?php echo $varAux; ?>'); return false;" class="btn btn-outline-primary btn-block" value="Enviar"/></div>
            </div>
            
            <?php } ?>
            <br/>
            <?php if(isset($_GET['etoner'])){ 
                echo "<input type='hidden' id='etoner' name='etoner' value='1'>";
            }else{
                echo "<a href='#' id='liga_envio' onclick='mostrarEnviados(); return false;'>Mostrar env&iacute;os</a>";
            }
            ?>
            <div style="display: inline; float: right;"><input type="checkbox" id="slc_todo_enviados" onclick="seleccionarTodosEnviados();"/>
                <div id="mensaje_sel_env" style="display: inline; ">Seleccionar todo</div></div>
            <div class="table-responsive">
            <table id="tAlmacen2" class="tabla_grid" style="display: none; width: 100%;">
                <thead>
                    <tr>
                        <?php
                        $cabeceras = array("Ticket", "Cliente", "Falla", "Toner", "Ubicacion en almacén" , "Surtidas", "Fecha envío" , "Detalle", "Reporte", "Tipo Envio");
                        for ($i = 0; $i < (count($cabeceras)); $i++) {
                            echo "<th width=\"2%\" align=\"center\" scope=\"col\">" . $cabeceras[$i] . "</th>";
                        }
                        ?>                                                                      
                    </tr>
                </thead>
                <tbody>
                    <?php
                    if ($idTicket == "") {                       
                        $consulta = "SELECT t.IdTicket,t.Resurtido,nt.IdNotaTicket,c.Modelo,cl.NombreRazonSocial,nr.CantidadSurtida,nr.Cantidad,nt1.FechaHora AS FechaEnvio,
                            (SELECT ar.Descripcion FROM c_area ar WHERE ar.IdArea=t.AreaAtencion)AS area,DATE(t.FechaHora) AS FechaHora,
                            CONCAT ('(',nr.Cantidad,') ',c.Modelo) AS refaccion,DATEDIFF(NOW(), t.FechaHora) AS diferencia,nt.IdEstatusAtencion,
                            t.EstadoDeTicket,cl.IdTipoCliente,cl.IdEstatusCobranza,t.TipoReporte,e.Nombre AS ultimoEstatus,nt.DiagnosticoSol, 
                            t.NombreCentroCosto,t.DescripcionReporte,c.NoParte,nt.MostrarCliente,nt.Activo,
                            nt.UsuarioSolicitud,nr.IdAlmacen, cl.ClaveCliente,kac.Ubicacion 
                            FROM k_nota_refaccion AS nr
                            INNER JOIN c_notaticket AS nt ON nt.IdNotaTicket = nr.IdNotaTicket 
                            INNER JOIN c_ticket AS t ON t.IdTicket = nt.IdTicket 
                            INNER JOIN c_componente AS c ON nr.NoParteComponente=c.NoParte
                            INNER JOIN c_cliente AS cl ON t.ClaveCliente=cl.ClaveCliente 
                            INNER JOIN c_estado AS e ON nt.IdEstatusAtencion=e.IdEstado
                            INNER JOIN k_almacencomponente AS kac ON (kac.id_almacen = nr.IdAlmacen AND kac.NoParte = c.NoParte)
                            LEFT JOIN c_notaticket AS nt1 ON nt1.IdNotaTicket = 
                            (
                                SELECT MIN(c_notaticket.IdNotaTicket) FROM c_notaticket LEFT JOIN k_nota_refaccion ON k_nota_refaccion.IdNotaTicket = c_notaticket.IdNotaTicket 
                                WHERE IdTicket = t.IdTicket AND IdEstatusAtencion = 66 AND k_nota_refaccion.NoParteComponente = nr.NoParteComponente
                                AND k_nota_refaccion.FechaCreacion >= nr.FechaCreacion
                            )
                            WHERE nt.IdEstatusAtencion=21 AND nr.Cantidad<>0 AND c.IdTipoComponente=2 
                            $whereCerrado $whereMoroso $whereEnviados 
                            ORDER BY nt.IdNotaTicket,c.Modelo ASC;";
                    } else {                       
                        $consulta = "SELECT t.IdTicket,t.Resurtido,nt.IdNotaTicket,c.Modelo,cl.NombreRazonSocial,nr.CantidadSurtida,nr.Cantidad,nt1.FechaHora AS FechaEnvio,
                            (SELECT ar.Descripcion FROM c_area ar WHERE ar.IdArea=t.AreaAtencion)AS area,DATE(t.FechaHora) AS FechaHora,
                            CONCAT ('(',nr.Cantidad,') ',c.Modelo) AS refaccion,DATEDIFF(NOW(), t.FechaHora) AS diferencia,nt.IdEstatusAtencion,
                            t.EstadoDeTicket,cl.IdTipoCliente,cl.IdEstatusCobranza,t.TipoReporte,e.Nombre AS ultimoEstatus,nt.DiagnosticoSol, 
                            t.NombreCentroCosto,t.DescripcionReporte,c.NoParte,nt.MostrarCliente,nt.Activo,
                            nt.UsuarioSolicitud,nr.IdAlmacen, cl.ClaveCliente,kac.Ubicacion 
                            FROM k_nota_refaccion AS nr
                            INNER JOIN c_notaticket AS nt ON nt.IdNotaTicket = nr.IdNotaTicket 
                            INNER JOIN c_ticket AS t ON t.IdTicket = nt.IdTicket 
                            INNER JOIN c_componente AS c ON nr.NoParteComponente=c.NoParte
                            INNER JOIN c_cliente AS cl ON t.ClaveCliente=cl.ClaveCliente 
                            INNER JOIN c_estado AS e ON nt.IdEstatusAtencion=e.IdEstado
                            INNER JOIN k_almacencomponente AS kac ON (kac.id_almacen = nr.IdAlmacen AND kac.NoParte = c.NoParte)
                            LEFT JOIN c_notaticket AS nt1 ON nt1.IdNotaTicket = 
                            (
                                SELECT MIN(c_notaticket.IdNotaTicket) FROM c_notaticket LEFT JOIN k_nota_refaccion ON k_nota_refaccion.IdNotaTicket = c_notaticket.IdNotaTicket 
                                WHERE IdTicket = t.IdTicket AND IdEstatusAtencion = 66 AND k_nota_refaccion.NoParteComponente = nr.NoParteComponente
                                AND k_nota_refaccion.FechaCreacion >= nr.FechaCreacion
                            )
                            WHERE nt.IdEstatusAtencion=21 AND nr.Cantidad<>0 AND c.IdTipoComponente=2 
                            AND t.IdTicket = $idTicket 
                            ORDER BY nt.IdNotaTicket,c.Modelo ASC;";
                    }
                    
                    $contador = 0;
                    //echo $consulta;
                    $query = $catalogo->obtenerLista($consulta);
                    $contadorFila = 0;
                    while ($rs = mysql_fetch_array($query)) {/* Recorremos todos los tickets resultantes del query */
                        $booleanFecha = FALSE;
                        $fecha_limite = strtotime("2014-03-31");
                        $fecha_ticket = strtotime($rs['FechaHora']);
                        if ($fecha_ticket >= $fecha_limite) {
                            $booleanFecha = TRUE;
                        } else {
                            $booleanFecha = FALSE;
                        }
                        if ($checked == "" && $rs['IdEstatusAtencion'] == "16") {/* Si ya esta cerrado por nota, saltamos */
                            continue;
                        }
                        /*                         * *********************    Obtenemos el color de la fila   ******************************** */
                        $color = "#F7F7DE";
                        $cerrado = false;
                        if (isset($rs['IdEstatusAtencion'])) {/* Si hay estado de la ultima nota */
                            if ($rs['IdEstatusAtencion'] != "16" && (isset($rs['EstadoDeTicket']) && $rs['EstadoDeTicket'] != "2")) {/* Si el ticket no esta cerrado */
                                if (strtoupper($rs['IdTipoCliente']) == "1") {/* Si el cliente es VIP */
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
                            }else{
                                $cerrado = true;
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
                            }else{
                                $cerrado = true;
                            }
                        }

                        /* En dado caso que se un ticekt verde pero en el filtro se selecciono otro color */
                        if ($color == "#F7F7DE" && ($colorPOST != "verde" && $colorPOST != "")) {
                            continue;
                        }

                        if ($rs['IdEstatusCobranza'] == "2") {/* Cliente moroso */
                            $color = "#D462FF";
                        }

                        if ($rs['EstadoDeTicket'] == "4") {/* Ticket cancelado */
                            $color = "#D1D0CE";
                        }

                        if ($rs['TipoReporte'] == "26") {/* Si es Mtto preventivo */
                            $color = "#00FFFF";
                        }
                        $contador++;
                        echo "<tr style='background-color: $color; color:black;'>";
                        echo "<td align='center' scope='row'><a href='#' title='Filtar ticket ".$rs['IdTicket']."'
                            onclick='BuscarTicketById(\"".$rs['IdTicket']."\",\"".$paginaActual."\"); return false;'>" . $rs['IdTicket'] . "</a></td>";
                        echo "<td align='center' scope='row'>" . $rs['NombreRazonSocial'] . " - " . $rs['NombreCentroCosto'] . "</td>";
                        echo "<td align='center' scope='row'>" . $rs['DescripcionReporte'] . "</td>";
                        echo "<td align='center' scope='row'>" . $rs['refaccion'] . "</td>";
                        echo "<td align='center' scope='row'>" . $rs['Ubicacion'] . "</td>";                        
                        echo "<td align='center' scope='row'>" . $rs['CantidadSurtida'] . "</td>";
                        echo "<td align='center' scope='row'>" . $rs['FechaEnvio'] . "</td>";                        
                        ?>
                    <td align='center' scope='row'>
                        <?php if ($booleanFecha) { ?>
                            <a href='#' onclick='detalleTicketAlmacen("mesa/alta_ticketphp.php", "<?php echo $rs['IdTicket']; ?>", "<?php echo "15"; ?>", "1");
                                    return false;' title='Detalle' ><img src="resources/images/Textpreview.png"/></a> 
                           <?php } else {
                               ?>
                            <a href='#' onclick='lanzarPopUp("Detalle", "hardware/alta_ticket_detalle_hw.php?tipo=<?php echo "SUMINISTRO" . "&id=" . $rs['IdTicket']; ?>");
                                    return false;' title='Detalle' ><img src="resources/images/Textpreview.png"/></a>
                           <?php }
                           ?>

                    </td>
                    <td align='center' scope='row'>
                        <?php
                         if(isset($rs['Resurtido']) && $rs['Resurtido'] == "1")
                            {
                                ?>
                                <a href='#' onclick='detalleReporte("reportes/reporte_ticket_resurtido.php", "<?php echo $rs['IdTicket']; ?>", "<?php echo $rs['idArea']; ?>", "0");
                                        return false;' title='Reporte' ><img src="resources/images/icono_impresora.png" width="35" height="35"/></a>
                            <?php }else{ ?>
                                <a href='#' onclick='detalleReporte("reportes/reporte_ticket.php", "<?php echo $rs['IdTicket']; ?>", "<?php echo $rs['idArea']; ?>", "0");
                                        return false;' title='Reporte' ><img src="resources/images/icono_impresora.png" width="35" height="35"/></a>
                        <?php } 
                        /*
                         * <a href='#' onclick='lanzarPopUp("Generar reporte", "almacen/lista_crearReporteNotaToner.php?ticket=<?php echo$rs['IdTicket']; ?>");
                                return false;' title='Generar reporte' ><img src="resources/images/icono_impresora.png" width="24" height="24"/></a>
                         */
                        ?>
                    </td>  
                    <?php
                        $totalRestantes = (int) $rs['Cantidad'] - (int) $rs['CantidadSurtida'];
                        if ($totalRestantes > 0 && !$cerrado) {
                        ?>
                        <!--<td align='center' scope='row'> <a href='#' onclick='VerEnvioToner("almacen/altaEnvioToner.php", "<?php //echo $rs['IdNotaTicket'] ?>");
                                return false;' title='Envío de toner' ><img src="resources/images/Apply.png" width="24" height="24"/></a></td>-->
                            <td align='center' scope='row'>
                                <input type="checkbox" id="check_<?php echo $contadorFila; ?>" name="check_<?php echo $contadorFila; ?>" 
                                       value="<?php echo $contadorFila; ?>"/>
                                <input type="hidden" id="valor_<?php echo $contadorFila; ?>" name="valor_<?php echo $contadorFila; ?>" 
                                       value="<?php echo $rs['IdNotaTicket']; ?>"/>
                            </td>
                            <?php
                            $contadorFila++;
                        } else {
                            echo "<td align='center' scope='row'></td>";
                        }
                        echo "<input type='hidden' id='ticket$contador' id='ticket$contador' value='" . $rs['IdTicket'] . "'/>";
                        echo "<input type='hidden' id='nota$contador' id='nota$contador' value='" . $rs['IdNotaTicket'] . "'/>";
                        echo "<input type='hidden' id='descripcion$contador' id='descripcion$contador' value='" . $rs['DiagnosticoSol'] . "'/>";
                        echo "<input type='hidden' id='refaccion$contador' id='refaccion$contador' value='" . $rs['NoParte'] . "'/>";
                        echo "<input type='hidden' id='cantidad$contador' id='cantidad$contador' value='" . $rs['Cantidad'] . "'>";
                        echo "<input type='hidden' id='mostrar$contador' id='mostrar$contador' value='" . $rs['MostrarCliente'] . "'/>";
                        echo "<input type='hidden' id='usuarioSolicitud$contador' id='usuarioSolicitud$contador' value='" . $rs['UsuarioSolicitud'] . "'/>";
                        echo "<input type='hidden' id='almacen$contador' id='almacen$contador' value='" . $rs['IdAlmacen'] . "'/>";
                        echo "<input type='hidden' id='cliente$contador' id='cliente$contador' value='" . $rs['ClaveCliente'] . "'/>";
                        echo "<input type='hidden' id='modelo$contador' id='modelo$contador' value='" . $rs['Modelo'] . "'/>";                        
                    }
                    ?>
                </td>
                </tbody>
                <input type="hidden" id="color_hidden" name="color_hidden" value="<?php echo $colorPOST; ?>"/>
                <input type="hidden" id="numeroCheck" name="numeroCheck" value="<?php echo $contador; ?>"/>
                <input type="hidden" id="numeroCheckEnvios" name="numeroCheckEnvios" value="<?php echo $contadorFila; ?>"/>
            </table>
            </div>
            <?php   
                    if($permisos_grid->getModificar()){ 
                        $varAux = "null";
                        if(isset($_GET['etoner'])){
                            $varAux = "etoner"; 
                        }?>
            <div class="form-row p-2">
                <div class="col-md-3">
                    <input type="button" id="enviar" name="enviar" onclick="envioMultiple('<?php echo $varAux; ?>'); return false;" class="btn btn-outline-primary btn-block" value="Enviar"/>
                </div>
            </div>
            
            <?php   } 
                    }
                } 
            ?>         
        </div>
        <?php if(isset($_GET['cliente']) && $_GET['cliente']){?>
        <script>
            $(document).ready(function(){
                BuscarTicketSolicitud('<?php echo $paginaActual;if(isset($_GET['regresar']) && $_GET['regresar'] != ""){ echo "?regresar=".$_GET['regresar'];}?>');
            });
        </script>
        <?php }?>
    </body>
</html>
