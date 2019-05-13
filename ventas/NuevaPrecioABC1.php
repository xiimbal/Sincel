<?php
session_start();
if (!isset($_SESSION['user']) || $_SESSION['user'] == "") {
    header("Location: index.php");
}
include_once("../WEB-INF/Classes/Catalogo.class.php");
$catalogo = new Catalogo();
?>
<script type="text/javascript" language="javascript" src="resources/js/paginas/ventas/Nuevo_precioabc.js"></script>
<form id="formprecioabc">
    <table style="width: 100%;">
        <tr>
            <td>Tipo</td>
            <td>
                <select id="tipo" name="tipo" class="filtro" style="width: 200px;" onchange="cargarmodelo('tipo', 'modelo');">
                    <option value="">Selecciona el tipo</option>
                    <option value="0">Equipo</option>
                    <?php
                    $query2 = $catalogo->obtenerLista("SELECT c_tipocomponente.IdTipoComponente AS ID,c_tipocomponente.Nombre AS Nombre FROM c_tipocomponente ORDER BY Nombre;");
                    while ($rs = mysql_fetch_array($query2)) {
                        echo "<option value=\"" . $rs['ID'] . "\">" . $rs['Nombre'] . "</option>";
                    }
                    ?>
                </select>
            </td>            
            <td>Modelo</td>
            <td><select id="modelo" name="modelo" class="filtro" style="width: 200px;" onchange="cargarnoparte('modelo', 'tipo', 'noparte');"></select></td>
            <td>Almacén</td>
            <td>
                <select id="almacen" name="almacen" class="filtro" style="width: 200px;">
                    <option value="">Selecciona el almacén</option>                    
                    <?php
                    $query2 = $catalogo->getListaAlta("c_almacen", "nombre_almacen");
                    while ($rs = mysql_fetch_array($query2)) {                        
                        echo "<option value=\"" . $rs['id_almacen'] . "\">" . $rs['nombre_almacen'] . "</option>";
                    }
                    ?>
                </select>
            </td>
        </tr>
        <tr>
            <td><br/><br/></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
        </tr>
        <tr>
            <td>Precio A</td>
            <td><input type="text" id="precioa" name="precioa" style="width: 200px;" ></td>
            <td>Precio B</td>
            <td><input type="text" id="preciob" name="preciob" style="width: 200px;" ></td>
            <td>Precio C</td>
            <td><input type="text" id="precioc" name="precioc" style="width: 200px;" ></td>
        </tr>
    </table>
    <br/><br/>
    <input type="submit" id="aceptar" class="boton" name="aceptar" value="Guardar"/>
    <input type="button" id="cancelar" class="boton" name="cancelar" value="Cancelar" onclick="cambiarContenidos('ventas/lista_precios_abc.php', 'Precios ABC');"/>
</form>