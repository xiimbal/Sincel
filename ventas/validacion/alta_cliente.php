<?php
session_start();

if (!isset($_SESSION['user']) || $_SESSION['user'] == "") {
    header("Location: ../../index.php");
}

include_once("../../WEB-INF/Classes/Cliente.class.php");
include_once("../../WEB-INF/Classes/Catalogo.class.php");
include_once("../../WEB-INF/Classes/PermisosSubMenu.class.php");    
$back = "";
$permisos_grid = new PermisosSubMenu();
$id = "";
$Nombre = "";
$Clave = "";
$rfc = "";
$razon = "";
$tipo = "";
//$numero_cuenta = "";
$tipoFacturacion = "";
$tipoCliente = "";
$disabled = "";
$NombreCodificado = "";
$ClaveGET = "";
$IdTipoMorosidad = "";
$Moroso = "";
$GeneraFactura = "";
$diasCredito = "";
$MostrarContrato = "checked='checked'";
$Activo = "checked='checked'";

if (isset($_POST['id']) && !is_null($_POST['id'])) {
    $equipo = new Cliente();
    $equipo->getRegistroById($_POST['id']);
    $id = $_POST['id'];
    $Nombre = $equipo->getNombreRazonSocial();
    $Clave = $equipo->getClaveCliente();
    $rfc = $equipo->getRFC();
    $tipo = $equipo->getModalidad();
    //$numero_cuenta = $equipo->getNumeroCuenta();
    $tipoFacturacion = $equipo->getIdTipoFacturacion();
    $tipoCliente = $equipo->getIdTipoCliente();
    $razon = $equipo->getIdDatosFacturacionEmpresa();
    $disabled = "readonly='readonly'";
    $Moroso = $equipo->getIdEstatusCobranza();
    $IdTipoMorosidad = $equipo->getIdTipoMorosidad();
    $diasCredito = $equipo->getDiasCredito();
    if($equipo->getGeneraFactura() == "1"){
        $GeneraFactura = "checked='checked'";
    }
    if($equipo->getMostarMesContrato() == "0"){
        $MostrarContrato = "";
    }
    
    if($equipo->getActivo() == "0"){
        $Activo = "";
    }
}

if (isset($_GET['Clave']) && $_GET['Nombre']) {
    $NombreCodificado = $_GET['Nombre'];
    $NombreGET = str_replace("__XX__", " ", $_GET['Nombre']);
    $ClaveGET = $_GET['Clave'];
    if (isset($_GET['Nuevo'])) {
        $Nombre = $NombreGET;
        $Clave = $ClaveGET;
        $disabled = "";
    }
}

