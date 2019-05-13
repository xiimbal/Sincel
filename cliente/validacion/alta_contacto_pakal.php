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
if(isset($_GET['idTicket'])){
    $idTicket = true;
    $idTicket = $_GET['idTicket'];
}

?>
<!DOCTYPE html>
<html lang="es">
    <head>
        <title></title>
        <script type="text/javascript" language="javascript" src="../resources/js/paginas/alta_validacion_pakal.js"></script>
        <script type="text/javascript" language="javascript" src="../resources/js/paginas/validacion/alta_contacto_pakal.js"></script>
    </head>
    <body>        
            <h2 class="text-dark">Chofer</h2>
            <form id="formContacto" name="formContacto" action="/" method="POST">
                <input type="hidden" name="clave_domicilio" id="clave_domicilio" value="<?php echo $_GET['id']; ?>" /> 
                <input type="hidden" name="nuevo_contacto" id="nuevo_contacto" value="<?php echo $nuevo; ?>" /> 
                <div class="form-row">
                    <div class="form-group col-md-3">
                        <label class="text-dark" for="nombre_contacto2">Nombre:</label>
                        <input class="form-control" type="text" id="nombre_contacto2" name="nombre_contacto2" class="complete" value="<?php echo $Nombre; ?>"/>
                    </div>
                    <div class="form-group col-md-3">
                         <label class="text-dark" for="telefono_contacto2">Tel&eacute;fono:</label>
                         <input class="form-control" type="text" id="telefono_contacto2" name="telefono_contacto2" class="complete" value="<?php echo $telefono; ?>"/>
                    </div>
                    <div class="form-group col-md-3">
                        <label class="text-dark" for="correo_contacto2">Correo electr&oacute;nico:</label>
                        <input class="form-control" type="email" id="correo_contacto2" name="correo_contacto2" class="complete" value="<?php echo $correo; ?>"/>
                    </div>
                    <div class="form-group col-md-2">
                        <label class="text-dark" for="celular_contacto2">Celular:</label>
                        <input class="form-control" type="text" id="celular_contacto2" name="celular_contacto2" class="complete" value="<?php echo $celular; ?>"/>
                    </div>
                    <div class="form-group col-md-1">
                        <input type="checkbox" name="activo" id="activo" <?php echo $activo; ?>/>
                        <label class="text-dark" for="activo">Activo:</label>
                    </div>
                </div>
                <div class="form-row">
                    <?php if($permisos_grid->tienePermisoEspecial($_SESSION['idUsuario'],26) || empty($id)){  ?>
                        <div class="form-group col-md-2">
                            <input type="submit" class="btn btn-success btn-block" value="Guardar" />  
                            <input type="hidden" name="id" id="id" value="<?php echo $id; ?>"/>        
                            <input type="hidden" name="independiente" id="independiente" value="true"/>
                        </div>
                    <?php } ?>
                    <?php if($cliente){ ?>
                        <div class="form-group col-md-2">
                            <input type="submit" id="cancelar_contacto" class="btn btn-danger btn-block" value="Cancelar"  onclick='cambiarContenidosContacto("../compras/crearCamion.php?idTicket=<?php echo $idTicket; ?>");return false;'/>
                        </div>
                    <?php }else{?>
                        <div class="form-group col-md-2">
                            <input type="submit" id="cancelar_contacto btn-block" class="boton" value="Cancelar" style="float: right; margin-right: 5px;" onclick="cambiarContenidoValidaciones('contacto2', '../compras/crearCamion.php',  <?php  if(isset($_POST['idTicket'])){ echo "'".$_POST['idTicket']."'";} else{ echo "null"; } ?>, '<?php echo $_GET['id']; ?>', null);return false;"/>
                        </div>
                    <?php }?>
                </div>
            </form>     
    </body>
</html>