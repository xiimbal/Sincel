<?php
session_start();

if (!isset($_SESSION['user']) || $_SESSION['user'] == "") {
    header("Location: ../../index.php");
}

include_once("../../WEB-INF/Classes/CentroCosto.class.php");
include_once("../../WEB-INF/Classes/Localidad.class.php");
include_once("../../WEB-INF/Classes/PermisosSubMenu.class.php");
include_once("../../WEB-INF/Classes/Catalogo.class.php");

$catalogo = new Catalogo();
$permisos_grid = new PermisosSubMenu();
$id = "";
$Nombre = "";
$Clave = "";
$Moroso = "";
$Calle = "";
$NoExterior = "";
$NoInterior = "";
$Colonia = "";
$Estado = "";
$Delegacion = "";
$CP = "";
$Latitud = "";
$Longitud = "";
$ClaveZona = "";
$idTipoLocalidad = "";
$localidad_string = "";
$Activo = "1";

if (isset($_POST['id']) && !is_null($_POST['id'])) {
    $equipo = new CentroCosto();
    $equipo->getRegistroById($_POST['id']);
    $id = $_POST['id'];
    if ($id != "") {
        $Nombre = $equipo->getNombre();
        $Clave = $equipo->getClaveCentroCosto();
        $Moroso = $equipo->getMoroso();
        $idTipoLocalidad = $equipo->getTipoDomicilioFiscal();
        $localidad = new Localidad();
        if ($idTipoLocalidad == "2") {
            $localidad->getLocalidadByClave($equipo->getClaveCliente());
        } else {
            $localidad->getLocalidadByClave($Clave);
        }
        $Calle = $localidad->getCalle();
        $NoExterior = $localidad->getNoExterior();
        $NoInterior = $localidad->getNoInterior();
        $Colonia = $localidad->getColonia();
        $Estado = $localidad->getEstado();
        $Delegacion = $localidad->getDelegacion();
        $CP = $localidad->getCodigoPostal();
        $Activo = $localidad->getActivo();
        $localidad_string = $localidad->getLocalidad();
        $Latitud = $localidad->getLatitud();
        $Longitud = $localidad->getLongitud();
        $ClaveZona = $localidad->getClaveZona();
    }
}

if (isset($_GET['Clave']) && isset($_GET['Nombre'])) {
    $NombreCodificado = $_GET['Nombre'];
    $NombreGET = str_replace("__XX__", " ", $_GET['Nombre']);
    $ClaveGET = $_GET['Clave'];
}

$nuevo = "";

