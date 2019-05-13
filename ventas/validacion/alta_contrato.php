<?php
session_start();

if (!isset($_SESSION['user']) || $_SESSION['user'] == "") {
    header("Location: ../../index.php");
}
include_once("../../WEB-INF/Classes/Catalogo.class.php");
include_once("../../WEB-INF/Classes/Contrato.class.php");
include_once("../../WEB-INF/Classes/Localidad.class.php");
include_once("../../WEB-INF/Classes/PermisosSubMenu.class.php");    
$permisos_grid = new PermisosSubMenu();
$id = "";
$fechaInicio = "";
$fechaFin = "";
$fechaFirma = "";
$disabled = "";
$Calle = "";
$NoExterior = "";
$NoInterior = "";
$Colonia = "";
$Estado = "";
$Delegacion = "";
$CP = "";
$Activo = "1";
$FormaPago = "";
$DomicilioFiscal = "";
$RazonSocial = "";
$numero_cuenta = "";
$dias_credito = "";
if (isset($_POST['id']) && !is_null($_POST['id'])) {
    $equipo = new Contrato();
    $equipo->getRegistroById($_POST['id']);
    $id = $_POST['id'];
    $fechaInicio = $equipo->getFechaInicio();
    $fechaFin = $equipo->getFechaTermino();
    $fechaFirma = $equipo->getFechaFirma();
    $Clave = $equipo->getClaveCliente();
    $FormaPago = $equipo->getFormaPago();
    $localidad = new Localidad();
    $DomicilioFiscal = $localidad->getLocalidadByClaveTipo($equipo->getClaveCliente(), 3);
    $RazonSocial = $equipo->getRazonSocial();
    $numero_cuenta = $equipo->getNumeroCuenta();
    $dias_credito = $equipo->getDiasCredito();
    if ($DomicilioFiscal != "") {
        $localidad = new Localidad();
        $DomicilioFiscal = $localidad->getLocalidadByClaveTipo($equipo->getClaveCliente(), 3);
        $Calle = $localidad->getCalle();
        $NoExterior = $localidad->getNoExterior();
        $NoInterior = $localidad->getNoInterior();
        $Colonia = $localidad->getColonia();
        $Estado = $localidad->getEstado();
        $Delegacion = $localidad->getDelegacion();
        $CP = $localidad->getCodigoPostal();
        $Activo = $localidad->getActivo();
    }
    $disabled = "readonly='readonly'";
}

if (isset($_GET['idCliente'])) {
    $ClaveGET = $_GET['idCliente'];
    if (isset($_GET['Nuevo'])) {
        $Clave = $ClaveGET;
        $disabled = "";
    }    
    $DomicilioFiscal = $localidad->getLocalidadByClaveTipo($ClaveGET, 3);    
    //echo $ClaveGET." - $DomicilioFiscal";
    if ($DomicilioFiscal != "") {
        $localidad = new Localidad();
        $DomicilioFiscal = $localidad->getLocalidadByClaveTipo($ClaveGET, 3);
        $Calle = $localidad->getCalle();
        $NoExterior = $localidad->getNoExterior();
        $NoInterior = $localidad->getNoInterior();
        $Colonia = $localidad->getColonia();
        $Estado = $localidad->getEstado();
        $Delegacion = $localidad->getDelegacion();
        $CP = $localidad->getCodigoPostal();
        $Activo = $localidad->getActivo();
    }
}

