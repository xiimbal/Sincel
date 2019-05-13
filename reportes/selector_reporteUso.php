<?php
session_start();
if (!isset($_SESSION['user']) || $_SESSION['user'] == "") {
    header("Location: ../index.php");
}
//$idReporte = $_POST['id'];
?>
<!DOCTYPE html>
<html lang="es">
    <head>        
        <script>
            $(document).ready(function() {
                $('.fecha').each(function() {
                    $(this).datepicker({
                        dateFormat: 'yy-mm-dd',
                        changeMonth: true,
                        changeYear: true
                    });
                });
                $("#form1").validate({
                    rules: {
                        fecha_inicial: {"required": true},
                        fecha_final: {"required": true},
                        impresion_bn: {'required':true, 'number':true},
                        impresion_color: {'required':true, 'number':true},
                        copia_color: {'required':true, 'number':true},
                        copia_bn: {'required':true, 'number':true},
                        scan: {'required':true, 'number':true}
                    }, messages: {
                        fecha_inicial: {"required": ' * Selecciona la fecha inicial'},
                        fecha_final: {"required": ' * Selecciona la fecha final'},
                        impresion_bn: {'required':' * Escribe el precio', 'number':' * S\u00f3lo se permiten n\u00fameros'},
                        impresion_color: {'required':' * Escribe el precio', 'number':' * S\u00f3lo se permiten n\u00fameros'},
                        copia_color: {'required':' * Escribe el precio', 'number':' * S\u00f3lo se permiten n\u00fameros'},
                        copia_bn: {'required':' * Escribe el precio', 'number':' * S\u00f3lo se permiten n\u00fameros'},
                        scan: {'required':' * Escribe el precio', 'number':' * S\u00f3lo se permiten n\u00fameros'}
                    }

                });
            });
        </script>
    </head>
    <body>
        <br/>
        <form id="form1" name="form1" action="reportes/reporteUsoGraficado.php" method="POST" target="_blank">
            <!--<div style="margin: 5% 0 5% 1%;"> El sistema está ahora listo para generar los reportes del archivo cargado.</div>-->
            <!--<input type="hidden" id="id_reporte" name="id_reporte" value="<?php //echo $idReporte ?>"/>-->
            <table style="width: 95%;">
                <tr>
                    <td><label for="fecha_inicial">Fecha inicial</label></td>
                    <td><input type="text" class="fecha" id="fecha_inicial" name="fecha_inicial" readonly="readonly"/></td>
                    <td><label for="fecha_final">Fecha final</label></td>
                    <td><input type="text" class="fecha" id="fecha_final" name="fecha_final" readonly="readonly"/></td>
                </tr>
                <tr>
                    <td><label for="impresion_color">Precio de impresi&oacute;n color</label></td>
                    <td><input type="text" id="impresion_color" name="impresion_color"/></td>
                    <td><label for="impresion_bn">Precio de impresi&oacute;n B/N</label></td>
                    <td><input type="text" id="impresion_bn" name="impresion_bn"/></td>
                </tr>
                <tr>
                    <td><label for="impresion_bn">Precio de copia color</label></td>
                    <td><input type="text" id="copia_color" name="copia_color"/></td>
                    <td><label for="impresion_bn">Precio de copia B/N</label></td>
                    <td><input type="text" id="copia_bn" name="copia_bn"/></td>
                </tr>
                <tr>
                    <td><label for="scan">Precio de scaneo</label></td>
                    <td><input type="text" id="scan" name="scan"/></td>
                    <td></td>
                    <td></td>
                </tr>
            </table>
            <br/>
            <input type="submit" class="button" id="submit_selector" name="submit_Selector" value="Generar" style="margin-left: 80%;"/>
        </form>
    </body>
</html>