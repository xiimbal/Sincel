<?php
session_start();

if (!isset($_SESSION['user']) || $_SESSION['user'] == "") {
    header("Location: index.php");
}

include_once("../WEB-INF/Classes/ReporteFacturacion.class.php");
include_once("../WEB-INF/Classes/Catalogo.class.php");
include_once("../WEB-INF/Classes/Catalogo.class.php");

$catalogo = new Catalogo();

?>

<script type="text/javascript" language="javascript" src="resources/js/paginas/reportes/Reporte_solicitud_resurtido.js"></script>
<br/><br/>
<form id="rtoners">
    <style>
        .ui-multiselect{
            width: 100%!important;
        }
    </style>
    <div class="form-row">
        <div class="form-group col-md-4">
            <label for="">Almacén</label>
            <select id="almacen" name="almacen">
                    <option value="">Todos los almacenes</option>
                    <?php
                    $query = $catalogo->obtenerLista("SELECT id_almacen,nombre_almacen FROM c_almacen ORDER BY nombre_almacen");
                    while ($rs = mysql_fetch_array($query)) {
                        echo "<option value='" . $rs['id_almacen'] . "' selected>" . $rs['nombre_almacen'] . " </option>";
                    }
                    ?>
                </select>
        </div>
        <div class="form-group col-md-4">
            <label for="fecha1">Fecha inicio</label>
            <input type="text" id="fecha1" name="fecha1" class="form-control" />
        </div>
        <div class="form-group col-md-4">
            <label for="fecha2">Fecha Fin</label>
            <input type="text" id="fecha2" name="fecha2" class="form-control" />
        </div>
    </div>
    <div class="form-row">
        <div class="form-group col-md-4">
            <label for="">Cliente</label>
              <select id="cliente" name="cliente" style="width: 200px;" onchange="cargarlocalidades('cliente','localidad');">
                    <?php
                    echo "<option value=''>Todos los clientes</option>";
                    $query = $catalogo->obtenerLista("SELECT ClaveCliente,NombreRazonSocial FROM c_cliente WHERE Activo=1 ORDER BY NombreRazonSocial");
                    while ($rs = mysql_fetch_array($query)) {
                        echo "<option value='" . $rs['ClaveCliente'] . "' >" . $rs['NombreRazonSocial'] . "</option>";
                    }
                    ?>
                </select>
        </div>
        <div class="form-group col-md-4">
            <label for="localidad">Localidad</label>
            <select id="localidad" name="localidad" style="width: 200px;" onchange="cargarequipos('localidad','equipo');">
                    <?php
                    echo "<option value=''>Todos las localidades</option>";
                    ?>
                </select>
        </div>
        <div class="form-group col-md-4">
            <label for="equipo">Equipo</label>
            <select id="equipo" name="equipo" style="width: 200px;">
                    <?php
                    echo "<option value=''>Todos los equipos</option>";
                    ?>
                </select>
        </div>
    </div>  
</form>
<div class="form-row">
    <div class="col-md-3">
        <input type="button" id="enviar" value="Consultar" class="btn btn-success btn-block" onclick="consultarreporttonner();"/>
    </div>
</div>
