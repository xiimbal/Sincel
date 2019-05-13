<?php
session_start();
if (!isset($_SESSION['user']) || $_SESSION['user'] == "") {
    header("Location: index.php");
}
include_once("../WEB-INF/Classes/Catalogo.class.php");
$catalogo = new Catalogo();
?>
<script type="text/javascript" language="javascript" src="resources/js/paginas/ventas/Nuevo_precioabc.js"></script>
<style>
    .ui-multiselect {
        max-width: 180px;
        font-size: 10px;
    }
    .ui-multiselect-checkboxes {
        font-size: 12px;
    }
</style>
<form id="formprecioabc">
    <div class="form-row">
        <div class="form-group col-md-4 col-12">
            <label for="tipo" class="m-0">Tipo</label>
            <select id="tipo" name="tipo" class="custom-select" onchange="cargarmodelo('tipo', 'modelo');">
                <option value="">Selecciona el tipo</option>
                <option value="0">Equipo</option>
                <?php
                $query2 = $catalogo->obtenerLista("SELECT c_tipocomponente.IdTipoComponente AS ID,c_tipocomponente.Nombre AS Nombre FROM c_tipocomponente ORDER BY Nombre;");
                while ($rs = mysql_fetch_array($query2)) {
                    echo "<option value=\"" . $rs['ID'] . "\">" . $rs['Nombre'] . "</option>";
                }
                ?>
            </select>
        </div>
        <div class="form-group col-md-4 col-12">
            <label for="modelo" class="m-0">Modelo</label>
            <select id="modelo" name="modelo" class="custom-select" onchange="cargarnoparte('modelo', 'tipo', 'noparte');"></select>
        </div>
        <div class="form-group col-md-4 col-12">
            <label for="almacen" class="m-0">Almacén</label>
            <select id="almacen" name="almacen" class="custom-select">
                <option value="">Selecciona el almacén</option>                    
                <?php
                $query2 = $catalogo->getListaAlta("c_almacen", "nombre_almacen");
                while ($rs = mysql_fetch_array($query2)) {                        
                    echo "<option value=\"" . $rs['id_almacen'] . "\">" . $rs['nombre_almacen'] . "</option>";
                }
                ?>
            </select>
        </div>
    </div>
    <div class="form-row">
        <div class="form-group col-md-4 col-12">
            <label for="precioa" class="m-0">Precio A</label>
            <input type="text" id="precioa" name="precioa" class="form-control">
        </div>
        <div class="form-group col-md-4 col-12">
            <label for="preciob" class="m-0">Precio B</label>
            <input type="text" id="preciob" name="preciob" class="form-control">
        </div>
        <div class="form-group col-md-4 col-12">
            <label for="precioc" class="m-0">Precio C</label>
            <input type="text" id="precioc" name="precioc" class="form-control" >
        </div>
    </div>
    <div class="form-row">
        <input type="submit" id="aceptar" class="btn btn-secondary" name="aceptar" value="Guardar"/>
        <input type="button" id="cancelar" class="btn btn-secondary" name="cancelar" value="Cancelar" onclick="cambiarContenidos('ventas/lista_precios_abc.php', 'Precios ABC');"/>
    </div>
</form>