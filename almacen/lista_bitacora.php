<?php

session_start();

if (!isset($_SESSION['user']) || $_SESSION['user'] == "") {
    header("Location: index.php");
}

include_once("../WEB-INF/Classes/Catalogo.class.php");
include_once("../WEB-INF/Classes/Usuario.class.php");
include_once("../WEB-INF/Classes/PermisosSubMenu.class.php");
include_once("../WEB-INF/Classes/EquipoCaracteristicasFormatoServicio.class.php");
$permisos_grid = new PermisosSubMenu();
$same_page = "almacen/lista_bitacora.php";
$permisos_grid->getPermisosSubmenu($_SESSION['idUsuario'], $same_page);

$catalogo = new Catalogo();
$usuario = new Usuario();

$alta = "almacen/alta_bitacora.php";
$reporte = "almacen/reporte_bitacora.php";

$where = "";
$serie = "";
$modelo = "";
$solicitud = "";
$bitacora = "";
$mostrarContador = "";

if(isset($_POST['serie']) && $_POST['serie']!=""){
    $where = "WHERE b.NoSerie LIKE '%".$_POST['serie']."%'";
    $serie = $_POST['serie'];
}

if(isset($_POST['modelo']) && $_POST['modelo']!=""){
    if($where!=""){
        $where.=" AND e.Modelo LIKE '%".$_POST['modelo']."%'";
    }else{
        $where = "WHERE e.Modelo LIKE '%".$_POST['modelo']."%'";
    }
    $modelo = $_POST['modelo'];
}

if(isset($_POST['solicitud']) && $_POST['solicitud']!=""){
    if($where!=""){
        $where.=" AND id_solicitud = ".$_POST['solicitud'];
    }else{
        $where = "WHERE id_solicitud = ".$_POST['solicitud'];
    }
    $solicitud = $_POST['solicitud'];
}

if(isset($_POST['bitacora']) && $_POST['bitacora']!=""){    
    if($where!=""){
        $where.=" AND id_bitacora = ".$_POST['bitacora'];
    }else{
        $where = "WHERE id_bitacora = ".$_POST['bitacora'];
    }
    $bitacora = $_POST['bitacora'];
}

if($where!=""){
    $where.=" AND b.Activo = 1";
}else{
    $where = "WHERE b.Activo = 1";
}

$claveCliente = "";
$having = "";
if(isset($_POST['ClaveCliente']) && $_POST['ClaveCliente']!=""){    
    if($having!=""){
        $having.=" AND ClaveCliente = '".$_POST['ClaveCliente']."'";
    }else{
        $having = "HAVING ClaveCliente = '".$_POST['ClaveCliente']."'";
    }    
    $claveCliente = $_POST['ClaveCliente'];
}

