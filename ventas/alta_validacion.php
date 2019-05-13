<?php
session_start();

if (!isset($_SESSION['user']) || $_SESSION['user'] == "") {
    header("Location: ../index.php");
}
include_once("../WEB-INF/Classes/Catalogo.class.php");
include_once("../WEB-INF/Classes/Ticket.class.php");
include_once("../WEB-INF/Classes/Pedido.class.php");
include_once("../WEB-INF/Classes/Localidad.class.php");
include_once("../WEB-INF/Classes/Contacto.class.php");
include_once("../WEB-INF/Classes/Zona.class.php");
include_once("../WEB-INF/Classes/Equipo.class.php");
include_once("../WEB-INF/Classes/Cliente.class.php");

$pagina_lista = "ventas/lista_Validacion.php";

$NombreCliente = "";
$ClaveCentroCosto = "";
$ClaveCliente = "";
$NombreCentroCosto = "";
$NoSerieEquipo = "";
$ModeloEquipo = "";    
$NombreResp = "";
$Telefono1Resp = "";    
$CelularResp = "";
$CorreoEResp = "";    
$NombreAtenc = "";
$Telefono1Atenc = "";    
$CorreoEAtenc = "";
$CelularAtenc = "";

$Calle = "";
$NoExterior = "";
$NoInterior = "";
$Colonia = "";
$Ciudad = "";
$Estado = "";
$Delegacion = "";        
$CodigoPostal = "";
$ClaveZona = "";
$NombreZona = "";

$ClaveContacto = "";
$NombreContacto = "";
$TelefonoContacto = "";
$CelularContacto = "";
$CorreoElectronicoContacto = "";


if (isset($_POST['id'])) {
    $obj = new Ticket();
    $localidad = new Localidad();
    $contacto = new Contacto(); 
    
    $obj->getTicketByID($_POST['id']);
    $IdTicket = $obj->getIdTicket();    
    $NombreCliente = $obj->getNombreCliente();
    $ClaveCentroCosto = $obj->getClaveCentroCosto();
    $ClaveCliente = $obj->getClaveCliente();
    $NombreCentroCosto = $obj->getNombreCentroCosto();
    $NoSerieEquipo = $obj->getNoSerieEquipo();
    $ModeloEquipo = $obj->getModeloEquipo();    
    $NombreResp = $obj->getNombreResp();
    $Telefono1Resp = $obj->getTelefono1Resp();    
    $CelularResp = $obj->getCelularResp();
    $CorreoEResp = $obj->getCorreoEResp();    
    $NombreAtenc = $obj->getNombreAtenc();
    $Telefono1Atenc = $obj->getTelefono1Atenc();    
    $CorreoEAtenc = $obj->getCorreoEAtenc();
    $CelularAtenc = $obj->getCelularAtenc();
    
    if($obj->getTipoReporte()==15){/*Si es toner*/
        $pedido = new Pedido();
        $NoSerieEquipo = $pedido->getClaveByIdTicket($_POST['id']);
    }
    
    /*Obtenemos los datos de la localidad del ticket*/
    if($localidad->getLocalidadTicket($IdTicket)){
        $Calle = $localidad->getCalle();
        $NoExterior = $localidad->getNoExterior();
        $NoInterior = $localidad->getNoInterior();
        $Colonia = $localidad->getColonia();
        $Ciudad = $localidad->getCiudad();
        $Estado = $localidad->getEstado();
        $Delegacion = $localidad->getDelegacion();        
        $CodigoPostal = $localidad->getCodigoPostal();
        $ClaveZona = $localidad->getClaveZona();
        $zona = new Zona();
        if($zona->getRegistroById($ClaveZona)){
            $NombreZona = $zona->getNombre();
        }else{
            $NombreZona = "";
        }
    }/*else if($localidad->getLocalidadByClave($ClaveCentroCosto)){        
        $Calle = $localidad->getCalle();
        $NoExterior = $localidad->getNoExterior();
        $NoInterior = $localidad->getNoInterior();
        $Colonia = $localidad->getColonia();
        $Ciudad = $localidad->getCiudad();
        $Estado = $localidad->getEstado();
        $Delegacion = $localidad->getDelegacion();        
        $CodigoPostal = $localidad->getCodigoPostal();
        $ClaveZona = $localidad->getClaveZona();
        $zona = new Zona();
        if($zona->getRegistroById($ClaveZona)){
            $NombreZona = $zona->getNombre();
        }else{
            $NombreZona = "";
        }
    }*/
    
    if($contacto->getContactoByClave($ClaveCliente)){        
        $ClaveContacto = $contacto->getClaveEspecialContacto();
        $NombreContacto = $contacto->getNombre();
        $TelefonoContacto = $contacto->getTelefono();
        $CelularContacto = $contacto->getCelular();
        $CorreoElectronicoContacto = $contacto->getCorreoElectronico();
    }    
    
} else {
    header("Location: index.php");
}

