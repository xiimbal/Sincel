<?php
session_start();

if (!isset($_SESSION['user']) || $_SESSION['user'] == "") {
    header("Location: ../../index.php");
}

include_once("../../WEB-INF/Classes/Contacto.class.php");
include_once("../../WEB-INF/Classes/Catalogo.class.php");
include_once("../../WEB-INF/Classes/PermisosSubMenu.class.php");    
$permisos_grid = new PermisosSubMenu();

$Nombre = "";
$telefono = "";
$celular = "";
$correo = "";
$tipo = "";
$Clave = "";
$nuevo = "";
$envia_factura = "";
$cliente_cobranza = "";
$activo = "checked='checked'";
$checked_cliente = "";
$checked_localidad = "checked='checked'";
$id = "";
$catalogo = new Catalogo();
$permiso_contactos_facturacion = $permisos_grid->tienePermisoEspecial($_SESSION['idUsuario'], 43);

if((isset($_POST['id']) && !is_null($_POST['id'])) || (isset($_GET['id']) && !is_null($_GET['id']))){    
    if(isset($_POST['id']) && !is_null($_POST['id'])){
        $id = $_POST['id'];
    }else{
        $id = $_GET['id'];
    }
    $obj = new Contacto();
    if($obj->getContactoByClave($id)){
        $Nombre = $obj->getNombre();
        $telefono = $obj->getTelefono();
        $celular = $obj->getCelular();
        $correo = $obj->getCorreoElectronico();
        $tipo = $obj->getIdTipoContacto();

        if($obj->getEnvioFactura() == "1"){
            $envia_factura = "checked='checked'";
        }
        
        if($obj->getContactoCobranza() == "1"){
            $cliente_cobranza = "checked='checked'";
        }
        
        if ($obj->getActivo() == "0") {
            $activo = "";
        }
        $Clave = $obj->getClaveEspecialContacto();  

        if($obj->getNivelContacto() == "2"){
            $checked_localidad = "";
            $checked_cliente = "checked='checked'";
        }    
    }
}

