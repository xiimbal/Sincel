<?php
session_start();

if (!isset($_SESSION['user']) || $_SESSION['user'] == "") {
    header("Location: index.php");
}
include_once("../WEB-INF/Classes/Proveedor.class.php");
include_once("../WEB-INF/Classes/Catalogo.class.php");
$catalogo = new Catalogo();
$pagina_lista = "admin/lista_proveedor_pakal.php";
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
        <script type="text/javascript" language="javascript" src="resources/js/paginas/alta_proveedor_pakal.js"></script>
        <script>
            $(document).ready(function() {
                $('.boton').button().css('margin-top', '20px');
            });
        </script>
    </head>
    <body>
        <div class="principal bg-white">
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
                <h3 class="text-dark"><b><?php echo "Alta proveedor"; ?></b></h3>
                <div class="form-row">
                       <div class="form-group col-md-3" style="w">
                           <label for="txt_nombre">Nombre</label>
                           <span class="obligatorio"> *</span>
                           <input class="form-control" type="text" id="txt_nombre" name="txt_nombre" value="<?php echo $nombre; ?>" />
                           <label for="txt_contacto">Contacto</label>
                           <input class="form-control" type="text" id="txt_contacto" name="txt_contacto" value="<?php echo $contacto; ?>"/>
                           <label for="txt_correo">Correo</label>
                           <input class="form-control" type="text" id="txt_correo" name="txt_correo" value="<?php echo $correo; ?>"/>
                       </div>
                       <div class="form-group col-md-3">
                           <label for="txt_rfc">RFC</label>
                           <span class="obligatorio">*</span>
                           <input class="form-control" type="text" id="txt_rfc" name="txt_rfc" value="<?php echo $rfc; ?>"/>
                           <label for="txt_tel">Telefono</label>
                           <input class="form-control" type="text" id="txt_tel" name="txt_tel" value="<?php echo $telefono; ?>"/>
                           
                       </div>
                       <div class="form-group col-md-3">
                           <label for="cuentaBancaria">Cuenta bancaria</label>
                           <select class="form-control" id="cuentaBancaria" name="cuentaBancaria" class="sizeMedio">
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
                           <label for="txt_no_cliente">No. cliente</label>
                           <input class="form-control" type="text" id="txt_no_cliente" name="txt_no_cliente" value="<?php echo $noCliente; ?>"/>
                       </div>
                       <div class="form-group col-md-3">
                           <label for="referenciaNum">Referencia Numerica: </label>
                           <input class="form-control" type="text" id="referenciaNum" name="referenciaNum" value="<?php echo $referencia; ?>"/>
                           <label for="activo">Activo</label>
                           <input type="checkbox" name="activo" id="activo" <?php echo $activo; ?>/>
                           <label for="ck_notificacion">Notificación al pagar</label>
                           <input type="checkbox" id="ck_notificacion" name="ck_notificacion" <?php echo $notificar; ?>/>
                       </div>
                </div>
                <?php if(isset($_POST['id'])){?>
                <a href="#" class="imagenMouse btn btn-success" title="Nuevo" onclick="cambiarContenidos('<?php echo $altaCuenta; ?>')" style="float: right; cursor: pointer;"><i class="fal fa-plus"></i></a>
                <!--img class="imagenMouse" src="resources/images/add.png" title="Nuevo" onclick="cambiarContenidos('<?php //echo $altaCuenta; ?>')" style="float: right; cursor: pointer;"-->
                
                <?php } ?>
                    <h3 class="text-dark">Domicilio</h3>
                <div class="form-row">
                    <div class="form-group col-md-3">
                      <label for="txt_calle">Calle</label>
                      <span class="obligatorio">*</span>
                      <input class="form-control" type="text" id="txt_calle" name="txt_calle" value="<?php echo $calle; ?>" />
                      <label for="txt_numExt">Numero Exterior</label><span class="obligatorio">*</span>
                      <input class="form-control" type="text" id="txt_numExt" name="txt_numExt" value="<?php echo $noExterior; ?>" />
                      <label for="txt_numInt">Número Interior</label>
                      <input class="form-control" type="text" id="txt_numInt" name="txt_numInt" value="<?php echo $noInterior; ?>" />
                      <label for="txt_colonia">Colonia</label>
                      <span class="obligatorio">*</span>
                      <input class="form-control" type="text" id="txt_colonia" name="txt_colonia" value="<?php echo $colonia; ?>" />
                    </div>
                    <div class="form-group col-md-3">
                      <label for="txt_ciudad">Ciudad</label>
                      <span class="obligatorio">*</span>
                      <input class="form-control" type="text" id="txt_ciudad" name="txt_ciudad" value="<?php echo $ciudad; ?>"/>
                      <label for="txt_delegacion">Delegación</label>
                      <span class="obligatorio">*</span>
                      <input class="form-control" type="text" id="txt_delegacion" name="txt_delegacion" value="<?php echo $delegacion; ?>"/>
                      <label for="txt_estado">Estado</label>
                      <span class="obligatorio">*</span>
                      <input class="form-control" type="text" id="txt_estado" name="txt_estado" value="<?php echo $estado; ?>"/>
                      <label for="">Pais</label>
                      <span class="obligatorio">*</span>
                      <input class="form-control" type="text" id="txt_pais" name="txt_pais" value="<?php echo $pais; ?>"/>
                    </div>
                    <div class="form-group col-md-3">
                      <label for="">Código postal</label>
                      <span class="obligatorio">*</span>
                      <input class="form-control" type="text" id="txt_cp" name="txt_cp" value="<?php echo $cp; ?>"/>
                      <br>
                      <input type="submit" class="btn btn-success" value="Guardar" />
                      
                        <input type="submit" class="btn btn-danger" value="Cancelar" onclick="cambiarContenidos('<?php echo $pagina_lista; ?>');
                        return false;"/>
                        <?php
                        echo "<input type='hidden' id='id' name='id' value='" . $clave . "'/> ";
                        ?>
                    </div>
                </div>
            </form>
        </div>
    </body>
</html>