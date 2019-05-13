<?php

session_start();

if (!isset($_SESSION['user']) || $_SESSION['user'] == "") {
    header("Location: ../index.php");
}

include_once("../WEB-INF/Classes/ccliente.class.php");
include_once("../WEB-INF/Classes/Catalogo.class.php");
include_once("../WEB-INF/Classes/Usuario.class.php");
include_once("../WEB-INF/Classes/PermisosSubMenu.class.php");

// Creacion de objetos a traves de los constructores
    $usuario = new Usuario();
    $cliente = new ccliente();
    $catalogo = new Catalogo(); 
    $permisos_grid = new PermisosSubMenu();

$pagina_lista = "ventas/mis_clientes.php";
$permisos_grid->getPermisosSubmenu($_SESSION['idUsuario'], $pagina_lista);
$pagina_popup = "cliente/alta_cliente.php";

    if (isset($_GET['id']) && $_GET['id'] != "") {    
        $cliente->getregistrobyID($_GET['id']);
        $pagina_popup = "cliente/alta_cliente.php?ClaveCliente=".$_GET['id'];
    }

    $regresar = "ventas/mis_clientes.php";

    if(isset($_GET['regresar']) && $_GET['regresar'] != ""){
        $regresar = $_GET['regresar'];
    }
?>