$ModeloCodificado =  str_replace(" ", "__XX__", $ModeloEquipo);
$NombreClienteAux = str_replace(" ", "__XX__", $NombreCliente);
$NombreCentroAux = str_replace(" ", "__XX__", $NombreCentroCosto);
?>
<!DOCTYPE html>
<html lang="es">
    <head>
        <title></title>
        <script type="text/javascript" language="javascript" src="resources/js/paginas/alta_validacion.js"></script>                
    </head>
    <body>       
        <input type="hidden" id="idTicket" name="idTicket" value="<?php echo $IdTicket; ?>"/>        
        <div class="principal">           
            <table style="width: 100%">
                <tr>
                    <td valign="top">
                        <h2><b>Datos del ticket</b></h2>
                        <div id="equipo1">
                            <fieldset>
                                <legend>Equipo</legend>
                                <table style="width: 100%">                                
                                <tr>
                                    <td><label for="no_serie1">No. serie equipo/N&uacute;mero de inventario:</label></td>
                                    <td>
                                        <input type="text" id="no_serie1" name="no_serie1" class="sizeMedium" value="<?php echo $NoSerieEquipo; ?>"/>                                        
                                        <img src="resources/images/Buscar.png" onclick="buscarEquipo();" style="width: 20px; height: 20px; cursor: pointer;"/>
                                    </td>
                                </tr>
                                <tr>
                                    <td><label for="modelo1">Modelo:</label></td>
                                    <td><input type="text" id="modelo1" name="modelo1" class="complete" value="<?php echo $ModeloEquipo; ?>"/></td>
                                </tr>
                            </table>
                            </fieldset>                                                                                    
                        </div>
                        <div id="cliente1">
                            <fieldset>
                                <legend>Cliente</legend>
                                <table style="width: 100%">                                    
                                    <tr>
                                        <td><label for='cliente_n1'>Clave:</label></td>
                                        <td>
                                            <input type='text' id='cliente_n1' name='cliente_n1' class="sizeMedium" value="<?php echo $ClaveCliente; ?>"/>
                                            <img src="resources/images/Buscar.png" onclick="buscarCliente();" style="width: 20px; height: 20px; cursor: pointer;"/>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td><label for='empresa1'>Nombre empresa:</label></td>
                                        <td><input type='text' id='empresa1' name='empresa1' class="complete" value="<?php echo $NombreCliente; ?>"/></td>
                                    </tr>                                
                                    <tr>
                                        <td><label for='localidad1'>Localidad:</label></td>
                                        <td><input type='text' id='localidad1' name='localidad1' class="complete"  value="<?php echo $NombreCentroCosto; ?>"/></td>
                                    </tr>  
                                    <tr><td><label for='clave_localidad1'>Clave localidad:</label></td>
                                        <td><input type='text' id='clave_localidad1' name='clave_localidad1' class="complete"  value="<?php echo $ClaveCentroCosto; ?>"/></td>                                        
                                    </tr>
                                </table>
                            </fieldset>                            
                        </div>
                        <div id="domicilio1">
                            <fieldset>
                                <legend>Domicilio de localidad</legend>
                                <table style="width: 100%">
                                    <tr>
                                        <td><label for='calle1'>Calle:</label></td>
                                        <td colspan="3">
                                            <textarea id="calle1" name="calle1" class="complete"><?php echo $Calle; ?></textarea>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td><label for='exterior1'>Exterior:</label></td>
                                        <td><input type='text' id='exterior1' name='exterior1' value="<?php echo $NoExterior; ?>"/></td>
                                        <td><label for='interior1'>Interior:</label></td>
                                        <td><input type='text' id='interior1' name='interior1' value="<?php echo $NoInterior; ?>"/></td>
                                    </tr>
                                    <tr>
                                        <td><label for='colonia1'>Colonia:</label></td>
                                        <td colspan="3"><input type='text' id='colonia1' name='colonia1' class="complete" value="<?php echo $Colonia; ?>"/></td>
                                    </tr>
                                    <tr>
                                        <td><label for='delegacion1'>Delegaci&oacute;n o Municipio:</label></td>
                                        <td colspan="3"><input type='text' id='delegacion1' name='delegacion1' class="complete" value="<?php echo $Delegacion; ?>"/></td>
                                    </tr>
                                    <tr>
                                        <td><label for='ciudad1'>Ciudad:</label></td>
                                        <td colspan="3"><input type='text' id='ciudad1' name='ciudad1' class="complete" value="<?php echo $Ciudad; ?>"/></td>
                                    </tr>
                                    <tr>
                                        <td><label for='estado1'>Estado:</label></td>
                                        <td colspan="3"><input type='text' id='estado1' name='estado1' class="complete" value="<?php echo $Estado; ?>"/></td>
                                    </tr>
                                    <tr>
                                        <td><label for='cp1'>CP:</label></td>
                                        <td colspan="3"><input type='text' id='cp1' name='cp1' class="complete" value="<?php echo $CodigoPostal; ?>"/></td>
                                    </tr>
                                    <tr>
                                        <td><label for='zona1'>Zona:</label></td>
                                        <td colspan="3"><input type='text' id='zona1' name='zona1' class="complete" value="<?php echo $NombreZona; ?>"/></td>
                                    </tr>
                                    <tr>
                                        <td><label for='ubicacion1'>Ubicaci&oacute;n:</label></td>
                                        <td colspan="3"><input type='text' id='ubicacion1' name='ubicacion1' class="complete"/></td>
                                    </tr>
                                </table>
                            </fieldset>                                                        
                        </div>
                        <div id="contacto1">
                            <fieldset>
                                <legend>Contacto de localidad</legend>
                                <table style="width: 100%">
                                    <tr>
                                        <td><label for='nombre_contacto1'>Nombre:</label></td>
                                        <td colspan='3'><input type='text' id='nombre_contacto1' name='nombre_contacto1' class="complete"  value="<?php echo $NombreContacto; ?>"/></td>
                                    </tr>
                                    <tr>
                                        <td><label for='telefono1'>Tel&eacute;fono:</label></td>
                                        <td colspan='3'><input type='text' id='telefono1' name='telefono1' class="complete"  value="<?php echo $TelefonoContacto; ?>"/></td>
                                    </tr>
                                    <tr>
                                        <td><label for='celular1'>Celular:</label></td>
                                        <td colspan='3'><input type='text' id='celular1' name='celular1' class="complete"  value="<?php echo $CelularContacto; ?>"/></td>
                                    </tr>
                                    <tr>
                                        <td><label for='correo1'>Correo:</label></td>
                                        <td colspan='3'><input type='text' id='correo1' name='correo1' class="complete"  value="<?php echo $CorreoElectronicoContacto; ?>"/></td>
                                    </tr>
                                </table>
                            </fieldset>                            
                        </div>
                        <div id="responsable1">
                            <fieldset>
                                <legend>Contacto responsable ticket</legend>
                                <table style="width: 100%">
                                    <tr>
                                        <td><label for='nombre_responsable1'>Nombre:</label></td>
                                        <td colspan='3'><input type='text' id='nombre_responsable1' name='nombre_responsable1' class="complete"  value="<?php echo $NombreResp; ?>"/></td>
                                    </tr>
                                    <tr>
                                        <td><label for='telefono_responsable1'>Tel&eacute;fono:</label></td>
                                        <td colspan='3'><input type='text' id='telefono_responsable1' name='telefono_responsable1' class="complete"  value="<?php echo $Telefono1Resp; ?>"/></td>
                                    </tr>
                                    <tr>
                                        <td><label for='celular_responsable1'>Celular:</label></td>
                                        <td colspan='3'><input type='text' id='celular_responsable1' name='celular_responsable1' class="complete"  value="<?php echo $CelularResp; ?>"/></td>
                                    </tr>
                                    <tr>
                                        <td><label for='correo_responsable1'>Correo:</label></td>
                                        <td colspan='3'><input type='text' id='correo_responsable1' name='correo_responsable1' class="complete"  value="<?php echo $CorreoEResp; ?>"/></td>
                                    </tr>
                                </table>
                            </fieldset>                            
                        </div>
                        <div id="contacto1">
                            <fieldset>
                                <legend>Contacto atención ticket</legend>
                                <table style="width: 100%">
                                    <tr>
                                        <td><label for='nombre_atencion1'>Nombre:</label></td>
                                        <td colspan='3'><input type='text' id='nombre_atencion1' name='nombre_atencion1' class="complete"  value="<?php echo $NombreAtenc; ?>"/></td>
                                    </tr>
                                    <tr>
                                        <td><label for='telefono_atencion1'>Tel&eacute;fono:</label></td>
                                        <td colspan='3'><input type='text' id='telefono_atencion1' name='telefono_atencion1' class="complete"  value="<?php echo $Telefono1Atenc; ?>"/></td>
                                    </tr>
                                    <tr>
                                        <td><label for='celular_atencion1'>Celular:</label></td>
                                        <td colspan='3'><input type='text' id='celular_atencion1' name='celular_atencion1' class="complete"  value="<?php echo $CelularAtenc; ?>"/></td>
                                    </tr>
                                    <tr>
                                        <td><label for='correo_atencion1'>Correo:</label></td>
                                        <td colspan='3'><input type='text' id='correo_atencion1' name='correo_atencion1' class="complete"  value="<?php echo $CorreoEAtenc; ?>"/></td>
                                    </tr>
                                </table>
                            </fieldset>                                                        
                        </div>
                    </td>
                    <td valign="top">
                        <div style="float: right; margin-right: 5px; "><input type="checkbox" id="ticket_validado" name="ticket_validado"/>Ticket validado</div>
                        <h2><b>Datos a actualizar</b></h2>
                        <div id="mensaje_equipo2"></div>
                        <div id="equipo2">
                            
                        </div>
                        <div id="mensaje_cliente2"></div>
                        <div id="cliente2">
                            
                        </div>
                        <div id="mensaje_localidad2"></div>
                        <div id="localidad2">
                            
                        </div>
                        <div id="mensaje_domicilio2"></div>
                        <div id="domicilio2">
                            
                        </div>
                        <div id="mensaje_contacto2"></div>
                        <div id="contacto2">
                            
                        </div>
                        <div id="mensaje_contrato2"></div>
                        <div id="contrato2">
                            
                        </div>
                        <div id="mensaje_anexo2"></div>
                        <div id="anexo2">
                            
                        </div>
                        <div id="mensaje_serviciosg2"></div>
                        <div id="servicios_g2" style="max-width: 97%;">

                        </div>
                        <div id="mensaje_serviciosp2"></div>
                        <div id="servicios_p2" style="max-width: 97%;">

                        </div>
                        <div id="mensaje_equipos"></div>
                        <div id="equipos_p2" style="max-width: 97%;">

                        </div>
                           
                        </div>
                    </td>
                </tr>
            </table>
            <?php            
            echo '<script type="text/javascript">cambiarContenidoValidaciones("equipo2", "ventas/validacion/lista_equipo.php?NoSerie='.$NoSerieEquipo.'&Modelo='.$ModeloCodificado.'", "'.$IdTicket.'", null, false);</script>';            
            echo '<script type="text/javascript">cambiarContenidoValidaciones("cliente2", "ventas/validacion/lista_cliente.php?Nombre='.$NombreClienteAux.'&Clave='.$ClaveCliente.'&NombreCentro='.$NombreCentroAux.'&ClaveCentro='.$ClaveCentroCosto.'", "'.$IdTicket.'", null, false);</script>';
            ?>         
            <div id="mensaje_validar" style="float: right;"></div>
            <input type="hidden" id="cc_actual" name="cc_actual" value=""/>
            <input type='hidden' id='clave_cliente1' name='clave_cliente1' class="complete"/>
            <br/>
            <input type="submit" class="boton" value="Regresar" style="float: right;" onclick="cambiarContenidos('<?php echo $pagina_lista; ?>');return false;"/>
            <input type="submit" class="boton" value="Validar Ticket" style="float: right;" onclick="validarTicket('<?php echo $_POST['id']; ?>','<?php echo $NoSerieEquipo; ?>');return false;"/><br/><br/><br/>
        </div>
    </body>
</html>