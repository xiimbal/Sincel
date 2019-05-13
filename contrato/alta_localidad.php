<?php
session_start();

if (!isset($_SESSION['user']) || $_SESSION['user'] == "") {
    header("Location: ../index.php");
}

include_once("../WEB-INF/Classes/Catalogo.class.php");
include_once("../WEB-INF/Classes/Cliente.class.php");
include_once("../WEB-INF/Classes/PermisosSubMenu.class.php");
$permisos_grid = new PermisosSubMenu();
$same_page = "ventas/mis_clientes_arbol.php";
$permisos_grid->getPermisosSubmenu($_SESSION['idUsuario'], $same_page);
$ClaveCliente = $_GET['id'];
$Cliente = new Cliente();
$Cliente->getRegistroById($ClaveCliente);
if ($permisos_grid->getAlta()) {
    ?>
<!DOCTYPE html>
<html lang="es">
    <head>
        <title>Alta de localidad</title>
        <!-- JS -->
        <link rel="stylesheet" href="../resources/css/genesis/jquery-ui-1.10.3.custom.css" type="text/css" media="screen" />
        <script src="../resources/js/jquery/jquery-1.11.3.min.js"></script>
        <script src="../resources/js/jquery/jquery-ui.min.js"></script>      
        <script type="text/javascript" src="../resources/js/jquery/jquery.validate.js"></script>
        <script type="text/javascript" src="../resources/js/jquery/jquery-ui-timepicker-addon.js"></script>
        <!-- multiselect -->
        <script src="../resources/js/multiselect/jquery.multiselect.min.js"></script>
        <script src="../resources/js/multiselect/jquery.multiselect.filter.min.js"></script>
        <link href="../resources/css/multiselect/jquery.multiselect.css" rel="stylesheet" type="text/css">
        <link href="../resources/css/multiselect/jquery.multiselect.filter.css" rel="stylesheet" type="text/css">

        <script type="text/javascript" src="../resources/js/funciones.js"></script>         
        <script type="text/javascript" language="javascript" src="../resources/js/paginas/validacion/alta_localidad.js"></script>
    </head>
    <body>        
        <div id="cargando" style="width:80%; margin-left: 50%; display: none; ">
            <img src="../resources/images/cargando.gif"/>                          
        </div>
        <div id="loading_text" style="width:80%; margin-top: 5px;  margin-left: 45%;"></div>
        <h2>Insertar localidad del cliente <b><?php echo $Cliente->getNombreRazonSocial(); ?></b></h2>
        <div id="mensaje_localidad2" style="font-size: 12px;"></div>
        <form id="formLocalidad" name="formLocalidad" action="/" method="POST">
            <table>
                <tr>
                    <td><label for="nombre_cc2">Nombre de la localidad</label></td>
                    <td><input type="text" id="nombre_cc2" name="nombre_cc2"/></td>
                </tr>
                <tr>
                    <td><label for="Moroso">Moroso:</label></td>
                    <td>           
                        <?php
                        echo "<input type=\"checkbox\" value=\"1\" name=\"Moroso\" id=\"Moroso\"/>";
                        ?>
                    </td>
                </tr>
            </table>
            <fieldset>
                <legend>Domicilio</legend>
                <table style=" width:100%">
                    <tr>
                        <td >
                            Calle:<br />
                        </td>
                        <td>
                            <input name="Calle" type="text" id="Calle" value="" style="width:350px;" />
                        </td>
                        <td >
                            No.exterior:<br /> 
                        </td>
                        <td>
                            <input name="NoExterior" type="text" id="NoExterior" value="" style="width:100px;" />
                        </td>
                        <td >
                            No. interior:<br />
                        </td>
                        <td>
                            <input name="NoInterior" type="text" id="NoInterior" value="" style="width:100px;" />
                        </td>
                    </tr>
                </table>
                <table>
                    <tr>
                        <td >
                            Colonia:<br />
                        </td>
                        <td>
                            <input name="Colonia" type="text" id="Colonia" value=""style="width:250px;" />
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
                                for ($var = 0; $var < count($values); $var++) {
                                    echo "<option value=\"" . $values[$var] . "\">" . $nombres[$var] . "</option>";
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
                            <input name="Delegacion" type="text" id="Delegacion" style="width:200px;" />
                        </td>
                        <td >
                            C.P:
                        </td>
                        <td>
                            <input name="CP" type="text" id="CP" />
                        </td>
                        <td >
                            Activo:
                        </td>
                        <td>
                            <?php
                            echo "<input type=\"checkbox\" value=\"1\" name=\"activo\" id=\"activo\" checked='checked'/>";
                            ?>
                        </td>
                    </tr>
                </table>
            </fieldset>            
            <input type="submit" id="submit_localidad" name="submit_localidad" class="boton" value="Guardar" style="margin-left: 91%;"/>
            <input type="hidden" id="id" name="id" value=""/>
            <input type="hidden" id="cliente_cc2" name="cliente_cc2" value="<?php echo $ClaveCliente; ?>"/>
            <input type="hidden" id="recarga" name="recarga" value="si"/>
            <input type="hidden" id="independiente" name="independiente" value="si"/>
        </form>
    </body>
</html>
<?php } ?>