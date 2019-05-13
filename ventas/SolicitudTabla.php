<?php
/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
session_start();

if (!isset($_SESSION['user']) || $_SESSION['user'] == "") {
    header("Location: index.php");
}
include_once("../WEB-INF/Classes/Catalogo.class.php");
include_once("../WEB-INF/Classes/PermisosSubMenu.class.php");
$psm = new PermisosSubMenu();
$catalogo = new Catalogo();
?>
<div class='ui-state-highlight ui-corner-all' style='height: 33px;margin-top:15px; margin-bottom:15px;'><p><span class='ui-icon ui-icon-info' style='float: left;'></span>Captura tu pedido</p></div>
<?php
if ($_POST['tipo'] != 6) {
    ?>
    <table id="tsolform" style="max-width: 97%;">
        <tr id="filaSolicitud_1">
            <td>
                <label for="numero">
                    Cantidad
                </label>
            </td>
            <td>
                <input type="text" id="numero" name="numero" maxlength="5" style="width: 40px;"/>
            </td>
            <td>
                <label for="tipo">
                    Tipo
                </label>
            </td>
            <td>
                <select id="tipo" name="tipo" class="tipo" onchange="
                            cambiarselectmodelo('tipo', 'modelo');
                            mostrarTipoInventario('tipo', 'tipo_inventario', 'div_serie_cliente');" 
                    style="max-width: 130px;">
                    <option value="">Seleccione tipo</option>
                    <option value="0">Equipo</option>
                    <?php
                    $query2 = $catalogo->obtenerLista("SELECT c_tipocomponente.IdTipoComponente AS ID,c_tipocomponente.Nombre AS Nombre 
                        FROM c_tipocomponente ORDER BY Nombre;");
                    while ($rs = mysql_fetch_array($query2)) {
                        echo "<option value=\"" . $rs['ID'] . "\">" . $rs['Nombre'] . "</option>";
                    }
                    ?>
                </select>
            </td>            
            <td>
                <select id="modelo" name="modelo" class="size filtro" style="width: 150px;">
                    <option value="">Selecciona el modelo</option>
                </select>
            </td>
            <?php if($psm->tienePermisoEspecial($_SESSION['idUsuario'], 35)){   ?>
            <td>
                <a href="#" title="Ver existencias" onclick="lanzarPopUpVerExistencias('Ver existencias', 'ventas/verificarExistenciasTipoModelo.php');
                            return false;"><img src="resources/images/existencias.png" width="28" height="28" style="margin-left: 10%;"/></a>
            </td>
            <?php } ?>
            <td>
                <select id="localidad" name="localidad" class="size filtro localidad" style="width: 150px;" 
                        onchange="actualizarDatosContrato();
                                mostrarEquiposLocalidad('localidad', 'serie_con_cliente');">
                </select>
            </td>
            <td>
                <select id="tipo_inventario" name="tipo_inventario" style="display: none;">
                    <?php
                    $query2 = $catalogo->obtenerLista("SELECT idTipo AS ID, Nombre FROM `c_tipoinventario` WHERE idTipo IN(1,9) AND Activo = 1;");
                    while ($rs = mysql_fetch_array($query2)) {
                        echo "<option value=\"" . $rs['ID'] . "\">" . $rs['Nombre'] . "</option>";
                    }
                    ?>
                </select>
                <div id="div_serie_cliente" style="display: none;">
                    <label for="serie_con_cliente">Equipo en localidad</label>
                    <select id="serie_con_cliente" name="serie_con_cliente">
                        <option value="">Selecciona un equipo</option>
                    </select>
                </div>                
            </td>
            <td>
                <label for="ubicacion">Ubicación</label>
            </td>
            <td>
                <input type="text" id="ubicacion" name="ubicacion" />
            </td>
            <td>
                <label for="retiro"># retiro</label>
            </td>
            <td>
                <input type="number" id="retiro" name="retiro" style="width: 40px;"/>
            </td>
            <td>
                <input type="button" class="boton" onclick="enviardetalle();" value="Agregar" style="float: right"/>
                <input type="button" class="boton" onclick="borrar();" value="Borrar" style="float: right"/>
            </td>
        </tr>
    </table>
<?php } else {
    ?><table id="tsolform" style="max-width: 97%;">
        <tr id="filaSolicitud_1">
            <td>
                <label for="numero">
                    Cantidad
                </label>
            </td>
            <td>
                <input type="text" id="numero" name="numero" maxlength="5" style="width: 20px;" onkeyup="calcularcostocant('numero', 'costo', 'total', 'costotro');"/>
            </td>
            <td>
                <label for="tipo">
                    Tipo
                </label>
            </td>
            <td>
                <select id="tipo" name="tipo" class="tipo" onchange="cambiarselectmodelo('tipo', 'modelo');
                        mostrarTipoInventario('tipo', 'tipo_inventario', 'div_serie_cliente');" style="max-width: 100px;">
                    <option value="">Seleccione tipo</option>
                    <option value="0">Equipo</option>
                    <?php
                    $query2 = $catalogo->obtenerLista("SELECT c_tipocomponente.IdTipoComponente AS ID,c_tipocomponente.Nombre AS Nombre FROM c_tipocomponente ORDER BY Nombre;");
                    while ($rs = mysql_fetch_array($query2)) {
                        echo "<option value=\"" . $rs['ID'] . "\">" . $rs['Nombre'] . "</option>";
                    }
                    ?>
                </select>
            </td>            
            <td>
                <select id="modelo" name="modelo" class="size filtro" onchange="cargarprecio('tipo', 'modelo', 'costo');" style="width: 150px;">
                    <option value="">Selecciona el modelo</option>
                </select>

            </td>
            <td>
                <label for="costo">
                    Costo
                </label>
            </td>
            <td>
                <select id="costo" name="costo" class="size filtro" onchange="calcularcostop('numero', 'costo', 'total', 'costotro', 'otrolabel', 'otroinput');"></select>
            </td>
            <td id="otrolabel">
                <label for="costotro">
                    Otro
                </label>
            </td>
            <td id="otroinput">
                <input type="text" id="costotro" name="costotro" onkeyup="calcularcosto('numero', 'costotro', 'total');" value="0" style="width: 50px;">
            </td>
            <td>
                <label for="total">
                    Total
                </label>
            </td>
            <td>
                <input type="text" id="total" name="total" style="width: 50px;" readonly="readonly"/>
            </td>            
            <td>
                <select id="tipo_inventario" name="tipo_inventario" style="display: none;">
                    <?php
                    $query2 = $catalogo->obtenerLista("SELECT idTipo AS ID, Nombre FROM `c_tipoinventario` WHERE idTipo IN(1,9) AND Activo = 1;");
                    while ($rs = mysql_fetch_array($query2)) {
                        echo "<option value=\"" . $rs['ID'] . "\">" . $rs['Nombre'] . "</option>";
                    }
                    ?>
                </select>
                <div id="div_serie_cliente" style="display: none;">
                    <label for="serie_con_cliente">Equipo en localidad</label>
                    <select id="serie_con_cliente" name="serie_con_cliente" style="width: 100px;" >
                        <option value="">Selecciona un equipo</option>
                    </select>
                </div>                
            </td>
            <td>
                <label for="ubicacion">Ubicación</label>
            </td>
            <td>
                <input type="text" id="ubicacion" name="ubicacion" />
            </td>
            <td>
                <label for="retiro"># retiro</label>
            </td>
            <td>
                <input type="number" id="retiro" name="retiro" style="width: 40px;" maxlength="6"/>
            </td>
            <td>
                <input type="button" class="boton" onclick="enviardetalle();" value="Agregar" style="float: right"/>
                <input type="button" class="boton" onclick="borrar();" value="Borrar" style="float: right"/>
            </td>
        </tr>
    </table>
<?php }
?>
