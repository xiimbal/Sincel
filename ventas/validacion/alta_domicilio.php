<?php
session_start();

if (!isset($_SESSION['user']) || $_SESSION['user'] == "") {
    header("Location: ../../index.php");
}

include_once("../../WEB-INF/Classes/Localidad.class.php");
include_once("../../WEB-INF/Classes/Catalogo.class.php");
include_once("../../WEB-INF/Classes/PermisosSubMenu.class.php");    
$permisos_grid = new PermisosSubMenu();
$back = "";
$id = "";
$Calle = "";
$NoExterior = "";
$NoInterior = "";
$Colonia = "";
$Ciudad = "";
$Estado = "";
$Delegacion = "";        
$CodigoPostal = "";
$catalogo = new Catalogo();

if(isset($_POST['id']) && !is_null($_POST['id'])){    
    $localidad = new Localidad();
    $id = $_POST['id'];        
    /*Obtenemos los datos de la localidad*/    
    if($localidad->getLocalidadById($id)){         
        $Calle = $localidad->getCalle();
        $NoExterior = $localidad->getNoExterior();
        $NoInterior = $localidad->getNoInterior();
        $Colonia = $localidad->getColonia();
        $Ciudad = $localidad->getCiudad();
        $Estado = $localidad->getEstado();
        $Delegacion = $localidad->getDelegacion();        
        $CodigoPostal = $localidad->getCodigoPostal();               
    } 
}

if (isset($_GET['id'])){    
    $ClaveGET = $_GET['id'];
}


if(isset($_GET['Nuevo'])){
    $nuevo = "si";
}else{
    $nuevo = "";
}
?>
<!DOCTYPE html>
<html lang="es">
    <head>
        <title></title>
        <script type="text/javascript" language="javascript" src="<?php echo $back; ?>resources/js/paginas/alta_validacion.js"></script>
        <script type="text/javascript" language="javascript" src="<?php echo $back; ?>resources/js/paginas/validacion/alta_domicilio.js"></script>
    </head>
    <body>        
        <fieldset>
            <legend>Domicilio de localidad</legend>
            <form id="formDomicilio" name="formDomicilio" action="/" method="POST">
                <input type="hidden" id="clave_2" name="clave_2" value="<?php echo $ClaveGET; ?>"/>
                <input type="hidden" id="clave_cliente2_domicilio" name="clave_cliente2_domicilio" value="<?php echo $_GET['idCliente'] ?>"/>
                <input type="hidden" id="nuevo_domicilio" name="nuevo_domicilio" value="<?php echo $nuevo; ?>"/>
                <table style="width: 100%">                    
                    <tr>
                        <td><label for='calle2'>Calle:</label></td>
                        <td colspan="3">
                            <textarea id="calle2" name="calle2" class="complete"><?php echo $Calle; ?></textarea>
                        </td>
                    </tr>
                    <tr>
                        <td><label for='exterior2'>Exterior:</label></td>
                        <td><input type='text' id='exterior2' name='exterior2' value="<?php echo $NoExterior; ?>"/></td>
                        <td><label for='interior2'>Interior:</label></td>
                        <td><input type='text' id='interior2' name='interior2' value="<?php echo $NoInterior; ?>"/></td>
                    </tr>
                    <tr>
                        <td><label for='colonia2'>Colonia:</label></td>
                        <td colspan="3"><input type='text' id='colonia2' name='colonia2' class="complete" value="<?php echo $Colonia; ?>"/></td>
                    </tr>
                    <tr>
                        <td><label for='delegacion2'>Delegaci&oacute;n o Municipio:</label></td>
                        <td colspan="3"><input type='text' id='delegacion2' name='delegacion2' class="complete" value="<?php echo $Delegacion; ?>"/></td>
                    </tr>
                    <tr>
                        <td><label for='ciudad2'>Ciudad:</label></td>
                        <td colspan="3"><input type='text' id='ciudad2' name='ciudad2' class="complete" value="<?php echo $Ciudad; ?>"/></td>
                    </tr>
                    <tr>
                        <td><label for='estado2'>Estado:</label></td>
                        <td colspan="3">
                            <?php
                                $result = $catalogo->getListaAltaTodo("c_ciudades", "Ciudad");
                                echo "<select id='estado2' name='estado2' class='complete'>";
                                while($rs = mysql_fetch_array($result)){
                                    $s = "";
                                    if($rs['Ciudad'] == $Estado){
                                        $s = "selected = 'selected'";
                                    }
                                    echo "<option value='".$rs['Ciudad']."' $s>".$rs['Ciudad']."</option>";
                                }
                                echo "</select>";
                            ?>
                            <!--<input type='text' id='estado2' name='estado2' class="complete" value="<?php //echo $Estado; ?>"/>-->
                        </td>
                    </tr>
                    <tr>
                        <td><label for='cp2'>CP:</label></td>
                        <td colspan="3"><input type='text' id='cp2' name='cp2' class="complete" value="<?php echo $CodigoPostal; ?>"/></td>
                    </tr>                
                </table>
                <input type="submit" id="cancelar_domicilio" class="boton" value="Cancelar" style="float: right; margin-right: 5px;" onclick="cambiarContenidoValidaciones('domicilio2', '<?php echo $back; ?>ventas/validacion/lista_domicilio.php?idCliente=<?php echo $_GET['idCliente'] ?>',  <?php  if(isset($_POST['idTicket'])){ echo "'".$_POST['idTicket']."'";} else{ echo "null"; } ?>, '<?php echo $ClaveGET; ?>' ,null);return false;"/>
                <?php if($permisos_grid->tienePermisoEspecial($_SESSION['idUsuario'],16) || empty($id)){  ?>
                    <input type="submit" class="boton" value="Guardar" style="float: right; margin-right: 5px;" />                
                <?php } ?>
                <input type="hidden" name="id" id="id" value="<?php echo $id; ?>"/>
                
                <br/><br/><br/><br/>
            </form>
        </fieldset>        
    </body>
</html>