<?php
session_start();

if (!isset($_SESSION['user']) || $_SESSION['user'] == "") {
    header("Location: index.php");
}

include_once("../WEB-INF/Classes/Catalogo.class.php");

$catalogo = new Catalogo();
?>
<html>
    <head>
        <title>Reporte de compras</title>
        <script type="text/javascript" language="javascript" src="resources/js/paginas/compras/reportecompras.js"></script>
        <style>
            .size{width: 90%;}
        </style>
    </head>
    <body>
        <form action="compras/reporte_global.php" target="_blank" method="POST">
            <table style="width: 100%;">
                <tr>
                    <td><label for="pedido">N&uacute;mero de pedido</label></td>
                    <td><input type="text" id="pedido" name="pedido"/></td>
                    <td><label for="orden">Orden de compra</label></td>
                    <td><input type="text" id="orden" name="orden"/></td>
                    <td><label for="proveedor">Proveedor</label></td>
                    <td>
                        <select id="proveedor" name="proveedor[]" multiple="multiple" class="multiple" style="max-width: 220px;">
                            <?php
                                $result = $catalogo->getListaAlta("c_proveedor", "NombreComercial");
                                while($rs = mysql_fetch_array($result)){
                                    echo "<option value='".$rs['ClaveProveedor']."'>".$rs['NombreComercial']." (".$rs['RFC'].")</option>";
                                }
                            ?>
                        </select>
                    </td>
                </tr>
                <tr>
                    <td><label for="fecha_inicio">Inicio fecha compra</label></td>
                    <td><input type="text" id="fecha_inicio" name="fecha_inicio" class="fecha"/></td>
                    <td><label for="fecha_fin">Fin fecha compra</label></td>
                    <td><input type="text" id="fecha_fin" name="fecha_fin" class="fecha"/></td>
                    <td><label for="tipo">Tipo</label></td>
                    <td>
                        <select id="tipo" name="tipo[]" multiple="multiple" class="multiple" style="max-width: 220px;">
                            <?php
                                echo "<option value='0'>Equipo</option>";
                                $result = $catalogo->getListaAlta("c_tipocomponente", "Nombre");
                                while($rs = mysql_fetch_array($result)){
                                    echo "<option value='".$rs['IdTipoComponente']."'>".$rs['Nombre']."</option>";
                                }
                            ?>
                        </select>
                    </td>
                </tr>
                <tr>
                    <td><label for="factura">Factura</label></td>
                    <td><input type="text" id="factura" name="factura"/></td>
                    <td><label for="fecha_factura_inicio">Inicio Fecha factura</label></td>
                    <td><input type="text" id="fecha_factura_inicio" name="fecha_factura_inicio" class="fecha"/></td>
                    <td><label for="fecha_factura_fin">Fin Fecha factura</label></td>
                    <td><input type="text" id="fecha_factura_fin" name="fecha_factura_fin" class="fecha"/></td>
                </tr>
                <tr>
                    <td colspan="4"></td>
                    <td colspan="2">
                        <input type="submit" value="Generar reporte" class="boton" style="margin-left: 10%;"/>
                    </td>
                </tr>
            </table>
        </form>        
    </body>
</html>
