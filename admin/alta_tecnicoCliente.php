<?php
session_start();

if (!isset($_SESSION['user']) || $_SESSION['user'] == "") {
    header("Location: index.php");
}
include_once("../WEB-INF/Classes/TFSCliente.class.php");
include_once("../WEB-INF/Classes/Catalogo.class.php");
$pagina_lista = "admin/lista_tecnicoCliente.php";
$id = "";
$usuario = "";
$cliente = "";
$auxusuario = "";
$auxcliente = "";
$read = "";
$localidad = "";
//$clienteLocalidad = "jj";
if (isset($_POST['claveCliente'])) {
    // $clienteLocalidad = $_POST['claveCliente'];
    $auxcliente = $_POST['claveCliente'];
    $auxusuario = $_POST['usuario'];
    $usuario = $_POST['auxUsuario'];
    $cliente = $_POST['auxcliente'];
    $localidad = $_POST['auxlocalidad'];
}
//echo $auxcliente;
?>
<!DOCTYPE html>
<html lang="es">
    <head>
        <script type="text/javascript" language="javascript" src="resources/js/paginas/alta_tecnicoCliente.js"></script>        
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
                $localidad = $obj->getLocalidad();
            }
            ?>
            <form id="formTecnicoCliente" name="formTecnicoCliente" action="/" method="POST">
                <table style="width: 100%">
                    <tr>
                        <td><label for="usuario">T&eacute;cnico</label></td>
                        <td>
                            <select id="usuario" name="usuario" class="filtro" <?php echo $read; ?> style="width:200px">
                                <?php
                                $catalogo = new Catalogo();
                                $query = $catalogo->obtenerLista("SELECT u.IdUsuario,u.Nombre,u.ApellidoPaterno
                                                                        FROM c_usuario u
                                                                        WHERE u.IdPuesto='18' OR u.IdPuesto='20'");
                                echo "<option value='0' >Selecciona una opción</option>";
                                while ($rs = mysql_fetch_array($query)) {
                                    $s = "";
                                    if ($auxusuario == $rs['IdUsuario'] || $usuario == $rs['IdUsuario']) {
                                        $s = "selected";
                                    }
                                    echo "<option value=" . $rs['IdUsuario'] . " " . $s . ">" . $rs['Nombre'] . " " . $rs['ApellidoPaterno'] . "</option>";
                                }
                                ?>
                            </select>
                        </td> 
                    </tr>
                    <tr>
                        <td><label for="cliente">Cliente</label></td>
                        <td>
                            <select id="cliente" name="cliente" class="filtro" style="width:200px" onchange="verLocalidad('admin/alta_tecnicoCliente.php');">
                                <?php
                                $catalogo = new Catalogo();
                                $query = $catalogo->getListaAlta("c_cliente", "NombreRazonSocial");
                                echo "<option value='0' >Selecciona una opción</option>";
                                while ($rs = mysql_fetch_array($query)) {
                                    $s = "";
                                    if ($auxcliente == $rs['ClaveCliente'] || $cliente == $rs['ClaveCliente']) {
                                        $s = "selected";
                                    }
                                    echo "<option value=" . $rs['ClaveCliente'] . " " . $s . ">" . $rs['NombreRazonSocial'] . "</option>";
                                }
                                ?>
                            </select>
                        </td>                    
                    </tr>
                    <tr>
                        <td><label for="localidad">Localidad</label></td>
                        <td>
                            <select id="localidad" name="localidad" class="filtro" style="width:200px">
                                <option value="0">Selecccione una opción</option>
                                <?php
                                // if ($auxcliente != "" || $cliente != "") {
                                if ($auxcliente != "")
                                    $clienteConsulta = $auxcliente;
                                else if ($cliente != "")
                                    $clienteConsulta = $cliente;
                                $catalogo = new Catalogo();
                                $query = $catalogo->obtenerLista("SELECT  cc.ClaveCentroCosto,cc.ClaveCliente,cc.Nombre
                                                                        FROM c_centrocosto cc
                                                                        WHERE cc.ClaveCliente='" . $clienteConsulta . "'
                                                                        ORDER BY cc.Nombre ASC");
                                while ($rs = mysql_fetch_array($query)) {
                                    $s = "";
                                    if ($localidad != "" && $localidad == $rs['ClaveCentroCosto']) {
                                        $s = "selected";
                                    }
                                    echo "<option value=" . $rs['ClaveCentroCosto'] . " " . $s . ">" . $rs['Nombre'] . "</option>";
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
                       echo "<input type='hidden' id='tipo' name='tipo' value='2'/> ";
                       echo "<input type='hidden' id='idUsuario' name='idUsuario' value='" . $usuario . "'/> ";
                       echo "<input type='hidden' id='idCliente' name='idCliente' value='" . $cliente . "'/> ";
                       echo "<input type='hidden' id='idLocalidad' name='idLocalidad' value='" . $localidad . "'/> ";
                       ?>

            </form>
        </div>
    </body>
</html>