if(isset($_POST['contador']) && $_POST['contador']=="true"){
    $mostrarContador = "checked='checked'";
}
?>
<!DOCTYPE html>
<html lang="es">
    <head>
        <script type="text/javascript" language="javascript" src="resources/js/paginas/lista.js"></script>
        <script type="text/javascript" language="javascript" src="resources/js/paginas/exportar_excel.js"></script>
        <script type="text/javascript" language="javascript" src="resources/js/paginas/lista_bitacora.js"></script>
    </head>
    <body>
        <div class="principal">
            <form class="p-3" action="reportes/ReporteBitacora.php" method="post" target="_blank" id="FormularioExportacion">
                <div class="form-row">
                    <div class="form-group col-md-4">
                        <label for="no_serie">No. de serie</label>
                        <input class="form-control" type="text" id="no_serie" name="no_serie" value="<?php echo $serie; ?>"/>
                    </div>
                    <div class="form-group col-md-4">
                        <label for="modelo">Modelo</label>
                        <input class="form-control" type="text" id="modelo" name="modelo" value="<?php echo $modelo; ?>"/>
                    </div>
                    <div class="form-group col-md-4">
                        <label for="cliente">Cliente</label>
                         <?php
                            echo "<select name='cliente'  class='custom-select' >";//id='cliente'
                                echo '<option value="">Selecciona el cliente</option>';                                                    
                                if($usuario->isUsuarioPuesto($_SESSION['idUsuario'], 21)){
                                    $query = $catalogo->obtenerLista("SELECT
                                        c_cliente.NombreRazonSocial AS NombreRazonSocial,
                                        c_cliente.ClaveCliente AS ClaveCliente
                                        FROM c_usuario
                                        INNER JOIN k_tfscliente ON k_tfscliente.IdUsuario=c_usuario.IdUsuario
                                        INNER JOIN c_cliente ON c_cliente.ClaveCliente = k_tfscliente.ClaveCliente
                                        WHERE c_usuario.IdUsuario=" . $_SESSION['idUsuario'] . " AND c_cliente.Activo=1
                                        ORDER BY NombreRazonSocial ASC");
                                }else if($usuario->isUsuarioPuesto($_SESSION['idUsuario'], 11)){
                                    $query = $catalogo->obtenerLista("SELECT
                                        c_cliente.NombreRazonSocial AS NombreRazonSocial,
                                        c_cliente.ClaveCliente AS ClaveCliente
                                        FROM c_usuario
                                        INNER JOIN c_cliente ON c_cliente.EjecutivoCuenta = c_usuario.IdUsuario
                                        WHERE c_usuario.IdUsuario=" . $_SESSION['idUsuario'] . " AND c_cliente.Activo=1
                                        ORDER BY NombreRazonSocial ASC;");
                                }else{
                                    $query = $catalogo->obtenerLista("SELECT * FROM c_cliente WHERE Activo=1 ORDER BY NombreRazonSocial");
                                }
                                while ($rs = mysql_fetch_array($query)) {
                                    $s = "";
                                    if($claveCliente!="" && $claveCliente == $rs['ClaveCliente']){
                                        $s = "selected = 'selected'";
                                    }
                                    echo "<option value=\"" . $rs['ClaveCliente'] . "\" $s>" . $rs['NombreRazonSocial'] . "</option>";
                                }
                            echo '</select>';                        
                            ?>
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group col-md-4">
                        <label for="id_solicitud">No. de solicitud</label>
                        <input class="form-control" type="text" id="id_solicitud" name="id_solicitud" value="<?php echo $solicitud; ?>"/>
                        <p id="error_solicitud"  style="display: none;">Ingresa un n&uacute;mero por favor</p>
                    </div>
                    <div class="form-group col-md-4">
                        <label for="id_bitacora">Folio de bit&aacute;cora</label>
                        <input class="form-control" type="text" id="id_bitacora" name="id_bitacora" value="<?php echo $bitacora; ?>"/>
                        <p id="error_bitacora" class="text-danger" style="display: none;">Ingresa un n&uacute;mero por favor</p>
                    </div>
                    <div class="form-group col-md-4 p-4">
                        <input type="checkbox" id="mostrar_contadores" name="mostrar_contadores" <?php echo $mostrarContador; ?>/>
                        <label for="mostrar_contadores">Mostrar contadores</label>
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group col-md-3">
                        <input type="button" class="btn btn-primary btn-block" value="Mostrar bitácoras" id="aceptar_bitacora" name="aceptar_bitacora" onclick="cargarBitacora('almacen/lista_bitacora.php','no_serie','modelo','id_solicitud','id_bitacora','cliente','mostrar_contadores'); return false;"/>
                    </div>
                    <?php if(isset($_POST['serie'])){/*Cuando ya se hizo un post, es decir, una busqueda*/?>
                        <div class="form-group col-md-3">
                            <input type="button" class="botonExcel btn btn-success btn-block" title="Exportar a excel" id="excelSubmit" name="excelSubmit" value="Exportar a excel" onclick="submitform()"/>
                                <input type="hidden" id="datos_a_enviar" name="datos_a_enviar" />
                        </div>
                    <?php } ?>
                </div>
            </form>
            <?php
                if(isset($_POST['serie'])){/*Cuando ya se hizo un post, es decir, una busqueda*/
            ?>
            <table id="tAlmacen" class="tabla_datos p-3">
                <thead>
                    <tr>
                        <?php
                        if($mostrarContador != ""){ //Si se va a mostrar los contadores
                            $cabeceras = array("Folio", "Folio solicitud", "Modelo", "No. Serie", "Ubicación", "Contador BN", "Contador color" ,"","","");
                            $columnas = array("id_bitacora", "id_solicitud", "NoParteCompuesta","NoSerie","id_bitacora");
                        }else{
                            $cabeceras = array("Folio", "Folio solicitud", "Modelo", "No. Serie", "Ubicación","","","");
                            $columnas = array("id_bitacora", "id_solicitud", "NoParteCompuesta","NoSerie","id_bitacora");
                        }
                        for ($i = 0; $i < (count($cabeceras) - 2); $i++) {
                            echo "<th width=\"2%\" align=\"center\" scope=\"col\">" . $cabeceras[$i] . "</th>";
                        }
                        echo "<th width=\"2%\" align=\"center\" scope=\"col\"></th>";                        
                        ?>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    /* Inicializamos la clase */
                    $catalogo = new Catalogo();
                    $consulta = "SELECT b.id_bitacora, b.id_solicitud, 
                        (CASE WHEN !ISNULL(c2.ClaveCliente) THEN c2.NombreRazonSocial ELSE c.NombreRazonSocial END) AS NombreRazonSocial, 
                        (CASE WHEN !ISNULL(c2.ClaveCliente) THEN c2.ClaveCliente ELSE c.ClaveCliente END) AS ClaveCliente, 
                        (CASE WHEN !ISNULL(cc2.ClaveCentroCosto) THEN cc2.Nombre ELSE cc.Nombre END) AS localidad,
                        a.nombre_almacen,
                        IF(ISNULL(csrg.IdSolicitudRetiroGeneral),'0',
                        IF(csr.PendienteRetiro=0 AND csrg.Contestado=0,'1',/*Solicitud de retiro*/
                        IF(csr.PendienteRetiro=1 AND csrg.Contestado=1 AND csrg.Aceptada=1,'2',
                        IF(csrg.Aceptada=1 AND csrg.Contestado=1 AND csr.IdAlmacen!=9 AND cal.id_almacen=9 AND (ISNULL(rh.NumReporte) OR rh.Retirado = 0),'3',/*Solicitud retiro aceptada*/
                        IF(rh.Retirado = 1 AND meq.pendiente = 1,'4',/*Retirado*/
                        IF(meq.pendiente = 0,'0',/*Aceptado en almacen*/
                        '0')))))) AS MoverRojo,
                        b.NoSerie, CONCAT(e.Modelo,' / ',b.NoParte) AS NoParteCompuesta "; 
                    if($mostrarContador != ""){ //Si se va a mostrar los contadores
                        $consulta .= ",b.NoParte,(SELECT CASE WHEN l.Fecha > lt.Fecha THEN l.Fecha ELSE lt.Fecha END) AS Fecha,
                            (SELECT CASE WHEN l.Fecha > lt.Fecha THEN l.ContadorBNPaginas ELSE lt.ContadorBN END) AS ContadorBN,
                            (SELECT CASE WHEN l.Fecha > lt.Fecha THEN l.ContadorColorPaginas ELSE lt.ContadorCL END)AS ContadorCL,
                            (SELECT CASE WHEN l.Fecha > lt.Fecha THEN l.ContadorBNML ELSE lt.ContadorBNA END) AS ContadorBNML,
                            (SELECT CASE WHEN l.Fecha > lt.Fecha THEN l.ContadorColorML ELSE lt.ContadorCLA END) AS ContadorCLML,
                            (SELECT CASE WHEN l.Fecha > lt.Fecha THEN l.NivelTonNegro ELSE lt.NivelTonNegro END) AS NivelTonNegro,
                            (SELECT CASE WHEN l.Fecha > lt.Fecha THEN l.NivelTonCian ELSE lt.NivelTonCian END) AS NivelTonCian,
                            (SELECT CASE WHEN l.Fecha > lt.Fecha THEN l.NivelTonMagenta ELSE lt.NivelTonMagenta END) AS NivelTonMagenta,
                            (SELECT CASE WHEN l.Fecha > lt.Fecha THEN l.NivelTonAmarillo ELSE lt.NivelTonAmarillo END) AS NivelTonAmarillo ";
                    }
                    $consulta.= "FROM `c_bitacora` AS b
                        LEFT JOIN c_equipo AS e ON b.NoParte = e.NoParte
                        LEFT JOIN c_inventarioequipo AS cinv ON cinv.NoSerie = b.NoSerie
                        LEFT JOIN c_solicitudretiro AS csr ON csr.IdSolicitudRetiro = (SELECT MAX(IdSolicitudRetiro) FROM c_solicitudretiro WHERE IdBitacora = b.id_bitacora)
                        LEFT JOIN c_solictudretirogeneral AS csrg ON csrg.IdSolicitudRetiroGeneral=csr.IdSolicitudRetiroGeneral
                        LEFT JOIN movimientos_equipo AS meq ON meq.id_movimientos = (SELECT MAX(id_movimientos) FROM movimientos_equipo WHERE NoSerie = b.NoSerie AND DATE(Fecha) = DATE(csrg.FechaReporte) AND clave_centro_costo_anterior = csr.ClaveLocalidad)
                        LEFT JOIN reportes_movimientos AS rm ON rm.id_movimientos = meq.id_movimientos
                        LEFT JOIN reportes_historicos AS rh ON rh.NumReporte = rm.id_reportes
                        LEFT JOIN k_almacenequipo AS ke ON ke.NoSerie=b.NoSerie
                        LEFT JOIN c_almacen AS cal ON cal.id_almacen=ke.id_almacen
                        LEFT JOIN k_anexoclientecc AS ka ON ka.IdAnexoClienteCC = cinv.IdAnexoClienteCC
                        LEFT JOIN c_centrocosto AS cc ON cc.ClaveCentroCosto = ka.CveEspClienteCC
                        LEFT JOIN c_cliente AS c ON c.ClaveCliente = cc.ClaveCliente
                        LEFT JOIN k_serviciogimgfa AS ks ON ks.IdKserviciogimgfa = cinv.IdKserviciogimgfa
                        LEFT JOIN c_centrocosto AS cc2 ON cc2.ClaveCentroCosto = ks.ClaveCentroCosto
                        LEFT JOIN c_cliente AS c2 ON c2.ClaveCliente = cc2.ClaveCliente
                        LEFT JOIN k_almacenequipo AS kae ON kae.NoSerie = b.NoSerie
                        LEFT JOIN c_almacen AS a ON a.id_almacen = kae.id_almacen "; 
                    if($mostrarContador != ""){ //Si se va a mostrar los contadores
                        $consulta .= "LEFT JOIN c_lectura AS l ON l.NoSerie = b.NoSerie AND l.Fecha = (SELECT MAX(Fecha) FROM c_lectura WHERE NoSerie = b.NoSerie)
                            LEFT JOIN c_lecturasticket AS lt ON lt.ClvEsp_Equipo = b.NoSerie AND lt.Fecha = (SELECT MAX(Fecha) FROM c_lecturasticket WHERE ClvEsp_Equipo = b.NoSerie)";
                    }
                    $consulta .= " $where GROUP BY id_bitacora $having;";
                    //echo $consulta;
                    $query = $catalogo->obtenerLista($consulta);                    
                    while ($rs = mysql_fetch_array($query)) {
                        echo "<tr>";
                        for ($i = 0; $i < count($columnas) - 1; $i++) {
                            echo "<td align='center' scope='row'>" . $rs[$columnas[$i]] . "</td>";
                        }
                        echo "<td align='center' scope='row'>";
                        if(isset($rs['localidad']) && $rs['localidad']!=""){
                            echo $rs['NombreRazonSocial'] . " - ".$rs['localidad'];
                        }else if(isset ($rs['nombre_almacen']) && $rs['nombre_almacen']!=""){
                            echo $rs['nombre_almacen'] . " (Almacén)";
                        }
                        
                        if ($rs['MoverRojo'] == "1") {
                            echo "<br/>El equipo tiene una solicitud de retiro";
                        } else if($rs['MoverRojo'] == "3"){
                            echo "<br/>Solicitud de retiro aceptada";
                        } else if($rs['MoverRojo'] == "4"){
                            echo "<br/>Falta entrada a almacén";
                        }
                        
                        echo "</td>";
                        
                        if($mostrarContador != ""){ //Si se va a mostrar los contadores
                             $equipoCaracteristica = new EquipoCaracteristicasFormatoServicio();
                             if($equipoCaracteristica->isFormatoAmplio($rs['NoParte'])){//Si es un equipo FA
                                 echo "<td align='center' scope='row'>".  number_format($rs['ContadorBNML'], 0)."</td>";
                                 if($equipoCaracteristica->isColor($rs['NoParte'])){//Si es un equipo a color
                                     echo "<td align='center' scope='row'>".  number_format($rs['ContadorCLML'], 0)."</td>";
                                 }else{
                                     echo "<td align='center' scope='row'></td>";
                                 }
                             }else{//Si no es FA
                                 echo "<td align='center' scope='row'>".  number_format($rs['ContadorBN'], 0)."</td>";
                                 if($equipoCaracteristica->isColor($rs['NoParte'])){//Si es un equipo a color
                                     echo "<td align='center' scope='row'>".  number_format($rs['ContadorCL'], 0)."</td>";
                                 }else{
                                     echo "<td align='center' scope='row'></td>";
                                 }
                             }
                        }
                        ?>
                        <td align='center' scope='row'>  
                            <?php if($permisos_grid->getConsulta()){ ?>
                            <a class="h5 text-dark" href='#' onclick='editarRegistro("<?php echo $alta; ?>", "<?php echo $rs[$columnas[count($columnas) - 1]]; ?>");return false;' title='Editar Registro' >
                                <i class="fal fa-pencil"></i>
                            </a>
                            <?php } ?>
                        </td>
                        <td align='center' scope='row'>                        
                            <a class="h5 text-dark" href='#' onclick='editarRegistro("<?php echo $reporte; ?>", "<?php echo $rs[$columnas[count($columnas) - 1]]; ?>");return false;' title='Ver Reporte' >
                                <i class="far fa-print"></i>
                            </a>
                        </td>
                    <?php
                        echo "</tr>";
                    }
                    ?>
                </tbody>
            </table>
            <?php
                }
            ?>
        </div>
    </body>
</html>
