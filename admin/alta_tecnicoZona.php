<?php
session_start();

if (!isset($_SESSION['user']) || $_SESSION['user'] == "") {
    header("Location: index.php");
}
include_once("../WEB-INF/Classes/TecnicoZona.class.php");
include_once("../WEB-INF/Classes/Catalogo.class.php");
$pagina_lista = "admin/lista_tecnicoZona.php";
$usuario = "";
$gzona = "";
$read = "";
$zona = "";
$activo = "checked='checked'";
$accion = "";
if (isset($_POST['gzona'])) {
    $gzona = $_POST['gzona'];
    $usuario = $_POST['usuario'];
    $accion = $_POST["accion"];
    $zona=$_POST['zona'];
}
?>
<!DOCTYPE html>
<html lang="es">
    <head>
        <script type="text/javascript" language="javascript" src="resources/js/paginas/alta_tecnicoZona.js"></script>
        <script>
            $(document).ready(function() {
                $('.boton').button().css('margin-top', '20px');
            });
        </script>
    </head>
    <body>
        <div class="principal">
            <?php
            $obj = new TecnicoZona();
            if (isset($_POST['id']) && isset($_POST['id2'])) {

                $obj->getRegistroById($_POST['id'], $_POST['id2']);
                $read = "disabled='disabled'";
                $usuario = $obj->getIdUsuario();
                $gzona = $obj->getGZona();
                $zona = $obj->getClaveZona();
                if ($obj->getActivo() == "0") {
                    $activo = "";
                }
                $accion = "editar";
            }
            ?>
            <form id="formTecnicoZona" name="formTecnicoZona" action="/" method="POST">
                <table style="width: 100%">
                    <tr>
                        <td><label for="usuario">T&eacute;cnico</label></td>
                        <td>
                            <select id="usuario" name="usuario" <?php echo $read; ?> style="width:200px">
                                <?php
                                $catalogo = new Catalogo();
                                $query = $catalogo->obtenerLista("SELECT u.IdUsuario,u.Nombre,u.ApellidoPaterno
                                                                        FROM c_usuario u
                                                                        WHERE u.IdPuesto='18' OR u.IdPuesto='20'");
                                echo "<option value='0' >Selecciona una opción</option>";
                                while ($rs = mysql_fetch_array($query)) {
                                    $s = "";
                                    if ($usuario == $rs['IdUsuario']) {
                                        $s = "selected";
                                    }
                                    echo "<option value=" . $rs['IdUsuario'] . " " . $s . ">" . $rs['Nombre'] . " " . $rs['ApellidoPaterno'] . "</option>";
                                }
                                ?>
                            </select>
                        </td> 
                    </tr>
                    <tr>
                        <td><label for="gzona">Grupo zona</label></td>
                        <td>
                            <select id="gzona" name="gzona" style="width:200px" onchange="verListaOpc('admin/alta_tecnicoZona.php');">
                                <?php
                                $catalogo = new Catalogo();
                                $query = $catalogo->getListaAlta("c_gzona", "nombre");
                                echo "<option value='0' >Selecciona una opción</option>";
                                while ($rs = mysql_fetch_array($query)) {
                                    $s = "";
                                    if ($gzona == $rs['id_gzona']) {
                                        $s = "selected";
                                    }
                                    echo "<option value=" . $rs['id_gzona'] . " " . $s . ">" . $rs['nombre'] . "</option>";
                                }
                                ?>
                            </select>
                        </td>                    
                    </tr>
                    <tr>
                        <td><label for="zona">Zona</label></td>
                        <td>
                            <select id="zona" name="zona" style="width:200px">
                                <option value="0">Selecccione una opción</option>
                                <?php
                                $catalogo = new Catalogo();
                                $query = $catalogo->obtenerLista("SELECT * FROM c_zona WHERE fk_id_gzona='" . $gzona . "'");
                                while ($rs = mysql_fetch_array($query)) {
                                    $s = "";
                                    if ($zona == $rs['ClaveZona']) {
                                        $s = "selected";
                                    }
                                    echo "<option value=" . $rs['ClaveZona'] . " " . $s . ">" . $rs['NombreZona'] . "</option>";
                                }
                                ?>
                            </select>
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <input type='checkbox' id='acitvo' name='activo' <?php echo $activo ?>/> Activo
                        </td>
                    </tr>
                </table>
                <input type = "submit" class = "boton" value = "Guardar" />
                <input type = "submit" class = "boton" value = "Cancelar" onclick = "cambiarContenidos('<?php echo $pagina_lista; ?>');
                return false;"/>
                <input type='hidden' id='id' name='id' value='<?php echo $usuario ?>'/>
                <input type='hidden' id='id2' name='id2' value='<?php echo $zona ?>'/>
                <input type='hidden' id='accion' name='accion' value='<?php echo $accion ?>'/>
            </form>
        </div>
    </body>
</html>