<html>
<body>
<form class="form-group" id="formcliente">
    <div class="p-4 bg-light rounded">        
        <div id="accordion">
            
            <!-- Datos del cliente  -->
            <div class="mb-4 card border-secondary">                
                <a id="clientes_header" class="card-header p-3" role="button" data-toggle="collapse" data-target="#clientes_card" aria-expanded="true" aria-controls="clientes_card">
                    <h5 class="mt-1 mb-0 text-primary">Datos del cliente</h5>
                </a>  

                <div id="clientes_card" class="collapse show" aria-labelledby="clientes_header" data-parent="#accordion">
                    <div class="card-body">
                        <div class="form-row"> <!-- filas ocultas -->
                            <div class="form-group col-md-12" style="display: none;"> <!-- Estatus de cobranza -->
                                <label for="EstatusCobranza">Estatus de cobranza:</label>                                        
                                <select name="EstatusCobranza" id="EstatusCobranza" class="sizeMedio custom-select">
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
                                </select> <span id="MainContent_reqValEstatusCobranza" style="display:none;"></span>                   
                            </div>                                        
                        </div>
                        <div class="form-row"> <!-- Primera fila -->                            
                            <div class="form-group col-12 col-md-4"> <!-- Nombre -->                                
                                <label class="m-0" for="RazonSocial"> Nombre:<span class="obligatorio"> *</span></label>                                                                       
                                <input class="form-control" name="RazonSocial" type="text" maxlength="150" id="RazonSocial" value="<?php if ($cliente->getRazonSocial() != "") {                             
                                        echo $cliente->getRazonSocial();
                                    } ?>"/> <span id="MainContent_reqValRazonSocial" style="display:none;"></span>
                            </div>

                            <div class="form-group col-12 col-md-4"> <!-- Ejecutivo Cuenta -->
                                <label class="m-0" for="ejecutivocuenta">Ejecutivo Cuenta:<span class="obligatorio"> *</span></label>                    
                                <?php
                                    if(!$usuario->isUsuarioPuesto($_SESSION['idUsuario'], 11)){
                                        echo '<select name="ejecutivocuenta" id="ejecutivocuenta"  class="custom-select">';
                                        echo '<option value="">Seleccione el vendedor</option>';
                                        $consulta ="SELECT DISTINCT u.IdUsuario AS IdUsuario,CONCAT(u.Nombre,' ',u.ApellidoPaterno,' ',u.ApellidoMaterno) AS Nombre 
                                            FROM c_usuario AS u WHERE IdPuesto = 11 AND Activo = 1 ORDER BY Nombre;";
                                        $query = $catalogo->obtenerLista($consulta);                                
                                        while ($rs=  mysql_fetch_array($query)) {
                                            if ($rs['IdUsuario'] == $cliente->getEjecutivoCuenta()) {
                                                echo "<option value=\"" .$rs['IdUsuario'] . "\" selected>" . $rs['Nombre'] . "</option>";
                                            } else {
                                                echo "<option value=\"" .$rs['IdUsuario'] . "\">" . $rs['Nombre'] . "</option>";
                                            }
                                        }
                                        echo "</select>";
                                    }else{

                                        if($usuario->getRegistroById($_SESSION['idUsuario'])){

                                            echo "<input type='hidden' id='ejecutivocuenta' name='ejecutivocuenta' value='".$usuario->getId()."' />";

                                            echo $usuario->getNombre()." ".$usuario->getPaterno()." ".$usuario->getMaterno();

                                        }

                                    }

                                ?>  

                                <span id="MainContent_reqValEstatusCobranza" style="display:none;"></span>    

                            </div>

                            <div class="form-group col-12 col-md-4">  <!-- Ejecutivo Atención a Cliente -->                                                            

                                <label class="m-0" for="ejecutivoatencion">Ejecutivo Atención a Cliente:</label>                                   

                                <?php                    

                                    echo '<select name="ejecutivoatencion" id="ejecutivoatencion"  class="custom-select">';

                                    echo '<option value="">Seleccione el ejecutivo</option>';

                                    $consulta ="SELECT DISTINCT u.IdUsuario AS IdUsuario,CONCAT(u.Nombre,' ',u.ApellidoPaterno,' ',u.ApellidoMaterno) 
                                    
                                    AS Nombre, Loggin FROM c_usuario AS u WHERE Activo = 1 ORDER BY Nombre;";

                                    $query = $catalogo->obtenerLista($consulta);                                

                                    while ($rs=  mysql_fetch_array($query)) {

                                        if ($rs['IdUsuario'] == $cliente->getEjecutivoAtencionCliente()) {

                                            echo "<option value=\"" .$rs['IdUsuario'] . "\" selected>" . $rs['Nombre'] . " (".$rs['Loggin'].")</option>";

                                        } else {

                                            echo "<option value=\"" .$rs['IdUsuario'] . "\">" . $rs['Nombre'] . " (".$rs['Loggin'].")</option>";

                                        }

                                    }

                                    echo "</select>";                        

                                ?>  

                                <span id="MainContent_reqValEstatusCobranza" style="display:none;"></span> 

                            </div>                        

                        </div>

                        <div class="form-row"> <!-- Segunda fila -->                                                    
                                
                            <div class="form-group col-12 col-md-4">  <!-- Tipo -->

                                <label class="m-0" for="TipoCliente">Tipo:</label>

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
                                
                            <div class="form-group col-12 col-md-4"> <!-- Tipo cliente -->                                

                                <label class="m-0" for="modalidad2">Tipo cliente:</label>
                                    
                                    <select id="modalidad2" name="modalidad2" class="custom-select">

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

                            <div class="form-group col-12 col-md-4"> <!-- Giro -->

                                <label class="m-0" for="Giro">Giro:</label>                                        

                                <select id="Giro" name="Giro" class="custom-select">

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

                        </div>                                                         

                        <div class="form-row"> <!-- Tercera fila -->    
                            
                            <div class="form-group col-12 col-md-4"> <!-- Razón social -->                            

                                <label class="m-0" for="razon_cliente2">Razón social:<span class="obligatorio"> *</span></label>

                                <select id="razon_cliente2" name="razon_cliente2" class="custom-select">

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

                            <div class="form-group col-12 col-md-4"> <!-- Zona -->

                                <label class="m-0" for="zona">Zona:<span class="obligatorio"> *</span></label>                    

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

                            <div class="form-group col-12 col-md-4"> <!-- Grupo -->

                                <label class="m-0" for="grupo">Grupo:</label>

                                <select id="grupo" name="grupo" class="custom-select">

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

                            </div>

                        </div>

                        <div class="form-row"> <!-- Cuarta fila -->

                            <div class="form-group col-12 col-md-4"> <!-- Cuenta Bancaria -->

                                <label class="m-0" for="cuentaBancaria">Cuenta Bancaria:</label>

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

                            <div class="form-group col-12 col-md-4"> <!-- Referencia Numerica -->

                                <label class="m-0" for="referenciaNum">Referencia Numerica: </label>                    

                                <input class="form-control" type="text" id="referenciaNum" name="referenciaNum" value="<?php if ($cliente->getReferenciaNumerica() != "") {echo $cliente->getReferenciaNumerica();} ?> ">

                            </div>

                            <div class="form-group col-12 col-md-4"> <!-- Califica al cliente -->
                                    
                                <label class="m-0" for="calificacion">Calificación: </label>                                                                                

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

                        <div class="form-row"> <!-- Quinta fila -->
                        
                            <div class="form-group col-12 col-md-5"> <!-- Comentario -->                        
                                
                                <label for="comentario">Comentario: </label>                

                                <textarea id='comentario' name='comentario' style='resize: none;' class="form-control">

                                    <?php 

                                        if ($cliente->getComentario() != "") {

                                            echo $cliente->getComentario();

                                        } 

                                    ?>

                                </textarea>

                            </div>

                            <div class="form-group col-12 col-md-2"> <!-- Imagen -->                                                                                             

                            </div>

                            <div class="form-group col-12 col-md-2"> <!-- imagen_url -->                            

                                <?php 

                                    if($cliente->getImagen()!=null){                                                                            

                                        echo "<label for='image'>Imagen: </label>   

                                            <input class='form-control' type='hidden' id='imagen_url' name='imagen_url' maxlength='200' value='".$cliente->getImagen()."'>

                                            <input class='form-control' id='image' type='image' src='".$cliente->getImagen()."' onclick='return false;' style='width: 100px; height:100px;'/>";

                                    } 

                                ?>
                            
                            </div>

                            <div class="form-group col-12 col-md-3"> <!-- Alta cliente -->

                                <?php if ($permisos_grid->tienePermisoEspecial($_SESSION['idUsuario'], 7)) {//Si tiene el permiso especial de alta cliente ?>

                                    <a href="#" title="Alta cliente" onclick="lanzarPopUp('Alta cliente', '<?php echo $pagina_popup; ?>');

                                                return false;"><img src="resources/images/client_icon.gif" width="28" height="28"/></a>

                                <?php } ?>

                            </div>
                        
                        </div>

                    </div>

                </div>

            </div>
                
            <!-- Datos de la facturacion -->
            <div class="mb-4 card border-secondary">
                
                <a id="domicilio_header" class="card-header p-3" role="button" data-toggle="collapse" data-target="#domicilio_card" aria-expanded="false" aria-controls="domicilio_card">

                    <h5 class="mt-1 mb-0 text-primary">Domicilio/Domiciolio Fiscal</h5>

                </a>  

                <div id="domicilio_card" class="collapse" aria-labelledby="domicilio_header" data-parent="#accordion">
                
                    <div class="card-body">

                        <div class="form-row"> <!-- Primeras filas -->
                        
                            <div class="form-group col-md-12"> <!-- Tipo de domicilio -->
                                
                                <label class="m-0" for="TipoDomicilioF">Tipo de domicilio:</label>
                                    
                                <input name="TipoDomicilioF" type="text" value="Domicilio cliente (fiscal)" maxlength="50" id="TipoDomicilioF" disabled="disabled" class="aspNetDisabled form-control" style="width:200px;"/>
                                
                                <span id="MainContent_reqValTipoDomicilio" style="display:none;"></span>
                                
                            </div>
                            
                            <div class="form-row col-md-12"> <!-- Primera fila -->
                            
                                <div class="form-group col-12 col-md-6"> <!-- Calle -->

                                    <label class="m-0" for="CalleF">Calle:<span class="obligatorio"> *</span></label>
                                
                                    <input class="form-control" name="CalleF" type="text" maxlength="100" id="CalleF" value="<?php if ($cliente->getCalleF() != "") { echo $cliente->getCalleF();} ?>"/>

                                    <span id="Calle" style="display:none;"></span>

                                </div>
                                
                                <div class="form-group col-6 col-md-2"> <!-- No. exterior -->
                                
                                    <label class="m-0" for="NoExteriorF">No. exterior:<span class="obligatorio"> *</span></label>                            
                                    
                                    <input name="NoExteriorF" type="text" maxlength="30" id="NoExteriorF" value="<?php if ($cliente->getNoExtF() != "") {
                                                    echo $cliente->getNoExtF();
                                                } ?>" class="form-control"/>
                                    <span id="MainContent_reqValNoExterior" style="display:none;"></span>

                                </div>

                                <div class="form-group col-6 col-md-2"> <!-- No. interior -->
                                
                                    <label class="m-0" for="NoInteriorF">No. interior: </label>
                                                    
                                    <input name="NoInteriorF" type="text" maxlength="30" id="NoInteriorF" value="<?php if ($cliente->getNoIntF() != "") {
                                        
                                        echo $cliente->getNoIntF();

                                    } ?>" class="form-control"/>

                                </div>

                            </div>

                        </div>

                        <div class="form-row"> <!-- Segunda fila -->
                        
                            <div class="form-group col-12 col-md-4"> <!-- Colonia -->
                                
                                <label class="m-0" for="ColoniaF">Colonia:<span class="obligatorio"> *</span></label>
                                    
                                <input name="ColoniaF" type="text" maxlength="50" id="ColoniaF" value="<?php if ($cliente->getColoniaF() != "") {

                                                echo $cliente->getColoniaF();

                                            } ?>" class="form-control" />

                                <span id="MainContent_reqValColonia" style="display:none;"></span>
                            
                            </div>

                            <div class="form-group col-12 col-md-4"> <!-- Ciudad -->
                            
                                <label class="m-0" for="CiudadF">Ciudad:<span class="obligatorio"> *</span></label>
                            
                                <input name="CiudadF" type="text" maxlength="50" id="CiudadF" value="<?php if ($cliente->getCiudadF() != "") {

                                                echo $cliente->getCiudadF();

                                            } ?>" class="form-control" />

                                <span id="MainContent_reqValCiudad" style="display:none;"></span>

                            </div>
                        
                            <div class="form-group col-12 col-md-4"> <!-- Estado -->
                            
                                <label class="m-0" for="EstadoF">Estado:<span class="obligatorio"> *</span></label>

                            
                                <select class="form-control" name="EstadoF" id="EstadoF">

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

                        <div class="form-row"> <!-- Tercera fila -->

                            <div class="form-group col-12 col-md-4"> <!-- Delegación -->
                            
                                <label class="m-0" for="DelegacionF">Delegación:<span class="obligatorio"> *</span></label>
                            
                                <input class="form-control" name="DelegacionF" type="text" maxlength="50" id="DelegacionF" value="<?php if ($cliente->getDelegacionF() != "") {

                                        echo $cliente->getDelegacionF();

                                    } ?>" class="form-control" />

                                <span id="MainContent_reqValDelegacion" style="display:none;"></span>

                            </div>

                            <div class="form-group col-12 col-md-4"> <!-- C.P -->
                                <label class="m-0" for="CPF">C.P:<span class="obligatorio"> *</span></label>
                                <input class="form-control" name="CPF" type="text" maxlength="30" id="CPF" value="<?php if ($cliente->getCPF() != "") {
                                        echo $cliente->getCPF();
                                    } ?>" />
                                <span id="MainContent_reqValCP" style="display:none;"></span>
                            </div>

                            <div class="form-group col-12 col-md-4"> <!-- Localidad -->
                                <label class="m-0" for="LocalidadF">Localidad:</label>
                                <input class="form-control" name="LocalidadF" type="text" maxlength="30" id="LocalidadF" value="<?php if ($cliente->getLocalidad() != "") {
                                        echo $cliente->getLocalidad();
                                    } ?>" />                        
                            </div>
                        </div>

                        <div class="form-row"> <!-- Cuarta fila -->
                            <div class="form-group col-12 col-md-3"> <!-- Latitud -->
                                <label class="m-0" for='latitud'>Latitud</label>
                                <input class="form-control" type="text" id='latitud' name='latitud' value='<?php if($cliente->getLatitud()!=null){  echo $cliente->getLatitud(); } ?>'/>
                            </div>   

                            <div class="form-group col-12 col-md-3"> <!-- Longitud -->
                                <label class="m-0" for='longitud'>Longitud</label>
                                <input class="form-control" type="text" id='longitud' name='longitud' value='<?php if($cliente->getLongitud()!=null){  echo $cliente->getLongitud(); } ?>'/>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Parte 3- Datos de la facturacion -->

            <div class="mb-4 card border-secondary">                    
                <a id="facturacion_header" class="card-header p-3" role="button" data-toggle="collapse" data-target="#facturacion_card" aria-expanded="false" aria-controls="facturacion_card">
                    <h5 class="mt-1 mb-0 text-primary">Datos de la Facturación</h5>
                </a> 

                <div id="facturacion_card" class="collapse" aria-labelledby="facturacion_header" data-parent="#accordion">
                    <div class="card-body">
                        <div class="form-row"> <!-- Primera fila -->
                            <div class="form-group col-12 col-md-3"> <!-- RFC -->
                                <label class="m-0" for="RFCD">RFC <span class="obligatorio"> *</span></label>
                                <input name="RFCD" type="text" maxlength="50" id="RFCD" value="<?php if ($cliente->getRFCD() != "") {
                                    echo $cliente->getRFCD();
                                } ?>" class="form-control" />
                                <span id="MainContent_reqValRFC" style="display:none;"></span>                        
                            </div>
                        </div>
                        
                        <div class="form-row"> <!-- Segunda fila -->
                            <div class="form-group col-12 col-md-6"> <!-- Correo electrónico 1 -->
                                <label class="m-0" for="CorreoE1D">Correo electrónico 1 para envío de factura</label>
                                <input name="CorreoE1D" type="text" maxlength="50" id="CorreoE1D" value="<?php if ($cliente->getCorreoE1D() != "") {
                                    echo $cliente->getCorreoE1D();
                                } ?>" class="form-control" />
                                <span id="MainContent_reqValCorreoE1" style="display:none;"></span>                        
                            </div>                                                        

                            <div class="form-group col-12 col-md-6"> <!-- Correo electrónico 2 -->
                                <label class="m-0" for="CorreoE2D">Correo electrónico 2 para envío de factura</label>
                                <input name="CorreoE2D" type="text" maxlength="50" id="CorreoE2D" value="<?php if ($cliente->getCorreoE2D() != "") {
                                    echo $cliente->getCorreoE2D();
                                } ?>" class="form-control" />
                            </div>
                        </div>
                        
                        <div class="form-row"> <!-- Tercera fila -->
                            <div class="form-group col-12 col-md-6"> <!-- Correo electrónico 3 -->
                                <label class="m-0" for="CorreoE3D">Correo electrónico 3 para envío de factura</label>
                                <input name="CorreoE3D" type="text" maxlength="50" id="CorreoE3D" value="<?php if ($cliente->getCorreoE3D() != "") {
                                    echo $cliente->getCorreoE3D();
                                } ?>" class="form-control" />
                            </div>                            
                            <div class="form-group col-12 col-md-6"> <!-- Correo electrónico 4 -->
                                <label class="m-0" for="CorreoE4D">Correo electrónico 4 para envío de factura :</label>
                                <input name="CorreoE4D" type="text" maxlength="50" id="CorreoE4D" value="<?php if ($cliente->getCorreoE4D() != "") {
                                    echo $cliente->getCorreoE4D();
                                } ?>" class="form-control" />
                            </div>
                        </div>

                        <div class="form-row"> <!-- Cuarta fila -->
                            <div class="form-group col-12 col-md-6"> <!-- Selecciona la addenda -->
                                <label class="m-0" for="addenda_cliente">Selecciona la addenda </label>
                                <select id='addenda_cliente' name='addenda_cliente' class="custom-select">
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
                        <div class="form-row"> <!-- Quinta fila -->
                            <div class="pt-2 pl-5 custom-control custom-checkbox col-12 col-md-4"> <!-- Mostrar addenda en representación impresa -->                                                           
                                <input type='checkbox' id='mostrar_pdf' name='mostrar_pdf' class="custom-control-input"
                                    <?php if ($cliente->getMostrarAddenda() == "1") {
                                        echo "checked='checked'";
                                    } else {
                                        echo "";
                                    } ?>
                                />
                                <label class="custom-control-label" for="mostrar_pdf">Mostrar addenda en representación impresa</label>
                            </div>
                            <div class="pt-2 pl-5 custom-control custom-checkbox col-12 col-md-4"> <!-- Mostrar condiciones de pago -->                                            
                                <input type='checkbox' id='condiciones_pago' name='condiciones_pago' class="custom-control-input"
                                    <?php if ($cliente->getMostarCondicionesPago() == "1") {
                                        echo "checked='checked'";
                                    } else {
                                        echo "";
                                    } ?>
                                />
                                <label class="custom-control-label" for="condiciones_pago">Mostrar condiciones de pago</label>
                            </div>
                            <div class="pt-2 pl-5 custom-control custom-checkbox col-12 col-md-4"> <!-- Ver No. Cuenta en PDF -->                                            
                                <input class="custom-control-input" type="checkbox" name="verPDF" id="verPDF" <?php if ($cliente->getVerCClientePDF() == "1") {
                                    echo "checked='checked'";                    
                                    } elseif ($cliente->getVerCClientePDF() == "0" || is_null($cliente->getVerCClientePDF())) {
                                        echo "";
                                    } else {
                                        echo "checked='checked'";
                                    } ?> value="1"
                                />
                                <label class="custom-control-label" for="verPDF">Ver No. Cuenta en PDF</label>
                            </div>
                        </div>  
                    </div>
                </div>
            </div>

            <!-- Parte 4 - Datos sociales -->
            <div class="mb-4 card border-secondary">
                <a id="sociales_header" class="card-header p-3" role="button" data-toggle="collapse" data-target="#sociales_card" aria-expanded="false" aria-controls="sociales_card">
                    <h5 class="mt-1 mb-0 text-primary">Datos sociales</h5>
                </a>
                <div id="sociales_card" class="collapse" aria-labelledby="sociales_header" data-parent="#accordion">
                    <div class="card-body">
                        <div class="form-row"> <!-- Primera fila -->
                            <div class="form-group col-12 col-md-4">
                                <label class="m-0" for="telefono">Teléfono:</label>
                                <input class="form-control" type="text" id="telefono" name="telefono" value="<?php if($cliente->getTelefono() != ""){ echo $cliente->getTelefono(); } ?>"/>
                            </div>
                            <div class="form-group col-12 col-md-4">
                                <label class="m-0" for="correo">Correo electrónico:</label></td>
                                <input class="form-control" type="text" id="correo" name="correo" value="<?php if($cliente->getEmail() != ""){ echo $cliente->getEmail(); } ?>"/>
                            </div>
                            <div class="form-group col-12 col-md-4">
                                <label class="m-0" for="horario">Horario:</label></td>
                                <input class="form-control" type="text" id="horario" name="horario" value="<?php if($cliente->getHorario() != ""){ echo $cliente->getHorario(); } ?>"/>
                            </div>
                        </div>

                        <div class="form-row"> <!-- Primera fila -->
                            <div class="mr-5 form-group col-12 col-md-4"> <!-- SitioWeb -->
                                <label class="m-0" for="sitio_web">SitioWeb:</label>
                                <input class="form-control" type="text" id="sitio_web" name="sitio_web" value="<?php if($cliente->getSitioweb() != ""){ echo $cliente->getSitioweb(); } ?>"/>
                            </div>
                            <div class="form-inline col-12 col-md-3"> <!-- Facebook -->
                                <label class="sr-only" for="facebook">Facebbok</label>
                                <div class="input-group mb-2">
                                    <div class="input-group-prepend">
                                        <div class="input-group-text">
                                            <?php if($cliente->getFacebook() != ""){ echo "<a href='".$cliente->getFacebook()."' target='_blank'>"; }?> 
                                                <img src="resources/images/facebook.png" title="Facebook" style="width: 24px; height: 24px;">
                                            <?php if($cliente->getFacebook() != ""){ echo "</a>"; }?> 
                                        </div>
                                    </div>
                                    <input class="form-control" type="text" id="facebook" name="facebook" value="<?php if($cliente->getFacebook() != ""){ echo $cliente->getFacebook(); } ?>"/>
                                </div>                                                                                    
                            </div>
                            <div class="form-inline col-12 col-md-3"> <!-- Twitter -->
                                <label class="sr-only" for="twitter">Twitter</label>                            
                                <div class="input-group mb-2">
                                    <div class="input-group-prepend">
                                        <div class="input-group-text">
                                            <?php if($cliente->getTwitter() != ""){ echo "<a href='".$cliente->getTwitter()."' target='_blank'>"; }?> 
                                                <img src="resources/images/Twitter-icon.png" title="Twitter" style="width: 24px; height: 24px;">            
                                            <?php if($cliente->getTwitter() != ""){ echo "</a>"; }?> 
                                        </div>
                                    </div>
                                    <input class="form-control" type="text" id="twitter" name="twitter" value="<?php if($cliente->getTwitter() != ""){ echo $cliente->getTwitter(); } ?>"/>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- fin de la parte 4 -->

            <!-- Parte 5 - Multi-categoria -->
            <div class="card border-secondary">
                <a id="multi_header" class="card-header p-3" role="button" data-toggle="collapse" data-target="#multi_card" aria-expanded="false" aria-controls="multi_card">
                    <h5 class="mt-1 mb-0 text-primary">Multi-categoría</h5>
                </a> 
                <div id="multi_card" class="collapse" aria-labelledby="multi_header" data-parent="#accordion">
                    <div class="card-body">
                        <ul id="t_datos_categorias" class="list-group list-group-flush"> <!-- Primera fila -->
                            <?php
                                if ($cliente->getIdCliente() != NULL){
                                    $result = $cliente->obtieneMultiCategoria();
                                } else {
                                    $result = NULL;
                                }

                                $numero = 1;
                                    
                                if ($cliente->getIdCliente() == NULL || mysql_num_rows($result) == 0) {//Si no hay multi-categorio
                                    echo "<li id='row_$numero' class='list-group-item'>";                            
                                    echo "<label class='m-2' for='categoria$numero'>Categoria</label><div class='d-flex flex-row'>";
                                    echo "<select id='categoria$numero' name='categoria$numero' class='custom-select m-2' style='max-width: 200px;'>";
                                    
                                    foreach ($array_giros as $key => $value) {
                                        echo "<option value='$key'>$value</option>";
                                    }
                                    
                                    echo "</select>";                                
                                    echo '<button class="btn btn-success m-2" title="Agregar otra categoría" onclick="agregarCategoria(); return false;"><i class="fas fa-plus" style="color:white"></i></button>';
                                    echo "</div></li>";
                                } else {
                                    while ($rs = mysql_fetch_array($result)) {
                                        echo "<div id='row_$numero' class='form-row'>";
                                        echo "<label for='categoria$numero'>Categoria</label>";
                                        echo "<select id='categoria$numero' name='categoria$numero' class='custom-select'>";
                                            foreach ($array_giros as $key => $value) {
                                                $s = "";
                                                if($key == $rs['IdGiro']){
                                                    $s = "selected='delected'";
                                                }
                                                echo "<option value='$key' $s>$value</option>";
                                            }
                                        echo "</select>";
                                        echo '<button class="btn btn-success m-2" title="Agregar otra categoría" onclick="agregarCategoria(); return false;">
                                                <i class="fas fa-plus" style="color:white"></i>
                                            </button>';
                                        if ($numero > 1) {
                                            echo "<button class='btn btn-danger m-2' title='Eliminar este periodo' onclick='borrarCategoria(" . $numero . "); return false;'>
                                                    <i class='fas fa-times' style='color:white'></i>
                                                </button>";
                                        }
                                        $numero++; 
                                        echo "</div>";
                                    }
                                    $numero--;
                                }
                            ?> 
                        </ul>    
                    </div>            
                </div>
            </div> 
            <div class="custom-control custom-checkbox">
                <input id="chkActivo" type="checkbox" name="chkActivo" class="custom-control-input" <?php if ($cliente->getActivo() == "1") {
                    echo "checked='checked'";
                } elseif ($cliente->getActivo() == "0") {
                    echo "";
                } else {
                    echo "checked='checked'";
                } ?> value="1"/>
                <label class="mt-3 ml-3 custom-control-label"  for="chkActivo">Activo</label>
            </div> 
            <!-- fin de la parte 5 -->
                    
            <!-- Parte 6 - Botones de accion -->
            <?php if (isset($_GET['id']) && $_GET['id'] != ""):?>
                <input type="hidden" value="<?php echo $_GET['id'];?>" id="id" name="id"/>
                <input type="hidden" value="<?php echo $cliente->getIdDomicilio();?>" id="domicilioid" name="domicilioid"/>
            <?php endif; ?>
            <div class="row">                
                <?php if($permisos_grid->getModificar()): ?>
                    <div class="col col-6 text-center py-3">
                        <button type="submit" class="btn btn-success boton" name="Guardar" id="Guardar"> Guardar</button>
                    </div>
                <?php endif; ?>
                <div class="col col-6 text-center py-3">
                    <button type="button" onclick="cambiarContenidos('<?php echo $regresar?>', 'Mis Clientes');
                            return false;" class="btn btn-danger boton" name="Cancelar" value="Cancelar" id="Cancelar"> Cancelar </button>
                </div>
            </div>
            <input type="hidden" id="numero_categoria" name="numero_categoria" value="<?php echo $numero; ?>"/>
            <input type="hidden" id="regresar" value="<?php echo $regresar?>">
                
        </div>
        <!-- fin del formulario -->
        
    </div>
</form>
<script type="text/javascript" language="javascript" src="resources/js/paginas/ventas/nuevo_cliente.js"></script>
</body>
</html>