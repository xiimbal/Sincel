<?php
session_start();

if (!isset($_SESSION['user']) || $_SESSION['user'] == "") {
    header("Location: ../../index.php");
}
include_once("../../WEB-INF/Classes/Catalogo.class.php");
include_once("../../WEB-INF/Classes/Cliente.class.php");
include_once("../../WEB-INF/Classes/Contrato.class.php");
include_once("../../WEB-INF/Classes/Localidad.class.php");
include_once("../../WEB-INF/Classes/PermisosSubMenu.class.php");
$permisos_grid = new PermisosSubMenu();
$catalogo = new Catalogo();
$id = "";
$fechaInicio = "";
$fechaFin = "";
$fechaFirma = "";
$disabled = "";
$Calle = "";
$NoExterior = "";
$NoInterior = "";
$Colonia = "";
$Estado = "";
$Delegacion = "";
$CP = "";
$Activo = "checked='checked'";
$FormaPago = "";
$MetodoPago = "";
$DomicilioFiscal = "";
$RazonSocial = "";
$numero_cuenta = "";
$idBanco = "";
$idFormaComprobante = "";
$idUsoCFDI = "";
$idCuentaBancaria = "";
$facturarA = "";
$dias_credito = "";
$desactivarLimbo = "disabled";
$checked = "";
$numero = 1;
if (isset($_POST['id']) && !is_null($_POST['id'])) {
    $equipo = new Contrato();
    $equipo->getRegistroById($_POST['id']);
    $id = $_POST['id'];
    if (substr($id, 0, 5) == "Limbo") {
        $checked = "checked";
    }
    $fechaInicio = $equipo->getFechaInicio();
    $fechaFin = $equipo->getFechaTermino();
    $fechaFirma = $equipo->getFechaFirma();
    $Clave = $equipo->getClaveCliente();
    $FormaPago = $equipo->getFormaPago();
    $MetodoPago = $equipo->getIdMetodoPago();
    $localidad = new Localidad();
    $DomicilioFiscal = $localidad->getLocalidadByClaveTipo($equipo->getClaveCliente(), 3);
    $RazonSocial = $equipo->getRazonSocial();
    $numero_cuenta = $equipo->getNumeroCuenta();
    $idBanco = $equipo->getIdBanco();
    $idCuentaBancaria = $equipo->getIdCuentaBancaria();
    $dias_credito = $equipo->getDiasCredito();
    $facturarA = $equipo->getFacturarA();
    $idFormaComprobante = $equipo->getIdFormaComprobantePago();    
    $idUsoCFDI = $equipo->getIdUsoCFDI();

    if ($equipo->getActivo() == "0") {
        $Activo = "";
    }
    if ($DomicilioFiscal != "") {
        $localidad = new Localidad();
        $DomicilioFiscal = $localidad->getLocalidadByClaveTipo($equipo->getClaveCliente(), 3);
        $Calle = $localidad->getCalle();
        $NoExterior = $localidad->getNoExterior();
        $NoInterior = $localidad->getNoInterior();
        $Colonia = $localidad->getColonia();
        $Estado = $localidad->getEstado();
        $Delegacion = $localidad->getDelegacion();
        $CP = $localidad->getCodigoPostal();
    }
    $disabled = "readonly='readonly'";
}

