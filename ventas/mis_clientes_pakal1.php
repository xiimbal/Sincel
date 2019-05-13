<?php
session_start();

if (!isset($_SESSION['user']) || $_SESSION['user'] == "") {
    header("Location: index.php");
}

/* Para mantener los filtros y paginados de la tabla */
if (isset($_GET['page']) && isset($_GET['filter'])) {
    $filter = str_replace("_XX__XX_", " ", $_GET['filter']);
    $page = $_GET['page'];
} else {
    $page = "0";
    $filter = ""; 
}

include_once("../WEB-INF/Classes/Catalogo.class.php");
include_once("../WEB-INF/Classes/Menu.class.php");
include_once("../WEB-INF/Classes/Usuario.class.php");
include_once("../WEB-INF/Classes/PermisosSubMenu.class.php");
$catalogo = new Catalogo();

$permisos_grid = new PermisosSubMenu();
$same_page = "ventas/mis_clientes_pakal.php";
$permisos_grid->getPermisosSubmenu($_SESSION['idUsuario'], $same_page);
$historico = "ventas/historico_clientes.php";
/* $menu = new Menu();    
  $factura = $menu->tieneSubmenu($_SESSION['idUsuario'], 92);/*Preguntamos si el usuario tiene permiso de facturar */
//echo $factura;
$permiso_facturar = new PermisosSubMenu();
$page_facturar = "facturacion/ReporteFacturacion.php";
$permiso_facturar->getPermisosSubmenu($_SESSION['idUsuario'], $page_facturar);
$factura = $permiso_facturar->getAlta();

$permiso_inctivos = $permiso_facturar->tienePermisoEspecial($_SESSION['idUsuario'], 23);
$abcContactos = $permiso_facturar->tienePermisoEspecial($_SESSION['idUsuario'], 34);
$id_cliente = "";
$id_vendedor = "";
$id_ejecutivo = "";
$rfc = "";
$estatus = "";
$ejecutivo = "";
$rfc = "";
$estatus = "";
$ejecutivo = "";
$rfc = "";
$estatus = "";
$ejecutivo = "";
$rfc = "";
$estatus = "";
$ejecutivo = "";
$rfc = "";
$estatus = "";
$ejecutivo = ""; 
$rfc = "";
$estatus = "";
$where = "";
$tipo = "";
$usuario = new Usuario();
if ($usuario->isUsuarioPuesto($_SESSION['idUsuario'], 11)) {
    $where = " WHERE c.EjecutivoCuenta = '" . $_SESSION['idUsuario'] . "'";
}

if (isset($_POST['id_cliente']) && $_POST['id_cliente'] != "0") {
    $id_cliente = $_POST['id_cliente'];
    $clientes = split(";", $id_cliente);
    
    if(!empty($id_cliente) && !empty($clientes)){

        ($where == "") ? $where = " WHERE (" : $where .= " AND (" ;

        foreach ($clientes as $value) {
            if(empty($value) || (trim($value) == "")) continue;
            $where .= " c.NombreRazonSocial LIKE '%".trim($value)."%' OR ";
        }

        if(!empty($where)){
            $where = substr($where, 0, strlen($where)-3);
        }

        $where .= ")";
    }
}

if (isset($_POST['rfc']) && $_POST['rfc'] != "") {
    $rfc = $_POST['rfc'];
    ($where == "") ? $where = " WHERE c.RFC = '$rfc'" : $where .= " AND c.RFC = '$rfc'";
}
if (isset($_POST['id_vendedor']) && $_POST['id_vendedor'] != "0") {
    $id_vendedor = $_POST['id_vendedor'];
    ($where == "") ? $where = " WHERE u.IdUsuario = '$id_vendedor'" : $where .= " AND u.IdUsuario = '$id_vendedor'";
    
}
if (isset($_POST['id_ejecutivo']) && $_POST['id_ejecutivo'] != "0") {
    $id_ejecutivo = $_POST['id_ejecutivo'];
    ($where == "") ? $where = " WHERE c.EjecutivoAtencionCliente = $id_ejecutivo" : $where .= " AND c.EjecutivoAtencionCliente = $id_ejecutivo";
    
}
if ($permiso_inctivos) {
    if (isset($_POST['estatus']) && $_POST['estatus'] != "") {
        $estatus = $_POST['estatus'];
        ($where == "") ? $where = " WHERE c.Activo = '$estatus'" : $where .= " AND c.Activo = '$estatus'";
    }
} else {
    ($where == "") ? $where = " WHERE c.Activo = '1'" : $where .= " AND c.Activo = '1'";
}
if (isset($_POST['tipo']) && $_POST['tipo'] != "0") {
    $tipo = $_POST['tipo'];
    ($where == "") ? $where = " WHERE c.Modalidad = '$tipo'" : $where .= "  AND c.Modalidad= '$tipo'";
    
}
$array_estatus = Array("1 */* Activo", "0 */* Inactivo");
$consulta = "SELECT ClaveCliente,NombreRazonSocial,c.RFC,tc.Nombre AS tipoCliente,cg.Nombre AS NombreGrupo,CONCAT(u.Nombre,' ',u.ApellidoPaterno,' ',u.ApellidoMaterno) AS vendedor,IF(c.Activo=1,'Activo','Inactivo') AS estatus
        FROM `c_cliente` AS c  LEFT JOIN c_usuario AS u ON c.EjecutivoCuenta = u.IdUsuario LEFT JOIN c_clientegrupo AS cg ON c.ClaveGrupo = cg.ClaveGrupo LEFT JOIN c_clientemodalidad AS tc ON tc.IdTipoCliente = c.Modalidad $where;";

