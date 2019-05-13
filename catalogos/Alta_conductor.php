<?php
session_start();
if (!isset($_SESSION['user']) || $_SESSION['user'] == "") {
    header("Location: index.php");
}
include_once("../WEB-INF/Classes/Catalogo.class.php");
include_once("../WEB-INF/Classes/Conductor.class.php");
$catalogo = new Catalogo();
$nombre = "";
$appat = "";
$apmat = "";
$usuario = "";
$activo = "";
if (isset($_GET['id']) && $_GET['id']) {
    $conductor = new Conductor();
    $conductor->getRegistroById($_GET['id']);
    $nombre = $conductor->getNombre();
    $appat = $conductor->getApellidoPaterno();
    $apmat = $conductor->getApellidoMaterno();
    $usuario = $conductor->getIdUsuario();
    $activo = $conductor->getActivo();
}
?>
<script type="text/javascript" language="javascript" src="resources/js/paginas/catalogos/Alta_conductor.js"></script>
<form id="formconductor">
    <table style="width: 100%;">
        <tr>
            <td>Nombre</td>
            <td>
                <input type="text" name="nombre" id="nombre" value="<?php echo $nombre ?>"/>
            </td>            
            <td>Apellido Paterno</td>
            <td><input type="text" name="appat" id="appat" value="<?php echo $appat ?>"/></td>
            <td>Apellido Materno</td>
            <td><input type="text" name="apmat" id="apmat" value="<?php echo $apmat ?>"/></td>
        </tr>
        <tr>
            <td>Usuario</td>
            <td>
                <select name="usuario" id="usuario">
                    <option value="">Seleccione un usuario</option>
                    <?php
                    $catalogo = new Catalogo();
                    $consulta = "";
                    if ($usuario != "") {
                        $consulta = "SELECT c_usuario.IdUsuario AS IdUsuario,CONCAT(c_usuario.ApellidoPaterno,' ',c_usuario.ApellidoMaterno,' ',c_usuario.Nombre) AS Nombre FROM c_usuario "
                                . "LEFT JOIN c_conductor ON c_conductor.IdUsuario=c_usuario.IdUsuario WHERE c_usuario.Activo=1 AND (ISNULL(c_conductor.IdUsuario) OR IdConductor=" . $_GET['id'] . ") ORDER BY Nombre";
                    } else {
                        $consulta = "SELECT c_usuario.IdUsuario AS IdUsuario,CONCAT(c_usuario.ApellidoPaterno,' ',c_usuario.ApellidoMaterno,' ',c_usuario.Nombre) AS Nombre FROM c_usuario "
                                . "LEFT JOIN c_conductor ON c_conductor.IdUsuario=c_usuario.IdUsuario WHERE c_usuario.Activo=1 AND ISNULL(c_conductor.IdUsuario) ORDER BY Nombre";
                    }
                    $query = $catalogo->obtenerLista($consulta);
                    if ($usuario != "") {
                        while ($rs = mysql_fetch_array($query)) {
                            $s = "";
                            if ($usuario == $rs['IdUsuario'])
                                $s = "selected";
                            echo "<option value='" . $rs['IdUsuario'] . "' " . $s . ">" . $rs['Nombre'] . "</option>";
                        }
                    } else {
                        while ($rs = mysql_fetch_array($query)) {
                            echo "<option value='" . $rs['IdUsuario'] . "'>" . $rs['Nombre'] . "</option>";
                        }
                    }
                    ?></select></td>
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
            <td></td>
            <td></td>
        </tr>
    </table>
    <?php
    if (isset($_GET['id']) && $_GET['id']) {
        ?>
        <input type="hidden" name="id" id="id" value="<?php echo $_GET['id'] ?>"/>
<?php } ?>
    <br/><br/>
    <input type="submit" id="aceptar" class="boton" name="aceptar" value="Guardar"/>
    <input type="button" id="cancelar" class="boton" name="cancelar" value="Cancelar" onclick="cambiarContenidos('catalogos/lista_conductores.php', 'Conductores');"/>
</form>