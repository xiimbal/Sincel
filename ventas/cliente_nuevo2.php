<?php
session_start();

if (!isset($_SESSION['user']) || $_SESSION['user'] == "") {
    header("Location: ../index.php");
}
include_once("../WEB-INF/Classes/ccliente.class.php");
include_once("../WEB-INF/Classes/Catalogo.class.php");
include_once("../WEB-INF/Classes/Usuario.class.php");
include_once("../WEB-INF/Classes/PermisosSubMenu.class.php");

$usuario = new Usuario();
$cliente = new ccliente();
$catalogo = new Catalogo();
$permisos_grid = new PermisosSubMenu();

$pagina_lista = "ventas/mis_clientes.php";
$permisos_grid->getPermisosSubmenu($_SESSION['idUsuario'], $pagina_lista);

/*Datos fiscales para clientes de venta*/
$FormaPago = "";
$MetodoPago = "";
$numero_cuenta = "";
$idBanco = "";
$idFormaComprobante = "";
$idUsoCFDI = "";
$idCuentaBancaria = "";
$dias_credito = "";

$pagina_popup = "cliente/alta_cliente.php";
if (isset($_GET['id']) && $_GET['id'] != "") {
    $cliente->getregistrobyID($_GET['id']);
    $pagina_popup = "cliente/alta_cliente.php?ClaveCliente=" . $_GET['id'];
    if($cliente->getModalidad() == "3" && $cliente->getDatosFiscalesVenta()){//Si es cliente de venta
        $FormaPago = $cliente->getFormaPago();
        $MetodoPago = $cliente->getMetodoPago();
        $numero_cuenta = $cliente->getNumero_cuenta();
        $idBanco = $cliente->getIdBanco();
        $idFormaComprobante = $cliente->getIdFormaComprobante();
        $idUsoCFDI = $cliente->getIdUsoCFDI();
        $idCuentaBancaria = $cliente->getIdCuentaBancaria();
        $dias_credito = $cliente->getDias_credito();
    }
}

$regresar = "ventas/mis_clientes.php";
if (isset($_GET['regresar']) && $_GET['regresar'] != "") {
    $regresar = $_GET['regresar'];
}

