<?php
session_start();

if (!isset($_SESSION['user']) || $_SESSION['user'] == "") {
    header("Location: index.php");
}
include_once("../WEB-INF/Classes/Catalogo.class.php");
include_once("../WEB-INF/Classes/Usuario.class.php");
$catalogo = new Catalogo();
$usuario = new Usuario();
$usuario->getRegistroById($_SESSION['idUsuario']);
$idAlmacen = "";
if ($usuario->getIdAlmacen() != null && $usuario->getIdAlmacen() != "") {
    $idAlmacen = $usuario->getIdAlmacen();
}
?>
<script type="text/javascript" language="javascript" src="resources/js/paginas/ventas/nueva_sol_equipo.js"></script>
<style>
    .size{width: 200px;}
</style>
<form id="solform">
    <table>
        <tr>
            <td>
                <label for="cliente">
                    Tipo de solicitud
                </label>
            </td>
            <td>
                <?php
                if (isset($_POST['id']) && $_POST['id'] != "") {
                    ?>
                    <select class="size" id="tipo_solicitud" name="tipo_solicitud" style="width: 600px" onchange="cambioTipoSolicitud();" disabled="disabled">
                        <?php
                        $query = $catalogo->obtenerLista("SELECT * FROM c_solicitud WHERE id_solicitud=" . $_POST['id']);
                        while ($rss = mysql_fetch_array($query)) {
                            $query = $catalogo->getListaAlta("c_tiposolicitud", "Nombre");
                            while ($rs = mysql_fetch_array($query)) {
                                if ($rs['IdTipoMovimiento'] == $rss['id_tiposolicitud']) {
                                    echo "<option value='" . $rs['IdTipoMovimiento'] . "' selected>" . $rs['Nombre'] . "</option>";
                                }/* else {
                                    echo "<option value='" . $rs['IdTipoMovimiento'] . "'>" . $rs['Nombre'] . "</option>";
                                }*/
                            }
                        }
                        ?>
                    </select>
                    <input type="hidden" id="IdSolicitud" name="IdSolicitud" value="<?php echo $_POST['id']; ?>"/>
                    <?php
                    echo "<script>setEdicion(1);setId_solicitud(".$_POST['id'].");cambioTipoSolicitud();</script>";
                } else {
                    ?>
                    <select class="size filtro" id="tipo_solicitud" name="tipo_solicitud" style="width: 600px" onchange="cambioTipoSolicitud();">
                        <option value="">Selecciona el tipo de solicitud</option>
                        <?php
                        $query = $catalogo->getListaAlta("c_tiposolicitud", "Nombre");
                        while ($rs = mysql_fetch_array($query)) {
                            echo "<option value='" . $rs['IdTipoMovimiento'] . "'>" . $rs['Nombre'] . "</option>";
                        }
                        ?>
                    </select>
                <?php } ?>
            </td>            
            <td></td>
        </tr>
    </table>

    <div id="cambiable_div">

    </div>
    <input type="submit" id="aceptar" class="boton" name="aceptar" value="Continuar" style="display: none;"/>
    <input type="button" id="cancelar" class="boton" name="cancelar" value="Cancelar" onclick="cambiarContenidos('ventas/list_sol_equipo.php', 'Solicitudes');"/>
</form>