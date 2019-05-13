<?php
session_start();

if (!isset($_SESSION['user']) || $_SESSION['user'] == "") {
    header("Location: index.php");
}
include_once("../WEB-INF/Classes/PermisosMenu.class.php");
include_once("../WEB-INF/Classes/Catalogo.class.php");
$pagina_lista = "admin/lista_permisoMenu.php";
$id = "";
$id1 = "";
$idPuesto = "";
$idSubmenu = "";
$alta = "checked='checked'";
$baja = "checked='checked'";
$modificacion = "checked='checked'";
$consulta = "checked='checked'";
$read = "";
?>
<!DOCTYPE html>
<html lang="es">
    <head>
        <script type="text/javascript" language="javascript" src="resources/js/paginas/alta_permisosMenu.js"></script>
        <script>
            $(document).ready(function() {
                $('.boton').button().css('margin-top', '20px');
            });
        </script>
    </head>
    <body>
        <div class="principal">
            <?php
            $obj = new PermisosMemu();
            if (isset($_POST['id']) && isset($_POST['id2'])) {

                $obj->getRegistroById($_POST['id'], $_POST['id2']);
                $read = "disabled";
                $idPuesto = $obj->getIdPuesto();
                $idSubmenu = $obj->getIdSubmenu();
                if ($obj->getAlta() == "0")
                    $alta = "";
                if ($obj->getBaja() == "0")
                    $baja = "";
                if ($obj->getModificacion() == "0")
                    $modificacion = "";
                if ($obj->getConsulta() == "0")
                    $consulta = "";
            }
            ?>
            <form id="formPermisoMenu" name="formPermisoMenu" action="/" method="POST">
                <table>
                    <tr>
                        <td><label for="puesto">Puesto</label><span class="obligatorio"> *</span></td>
                        <td>
                            <select id="puesto" name="puesto">
                                <?php
                                $catalogo = new Catalogo();
                                if ($idPuesto != "") {
                                     $query = $catalogo->obtenerLista("SELECT * FROM c_puesto WHERE IdPuesto='".$idPuesto."' ORDER BY Nombre;");                                     
                                     while ($rs = mysql_fetch_array($query)) {
                                        echo "<option value=" . $rs['IdPuesto'] . " " . $s . ">" . $rs['Nombre'] . "</option>";
                                    }
                                } else {
                                    $query = $catalogo->getListaAltaTodo("c_puesto","Nombre");
                                    echo "<option value='0' >Selecciona una opción</option>";
                                    while ($rs = mysql_fetch_array($query)) {
                                        $s = "";
                                        if ($idPuesto != "" && $idPuesto == $rs['IdPuesto']) {
                                            $s = "selected";
                                        }
                                        echo "<option value=" . $rs['IdPuesto'] . " " . $s . ">" . $rs['Nombre'] . "</option>";
                                    }
                                }
                                ?>
                            </select>
                        </td>            
                        <td><label for="submenu">Submemu</label><span class="obligatorio"> *</span></td>
                        <td>
                            <select id="submenu" name="submenu">
                                <?php
                                $catalogo = new Catalogo();
                                $query = $catalogo->obtenerLista("SELECT * FROM m_submenu sm ORDER BY sm.nom_sub");
                                echo "<option value='0' >Selecciona una opción</option>";
                                while ($rs = mysql_fetch_array($query)) {
                                    $s = "";
                                    if ($idSubmenu != "" && $idSubmenu == $rs['id_sub']) {
                                        $s = "selected";
                                    }
                                    echo "<option value=" . $rs['id_sub'] . " " . $s . ">" . $rs['nom_sub'] ." (".$rs['descripcion']. ")</option>";
                                }
                                ?>
                            </select>
                        </td>                    
                    </tr>
                    <tr>
                        <td></td><td><input type="checkbox" name="alta" id="alta" <?php echo $alta; ?>/>Alta</td>
                        <td></td><td><input type="checkbox" name="baja" id="baja" <?php echo $baja; ?>/>Baja</td>
                    </tr>
                    <tr>
                        <td></td><td><input type="checkbox" name="modificacion" id="modificacion" <?php echo $modificacion; ?>/>Modificacion</td>
                        <td></td><td><input type="checkbox" name="consulta" id="consulta" <?php echo $consulta; ?>/>Consulta</td>
                    </tr>
                </table>
                <br/>
                <div id="permisos_especiales"></div>
                <input type="submit" class="boton" value="Guardar" />
                <input type="submit" class="boton" value="Cancelar" onclick="cambiarContenidos('<?php echo $pagina_lista; ?>'); return false;"/>
                <?php
                if (isset($_POST['id2']) && isset($_POST['id'])) {
                    $id1 = $_POST['id2'];
                    $id = $_POST['id'];
                }
                echo "<input type='hidden' id='idP' name='idP' value='" . $id . "'/> ";
                echo "<input type='hidden' id='idS' name='idS' value='" . $id1 . "'/> ";
                ?>

            </form>
        </div>
    </body>
</html>