?>
<html>
    <head>
        <script type="text/javascript" language="javascript" src="resources/js/paginas/ventas/nuevo_cliente.js"></script>
        <style>

            .sizeMedio{width: 220px;}
        </style>

        <link href="resources/css/Bootstrap 4/bootstrap.min.css" rel="stylesheet">
        <link href="resources/css/Bootstrap 4/fontawesome/all.min.css" rel="stylesheet">

    </head>
    <body>

       
        <form id="formcliente">
           <div class="container-fluid">
           
                <tr style="display: none;">
                    <div class="form-row">

                       <div class="form-group col-md-4"> 
                    <label> Estatus de cobranza:</label>
                        <select class="form-control" name="EstatusCobranza" id="EstatusCobranza" class="sizeMedio">
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

                     <div class="form-group">
                    <label>Ejecutivo Cuenta:<span class="obligatorio">*</span></label>
                        <?php
                        if (!$usuario->isUsuarioPuesto($_SESSION['idUsuario'], 11)) {
                            echo '<select class="form-control" name="ejecutivocuenta" id="ejecutivocuenta"  class="sizeMedio">';
                            echo '<option  value="">Seleccione el vendedor</option>';
                            $consulta = "SELECT DISTINCT u.IdUsuario AS IdUsuario,CONCAT(u.Nombre,' ',u.ApellidoPaterno,' ',u.ApellidoMaterno) AS Nombre 
                                FROM c_usuario AS u
                                WHERE IdPuesto = 11 AND Activo = 1 ORDER BY Nombre;";
                            $query = $catalogo->obtenerLista($consulta);
                            while ($rs = mysql_fetch_array($query)) {
                                if ($rs['IdUsuario'] == $cliente->getEjecutivoCuenta()) {
                                    echo "<option value=\"" . $rs['IdUsuario'] . "\" selected>" . $rs['Nombre'] . "</option>";
                                } else {
                                    echo "<option value=\"" . $rs['IdUsuario'] . "\">" . $rs['Nombre'] . "</option>";
                                }
                            }
                            echo "</select>";
                        } else {
                            if ($usuario->getRegistroById($_SESSION['idUsuario'])) {
                                echo "<input class='form-control' type='hidden' id='ejecutivocuenta' name='ejecutivocuenta' value='" . $usuario->getId() . "' />";
                                echo $usuario->getNombre() . " " . $usuario->getPaterno() . " " . $usuario->getMaterno();
                            }
                        }
                        ?>                    
                        <span id="MainContent_reqValEstatusCobranza" ></span>
                    </div>   
         

                  <div class="form-group col-md-4">
                    <label>Calificación:</label>
                        <select class="form-control" id="calificacion" name="calificacion" class="sizeMedio">
                            <option value=''>Califica al cliente</option>
                            <?php
                            for ($i = 0; $i <= 10; $i++) {
                                $s = "";
                                if ($cliente->getCalificacion() != "" && $cliente->getCalificacion() == $i) {
                                    $s = "selected";
                                }
                                echo "<option value='$i' $s>$i</option>";
                            }
                            ?>
                        </select>
                </div>


                <div class="form-group col-md-4">
                    <label>Ejecutivo Atención a Cliente:</label>   
                        <?php
                        echo '<select class="form-control" name="ejecutivoatencion" id="ejecutivoatencion"  class="sizeMedio">';
                        echo '<option value="">Seleccione el ejecutivo</option>';
                        $consulta = "SELECT DISTINCT u.IdUsuario AS IdUsuario,CONCAT(u.Nombre,' ',u.ApellidoPaterno,' ',u.ApellidoMaterno) AS Nombre,
                            Loggin
                            FROM c_usuario AS u
                            WHERE Activo = 1 ORDER BY Nombre;";
                        $query = $catalogo->obtenerLista($consulta);
                        while ($rs = mysql_fetch_array($query)) {
                            if ($rs['IdUsuario'] == $cliente->getEjecutivoAtencionCliente()) {
                                echo "<option value=\"" . $rs['IdUsuario'] . "\" selected>" . $rs['Nombre'] . " (" . $rs['Loggin'] . ")</option>";
                            } else {
                                echo "<option value=\"" . $rs['IdUsuario'] . "\">" . $rs['Nombre'] . " (" . $rs['Loggin'] . ")</option>";
                            }
                        }
                        echo "</select>";
                        ?>                    
                        <span id="MainContent_reqValEstatusCobranza"></span>
                </div>


                <div class="form-group col-md-4">
                    <label> Nombre:<span class="obligatorio"> *</span></label>
                        <input class="form-control" name="RazonSocial" type="text" maxlength="150" id="RazonSocial" value="<?php
                        if ($cliente->getRazonSocial() != "") {
                            echo $cliente->getRazonSocial();
                        }
                        ?>"  class="sizeMedio" /> <span id="MainContent_reqValRazonSocial" style="display:none;"></span>
                   
                </div>

                <div class="form-group col-md-4">
                    <label>Comentario:</label>
                        <textarea id='comentario' name='comentario' style='resize: none;' class="form-control rounded-0">
                            <?php
                            if ($cliente->getComentario() != "") {
                                echo $cliente->getComentario();
                            }
                            ?>
                        </textarea>
                </div>


                <div class="form-group col-md-4">
                    <label>Tipo:</label>
                        <select  class="form-control" name="TipoCliente" id="TipoCliente"  class="sizeMedio">
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
                        </select><span id="MainContent_reqValTipoCliente" style="display:none;"></span>
                    </div>

                   <div class="form-group col-md-4">
                    <label>Imagen:</label>
                        <input class="form-control" type='hidden' id='imagen_url' name='imagen_url' maxlength="200" class="sizeMedio" value='<?php
                        if ($cliente->getImagen() != null) {
                            echo $cliente->getImagen();
                        }
                        ?>'>                                            
                               <?php
                               if ($cliente->getImagen() != null) {
                                   echo "<br/><br/><input type='image' src='" . $cliente->getImagen() . "' onclick='return false;' style='width: 100px; height:100px;'/>";
                               }
                               ?>
                 </div>



                <div class="form-group col-md-4">
                    <label for="modalidad2">Tipo cliente:</label>                     
                        <select class="form-control" id="modalidad2" name="modalidad2" class="sizeMedio" onchange="mostrarDatosDeVentas();">
                            <?php
                            $query = $catalogo->getListaAltaTodo("c_clientemodalidad", "Nombre");
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


                <div class="form-group col-md-4">
                    <label>Giro:</label>
                        <select class="form-control" id="Giro" name="Giro" class="sizeMedio">
                            <?php
                            $query = $catalogo->getListaAltaTodo("c_giro", "Nombre");
                            $array_giros = array();
                            while ($rs = mysql_fetch_array($query)) {
                                $s = "";
                                if ($cliente->getGiro() != "" && $cliente->getGiro() == $rs['IdGiro']) {
                                    $s = "selected";
                                }
                                $array_giros[$rs['IdGiro']] = $rs['Nombre'];
                                echo "<option value=" . $rs['IdGiro'] . " " . $s . ">" . $rs['Nombre'] . "</option>";
                            }
                            ?>
                        </select>
                        <span id="MainContent_reqValGiro" style="display:none;"></span>
                </div> 


                <div class="form-group col-md-4">
                    <label for="zona">Zona:<span class="obligatorio"> *</span></label>
                        <select class="form-control" id="zona" name="zona" class="sizeMedio">
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


                <div class="form-group col-md-4">
                    <label for="grupo">Grupo:</label>
                        <select  class="form-control" id="grupo" name="grupo" class="sizeMedio">
                            <?php
                            $query = $catalogo->getListaAlta("c_clientegrupo", "Nombre");
                            echo "<option value='' >Ningún grupo</option>";
                            while ($rs = mysql_fetch_array($query)) {
                                $s = "";
                                if ($cliente->getClaveGrupo() != "" &&
                                        $cliente->getClaveGrupo() == $rs['ClaveGrupo']) {
                                    $s = "selected";
                                }
                                echo "<option value=" . $rs['ClaveGrupo'] . " " . $s . ">" . $rs['Nombre'] . "</option>";
                            }
                            ?>
                        </select>
                        <?php if ($permisos_grid->tienePermisoEspecial($_SESSION['idUsuario'], 7)) {//Si tiene el permiso especial de alta cliente  ?>
                            <a href="#" title="Alta cliente" onclick="lanzarPopUp('Alta cliente', '<?php echo $pagina_popup; ?>');
                                        return false;"><img src="resources/images/client_icon.gif" width="28" height="28"/></a>
                           <?php } ?>
                </div>



                <div class="form-group col-md-4">
                    <label>Cuenta Bancaria: </label>
                        <select class="form-control" id="cuentaBancaria" name="cuentaBancaria" class="sizeMedio">
                            <?php
                            if (isset($_GET['id']) && $_GET['id'] != "") {
                                $query = $catalogo->obtenerLista("SELECT *,b.Nombre from c_cuentaBancaria cb 
                            LEFT JOIN c_banco AS b ON b.IdBanco = cb.idBanco WHERE cb.RFC = '" . $cliente->getIdDatosFacturacionEmpresa() . "' ORDER BY noCuenta;");
                            } else {
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
                   
                   <div class="form-group col-md-4">
                    <label>Referencia Numerica:</label>
                    <input class="form-control" type="text" id="referenciaNum" name="referenciaNum" value="<?php
                        if ($cliente->getReferenciaNumerica() != "") {
                            echo $cliente->getReferenciaNumerica();
                        }
                        ?> "/>
                 </div>
            
            
           
            <div class="form-row">
                <legend>Datos Fiscales</legend>
                <div class="form-group col-md-4">
                    <label for="razon_cliente2">Razón social:<span class="obligatorio"> *</span></label>
                            <select class="form-control" id="razon_cliente2" name="razon_cliente2" class="sizeMedio">
                                <?php
                                $query = $catalogo->getListaAlta("c_datosfacturacionempresa", "RazonSocial");
                                echo "<option value='' >Selecciona una opción</option>";
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

                    <div class="form-group col-md-4">
                        <label>RFC:<span class="obligatorio"> *</span></label>
                            <input class="form-control" name="RFCD" type="text" maxlength="50" id="RFCD" value="<?php
                            if ($cliente->getRFCD() != "") {
                                echo $cliente->getRFCD();
                            }
                            ?>" class="sizeMedio" />                                        
                    </div>


                    <div class="form-group col-md-4">
                        <label for="forma_pago">Forma de pago:</label><span class="obligatorio"> *</span>
                            <select class="form-control" id="forma_pago" name="forma_pago" class="sizeMedio">
                                <option value="">Selecciona la forma de pago</option><?php
                                $result = $catalogo->getListaAlta("c_formapago", "Nombre");
                                while ($rs = mysql_fetch_array($result)) {
                                    $s = ($rs['IdFormaPago'] == $FormaPago) ? "selected" : "";
                                    echo "<option value='" . $rs['IdFormaPago'] . "' $s>" . $rs['Nombre'] . " - " . $rs['Descripcion'] . "</option>";
                                }
                                ?>
                            </select>
                        </div>


                     <div class="form-group col-md-4">
                        <label for="metodo_pago">Método de pago:</label><span class="obligatorio"> *</span>
                            <select class="form-control" id="metodo_pago" name="metodo_pago" class="sizeMedio">
                                <option value="">Selecciona el método de pago</option><?php
                                $result = $catalogo->getListaAlta("c_metodopago", "ClaveMetodoPago");
                                while ($rs = mysql_fetch_array($result)) {
                                    $s = ($rs['IdMetodoPago'] == $MetodoPago) ? "selected" : "";
                                    echo "<option value='" . $rs['IdMetodoPago'] . "' $s>" . $rs['ClaveMetodoPago'] . " " . $rs['MetodoPago'] . "</option>";
                                }
                                ?>
                            </select>
                        </div>

                     <div class="form-group col-md-4">
                        <label>Uso CFDI<span class="obligatorio">*</span></label>
                            <select class="form-control" id="usoCFDI" name="usoCFDI" class="sizeMedio">
                                <?php
                                $result = $catalogo->getListaAlta("c_usocfdi", "ClaveCFDI");
                                echo "<option value=''>Selecciona una opción</option>";
                                while ($rs = mysql_fetch_array($result)) {
                                    $s = "";
                                    if ($idUsoCFDI == (int) $rs['IdUsoCFDI']) {
                                        $s = "selected";
                                    }
                                    echo "<option value='" . $rs['IdUsoCFDI'] . "' $s>" . $rs['ClaveCFDI'] . " " . $rs['Descripcion'] . "</option>";
                                }
                                ?>
                            </select>
                    </div>


                     <div class="form-group col-md-4">
                        <label for="numero_cuenta">Número Cuenta:</label>
                            <input class="form-control" type="number" id="numero_cuenta" name="numero_cuenta" class="sizeMedio" value="<?php echo $numero_cuenta; ?>"/>     
                            </div>  


                <div class="form-group col-md-4">
                    <label  for="banco">Banco</label>
                            <select class="form-control" id="banco" name="banco" class="sizeMedio">
                                <option class="form-control" value="">Seleccione un banco</option>
                                <?php
                                $result = $catalogo->getListaAlta("c_banco", "Nombre");
                                while ($rs = mysql_fetch_array($result)) {
                                    $s = "";
                                    if ($rs['IdBanco'] == $idBanco) {
                                        $s = "selected";
                                    }
                                    echo "<option value='" . $rs['IdBanco'] . "' $s>" . $rs['Nombre'] . " - " . $rs['RFC'] . "</option>";
                                }
                                ?>
                            </select>
                        </div>

                        <div class="form-group  col-md-4"">
                        <label for="dias_credito">D&iacute;as de cr&eacute;dito:</label>
                            <input class="form-control" type="text" id="dias_credito" name="dias_credito" class="sizeMedio" value="<?php echo $dias_credito; ?>"/>                            
                    </div>
                


                   
                            
                                <legend>Complemento de pago</legend>
                                <div class="form-group col-md-4" >
                                        <label  for="forma_pago_complemento">Forma de pago:</label>
                                            <select class="form-control" id="forma_pago_complemento" name="forma_pago_complemento" class="sizeMedio">
                                                <option value="">Selecciona la forma de pago</option><?php
                                                $result = $catalogo->getListaAlta("c_formapago", "Nombre");
                                                while ($rs = mysql_fetch_array($result)) {
                                                    $s = "";
                                                    if ($rs['IdFormaPago'] == $idFormaComprobante) {
                                                        $s = "selected";
                                                    }
                                                    echo "<option value='" . $rs['IdFormaPago'] . "' $s>" . $rs['Nombre'] . " - " . $rs['Descripcion'] . "</option>";
                                                }
                                                ?>
                                            </select>
                                       </div>


                                    <div class="form-group col-md-4">
                                        <label  for="banco">Cuenta Bancaria Beneficiaria</label>
                                            <select class="form-control" id="cuenta_bancaria" name="cuenta_bancaria">
                                                <option value="">Seleccione una cuenta bancaria</option>
                                                <?php
                                                    $consulta = "SELECT cb.idCuentaBancaria, cb.noCuenta, cb.tipoCuenta, b.Nombre AS Banco
                                                        FROM c_cuentaBancaria AS cb
                                                        LEFT JOIN c_banco AS b ON b.IdBanco = cb.idBanco                                                        
                                                        ORDER BY Banco, noCuenta;";
                                                $result = $catalogo->obtenerLista($consulta);
                                                while ($rs = mysql_fetch_array($result)) {
                                                    $s = "";
                                                    if ($rs['idCuentaBancaria'] == $idCuentaBancaria) {
                                                        $s = "selected";
                                                    }
                                                    echo "<option value='" . $rs['idCuentaBancaria'] . "' $s>" . $rs['Banco'] . " - " . $rs['noCuenta'] . " - " . $rs['tipoCuenta'] . "</option>";
                                                }
                                                ?>
                                            </select>                                                              
                                        </div>
                                 
           
          
           



                <legend>Domicilio/Domicilio fiscal</legend>
                <div class="form-group col-md-4">
                    
                        <label>Tipo de domicilio:</label>
                            <input class="form-control" name="TipoDomicilioF" type="text" value="Domicilio cliente (fiscal)" maxlength="50" id="TipoDomicilioF" disabled="disabled" class="aspNetDisabled" />
                        </div>
                    

                        <div class="form-group  col-md-4">
                            <label>Calle:<span class="obligatorio"> *</span></label>
                            <input class="form-control" name="CalleF" type="text" maxlength="100" id="CalleF" value="<?php
                            if ($cliente->getCalleF() != "") {
                                echo $cliente->getCalleF();
                            }
                            ?>" />
                            <span id="Calle" style="display:none;"></span>
                        </div>


                    <div class="form-group  col-md-4">
                        <label>No. exterior:<span class="obligatorio"> *</span></label> 
                        <input class="form-control" name="NoExteriorF" type="text" maxlength="30" id="NoExteriorF" value="<?php
                            if ($cliente->getNoExtF() != "") {
                                echo $cliente->getNoExtF();
                            }
                            ?>" />
                            <span id="MainContent_reqValNoExterior" style="display:none;"></span>
                        </div>


                    <div class="form-group col-md-4">
                        <label>No. interior:</label>
                            <input class="form-control" name="NoInteriorF" type="text" maxlength="30" id="NoInteriorF" value="<?php
                            if ($cliente->getNoIntF() != "") {
                                echo $cliente->getNoIntF();
                            }
                            ?>" />
                        </div>



                
                    
                        <div class="form-group col-md-4">
                            <label>Colonia:<span class="obligatorio"> *</span></label>
                            <input class="form-control" name="ColoniaF" type="text" maxlength="50" id="ColoniaF" value="<?php
                            if ($cliente->getColoniaF() != "") {
                                echo $cliente->getColoniaF();
                            }
                            ?>" />
                            <span id="MainContent_reqValColonia" style="display:none;"></span>
                        </div>



                        <div class="form-group  col-md-4">
                            <label>Ciudad:<span class="obligatorio"> *</span></label>
                            <input class="form-control" name="CiudadF" type="text" maxlength="50" id="CiudadF" value="<?php
                            if ($cliente->getCiudadF() != "") {
                                echo $cliente->getCiudadF();
                            }
                            ?>" />
                            <span id="MainContent_reqValCiudad" style="display:none;"></span>
                        </div>
                       
                    <div class="form-group col-md-4">
                        <label>Estado:<span class="obligatorio"> *</span></label>
                            <select class="form-control" name="EstadoF" id="EstadoF">
                                <?php
                                $nombres = Array("Selecciona un estado", "Aguascalientes", "Baja California", "Baja California Sur", "Campeche", "Ciudad de México", "Coahuila", "Colima", "Chiapas", "Chihuahua", "Durango", "Estado de México", "Guanajuato", "Guerrero", "Hidalgo", "Jalisco", "Michoacán", "Morelos", "Nayarit", "Nuevo León", "Oaxaca", "Puebla", "Querétaro", "Quintana Roo", "San Luis Potosí", "Sinaloa", "Sonora", "Tabasco", "Tamaulipas", "Tlaxcala", "Veracruz", "Yucatán", "Zacatecas");
                                $values = Array("", "Aguascalientes", "Baja California", "Baja California Sur", "Campeche", "Ciudad de México", "Coahuila", "Colima", "Chiapas", "Chihuahua", "Durango", "Estado de México", "Guanajuato", "Guerrero", "Hidalgo", "Jalisco", "Michoacán", "Morelos", "Nayarit", "Nuevo León", "Oaxaca", "Puebla", "Querétaro", "Quintana Roo", "San Luis Potosí", "Sinaloa", "Sonora", "Tabasco", "Tamaulipas", "Tlaxcala", "Veracruz", "Yucatán", "Zacatecas");
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


                    
                    <div class="form-group  col-md-4">
                        <label>Delegación:<span class="obligatorio">*</span></label>
                            <input class="form-control" name="DelegacionF" type="text" maxlength="50" id="DelegacionF" value="<?php
                            if ($cliente->getDelegacionF() != "") {
                                echo $cliente->getDelegacionF();
                            }
                            ?>" />
                            <span id="MainContent_reqValDelegacion" style="display:none;"></span>
                        </div>


                    <div class="form-group col-md-4">
                        <label>C.P:<span class="obligatorio">*</span></label>
                            <input class="form-control" name="CPF" type="text" maxlength="30" id="CPF" value="<?php
                            if ($cliente->getCPF() != "") {
                                echo $cliente->getCPF();
                            }
                            ?>" />
                            <span id="MainContent_reqValCP" style="display:none;"></span>
                            <span id="MainContent_regValCP" style="display:none;"></span>
                        </div>


                    <div class="form-group col-md-4">
                        <label>Localidad:</label>
                            <input  class="form-control" name="LocalidadF" type="text" maxlength="30" id="LocalidadF" value="<?php
                            if ($cliente->getLocalidad() != "") {
                                echo $cliente->getLocalidad();
                            }
                            ?>" />                        
                      </div>


                    <div class="form-group  col-md-4">
                        <label  for='latitud'>Latitud</label>
                            <input class="form-control" type="text" id='latitud' name='latitud' value='<?php
                            if ($cliente->getLatitud() != null) {
                                echo $cliente->getLatitud();
                            }
                            ?>'/>
                        </div>

                    <div class="form-group col-md-4">
                        <label  for='longitud'>Longitud</label>
                            <input class="form-control" type="text" id='longitud' name='longitud' value='<?php
                            if ($cliente->getLongitud() != null) {
                                echo $cliente->getLongitud();
                            }
                            ?>'/>
                     </div>
   


            
                        <legend>Datos de la Facturación</legend>                         
                                <div class="form-group col-md-4">
                                    <label>Correo electrónico 1 para envío de factura :</label> 
                                        <input class="form-control" name="CorreoE1D" type="text" maxlength="50" id="CorreoE1D" value="<?php
                                        if ($cliente->getCorreoE1D() != "") {
                                            echo $cliente->getCorreoE1D();
                                        }
                                        ?>"/>
                                        <span id="MainContent_reqValCorreoE1" ></span>
                                    </div>
                                     

                                <div class="form-group  col-md-4">
                                    <label>Correo electrónico 2 para envío de factura :</label>
                                        <input class="form-control" name="CorreoE2D" type="text" maxlength="50" id="CorreoE2D" value="<?php
                                        if ($cliente->getCorreoE2D() != "") {
                                            echo $cliente->getCorreoE2D();
                                        }
                                        ?>"/>
                                   </div>
                               

                                <div class="form-group  col-md-4">
                                    <label>Correo electrónico 3 para envío de factura :</label>
                                        <input class="form-control" name="CorreoE3D" type="text" maxlength="50" id="CorreoE3D" value="<?php
                                        if ($cliente->getCorreoE3D() != "") {
                                            echo $cliente->getCorreoE3D();
                                        }
                                        ?>" />
                                   </div>


                                 <div  class="form-group col-md-4">
                                    <label class="m-0" >Correo electrónico 4 para envío de factura :</label>
                                      <input class="form-control" name="CorreoE4D" type="text" maxlength="50" id="CorreoE4D" value="<?php
                                        if ($cliente->getCorreoE4D() != "") {
                                            echo $cliente->getCorreoE4D();
                                        }
                                        ?>"  />
                                   </div>
                                


                                <div class="form-group  col-md-4">
                                    <label>Selecciona la addenda</label>
                                        <select class="form-control"  id='addenda_cliente' name='addenda_cliente'>
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


                                <div class="form-group  col-md-4">
                                        <input  type='checkbox' id='mostrar_pdf' name='mostrar_pdf' 
                                        <?php
                                        if ($cliente->getMostrarAddenda() == "1") {
                                            echo "checked='checked'";
                                        } else {
                                            echo "";
                                        }
                                        ?>
                                           />
                                       <label >Mostrar addenda en representación impresa</label><br>
                                       <input  type='checkbox' id='condiciones_pago' name='condiciones_pago' 
                                        <?php
                                        if ($cliente->getMostarCondicionesPago() == "1") {
                                            echo "checked='checked'";
                                        } else {
                                            echo "";
                                        }
                                        ?>
                                               />
                                        <label >Mostrar condiciones de pago</label><br>
                                        <input  type="checkbox" name="verPDF" id="verPDF" <?php
                                        if ($cliente->getVerCClientePDF() == "1") {
                                            echo "checked='checked'";
                                        } elseif ($cliente->getVerCClientePDF() == "0" || is_null($cliente->getVerCClientePDF())) {
                                            echo "";
                                        } else {
                                            echo "checked='checked'";
                                        }
                                        ?> value="1"/>
                                        <label for="NoCuenta">Ver No. Cuenta en PDF</label><br>
                                </div>
          

           
                <legend>Datos sociales</legend>
                
                    <div class="form-group  col-md-4">
                        <label for="telefono">Tel&eacute;fono:</label>
                            <input class="form-control"type="text" id="telefono" name="telefono" value="<?php
                            if ($cliente->getTelefono() != "") {
                                echo $cliente->getTelefono();
                            }
                            ?>"/>
                        
                        </div>

                    <div class="form-group  col-md-4">
                        <label  for="correo">Correo electr&oacute;nico:</label>
                            <input class="form-control" type="text" id="correo" name="correo" value="<?php
                            if ($cliente->getEmail() != "") {
                                echo $cliente->getEmail();
                            }
                            ?>"/>
                    </div>



                    <div class="form-group col-md-4">                    
                        <label for="horario">Horario:</label>
                         <input class="form-control" type="text" id="horario" name="horario" value="<?php
                            if ($cliente->getHorario() != "") {
                                echo $cliente->getHorario();
                            }
                            ?>"/>
                        </div>

                     <div class="form-group  col-md-4"> 
                        <label  for="sitio_web">SitioWeb:</label>
                           <input class="form-control" type="text" id="sitio_web" name="sitio_web" value="<?php
                            if ($cliente->getSitioweb() != "") {
                                echo $cliente->getSitioweb();
                            }
                            ?>"/>                    
                    </div>
                

                    
                        <div class="form-group  col-md-4">
                            <label for="facebook">
                                <?php
                                if ($cliente->getFacebook() != "") {
                                    echo "<a href='" . $cliente->getFacebook() . "' target='_blank'>";
                                }
                                ?> 
                                <img src="resources/images/facebook.png" title="Facebook" style="width: 24px; height: 24px;">
                                <?php
                                if ($cliente->getFacebook() != "") {
                                    echo "</a>";
                                }
                                ?> 
                            </label><br>

                            <input class="form-control" type="text" id="facebook" name="facebook" value="<?php
                            if ($cliente->getFacebook() != "") {
                                echo $cliente->getFacebook();
                            }
                            ?>"/>
                          </div>

                        <div class="form-group  col-md-4">
                            <label for="twitter">
                                <?php
                                if ($cliente->getTwitter() != "") {
                                    echo "<a href='" . $cliente->getTwitter() . "' target='_blank'>";
                                }
                                ?> 
                                <img src="resources/images/Twitter-icon.png" title="Twitter" style="width: 30px; height: 30px;">            
                                <?php
                                if ($cliente->getTwitter() != "") {
                                    echo "</a>";
                                }
                                ?> 
                            </label><br>
                        
                            <input class="form-control" type="text" id="twitter" name="twitter" value="<?php
                            if ($cliente->getTwitter() != "") {
                                echo $cliente->getTwitter();
                            }
                            ?>"/>
                        </div>
                    
           
                <legend>Multi-categoría</legend>
                <div class="form-group  col-md-4">
                <div id="t_datos_categorias">
                    <?php
                    if ($cliente->getIdCliente() != NULL) {
                        $result = $cliente->obtieneMultiCategoria();
                    } else {
                        $result = NULL;
                    }

                    $numero = 1;

                    if ($cliente->getIdCliente() == NULL || mysql_num_rows($result) == 0) {//Si no hay multi-categorio
                        echo "<tr id='row_$numero'>";
                        echo "<label>Categoria</label>";
                        echo "<td>";
                        echo "<select class='form-control' id='categoria$numero' name='categoria$numero'>";
                        foreach ($array_giros as $key => $value) {
                            echo "<option value='$key'>$value</option>";
                        }
                        echo "</select>";
                        echo "</td>";
                        echo '<td><input type="image" src="resources/images/add.png" title="Agregar otro periodo" onclick="agregarCategoria(); return false;" /></td>';
                        echo "<tr>";
                    } else {
                        while ($rs = mysql_fetch_array($result)) {
                            echo "<tr id='row_$numero'>";
                            echo "<td>Categoria</td>";
                            echo "<td>";
                            echo "<select id='categoria$numero' name='categoria$numero'>";
                            foreach ($array_giros as $key => $value) {
                                $s = "";
                                if ($key == $rs['IdGiro']) {
                                    $s = "selected='delected'";
                                }
                                echo "<option value='$key' $s>$value</option>";
                            }
                            echo "</select>";
                            echo "</td>";
                            echo '<td><input type="image" src="resources/images/add.png" title="Agregar otra categoría" onclick="agregarCategoria(); return false;" /></td>';
                            if ($numero > 1) {
                                        echo "<button class='btn btn-danger m-2' title='Eliminar este periodo' onclick='borrarCategoria(" . $numero . "); return false;'><i class='fas fa-times' style='color:white'></i></button>";
                                    }
                            $numero++;
                            echo "</tr>";
                        }
                        $numero--;
                    }
                    ?>  
                </div>
            </div>              
        

            <div class="form-group  col-md-4"> 
                <br><label for="Activo">Activo</label>
                <input class="form-control"  id="chkActivo" type="checkbox" name="chkActivo" <?php
                if ($cliente->getActivo() == "1") {
                    echo "checked='checked'";
                } elseif ($cliente->getActivo() == "0") {
                    echo "";
                } else {
                    echo "checked='checked'";
                }
                ?> value="1"/>
            </div>
           
            <?php
            if (isset($_GET['id']) && $_GET['id'] != "") {
                ?>
                <input type="hidden" value="<?php echo $_GET['id']; ?>" id="id" name="id"/>
                <input type="hidden" value="<?php echo $cliente->getIdDomicilio(); ?>" id="domicilioid" name="domicilioid"/>
                <?php
            }
            ?>
          
                 
                    <?php if ($permisos_grid->getModificar()) { ?>
                        
                        
                            <input type="submit" class="button btn btn-lang btn-block btn-outline-success mt-3 mb-3" name="Guardar" value="Guardar"  id="Guardar" />
                        <?php } ?>
                            <input type="button" onclick="cambiarContenidos('<?php echo $regresar ?>', 'Mis Clientes');
                                return false;" class="button btn btn-lang btn-block btn-outline-danger mt-3 mb-3" name="Cancelar" value="Cancelar" id="Cancelar" />
            <input type="hidden" id="numero_categoria" name="numero_categoria" value="<?php echo $numero; ?>"/>
            <input type="hidden" id="regresar" value="<?php echo $regresar ?>">

    </div>
    </div>
    </form> 
    </div>
    </body>
</html>