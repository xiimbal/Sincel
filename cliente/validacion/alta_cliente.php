<?php
session_start();

if (!isset($_SESSION['user']) || $_SESSION['user'] == "") {
    header("Location: ../../index.php");
}

include_once("../../WEB-INF/Classes/Cliente.class.php");
include_once("../../WEB-INF/Classes/Catalogo.class.php");
include_once("../../WEB-INF/Classes/PermisosSubMenu.class.php");    
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
    <body>   
        <form id="formCliente" name="formCliente" action="/" method="POST">
            <div class="container-fluid p-3 rounded bg-light">
                <h5>Cliente</h5>
                <div class="form-row">
                    <div class="form-group col-md-4 col-12">
                        <label for="nombre_cliente2">Nombre:<span class="obligatorio"> *</span></label>
                        <input type="text" id="nombre_cliente2" name="nombre_cliente2" class="form-control" value="<?php echo $Nombre; ?>"/>
                    </div>
                    <div class="form-group col-md-4 col-12">
                        <label for="rfc_cliente2">RFC:<span class="obligatorio"> *</span>
                        <input type="text" id="rfc_cliente2" name="rfc_cliente2" class="form-control" value="<?php echo $rfc; ?>"/>
                        <div id="error_rfc" style="color: red;"></div>
                    </div>
                    <div class="form-group col-md-4 col-12">
                        <label for="razon_cliente2">Razón social:<span class="obligatorio"> *</span></label>
                        <select id="razon_cliente2" name="razon_cliente2" class="custom-select">
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
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group col-md-4 col-12">
                        <label for="modalidad2">Tipo cliente:</label>
                        <select id="modalidad2" name="modalidad2" class="custom-select">
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
                    </div>
                    <div class="form-group col-md-4 col-12">
                        <label for="tipo">Tipo:</label>
                        <select id="tipo" name="tipo" class="custom-select">
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
                    </div>
                    <div class="form-group col-md-4 col-12">
                        <label for="tipo_facturacion">Tipo facturaci&oacute;n:</label>
                        <select id="tipo_facturacion" name="tipo_facturacion" class="custom-select">
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
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group col-md-4 col-12">
                        <label for="dias_credito">D&iacute;as de cr&eacute;dito:</label>
                        <input type="text" id="dias_credito" name="dias_credito" value="<?php echo $diasCredito; ?>" class="form-control"/>
                    </div>
                    <div class="form-group col-md-4 col-12">
                        <label for="tipo_morosidad">Tipo Morosidad:</label>
                        <select id="tipo_morosidad" name="tipo_morosidad" class="custom-select">
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
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-check col-md-3 col-6">
                        <?php
                            $s = "";
                            if ($Moroso == "2") $s = "checked";
                            echo "<input type=\"checkbox\" class=\"form-check-input\" value=\"1\" name=\"Moroso\" id=\"Moroso\" " . $s . "/>";
                        ?>
                        <label for="Moroso">Moroso:</label>
                    </div>
                    <div class="form-check col-md-3 col-6">
                        <?php echo "<input type=\"checkbox\" class=\"form-check-input\" value=\"1\" name=\"genera\" id=\"genera\" $GeneraFactura/>"; ?>
                        <label for="Moroso">Generar pre-factura atm:</label>
                    </div>
                    <div class="form-check col-md-3 col-6">
                        <?php echo "<input type=\"checkbox\" class=\"form-check-input\" value=\"1\" name=\"mostrar_contrato\" id=\"mostrar_contrato\" $MostrarContrato/>"; ?>
                        <label for="mostrar_contrato">Mostrar contrato en factura:</label>
                    </div>
                    <div class="form-check col-md-3 col-6">
                        <?php  echo "<input type=\"checkbox\" class=\"form-check-input\" value=\"1\" name=\"activo\" id=\"activo\" $Activo/>"; ?>
                        <label for="activo">Activo</label>
                    </div>
                </div>
                <div class="form-row">
                    <div class="col-md-4 col-12 offset-md-8">
                        <input type="submit" class="btn btn-secondary" value="Cancelar" onclick=" cambiarContenidoValidaciones('cliente2', '../cliente/validacion/lista_cliente.php?Nombre=<?php echo $NombreCodificado; ?>&Clave=<?php echo $ClaveGET; ?>&NombreCentro=<?php echo $_GET['NombreCentro']; ?>&ClaveCentro=<?php echo $_GET['ClaveCentro']; ?>', 
                            <?php
                                if (isset($_POST['idTicket'])) {
                                    echo "'" . $_POST['idTicket'] . "'";
                                } else {
                                    echo "null";
                                }
                            ?>, null); return false;"/>
                        <?php if($permisos_grid->tienePermisoEspecial($_SESSION['idUsuario'],16) || empty($id)){  ?>
                            <input type="submit" class="btn btn-secondary" value="Guardar" />                
                        <?php } ?>
                    </div>
                </div>
                                                     
                
                <input type="hidden" name="id" id="id" value="<?php echo $id; ?>"/>
                <input type="hidden" name="independiente" id="independiente" value="true"/>
            
            </div>
        </form>
        <script type="text/javascript" language="javascript" src="../resources/js/paginas/alta_validacion.js"></script>
        <script type="text/javascript" language="javascript" src="../resources/js/paginas/validacion/alta_cliente.js"></script>      
    </body>
</html>