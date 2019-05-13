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
<form id="formcuentaBancaria">
    <table style="width: 70%;">
        <tr>
            <td>Banco</td>
            <td>
                <div id="copiarComponente" name="copiarComponente">
                <select id="componenteCopiar" name="componenteCopiar" style="max-width: 250px;">
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
                </select></div>
            </td>
            <td>No. Cuenta</td>
            <td><input type="text" name="noCuenta" id="noCuenta" value="<?php echo $cuenta->getNoCuenta(); ?>"/></td>
        </tr>
        <tr>
            <td>Tipo Cuenta</td>
            <td><input type="text" name="tipoCuenta" id="tipoCuenta" value="<?php echo $cuenta->getTipoCuenta(); ?>"/></td>
            <td>RFC</td>
            <td><div id="RFC" name="RFC">
                <select id="RFC" name="RFC" style="max-width: 250px;">
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
            </select></div></td>
        </tr>
        <tr>
            <td>Clave</td>
            <td><input type="text" name="clave" id="clave" value="<?php echo $cuenta->getClave(); ?>"/></td>
            <td>Sucursal</td>
            <td><input type="text" name="sucursal" id="sucursal" value="<?php echo $cuenta->getSucursal(); ?>"/></td>
        </tr>
        <tr>
            <td>Ejecutivo de Cuenta</td>
            <td><input type="text" name="ejecutivo" id="ejecutivo" value="<?php echo $cuenta->getEjecutivoCuenta(); ?>"/></td>
            <td>Telefono del ejecutivo</td>
            <td><input type="text" name="telefono" id="telefono" value="<?php echo $cuenta->getTelEjecutivo(); ?>"/></td>
            <td>Correo del ejecutivo</td>
            <td><input type="text" name="correo" id="correo" value="<?php echo $cuenta->getCorreoEjecutivo(); ?>"/></td>
        </tr>
        <tr>
            <td>Activo</td>
            <td><input type="checkbox" value="1" name="activo" id="activo" <?php
                if (isset($_GET['id']) && $_GET['id']) {
                    if ($cuenta->getActivo() != "" && $cuenta->getActivo() == 1) {
                        echo "checked";
                    }
                }else{
                    echo "checked";
                }
                ?>/></td>
            <td>Descripcion</td>
            <td><input type="text" name="descripcion" id="descripcion" value="<?php echo $cuenta->getDescripcion(); ?>"/></td>
            <td>Dia de Corte</td>
            <td><div id="fecha_corte" name="fecha_corte">
                <select id="fecha_corte" name="fecha_corte" style="max-width: 250px;">
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
            </select></div></td>
        </tr>
    </table>
    <?php
    if (isset($_GET['id']) && $_GET['id']) {
        ?>
        <input type="hidden" name="id" id="id" value="<?php echo $_GET['id'] ?>"/>
    <?php } ?>
    <br/><br/>
    <input type="submit" id="aceptar" class="boton" name="aceptar" value="Guardar"/>
    <input type="button" id="cancelar" class="boton" name="cancelar" value="Cancelar" onclick="cambiarContenidos('Bancos/lista_cuentaBancaria.php', 'Cuentas Bancarias');"/>
</form>


