<?php
session_start();

if (!isset($_SESSION['user']) || $_SESSION['user'] == "") {
    header("Location: ../../index.php");
}

include_once("../../WEB-INF/Classes/ServicioIM.class.php");
include_once("../../WEB-INF/Classes/ServicioGIM.class.php");
include_once("../../WEB-INF/Classes/ServicioFA.class.php");
include_once("../../WEB-INF/Classes/ServicioGFA.class.php");
include_once("../../WEB-INF/Classes/Catalogo.class.php");
include_once("../../WEB-INF/Classes/Anexo.class.php");
include_once("../../WEB-INF/Classes/PermisosSubMenu.class.php");    
$permisos_grid = new PermisosSubMenu();
$back = "";
$catalogo = new Catalogo();
$ClaveAnexo = "";
if (isset($_GET['Anexo'])) {
    $ClaveAnexo = $_GET['Anexo'];
}

$CC = "";
$idKAnexo = "";

if (isset($_GET['CC']) && $_GET['CC']!="") {
    $CC = $_GET['CC'];
}else{
    $consulta = "SELECT cat.ClaveAnexoTecnico, cc.ClaveCliente, cc.ClaveCentroCosto 
        FROM c_anexotecnico AS cat
        LEFT JOIN c_contrato AS ctt ON ctt.NoContrato = cat.NoContrato
        LEFT JOIN c_cliente AS c ON c.ClaveCliente = ctt.ClaveCliente
        LEFT JOIN c_centrocosto AS cc ON cc.ClaveCliente = c.ClaveCliente
        WHERE cat.ClaveAnexoTecnico = '$ClaveAnexo'
        GROUP BY cc.ClaveCentroCosto;";    
    $result = $catalogo->obtenerLista($consulta);
    while ($rs = mysql_fetch_array($result)) {        
        if (isset($rs['ClaveCentroCosto']) && $rs['ClaveCentroCosto']!="") {
            $CC = $rs['ClaveCentroCosto'];
            break;
        }
    }
    if($CC == ""){
        echo "<br/>Error, no hay dato de localidad";
        return;
    }
}

$anexo = new Anexo();
$idKanexos = $anexo->getIdAnexosDeAnexoLocalidad($ClaveAnexo, $CC);
if (!empty($idKanexos)) {//Si ya existe un k_anexo para esta localidad
    $idKAnexo = $idKanexos[0];
} else {//Sino existe
    $anexo->setClaveAnexoTecnico($ClaveAnexo);
    $anexo->setClaveCC($CC);
    $anexo->setFechaElaboracion(date("Y") . "-" . date("m") . "-" . date("d"));
    $anexo->setUsuarioCreacion($_SESSION['user']);
    $anexo->setUsuarioUltimaModificacion($_SESSION['user']);
    $anexo->setPantalla("PHP AltaServicio_im");
    if ($anexo->newK_anexoClienteCC()) {
        $idKAnexo = $anexo->getIdAnexoClienteCC();
    }
}

$nuevo = "";
if (isset($_GET['Nuevo'])) {
    $nuevo = "si";
}

if ($nuevo == "si") {
    $prefijo_menor = strtolower($_POST['idKServicio']);
    $prefijo_mayor = strtoupper($_POST['idKServicio']);
} else {
    $prefijo_menor = strtolower($_GET['prefijo']);
    $prefijo_mayor = strtoupper($_GET['prefijo']);
}

//echo "$nuevo Prefijos: $prefijo_mayor $prefijo_menor";

echo "<select id='tipo_servicio' name='tipo_servicio' onchange='cambiarContenidoServicio(\"div_servicioim\",\"ventas/validacion/alta_servicio_im.php?Nuevo=true&CC=$CC&Anexo=$ClaveAnexo\",
                                true,\"$ClaveAnexo\",this.value);'>";
if ($nuevo == "si") {
    echo "<option value=''>Seleccione un tipo de servicio</option>";
}
$s = "";
if ($prefijo_menor == "im") {
    $s = "selected='selected'";
}
if ($nuevo == "si" || $s != "") {
    echo "<option value='im' $s>Particulares de impresión</option>";
}
$s = "";
if ($prefijo_menor == "gim") {
    $s = "selected='selected'";
}
if ($nuevo == "si" || $s != "") {
    echo "<option value='gim' $s>Globales de impresión</option>";
}
$s = "";
if ($prefijo_menor == "fa") {
    $s = "selected='selected'";
}
if ($nuevo == "si" || $s != "") {
    echo "<option value='fa' $s>Particulares FA</option>";
}
$s = "";
if ($prefijo_menor == "gfa") {
    $s = "selected='selected'";
}
if ($nuevo == "si" || $s != "") {
    echo "<option value='gfa' $s>Globales FA</option>";
}
echo "</select>";

