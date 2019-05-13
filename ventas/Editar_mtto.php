<?php
session_start();
if (!isset($_SESSION['user']) || $_SESSION['user'] == "") {
    header("Location: index.php");
}
include_once("../WEB-INF/Classes/mtto.class.php");
include_once("../WEB-INF/Classes/Catalogo.class.php");
$urlextra = "";
if (isset($_GET['noserie']) && $_GET['noserie'] != "") {
    if ($urlextra == "") {
        $urlextra.="?noserie=" . $_GET['noserie'];
    } else {
        $urlextra.="&noserie=" . $_GET['noserie'];
    }
}

if (isset($_GET['vendedor']) && $_GET['vendedor'] != "") {
    if ($urlextra == "") {
        $urlextra.="?vendedor=" . $_GET['vendedor'];
    } else {
        $urlextra.="&vendedor=" . $_GET['vendedor'];
    }
}

if (isset($_GET['cliente']) && $_GET['cliente'] != "") {
    if (isset($_GET['localidad']) && $_GET['localidad'] != "") {
        if ($urlextra == "") {
            $urlextra.="?localidad=" . $_GET['localidad'];
        } else {
            $urlextra.="&localidad=" . $_GET['localidad'];
        }
    }
    if ($urlextra == "") {
        $urlextra.="?cliente=" . $_GET['cliente'];
    } else {
        $urlextra.="&cliente=" . $_GET['cliente'];
    }
}
if (isset($_GET['fecha1']) && $_GET['fecha1'] != "" && isset($_GET['fecha2']) && $_GET['fecha2'] != "") {
    if ($urlextra == "") {
        $urlextra.="?fecha1=" . $_GET['fecha1'] . "&fecha2=" . $_GET['fecha2'];
    } else {
        $urlextra.="&fecha1=" . $_GET['fecha1'] . "&fecha2=" . $_GET['fecha2'];
    }
}
$mtto = new mtto();
$mtto->setId_mtto($_POST['id']);
$mtto->getMantenimientoByID();
?>
<script type="text/javascript" language="javascript" src="resources/js/paginas/ventas/Editar_mtto.js"></script>
<!--link responsivo-->
        <link href="resources/css/Bootstrap 4/bootstrap.min.css" rel="stylesheet">
        <link href="resources/css/Bootstrap 4/fontawesome/css/all.min.css" rel="stylesheet"> 
<br/><br/>

<form id="formmtto">
 <div class="container-fluid">
        <div class="form-row">

        <div class="form-group  col-md-4">
            <label for="fecha">Fecha</label>
            <input class="form-control" type="text" id="fecha" name="fecha" value="<?php echo $mtto->getFecha() ?>"/>
        </div>

        <div class="form-group  col-md-4">
            <label for="NoSerie">NoSerie</label>
            <input class="form-control" type="text" id="NoSerie" name="NoSerie" value="<?php echo $mtto->getNoSerie() ?>" disabled="disabled"/>
        </div>

        <div class="form-group  col-md-4">
            <label for="Estatus">Estatus</label>
            <input class="form-control" type="text" id="Estatus" name="Estatus" value="<?php echo $mtto->getEstatus() ?>" disabled="disabled"/>
        </div>

        <div class="form-group  col-md-4">
            <label for="cliente">Cliente</label>
            <select class="form-control" id="cliente" name="cliente" disabled="disabled">
                    <?php
                    /* Inicializamos la clase */
                    $catalogo = new Catalogo();
                    $query = $catalogo->obtenerLista("SELECT DISTINCT c_cliente.NombreRazonSocial AS cliente,c_cliente.ClaveCliente AS ID FROM c_mantenimiento
INNER JOIN c_centrocosto ON c_centrocosto.ClaveCentroCosto=c_mantenimiento.ClaveCentroCosto
INNER JOIN c_cliente ON c_cliente.ClaveCliente=c_centrocosto.ClaveCliente");
                    echo "<option value=''>Todos los clientes</option>";
                    while ($rs = mysql_fetch_array($query)) {
                        if ($mtto->getCliente() == $rs['ID']) {
                            echo "<option value='" . $rs['ID'] . "' selected>" . $rs['cliente'] . "</option>";
                        } else {
                            echo "<option value='" . $rs['ID'] . "' >" . $rs['cliente'] . "</option>";
                        }
                    }
                    ?> 
                </select>
            </div>


        <div class="form-group  col-md-4" >
            <label for="localidad">Localidad</label>
                <select class="form-control" id="localidad" name="localidad"  disabled="disabled">
                    <?php
                    $query3 = $catalogo->obtenerLista("SELECT c_centrocosto.ClaveCentroCosto AS ID,c_centrocosto.Nombre AS Nombre 
FROM c_centrocosto 
INNER JOIN c_cliente ON c_cliente.ClaveCliente=c_centrocosto.ClaveCliente
WHERE c_cliente.ClaveCliente='" . $mtto->getCliente() . "'");
                    echo "<option value=\"\" >Selecciona la localidad</option>";
                    while ($rsp = mysql_fetch_array($query3)) {
                        if ($mtto->getLocalidad() == $rsp['ID']) {
                            echo "<option value=\"" . $rsp['ID'] . "\" selected >" . $rsp['Nombre'] . "</option>";
                        } else {
                            echo "<option value=\"" . $rsp['ID'] . "\" >" . $rsp['Nombre'] . "</option>";
                        }
                    }
                    ?> 
                </select>
            </div>

    <input type="hidden" name="idmtto" value="<?php echo $mtto->getId_mtto() ?>"/>
    <input type="submit" id="aceptar" class="button btn btn-lang btn-block btn-outline-success mt-3 mb-3" name="aceptar" value="Guardar"/>
    <input type="button" id="cancelar" class="button btn btn-lang btn-block btn-outline-danger mt-3 mb-3" name="cancelar" value="Cancelar" onclick="cambiarContenidos('ventas/lista_mttos.php<?php echo $urlextra ?>', 'Mtto Preventivo');"/>
</div>
</div>

</form>
<script type="text/javascript" language="javascript">
        setdireccion("<?php echo $urlextra ?>");
</script>