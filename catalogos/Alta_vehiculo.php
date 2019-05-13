<?php
session_start();
if (!isset($_SESSION['user']) || $_SESSION['user'] == "") {
    header("Location: index.php");
}
include_once("../WEB-INF/Classes/Vehiculo.class.php");
$placas = "";
$modelo = "";
$activo = "";
$capacidad = "";
if (isset($_GET['id']) && $_GET['id']) {
    $conductor = new Vehiculo();
    $conductor->getRegistroById($_GET['id']);
    $placas = $conductor->getPlacas();
    $modelo =$conductor->getModelo();
    $capacidad = $conductor->getCapacidad();
    $activo = $conductor->getActivo();
}
?>
<script type="text/javascript" language="javascript" src="resources/js/paginas/catalogos/Alta_vehiculo.js"></script>
<form id="formvehiculo">
    <table style="width: 100%;">
        <tr>
            <td>Placas</td>
            <td>
                <input type="text" name="placas" id="placas" value="<?php echo $placas ?>"/>
            </td>
            <td>Modelo</td>
            <td>
                <input type="text" name="modelo" id="modelo" value="<?php echo $modelo ?>"/>
            </td>
            <td><label for="capacidad">Capacidad </label><span class="obligatorio"> *</span></td>
            <td><input type="text" id="capacidad" name="capacidad" value="<?php echo $capacidad; ?>"/></td>
            <td>Activo</td>
            <td><input type="checkbox" value="1" name="activo" id="activo" <?php
                if (isset($_GET['id']) && $_GET['id']) {
                    if ($activo != "" && $activo == 1) {
                        echo "checked";
                    }
                }else{
                    echo "checked";
                }
                ?>/></td>
        </tr>
    </table>
    <?php
    if (isset($_GET['id']) && $_GET['id']) {
        ?>
        <input type="hidden" name="id" id="id" value="<?php echo $_GET['id'] ?>"/>
<?php } ?>
    <br/><br/>
    <input type="submit" id="aceptar" class="boton" name="aceptar" value="Guardar"/>
    <input type="button" id="cancelar" class="boton" name="cancelar" value="Cancelar" onclick="cambiarContenidos('catalogos/lista_vehiculo.php', 'Vehículos');"/>
</form>