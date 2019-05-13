<?php
session_start();

if (!isset($_SESSION['user']) || $_SESSION['user'] == "") {
    header("Location: index.php");
}
include_once("../WEB-INF/Classes/Proveedor.class.php");
include_once("../WEB-INF/Classes/Catalogo.class.php");
$catalogo = new Catalogo();
$pagina_lista = "admin/lista_proveedor.php";
$altaCuenta = "";
$read = "";
$clave = "";
$nombre = "";
$rfc = "";
$tipo = "";
$contacto = "";
$telefono = "";
$correo = "";
$banco = "";
$ctBancaria = "";
$formaPago = "";
$diasCredito = "";
$notificar = "";
$noCliente = "";
$porcentaje = "";
//
$calle = "";
$noExterior = "";
$noInterior = "";
$colonia = "";
$ciudad = "";
$delegacion = "";
$estado = "";
$pais = "";
$cp = "";
$facturarA = "";
$activo = "checked='checked'";
?>
<!DOCTYPE html>
<html lang="es">
    <head>
        <script type="text/javascript" language="javascript" src="resources/js/paginas/alta_proveedor.js"></script>
        <script>
            $(document).ready(function() {
                $('.boton').button().css('margin-top', '20px');
            });
        </script>
    </head>
    <body>
        <div class="principal">
            <?php
            if (isset($_POST['id'])) {
                $obj = new Proveedor();
                if($obj->getRegistroById($_POST['id'])){
                    $read = "readonly='readonly'";
                    $clave = $obj->getClave();
                    $nombre = $obj->getNombre();
                    $rfc = $obj->getRfc();
                    $altaCuenta = "Bancos/alta_cuentaBancaria.php?Proveedor=".$rfc;
                    $tipo = $obj->getTipo();
                    if ($obj->getActivo() == "0") {
                        $activo = "";
                    }
                    if ($obj->getNotificar() != "0") {
                        $notificar = "checked='checked'";
                    }
                    $telefono = $obj->getTelefono();
                    $contacto = $obj->getContacto();
                    $correo = $obj->getCorreo();
                    $formaPago = $obj->getFormPago();
                    $ctBancaria = $obj->getCuentaBancaria();
                    $diasCredito = $obj->getDiasCredito();
                    $calle = $obj->getCalle();
                    $noExterior = $obj->getNumExterior();
                    $noInterior = $obj->getNumInterior();
                    $colonia = $obj->getColonia();
                    $ciudad = $obj->getCiudad();
                    $delegacion = $obj->getDelegacion();
                    $estado = $obj->getEstado();
                    $pais = $obj->getPais();
                    $cp = $obj->getCp();
                    $noCliente = $obj->getNoiCliente();
                    $referencia = $obj->getReferencia();
                    $porcentaje = $obj->getPorcentajeServicio();
                }
            }
            ?>

            <form id="formProveedor" name="formProveedor" action="/" method="POST">
                <h3><b><?php echo "Alta proveedor"; ?></b></h3>
                <br/><br/>
                <table style="min-width: 100%;">
                    <tr>
                        <td>Clave<span class="obligatorio"> *</span></td><td><input type="text" id="txt_clave" name="txt_clave" value="<?php echo $clave; ?>" <?php echo $read; ?>/></td>
                        <td>Nombre<span class="obligatorio"> *</span></td><td colspan="3"><input type="text" id="txt_nombre" name="txt_nombre" value="<?php echo $nombre; ?>" style="width: 91%"/></td>      
                        <td>Tipo de proveedor<span class="obligatorio"> *</span></td>
                        <td>
                            <select id="sl_tipo" name="sl_tipo" style="width: 175px">
                                <?php
                                $query = $catalogo->getListaAlta("c_tipoproveedor", "Nombre");
                                echo "<option value='0' >Selecciona una opción</option>";
                                while ($rs = mysql_fetch_array($query)) {
                                    $s = "";
                                    if ($tipo != "" && $tipo == $rs['IdTipoProveedor']) {
                                        $s = "selected";
                                    }
                                    echo "<option value=" . $rs['IdTipoProveedor'] . " " . $s . ">" . $rs['Nombre'] . "</option>";
                                }
                                ?>
                            </select>
                        </td>             
                    </tr>

                    <tr>
                        <td>RFC<span class="obligatorio">*</span></td><td><input type="text" id="txt_rfc" name="txt_rfc" value="<?php echo $rfc; ?>"/></td>
                        <td>Telefono</td><td><input type="text" id="txt_tel" name="txt_tel" value="<?php echo $telefono; ?>"/></td>
                        <td>Contacto</td><td><input type="text" id="txt_contacto" name="txt_contacto" value="<?php echo $contacto; ?>"/></td>                
                        <td>Correo</td><td><input type="text" id="txt_correo" name="txt_correo" value="<?php echo $correo; ?>"/></td>                        
                    </tr>
                    <tr>
                        <td>Forma de pago</td>
                        <td>
                            <select id="sl_forma_pago" name="sl_forma_pago" style="width: 175px">
                                <?php
                                $query_tipo_pago = $catalogo->getListaAlta("c_formapago", "Nombre");
                                echo "<option value='0' >Selecciona una opción</option>";
                                while ($rs = mysql_fetch_array($query_tipo_pago)) {
                                    $s = "";
                                    if ($formaPago != "" && $formaPago == $rs['IdFormaPago']) {
                                        $s = "selected";
                                    }
                                    echo "<option value=" . $rs['IdFormaPago'] . " " . $s . ">" . $rs['Nombre'] . "</option>";
                                }
                                ?>
                            </select>
                        </td>      
                        <td>Cuenta bancaria</td>
                        <td>
                        <select id="cuentaBancaria" name="cuentaBancaria" class="sizeMedio">
                            <?php                        
                            $consulta = "SELECT cb.idCuentaBancaria, cb.noCuenta, b.Nombre AS Banco FROM `c_cuentaBancaria` AS cb LEFT JOIN c_banco AS b ON b.IdBanco = cb.idBanco WHERE cb.Activo = 1 ORDER BY noCuenta;;";
                            $query = $catalogo->obtenerLista($consulta);
                            echo "<option value='' >Ninguna cuenta</option>";
                            while ($rs = mysql_fetch_array($query)) {
                                $s = "";
                                if ($ctBancaria != "" && $ctBancaria == $rs['idCuentaBancaria']) {
                                    $s = "selected";
                                }
                                echo "<option value=" . $rs['idCuentaBancaria'] . " " . $s . ">" . $rs['noCuenta'] . " ".$rs['Banco']."</option>";
                            }
                            ?>
                        </select>
                        <td>Dias de credito</td><td><input type="text" id="txt_Dias" name="txt_Dias" value="<?php echo $diasCredito; ?>"/></td>                        
                        <td>No. cliente</td><td><input type="text" id="txt_no_cliente" name="txt_no_cliente" value="<?php echo $noCliente; ?>"/></td>
                    </tr>
                    <tr>
                        <td>Referencia Numerica: </td><td><input type="text" id="referenciaNum" name="referenciaNum" value="<?php echo $referencia; ?>"/></td>
                        <td>Porcentaje de Pago por servicio: </td>
                        <td><input type="number" id="porcentaje_servicio" name="porcentaje_servicio" value="<?php echo $porcentaje; ?>"/></td>
                        <td></td><td></td>                       
                        <td colspan="2">
                            <input type="checkbox" name="activo" id="activo" <?php echo $activo; ?>/>Activo
                              <input type="checkbox" id="ck_notificacion" name="ck_notificacion" <?php echo $notificar; ?>/> Notificación al pagar
                        </td>
                    </tr>
                </table>
                <?php if(isset($_POST['id'])){?>
                <br>
                <img class="imagenMouse" src="resources/images/add.png" title="Nuevo" onclick="cambiarContenidos('<?php echo $altaCuenta; ?>')" style="float: right; cursor: pointer;" />
                <br>
                <?php } ?>
                <fieldset>
                    <legend>Domicilio</legend>
                    <table style="min-width: 100%;">
                        <tr>
                            <td>Calle<span class="obligatorio">*</span></td><td><input type="text" id="txt_calle" name="txt_calle" value="<?php echo $calle; ?>" /></td>
                            <td>Numero Exterior<span class="obligatorio">*</span></td><td><input type="text" id="txt_numExt" name="txt_numExt" value="<?php echo $noExterior; ?>" /></td>
                            <td>Número Interior</td><td><input type="text" id="txt_numInt" name="txt_numInt" value="<?php echo $noInterior; ?>" /></td>
                            <td>Colonia<span class="obligatorio">*</span></td><td><input type="text" id="txt_colonia" name="txt_colonia" value="<?php echo $colonia; ?>" /></td>
                        </tr>

                        <tr>
                            <td>Ciudad<span class="obligatorio">*</span></td><td><input type="text" id="txt_ciudad" name="txt_ciudad" value="<?php echo $ciudad; ?>"/></td>
                            <td>Delegación<span class="obligatorio">*</span></td><td><input type="text" id="txt_delegacion" name="txt_delegacion" value="<?php echo $delegacion; ?>"/></td>
                            <td>Estado<span class="obligatorio">*</span></td><td><input type="text" id="txt_estado" name="txt_estado" value="<?php echo $estado; ?>"/></td>                
                            <td>Pais<span class="obligatorio">*</span></td><td><input type="text" id="txt_pais" name="txt_pais" value="<?php echo $pais; ?>"/></td>                        
                        </tr>
                        <tr>                       
                            <td>Código postal<span class="obligatorio">*</span></td><td><input type="text" id="txt_cp" name="txt_cp" value="<?php echo $cp; ?>"/></td>                        
                        </tr>
                    </table>
                </fieldset>
                <br/><br/>
                <input type="submit" class="boton" value="Guardar" />
                <input type="submit" class="boton" value="Cancelar" onclick="cambiarContenidos('<?php echo $pagina_lista; ?>');
                        return false;"/>
                <?php
                echo "<input type='hidden' id='id' name='id' value='" . $clave . "'/> ";
                ?>
            </form>
        </div>
    </body>
</html>