<?php
session_start();
if (!isset($_SESSION['user']) || $_SESSION['user'] == "") {
    header("Location: ../index.php");
}
include_once("../WEB-INF/Classes/Catalogo.class.php");
$catalogo = new Catalogo();
?>
<!DOCTYPE html>
<html lang="es">
    <head>        
        <title>Reporte de resurtido de almacén</title>
        <script type="text/javascript" language="javascript" src="resources/js/paginas/lista.js"></script>
        <script type="text/javascript" language="javascript" src="resources/js/paginas/almacen/reporte_resurtido.js"></script>
    </head>
    <body>
        <br/>
        <form id="form1" name="form1" action="almacen/reporte_resurtido_pdf.php" method="POST" target="_blank">            
            <table style="width: 95%;">
                <tr>
                    <td><label for="almacen">Almacén</label></td>
                    <td>
                        <select class="filtro" id="almacen" name="almacen">
                            <?php
                            $result = $catalogo->getListaAlta("c_almacen", "nombre_almacen");
                            while ($rs = mysql_fetch_array($result)) {
                                $s = "";
                                if ($rs['id_almacen'] == "6") {
                                    $s = "selected='selected'";
                                }
                                echo "<option value='" . $rs['id_almacen'] . "' $s>" . $rs['nombre_almacen'] . "</option>";
                            }
                            ?>
                        </select>
                    </td>
                    <td>Proveedor:</td>
                    <td>
                        <select id="slProveedor" name="slProveedor" class="filtro" style="width: 155px">
                            <option value="0">Selecione un opción</option>
                            <?php
                            $queryProveedor = $catalogo->getListaAlta("c_proveedor", "NombreComercial");
                            while ($rs = mysql_fetch_array($queryProveedor)) {                                
                                echo "<option value='" . $rs['ClaveProveedor'] . "' $s>" . $rs['NombreComercial'] . "</option>";
                            }
                            ?>
                        </select>
                    </td>   
                </tr>
                <tr>
                    <td>Tipo</td>
                    <td>
                        <select id="tipo" name="tipo[]" class="multiselect" onchange="cambiarselectmodelo('tipo', 'modelo');" style="width: 230px;" multiple="multiple">                                                        
                            <?php
                            $query2 = $catalogo->obtenerLista("SELECT c_tipocomponente.IdTipoComponente AS ID,c_tipocomponente.Nombre AS Nombre FROM c_tipocomponente ORDER BY Nombre;");
                            while ($rs = mysql_fetch_array($query2)) {
                                echo "<option value=\"" . $rs['ID'] . "\">" . $rs['Nombre'] . "</option>";
                            }
                            ?>
                        </select>
                    </td>
                    <td>Modelo</td>
                    <td>
                        <select id="modelo" name="modelo" class="filtro" style="width: 230px;">
                            <option value="">Selecciona el modelo</option>
                        </select>
                    </td>
                    <td><input type="checkbox" id="agrupar" name="agrupar" value="1"/>Agrupar por modelo</td>
                </tr>
                <tr>
                    <td><label for="fecha_inicial">Fecha inicial</label></td>
                    <td><input type="text" class="fecha" id="fecha_inicial" name="fecha_inicial" readonly="readonly"/></td>
                    <td><label for="fecha_final">Fecha final</label></td>
                    <td><input type="text" class="fecha" id="fecha_final" name="fecha_final" readonly="readonly"/></td>
                </tr>                
            </table>
            <div style="font-size: 9px; color: gray;">Las fechas son unicamente para filtrar la cantidad de movimientos</div>
            <br/>
            <input type="submit" class="button" id="submit_selector" name="submit_Selector" value="Generar" style="margin-left: 80%;"/>
        </form>
    </body>
</html>