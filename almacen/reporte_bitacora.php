<?php

session_start();

if (!isset($_SESSION['user']) || $_SESSION['user'] == "" || !isset($_POST['id'])) {
    header("Location: index.php");
}

include_once("../WEB-INF/Classes/Parametros.class.php");

$parametros = new Parametros();
$mostrarContadores = true;
if($parametros->getRegistroById("13") && $parametros->getValor() == "0"){
    $mostrarContadores = false;
}

$idBitacora = $_POST['id'];

?>
<!DOCTYPE html>
<html lang="es">
    <head>
        <script type="text/javascript" language="javascript" src="resources/js/paginas/reporte_bitacora.js"></script>
        <style>
            .area_trabajo {margin: 5% 5% 0 5%;}            
        </style>
    </head>
    <body>
        <div class="area_trabajo">
            <form id="form_bitacora" name="form_bitacora" target="_blank" action="reportes/reporte_bitacora.php" method="POST">
                <table>
                    <tr>
                        <td><label for="fecha_inicio">Fecha inicial</label></td>
                        <td><input type="text" class="fecha" name="fecha_inicio" id="fecha_inicio" readonly="readonly"/></td>
                        <td><label for="fecha_final">Fecha final</label></td>
                        <td><input type="text" class="fecha" name="fecha_final" id="fecha_final" readonly="readonly"/></td>
                    </tr>
                </table>
                <br/><input type="checkbox" checked="checked" id="datos_actuales" name="datos_actuales" value="datos_actuales"/>Datos actuales
                <br/><input type="checkbox" checked="checked" id="h_clientes" name="h_clientes" value="h_clientes"/>H. Clientes
                <br/><input type="checkbox" checked="checked" id="h_tickets" name="h_tickets" value="h_tickets"/>H. Tickets                
                <br/><input type="checkbox" checked="checked" id="h_refacciones" name="h_refacciones" value="h_refacciones"/>H. Refacciones
                <?php
                    if($mostrarContadores){
                        echo '<br/><input type="checkbox" checked="checked" id="h_toner" name="h_toner" value="h_toner"/>H. Toner';
                    }
                ?>
                
                <br/><input type="checkbox" checked="checked" id="h_mantenimiento" name="h_mantenimiento" value="h_mantenimiento"/>H. Mantenimientos
                <br/><input type="checkbox" checked="checked" id="h_incidencias" name="h_incidencias" value="h_incidencias"/>H. Incidencias
                <input type="hidden" id="id" name="id" value ="<?php echo $idBitacora; ?>"/>
                <br/><br/>
                <input type="submit" class="button" id="aceptar" name="aceptar" value="Aceptar"/>
                <input type="submit" class="button" id="cancelar" name="cancelar" value="Cancelar" onclick="cambiarContenidos('almacen/lista_bitacora.php','Bitácoras'); return false;"/>
            </form>
        </div>
    </body>
</html>