if (!isset($_POST['idKServicio']) || $_POST['idKServicio'] == "") {
    return;
}

$id = "";
$tipo = "";
$renta = "";
$incluidasBN = "";
$incluidasColor = "";
$excedenteBN = "";
$excedenteColor = "";
$procesadasColor = "";
$procesadasBN = "";

if ($prefijo_menor == "im") {
    $div = "servicios_p2";
    $pagina = "lista_servicios_parti.php";
} else if ($prefijo_menor == "fa") {
    $div = "servicios_p2";
    $pagina = "lista_servicios_parti.php";
} else if ($prefijo_menor == "gim") {
    $div = "servicios_p2";
    $pagina = "lista_servicios_parti.php";
} else if ($prefijo_menor == "gfa") {
    $div = "servicios_p2";
    $pagina = "lista_servicios_parti.php";
}

if (isset($_POST['idKServicio']) && !is_null($_POST['idKServicio']) && $_POST['idKServicio'] != "" && $nuevo == "") {
    $id = $_POST['idKServicio'];
    if ($prefijo_menor == "im") {
        $obj = new ServicioIM();
        $obj->getRegistroById($id);
        $tipo = $obj->getIdServicioIM();
        $renta = $obj->getRentaMensual();
        $incluidasBN = $obj->getPaginasIncluidasBN();
        $incluidasColor = $obj->getPaginasIncluidasColor();
        $excedenteBN = $obj->getCostoPaginasExcedentesBN();
        $excedenteColor = $obj->getCostoPaginasExcedentesColor();
        $procesadasColor = $obj->getCostoPaginaProcesadaColor();
        $procesadasBN = $obj->getCostoPaginaProcesadaBN();
    } else if ($prefijo_menor == "fa") {
        $obj = new ServicioFA();
        $obj->getRegistroById($id);
        $tipo = $obj->getIdServicioFA();
        $renta = $obj->getRentaMensual();
        $incluidasBN = $obj->getMLIncluidosBN();
        $incluidasColor = $obj->getMLIncluidosColor();
        $excedenteBN = $obj->getCostoMLExcedentesBN();
        $excedenteColor = $obj->getCostoMLExcedentesColor();
        $procesadasColor = $obj->getCostoMLProcesadosColor();
        $procesadasBN = $obj->getCostoMLProcesadosBN();
    } else if ($prefijo_menor == "gim") {
        $obj = new KServicioGIM();
        $obj->getRegistroById($id);
        $tipo = $obj->getIdServicioGIM();
        $renta = $obj->getRentaMensual();
        $incluidasBN = $obj->getPaginasIncluidasBN();
        $incluidasColor = $obj->getPaginasIncluidasColor();
        $excedenteBN = $obj->getCostoPaginasExcedentesBN();
        $excedenteColor = $obj->getCostoPaginasExcedentesColor();
        $procesadasBN = $obj->getCostoPaginaProcesadaBN();
        $procesadasColor = $obj->getCostoPaginaProcesadaColor();
    } else if ($prefijo_menor == "gfa") {
        $obj = new ServicioGFA();
        $obj->getRegistroById($id);
        $tipo = $obj->getIdServicioGFA();
        $renta = $obj->getRentaMensual();
        $incluidasBN = $obj->getMLIncluidosBN();
        $incluidasColor = $obj->getMLIncluidosColor();
        $excedenteBN = $obj->getCostoMLExcedentesBN();
        $excedenteColor = $obj->getCostoMLExcedentesColor();
        $procesadasBN = $obj->getCostoMLProcesadosBN();
        $procesadasColor = $obj->getCostoMLProcesadosColor();
    }
}
?>
<!DOCTYPE html>
<html lang="es">
    <head>
        <title></title>
        <script type="text/javascript" language="javascript" src="<?php echo $back; ?>resources/js/paginas/alta_validacion.js"></script>
        <script type="text/javascript" language="javascript" src="<?php echo $back; ?>resources/js/paginas/validacion/alta_servicioim.js"></script>
        <style>
            .chico{width: 80px;}
        </style>
    </head>
    <body>        
        <!--<fieldset>-->
        <!--<legend>Servicios generales</legend>-->
        <form id="formServicios<?php echo $prefijo_mayor; ?>" name="formServicios<?php echo $prefijo_mayor; ?>" action="/" method="POST">
            <input type="hidden" name="anexo_servicioIM" id="anexo_servicioIM" value="<?php echo $idKAnexo; ?>"/>
            <input type="hidden" name="nuevo_servicoIM" id="nuevo_servicoIM" value="<?php echo $nuevo; ?>"/>
            <table style="width: 100%">                    
                <tr>
                    <td><label for="tipo_servicioIM">Tipo:</label></td>
                    <td>
                        <select id="tipo_servicioIM" name="tipo_servicioIM" style="max-width: 180px;" onchange="cambiarTipoServicio('tipo_servicioIM');">
                            <?php
                            $query = $catalogo->getListaAlta("c_servicio" . $prefijo_menor, "IdServicio" . $prefijo_mayor);
                            echo "<option value='0' >Selecciona una opción</option>";
                            while ($rs = mysql_fetch_array($query)) {
                                $s = "";
                                if ($tipo != "" && $tipo == $rs['IdServicio' . $prefijo_mayor]) {
                                    $s = "selected";
                                }
                                echo "<option value=" . $rs['IdServicio' . $prefijo_mayor] . " " . $s . ">" . $rs['Nombre'] . "</option>";
                            }
                            ?>
                        </select>
                    </td>
                    <td><label for="renta_servicioIM" class='renta' style='display: none;'>Renta mensual:</label></td>
                    <td><input type="text" class="chico renta" id="renta_servicioIM" name="renta_servicioIM" value="<?php echo $renta; ?>" style='display: none;'/></td>
                </tr>                    
                <tr>
                    <td><label for="incluidasBN" class='pibn' style='display: none;'>P&aacute;ginas incluidas B/N:</label></td>
                    <td><input type="text" class="chico pibn" id="incluidasBN" name="incluidasBN" value="<?php echo $incluidasBN; ?>" style='display: none;'/></td>
                    <td><label for="incluidasColor" class='picl' style='display: none;'>P&aacute;ginas incluidas Color:</label></td>
                    <td><input type="text" class="chico picl" id="incluidasColor" name="incluidasColor" value="<?php echo $incluidasColor; ?>" style='display: none;'/></td>
                </tr>                       
                <tr>
                    <td><label for="excedentesBN" class='pebn' style='display: none;'>Costo p&aacute;ginas excedentes B/N:</label></td>
                    <td><input type="text" class="chico pebn" id="excedentesBN" name="excedentesBN" value="<?php echo $excedenteBN; ?>" style='display: none;'/></td>
                    <td><label for="excedentesColor" class='pecl' style='display: none;'>Costo p&aacute;ginas excedentes Color:</label></td>
                    <td><input type="text" class="chico pecl" id="excedentesColor" name="excedentesColor" value="<?php echo $excedenteColor; ?>" style='display: none;'/></td>
                </tr>
                <tr>
                    <td><label for="procesadasBN" class='ppbn' style='display: none;'>Costo p&aacute;ginas procesadas B/N:</label></td>
                    <td><input type="text" class="chico ppbn" id="procesadasBN" name="procesadasBN" value="<?php echo $procesadasBN; ?>" style='display: none;'/></td>
                    <td><label for="procesadasColor" class='ppcl' style='display: none;'>Costo p&aacute;ginas procesadas Color:</label></td>
                    <td><input type="text" class="chico ppcl" id="procesadasColor" name="procesadasColor" value="<?php echo $procesadasColor; ?>" style='display: none;'/></td>
                </tr>                    
            </table>
            <input type="submit" id="cancelar_servicioim" class="boton" id="cancelar" value="Cancelar" style="float: right; margin-right: 5px;"
                   onclick="cargarDependencia('<?php echo $div; ?>', '<?php echo $back; ?>ventas/validacion/<?php echo $pagina; ?>', '<?php echo $ClaveAnexo; ?>', null, '<?php echo $CC; ?>');
                           return false;"/>
            <?php if($permisos_grid->tienePermisoEspecial($_SESSION['idUsuario'],16) || empty($id)){  ?>
                    <input type="submit" class="boton" value="Guardar" id="guardar_servicio_im" onclick="subimitForm();
                    return false;" style="float: right; margin-right: 5px;" />
            <?php } ?>
            
            <input type="hidden" id="div_pagina" name="div_pagina" value="<?php echo $div; ?>"/>
            <input type="hidden" id="pagina" name="pagina" value="<?php echo $pagina; ?>"/>
            <input type="hidden" id="claveAnexo" name="claveAnexo" value="<?php echo $ClaveAnexo; ?>"/>
            <input type="hidden" id="cc" name="cc" value="<?php echo $CC; ?>"/>
            <input type="hidden" name="id" id="id" value="<?php echo $id; ?>"/> 
            
            <input type="hidden" name="prefijo" id="prefijo" value="<?php echo $prefijo_menor; ?>"/>
            <br/><br/><br/><br/>
        </form>
        <!--</fieldset>-->
    </body>
</html>