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

         <link href="resources/css/Bootstrap 4/bootstrap.min.css" rel="stylesheet">
        <link href="resources/css/Bootstrap 4/fontawesome/all.min.css" rel="stylesheet">
        
    </head>
    <body>
        <div class="principal">       
            <form action="reportes/reporte_clientes_light.php" id="form_clientes" method="post" target="_blank" >
                <div class="container-fluid">
                	<div class="form-row">
                    <!--Clientes-->
                    <div class="form-group col-md-4">
                        <label for="sl_cliente">Cliente</label>
                        <input class="form-control" id="sl_cliente" class="auto_complete_text" value="<?php echo $id_cliente; ?>"/>
                    </div>
                    <!--RFC-->
                    <div class="form-group col-md-4">
                        <label for="txt_rfc">RFC</label>
                        <input class="form-control" type="text" id="txt_rfc" name="txt_rfc" value="<?php echo $rfc;?>"/>
                    </div>
                    <!--Vendedor-->
                    <?php if (!$usuario->isUsuarioPuesto($_SESSION['idUsuario'], 11)): ?>
                    <div class="form-group col-md-4">
                        <label for="sl_vendedor">Ejecutivo de cuenta</label>
                        <select class="form-control" id="sl_vendedor" name="sl_vendedor">
                            <option value="0">Todos los vendedores</option>
                            <?php
                                $query_vendedor = $catalogo->obtenerLista("SELECT usu.IdUsuario,CONCAT(usu.Nombre,' ',usu.ApellidoPaterno,' ',usu.ApellidoMaterno) aS usuario FROM c_cliente cl INNER JOIN c_usuario usu ON cl.EjecutivoCuenta=usu.IdUsuario GROUP BY usu.IdUsuario");
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
                    <!--Tipo-->
                    <div class="form-group col-md-4">
                        <label for="sl_tipo">Tipo</label>
                        <select class="form-control" id="sl_tipo" name="sl_tipo">
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
                    </div>
                    <!--Estatus-->
                    <?php if ($permiso_inctivos): ?>
                    <div class="form-group col-md-4">
                        <label for="sl_estatus">Estatus</label>
                        <select  class="form-control" id="sl_estatus" name="sl_estatus">
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
                    <!--Atencion a clientes-->
                    <div class="form-group col-md-4">
                        <label for="sl_atencion">Ejecutivo Atención a Clientes</label>
                        <select class="form-control" id="sl_atencion" name="sl_atencion">
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
                    <!--Botonera-->
                    <input type="button" class="button btn btn-lang btn-block btn-outline-primary mt-3 mb-3" id="boton_buscar" value="Buscar" onclick="buscar_cliente()"/>
                    <input type="submit" class="button btn btn-lang btn-block btn-outline-warning mt-3 mb-3" style="cursor: pointer;" value="Reporte clientes" />
                </div>
            </div>
            </form>
            <input type="hidden" id="page" name="page" value="<?php echo $page; ?>"/>
            <input type="hidden" id="filter" name="filter" value="<?php echo $filter; ?>"/>
            <?php if ($permisos_grid->getAlta()): ?>
                <img class="imagenMouse" src="resources/images/add.png" title="Nuevo"  onclick='cambiarContenidos("ventas/cliente_nuevo.php", "Alta cliente"); return false;' style="float: right; cursor: pointer;" />
            <?php endif; ?>
            <?php if (isset($_POST['id_cliente'])): ?>
                <div class="table-responsive">
                    <table class="table" id="tAlmacen">
                        <thead class="thead-dark">
                            <tr>
                                <th>Nombre</th>
                                <th>Vendedor</th>
                                <th>Rfc</th>
                                <th>Tipo</th>
                                <th>Estatus</th>
                                <th>Histórico</th>
                                <th>Modificar</th>
                                    <?php
                                    if ($factura) {
                                        echo "<th>Generar factura</th>";
                                    }
                                    if($abcContactos){
                                        echo "<th>Contactos</th>";
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
                                echo "<tr>
                                        <td>" . $rs['NombreRazonSocial'] . " $grupo</td>
                                        <td>" . $rs['vendedor'] . "</td>
                                        <td>" . $rs['RFC'] . "</td>
                                        <td>" . $rs['tipoCliente'] . "</td>
                                        <td>" . $rs['estatus'] . "</td>
                                        <td>
                                            <a href='#' onclick='cambiarContenidos(\"$historico?id=" . $rs['ClaveCliente'] . "\",\"Ventas > Histórico de cliente\")' title='Histórico' >
                                                <i class='far fa-history' style='font-size:1.2rem;'></i>
                                            </a>
                                        </td>";
                                ?>
                            <td> 
                                <?php if ($permisos_grid->getModificar()) { ?>
                                    <a href='#' onclick='cambiarContenidos("ventas/cliente_nuevo.php?id=<?php echo $rs['ClaveCliente']; ?>", "Modificar cliente"); return false;' title='Detalle' >
                                        <i class="fal fa-file-edit" style="font-size:1.2rem;"></i>
                                    </a>
                                <?php } else if ($permisos_grid->getConsulta()) { ?>
                                    <a href='#' onclick='cambiarContenidos("ventas/cliente_nuevo.php?id=<?php echo $rs['ClaveCliente']; ?>", "Modificar cliente");  return false;' title='Detalle' >
                                        <i class="far fa-eye" style="font-size:1.2rem;"></i>
                                    </a>
                                <?php } ?>
                            </td>
                            <?php
                            if ($factura) {
                                echo "<td>
                                        <a href='principal.php?mnu=facturacion&action=alta_factura_33&param1=" . $rs['ClaveCliente'] ."' target='_blank' title='Facturar'>
                                            <i class='fal fa-ballot-check' style='font-size:1.2rem;'></i>
                                        </a>
                                    </td>";
                            }
                            if($abcContactos) {
                            ?>
                                <td>
                                    <a href="#" title="Alta contacto" onclick="lanzarPopUp('Alta contacto', 'cliente/alta_contacto.php?ClaveCliente=<?php echo $rs['ClaveCliente']; ?>'); return false;">
                                        <i class="fal fa-users" style="font-size:1.2rem;"></i>
                                    </a>
                                </td>
                            <?php
                            }
                            echo "</tr>";
                        }
                        ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </body>
</html>