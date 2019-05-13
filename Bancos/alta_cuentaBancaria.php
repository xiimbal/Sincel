<?php

session_start();
if (!isset($_SESSION['user']) || $_SESSION['user'] == "") {
    header("Location: index.php");
}
include_once("../WEB-INF/Classes/Catalogo.class.php");
include_once("../WEB-INF/Classes/CuentaBancaria.class.php");

$cuenta = new CuentaBancaria();
$proveedor = null;
if (isset($_GET['Proveedor']) && $_GET['Proveedor']) {
    $proveedor = $_GET['Proveedor'];
}

if (isset($_GET['id']) && $_GET['id']) {
    $cuenta->getRegistroById($_GET['id']);
}
?>
<script type="text/javascript" language="javascript" src="resources/js/paginas/catalogos/alta_cuentaBancaria.js"></script>
<form id="formcuentaBancaria" class="form">
        <div class="form-row">
            <div class="form-group col-md-4">
                <label for="componenteCopiar">Banco</label>
                <select name="componenteCopiar" class="custom-select">
                    <?php
                    $equipos = new Catalogo();
                    $query = $equipos->getListaAlta("c_banco", "Nombre");
                    echo "<option value='0' >Selecciona una opción</option>";
                    while ($rs = mysql_fetch_array($query)) {
                        if(strcmp(trim($rs['Nombre']),trim($cuenta->getBanco())) == 0)
                        {
                            echo "<option value=\"" . $rs['IdBanco'] . "\" selected=\"selected\" >" . $rs['Nombre'] ."</option>";
                        }else
                        {
                            echo "<option value=\"" . $rs['IdBanco'] . "\" >" . $rs['Nombre'] ."</option>";                     
                        }
                    }
                    ?>
                </select>
            </div>
            <div class="form-group col-md-4">
                <label for="noCuenta">No. Cuenta</label>
                <input class="form-control" type="text" name="noCuenta" id="noCuenta" value="<?php echo $cuenta->getNoCuenta(); ?>"/>
            </div>
            <div class="form-group col-md-4">
                <label for="ejecutivo">Ejecutivo de Cuenta</label>
                <input type="text" name="ejecutivo" id="ejecutivo" value="<?php echo $cuenta->getEjecutivoCuenta(); ?>" class="form-control"/>
            </div>
            <div class="form-group col-md-4">
                <label for="telefono">Telefono del ejecutivo</label>
                <input class="form-control" type="text" name="telefono" id="telefono" value="<?php echo $cuenta->getTelEjecutivo(); ?>"/>
            </div>
            <div class="form-group col-md-4">
                <label for="correo">Correo del ejecutivo</label>
                <input class="form-control" type="text" name="correo" id="correo" value="<?php echo $cuenta->getCorreoEjecutivo(); ?>"/>
            </div>
            <div class="form-group col-md-4">
                <label for="sucursal">Sucursal</label>
                <input class="form-control" type="text" name="sucursal" id="sucursal" value="<?php echo $cuenta->getSucursal(); ?>"/>
            </div>
            <div class="form-group col-md-4">
                <label for="tipoCuenta">Tipo Cuenta</label>
                <input class="form-control" type="text" name="tipoCuenta" id="tipoCuenta" value="<?php echo $cuenta->getTipoCuenta(); ?>"/>
            </div>
            <div class="form-group col-md-4">
                <label for="">RFC</label>
                <select  name="RFC"  class="custom-select" >
                    <?php
                    if($proveedor != null){
                        echo "<option value=\"" . $proveedor . "\" selected=\"selected\" >" . $proveedor ."</option>";
                    }else{
                        $equipos = new Catalogo();
                        $query = $equipos->getListaAlta("c_proveedor", "RFC");
                        echo "<option value='0' >Selecciona una opción</option>";
                        while ($rs = mysql_fetch_array($query)) {
                            if(strcmp($rs['RFC'], $cuenta->getRFC()) == 0){
                                echo "<option value=\"" . $rs['RFC'] . "\" selected=\"selected\" >" . $rs['NombreComercial']."-".$rs['RFC'] ."</option>";
                            }else{
                                echo "<option value=\"" . $rs['RFC'] . "\" >" .$rs['NombreComercial']."-" . $rs['RFC'] ."</option>";
                            }
                        }
                        $query = $equipos->getListaAlta("c_datosfacturacionempresa", "IdDatosFacturacionEmpresa");
                        while ($rs = mysql_fetch_array($query)) {
                            if(strcmp($rs['IdDatosFacturacionEmpresa'], $cuenta->getRFC()) == 0){
                                echo "<option value=\"" . $rs['IdDatosFacturacionEmpresa'] . "\" selected=\"selected\" >" . $rs['RazonSocial'] ."</option>";
                            }else{
                                echo "<option value=\"" . $rs['IdDatosFacturacionEmpresa'] . "\" >" . $rs['RazonSocial'] ."</option>";
                            }
                        }
                    }
                    ?>
                </select>
            </div>
            <div class="form-group col-md-4">
                <label for="clave">Clave</label>
                <input type="text" name="clave" id="clave" value="<?php echo $cuenta->getClave(); ?>" class="form-control"/>
            </div>
            <div class="form-group col-md-4">
                <label for="activo" class="form-check-label">Activo</label>
                <input class="form-check" type="checkbox" value="1" name="activo" id="activo" <?php
                if (isset($_GET['id']) && $_GET['id']) {
                    if ($cuenta->getActivo() != "" && $cuenta->getActivo() == 1) {
                        echo "checked";
                    }
                }else{
                    echo "checked";
                }
                ?>/>
            </div>
            <div class="form-group col-md-4">
                <label for="descripcion">Descripcion</label>
                <input class="form-control" type="text" name="descripcion" id="descripcion" value="<?php echo $cuenta->getDescripcion(); ?>"/>
            </div>
            <div class="form-group col-md-4">
                <label for="fecha_corte">Dia de Corte</label>
                <select class="form-control" id="fecha_corte" name="fecha_corte">
                    <?php
                    echo "<option value='0' >Selecciona una opción</option>"; 
                    for ($cont = 0; $cont <= 31; $cont++) {
                        if($cont == $cuenta->getFechaCorte()){
                            echo "<option value=\"" . $cont . "\" selected=\"selected\" >" . $cont ."</option>";
                        }else{
                            echo "<option value=\"" . $cont . "\" >" . $cont ."</option>";
                        }
                    }
                    ?>
                </select>
            </div>
            <div class="form-group col-md-4">
                <?php
               if (isset($_GET['id']) && $_GET['id']) {
               ?>
               <input type="hidden" name="id" id="id" value="<?php echo $_GET['id'] ?>"/>
               <?php } ?> 
               <input type="submit" id="aceptar" class="btn btn-success" name="aceptar" value="Guardar"/>
                <input type="button" id="cancelar" class="btn btn-danger" name="cancelar" value="Cancelar" onclick="cambiarContenidos('Bancos/lista_cuentaBancaria.php', 'Cuentas Bancarias');"/>
            </div>
        </div>
    
</form>