<?php
session_start();

if (!isset($_SESSION['user']) || $_SESSION['user'] == "") {
    header("Location: ../index.php");
}
include_once("../WEB-INF/Classes/GrupoEmpresas.class.php");
include_once("../WEB-INF/Classes/Catalogo.class.php");
$grupo = new GrupoEmpresas();
if (isset($_GET['id']) && $_GET['id'] != "") {
    $grupo->getregistrobyID($_GET['id']);
}
?>
<script type="text/javascript" language="javascript" src="resources/js/paginas/Multi/Editar_GrupoEmpresa.js"></script>
<form id="formcliente">
    <table style=" width:100%">
        <tr>
            <td>
                <table style=" width:100%">
                    <tr>
                        <td>
                            <table style=" width:100%">
                                <tr>
                                    <td style=" width:140px"> Nombre:</td>
                                    <td><input name="Descripcion" type="text" maxlength="150" id="Descripcion" value="<?php
                                        if ($grupo->getDescripcion() != "") {
                                            echo $grupo->getDescripcion();
                                        }
                                        ?>" style="width:200px;" /> <span id="MainContent_reqValRazonSocial" style="display:none;"></span></td>
                                </tr>
                                <tr>
                                    <td>Empresas:</td>
                                    <td ><select name="empresa" id="empresa" multiple="multiple">
                                            <?php
                                            $catalogo = new Catalogo();
                                            $query = $catalogo->obtenerLista("SELECT e.idEmpresa,e.RazonSocial FROM c_empresa AS e
ORDER BY RazonSocial DESC");
                                            if ($grupo->getEmpresas() != NULL) {
                                                $empresas = $grupo->getEmpresas();
                                                $i = 0;
                                                while ($rs = mysql_fetch_array($query)) {
                                                    if ($empresas[$i] == $rs['idEmpresa']) {
                                                        if ($i < count($empresas))
                                                            $i++;
                                                        echo "<option value=\"" . $rs['idEmpresa'] . "\" selected>" . $rs['RazonSocial'] . "</option>";
                                                    } else {
                                                        echo "<option value=\"" . $rs['idEmpresa'] . "\">" . $rs['RazonSocial'] . "</option>";
                                                    }
                                                }
                                            } else {
                                                while ($rs = mysql_fetch_array($query)) {
                                                    echo "<option value=\"" . $rs['idEmpresa'] . "\">" . $rs['RazonSocial'] . "</option>";
                                                }
                                            }
                                            ?>

                                        </select>  <span id="MainContent_reqValTipoCliente" style="display:none;"></span></td>
                                </tr>
                            </table>
                    </tr>
                </table>
                <br />

                <?php
                if (isset($_GET['id']) && $_GET['id'] != "") {
                    ?>
                    <input type="hidden" value="<?php echo $_GET['id']; ?>" id="id" name="id"/>
                    <?php
                }
                ?>
                <table style=" width:100%; text-align:center">
                    <tr>
                        <td>
                            <input type="submit" class="boton" name="Guardar" value="Guardar"  id="Guardar" />
                        </td>
                        <td>
                            <input type="button" onclick="cambiarContenidos('Multi/list_grupo_empresas.php', 'Grupo - Empresas');
                                    return false;" class="boton" name="Cancelar" value="Cancelar" id="Cancelar" />
                        </td>
                    </tr>
                </table>
    </table>
</form>