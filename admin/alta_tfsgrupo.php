<?php
session_start();

if (!isset($_SESSION['user']) || $_SESSION['user'] == "") {
    header("Location: index.php");
}
include_once("../WEB-INF/Classes/TFSGrupoCliente.class.php");
include_once("../WEB-INF/Classes/Catalogo.class.php");

$pagina_lista = "admin/lista_tfsgrupo.php";

$IdTfs = '';
$ClaveGrupo = '';
$Activo = '';
$UsuarioCreacion = '';
$FechaCreacion = '';
$UsuarioUltimaModificacion = '';
$FechaUltimaModificacion = '';
$Pantalla = '';
$read = '';

?>
<!DOCTYPE html>
<html lang="es">
    <head>
        <script type="text/javascript" language="javascript" src="resources/js/paginas/alta_tfsgrupo.js"></script>
        <script>
            $(document).ready(function() {
                $('.boton').button().css('margin-top', '20px');
            });
        </script>
    </head>
    <body>
        <div class="principal">
            <?php
            $obj = new TFSGrupoCliente();
            if (isset($_POST['id']) && isset($_POST['id2'])) {
                $obj->getRegistroByIDs($_POST['id'], $_POST['id2']);
                $read = "disabled='disabled'";
                $IdTfs = $obj->getIdTfs();
                $ClaveGrupo = $obj->getClaveGrupo();
                $Activo = $obj->getActivo();
                $UsuarioCreacion = $obj->getUsuarioCreacion();
                $FechaCreacion = $obj->getFechaCreacion();
                $UsuarioUltimaModificacion = $obj->getUsuarioUltimaModificacion();
                $FechaUltimaModificacion = $obj->getFechaUltimaModificacion();
                $Pantalla = $obj->getPantalla();
            }
            ?>
            <form id="formTfsGrupo" name="formTfsGrupo" action="/" method="POST">
                <table>
                    <tr>
                        <td><label for="tfs">TFS</label>
                            <select id="tfs" name="tfs" <?php echo $read; ?>>
                                <?php
                                $catalogo = new Catalogo();
                                $query = $catalogo->obtenerLista("SELECT u.IdUsuario, CONCAT(u.Nombre,' ',u.ApellidoPaterno,' ',u.ApellidoMaterno) AS tfs FROM c_usuario u WHERE u.IdPuesto='21' ORDER BY tfs");
                                echo "<option value='0' >Selecciona una opción</option>";
                                while ($rs = mysql_fetch_array($query)) {
                                    $s = "";
                                    if ($IdTfs != "" && $IdTfs == $rs['IdUsuario']) {
                                        $s = "selected";
                                    }
                                    echo "<option value=" . $rs['IdUsuario'] . " " . $s . ">" . $rs['tfs'] . "</option>";
                                }
                                ?>
                            </select>
                        </td> 
                        <td></td>
                        <td><label for="grupo">Grupo de Clientes</label>
                            <select id="grupo" name="grupo" style="max-width: 300px">
                                <?php                               
                                $query = $catalogo->getListaAlta("c_clientegrupo", "Nombre");
                                echo "<option value='0' >Selecciona una opción</option>";
                                while ($rs = mysql_fetch_array($query)) {
                                    $s = "";
                                    if ($ClaveGrupo != "" && $ClaveGrupo == $rs['ClaveGrupo']) {
                                        $s = "selected";
                                    }
                                    echo "<option value=" . $rs['ClaveGrupo'] . " " . $s . ">" . $rs['Nombre'] . "</option>";
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
                echo "<input type='hidden' id='idUsuario' name='ClaveGrupoAnterior' value='" . $ClaveGrupo . "'/> ";
                echo "<input type='hidden' id='id_tfs' name='id_tfs' value='" . $IdTfs . "'/> ";
                ?>
            </form>
        </div>
    </body>
</html>