if (isset($_GET['Nuevo'])) {
    $Nombre = $NombreGET;
    $Clave = $ClaveGET;
    $nuevo = "si";
}
?>
<!DOCTYPE html>
<html lang="es">
    <head>
        <title></title>
        <script type="text/javascript" language="javascript" src="../resources/js/paginas/alta_validacion.js"></script>
        <script type="text/javascript" language="javascript" src="../resources/js/paginas/validacion/alta_localidad.js"></script>
    </head>
    <body>        
        <fieldset>
            <legend>Localidad</legend>
            <form id="formLocalidad" name="formLocalidad" action="/" method="POST">
                <input type="hidden" name="cliente_cc2" id="cliente_cc2" value="<?php echo $_GET['idCliente']; ?>"/>
                <input type="hidden" name="nuevo_cc2" id="nuevo_cc2" value="<?php echo $nuevo; ?>"/>
                <table style="width: 100%">
                    <tr>
                        <td><label for="nombre_cc2">Nombre:</label></td>
                        <td><input type="text" id="nombre_cc2" name="nombre_cc2" class="complete" value="<?php echo $Nombre; ?>"/></td>
                    </tr>
                    <tr>
                        <td></td>
                        <td>
                            <?php
                            $s = "checked='checked'";
                            if ($Activo == "0") {
                                $s = "";
                            }
                            echo "<input type=\"checkbox\" value=\"1\" name=\"activo\" id=\"activo\" " . $s . "/> Activo Localidad";
                            ?>
                        </td>
                    </tr>
                </table>
                <fieldset>
                    <legend>Domicilio fiscal</legend>
                    <table>
                        <tr>
                            <td></td>
                            <td>           
                                <?php $s = "";
                                if ($idTipoLocalidad == "0" || $idTipoLocalidad == "") {
                                    $s = "checked='checked'";
                                } ?>
                                <input type="radio" id="domicilio_fiscal" name="domicilio_fiscal" value="0" <?php echo $s; ?>/>Ninguno
                                <?php $s = "";
                                if ($idTipoLocalidad == "1") {
                                    $s = "checked='checked'";
                                } ?>
                                <input type="radio" id="domicilio_fiscal" name="domicilio_fiscal" value="1" <?php echo $s; ?>/>Localidad
                                <?php $s = "";
                                if ($idTipoLocalidad == "2") {
                                    $s = "checked='checked'";
                                } ?>
                                <input type="radio" id="domicilio_fiscal" name="domicilio_fiscal" value="2" <?php echo $s; ?>/>Cliente
                            </td>
                        </tr>
                    </table>
                </fieldset>
                <fieldset>
                    <legend>Domicilio</legend>
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
                            <td>Localidad:</td>
                            <td><input type="text" id="localidad_string" name="localidad_string" value="<?php echo $localidad_string; ?>"/></td>
                        </tr>
                        <tr>
                            <td>
                                Estado:<br />
                            </td>
                            <td>
                                <select name="Estado" id="Estado">
                                    <?php
                                    $nombres = Array("Selecciona un estado", "Aguascalientes", "Baja California", "Baja California Sur", "Campeche", "Ciudad de México", "Coahuila", "Colima", "Chiapas", "Chihuahua", "Durango", "Estado de México", "Guanajuato", "Guerrero", "Hidalgo", "Jalisco", "Michoacán", "Morelos", "Nayarit", "Nuevo León", "Oaxaca", "Puebla", "Querétaro", "Quintana Roo", "San Luis Potosí", "Sinaloa", "Sonora", "Tabasco", "Tamaulipas", "Tlaxcala", "Veracruz", "Yucatán", "Zacatecas");
                                    $values = Array("", "Aguascalientes", "Baja California", "Baja California Sur", "Campeche", "Ciudad de México", "Coahuila", "Colima", "Chiapas", "Chihuahua", "Durango", "Estado de México", "Guanajuato", "Guerrero", "Hidalgo", "Jalisco", "Michoacán", "Morelos", "Nayarit", "Nuevo León", "Oaxaca", "Puebla", "Querétaro", "Quintana Roo", "San Luis Potosí", "Sinaloa", "Sonora", "Tabasco", "Tamaulipas", "Tlaxcala", "Veracruz", "Yucatán", "Zacatecas");
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
                            <td><label for="zona">Zona:<span class="obligatorio"> *</span></label></td>
                            <td>
                                <select id="zona" name="zona" class="sizeMedio">
                                    <?php
                                    $query = $catalogo->getListaAlta("c_zona", "NombreZona");
                                    echo "<option value='0' >Selecciona una zona</option>";
                                    while ($rs = mysql_fetch_array($query)) {
                                        $s = "";
                                        if ($ClaveZona == $rs['ClaveZona']) {
                                            $s = "selected";
                                        }
                                        echo "<option value=" . $rs['ClaveZona'] . " " . $s . ">" . $rs['NombreZona'] . "</option>";
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
                        </tr>
                        <tr>
                            <td >
                                Latitud:<br />
                            </td>
                            <td>
                                <input name="Latitud" type="text" id="Delegacion" value="<?php echo $Latitud; ?>" style="width:200px;" />
                            </td>
                            <td >
                                Longitud:
                            </td>
                            <td>
                                <input name="Longitud" type="text" id="CP" value="<?php echo $Longitud; ?>" />
                            </td> 
                        </tr>
                    </table>
                </fieldset>
                <input type="submit" class="boton" id="cancelar" value="Cancelar" style="float: right; margin-right: 5px;" onclick="cambiarContenidoValidaciones('localidad2', '../cliente/validacion/lista_localidad.php?Nombre=<?php echo $NombreCodificado; ?>&Clave=<?php echo $ClaveGET; ?>', <?php
                if (isset($_POST['idTicket'])) {
                    echo "'" . $_POST['idTicket'] . "'";
                } else {
                    echo "null";
                }
                ?>, '<?php echo $_GET['idCliente']; ?>', null);
                        return false;"/>
<?php if ($permisos_grid->tienePermisoEspecial($_SESSION['idUsuario'], 26) || empty($id)) { ?>
                    <input type="submit" class="boton" value="Guardar" style="float: right; margin-right: 5px;" />                
<?php } ?>
                <input type="hidden" name="id" id="id" value="<?php echo $id; ?>"/>      
                <input type="hidden" name="independiente" id="independiente" value="true"/>
                <br/><br/><br/><br/>
            </form>
        </fieldset>        
    </body>
</html>