?>
<!DOCTYPE html>
<html lang="es">
    <head>
        <title></title>
        <script type="text/javascript" language="javascript" src="<?php echo $back; ?>resources/js/paginas/alta_validacion.js"></script>
        <script type="text/javascript" language="javascript" src="<?php echo $back; ?>resources/js/paginas/validacion/alta_contrato.js"></script>
    </head>
    <body>        
        <fieldset>
            <legend>Contrato</legend>
            <form id="formContrato" name="formContrato" action="/" method="POST" >
                <input type="hidden" id="clave_contrato_cliente" name="clave_contrato_cliente" value="<?php echo $_GET['idCliente']; ?>"/>
                <table style="width: 100%">       
                    <tr>
                        <td><label for="fecha_ini2">Fecha inicio:</label></td>
                        <td><input type="text" id="fecha_ini2" name="fecha_ini2" class="complete fecha" value="<?php echo $fechaInicio; ?>"/></td>
                        <td><label for="fecha_fin2">Fecha final:</label></td>
                        <td><input type="text" id="fecha_fin2" name="fecha_fin2" class="complete fecha" value="<?php echo $fechaFin; ?>"/></td>
                    </tr>
                    <tr>
                        <td><label for="forma_pago">Forma de pago:</label></td>
                        <td><select id="forma_pago" name="forma_pago">
                                <option value="">Selecciona la forma de pago</option><?php
                                $catalogo = new Catalogo();
                                $consulta = "SELECT * FROM c_formapago";
                                $result = $catalogo->obtenerLista($consulta);
                                while ($rs = mysql_fetch_array($result)) {
                                    $s = "";
                                    if ($rs['IdFormaPago'] == $FormaPago) {
                                        $s = "selected";
                                    }
                                    echo "<option value='" . $rs['IdFormaPago'] . "' $s>" . $rs['Nombre'] . "</option>";
                                }
                                ?></select></td>
                        <td><label for="razon_social">Razón Social:</label></td>
                        <td><select id="razon_social" name="razon_social">
                                <option value="">Selecciona la razón social</option><?php
                                $consulta = "SELECT * FROM c_datosfacturacionempresa";
                                $result = $catalogo->obtenerLista($consulta);
                                while ($rs = mysql_fetch_array($result)) {
                                    $s = "";
                                    if ($rs['IdDatosFacturacionEmpresa'] == $RazonSocial) {
                                        $s = "selected";
                                    }
                                    echo "<option value='" . $rs['IdDatosFacturacionEmpresa'] . "' $s>" . $rs['RazonSocial'] . "</option>";
                                }
                                ?></select></td>
                    </tr>
                    <tr>
                        <td><label for="numero_cuenta">Número Cuenta:</label></td>
                        <td>
                            <input type="text" id="numero_cuenta" name="numero_cuenta" style="width: 250px;" value="<?php echo $numero_cuenta; ?>"/>                            
                        </td>
                        <td><label for="dias_credito">D&iacute;as de cr&eacute;dito:</label></td>
                        <td>
                            <input type="text" id="dias_credito" name="dias_credito" style="width: 250px;" value="<?php echo $dias_credito; ?>"/>                            
                        </td>
                    </tr>
                </table>
                <fieldset>
                    <legend>Dirección Fiscal</legend>
                    <table style=" width:100%">
                        <tr>
                            <td >
                                Calle:<br />
                            </td>
                            <td>
                                <input name="Calle" type="text" id="Calle" value="<?php echo $Calle; ?>" style="width:350px;" />
                            </td>
                            <td >
                                No.exterior:<br /> 
                            </td>
                            <td>
                                <input name="NoExterior" type="text" id="NoExterior" value="<?php echo $NoExterior; ?>" style="width:100px;" />
                            </td>
                            <td >
                                No. interior:<br />
                            </td>
                            <td>
                                <input name="NoInterior" type="text" id="NoInterior" value="<?php echo $NoInterior; ?>" style="width:100px;" />
                            </td>
                        </tr>
                    </table>
                    <table>
                        <tr>
                            <td >
                                Colonia:<br />
                            </td>
                            <td>
                                <input name="Colonia" type="text" id="Colonia" value="<?php echo $Colonia; ?>"style="width:250px;" />
                            </td>
                            <td style=" width:40px"></td>
                            <td >
                                Estado:<br />
                            </td>
                            <td>
                                <select name="Estado" id="Estado">
                                    <?php
                                    $nombres = Array("Selecciona un estado", "Aguascalientes", "Baja California", "Baja California Sur", "Campeche", "Coahuila", "Colima", "Chiapas", "Chihuahua",  "Durango", "Estado de México", "Guanajuato", "Guerrero", "Hidalgo", "Jalisco", "Michoacán", "Morelos", "Nayarit", "Nuevo León", "Oaxaca", "Puebla", "Querétaro", "Quintana Roo", "San Luis Potosí", "Sinaloa", "Sonora", "Tabasco", "Tamaulipas", "Tlaxcala", "Veracruz", "Yucatán", "Zacatecas");
                                    $values = Array("", "Aguascalientes", "Baja California", "Baja California Sur", "Campeche", "Coahuila", "Colima", "Chiapas", "Chihuahua",  "Durango", "Estado de México", "Guanajuato", "Guerrero", "Hidalgo", "Jalisco", "Michoacán", "Morelos", "Nayarit", "Nuevo León", "Oaxaca", "Puebla", "Querétaro", "Quintana Roo", "San Luis Potosí", "Sinaloa", "Sonora", "Tabasco", "Tamaulipas", "Tlaxcala", "Veracruz", "Yucatán", "Zacatecas");
                                    if ($Estado != "") {
                                        for ($var = 0; $var < count($values); $var++) {
                                            if ($values[$var] == $Estado) {
                                                echo "<option value=\"" . $values[$var] . "\" selected>" . $nombres[$var] . "</option>";
                                            } else {
                                                echo "<option value=\"" . $values[$var] . "\">" . $nombres[$var] . "</option>";
                                            }
                                        }
                                    } else {
                                        for ($var = 0; $var < count($values); $var++) {
                                            echo "<option value=\"" . $values[$var] . "\">" . $nombres[$var] . "</option>";
                                        }
                                    }
                                    ?>
                                </select>
                            </td>
                        </tr>
                        <tr>
                            <td >
                                Delegación:<br />
                            </td>
                            <td>
                                <input name="Delegacion" type="text" id="Delegacion" value="<?php echo $Delegacion; ?>" style="width:200px;" />
                            </td>
                            <td >
                                C.P:
                            </td>
                            <td>
                                <input name="CP" type="text" id="CP" value="<?php echo $CP; ?>" />
                            </td>
                            <td >
                                Activo:
                            </td>
                            <td>
                                <?php
                                $s = "";
                                if ($Activo == "1") {
                                    $s = "checked='checked'";
                                }
                                echo "<input type=\"checkbox\" value=\"1\" name=\"activo\" id=\"activo\" " . $s . "/>";
                                ?>
                            </td>
                        </tr>
                    </table>
                </fieldset>
                <input type="submit" id="cancelar_contrato" class="boton" value="Cancelar" style="float: right; margin-right: 5px;" onclick="cambiarContenidoValidaciones('contrato2', '<?php echo $back; ?>ventas/validacion/lista_contrato.php', <?php
                if (isset($_POST['idTicket'])) {
                    echo "'" . $_POST['idTicket'] . "'";
                } else {
                    echo "null";
                }
                ?>, '<?php echo $_GET['idCliente']; ?>', null);
                        return false;"/>
                <?php if($permisos_grid->tienePermisoEspecial($_SESSION['idUsuario'],16) || empty($id)){  ?>
                    <input type="submit" class="boton" value="Guardar" style="float: right; margin-right: 5px;" />                
                <?php } ?>
                <input type="hidden" name="id" id="id" value="<?php echo $id; ?>"/>
                
                <br/><br/><br/><br/>
            </form>
        </fieldset>        
    </body>
</html>