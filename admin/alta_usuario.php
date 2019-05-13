<?php
session_start();

if (!isset($_SESSION['user']) || $_SESSION['user'] == "") {
    header("Location: index.php");
}
include_once("../WEB-INF/Classes/Usuario.class.php");
include_once("../WEB-INF/Classes/Catalogo.class.php");
$pagina_lista = "admin/lista_usuario.php";

$id = "";
$usuario = "";
$nombre = "";
$app = "";
$apm = "";
$correo = "";
$password = "";
$puesto = "";
$rfc = "";
$activo = "checked='checked'";
$mensajero = '';
$idAlmacen = "";
$idUsuarioMDB = "";
$negocios = array();
$proveedorFactura = "";
?>
<!DOCTYPE html>
<html lang="es">
    <head>
        <script type="text/javascript" language="javascript" src="resources/js/paginas/alta_usuario.js"></script>        
    </head>
    <body>
        <div class="principal">
            <?php
            if (isset($_POST['id'])) {
                $obj = new Usuario();
                $obj->getRegistroById($_POST['id']);
                $id = $obj->getId();
                $usuario = $obj->getUsuario();
                $nombre = $obj->getNombre();
                $app = $obj->getPaterno();
                $apm = $obj->getMaterno();
                $correo = $obj->getEmail();
                $password = $obj->getPassword();
                $puesto = $obj->getPuesto();
                $idAlmacen = $obj->getIdAlmacen();
                $rfc = $obj->getRFC();
                $idUsuarioMDB = $obj->getIdUsuarioMultiBD();
                if ($obj->getActivo() == "0") {
                    $activo = "";
                }
                if ($obj->isMensajeroConductor()) {
                    $mensajero = "checked='checked'";
                }
                $negocios = $obj->obtenerNegociosDeUsuario();
                $proveedorFactura = $obj->getProveedorFactura();
            }
            ?>

            <form id="formUsuario" name="formUsuario" action="/" method="POST">
                <table style="width: 95%;">
                    <tr>
                        <td><label for="usuario">Usuario</label><span class="obligatorio"> *</span></td>
                        <td><input type="text" id="usuario" name="usuario" value="<?php echo $usuario; ?>"/></td>
                        <td><label for="nombre">Nombre(s)</label><span class="obligatorio"> *</span></td>
                        <td><input type="text" id="nombre" name="nombre" value="<?php echo $nombre; ?>"/></td>                        
                        <td><label for="puesto">Puesto</label></td>
                        <td>
                            <select id="puesto" name="puesto">
                                <?php
                                $catalogo = new Catalogo();
                                $query = $catalogo->getListaAlta("c_puesto", "Nombre");
                                echo "<option value='0' >Selecciona una opción</option>";
                                while ($rs = mysql_fetch_array($query)) {
                                    $s = "";
                                    if ($puesto != "" && $puesto == $rs['IdPuesto']) {
                                        $s = "selected";
                                    }
                                    echo "<option value=" . $rs['IdPuesto'] . " " . $s . ">" . $rs['Nombre'] . "</option>";
                                }
                                ?>
                            </select>
                        </td>
                    </tr>
                    <tr>
                        <td><label for="paterno">Apellido paterno</label><span class="obligatorio"> *</span></td>
                        <td><input type="text" id="paterno" name="paterno" value="<?php echo $app; ?>"/></td>
                        <td><label for="materno">Apellido materno</label></td>
                        <td><input type="text" id="materno" name="materno" value="<?php echo $apm; ?>"/></td>
                        <td><label for="correo">Correo electr&oacute;nico</label><span class="obligatorio"> *</span></td>
                        <td><input type="text" id="correo" name="correo" value="<?php echo $correo; ?>"/></td>
                    </tr>
                    <tr>
                        <td><label for="rfc">RFC</label></td>
                        <td><input type="text" id="rfc" name="rfc" value="<?php echo $rfc; ?>"/></td>                        
                        <td><label for="pass1">Contrase&ntilde;a</label><span class="obligatorio"> *</span></td>
                        <td><input type="password" id="pass1" name="pass1" value="<?php echo $password; ?>"/></td>
                        <td><label for="pass1">Repite la contrase&ntilde;a</label><span class="obligatorio"> *</span></td>
                        <td><input type="password" id="pass2" name="pass2" value="<?php echo $password; ?>"/></td>                        
                    </tr>
                    <tr>
                        
                    </tr>
                    <tr>
                        <td>
                            <?php
                            if (isset($_POST['id'])) {
                                echo '<input type="checkbox" name="cambiar" id="cambiar" onchange="activarDesactivarPassword(\'cambiar\');"/>Cambiar contraseña';
                            }
                            ?>
                        </td>                        
                        <td>
                            <input type="checkbox" name="activo" id="activo" <?php echo $activo; ?>/>Activo                            
                            <input type="checkbox" id="mensajero" name="mensajero" value="Si" <?php echo $mensajero; ?>/>Mensajero
                        </td>
                        <td></td>
                        <td></td>
                    </tr>
                    <tr>
                        <td><label for="almacen">Almacén</label></td>
                        <td><select id="almacen" name="almacen" style="width: 180px;">
                                <option value="0">Seleccione un almacén</option>
                                <?php
                                $catalogo1 = new Catalogo();
                                $query1 = $catalogo1->getListaAlta("c_almacen", "nombre_almacen");
                                while ($rs = mysql_fetch_array($query1)) {
                                    $s = "";
                                    if ($idAlmacen != "" && $idAlmacen == $rs['id_almacen']) {
                                        $s = "selected";
                                    }
                                    echo "<option value=" . $rs['id_almacen'] . " " . $s . ">" . $rs['nombre_almacen'] . "</option>";
                                }
                                ?>
                            </select>
                        </td>
                        <td><label for="negocios">Negocios</label></td>
                        <td>
                            <select id="negocios" name="negocios[]" class="multiselect" multiple="multiple" style="width: 180px;">                                
                                <?php
                                $catalogo1 = new Catalogo();
                                $query1 = $catalogo1->getListaAlta("c_cliente", "NombreRazonSocial");
                                while ($rs = mysql_fetch_array($query1)) {  
                                    $s = "";
                                    if(in_array($rs['ClaveCliente'], $negocios)){
                                        $s = "selected='selected'";
                                    }
                                    echo "<option value=" . $rs['ClaveCliente'] . " " . $s . ">" . $rs['NombreRazonSocial'] . "</option>";
                                }
                                ?>
                            </select>
                        </td>
                        <td><label for="proveedorF">Proveedor que factura</label></td> 
                        <td>
                            <select id="proveedorF" name="proveedorF" class="filtro">
                                <option value="">Selecciona proveedor</option>
                                <?php
                                $query2 = "SELECT ClaveProveedor, NombreComercial FROM c_proveedor";
                                $result = $catalogo->obtenerLista($query2);
                                while($rs = mysql_fetch_array($result)){
                                    $s = "";
                                    if(!empty($proveedorFactura) && $proveedorFactura == $rs['ClaveProveedor']){
                                        $s = "selected";
                                    }
                                    echo "<option value='" . $rs['ClaveProveedor'] . "' $s>" . $rs['NombreComercial'] . "</option>";
                                }
                                ?>
                            </select>
                        </td> 
                    </tr>
                </table>
                <br/><br/>
                <input type="submit" name="submit" class="boton" value="Guardar" />                
                <input type="submit" class="boton" value="Cancelar" onclick="cambiarContenidos('<?php echo $pagina_lista; ?>');
                return false;"/>                            
                       <?php
                       echo "<input type='hidden' id='id' name='id' value='$id'/> ";
                       echo "<input type='hidden' id='idUsuarioMBD' name='idUsuarioMBD' value='$idUsuarioMDB'/> ";
                       ?>
            </form>
        </div>
    </body>
</html>