?>
<!DOCTYPE html>
<html lang="es">
    <head>        
        <script type="text/javascript" language="javascript" src="resources/js/paginas/lista_ticket.js"></script>
    </head>
    <body>
        <div class="principal">       
            <form action="reportes/reporte_clientes_light.php" id="form_clientes" method="post" target="_blank">
                <div class="p-4 bg-light rounded">
                    <div class="form-row">
                        <!--Cliente-->
                        <div class="form-group col-12 col-md-4">
                            <label for="sl_cliente" class="m-0">Cliente</label>
                            <input id="sl_cliente" class="auto_complete_text form-control" value="<?php echo $id_cliente; ?>">
                        </div>
                        <!--RFC-->
                        <div class="form-group col-12 col-md-4">
                            <label for="txt_rfc" class="m-0">RFC</label>
                            <input type="text" id="txt_rfc" name="txt_rfc" value="<?php echo $rfc;?>" class="form-control"/>
                        </div>
                        <?php if (!$usuario->isUsuarioPuesto($_SESSION['idUsuario'], 11)): ?>
                            <!--Vendedor-->
                            <div class="form-group col-12 col-md-4">
                                <label for="sl_vendedor" class="m-0">Vendedor</label>
                                <select id="sl_vendedor" name="sl_vendedor" class="custom-select">
                                    <option value="0">Todos los vendedores</option>
                                    <?php
                                    $query_vendedor = $catalogo->obtenerLista("SELECT usu.IdUsuario,CONCAT(usu.Nombre,' ',usu.ApellidoPaterno,' ',usu.ApellidoMaterno) aS usuario FROM c_cliente cl 
                                        INNER JOIN c_usuario usu ON cl.EjecutivoCuenta=usu.IdUsuario GROUP BY usu.IdUsuario");
                                    while ($rs = mysql_fetch_array($query_vendedor)) {
                                        $s = "";
                                        if ($id_vendedor == $rs['IdUsuario']) {
                                            $s = "selected";
                                        }
                                        echo "<option value='" . $rs['IdUsuario'] . "' $s>" . $rs['usuario'] . "</option>";
                                    }
                                    ?>
                                </select>
                            </div>
                        <?php endif; ?>
                    </div>
                    <div class="form-row">
                        <!--Tipo-->
                        <div class="form-group col-12 col-md-4">
                            <label for="sl_tipo" class="m-0">Tipo</label>
                            <select id="sl_tipo" name="sl_tipo" class="custom-select">
                                <option value="0">Todos los tipos</option>
                                <?php
                                $query_tipo = $catalogo->getListaAlta("c_clientemodalidad", "Nombre");
                                while ($rs = mysql_fetch_array($query_tipo)) {
                                    $s = "";
                                    if ($tipo == $rs['IdTipoCliente']) {
                                        $s = "selected";
                                    }
                                    echo "<option value='" . $rs['IdTipoCliente'] . "' $s>" . $rs['Nombre'] . "</option>";
                                }
                                ?>
                            </select>
                        </div>
                        <?php if ($permiso_inctivos): ?>
                            <!--Estatus-->
                            <div class="form-group col-12 col-md-4">
                                <label for="sl_estatus" class="m-0">Estatus</label>
                                <select id="sl_estatus" name="sl_estatus" class="custom-select">
                                    <option value="">Todos los estatus</option>
                                    <?php
                                        foreach ($array_estatus as $value) {
                                            $estatus_aux = explode(" */* ", $value);
                                            $s = "";
                                            if ($estatus == $estatus_aux['0']) {
                                                $s = "selected";
                                            }
                                            echo "<option value='" . $estatus_aux[0] . "' $s>" . $estatus_aux[1] . "</option>";
                                        }
                                    ?>
                                </select>
                            </div>
                        <?php endif; ?>
                        <!--Ejecutivo-->
                        <div class="form-group col-12 col-md-4">
                            <label for="sl_atencion" class="m-0">Ejecutivo A.C.</label>
                            <select id="sl_atencion" name="sl_atencion" class="custom-select">
                                <option value="0">Todos los ejecutivos</option>
                                <?php
                                    $query_vendedor = $catalogo->obtenerLista("SELECT CONCAT(Nombre,' ',ApellidoPaterno,' ',ApellidoMaterno) AS Nombre, "
                                            . "IdUsuario, Loggin "
                                            . "FROM c_usuario WHERE Activo = 1 ORDER BY Nombre;");
                                    while ($rs = mysql_fetch_array($query_vendedor)) {
                                        $s = "";
                                        if ($id_ejecutivo == $rs['IdUsuario']) {
                                            $s = "selected";
                                        }
                                        echo "<option value='" . $rs['IdUsuario'] . "' $s>" . $rs['Nombre'] . " (" . $rs['Loggin'] . ")</option>";
                                    }
                                ?>
                            </select>
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group col-12 col-md-4">
                            <input type="button" class="btn btn-secondary" id="boton_buscar" value="Buscar" onclick="buscar_cliente_pakal()"/>
                            <input type="submit" class="btn btn-secondary" value="Reporte clientes" />
                        </div>
                    </div>
                </div>
            </form>
            <input type="hidden" id="page" name="page" value="<?php echo $page; ?>"/>
            <input type="hidden" id="filter" name="filter" value="<?php echo $filter; ?>"/>
            <?php if ($permisos_grid->getAlta()) : ?>
                <img class="imagenMouse" src="resources/images/add.png" title="Nuevo" onclick='cambiarContenidos("ventas/cliente_nuevo_pakal.php", "Alta cliente"); return false;' style="float: right; cursor: pointer;" />
            <?php 
                endif;     
                if (isset($_POST['id_cliente'])):
            ?>
                <div class="p-4 bg-light rounded">
                    <div class="table-responsive" id="tabla-contenedor">
                        <table id="tabla-datos" class="table">
                            <thead class="thead-dark">
                                <tr>
                                    <th>Nombre</th>
                                    <th>Vendedor</th>
                                    <th>RFC</th>
                                    <th>Tipo</th>
                                    <th>Estatus</th>
                                    <th>Modificar</th>
                                    <?php
                                        if ($factura) echo "<th>Generar factura</th>";
                                        if ($abcContactos) echo "<th>Contactos</th>";
                                    ?>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                    $query = $catalogo->obtenerLista($consulta);                        
                                    while ($rs = mysql_fetch_array($query)) {
                                        $grupo = "";
                                        if (isset($rs['NombreGrupo']) && $rs['NombreGrupo'] != "") $grupo = "(" . $rs['NombreGrupo'] . ") ";
                                        echo "<tr>
                                                <td>" . $rs['NombreRazonSocial'] . " $grupo</td>
                                                <td>" . $rs['vendedor'] . "</td>
                                                <td>" . $rs['RFC'] . "</td>
                                                <td>" . $rs['tipoCliente'] . "</td>
                                                <td>" . $rs['estatus'] . "</td>
                                                <td>";
                                        if ($permisos_grid->getModificar()) {
                                            echo "<a href='#' onclick='cambiarContenidos(\"ventas/cliente_nuevo_pakal.php?id=".$rs['ClaveCliente']."\", \"Modificar cliente\");return false;' title='Detalle'>
                                                    <img src='resources/images/Modify.png'/>
                                                </a>";
                                        } else if ($permisos_grid->getConsulta()) {
                                            echo "<a href='#' onclick='cambiarContenidos(\"ventas/cliente_nuevo.php?id=". $rs['ClaveCliente'] ."\",\"Modificar cliente\");return false;' title='Detalle' >
                                                    <img src='resources/images/Textpreview.png'/>
                                                </a>";
                                        }
                                        echo "</td>";
                                        
                                        if ($factura) {
                                            echo "<td>
                                                    <a href='principal.php?mnu=facturacion&action=alta_factura&param1=" . $rs['ClaveCliente'] . "' target='_blank' title='Facturar'>
                                                        <img src='resources/images/facturar.png' width='35' height='35'/>
                                                    </a>
                                                </td>";
                                        }
                                        if($abcContactos) {
                                            echo "<td>
                                                    <a href='#' title='Alta contacto' onclick=\"lanzarPopUp('Alta contacto', 'cliente/alta_contacto.php?ClaveCliente=". $rs['ClaveCliente']."');return false;\">
                                                        <img src='resources/images/contact_icon.ico' width='28' height='28'/>
                                                    </a>
                                                </td>";
                                        }
                                        echo "</tr>";
                                    }
                                ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            <?php endif; ?>
            <!-- <script src="js/tables.js"></script> -->
        </div>
    </body>
</html>