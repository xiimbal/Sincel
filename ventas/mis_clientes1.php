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
$same_page = "ventas/mis_clientes.php";
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
        if ($where == "") {
            $where = " WHERE (";
        } else {
            $where .= " AND (";
        }

        foreach ($clientes as $value) {
            if(empty($value) || (trim($value) == "")){
                continue;
            }
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
    if ($where == "") {
        $where = " WHERE c.RFC = '$rfc'";
    } else {
        $where .= " AND c.RFC = '$rfc'";
    }
}
if (isset($_POST['id_vendedor']) && $_POST['id_vendedor'] != "0") {
    $id_vendedor = $_POST['id_vendedor'];
    if ($where == "") {
        $where = " WHERE u.IdUsuario = '$id_vendedor'";
    } else {
        $where .= " AND u.IdUsuario = '$id_vendedor'";
    }
}
if (isset($_POST['id_ejecutivo']) && $_POST['id_ejecutivo'] != "0") {
    $id_ejecutivo = $_POST['id_ejecutivo'];
    if ($where == "") {
        $where = " WHERE c.EjecutivoAtencionCliente = $id_ejecutivo";
    } else {
        $where .= " AND c.EjecutivoAtencionCliente = $id_ejecutivo";
    }
}
if ($permiso_inctivos) {
    if (isset($_POST['estatus']) && $_POST['estatus'] != "") {
        $estatus = $_POST['estatus'];
        if ($where == "") {
            $where = " WHERE c.Activo = '$estatus'";
        } else {
            $where .= " AND c.Activo = '$estatus'";
        }
    }
} else {
    if ($where == "") {
        $where = " WHERE c.Activo = '1'";
    } else {
        $where .= " AND c.Activo = '1'";
    }
}
if (isset($_POST['tipo']) && $_POST['tipo'] != "0") {
    $tipo = $_POST['tipo'];
    if ($where == "") {
        $where = " WHERE c.Modalidad = '$tipo'";
    } else {
        $where .= "  AND c.Modalidad= '$tipo'";
    }
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
            <form action="reportes/reporte_clientes_light.php" id="form_clientes" method="post" target="_blank" >
                <table style="width: 100%">
                    <tr>
                        <td>Cliente</td>
                        <td>
                            <input id="sl_cliente" class="auto_complete_text" style="width: 180px;" value="<?php echo $id_cliente; ?>">                            
                        </td>
                        <td>RFC</td>
                        <td><input type="text" id="txt_rfc" name="txt_rfc" value="<?php echo $rfc;
                            ?>" style="width: 180px"/></td>
                                   <?php if (!$usuario->isUsuarioPuesto($_SESSION['idUsuario'], 11)) { ?>
                            <td>Ejecutivo de cuenta</td>
                            <td>
                                <select id="sl_vendedor" name="sl_vendedor" style="width: 170px">
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
                            </td>
                        <?php } ?>
                    </tr>
                    <tr>
                        <td>Tipo</td>
                        <td>
                            <select id="sl_tipo" name="sl_tipo" style="width: 170px">
                                <option value="0">Todos los tipos</option>
                                <?php
                                $query_tipo = $catalogo->getListaAltaTodo("c_clientemodalidad", "Nombre");
                                while ($rs = mysql_fetch_array($query_tipo)) {
                                    $s = "";
                                    if ($tipo == $rs['IdTipoCliente']) {
                                        $s = "selected";
                                    }
                                    echo "<option value='" . $rs['IdTipoCliente'] . "' $s>" . $rs['Nombre'] . "</option>";
                                }
                                ?>
                            </select>
                        </td>
                        <?php if ($permiso_inctivos) { ?>
                            <td>Estatus</td>
                            <td>
                                <select id="sl_estatus" name="sl_estatus" style="width: 150px">
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
                            </td>
                        <?php } ?>
                        <td>Ejecutivo Atención a Clientes</td>
                        <td>
                            <select id="sl_atencion" name="sl_atencion" style="width: 170px">
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
                        </td>
                    </tr>
                    <tr>
                        <td colspan="4"></td>
                        <td><input type="button" class="boton" id="boton_buscar" value="Buscar" onclick="buscar_cliente()"/></td>
                        <td><input type="submit" class="boton" style="cursor: pointer;" value="Reporte clientes" /></td>
                    </tr>
                </table>
            </form>
            <input type="hidden" id="page" name="page" value="<?php echo $page; ?>"/>
            <input type="hidden" id="filter" name="filter" value="<?php echo $filter; ?>"/>
            <?php if ($permisos_grid->getAlta()) { ?>
                <img class="imagenMouse" src="resources/images/add.png" title="Nuevo"  onclick='cambiarContenidos("ventas/cliente_nuevo.php", "Alta cliente");
                            return false;' style="float: right; cursor: pointer;" />
                     <?php
                 }
                 if (isset($_POST['id_cliente'])) {
                     ?>
                <table id="tAlmacen" style="width: 100%;">
                    <thead>
                        <tr>
                            <th align='center' scope='row' style='width:30%'>Nombre</th>
                            <th align='center' scope='row' style='width:15%'>Vendedor</th>
                            <th align='center' scope='row' style='width:10%'>Rfc</th>
                            <th align='center' scope='row' style='width:8%'>Tipo</th>
                            <th align='center' scope='row' style='width:8%'>Estatus</th>
                            <th align="center" scope="row" style="width:8%">Histórico</th>
                            <th align='center' scope='row' style='width:8%'>Modificar</th>
                                <?php
                                if ($factura) {
                                    echo "<th align='center' scope='row' style='width:8%'>Generar factura</th>";
                                }
                                if($abcContactos){
                                    echo "<th align='center' scope='row' style='width:8%'>Contactos</th>";
                                }
                                ?>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $query = $catalogo->obtenerLista($consulta);                        
                        while ($rs = mysql_fetch_array($query)) {
                            $grupo = "";
                            if (isset($rs['NombreGrupo']) && $rs['NombreGrupo'] != "") {
                                $grupo = "(" . $rs['NombreGrupo'] . ") ";
                            }
                            echo "<tr>";
                            echo "<td align='center' scope='row'>" . $rs['NombreRazonSocial'] . " $grupo</td>";
                            echo "<td align='center' scope='row'>" . $rs['vendedor'] . "</td>";
                            echo "<td align='center' scope='row'>" . $rs['RFC'] . "</td>";
                            echo "<td align='center' scope='row'>" . $rs['tipoCliente'] . "</td>";
                            echo "<td align='center' scope='row'>" . $rs['estatus'] . "</td>";
                            echo "<td align='center' scope='row'><a href='#' onclick='cambiarContenidos(\"$historico?id=" . $rs['ClaveCliente'] . "\",\"Ventas > Histórico de cliente\")' title='Histórico' >
                                <img src='resources/images/historico.png' width='25' height='25'/></a></td>";
                            ?>
                        <td align='center' scope='row'> 
                            <?php if ($permisos_grid->getModificar()) { ?>
                                <a href='#' onclick='cambiarContenidos("ventas/cliente_nuevo.php?id=<?php echo $rs['ClaveCliente']; ?>", "Modificar cliente");
                                                    return false;' title='Detalle' ><img src="resources/images/Modify.png"/></a>
                               <?php } else if ($permisos_grid->getConsulta()) { ?>
                                <a href='#' onclick='cambiarContenidos("ventas/cliente_nuevo.php?id=<?php echo $rs['ClaveCliente']; ?>", "Modificar cliente");
                                                    return false;' title='Detalle' ><img src="resources/images/Textpreview.png"/></a>
                               <?php } ?>
                        </td>
                        <?php
                        if ($factura) {
                            echo "<td align='center' scope='row'>";
                            echo "<a href='principal.php?mnu=facturacion&action=alta_factura_33&param1=" . $rs['ClaveCliente'] . "' target='_blank' title='Facturar'>
                                    <img src='resources/images/facturar.png' width='35' height='35'/>
                                </a>";

                            echo "</td>";
                        }
                        if($abcContactos) {
                        ?>
                            <td align='center' scope='row'>
                            <a href="#" title="Alta contacto" onclick="lanzarPopUp('Alta contacto', 'cliente/alta_contacto.php?ClaveCliente=<?php echo $rs['ClaveCliente']; ?>');
                            return false;"><img src='resources/images/contact_icon.ico' width='28' height='28'/></a>
                            </td>
                        <?php
                        }
                        echo "</tr>";
                    }
                    ?>
                    </tbody>
                </table>
            <?php } ?>
        </div>
    </body>
</html>