if(isset($_GET['Nuevo'])){
    $nuevo = "si";
}
$cliente = false;
$ClaveCliente;
if(isset($_GET['Cliente'])){
    $cliente = true;
    $ClaveCliente = $_GET['Cliente'];
}
?>
<!DOCTYPE html>
<html lang="es">
    <head>
        <title></title>
        <script type="text/javascript" language="javascript" src="../resources/js/paginas/alta_validacion.js"></script>
        <script type="text/javascript" language="javascript" src="../resources/js/paginas/validacion/alta_contacto.js"></script>
    </head>
    <body>        
        <fieldset>
            <legend>Contactos</legend>
            <form id="formContacto" name="formContacto" action="/" method="POST">
                <input type="hidden" name="clave_domicilio" id="clave_domicilio" value="<?php echo $_GET['id']; ?>" /> 
                <input type="hidden" name="nuevo_contacto" id="nuevo_contacto" value="<?php echo $nuevo; ?>" /> 
                <table style="width: 100%">                               
                    <tr>
                        <td><label for="nombre_contacto2">Nombre:</label></td>
                        <td><input type="text" id="nombre_contacto2" name="nombre_contacto2" class="complete" value="<?php echo $Nombre; ?>"/></td>                        
                        <td><label for="correo_contacto2">Correo electr&oacute;nico:</label></td>
                        <td><input type="email" id="correo_contacto2" name="correo_contacto2" class="complete" value="<?php echo $correo; ?>"/></td>
                    </tr>
                    <tr>
                        <td><label for="telefono_contacto2">Tel&eacute;fono:</label></td>
                        <td><input type="text" id="telefono_contacto2" name="telefono_contacto2" class="complete" value="<?php echo $telefono; ?>"/></td>
                        <td><label for="celular_contacto2">Celular:</label></td>
                        <td><input type="text" id="celular_contacto2" name="celular_contacto2" class="complete" value="<?php echo $celular; ?>"/></td>
                    </tr>
                    <tr>
                        <td><label for="tipo_contacto2">Tipo:</label></td>
                        <td>
                            <select id="tipo_contacto2" name="tipo_contacto2" style="width: 250px;">
                                <?php
                                $query = $catalogo->getListaAlta("c_tipocontacto", "Nombre");
                                echo "<option value='0' >Selecciona un tipo</option>";
                                while ($rs = mysql_fetch_array($query)) {
                                    if( ($rs['IdTipoContacto'] == "15" || $rs['IdTipoContacto'] == "13") && !$permiso_contactos_facturacion){
                                        continue;
                                    }
                                    
                                    $s = "";
                                    if ($tipo != "" && $tipo == $rs['IdTipoContacto']) {
                                        $s = "selected";
                                    }                                    
                                    echo "<option value=" . $rs['IdTipoContacto'] . " " . $s . ">" . $rs['Nombre'] . "</option>";
                                }
                                ?>
                            </select>
                        </td>
                        <td><label for="activo">Activo:</label></td>
                        <td><input type="checkbox" name="activo" id="activo" <?php echo $activo; ?>/>Activo</td>
                    </tr>
                    <tr>
                        <td>Enviar factura de localidad</td>
                        <td><input type="checkbox" id="envio_factura" name="envio_factura" <?php echo $envia_factura; ?>/></td>
                        <td>Contacto de cobranza</td>
                        <td><input type="checkbox" id="contacto_cobranza" name="contacto_cobranza" <?php echo $cliente_cobranza; ?>/></td>
                    </tr>
                    <tr>
                        <?php if($cliente){ ?>
                        <td>Asignado a: </td>
                        <td>
                            <select id="cliente" name="cliente" required="required">
                                <?php
                                    echo "<option value='' >Elija una opción</option>";
                                    $s = "";
                                    if($ClaveCliente == $Clave){
                                            $s = "selected = 'selected'"; 
                                        }
                                    echo "<option value='$ClaveCliente' $s > Cliente </option>";
                                    $resultCliente = $catalogo->obtenerLista("SELECT ClaveCentroCosto, Nombre FROM c_centrocosto WHERE ClaveCliente = '$ClaveCliente'");
                                    while($rsCliente = mysql_fetch_array($resultCliente)){
                                        $s = "";
                                        if($rsCliente['ClaveCentroCosto'] == $Clave){
                                            $s = "selected = 'selected'"; 
                                        }
                                        echo "<option value='".$rsCliente['ClaveCentroCosto']."' $s >".$rsCliente['Nombre']."</option>";
                                    }
                                ?>  
                            </select>
                        </td>
                        <?php } else{ ?>
                        <td><label for="nivel">Nivel:</label></td>
                        <td>
                            <input type="radio" id="nivel_localidad" name="nivel" value="1" <?php echo $checked_localidad; ?>/>Localidad
                            <input type="radio" id="nivel_cliente" name="nivel" value="2" <?php echo $checked_cliente; ?>/>Cliente
                        </td>
                        <?php } ?>
                    </tr>    
                </table>
                <?php if($cliente){ ?>
                <input type="submit" id="cancelar_contacto" class="boton" value="Cancelar" style="float: right; margin-right: 5px;" onclick='cambiarContenidosContacto("../cliente/editar_contacto.php?ClaveCliente=<?php echo $ClaveCliente; ?>", "Nuevo Contacto");return false;'/>
                <?php }else{?>
                <input type="submit" id="cancelar_contacto" class="boton" value="Cancelar" style="float: right; margin-right: 5px;" onclick="cambiarContenidoValidaciones('contacto2', '../cliente/validacion/lista_contacto.php',  <?php  if(isset($_POST['idTicket'])){ echo "'".$_POST['idTicket']."'";} else{ echo "null"; } ?>, '<?php echo $_GET['id']; ?>', null);return false;"/>
                <?php }
                if($permisos_grid->tienePermisoEspecial($_SESSION['idUsuario'],26) || empty($id)){  ?>
                    <input type="submit" class="boton" value="Guardar" style="float: right; margin-right: 5px;" />                
                <?php } ?>
                <input type="hidden" name="id" id="id" value="<?php echo $id; ?>"/>        
                <input type="hidden" name="independiente" id="independiente" value="true"/>
                <br/><br/><br/><br/>
            </form>
        </fieldset>        
    </body>
</html>