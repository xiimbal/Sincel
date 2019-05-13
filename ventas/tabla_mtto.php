<?php
session_start();

if (!isset($_SESSION['user']) || $_SESSION['user'] == "") {
    header("Location: index.php");
}

include_once("../WEB-INF/Classes/Catalogo.class.php");
include_once("../WEB-INF/Classes/PermisosSubMenu.class.php");
$permisos_grid = new PermisosSubMenu();
$same_page = "ventas/lista_mttos.php";
$permisos_grid->getPermisosSubmenu($_SESSION['idUsuario'], $same_page);

$urlextra = "";
$where = "WHERE c_mantenimiento.Estatus=0 ";
if (isset($_POST['noserie']) && $_POST['noserie'] != "") {
    $where.=" AND c_mantenimiento.NoSerie LIKE '%" . $_POST['noserie'] . "%' ";
    if ($urlextra == "") {
        $urlextra.="?noserie=" . $_POST['noserie'];
    } else {
        $urlextra.="&noserie=" . $_POST['noserie'];
    }
}

if (isset($_POST['vendedor']) && $_POST['vendedor'] != "") {
    $where.=" AND c_cliente.EjecutivoCuenta LIKE '%" . $_POST['vendedor'] . "%' ";
    if ($urlextra == "") {
        $urlextra.="?vendedor=" . $_POST['vendedor'];
    } else {
        $urlextra.="&vendedor=" . $_POST['vendedor'];
    }
}

if (isset($_POST['cliente']) && $_POST['cliente'] != "") {
    $where.=" AND c_cliente.ClaveCliente='" . $_POST['cliente'] . "' ";
    if (isset($_POST['localidad']) && $_POST['localidad'] != "") {
        $where.=" AND c_mantenimiento.ClaveCentroCosto='" . $_POST['localidad'] . "' ";
        if ($urlextra == "") {
            $urlextra.="?localidad=" . $_POST['localidad'];
        } else {
            $urlextra.="&localidad=" . $_POST['localidad'];
        }
    }
    if ($urlextra == "") {
        $urlextra.="?cliente=" . $_POST['cliente'];
    } else {
        $urlextra.="&cliente=" . $_POST['cliente'];
    }
}
if (isset($_POST['fecha1']) && $_POST['fecha1'] != "" && isset($_POST['fecha2']) && $_POST['fecha2'] != "") {
    $where.=" AND c_mantenimiento.Fecha BETWEEN '" . $_POST['fecha1'] . "' AND '" . $_POST['fecha2'] . "'";
    if ($urlextra == "") {
        $urlextra.="?fecha1=" . $_POST['fecha1'] . "&fecha2=" . $_POST['fecha2'];
    } else {
        $urlextra.="&fecha1=" . $_POST['fecha1'] . "&fecha2=" . $_POST['fecha2'];
    }
}

$catalogo = new Catalogo();
$query = $catalogo->obtenerLista("SELECT
	c_mantenimiento.IdMantenimiento AS ID,
	c_mantenimiento.NoSerie AS NoSerie,
	c_mantenimiento.Fecha AS Fecha,
	if(c_mantenimiento.Estatus=0,'En proceso','') AS Estatus,
	c_centrocosto.Nombre AS CentroCosto,
	c_cliente.NombreRazonSocial AS Cliente
FROM
	c_mantenimiento
INNER JOIN c_centrocosto ON c_centrocosto.ClaveCentroCosto = c_mantenimiento.ClaveCentroCosto
INNER JOIN c_cliente ON c_cliente.ClaveCliente = c_centrocosto.ClaveCliente " . $where);
$cabeceras = array("Fecha", "No Serie", "Cliente", "Localidad", "Estatus", "", "");
$alta = "ventas/Editar_mtto.php";
$nuevo = "ventas/Nuevo_mtto.php";
$eliminar = "WEB-INF/Controllers/Ventas/Controller_Eliminar_mtto.php";
?>
<script type="text/javascript" language="javascript" src="resources/js/paginas/ventas/tabla_mtto.js"></script>
<?php if($permisos_grid->getAlta()){ ?>
<img class="imagenMouse" src="resources/images/add.png" title="Nuevo" onclick='cambiarContenidos("<?php echo $nuevo. $urlextra ; ?>");' style="float: right; cursor: pointer;" />  
<?php } ?>
<br/><br/><br/>
<table class="table-responsive" id="tmtto">
    <thead>
        <tr>
            <?php
            foreach ($cabeceras as $a) {
                echo "<th width=\"2%\" align=\"center\" scope=\"col\">" . $a . "</th>";
            }
            ?>
        </tr>
    </thead>
    <tbody>
        <?php
        while ($rs = mysql_fetch_array($query)) {
            echo "<tr>";
            echo "<td width=\"2%\" align=\"center\" scope=\"col\">" . $rs['Fecha'] . "</td>";
            echo "<td width=\"2%\" align=\"center\" scope=\"col\">" . $rs['NoSerie'] . "</td>";
            echo "<td width=\"2%\" align=\"center\" scope=\"col\">" . $rs['Cliente'] . "</td>";
            echo "<td width=\"2%\" align=\"center\" scope=\"col\">" . $rs['CentroCosto'] . "</td>";
            echo "<td width=\"2%\" align=\"center\" scope=\"col\">" . $rs['Estatus'] . "</td>";            
            echo "<td width=\"2%\" align=\"center\" scope=\"col\">";             
            if($permisos_grid->getModificar()){
                echo"<a href='#' onclick='editarRegistro(\"" . $alta . $urlextra . "\",\"" . $rs['ID'] . "\");' title='Editar Registro' ><img src='resources/images/Modify.png'/></a>"; 
            }
            echo "</td>";
            echo "<td width=\"2%\" align=\"center\" scope=\"col\">";            
            if($permisos_grid->getBaja()){
                echo"<a href='#' onclick='eliminarRegistromtto(\"" . $eliminar . "\",\"" . $rs['ID'] . "\",\"ventas/lista_mttos.php" . $urlextra . "\");return false;' title='Eliminar Registro' ><img src='resources/images/Erase.png'/></a>"; 
            }
            echo"</td>";
            echo "</tr>";
        }
        ?>
    </tbody>
</table>