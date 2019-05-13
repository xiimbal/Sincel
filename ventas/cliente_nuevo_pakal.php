<?php
session_start();

if (!isset($_SESSION['user']) || $_SESSION['user'] == "") {
    header("Location: ../index.php");
}
include_once("../WEB-INF/Classes/ccliente.class.php");
include_once("../WEB-INF/Classes/Catalogo.class.php");
include_once("../WEB-INF/Classes/Usuario.class.php");
include_once("../WEB-INF/Classes/PermisosSubMenu.class.php");
include_once("../WEB-INF/Classes/lib/PHPImagen.lib.php");

$usuario = new Usuario();
$cliente = new ccliente();
$catalogo = new Catalogo(); 
$permisos_grid = new PermisosSubMenu();

$pagina_lista = "ventas/mis_clientes_pakal.php";
$permisos_grid->getPermisosSubmenu($_SESSION['idUsuario'], $pagina_lista);

$pagina_popup = "cliente/alta_cliente.php";
if (isset($_GET['id']) && $_GET['id'] != "") {    
    $cliente->getregistrobyID($_GET['id']);
    $pagina_popup = "cliente/alta_cliente.php?ClaveCliente=".$_GET['id'];
}

$regresar = "ventas/mis_clientes_pakal.php";
if(isset($_GET['regresar']) && $_GET['regresar'] != ""){
    $regresar = $_GET['regresar'];
}
?>
<html>
    <head>
        <script type="text/javascript" language="javascript" src="resources/js/paginas/ventas/nuevo_cliente_pakal.js"></script>
        <style>
            .sizeMedio{width: 220px;}
        </style>
    </head>
    <body>
        <form id="formcliente">
            <div class="p-4 bg-light rounded">

                <div class="mb-4 card border-secondary">                
                    <a id="clientes_header" class="card-header p-3" role="button" data-toggle="collapse" data-target="#clientes_card" aria-expanded="true" aria-controls="clientes_card">
                        <h5 class="mt-1 mb-0 text-primary">Datos del cliente</h5>
                    </a>  
                    <div id="clientes_card" class="collapse show" aria-labelledby="clientes_header" data-parent="#accordion">
                        <div class="card-body">
                            <div class="form-row">
                                <div class="form-group col-md-6 col-12" style="display: none">
                                    <label for="EstatusCobranza" class="m-0">Estatus de cobranza:</label>
                                    <select name="EstatusCobranza" id="EstatusCobranza" class="custom-select">
                                        <?php
                                        $nombres = Array("Al Corriente", "Moroso");
                                        $values = Array("1", "2");
                                        if ($cliente->getEstatusCobranza() != "") {
                                            for ($var = 0; $var < count($values); $var++) {
                                                if ($values[$var] == $cliente->getEstatusCobranza()) {
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
                                    <span id="MainContent_reqValEstatusCobranza" style="display:none;"></span>
                                </div>
                                <div class="form-group col-md-6 col-12">
                                    <label for="calificacion" class="m-0">Calificacion:</label>
                                    <select id="calificacion" name="calificacion" class="custom-select">
                                        <option value=''>Califica al cliente</option>
                                        <?php
                                            for($i = 0; $i<= 10; $i++){
                                                $s = "";
                                                if ($cliente->getCalificacion() != "" && $cliente->getCalificacion() == $i) {
                                                    $s = "selected";
                                                }
                                                echo "<option value='$i' $s>$i</option>";
                                            }
                                        ?>
                                    </select>
                                </div>
                            </div>
                            <div class="form-row">
                                <div class="form-group col-md-6 col-12">
                                    <label for="RazonSocial" class="m-0">Nombre:<span class="obligatorio"> *</span></label>
                                    <input name="RazonSocial" type="text" maxlength="150" id="RazonSocial" value="<?php if ($cliente->getRazonSocial() != "") echo $cliente->getRazonSocial(); ?>"  class="form-control" /> 
                                    <span id="MainContent_reqValRazonSocial" style="display:none;"></span>
                                </div>
                                <div class="form-group col-md-6 col-12">
                                    <label for="comentario" class="m-0">Comentario:</label>
                                    <textarea id='comentario' name='comentario' style='resize: none;' class="form-control">
                                        <?php 
                                            if ($cliente->getComentario() != "") {
                                                echo $cliente->getComentario();
                                            } 
                                        ?>
                                    </textarea>
                                </div>
                            </div>
                            <div class="form-row">
                                <div class="form-group col-md-6 col-12">
                                    <label for="TipoCliente" class="m-0">Nivel:</label>
                                    <select name="TipoCliente" id="TipoCliente"  class="custom-select">
                                        <?php                                               
                                        $query = $catalogo->getListaAlta("c_tipocliente", "Nombre");
                                            while ($rs = mysql_fetch_array($query)) {
                                                $s = "";
                                                if ($cliente->getTipoCliente() != "" && $cliente->getTipoCliente() == $rs['IdTipoCliente']) {
                                                    $s = "selected";
                                                }
                                                echo "<option value=" . $rs['IdTipoCliente'] . " " . $s . ">" . $rs['Nombre'] . "</option>";
                                            }
                                        ?>
                                    </select>
                                    <span id="MainContent_reqValTipoCliente" style="display:none;"></span>
                                </div>
                                <div class="form-group col-md-6 col-12">
                                    <label for="imagen_url" class="m-0">Imagen:</label>
                                    <input type='file' id='imagen_url' name='imagen_url' maxlength="200" value='<?php if($cliente->getImagen()!=null) echo $cliente->getImagen(); ?>'>                                            
                                    <?php 
                                        if($cliente->getImagen()!=null){ 
                                            echo "<input type='image' src='".$cliente->getImagen()."' onclick='return false;' style='width: 100px; height:100px;'/>";  
                                        } 
                                    ?>
                                </div>
                            </div>
                            <div class="form-row">
                                <div class="form-group col-md-6 col-12">
                                    <label for="modalidad2" class="m-0">Tipo de cliente:</label>
                                    <select id="modalidad2" name="modalidad2" class="custom-select">
                                        <?php
                                        $query = $catalogo->obtenerLista("SELECT Nombre FROM pakal_rp.c_clientemodalidad where IdTipoCliente=2 OR IdTipoCliente=3 OR IdTipoCliente=7 OR IdTipoCliente=5;");
                                        while ($rs = mysql_fetch_array($query)) {
                                            $s = "";
                                            if ($cliente->getModalidad() != "" && $cliente->getModalidad() == $rs['IdTipoCliente']) {
                                                $s = "selected";
                                            }
                                            echo "<option value=" . $rs['IdTipoCliente'] . " " . $s . ">" . $rs['Nombre'] . "</option>";
                                        }
                                        ?>
                                    </select>
                                </div>
                                <div class="form-group col-md-6 col-12">
                                    <label for="" class="m-0">Razón social:<span class="obligatorio"> *</span></label>
                                    <select id="razon_cliente2" name="razon_cliente2" class="custom-select">
                                        <?php                        
                                        $query = $catalogo->getListaAlta("c_datosfacturacionempresa", "RazonSocial");
                                        //echo "<option value='' >Selecciona una opción</option>";
                                        while ($rs = mysql_fetch_array($query)) {
                                            $s = "";
                                            if ($cliente->getIdDatosFacturacionEmpresa() != "" && 
                                                    $cliente->getIdDatosFacturacionEmpresa() == $rs['IdDatosFacturacionEmpresa']) {
                                                $s = "selected";
                                            }
                                            echo "<option value=" . $rs['IdDatosFacturacionEmpresa'] . " " . $s . ">" . $rs['RazonSocial'] . "</option>";
                                        }
                                        ?>
                                    </select>
                                </div>
                            </div>
                            <div class="form-row">
                                <div class="form-group col-md-5 col-11">
                                    <label for="zona" class="m-0">Zona:<span class="obligatorio"> *</span></label>
                                    <select id="zona" name="zona" class="custom-select">
                                        <?php                        
                                        $query = $catalogo->getListaAlta("c_zona", "NombreZona");
                                        echo "<option value='' >Selecciona una zona</option>";
                                        while ($rs = mysql_fetch_array($query)) {
                                            $s = "";
                                            if ($cliente->getClaveZona() != "" && 
                                                    $cliente->getClaveZona() == $rs['ClaveZona']) {
                                                $s = "selected";
                                            }
                                            echo "<option value=" . $rs['ClaveZona'] . " " . $s . ">" . $rs['NombreZona'] . "</option>";
                                        }
                                        ?>
                                    </select>
                                </div>
                                <?php if ($permisos_grid->tienePermisoEspecial($_SESSION['idUsuario'], 7)): ?>
                                    <div class="form-group col-md-1 col-1">
                                        <a href="#" title="Alta cliente" onclick="lanzarPopUp('Alta cliente', '<?php echo $pagina_popup; ?>'); return false;">
                                            <img src="resources/images/client_icon.gif" width="28" height="28"/>
                                        </a>
                                    </div>
                                <?php endif; ?>
                                <div class="form-group col-md-6 col-12">
                                    <label for="" class="m-0"></label>
                                    <select id="cuentaBancaria" name="cuentaBancaria" class="custom-select">
                                        <?php                 
                                        if(isset($_GET['id']) && $_GET['id']!=""){
                                            $query = $catalogo->obtenerLista("SELECT *,b.Nombre from c_cuentaBancaria cb 
                                            LEFT JOIN c_banco AS b ON b.IdBanco = cb.idBanco WHERE cb.RFC = '".$cliente->getIdDatosFacturacionEmpresa()."' ORDER BY noCuenta;");
                                        }else{
                                            $query = $catalogo->obtenerLista("SELECT *,b.Nombre from c_cuentaBancaria cb 
                                                LEFT JOIN c_banco AS b ON b.IdBanco = cb.idBanco ORDER BY noCuenta;");
                                        }
                                        echo "<option value='' >Ninguna cuenta</option>";
                                        while ($rs = mysql_fetch_array($query)) {
                                            $s = "";
                                            if ($cliente->getIdCuentaBancaria() != "" && 
                                                    $cliente->getIdCuentaBancaria() == $rs['idCuentaBancaria']) {
                                                $s = "selected";
                                            }
                                            echo "<option value=" . $rs['idCuentaBancaria'] . " " . $s . ">" . $rs['noCuenta'] . "</option>";
                                        }
                                        ?>
                                    </select>
                                </div>
                            </div>
                            <div class="form-row">
                                <div class="form-group col-md-6 col-12">
                                    <label for="referenciaNum" class="m-0">Referencia Numerica:</label>
                                    <input type="text" id="referenciaNum" name="referenciaNum" value="<?php if ($cliente->getReferenciaNumerica() != "") echo $cliente->getReferenciaNumerica(); ?> ">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="mb-4 card border-secondary">                
                    <a id="clientes_header" class="card-header p-3" role="button" data-toggle="collapse" data-target="#clientes_card" aria-expanded="true" aria-controls="clientes_card">
                        <h5 class="mt-1 mb-0 text-primary">Domicilio/Domicilio Fiscal</h5>
                    </a>  

                    <div id="clientes_card" class="collapse show" aria-labelledby="clientes_header" data-parent="#accordion">
                        <div class="card-body">
                            <div class="form-row">
                                <div class="form-group col-md-6 col-12">
                                    <label for="TipoDomicilioF" class="m-0">Tipo de domicilio:</label>
                                    <input name="TipoDomicilioF" type="text" value="Domicilio cliente (fiscal)" maxlength="50" id="TipoDomicilioF" disabled="disabled" class="form-control"/>
                                    <span id="MainContent_reqValTipoDomicilio" style="display:none;"></span>
                                </div>
                                <div class="form-group col-md-6 col-12">
                                    <label for="CalleF" class="m-0">Calle:<span class="obligatorio"> *</span></label>
                                    <input name="CalleF" type="text" maxlength="100" id="CalleF" value="<?php if ($cliente->getCalleF() != "") echo $cliente->getCalleF(); ?>" class="form-control"/>
                                    <span id="Calle" style="display:none;"></span>
                                </div>
                            </div>
                            <div class="form-row">
                                <div class="form-group col-md-6 col-12">
                                    <label for="NoExteriorF" class="m-0">No. exterior:<span class="obligatorio"> *</span></label>
                                    <input name="NoExteriorF" type="text" maxlength="30" id="NoExteriorF" value="<?php if ($cliente->getNoExtF() != "") echo $cliente->getNoExtF(); ?>" class="form-control"/>
                                    <span id="MainContent_reqValNoExterior" style="display:none;"></span>
                                </div>
                                <div class="form-group col-md-6 col-12">
                                    <label for="NoInteriorF" class="m-0">No. interior:</label>
                                    <input name="NoInteriorF" type="text" maxlength="30" id="NoInteriorF" value="<?php if ($cliente->getNoIntF() != "") echo $cliente->getNoIntF();?>" class="form-control"/>
                                </div>
                            </div>
                            <div class="form-row">
                                <div class="form-group col-md-4 col-12">
                                    <label for="ColoniaF" class="m-0">Colonia:<span class="obligatorio"> *</span></label>
                                    <input name="ColoniaF" type="text" maxlength="50" id="ColoniaF" value="<?php if ($cliente->getColoniaF() != "") echo $cliente->getColoniaF(); ?>" class="form-control"/>
                                    <span id="MainContent_reqValColonia" style="display:none;"></span>
                                </div>
                                <div class="form-group col-md-4 col-12">
                                    <label for="CiudadF" class="m-0">Ciudad:<span class="obligatorio"> *</span></label>
                                    <input name="CiudadF" type="text" maxlength="50" id="CiudadF" value="<?php if ($cliente->getCiudadF() != "") echo $cliente->getCiudadF(); ?>" class="form-control" />
                                    <span id="MainContent_reqValCiudad" style="display:none;"></span>
                                </div>
                                <div class="form-group col-md-4 col-12">
                                    <label for="EstadoF" class="m-0">Estado:<span class="obligatorio"> *</span></label>
                                    <select name="EstadoF" id="EstadoF" class="custom-select">
                                        <?php
                                            $nombres = Array("Selecciona un estado", "Aguascalientes", "Baja California", "Baja California Sur", "Campeche", "Ciudad de México","Coahuila", "Colima", "Chiapas", "Chihuahua", "Durango", "Estado de México", "Guanajuato", "Guerrero", "Hidalgo", "Jalisco", "Michoacán", "Morelos", "Nayarit", "Nuevo León", "Oaxaca", "Puebla", "Querétaro", "Quintana Roo", "San Luis Potosí", "Sinaloa", "Sonora", "Tabasco", "Tamaulipas", "Tlaxcala", "Veracruz", "Yucatán", "Zacatecas");
                                            $values = Array("", "Aguascalientes", "Baja California", "Baja California Sur", "Campeche", "Ciudad de México","Coahuila", "Colima", "Chiapas", "Chihuahua",  "Durango", "Estado de México", "Guanajuato", "Guerrero", "Hidalgo", "Jalisco", "Michoacán", "Morelos", "Nayarit", "Nuevo León", "Oaxaca", "Puebla", "Querétaro", "Quintana Roo", "San Luis Potosí", "Sinaloa", "Sonora", "Tabasco", "Tamaulipas", "Tlaxcala", "Veracruz", "Yucatán", "Zacatecas");
                                            if ($cliente->getEstadoF() != "") {
                                                for ($var = 0; $var < count($values); $var++) {
                                                    if ($values[$var] == $cliente->getEstadoF()) {
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
                                    <span id="MainContent_reqVEstado" style="display:none;"></span>
                                </div>
                            </div>
                            <div class="form-row">
                                <div class="form-group col-md-4 col-12">
                                    <label for="DelegacionF" class="m-0">Delegación:<span class="obligatorio"> *</span></label>
                                    <input name="DelegacionF" type="text" maxlength="50" id="DelegacionF" value="<?php if ($cliente->getDelegacionF() != "") echo $cliente->getDelegacionF(); ?>" class="form-control" />
                                    <span id="MainContent_reqValDelegacion" style="display:none;"></span>
                                </div>
                                <div class="form-group col-md-4 col-12">
                                    <label for="CPF" class="m-0">C.P:<span class="obligatorio"> *</span></label>
                                    <input name="CPF" type="text" maxlength="30" id="CPF" value="<?php if ($cliente->getCPF() != "") echo $cliente->getCPF(); ?>" class="form-control"/>
                                    <span id="MainContent_reqValCP" style="display:none;"></span>
                                </div>
                                <div class="form-group col-md-4 col-12">
                                    <label for="LocalidadF" class="m-0">Localidad</label>
                                    <input name="LocalidadF" type="text" maxlength="30" id="LocalidadF" value="<?php if ($cliente->getLocalidad() != "") echo $cliente->getLocalidad(); ?>" class="form-control"/>
                                </div>
                            </div>
                            <div class="form-row">
                                <div class="form-group col-md-4 col-12">
                                    <label for="latitud" class="m-0">Latitud:</label>
                                    <input type="text" id='latitud' name='latitud' value='<?php if($cliente->getLatitud()!=null) echo $cliente->getLatitud(); ?>' class="form-control"/>
                                </div>
                                <div class="form-group col-md-4 col-12">
                                    <label for="longitud" class="m-0">Longitud:</label>
                                    <input type="text" id='longitud' name='longitud' value='<?php if($cliente->getLongitud()!=null) echo $cliente->getLongitud();?>' class="form-control"/>
                                </div>
                            </div> 
                        </div>
                    </div>
                </div>
            
                <div class="mb-4 card border-secondary">                
                    <a id="clientes_header" class="card-header p-3" role="button" data-toggle="collapse" data-target="#clientes_card" aria-expanded="true" aria-controls="clientes_card">
                        <h5 class="mt-1 mb-0 text-primary">Datos de la facturacion</h5>
                    </a>  
                    <div id="clientes_card" class="collapse show" aria-labelledby="clientes_header" data-parent="#accordion">
                        <div class="card-body">

                            <div class="form-row">
                                <div class="form-group col-md-4 col-12">
                                    <label for="RFCD" class="m-0">RFC:<span class="obligatorio"> *</span></label>
                                    <input name="RFCD" type="text" maxlength="50" id="RFCD" value="<?php if ($cliente->getRFCD() != "") echo $cliente->getRFCD(); ?>" class="form-control" />
                                    <span id="MainContent_reqValRFC" style="display:none;"></span>
                                    <span id="MainContent_regValRFC" style="display:none;"></span>
                                </div>
                                <div class="form-group col-md-4 col-12">
                                    <label for="CorreoE1D" class="m-0">Correo electrónico 1 para envío de factura:</label>
                                    <input name="CorreoE1D" type="text" maxlength="50" id="CorreoE1D" value="<?php if ($cliente->getCorreoE1D() != "") echo $cliente->getCorreoE1D(); ?>" class="form-control"/>
                                    <span id="MainContent_reqValCorreoE1" style="display:none;"></span>
                                </div>
                                <div class="form-group col-md-4 col-12">
                                    <label for="CorreoE2D" class="m-0">Correo electrónico 2 para envío de factura:</label>
                                    <input name="CorreoE2D" type="text" maxlength="50" id="CorreoE2D" value="<?php if ($cliente->getCorreoE2D() != "") echo $cliente->getCorreoE2D();?>" class="form-control"/>
                                </div>
                            </div>
                            <div class="form-row">
                                <div class="form-group col-md-4 col-12">
                                    <label for="CorreoE3D" class="m-0">Correo electrónico 3 para envío de factura:</label>
                                    <input name="CorreoE3D" type="text" maxlength="50" id="CorreoE3D" value="<?php if ($cliente->getCorreoE3D() != "") echo $cliente->getCorreoE3D(); ?>" class="form-control"/>
                                </div>
                                <div class="form-group col-md-4 col-12">
                                    <label for="CorreoE4D" class="m-0">Correo electrónico 4 para envío de factura:</label>
                                    <input name="CorreoE4D" type="text" maxlength="50" id="CorreoE4D" value="<?php if ($cliente->getCorreoE4D() != "") echo $cliente->getCorreoE4D(); ?>" class="form-control"/>
                                </div>
                                <div class="form-group col-md-4 col-12">
                                    <label for="addenda_cliente" class="m-0">Selecciona la addenda</label>
                                    <select id="addenda_cliente" name="addenda_cliente" class="custom-select">
                                        <?php                   
                                            $query = $catalogo->getListaAlta("c_addenda", "nombre_addenda");
                                            echo "<option value=''>Sin addenda</option>";
                                            while ($rs = mysql_fetch_array($query)) {
                                                $s = "";
                                                if ($cliente->getIdAddenda() != "" && 
                                                        $cliente->getIdAddenda() == $rs['id_addenda']) {
                                                    $s = "selected";
                                                }
                                                echo "<option value=" . $rs['id_addenda'] . " " . $s . ">" . $rs['nombre_addenda'] . "</option>";
                                            }
                                        ?>
                                    </select>
                                </div>
                            </div>
                            <div class="form-row">
                                <div class="form-group col-md-4 col-12">
                                    <input type="checkbox" id="mostrar_pdf" name="mostrar_pdf" class="form-check-input" <?php echo $cliente->getMostrarAddenda() == "1" ? "checked='checked'" : "" ; ?>/>
                                    <label for="checkbox" class="m-0">Mostrar addenda en representación impresa</label>
                                </div>
                                <div class="form-group col-md-4 col-12">
                                    <input type="checkbox" id="condiciones_pago" name="condiciones_pago" class="form-check-input" <?php echo $cliente->getMostarCondicionesPago() == "1" ? "checked='checked'" : "" ; ?>/>
                                    <label for="condiciones_pago" class="m-0">Mostrar condiciones de pago</label>
                                </div>
                                <div class="form-group col-md-4 col-12">
                                    <input type="checkbox" name="verPDF" id="verPDF" class="form-check-input" <?php if ($cliente->getVerCClientePDF() == "1") {
                                        echo "checked='checked'";
                                    } elseif ($cliente->getVerCClientePDF() == "0" || is_null($cliente->getVerCClientePDF())) {
                                        echo "";
                                    } else {
                                        echo "checked='checked'";
                                    } ?> value="1"/>
                                    <label for="verPDF" class="m-0">Ver No. Cuenta en PDF</label>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="mb-4 card border-secondary">                
                    <a id="clientes_header" class="card-header p-3" role="button" data-toggle="collapse" data-target="#clientes_card" aria-expanded="true" aria-controls="clientes_card">
                        <h5 class="mt-1 mb-0 text-primary">Datos sociales</h5>
                    </a>  
                    <div id="clientes_card" class="collapse show" aria-labelledby="clientes_header" data-parent="#accordion">
                        <div class="card-body">
                            <div class="form-row">
                                <div class="form-group col-md-4 col-12">
                                    <label for="telefono" class="m-0">Tel&eacute;fono:</label>
                                    <input type="text" id="telefono" name="telefono" value="<?php if($cliente->getTelefono() != "") echo $cliente->getTelefono();?>" class="form-control"/>
                                </div>
                                <div class="form-group col-md-4 col-12">
                                    <label for="correo" class="m-0">Correo electr&oacute;nico:</label>
                                    <input type="text" id="correo" name="correo" value="<?php if($cliente->getEmail() != "") echo $cliente->getEmail();?>" class="form-control"/>
                                </div>
                                <div class="form-group col-md-4 col-12">
                                    <label for="horario" class="m-0">Horario:</label>
                                    <input type="text" id="horario" name="horario" value="<?php if($cliente->getHorario() != "") echo $cliente->getHorario();?>" class="form-control"/>
                                </div>
                            </div>
                            <div class="form-row">
                                <div class="form-group col-md-4 col-12">
                                    <label for="sitio_web">SitioWeb:</label>
                                    <input type="text" id="sitio_web" name="sitio_web" value="<?php if($cliente->getSitioweb() != "") echo $cliente->getSitioweb();?>"  class="form-control"/>
                                </div>
                                <div class="form-group col-md-4 col-12">
                                    <label for="facebook">
                                        <?php if($cliente->getFacebook() != ""){ echo "<a href='".$cliente->getFacebook()."' target='_blank'>"; }?> 
                                            <img src="resources/images/facebook.png" title="Facebook" style="width: 24px; height: 24px;">
                                        <?php if($cliente->getFacebook() != ""){ echo "</a>"; }?> 
                                        <input type="text" id="facebook" name="facebook" value="<?php if($cliente->getFacebook() != "") echo $cliente->getFacebook();?>" class="form-control"/>
                                    </label>
                                </div>
                                <div class="form-group col-md-4 col-12">
                                    <?php if($cliente->getTwitter() != ""){ echo "<a href='".$cliente->getTwitter()."' target='_blank'>"; }?> 
                                        <img src="resources/images/Twitter-icon.png" title="Twitter" style="width: 30px; height: 30px;">            
                                    <?php if($cliente->getTwitter() != ""){ echo "</a>"; }?> 
                                    <input type="text" id="twitter" name="twitter" value="<?php if($cliente->getTwitter() != "") echo $cliente->getTwitter();?>" class="form-control"/>
                                </div>
                            </div>
                            <div class="form-row">
                                <div class="form-group col-md-4 col-12">
                                    <input id="chkActivo" type="checkbox" name="chkActivo" class="form-check-input" <?php if ($cliente->getActivo() == "1") {
                                        echo "checked='checked'";
                                    } elseif ($cliente->getActivo() == "0") {
                                        echo "";
                                    } else {
                                        echo "checked='checked'";
                                    } ?> value="1"/><label for="chkActivo">Activo</label>
                                </div>
                                <div class="form-group col-md-4 col-12">
                                    <input type="button" onclick="cambiarContenidos('<?php echo $regresar?>', 'Mis Clientes'); return false;" class="btn btn-secondary" name="Cancelar" value="Cancelar" id="Cancelar" />
                                    <?php if($permisos_grid->getModificar()): ?>
                                        <input type="submit" class="btn btn-secondary" name="Guardar" value="Guardar"  id="Guardar" />
                                    <?php endif; ?>
                                </div>
                                
                            </div>
                        </div>
                    </div>    
                </div>
            </div>
            <?php
                if (isset($_GET['id']) && $_GET['id'] != "") {
                    ?>
                    <input type="hidden" value="<?php echo $_GET['id'];?>" id="id" name="id"/>
                    <input type="hidden" value="<?php echo $cliente->getIdDomicilio();?>" id="domicilioid" name="domicilioid"/>
                    <?php
                }
            ?>
            <input type="hidden" id="numero_categoria" name="numero_categoria" value="<?php echo $numero; ?>"/>
            <input type="hidden" id="regresar" value="<?php echo $regresar?>">
        </form> 
    </body>
</html>