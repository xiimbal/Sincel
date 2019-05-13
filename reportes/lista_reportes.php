<?php
session_start();
if (!isset($_SESSION['user']) || $_SESSION['user'] == "") {
    header("Location: ../index.php");
}
?>
<!DOCTYPE html>
<html lang="es">
    <head>
        <title>Lista de reportes de servicios</title>
        <script type="text/javascript" language="javascript" src="resources/js/paginas/lista.js"></script>
        <script type="text/javascript" language="javascript" src="resources/js/paginas/reportes/lista_reportes.js"></script>
    </head>
    <body>
        <form id="formReporteServicio" name="formReporteServicio" action="#" method="POST" target="_blank">            
            <table style="width: 95%;">
                <tr>
                    <td>Fecha Inicio</td>
                    <td><input type="text" id="Fecha_Inicio" name="Fecha_Inicio" class="fecha"/></td>
                    <td>Fecha Fin</td>
                    <td><input type="text" id="Fecha_Fin" name="Fecha_Fin" class="fecha"/></td>
                </tr>
            </table>
            <br/><br/>
            <table style="width: 70%;">
                <tr>                    
                    <td><input type="button" id="reporte_facturas" name="reporte_facturas" value="5.2 Facturas" class="boton"/></td>
                    <td><input type="button" id="reporte_pagos" name="reporte_pagos" value="4.4 Reporte de pagos" class="boton"/></td>
                    <td><input type="button" id="layout_facturas" name="layout_facturas" value="5.3 Layout factura" class="boton"/></td>
                </tr>
            </table>
        </form>
    </body>
</html>
