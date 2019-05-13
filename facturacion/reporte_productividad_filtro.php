<?php
session_start();

if (!isset($_SESSION['user']) || $_SESSION['user'] == "") {
    header("Location: ../index.php");
}

include_once("../WEB-INF/Classes/Catalogo.class.php");
include_once("../WEB-INF/Classes/Parametros.class.php");

$parametros = new Parametros();
$tipo_cambio = 15;
$costo_tecnico = 500;
$costo_paqueteria = 150;
$costo_propio = 100;

if($parametros->getRegistroById(32)){
    $tipo_cambio = (float)$parametros->getValor();
}
if($parametros->getRegistroById(33)){
    $costo_tecnico = (float)$parametros->getValor();
}
if($parametros->getRegistroById(34)){
    $costo_paqueteria = (float)$parametros->getValor();
}
if($parametros->getRegistroById(35)){
    $costo_propio = (float)$parametros->getValor();
}
?>
<html>
    <head>
        <title></title>
        <script type="text/javascript" language="javascript" src="resources/js/paginas/facturacion/Pendientes_Pago.js"></script>
    </head>
    <body>
        <form target="_blank" action="facturacion/reporte_productividad.php" method="POST">
            <table style="width: 100%;">
                <tr>
                    <td>Fecha inicio</td>
                    <td>
                        <input type="text" class='fecha' id="fecha1" name="fecha1" value="<?php echo date('Y')."-01-01" ?>"/>
                    </td>
                    <td>Fecha Fin</td>
                    <td>
                        <input type="text" class='fecha' id="fecha2" name="fecha2" value="<?php echo date('Y')."-".date('m')."-".date('d') ?>"/>
                    </td>
                    <td><label for="cliente">Cliente: </label></td>
                    <td>
                        <select id="cliente" name="cliente[]" style="max-width: 200px;" class="multiselect" multiple="multiple">                            
                            <?php
                            $catalogo = new Catalogo();
                            $result = $catalogo->getListaAlta("c_cliente", "NombreRazonSocial");
                            while ($rs = mysql_fetch_array($result)) {                                
                                echo "<option value='" . $rs['ClaveCliente'] . "'>" . $rs['NombreRazonSocial'] . "</option>";
                            }
                            ?>
                        </select>
                    </td>
                    <td>
                        <input type="submit" class="button" value="Generar reporte productividad cliente" name="reporte1"/>
                        <br/><br/>
                        <input type="submit" class="button" value="Generar reporte productividad modelo" name="reporte2"/>
                    </td>                    
                </tr> 
                <tr>
                    <td>RFC</td>
                    <td>
                        <input type="text" id="rfc" name="rfc" value="" maxlength="14"/>
                    </td>
                    <td>Tipo de cambio</td>
                    <td colspan="3">
                        <input type="number" id="tipo_cambio" name="tipo_cambio" value="<?php echo $tipo_cambio; ?>" maxlength="6" step="any" required="required"/>
                        <br/><span style="font-size: 10px; color: blue;">* Este tipo de cambio se aplica al precio de lista cuándo no se encuentra un precio de compra de algún componente</span>
                    </td>
                </tr>
                <tr>
                    <td>Costo de ténico</td>
                    <td>
                        <input type="number" id="costo_tecnico" name="costo_tecnico" value="<?php echo $costo_tecnico; ?>" maxlength="9" step="any" required="required"/>
                        <br/><span style="font-size: 9px; color: blue;">* Costo promedio de un técnico cuándo hace servicios con cliente</span>
                    </td>
                    <td>Costo envío paquetería</td>
                    <td>
                        <input type="number" id="costo_paqueteria" name="costo_paqueteria" value="<?php echo $costo_paqueteria; ?>" maxlength="9" step="any" required="required"/>
                        <br/><span style="font-size: 9px; color: blue;">* Costo promedio de un envío por paquetería</span>
                    </td>
                    <td>Costo envío propio</td>
                    <td>
                        <input type="number" id="costo_propio" name="costo_propio" value="<?php echo $costo_propio; ?>" maxlength="9" step="any" required="required"/>
                        <br/><span style="font-size: 9px; color: blue;">* Costo promedio de un envío por transporte propio</span>
                    </td>
                </tr>
            </table>
        </form>
    </body>
</html>