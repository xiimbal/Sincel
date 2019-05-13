<?php
session_start();

if (!isset($_SESSION['user']) || $_SESSION['user'] == "") {
    header("Location: index.php");
}
include_once("../WEB-INF/Classes/TFSCliente.class.php");
include_once("../WEB-INF/Classes/Catalogo.class.php");
$pagina_lista = "admin/lista_ktfsCliente.php";
$id = "";
$usuario = "";
$cliente = "";
$read = "";
?>
<!DOCTYPE html>
<html lang="es">
    <head>
        <script type="text/javascript" language="javascript" src="resources/js/paginas/alta_tfsCliente.js"></script>        
    </head>
    <body>
        <div class="principal">
            <?php
            $obj = new TFSCliente();
            if (isset($_POST['id']) && isset($_POST['id2'])) {

                $obj->getRegistroById($_POST['id'], $_POST['id2']);
                $read = "disabled='disabled'";
                $usuario = $obj->getIdUsuario();
                $cliente = $obj->getClaveCliente();
            }
            ?>
            <form id="formTfsCliente" name="formTfsCliente" action="/" method="POST">
                <table >
                    <tr>
                        <td><label for="usuario">Usuario</label>
                            <select id="usuario" name="usuario" class="filtro" <?php echo $read; ?>>
                                <?php
                                $catalogo = new Catalogo();
                                $query = $catalogo->obtenerLista("SELECT u.IdUsuario,u.Nombre,u.ApellidoPaterno
                                                                        FROM c_usuario u
                                                                        WHERE u.IdPuesto='21' ORDER BY u.Nombre");
                                echo "<option value='0' >Selecciona una opción</option>";
                                while ($rs = mysql_fetch_array($query)) {
                                    $s = "";
                                    if ($usuario != "" && $usuario == $rs['IdUsuario']) {
                                        $s = "selected";
                                    }
                                    echo "<option value=" . $rs['IdUsuario'] . " " . $s . ">" . $rs['Nombre'] . " " . $rs['ApellidoPaterno'] . "</option>";
                                }
                                ?>
                            </select>
                        </td> 
                        <td>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</td>
                        <td><label for="cliente">Cliente</label>
                            <select id="cliente" name="cliente" class="filtro" style="max-width: 300px">
                                <?php
                                $catalogo = new Catalogo();
                                $query = $catalogo->getListaAlta("c_cliente", "NombreRazonSocial");
                                echo "<option value='0' >Selecciona una opción</option>";
                                while ($rs = mysql_fetch_array($query)) {
                                    $s = "";
                                    if ($cliente != "" && $cliente == $rs['ClaveCliente']) {
                                        $s = "selected";
                                    }
                                    echo "<option value=" . $rs['ClaveCliente'] . " " . $s . ">" . $rs['NombreRazonSocial'] . "</option>";
                                }
                                ?>
                            </select>
                        </td>                    
                    </tr>
                </table>
                <input type="submit" class="boton" value="Guardar" />
                <input type="submit" class="boton" value="Cancelar" onclick="cambiarContenidos('<?php echo $pagina_lista; ?>');
                return false;"/>
                       <?php
                       echo "<input type='hidden' id='tipo' name='tipo' value='1'/> ";
                       echo "<input type='hidden' id='idUsuario' name='idUsuario' value='" . $usuario . "'/> ";
                       echo "<input type='hidden' id='idCliente' name='idCliente' value='" . $cliente . "'/> ";
                       echo "<input type='hidden' id='idLocalidad' name='idLocalidad' value=''/> ";
                       ?>

            </form>
        </div>
    </body>
</html>
