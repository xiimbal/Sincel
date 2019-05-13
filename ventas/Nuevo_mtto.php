<?php
session_start();
if (!isset($_SESSION['user']) || $_SESSION['user'] == "") {
    header("Location: index.php");
}
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
include_once("../WEB-INF/Classes/Catalogo.class.php");
?>
<script type="text/javascript" language="javascript" src="resources/js/paginas/ventas/Nuevo_mtto.js"></script>
<!--link responsivo-->
        <link href="resources/css/Bootstrap 4/bootstrap.min.css" rel="stylesheet">
        <link href="resources/css/Bootstrap 4/fontawesome/css/all.min.css" rel="stylesheet"> 
<br/><br/>

<form id="formmtto">
    <div class="container-fluid">
        <div class="form-row">

            <div class="form-group  col-md-4">
            <label>Fecha</label>
            <input class="form-control" type="text" id="fecha" name="fecha" value=""/> 
           </div>
    
     
        <div class="form-group  col-md-4">
            <label>Cliente</label>
            <select class="form-control" id="cliente" name="cliente" onchange="cargarlocalidades('cliente', 'localidad');">
                    <?php
                    /* Inicializamos la clase */
                    $catalogo = new Catalogo();
                    $query = $catalogo->obtenerLista("SELECT c_puesto.IdPuesto FROM `c_usuario` INNER JOIN c_puesto ON c_usuario.IdPuesto=c_puesto.IdPuesto WHERE c_usuario.IdUsuario=" . $_SESSION['idUsuario']);
                    $rs = mysql_fetch_array($query);
                    $vendedor = false;
                    if ($rs['IdPuesto'] == 11) {
                        $vendedor = true;
                    }
                    if ($vendedor) {
                        "SELECT c_cliente.NombreRazonSocial AS Nombre,c_cliente.ClaveCliente AS ID FROM c_usuario
INNER JOIN c_cliente ON c_cliente.EjecutivoCuenta=" . $_SESSION['idUsuario'] . "
                    ORDER BY Nombre";
                    } else {
                        $consulta = "SELECT c_cliente.NombreRazonSocial AS Nombre,c_cliente.ClaveCliente AS ID FROM c_usuario
INNER JOIN c_cliente ON c_cliente.EjecutivoCuenta=c_usuario.IdUsuario
                    ORDER BY Nombre";
                    }
                    $query = $catalogo->obtenerLista($consulta);
                    echo "<option value=\"\">Selecciona cliente</option>";
                    while ($rs = mysql_fetch_array($query)) {
                        echo "<option value=\"" . $rs['ID'] . "\">" . $rs['Nombre'] . "</option>";
                    }
                    ?> 
                </select>
            </div>

        <div class="form-group  col-md-4">
            <label>Localidad</label>
                <select class="form-control" id="localidad" name="localidad"  onchange="cargarNoSerie('localidad', 'NoSerie');">
            </select>
        </div>

        <div class="form-group  col-md-4">
            <label>No Serie</label>
            <select  class="form-control" id="NoSerie" name="NoSerie">
        </select>
    </div>
           
    <input type="submit" id="aceptar" class="button btn btn-lang btn-block btn-outline-success mt-3 mb-3" name="aceptar" value="Guardar"/>
    <input type="button" id="cancelar" class="button btn btn-lang btn-block btn-outline-danger mt-3 mb-3" name="cancelar" value="Cancelar" onclick="cambiarContenidos('ventas/lista_mttos.php<?php echo $urlextra ?>', 'Mtto Preventivo');"/>
</div>
</div>

</form>
<script type="text/javascript" language="javascript">
                setdireccion("<?php echo $urlextra ?>");
</script>