if (isset($_GET['idCliente'])) {
    $ClaveGET = $_GET['idCliente'];
    $cliente = new Cliente();
    $grupo = "";
    if ($cliente->getRegistroById($ClaveGET)) {
        $grupo = $cliente->getClaveGrupo();
    }
    if (isset($_GET['Nuevo'])) {
        $Clave = $ClaveGET;
        $disabled = "";
        $desactivarLimbo = "";
    }
    $DomicilioFiscal = $localidad->getLocalidadByClaveTipo($ClaveGET, 3);
    //echo $ClaveGET." - $DomicilioFiscal";
    if ($DomicilioFiscal != "") {
        $localidad = new Localidad();
        $DomicilioFiscal = $localidad->getLocalidadByClaveTipo($ClaveGET, 3);
        $Calle = $localidad->getCalle();
        $NoExterior = $localidad->getNoExterior();
        $NoInterior = $localidad->getNoInterior();
        $Colonia = $localidad->getColonia();
        $Estado = $localidad->getEstado();
        $Delegacion = $localidad->getDelegacion();
        $CP = $localidad->getCodigoPostal();
    }
}
?>
<!DOCTYPE html>
<html lang="es">
    <head>
        <title></title>
        <script type="text/javascript" language="javascript" src="../resources/js/paginas/alta_validacion.js"></script>
        <script type="text/javascript" language="javascript" src="../resources/js/paginas/validacion/alta_contrato.js"></script>
    </head>
    <body>        
        <fieldset>
            <legend>Contrato</legend>
            <form id="formContrato" name="formContrato" action="/" method="POST" >
                <input type="hidden" id="clave_contrato_cliente" name="clave_contrato_cliente" value="<?php echo $_GET['idCliente']; ?>"/>
                <table style="width: 100%" id="tcontrato">       
                    <tr>
                        <td><label for="fecha_ini2">Fecha inicio:</label></td>
                        <td><input type="text" id="fecha_ini2" name="fecha_ini2" class="complete fecha" value="<?php echo $fechaInicio; ?>"/></td>
                        <td><label for="fecha_fin2">Fecha final:</label></td>
                        <td><input type="text" id="fecha_fin2" name="fecha_fin2" class="complete fecha" value="<?php echo $fechaFin; ?>"/></td>
                    </tr>
                    <tr>
                        <td><label for="forma_pago">Forma de pago:</label></td>
                        <td>
                            <select id="forma_pago" name="forma_pago">
                                <option value="">Selecciona la forma de pago</option><?php                                
                                $result = $catalogo->getListaAlta("c_formapago", "Nombre");
                                while ($rs = mysql_fetch_array($result)) {
                                    $s = ($rs['IdFormaPago'] == $FormaPago)? "selected" : "";                                
                                    echo "<option value='" . $rs['IdFormaPago'] . "' $s>" . $rs['Nombre'] . " - ".$rs['Descripcion']."</option>";
                                }
                                ?>
                            </select>
                        </td>    
                        <td><label for="metodo_pago">Método de pago:</label></td>
                        <td>
                            <select id="metodo_pago" name="metodo_pago">
                                <option value="">Selecciona el método de pago</option><?php                                
                                $result = $catalogo->getListaAlta("c_metodopago", "ClaveMetodoPago");
                                while ($rs = mysql_fetch_array($result)) {
                                    $s = ($rs['IdMetodoPago'] == $MetodoPago)? "selected" : "";                                    
                                    echo "<option value='" . $rs['IdMetodoPago'] . "' $s>" . $rs['ClaveMetodoPago'] . " ".$rs['MetodoPago']."</option>";
                                }
                                ?>
                            </select>
                        </td> 
                    </tr>
                    <tr>
                        <td><label for="numero_cuenta">Número Cuenta:</label></td>
                        <td>
                            <input type="number" id="numero_cuenta" name="numero_cuenta" style="width: 200px;" value="<?php echo $numero_cuenta; ?>"/>                            
                        </td>
                        <td><label for="banco">Banco</label></td>
                        <td>
                            <select id="banco" name="banco" class="complete">
                                <option value="">Seleccione un banco</option>
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
                        </td>
                    </tr>
                    <tr>
                        <td><label for="razon_social">Razón Social:</label></td>
                        <td>
                            <select id="razon_social" name="razon_social">
                                <option value="">Selecciona la razón social</option><?php
                                $consulta = "SELECT * FROM c_datosfacturacionempresa";
                                $result = $catalogo->obtenerLista($consulta);
                                while ($rs = mysql_fetch_array($result)) {
                                    $s = "";
                                    if ($rs['IdDatosFacturacionEmpresa'] == $RazonSocial) {
                                        $s = "selected";
                                    }
                                    echo "<option value='" . $rs['IdDatosFacturacionEmpresa'] . "' $s>" . $rs['RazonSocial'] . "</option>";
                                }
                                ?>
                            </select>
                        </td>
                        <td>Uso CFDI</td>
                            <td>
                                <select id="usoCFDI" name="usoCFDI" style="width: 250px;">
                                <?php
                                    $result = $catalogo->getListaAlta("c_usocfdi", "ClaveCFDI");
                                    echo "<option value=''>Selecciona una opción</option>";
                                    while($rs = mysql_fetch_array($result)){
                                        $s = "";
                                        if($idUsoCFDI == (int)$rs['IdUsoCFDI']){
                                            $s = "selected";
                                        }
                                        echo "<option value='".$rs['IdUsoCFDI']."' $s>".$rs['ClaveCFDI']." ".$rs['Descripcion']."</option>";
                                    }
                                ?>
                                </select>
                            </td>
                    </tr>
                    <tr>
                        <td><label for="dias_credito">D&iacute;as de cr&eacute;dito:</label></td>
                        <td>
                            <input type="text" id="dias_credito" name="dias_credito" style="width: 200px;" value="<?php echo $dias_credito; ?>"/>                            
                        </td>
                        <td></td>
                        <td>
                            <input type="checkbox" name="limbo" value="limbo" align="left" <?php echo $desactivarLimbo . " " . $checked; ?>> Limbo
                            <input type="checkbox" name="activo" value="1" align="left" <?php echo $Activo; ?>> Activo
                        </td>                        
                    </tr>
                    <tr>
                        <td colspan="4"><h2>Valores para impresión de factura</h2></td>
                    </tr>
                    <tr>
                        <td style="text-align:center">Campo</td>
                        <td style="text-align:center">Valor</td>
                        <td></td>
                        <td></td>
                    <tr>    
                        <?php
                        if (!empty($id)) {
                            $resultCampos = $equipo->getCamposByNoContrato($id);
                        } else {
                            $resultCampos = NULL;
                        }

                        if (empty($id) || mysql_num_rows($resultCampos) == 0) {
                            echo "<tr id='row_$numero'>";
                            echo "<td style='text-align:center'><input type='text' name='campo_$numero' id='campo_$numero'></td>";
                            echo "<td style='text-align:center'><input type='text' name='valor_$numero' id='valor_$numero'></td>";
                            echo "<td style='text-align:center'><input type='checkbox' name='mostrar_$numero' id='mostrar_$numero' value='Activo'></td>";
                            echo '<td style="text-align:center"><input type="image" src="../resources/images/add.png" title="Agregar otro concepto" onclick="agregarConcepto(); return false;" /></td>';
                            echo "<td></td>";
                            echo "</tr>";
                        } else {
                            while ($rsCampos = mysql_fetch_array($resultCampos)) {
                                echo "<tr id='row_$numero'>";
                                echo "<td style='text-align:center'><input type='text' name='campo_$numero' id='campo_$numero' value = '" . $rsCampos['campo'] . "'></td>";
                                echo "<td style='text-align:center'><input type='text' name='valor_$numero' id='valor_$numero' value = '" . $rsCampos['valor'] . "'></td>";
                                $s = "";
                                if ((int) $rsCampos['mostrarPDF'] == 1) {
                                    $s = "checked";
                                }
                                echo "<td style='text-align:center'><input type='checkbox' name='mostrar_$numero' id='mostrar_$numero' value='Activo' $s></td>";
                                echo '<td style="text-align:center"><input type="image" src="../resources/images/add.png" title="Agregar otro concepto" onclick="agregarConcepto(); return false;" /></td>';
                                echo "<td><input type='image' src='../resources/images/Erase.png' title='Eliminar este concepto' onclick='borrarConcepto(" . $numero . "); return false;'/></td>";
                                $numero++;
                                echo "</tr>";
                            }
                            $numero--;
                        }
                        ?>
                </table>
                <fieldset>
                    <legend>Complemento de pago</legend>
                    <table>
                        <tr>
                            <td><label for="forma_pago_complemento">Forma de pago:</label></td>
                            <td>
                                <select id="forma_pago_complemento" name="forma_pago_complemento">
                                    <option value="">Selecciona la forma de pago</option><?php                                
                                    $result = $catalogo->getListaAlta("c_formapago", "Nombre");
                                    while ($rs = mysql_fetch_array($result)) {
                                        $s = "";
                                        if ($rs['IdFormaPago'] == $idFormaComprobante) {
                                            $s = "selected";
                                        }
                                        echo "<option value='" . $rs['IdFormaPago'] . "' $s>" . $rs['Nombre'] . " - ".$rs['Descripcion']."</option>";
                                    }
                                    ?>
                                </select>
                            </td>
                            <td><label for="banco">Cuenta Bancaria Beneficiaria</label></td>
                            <td>
                                <select id="cuenta_bancaria" name="cuenta_bancaria" class="complete">
                                    <option value="">Seleccione una cuenta bancaria</option>
                                    <?php
                                    $consulta = "SELECT cb.idCuentaBancaria, cb.noCuenta, cb.tipoCuenta, b.Nombre AS Banco
                                        FROM c_cuentaBancaria AS cb
                                        LEFT JOIN c_banco AS b ON b.IdBanco = cb.idBanco
                                        WHERE cb.RFC IN(SELECT IdDatosFacturacionEmpresa FROM c_cliente WHERE ClaveCliente = '$ClaveGET') 
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
                            </td>                                                        
                        </tr>
                    </table>
                </fieldset>
                <fieldset>
                    <legend>Dirección Fiscal</legend>
                    <table style=" width:100%">
                        <tr>
                            <td >
                                Calle:<br />
                            </td>
                            <td>
                                <input name="Calle" type="text" id="Calle" value="<?php echo $Calle; ?>" style="width:350px;" />
                            </td>
                            <td >
                                No.exterior:<br /> 
                            </td>
                            <td>
                                <input name="NoExterior" type="text" id="NoExterior" value="<?php echo $NoExterior; ?>" style="width:100px;" />
                            </td>
                            <td >
                                No. interior:<br />
                            </td>
                            <td>
                                <input name="NoInterior" type="text" id="NoInterior" value="<?php echo $NoInterior; ?>" style="width:100px;" />
                            </td>
                        </tr>
                    </table>
                    <table>
                        <tr>
                            <td >
                                Colonia:<br />
                            </td>
                            <td>
                                <input name="Colonia" type="text" id="Colonia" value="<?php echo $Colonia; ?>"style="width:250px;" />
                            </td>
                            <td style=" width:40px"></td>
                            <td >
                                Estado:<br />
                            </td>
                            <td>
                                <select name="Estado" id="Estado">
                                    <?php
                                    $nombres = Array("Selecciona un estado", "Aguascalientes", "Baja California", "Baja California Sur", "Campeche", "Ciudad de México", "Coahuila", "Colima", "Chiapas", "Chihuahua", "Durango", "Estado de México", "Guanajuato", "Guerrero", "Hidalgo", "Jalisco", "Michoacán", "Morelos", "Nayarit", "Nuevo León", "Oaxaca", "Puebla", "Querétaro", "Quintana Roo", "San Luis Potosí", "Sinaloa", "Sonora", "Tabasco", "Tamaulipas", "Tlaxcala", "Veracruz", "Yucatán", "Zacatecas");
                                    $values = Array("", "Aguascalientes", "Baja California", "Baja California Sur", "Campeche", "Ciudad de México", "Coahuila", "Colima", "Chiapas", "Chihuahua", "Durango", "Estado de México", "Guanajuato", "Guerrero", "Hidalgo", "Jalisco", "Michoacán", "Morelos", "Nayarit", "Nuevo León", "Oaxaca", "Puebla", "Querétaro", "Quintana Roo", "San Luis Potosí", "Sinaloa", "Sonora", "Tabasco", "Tamaulipas", "Tlaxcala", "Veracruz", "Yucatán", "Zacatecas");
                                    if ($Estado != "") {
                                        for ($var = 0; $var < count($values); $var++) {
                                            if ($values[$var] == $Estado) {
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
                            </td>
                        </tr>
                        <tr>
                            <td >
                                Delegación:<br />
                            </td>
                            <td>
                                <input name="Delegacion" type="text" id="Delegacion" value="<?php echo $Delegacion; ?>" style="width:200px;" />
                            </td>
                            <td >
                                C.P:
                            </td>
                            <td>
                                <input name="CP" type="text" id="CP" value="<?php echo $CP; ?>" />
                            </td>

                        </tr>
                        <?php if (isset($grupo) && $grupo != "") { ?>
                            <tr>
                                <td>Este cliente factura a :</td>
                                <td>
                                    <select id="facturarA" name="facturarA">
                                        <option value="">Selecciona el cliente</option><?php
                            $consulta = "SELECT ClaveCliente, NombreRazonSocial FROM c_cliente WHERE ClaveGrupo = '$grupo' AND Activo = 1";
                            $result = $catalogo->obtenerLista($consulta);
                            while ($rs = mysql_fetch_array($result)) {
                                $s = "";
                                if ($rs['ClaveCliente'] == $facturarA) {
                                    $s = "selected";
                                }
                                echo "<option value='" . $rs['ClaveCliente'] . "' $s>" . $rs['NombreRazonSocial'] . "</option>";
                            }
                            ?></select>
                                </td>
                            </tr>
                        <?php } ?>
                    </table>
                </fieldset>
                <input type="submit" id="cancelar_contrato" class="boton" value="Cancelar" style="float: right; margin-right: 5px;" onclick="cambiarContenidoValidaciones('contrato2', '../cliente/validacion/lista_contrato.php', <?php
                        if (isset($_POST['idTicket'])) {
                            echo "'" . $_POST['idTicket'] . "'";
                        } else {
                            echo "null";
                        }
                        ?>, '<?php echo $_GET['idCliente']; ?>', null);
                        return false;"/>
                       <?php if ($permisos_grid->tienePermisoEspecial($_SESSION['idUsuario'], 16) || empty($id)) { ?>
                    <input type="submit" class="boton" value="Guardar" style="float: right; margin-right: 5px;" />                
                <?php } ?>
                <input type="hidden" name="id" id="id" value="<?php echo $id; ?>"/>
                <input type="hidden" name="independiente" id="independiente" value="true"/>
                <input type="hidden" id="numero_conceptos" name="numero_conceptos" value="<?php echo $numero; ?>"/>
                <br/><br/><br/><br/>
            </form>
        </fieldset>        
    </body>
</html>