?>
<!DOCTYPE html>
<html lang="es">
    <head>
        <title></title>
        <script type="text/javascript" language="javascript" src="<?php echo $back; ?>resources/js/paginas/alta_validacion.js"></script>
        <script type="text/javascript" language="javascript" src="<?php echo $back; ?>resources/js/paginas/validacion/alta_cliente.js"></script>
    </head>
    <body>        
        <fieldset style="max-width: 90%;">
            <legend>Cliente</legend>
            <form id="formCliente" name="formCliente" action="/" method="POST">
                <table style="width: 100%">                      
                    <tr>
                        <td><label for="nombre_cliente2">Nombre:</label></td>
                        <td><input type="text" id="nombre_cliente2" name="nombre_cliente2" class="complete" value="<?php echo $Nombre; ?>"/></td>
                    </tr>
                    <tr>
                        <td><label for="rfc_cliente2">RFC:</label></td>
                        <td>
                            <input type="text" id="rfc_cliente2" name="rfc_cliente2" class="complete" value="<?php echo $rfc; ?>"/>
                            <div id="error_rfc" style="color: red;"></div>
                        </td>
                    </tr>            
                    <tr>
                        <td><label for="razon_cliente2">Razón social:</label></td>
                        <td>
                            <select id="razon_cliente2" name="razon_cliente2" style="width: 250px;">
                                <?php
                                $catalogo = new Catalogo();
                                $query = $catalogo->getListaAlta("c_datosfacturacionempresa", "RazonSocial");
                                echo "<option value='0' >Selecciona una opción</option>";
                                while ($rs = mysql_fetch_array($query)) {
                                    $s = "";
                                    if ($razon != "" && $razon == $rs['IdDatosFacturacionEmpresa']) {
                                        $s = "selected";
                                    }
                                    echo "<option value=" . $rs['IdDatosFacturacionEmpresa'] . " " . $s . ">" . $rs['RazonSocial'] . "</option>";
                                }
                                ?>
                            </select>
                        </td>
                    </tr>
                    <tr>
                        <td><label for="modalidad2">Tipo cliente:</label></td>
                        <td>                            
                            <select id="modalidad2" name="modalidad2" style="width: 250px;">
                                <?php
                                $query = $catalogo->getListaAltaTodo("c_clientemodalidad", "Nombre");
                                while ($rs = mysql_fetch_array($query)) {
                                    $s = "";
                                    if ($tipo != "" && $tipo == $rs['IdTipoCliente']) {
                                        $s = "selected";
                                    }
                                    echo "<option value=" . $rs['IdTipoCliente'] . " " . $s . ">" . $rs['Nombre'] . "</option>";
                                }
                                ?>
                            </select>
                        </td>
                    </tr>
                    <tr style="display:none">
                        <td><label for="tipo_facturacion">Tipo facturaci&oacute;n:</label></td>
                        <td>                            
                            <select id="tipo_facturacion" name="tipo_facturacion" style="width: 250px;">
                                <?php
                                $query = $catalogo->getListaAltaTodo("c_tipofacturacion", "IdTipoFacturacion");
                                while ($rs = mysql_fetch_array($query)) {
                                    $s = "";
                                    if ($tipoFacturacion != "" && $tipoFacturacion == $rs['IdTipoFacturacion']) {
                                        $s = "selected";
                                    }
                                    echo "<option value=" . $rs['IdTipoFacturacion'] . " " . $s . ">" . $rs['TipoFacturacion'] . "</option>";
                                }
                                ?>
                            </select>
                        </td>
                    </tr>
                    <tr>
                        <td><label for="tipo">Tipo:</label></td>
                        <td>                            
                            <select id="tipo" name="tipo" style="width: 250px;">
                                <?php
                                $query = $catalogo->getListaAltaTodo("c_tipocliente", "Orden");
                                while ($rs = mysql_fetch_array($query)) {
                                    $s = "";
                                    if ($tipoCliente != "" && $tipoCliente == $rs['IdTipoCliente']) {
                                        $s = "selected";
                                    }
                                    echo "<option value=" . $rs['IdTipoCliente'] . " " . $s . ">" . $rs['Nombre'] . "</option>";
                                }
                                ?>
                            </select>
                        </td>
                    </tr>
                    <tr>
                        <td><label for="dias_credito">D&iacute;as de cr&eacute;dito:</label></td>
                        <td>                            
                            <input type="text" id="dias_credito" name="dias_credito" value="<?php echo $diasCredito; ?>" style="width: 250px;"/>
                        </td>
                    </tr>
                    <tr style="display:none">
                        <td><label for="tipo_morosidad">Tipo Morosidad:</label></td>
                        <td>                            
                            <select id="tipo_morosidad" name="tipo_morosidad" style="width: 250px;">
                                <?php
                                $query = $catalogo->getListaAltaTodo("c_tipofacturacion", "IdTipoFacturacion");
                                while ($rs = mysql_fetch_array($query)) {
                                    $s = "";
                                    if ($IdTipoMorosidad != "" && $IdTipoMorosidad == $rs['IdTipoFacturacion']) {
                                        $s = "selected";
                                    }
                                    echo "<option value=" . $rs['IdTipoFacturacion'] . " " . $s . ">" . $rs['TipoFacturacion'] . "</option>";
                                }
                                ?>
                            </select>
                        </td>
                    </tr>
                    <tr style="display:none">
                        <td><label for="Moroso">Moroso:</label></td>
                        <td>           
                            <?php
                            $s = "";
                            if ($Moroso == "2") {
                                $s = "checked";
                            }
                            echo "<input type=\"checkbox\" value=\"1\" name=\"Moroso\" id=\"Moroso\" " . $s . "/>";
                            ?>
                        </td>
                    </tr>                                        
                    <tr>
                        <td><label for="Moroso">Generar pre-factura atm:</label></td>
                        <td>           
                            <?php                            
                            echo "<input type=\"checkbox\" value=\"1\" name=\"genera\" id=\"genera\" $GeneraFactura/>";
                            ?>
                        </td>
                    </tr>
                    <tr>
                        <td><label for="mostrar_contrato">Mostrar contrato en factura:</label></td>
                        <td>           
                            <?php                            
                            echo "<input type=\"checkbox\" value=\"1\" name=\"mostrar_contrato\" id=\"mostrar_contrato\" $MostrarContrato/>";
                            ?>
                        </td>
                    </tr>                    
                    <tr>
                        <td><label for="activo">Activo</label></td>
                        <td>           
                            <?php                            
                            echo "<input type=\"checkbox\" value=\"1\" name=\"activo\" id=\"activo\" $Activo/>";
                            ?>
                        </td>
                    </tr>
                </table>                                      
                <input type="submit" class="boton" value="Cancelar" style="float: right; margin-right: 5px;" onclick=" cambiarContenidoValidaciones('cliente2', '<?php echo $back; ?>ventas/validacion/lista_cliente.php?Nombre=<?php echo $NombreCodificado; ?>&Clave=<?php echo $ClaveGET; ?>&NombreCentro=<?php echo $_GET['NombreCentro']; ?>&ClaveCentro=<?php echo $_GET['ClaveCentro']; ?>', <?php
                if (isset($_POST['idTicket'])) {
                    echo "'" . $_POST['idTicket'] . "'";
                } else {
                    echo "null";
                }
                ?>, null);
                        return false;"/>
                <?php if($permisos_grid->tienePermisoEspecial($_SESSION['idUsuario'],16) || empty($id)){  ?>
                    <input type="submit" class="boton" value="Guardar" style="float: right; margin-right: 5px;" />                
                <?php } ?>
                <input type="hidden" name="id" id="id" value="<?php echo $id; ?>"/>
                <?php
                    if (isset($_POST['idTicket'])) {
                        echo "<input type='hidden' id='idTicketValidar' name='idTicketValidar' value='".$_POST['idTicket']."'/>";
                    }
                ?>
                <br/><br/><br/><br/>
            </form>
        </fieldset>        
    </body>
</html>