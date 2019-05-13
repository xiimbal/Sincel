<?php
session_start();

if (!isset($_SESSION['user']) || $_SESSION['user'] == "") {
    header("Location: index.php");
}

include_once("../WEB-INF/Classes/Catalogo.class.php");
$reporte = "compras/lista_reporte_compras.php"; 

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
        <form action="compras/lista_reporte_compras.php" target="_blank" method="POST" class="bg-white">
            <div class="form-row">
                <div class="form-group col-md-4">
                    <label for="pedido">N&uacute;mero de pedido</label>
                    <input class="form-control" type="text" id="pedido" name="pedido"/>
                </div>
                <div class="form-group col-md-4">
                    <label for="orden">Orden de compra</label>
                    <input class="form-control" type="text" id="orden" name="orden"/>
                </div>
                <div class="form-group col-md-4">
                    <label for="proveedor">Proveedor</label>
                    <select id="proveedor" name="proveedor[]" multiple="multiple" class="multiple form-control" style="max-width: 220px;">
                            <?php
                                $result = $catalogo->getListaAlta("c_proveedor", "NombreComercial");
                                while($rs = mysql_fetch_array($result)){
                                    echo "<option value='".$rs['ClaveProveedor']."'>".$rs['NombreComercial']." (".$rs['RFC'].")</option>";
                                }
                            ?>
                        </select>
                </div>
            </div>
            <div class="form-row">
                <div class="form-group col-md-4">
                    <label for="fecha_inicio">Inicio fecha compra</label>
                    <input type="text" id="fecha_inicio" name="fecha_inicio" class="fecha form-control"/>
                </div>
                <div class="form-group col-md-4">
                    <label for="fecha_fin">Fin fecha compra</label>
                    <input type="text" id="fecha_fin" name="fecha_fin" class="fecha form-control"/>
                </div>
                <div class="form-group col-md-4">
                    <label for="tipo">Tipo</label>
                    <select id="tipo" name="tipo[]" multiple="multiple" class="multiple form-control" style="max-width: 220px;">
                            <?php
                                echo "<option value='0'>Equipo</option>";
                                $result = $catalogo->getListaAlta("c_tipocomponente", "Nombre");
                                while($rs = mysql_fetch_array($result)){
                                    echo "<option value='".$rs['IdTipoComponente']."'>".$rs['Nombre']."</option>";
                                }
                            ?>
                        </select>
                </div>
            </div>
            <div class="form-row">
                <div class="form-group col-md-4">
                    <label for="factura">Factura</label>
                    <input class="form-control" type="text" id="factura" name="factura"/>
                </div>
                <div class="form-group col-md-4">
                    <label for="fecha_factura_inicio">Inicio Fecha factura</label>
                    <input type="text" id="fecha_factura_inicio" name="fecha_factura_inicio" class="fecha form-control"/>
                </div>
                <div class="form-group col-md-4">
                    <label for="fecha_factura_fin">Fin Fecha factura</label>
                    <input type="text" id="fecha_factura_fin" name="fecha_factura_fin" class="fecha form-control"/>
                </div>
                <input type="submit" value="Generar reporte" class="btn btn-success" style="margin-left: 10%;" onclick='cambiarContenidos("<?php echo $reporte; ?>");' />
            </div>
        </form>        
    </body>
</html>
